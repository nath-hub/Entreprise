<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperatorController extends Controller
{

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
        // $user = auth()->user();

        // if ($user->role !== 'super_admin') {
        //     return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        // }

        return Operator::with('country')->get();
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
     *                     required={"name", "code", "country_id", "commission_rate", "is_active"},
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="country_code", type="string"),
     *                     @OA\Property(property="api_endpoint", type="string"),
     *                     @OA\Property(property="commission_rate", type="number"),
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
            'country_code' => 'required|string',
            'api_endpoint' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:1',
            'is_active' => 'nullable',
        ]);

        // Normaliser is_active pour multipart/form-data
        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $validated['is_active'] = true; // Valeur par défaut
        }

        // Vérifier manuellement que le country_id existe dans les deux bases
        $countryExistsInSandbox = \App\Models\Country::on('mysql_sandbox')->where('code', $validated['country_code'])->first();
        $countryExistsInProd = \App\Models\Country::on('mysql_prod')->where('code', $validated['country_code'])->first();
         
        if (!$countryExistsInSandbox || !$countryExistsInProd) {
            return response()->json([
                'message' => 'Le pays spécifié doit exister dans les deux environnements (sandbox et production)',
                'details' => [
                    'exists_in_sandbox' => $countryExistsInSandbox,
                    'exists_in_prod' => $countryExistsInProd
                ]
            ], 422);
        }

        $validatedSandbox = $validated;
        $validatedProd = $validated;

        $validatedSandbox['id'] = (string) Str::uuid();
        $validatedProd['id'] = (string) Str::uuid();
 
        $validatedSandbox['country_id'] = $countryExistsInSandbox->id;
        $validatedProd['country_id'] = $countryExistsInProd->id;

        // Créer l'opérateur dans mysql_sandbox
        $operatorSandbox = Operator::on('mysql_sandbox')->create($validatedSandbox);

        // Créer l'opérateur dans mysql_prod
        $operatorProd = Operator::on('mysql_prod')->create($validatedProd);

        return response()->json($operatorSandbox, 201);
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

        // $user = auth()->user();

        // if ($user->role !== 'super_admin') {
        //     return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        // }

        $operator = Operator::with('country')->find($id);

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

        $operator = Operator::with('country')->where('code', $code)->where('is_active', true)->first();

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

        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'code' => 'sometimes|string|max:20',
            'country_id' => 'sometimes|string|uuid',
            'api_endpoint' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:1',
            'is_active' => 'boolean',
        ]);

        // Vérifier manuellement que le country_id existe dans au moins une des bases
        $countryExists = \App\Models\Country::on('mysql_sandbox')->where('id', $validated['country_id'])->exists() ||
            \App\Models\Country::on('mysql_prod')->where('id', $validated['country_id'])->exists();

        if (!$countryExists) {
            return response()->json(['message' => 'Le pays spécifié n\'existe pas'], 422);
        }

        // Chercher l'opérateur dans mysql_sandbox
        $operatorSandbox = Operator::on('mysql_sandbox')->find($id);

        // Chercher l'opérateur dans mysql_prod
        $operatorProd = Operator::on('mysql_prod')->find($id);

        if ($operatorSandbox) {
            // Mettre à jour dans mysql_sandbox
            $operatorSandbox->update($validated);

            // Chercher l'opérateur correspondant dans mysql_prod par code si les IDs diffèrent
            $operatorProdMatch = Operator::on('mysql_prod')
                ->where('code', $operatorSandbox->code)
                ->first();

            if ($operatorProdMatch) {
                $operatorProdMatch->update($validated);
            }
        } elseif ($operatorProd) {
            // Mettre à jour dans mysql_prod
            $operatorProd->update($validated);

            // Chercher l'opérateur correspondant dans mysql_sandbox par code
            $operatorSandboxMatch = Operator::on('mysql_sandbox')
                ->where('code', $operatorProd->code)
                ->first();

            if ($operatorSandboxMatch) {
                $operatorSandboxMatch->update($validated);
            }
        } else {
            return response()->json(['message' => 'Opérateur introuvable'], 404);
        }

        return response()->json($operatorSandbox ?: $operatorProd);
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
        $user = auth()->user();

        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        // Chercher l'opérateur dans mysql_sandbox
        $operatorSandbox = Operator::on('mysql_sandbox')->find($id);

        // Chercher l'opérateur dans mysql_prod
        $operatorProd = Operator::on('mysql_prod')->find($id);

        if ($operatorSandbox) {
            // Chercher l'opérateur correspondant dans mysql_prod par code
            $operatorProdMatch = Operator::on('mysql_prod')
                ->where('code', $operatorSandbox->code)
                ->first();

            if ($operatorProdMatch) {
                $operatorProdMatch->delete();
            }

            // Supprimer de mysql_sandbox
            $operatorSandbox->delete();
        } elseif ($operatorProd) {
            // Chercher l'opérateur correspondant dans mysql_sandbox par code
            $operatorSandboxMatch = Operator::on('mysql_sandbox')
                ->where('code', $operatorProd->code)
                ->first();

            if ($operatorSandboxMatch) {
                $operatorSandboxMatch->delete();
            }

            // Supprimer de mysql_prod
            $operatorProd->delete();
        } else {
            return response()->json(['message' => 'Opérateur introuvable'], 404);
        }

        return response()->json(['message' => 'Opérateur supprimé avec succès']);
    }
}
