<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\Colocation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    /**
     * Determine whether the user can view the expense.
     */
    public function view(User $user, Expense $expense): Response
    {
        // Admin global peut voir toutes les dépenses
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Vérifier si l'utilisateur est membre actif de la colocation
        $isActiveMember = $expense->colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($isActiveMember) {
            return Response::allow();
        }

        return Response::deny('Vous n\'êtes pas membre de cette colocation.');
    }

    /**
     * Determine whether the user can create expenses.
     */
    public function create(User $user, Colocation $colocation): Response
    {
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        $isActiveMember = $colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isActiveMember) {
            return Response::deny('Vous n\'etes pas membre de cette colocation.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the expense.
     */
    public function update(User $user, Expense $expense): Response
    {
        // Admin global peut modifier toutes les dépenses
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Le propriétaire de la colocation peut modifier toutes les dépenses
        $isOwner = ((int) $expense->colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        // L'utilisateur qui a créé la dépense peut la modifier
        if ($expense->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Vous ne pouvez pas modifier cette dépense.');
    }

    /**
     * Determine whether the user can delete the expense.
     */
    public function delete(User $user, Expense $expense): Response
    {
        // Admin global peut supprimer toutes les dépenses
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Le propriétaire de la colocation peut supprimer toutes les dépenses
        $isOwner = ((int) $expense->colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        // L'utilisateur qui a créé la dépense peut la supprimer
        if ($expense->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Vous ne pouvez pas supprimer cette dépense.');
    }
}

