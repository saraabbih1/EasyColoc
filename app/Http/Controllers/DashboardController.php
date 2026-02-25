<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Colocation;


class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $membership = $user->memberships()
            ->where('status', 'active')
            ->with('colocation');
            //->first();

        $totalPaid = Expense::where('user_id', $user->id)->sum('amount');

        return view('dashboard', compact('user', 'membership', 'totalPaid'));
    }
    public function calculateBalances($colocationId)
{
    $colocation = Colocation::with(['memberships.user', 'expenses'])
        ->findOrFail($colocationId);

    $members = $colocation->memberships->where('status', 'active');
    $totalExpenses = $colocation->expenses->sum('amount');
    $membersCount = $members->count();

    $share = $membersCount > 0 ? $totalExpenses / $membersCount : 0;

    $balances = [];

    foreach ($members as $membership) {
        $user = $membership->user;
        $paid = $colocation->expenses
            ->where('user_id', $user->id)
            ->sum('amount');

        $balances[] = [
            'user' => $user->name,
            'paid' => $paid,
            'balance' => $paid - $share
        ];
    }

    return $balances;
}
}