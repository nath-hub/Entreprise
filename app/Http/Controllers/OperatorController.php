<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\User;
use App\Traits\MultiDatabaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperatorController extends Controller
{
    use MultiDatabaseTrait;

    /**
     * @OA\Get(
     *     path="/api/operators",
     *     tags={"Operators"},
     *     summary="Lister tous les opérateurs",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste des opérateurs"),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Requête invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSION_DENIED")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $connection = $this->getDatabaseConnection();

        return Operator::on($connection)->with('country')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/operators",
     *     tags={"Operators"},
     *     summary="Créer un opérateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     required={"name", "code", "country_id"},
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="country_id", type="integer"),
     *                     @OA\Property(property="api_endpoint", type="string"),
     *                     @OA\Property(property="commission_rate", type="number", format="float"),
     *                     @OA\Property(property="is_active", type="boolean")
     *                 )
     *             ),
     *             @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="country_id", type="integer"),
     *                 @OA\Property(property="api_endpoint", type="string"),
     *                 @OA\Property(property="commission_rate", type="number"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *         }
     *     ),
     *     @OA\Response(response=201, description="Opérateur créé"),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Requête invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSION_DENIED")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:20',
            'country_id' => 'required|exists:countries,id',
            'api_endpoint' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $operator = $this->executeOnBothDatabases(function ($connection, $data) {
            $operator = new Operator();
            $operator->setConnection($connection);
            $operator->fill($data);
            $operator->save();
            return $operator;
        }, $validated);

        $operator = (object) $operator;

        return response()->json($operator, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/operators/{id}",
     *     tags={"Operators"},
     *     summary="Voir un opérateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détails de l'opérateur"),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Requête invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSION_DENIED")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $connection = $this->getDatabaseConnection();

        $operator = Operator::on($connection)->with('country')->find($id);

        if (!$operator) {
            return response()->json(['message' => 'Opérateur introuvable'], 404);
        }

        return $operator;
    }


    /**
     * @OA\Get(
     *     path="/api/operators/code/{code}",
     *     tags={"Operators"},
     *     summary="Voir un opérateur via son code",
     *     @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détails de l'opérateur"),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Requête invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSION_DENIED")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
     *         )
     *     )
     * )
     */
    public function showCountryByCode($code)
    {
        $connection = $this->getDatabaseConnection();

        $operator = Operator::on($connection)->with('country')->where('code', $code)->where('is_active', true)->first();

        if (!$operator) {
            return response()->json(['message' => 'Opérateur introuvable'], 404);
        }

        return $operator;
    }

    /**
     * @OA\Put(
     *     path="/api/operators/{id}",
     *     tags={"Operators"},
     *     summary="Mettre à jour un opérateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="country_id", type="integer"),
     *                     @OA\Property(property="api_endpoint", type="string"),
     *                     @OA\Property(property="commission_rate", type="number", format="float"),
     *                     @OA\Property(property="is_active", type="boolean")
     *                 )
     *             ),
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="country_id", type="integer"),
     *                 @OA\Property(property="api_endpoint", type="string"),
     *                 @OA\Property(property="commission_rate", type="number", format="float"),
     *                 @OA\Property(property="is_active", type="boolean"),
     *             )
     *         )
     *         }
     *     ),
     *     @OA\Response(response=200, description="Opérateur mis à jour"),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Requête invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSION_DENIED")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $connection = $this->getDatabaseConnection();
        $userId = auth()->id(); // id de l’utilisateur connecté dans la session/token

        $user = User::on($connection)->find($userId);

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        $operator = Operator::on($connection)->find($id);

        if (!$operator) {
            return response()->json(['message' => 'Opérateur introuvable'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'code' => 'sometimes|string|max:20',
            'country_id' => 'sometimes|exists:countries,id',
            'api_endpoint' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:1',
            'is_active' => 'boolean',
        ]);

        $operator->update($validated);

        return response()->json($operator);
    }

    /**
     * @OA\Delete(
     *     path="/api/operators/{id}",
     *     tags={"Operators"},
     *     summary="Supprimer un opérateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Supprimé"),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Requête invalide")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="code", type="string", example="PERMISSION_DENIED")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $connection = $this->getDatabaseConnection();
        $userId = auth()->id(); // id de l’utilisateur connecté dans la session/token

        $user = User::on($connection)->find($userId);

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        $operator = Operator::on($connection)->find($id);

        if (!$operator) {
            return response()->json(['message' => 'Opérateur introuvable'], 404);
        }

        $operator->delete();

        return response()->json(['message' => 'Opérateur supprimé avec succès']);
    }
}
