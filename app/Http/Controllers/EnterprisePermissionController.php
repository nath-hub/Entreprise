<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\EnterprisePermissionService;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;

class EnterprisePermissionController extends Controller
{
    protected $permissionService;

    public function __construct(EnterprisePermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }



    /**
     * @OA\Post(
     *     path="/permissions/assign",
     *     summary="Assigner des permissions à un utilisateur d'entreprise",
     *     tags={"Permissions"},
     *     operationId="assignPermissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"user_email", "permissions"},
     *                 @OA\Property(
     *                     property="user_email",
     *                     type="string",
     *                     format="email",
     *                     example="utilisateur@entreprise.com",
     *                     description="Email de l'utilisateur à qui on assigne les permissions"
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="string",
     *                     example="voir_transactions,modifier_profil",
     *                     description="Permission à assigner (séparées par des virgules si multiples)",
     *                     enum={"can_create_transaction","can_view_reports","all_permissions"} 
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions assignées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions assignées avec succès"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="PERMISSIONS_ASSIGNED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Aucune permission n'a été assignée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Aucune permission n'a été assignée"),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="code", type="string", example="NO_PERMISSIONS_ASSIGNED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Erreur lors de l'assignation des permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Exception message ici"),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSIONS_ASSIGNMENT_FAILED")
     *         )
     *     )
     * )
     */

    public function assignPermissions(Request $request)
    {
        $request->validate([
            'user_email' => 'required|exists:users,email',
            'permissions' => 'string|in:' . implode(',', EnterprisePermissionService::AVAILABLE_PERMISSIONS)
        ]);

        try {
            $result = $this->permissionService->assignPermissionsToUser(
                auth()->id(),
                $request->user_id,
                $request->permissions
            );
            if (!$result) {
                return response()->json([
                    'message' => 'Aucune permission n\'a été assignée',
                    'success' => false,
                    'status' => '400',
                    'code' => 'NO_PERMISSIONS_ASSIGNED'
                ], 400);
            }
            return response()->json([
                'message' => 'Permissions assignées avec succès',
                'success' => true,
                'status' => '200',
                'code' => 'PERMISSIONS_ASSIGNED'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
                'status' => '403',
                'code' => 'PERMISSIONS_ASSIGNMENT_FAILED'
            ], 403);
        }
    }

    /**
     * @OA\Post(
     *     path="/permissions/revoke",
     *     summary="Révoquer des permissions à un utilisateur d'entreprise",
     *     tags={"Permissions"},
     *     operationId="revokePermissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"user_email", "permissions"},
     *                 @OA\Property(
     *                     property="user_email",
     *                     type="string",
     *                     format="email",
     *                     example="utilisateur@entreprise.com",
     *                     description="Email de l'utilisateur dont on souhaite retirer des permissions"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions révoquées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions révoquées avec succès"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="PERMISSIONS_REVOKED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Aucune permission n'a été révoquée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Aucune permission n'a été révoquée"),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="code", type="string", example="NO_PERMISSIONS_REVOKED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Erreur lors de la révocation des permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Exception message ici"),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSIONS_REVOKE_FAILED")
     *         )
     *     )
     * )
     */

    public function revokePermissions(Request $request)
    {
        $request->validate([
            'user_email' => 'required|exists:users,email',
        ]);

        $user = User::where('email', $request->user_email)->first();

        try {
            $result = $this->permissionService->revokePermissionsFromUser(
                auth()->id(),
                $user,
            );

            if (!$result) {
                return response()->json([
                    'message' => 'Aucune permission n\'a été révoquée',
                    'success' => false,
                    'status' => '400',
                    'code' => 'NO_PERMISSIONS_REVOKED'
                ], 400);
            }

            return response()->json([
                'message' => 'Permissions révoquées avec succès',
                'success' => true,
                'status' => '200',
                'code' => 'PERMISSIONS_REVOKED'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
                'status' => '403',
                'code' => 'PERMISSIONS_REVOKE_FAILED'
            ], 403);
        }
    }

    /**
     * @OA\Get(
     *     path="/enterprise_users",
     *     summary="Lister les utilisateurs d'une entreprise avec leurs permissions",
     *     tags={"Permissions"},
     *     operationId="listEnterpriseUsers",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                 @OA\Property(property="email", type="string", format="email", example="jean@exemple.com"),
     *                 @OA\Property(property="role", type="string", example="operateur_entreprise"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="voir_transactions"))
     *             )),
     *             @OA\Property(
     *                 property="available_permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="voir_transactions")
     *             ),
     *             @OA\Property(property="status", type="string", example="200"),
     *             @OA\Property(property="code", type="string", example="USERS_LISTED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="L'utilisateur authentifié n'est pas autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="string", example="403"),
     *             @OA\Property(property="code", type="string", example="ACCESS_DENIED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune entreprise trouvée pour l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Aucune entreprise trouvée pour cet administrateur."),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="string", example="404"),
     *             @OA\Property(property="code", type="string", example="ENTERPRISE_NOT_FOUND")
     *         )
     *     )
     * )
     */

    public function listEnterpriseUsers()
    {
        $user = auth()->user();

        if ($user->role !== 'admin_entreprise') {
            return response()->json([
                'message' => 'Accès refusé',
                'success' => false,
                'status' => '403',
                'code' => 'ACCESS_DENIED'
            ], 403);
        }

        $enterprise = Entreprise::where('user_id', $user->id)->first();

        if (!$enterprise) {
            return response()->json([
                'message' => 'Aucune entreprise trouvée pour cet administrateur.',
                'success' => false,
                'status' => '404',
                'code' => 'ENTERPRISE_NOT_FOUND'
            ], 404);
        }

        $users = $this->permissionService->getEnterpriseUsersWithPermissions($enterprise->id);

        return response()->json([
            'users' => $users,
            'available_permissions' => EnterprisePermissionService::AVAILABLE_PERMISSIONS,
            'status' => '200',
            'code' => 'USERS_LISTED'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/my-permissions",
     *     summary="Afficher les permissions de l'utilisateur connecté",
     *     tags={"Permissions"},
     *     operationId="myPermissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permissions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=7),
     *             @OA\Property(property="role", type="string", example="operateur_entreprise"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="voir_transactions"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function myPermissions()
    {
        $permissions = $this->permissionService->getUserPermissions(auth()->id());

        return response()->json([
            'user_id' => auth()->id(),
            'role' => auth()->user()->role,
            'permissions' => $permissions
        ]);
    }
}
