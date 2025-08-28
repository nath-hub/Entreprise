<?php

namespace App\Http\Controllers;

use App\Helpers\InternalHttpClient;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MultiDatabaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    use MultiDatabaseTrait;
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new InternalHttpClient();
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Utilisateurs"},
     *     summary="Créer un nouvel utilisateur",
     *     description="Crée un utilisateur avec les informations fournies (JSON ou form-data).",
     *
     *        @OA\RequestBody(
     *            required=true,
     *            description="Données en JSON ou formulaire",
     *            @OA\MediaType(
     *                mediaType="application/json",
     *                @OA\Schema(
     *                    required={"name", "email", "telephone", "password", "role"},
     *                    @OA\Property(property="name", type="string", example="nath"),
     *                    @OA\Property(property="email", type="string", example="nath@example.com"),
     *                    @OA\Property(property="telephone", type="string", example="677852729"),
     *                    @OA\Property(property="password", type="string", format="password", example="motdepasse123"),
     *                    @OA\Property(
     *                        property="role",
     *                        type="string",
     *                        enum={"super_admin", "admin_entreprise", "operateur_entreprise", "consultant_entreprise"},
     *                        example="admin_entreprise"
     *                    )
     *                )
     *            ),
     *            @OA\MediaType(
     *                mediaType="multipart/form-data",
     *                @OA\Schema(
     *                    type="object",
     *                    required={"name", "email", "telephone", "password", "role"},
     *                    @OA\Property(property="name", type="string", example="nath"),
     *                    @OA\Property(property="email", type="string", example="nath@example.com"),
     *                    @OA\Property(property="telephone", type="string", example="677852729"),
     *                    @OA\Property(property="password", type="string", format="password", example="motdepasse123"),
     *                    @OA\Property(
     *                        property="role",
     *                        type="string",
     *                        enum={"super_admin", "admin_entreprise", "operateur_entreprise", "consultant_entreprise"},
     *                        example="admin_entreprise"
     *                    )
     *                )
     *            )
     *        ),

     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Utilisateur enregistré avec succès.")
     *         )
     *     ),

     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="Erreur de validation"),
     *             @OA\Property(property="message", type="string", example="Les données envoyées ne sont pas valides.")
     *         )
     *     ),

     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="Non authentifié"),
     *             @OA\Property(property="message", type="string", example="Jeton manquant ou invalide.")
     *         )
     *     ),

     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="Erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur s’est produite.")
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'telephone' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'in:super_admin,admin_entreprise,operateur_entreprise,consultant_entreprise',
        ]);

        $verification_code = Str::random(6);

        $currentDateTime = Carbon::now();

        $resetPasswordCode = $currentDateTime->addHour();

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'operateur_entreprise',
            'permissions' => 'all_permissions',
            'verification_code' => $verification_code,
            'reset_password_expires_at' => $resetPasswordCode,
            'environment' => 'sandbox', // par défaut
        ];

        $user = $this->executeOnBothDatabases(function ($connection, $data) {
            $user = new User();
            $user->setConnection($connection);
            $user->fill($data);
            $user->save();
            return $user;
        }, $userData);

        $user = (object) $user;

        // 🔔 Notification (après création)
        $authServiceUrl = config('services.services_notifications.url');
        $httpClient = new InternalHttpClient();
        $httpClient->post($request, $authServiceUrl, 'api/send-verification-code', ['id' => $user->mysql_sandbox->id], ['read:users']);

        return response()->json([
            'status' => 201,
            'user_id' => $user->mysql_sandbox->id,
        ], 201);
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentification"},
     *     summary="Connexion utilisateur",
     *     description="Permet à un utilisateur de se connecter avec son email et son mot de passe.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", format="email", example="utilisateur@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="motdepasse123")
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", format="email", example="utilisateur@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="motdepasse123")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Connexion réussie."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Échec d’authentification",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="non authentifié"),
     *             @OA\Property(property="message", type="string", example="Identifiants incorrects.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="erreur de validation"),
     *             @OA\Property(property="message", type="string", example="Les champs email et mot de passe sont requis.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue sur le serveur.")
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Authentification multi-DB
        $user = $this->authenticateUser($email, $password);

        if (!$user) {
            return response()->json([
                'message' => 'Identifiants invalides',
                'status' => 401,
            ], 401);
        }

        // Vérification email
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Veuillez vérifier votre adresse email avant de vous connecter.',
                'status' => 403,
                'field' => 'email_verification'
            ], 403);
        }

        // Première connexion → notifier
        if ($user->date_derniere_connexion == null) {
            $authServiceUrl = config('services.services_notifications.url');

            $httpClient = new InternalHttpClient();
            $httpClient->post(
                $request,
                $authServiceUrl,
                'api/notifications/welcome',
                ['id' => $user->id],
                ['read:users']
            );
        }

        // Génération du token
        $token = $user->createToken('API Token')->plainTextToken;

        // Mise à jour du statut utilisateur
        $user->date_derniere_connexion = now();
        $user->statut = 'actif';
        $user->save();

        return response()->json([
            'message' => 'Connexion réussie.',
            'status' => 200,
            'token_type' => 'Bearer',
            'token' => $token,
            'environment' => $user->environment ?? null,
        ], 200);
    }


    protected function authenticateUser($email, $password)
    {
        $connections = ['mysql_prod', 'mysql_sandbox'];

        foreach ($connections as $connection) {
            $user = User::on($connection)->where('email', $email)->first();

            if ($user && Hash::check($password, $user->password)) {
                // Ajouter info sur l’environnement
                $user->environment = $connection === 'mysql_prod' ? 'prod' : 'sandbox';
                return $user;
            }
        }

        return null;
    }


    /**
     * @OA\Get(
     *     path="/api/token/users",
     *     tags={"Authentification"},
     *     summary="Profil de l'utilisateur connecté",
     *     description="Retourne les informations de l'utilisateur authentifié à partir du token.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Données de l'utilisateur récupérées avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Utilisateur connecté récupéré."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="84d8c8e2-dea9-4381-bcc8-8d0ecd24eb2b"),
     *                 @OA\Property(property="name", type="string", example="nath"),
     *                 @OA\Property(property="email", type="string", example="floretaffot1@gmail.com"),
     *                 @OA\Property(property="telephone", type="string", example="677852729"),
     *                 @OA\Property(property="role", type="string", example="super_admin"),
     *                 @OA\Property(property="permissions", type="string", example="all_permissions"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="langue_preferee", type="string", example="fr"),
     *                 @OA\Property(property="preferences_notifications", type="string", example="notifications"),
     *                 @OA\Property(property="photo_profil_url", type="string", nullable=true, example="https://example.com/image.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-26T16:32:11.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-01T09:46:59.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="non authentifié"),
     *             @OA\Property(property="message", type="string", example="Token manquant ou invalide.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue.")
     *         )
     *     )
     * )
     */

    public function showByToken(Request $request)
    {

        $me = $request->user();

        return response()->json($me);
    }



    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentification"},
     *     summary="Déconnexion",
     *     description="Déconnecte l'utilisateur et invalide son token d'authentification.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="non authentifié"),
     *             @OA\Property(property="message", type="string", example="Token manquant ou invalide.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la déconnexion.")
     *         )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie', 'status' => 200], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/verify_code",
     *     tags={"Authentification"},
     *     summary="Vérifier le code de confirmation",
     *     description="Vérifie le code de vérification envoyé à l'adresse email de l'utilisateur et active son compte.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "code"},
     *                 @OA\Property(property="email", type="string", format="email", example="utilisateur@example.com"),
     *                 @OA\Property(property="code", type="string", example="123456")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Compte vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Compte verifier avec succes"),
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJK..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user_id", type="string", format="uuid", example="84d8c8e2-dea9-4381-bcc8-8d0ecd24eb2b"),
     *             @OA\Property(property="user_name", type="string", example="nath"),
     *             @OA\Property(property="role", type="string", example="super_admin")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Code invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=401),
     *             @OA\Property(property="code", type="string", example="INVALID_VERIFICATION_CODE"),
     *             @OA\Property(property="message", type="string", example="code de verification invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="erreur de validation"),
     *             @OA\Property(property="message", type="string", example="L’email et le code sont obligatoires.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue.")
     *         )
     *     )
     * )
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->verification_code != $request->code) {
            return response()->json([
                'status' => 401,
                'code' => 'INVALID_VERIFICATION_CODE',
                'message' => 'code de verification invalide'
            ], 401);
        }

        $authServiceUrl = config('services.services_notifications.url');

        $httpClient = new InternalHttpClient();

        $httpClient->post($request, $authServiceUrl, 'api/send-account-verified-success', ['id' => $user->id], ['read:users']);


        $user->verification_code = null;
        $user->email_verified_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Compte verifier avec succes',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     tags={"Authentification"},
     *     summary="Vérifier le code de confirmation",
     *     description="Vérifie le code de vérification envoyé à l'adresse email de l'utilisateur et active son compte.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "code"},
     *                 @OA\Property(property="email", type="string", format="email", example="utilisateur@example.com"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Compte vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Un lien de réinitialisation vous a été envoyé."),
     *             @OA\Property(property="code", type="string", example="RESET_LINK_SENT"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Code invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=401),
     *             @OA\Property(property="code", type="string", example="INVALID_VERIFICATION_CODE"),
     *             @OA\Property(property="message", type="string", example="code de verification invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="erreur de validation"),
     *             @OA\Property(property="message", type="string", example="L’email et le code sont obligatoires.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue.")
     *         )
     *     )
     * )
     */

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "Le format de l'adresse email est invalide."
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            $user->email = $request->email;
            $user->verification_code = Str::random(6);
            $user->save();

            $authServiceUrl = config('services.services_notifications.url');

            $httpClient = new InternalHttpClient();

            $httpClient->post($request, $authServiceUrl, 'api/send-verification-code', ['id' => $user->id], ['read:users']);


            return response()->json([
                "message" => "Un lien de réinitialisation vous a été envoyé.",
                "statut" => 200,
                "code" => "RESET_LINK_SENT",
            ], 200);
        } elseif (is_null($user)) {
            return response()->json([
                "message" => "Nous ne pouvons pas trouver d'utilisateur avec cette adresse e-mail.",
                "code" => "USER_NOT_FOUND",
                "statut" => 404
            ], 404); // Retourne 404 Not Found
        }

        return response()->json([
            "message" => "Impossible d'envoyer le lien de réinitialisation de mot de passe pour le moment. Veuillez réessayer plus tard.",
            "code" => "RESET_LINK_FAILED"
        ], 500);
    }


    /**
     * @OA\Post(
     *     path="/password/update",
     *     tags={"Authentification"},
     *     summary="Met à jour le mot de passe d'un utilisateur",
     *     description="Permet de réinitialiser le mot de passe d'un utilisateur via son email.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Adresse email de l'utilisateur"),
     *             @OA\Property(property="password", type="string", minLength=8, example="newpassword123", description="Nouveau mot de passe (min 8 caractères)")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Your password has been reset.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nous ne pouvons pas trouver d'utilisateur avec cette adresse e-mail."),
     *             @OA\Property(property="code", type="string", example="USER_NOT_FOUND"),
     *             @OA\Property(property="statut", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"The email field is required."},
     *                     "password": {"The password must be at least 8 characters."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "message" => "Nous ne pouvons pas trouver d'utilisateur avec cette adresse e-mail.",
                "code" => "USER_NOT_FOUND",
                "statut" => 404
            ], 404);
        }
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        $authServiceUrl = config('services.services_notifications.url');

        $httpClient = new InternalHttpClient();

        $httpClient->post($request, $authServiceUrl, 'api/send-password-reset-success', ['id' => $user->id], ['read:users']);


        return response()->json(["statut" => 200, 'message' => 'Your password has been reset.'], 200);
    }

    /**
     * @OA\Post(
     *     path="/change_password",
     *     tags={"Authentification"},
     *     summary="Change le mot de passe de l'utilisateur connecté",
     *     description="Permet à un utilisateur authentifié de changer son mot de passe en fournissant l'ancien et le nouveau mot de passe.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password","new_password"},
     *             @OA\Property(property="old_password", type="string", format="password", example="oldpass123", description="L'ancien mot de passe"),
     *             @OA\Property(property="new_password", type="string", minLength=8, format="password", example="newpass1234", description="Le nouveau mot de passe (min 8 caractères)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe changé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="integer", example=200),
     *             @OA\Property(property="code", type="string", example="PASS_CHANGED_SUCCESS"),
     *             @OA\Property(property="message", type="string", example="Votre mot de passe a été changé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Ancien mot de passe incorrect ou utilisateur non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="L'ancien mot de passe est incorrect."),
     *             @OA\Property(property="code", type="string", example="PASS_CHANGED_FAILED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation des données",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "old_password": {"The old password field is required."},
     *                     "new_password": {"The new password must be at least 8 characters."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        $user = $request->user();

        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return response()->json(['statut' => 403, 'message' => "L'ancien mot de passe est incorrect.", "code" => "PASS_CHANGED_FAILED"], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $authServiceUrl = config('services.services_notifications.url');

        $httpClient = new InternalHttpClient();

        $httpClient->post($request, $authServiceUrl, 'api/send-password-reset-success', ['id' => $user->id], ['read:users']);


        return response()->json(['statut' => 200, 'code' => 'PASS_CHANGED_SUCCESS', 'message' => 'Votre mot de passe a été changé avec succès.'], 200);
    }
}
