<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Membership;
use App\Models\User;

class AdminService
{
    public function dashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_banned', false)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'total_colocations' => Colocation::count(),
            'active_colocations' => Colocation::active()->count(),
            'cancelled_colocations' => Colocation::cancelled()->count(),
            'total_expenses' => Expense::count(),
            'total_expenses_amount' => Expense::sum('amount'),
            'total_memberships' => Membership::count(),
            'active_memberships' => Membership::active()->count(),
        ];
    }
}
