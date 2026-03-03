<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, Category $category): Response
    {
        // Admin global peut voir toutes les catégories
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Vérifier si l'utilisateur est membre actif de la colocation
        $isActiveMember = $category->colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($isActiveMember) {
            return Response::allow();
        }

        return Response::deny('Vous n\'êtes pas membre de cette colocation.');
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): Response
    {
        // L'utilisateur doit avoir une colocation active
        if (!$user->hasActiveColocation()) {
            return Response::deny('Vous devez avoir une colocation active pour ajouter des catégories.');
        }

        // Les utilisateurs bannis ne peuvent pas créer de catégories
        if ($user->isBanned()) {
            return Response::deny('Votre compte est banni.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): Response
    {
        // Admin global peut modifier toutes les catégories
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire de la colocation peut modifier les catégories
        $isOwner = ((int) $category->colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut modifier les catégories.');
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): Response
    {
        // Admin global peut supprimer toutes les catégories
        if ($user->isGlobalAdmin()) {
            return Response::allow();
        }

        // Seul le propriétaire de la colocation peut supprimer les catégories
        $isOwner = ((int) $category->colocation->owner_id === (int) $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        return Response::deny('Seul le propriétaire peut supprimer les catégories.');
    }
}
