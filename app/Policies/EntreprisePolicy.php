<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Entreprise;
use App\Models\User;

class EntreprisePolicy
{
    public function before(User $user, string $ability)
    {
        if ($user->role === 'super_admin') {
            return true;
        }
    }

    /**
     * Détermine si l'utilisateur peut voir toutes les entreprises.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'super_admin' || $user->role === 'admin_entreprise' || $user->role === 'consultant_entreprise';
    }

    /**
     * Détermine si l'utilisateur peut voir une entreprise spécifique.
     * Les admins d'entreprise ne peuvent voir que leur propre entreprise.
     */
    public function view(User $user, Entreprise $entreprise): bool
    {
        return $user->role === 'super_admin' ||
               ($user->role === 'admin_entreprise' && $user->id === $entreprise->user_id) ||
               ($user->role === 'consultant_entreprise' && $user->id === $entreprise->user_id);
               // Adaptez la logique ici si un consultant peut voir toute entreprise de "son" entreprise
    }

    /**
     * Détermine si l'utilisateur peut créer une entreprise.
     * Seul un admin d'entreprise peut créer une entreprise (la sienne), ou un super admin.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin_entreprise' || $user->role === 'super_admin';
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour une entreprise.
     * Un admin d'entreprise ne peut mettre à jour que sa propre entreprise.
     */
    public function update(User $user, Entreprise $entreprise): bool
    {
        return $user->role === 'super_admin' ||
               ($user->role === 'admin_entreprise' && $user->id === $entreprise->user_id);
    }

    /**
     * Détermine si l'utilisateur peut supprimer une entreprise.
     * Un admin d'entreprise ne peut supprimer que sa propre entreprise.
     */
    public function delete(User $user, Entreprise $entreprise): bool
    {
        return $user->role === 'super_admin' ||
               ($user->role === 'admin_entreprise' && $user->id === $entreprise->user_id);
    }
}
