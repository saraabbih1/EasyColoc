<?php

namespace App\Policies;

use App\Models\Colocation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ColocationPolicy
{
    /**
     * Determine whether the user can view the colocation.
     */
    public function view(User $user, Colocation $colocation): Response
    {
        // Admin global peut voir toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Vérifier si l'utilisateur est membre actif de la colocation
        $isActiveMember = $colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($isActiveMember) {
            return Response::allow();
        }

        return Response::deny('Vous n\'êtes pas membre de cette colocation.');
    }

    /**
     * Determine whether the user can create colocations.
     */
    public function create(User $user): Response
    {
        // L'utilisateur peut créer une colocation s'il n'en a pas déjà une active
        if ($user->hasActiveColocation()) {
            return Response::deny('Vous avez déjà une colocation active.');
        }

        // Les utilisateurs bannis ne peuvent pas créer de colocation
        if ($user->isBanned()) {
            return Response::deny('Votre compte est banni.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the colocation.
     */
    public function update(User $user, Colocation $colocation): Response
    {
        // Admin global peut modifier toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire peut modifier la colocation
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut modifier la colocation.');
    }

    /**
     * Determine whether the user can delete the colocation.
     */
    public function delete(User $user, Colocation $colocation): Response
    {
        // Admin global peut supprimer toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire peut supprimer la colocation
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut supprimer la colocation.');
    }

    /**
     * Determine whether the user can manage the colocation (membres, invitations, etc.).
     */
    public function manage(User $user, Colocation $colocation): Response
    {
        // Admin global peut gérer toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire peut gérer la colocation
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut gérer la colocation.');
    }

    /**
     * Determine whether the user can invite members.
     */
    public function invite(User $user, Colocation $colocation): Response
    {
        // Admin global peut inviter dans toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire peut inviter des membres
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut inviter des membres.');
    }

    /**
     * Determine whether the user can remove members.
     */
    public function removeMember(User $user, Colocation $colocation): Response
    {
        // Admin global peut retirer des membres de toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire peut retirer des membres
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut retirer des membres.');
    }

    /**
     * Determine whether the user can leave the colocation.
     */
    public function leave(User $user, Colocation $colocation): Response
    {
        // Admin global ne peut pas quitter une colocation (il peut la supprimer)
        if ($user->isGlobalAdmin()) {
            return Response::deny('En tant qu\'administrateur, vous ne pouvez pas quitter une colocation.');
        }

        // L'utilisateur doit être membre actif pour pouvoir partir
        $isActiveMember = $colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isActiveMember) {
            return Response::deny('Vous n\'êtes pas membre de cette colocation.');
        }

        // Le propriétaire ne peut pas partir s'il y a d'autres membres
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner && $colocation->activeMemberships()->count() > 1) {
            return Response::deny('Le propriétaire ne peut pas quitter la colocation tant qu\'il y a d\'autres membres.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can cancel the colocation.
     */
    public function cancel(User $user, Colocation $colocation): Response
    {
        // Admin global peut annuler toutes les colocations
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire peut annuler la colocation
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut annuler la colocation.');
    }
}
