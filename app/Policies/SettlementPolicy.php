<?php

namespace App\Policies;

use App\Models\Settlement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SettlementPolicy
{
    /**
     * Determine whether the user can view the settlement.
     */
    public function view(User $user, Settlement $settlement): Response
    {
        // Admin global peut voir tous les settlements
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Vérifier si l'utilisateur est membre actif de la colocation
        $isActiveMember = $settlement->colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($isActiveMember) {
            return Response::allow();
        }

        return Response::deny('Vous n\'êtes pas membre de cette colocation.');
    }

    /**
     * Determine whether the user can create settlements.
     */
    public function create(User $user): Response
    {
        // L'utilisateur doit avoir une colocation active
        if (!$user->hasActiveColocation()) {
            return Response::deny('Vous devez avoir une colocation active pour gérer les dettes.');
        }

        // Les utilisateurs bannis ne peuvent pas créer de settlements
        if ($user->isBanned()) {
            return Response::deny('Votre compte est banni.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can mark the settlement as paid.
     */
    public function markAsPaid(User $user, Settlement $settlement): Response
    {
        // Admin global peut marquer tous les settlements comme payés
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Vérifier si l'utilisateur est membre actif de la colocation
        $isActiveMember = $settlement->colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isActiveMember) {
            return Response::deny('Vous n\'êtes pas membre de cette colocation.');
        }

        // Seul le débiteur ou le créditeur peut marquer le paiement
        if ($settlement->debtor_id === $user->id || $settlement->creditor_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Seul le débiteur ou le créditeur peut marquer ce paiement.');
    }

    /**
     * Determine whether the user can optimize settlements.
     */
    public function optimize(User $user): Response
    {
        // L'utilisateur doit avoir une colocation active
        if (!$user->hasActiveColocation()) {
            return Response::deny('Vous devez avoir une colocation active pour optimiser les dettes.');
        }

        // Les utilisateurs bannis ne peuvent pas optimiser les settlements
        if ($user->isBanned()) {
            return Response::deny('Votre compte est banni.');
        }

        return Response::allow();
    }
}
