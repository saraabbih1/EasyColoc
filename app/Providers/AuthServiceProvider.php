<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Colocation' => 'App\Policies\ColocationPolicy',
        'App\Models\Expense' => 'App\Policies\ExpensePolicy',
        'App\Models\Category' => 'App\Policies\CategoryPolicy',
        'App\Models\Settlement' => 'App\Policies\SettlementPolicy',
        'App\Models\Payment' => 'App\Policies\PaymentPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Global Admin Gates
        Gate::before(function ($user, $ability) {
            if ($user->isGlobalAdmin()) {
                return true;
            }
        });

        // Colocation Gates
        Gate::define('view', function ($user, $colocation) {
            return $user->memberships()
                ->where('colocation_id', $colocation->id)
                ->where('status', 'active')
                ->exists();
        });

        Gate::define('manage', function ($user, $colocation) {
            return ((int) $colocation->owner_id === (int) $user->id);
        });

        Gate::define('member', function ($user, $colocation) {
            return $user->memberships()
                ->where('colocation_id', $colocation->id)
                ->where('status', 'active')
                ->exists();
        });

        // Expense Gates
        Gate::define('edit', function ($user, $expense) {
            return $expense->user_id === $user->id || 
                   ((int) $expense->colocation->owner_id === (int) $user->id);
        });

        Gate::define('delete', function ($user, $expense) {
            return $expense->user_id === $user->id || 
                   ((int) $expense->colocation->owner_id === (int) $user->id);
        });

        // Settlement Gates
        Gate::define('markAsPaid', function ($user, $settlement) {
            return $settlement->debtor_id === $user->id || $settlement->creditor_id === $user->id;
        });
    }
}
