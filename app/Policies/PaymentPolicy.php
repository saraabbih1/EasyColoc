<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function create(User $user): Response
    {
        if ($user->isBanned()) {
            return Response::deny('Votre compte est banni.');
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
}
