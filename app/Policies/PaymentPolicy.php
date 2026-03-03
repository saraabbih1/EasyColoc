<?php

namespace App\Policies;

use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function create(User $user, Colocation $colocation): Response
    {
        if ($user->isBanned()) {
            return Response::deny('Votre compte est banni.');
        }

        $isActiveMember = $colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isActiveMember) {
            return Response::deny('Vous n\'etes pas membre actif de cette colocation.');
        }

        return Response::allow();
    }

    public function view(User $user, Payment $payment): Response
    {
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        if ((int) $payment->from_user_id === (int) $user->id || (int) $payment->to_user_id === (int) $user->id) {
            return Response::allow();
        }

        return Response::deny('Acces non autorise a ce paiement.');
    }

    public function pay(User $user, Expense $expense): Response
    {
        $isActiveMember = $expense->colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isActiveMember) {
            return Response::deny('Vous n\'etes pas membre actif de cette colocation.');
        }

        if ((int) $expense->user_id === (int) $user->id) {
            return Response::deny('Vous ne pouvez pas payer votre propre depense.');
        }

        $paid = (float) $expense->payments()->sum('amount');
        if ($paid >= ((float) $expense->amount - 0.009)) {
            return Response::deny('Cette depense est deja reglee.');
        }

        return Response::allow();
    }
}
