<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Membership;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AdminController extends Controller
{
    public function __construct(private readonly AdminService $adminService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard()
    {
        $stats = $this->adminService->dashboardStats();

        $recentUsers = User::latest()->take(10)->get();
        $recentColocations = Colocation::latest()->take(10)->get();
        $bannedUsers = User::where('is_banned', true)->latest()->take(10)->get();

        if (DB::getDriverName() === 'sqlite') {
            $monthlyRegistrations = User::selectRaw("strftime('%Y', created_at) as year, strftime('%m', created_at) as month, COUNT(*) as count")
                ->groupBy('year', 'month')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->take(12)
                ->get();

            $monthlyExpenses = Expense::selectRaw("strftime('%Y', expense_date) as year, strftime('%m', expense_date) as month, SUM(amount) as total, COUNT(*) as count")
                ->groupBy('year', 'month')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->take(12)
                ->get();
        } else {
            $monthlyRegistrations = User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get();

            $monthlyExpenses = Expense::selectRaw('YEAR(expense_date) as year, MONTH(expense_date) as month, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get();
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentColocations',
            'bannedUsers',
            'monthlyRegistrations',
            'monthlyExpenses'
        ));
    }

    public function users()
    {
        $users = User::query()
            ->with([
                'memberships' => function ($query) {
                    $query->where('status', 'active')->with('colocation');
                }
            ])
            ->withCount(['memberships', 'expenses'])
            ->latest()
            ->paginate(50);

        return view('admin.users', compact('users'));
    }

    public function banUser(User $user)
    {
        if ($user->isGlobalAdmin()) {
            return back()->with('error', 'Vous ne pouvez pas bannir un administrateur global.');
        }

        $user->ban();

        return back()->with('success', 'Utilisateur banni avec succès.');
    }

    public function unbanUser(User $user)
    {
        $user->unban();

        return back()->with('success', 'Utilisateur débanni avec succès.');
    }

    public function colocations()
    {
        $colocations = Colocation::with(['owner', 'activeMemberships.user'])
            ->withCount(['memberships', 'expenses'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.colocations', compact('colocations'));
    }

    public function expenses()
    {
        $expenses = Expense::with(['colocation', 'user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.expenses', compact('expenses'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'users');

        $results = [];

        switch ($type) {
            case 'users':
                $results = User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->with([
                        'memberships' => function ($query) {
                            $query->where('status', 'active')->with('colocation');
                        }
                    ])
                    ->paginate(20);
                break;
            case 'colocations':
                $results = Colocation::where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->with(['owner', 'activeMemberships'])
                    ->paginate(20);
                break;
            case 'expenses':
                $results = Expense::where('title', 'like', "%{$query}%")
                    ->with(['colocation', 'user', 'category'])
                    ->paginate(20);
                break;
        }

        return view('admin.search', compact('results', 'query', 'type'));
    }

    public function mailTest()
    {
        $user = auth()->user();

        try {
            Log::info('Admin mail test started', [
                'mailer' => config('mail.default'),
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            Mail::raw(
                "Test SMTP EasyColoc\n\nMailer: ".config('mail.default')."\nDate: ".now()->toDateTimeString(),
                function ($message) use ($user): void {
                    $message->to($user->email)->subject('EasyColoc - Test Email');
                }
            );

            return back()->with('success', 'Email de test envoye a '.$user->email.' via '.config('mail.default').'.');
        } catch (Throwable $e) {
            Log::error('Admin mail test failed', [
                'mailer' => config('mail.default'),
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', "Echec de l'email de test. Verifiez storage/logs/laravel.log.");
        }
    }
}
