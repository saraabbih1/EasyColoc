<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

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
}