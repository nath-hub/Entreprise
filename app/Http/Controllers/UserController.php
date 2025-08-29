<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Utilisateurs"},
     *     summary="Liste des utilisateurs",
     *     description="Récupère la liste paginée des utilisateurs.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid", example="84d8c8e2-dea9-4381-bcc8-8d0ecd24eb2b"),
     *                 @OA\Property(property="name", type="string", example="nath"),
     *                 @OA\Property(property="email", type="string", example="floretaffot1@gmail.com"),
     *                 @OA\Property(property="telephone", type="string", example="677852729"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-06-26T17:32:23.000000Z"),
     *                 @OA\Property(property="role", type="string", example="super_admin"),
     *                 @OA\Property(property="permissions", type="string", example="all_permissions"),
     *                 @OA\Property(property="reset_password_token", type="string", nullable=true, example=null),
     *                 @OA\Property(property="reset_password_expires_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="date_derniere_connexion", type="string", example="2025-07-01 09:46:59"),
     *                 @OA\Property(property="statut", type="string", example="inactif"),
     *                 @OA\Property(property="langue_preferee", type="string", example="fr"),
     *                 @OA\Property(property="preferences_notifications", type="string", example="notifications"),
     *                 @OA\Property(property="photo_profil_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-26T16:32:11.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-01T09:46:59.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=401),
     *             @OA\Property(property="status", type="string", example="Non authentifié"),
     *             @OA\Property(property="message", type="string", example="L’utilisateur n’est pas authentifié ou le jeton est invalide.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=403),
     *             @OA\Property(property="status", type="string", example="Accès refusé"),
     *             @OA\Property(property="message", type="string", example="Vous n’avez pas les autorisations nécessaires pour accéder à cette ressource.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=422),
     *             @OA\Property(property="status", type="string", example="Erreur de validation"),
     *             @OA\Property(property="message", type="string", example="Les données envoyées ne sont pas valides."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Le champ email est obligatoire.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=500),
     *             @OA\Property(property="status", type="string", example="Erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur inattendue s’est produite sur le serveur.")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {

        $user = $request->user();

        if (
            $user->statut !== 'actif' ||
            $user->role !== 'super_admin' ||
            $user->permissions !== 'all_permissions'
        ) {
            return response()->json([
                'status' => 403,
                'message' => 'Accès refusé : vous n’avez pas les autorisations requises.',
            ], 403);
        }

        return response()->json(User::all());
    }


    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Utilisateurs"},
     *     summary="Détails d’un utilisateur",
     *     description="Récupère les informations d’un utilisateur à partir de son identifiant.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Identifiant UUID de l’utilisateur",
     *         @OA\Schema(type="string", format="uuid", example="84d8c8e2-dea9-4381-bcc8-8d0ecd24eb2b")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur récupéré avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid", example="84d8c8e2-dea9-4381-bcc8-8d0ecd24eb2b"),
     *             @OA\Property(property="name", type="string", example="nath"),
     *             @OA\Property(property="email", type="string", example="floretaffot1@gmail.com"),
     *             @OA\Property(property="telephone", type="string", example="677852729"),
     *             @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-06-26T17:32:23.000000Z"),
     *             @OA\Property(property="role", type="string", example="super_admin"),
     *             @OA\Property(property="permissions", type="string", example="all_permissions"),
     *             @OA\Property(property="reset_password_token", type="string", nullable=true, example=null),
     *             @OA\Property(property="reset_password_expires_at", type="string", nullable=true, example=null),
     *             @OA\Property(property="date_derniere_connexion", type="string", example="2025-07-01 09:46:59"),
     *             @OA\Property(property="statut", type="string", example="inactif"),
     *             @OA\Property(property="langue_preferee", type="string", example="fr"),
     *             @OA\Property(property="preferences_notifications", type="string", example="notifications"),
     *             @OA\Property(property="photo_profil_url", type="string", nullable=true, example=null),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-26T16:32:11.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-01T09:46:59.000000Z")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=401),
     *             @OA\Property(property="status", type="string", example="Non authentifié"),
     *             @OA\Property(property="message", type="string", example="L’utilisateur n’est pas authentifié ou le jeton est invalide.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=403),
     *             @OA\Property(property="status", type="string", example="Accès refusé"),
     *             @OA\Property(property="message", type="string", example="Vous n’avez pas les autorisations nécessaires pour accéder à cette ressource.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur introuvable.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=404),
     *             @OA\Property(property="status", type="string", example="Non trouvé"),
     *             @OA\Property(property="message", type="string", example="Aucun utilisateur trouvé avec cet identifiant.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="number", example=500),
     *             @OA\Property(property="status", type="string", example="Erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur inattendue s’est produite sur le serveur.")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }


    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Utilisateurs"},
     *     summary="Modifier un utilisateur",
     *     description="Met à jour les informations d’un utilisateur. Les données sont envoyées en multipart/form-data.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur à modifier",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Nath modifié"),
     *                 @OA\Property(property="email", type="string", format="email", example="modifie@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="690000000"),
     *                 @OA\Property(
     *                     property="langue_preferee",
     *                     type="string",
     *                     enum={"fr", "en"},
     *                     example="fr"
     *                 ),
     *                 @OA\Property(
     *                     property="preferences_notifications",
     *                     type="string",
     *                     enum={"email_marketing", "notifications"},
     *                     example="notifications"
     *                 ),
     *                 @OA\Property(
     *                     property="photo_profil",
     *                     type="string",
     *                     format="binary",
     *                     description="Fichier image JPEG, PNG ou JPG"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Utilisateur mis à jour avec succès."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="Erreur de validation"),
     *             @OA\Property(property="message", type="string", example="Les données envoyées ne sont pas valides.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="Non authentifié"),
     *             @OA\Property(property="message", type="string", example="Jeton manquant ou invalide.")
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'telephone' => 'required|string|max:20|unique:users,telephone,' . $id,
            'langue_preferee' => 'required|in:fr,en',
            'preferences_notifications' => 'required|in:email_marketing,notifications',
            'photo_profil' => 'nullable|file|mimes:jpg,jpeg,png|max:3048',
        ]);

        $user = User::findOrFail($id);

        $data = $request->only([
            'name',
            'email',
            'telephone',
            'role',
            'permissions',
            'statut',
            'langue_preferee',
            'preferences_notifications',
            'photo_profil'
        ]);

        // Gérer l’upload du fichier photo
        if ($request->hasFile('photo_profil')) {
            $file = $request->file('photo_profil');
            $path = $file->store('photos_profil', 'public'); // stocké dans storage/app/public/photos_profil
            $data['photo_profil_url'] = 'storage/' . $path; // URL publique
        }

        $user->update($data);

        return response()->json([
            'code' => 200,
            'status' => 'succès',
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $user
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Utilisateurs"},
     *     summary="Supprimer un utilisateur",
     *     description="Supprime un utilisateur de la base de données.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Identifiant de l’utilisateur",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur supprimé avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Utilisateur supprimé.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Utilisateur introuvable.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Jeton manquant ou invalide.")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (is_null($user)) {
            return response()->json([
                'message' => 'L\'utilisateur avec l\'ID ' . $id . ' est introuvable.',
                'code' => 'USER_NOT_FOUND',
                'status' => 404,
                'field' => 'id'
            ], 404); // Code d'état HTTP 404 Not Found
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé', 'status' => 200], 200);
    }



    /**
     * @OA\Put(
     *     path="/api/update/status/{id}",
     *     tags={"Utilisateurs"},
     *     summary="Modifier un utilisateur",
     *     description="Met à jour le status d’un utilisateur. Les données sont envoyées en multipart/form-data.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur à modifier",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     enum={"actif", "inactif", "suspendu", "bloque", "en_attente_verification"},
     *                     example="fr"
     *                 ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Utilisateur mis à jour avec succès."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="Erreur de validation"),
     *             @OA\Property(property="message", type="string", example="Les données envoyées ne sont pas valides.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="Non authentifié"),
     *             @OA\Property(property="message", type="string", example="Jeton manquant ou invalide.")
     *         )
     *     )
     * )
     */

    public function updateStatus(Request $request, $id)
    {

        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        $request->validate([
            'status' => 'required|in:actif,inactif,suspendu,bloque,en_attente_verification',
        ]);

        $user = User::findOrFail($id);

        $user->update(['statut' => $request->input('status')]);

        return response()->json([
            'code' => 200,
            'status' => 'succès',
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $user
        ]);
    }

}
