<?php

namespace App\Http\Services;

use App\Models\User;

class EnterprisePermissionService
{
    /**
     * Permissions disponibles dans le système
     */
    const AVAILABLE_PERMISSIONS = [
        'can_create_transaction',
        'can_view_reports',
        'all_permissions'
    ];

    /**
     * Assigne des permissions à un utilisateur d'entreprise
     */
    public function assignPermissionsToUser($adminUserId, $targetUserEmail, string $permissions)
    {
        $admin = User::findOrFail($adminUserId);
        $targetUser = User::findOrFail($targetUserEmail);

        // Vérifier que l'admin a le droit d'assigner des permissions
        if (!$this->canAssignPermissions($admin, $targetUser)) {
            return false; // ou lancer une exception
        }

        // Valider les permissions
        $permission = $this->validatePermissions($permissions);

        $targetUser->update([
            'permission' => $permission,
            'enterprise_id' => $admin->enterprise_id,
            'granted_by' => $adminUserId
        ]);

        return true;
    }

    /**
     * Révoque des permissions d'un utilisateur
     */
    public function revokePermissionsFromUser($adminUserId, $targetUser)
    {
        $admin = User::findOrFail($adminUserId);

        if (!$this->canAssignPermissions($admin, $targetUser)) {
            return false; // ou lancer une exception
        }

        $targetUser->update([
            'permission' => null,
            'enterprise_id' => $admin->enterprise_id,
            'granted_by' => $adminUserId
        ]);

        return true;
    }

    /**
     * Vérifie si un admin peut assigner des permissions à un utilisateur
     */
    public function canAssignPermissions($admin, $targetUser)
    {
        // L'admin doit avoir le rôle admin_entreprise
        if ($admin->role !== 'admin_entreprise') {
            return false;
        }

        // L'utilisateur cible doit être dans la même entreprise
        if ($admin->enterprise_id !== $targetUser->enterprise_id) {
            return false;
        }

        // L'utilisateur cible doit avoir un rôle autorisé
        $allowedRoles = ['operateur_entreprise', 'consultant_entreprise'];
        if (!in_array($targetUser->role, $allowedRoles)) {
            return false;
        }

        return true;
    }

    /**
     * Valide les permissions demandées
     */
    private function validatePermissions(string $permission)
    {
        if (in_array($permission, self::AVAILABLE_PERMISSIONS)) {
            return [$permission]; // valide, on retourne sous forme de tableau
        }

        return [];
    }

    /**
     * Récupère toutes les permissions d'un utilisateur dans son entreprise
     */
    public function getUserPermissions($userId)
    {
        $user_permissions = User::findOrFail($userId)->pluck('permission');
 
        return $user_permissions;
    }

    

    /**
     * Récupère tous les utilisateurs d'une entreprise avec leurs permissions
     */
    public function getEnterpriseUsersWithPermissions($enterpriseId)
    {
        return User::whereHas('permissions', function ($query) use ($enterpriseId) {
            $query->where('enterprise_id', $enterpriseId);
        })
        ->whereIn('role', ['operateur_entreprise', 'consultant_entreprise'])
        ->with(['permissions' => function ($query) use ($enterpriseId) {
            $query->where('enterprise_id', $enterpriseId);
        }])
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions->pluck('permission')->toArray()
            ];
        });
    }

  
 
}
