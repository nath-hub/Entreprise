<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;


class CountryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/countries",
     *     summary="Lister tous les pays",
     *     description="Retourne la liste de tous les pays. Accessible uniquement au super_admin.",
     *     operationId="indexCountries",
     *     tags={"Pays"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des pays",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="CM"),
     *                 @OA\Property(property="name", type="string", example="Cameroun"),
     *                 @OA\Property(property="currency_code", type="string", example="XAF"),
     *                 @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-15T14:25:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-15T14:25:00Z")
     *             )
     *         )
     *     ),
     * 
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
       auth()->user();
 
        return Country::all();
    }

    /**
     * @OA\Get(
     *     path="/api/countries/{id}",
     *     summary="Afficher un pays par ID",
     *     description="Retourne les informations d'un pays spécifique. Accessible uniquement au super_admin.",
     *     operationId="showCountry",
     *     tags={"Pays"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du pays à afficher",
     *         @OA\Schema(type="string", example="dkfjfoisofoikvfgvrr")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Détails du pays",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="CM"),
     *             @OA\Property(property="name", type="string", example="Cameroun"),
     *             @OA\Property(property="currency_code", type="string", example="XAF"),
     *             @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-15T14:25:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-15T14:25:00Z")
     *         )
     *     ),
     *
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
     *         description="Pays introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pays introuvable")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
       
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['message' => 'Pays introuvable'], 404);
        }
        return $country;
    }


        /**
     * @OA\Get(
     *     path="/api/countries/code/{code}",
     *     summary="Afficher un pays par CODE",
     *     description="Retourne les informations d'un pays spécifique. Accessible uniquement au super_admin.",
     *     operationId="showCountryByCode",
     *     tags={"Pays"}, 
     * 
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="ID du pays à afficher",
     *         @OA\Schema(type="string", example="dkfjfoisofoikvfgvrr")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Détails du pays",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="CM"),
     *             @OA\Property(property="name", type="string", example="Cameroun"),
     *             @OA\Property(property="currency_code", type="string", example="XAF"),
     *             @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-15T14:25:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-15T14:25:00Z")
     *         )
     *     ),
     *
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
     *         description="Pays introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pays introuvable")
     *         )
     *     )
     * )
     */

    public function showCountryByCode($code)
    {
       
        $country = Country::where('code', $code)->where('is_active', true)->first();
        if (!$country) {
            return response()->json(['message' => 'Pays introuvable'], 404);
        }
        return $country;
    }

    /**
     * @OA\Post(
     *     path="/api/countries",
     *     summary="Créer un pays",
     *     description="Crée un nouveau pays (super_admin uniquement)",
     *     operationId="storeCountry",
     *     tags={"Pays"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     required={"code", "name", "currency_code"},
     *                     @OA\Property(property="code", type="string", example="CM"),
     *                     @OA\Property(property="name", type="string", example="Cameroun"),
     *                     @OA\Property(property="currency_code", type="string", example="XAF"),
     *                     @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     required={"code", "name", "currency_code"},
     *                     @OA\Property(property="code", type="string", example="CM"),
     *                     @OA\Property(property="name", type="string", example="Cameroun"),
     *                     @OA\Property(property="currency_code", type="string", example="XAF"),
     *                     @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pays créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="CM"),
     *             @OA\Property(property="name", type="string", example="Cameroun"),
     *             @OA\Property(property="currency_code", type="string", example="XAF"),
     *             @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
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
            'code' => 'required|string|size:2|unique:countries,code',
            'name' => 'required|string|max:100',
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'is_active' => 'boolean'
        ]);

        $country = Country::create($validated);

        return response()->json($country, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/countries/{id}",
     *     summary="Mettre à jour un pays",
     *     description="Modifie les informations d’un pays existant (super_admin uniquement)",
     *     operationId="updateCountry",
     *     tags={"Pays"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du pays à modifier",
     *         @OA\Schema(type="string", example="fkfmgfkgn")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(property="code", type="string", example="CM"),
     *                     @OA\Property(property="name", type="string", example="Cameroun"),
     *                     @OA\Property(property="currency_code", type="string", example="XAF"),
     *                     @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     @OA\Property(property="code", type="string", example="CM"),
     *                     @OA\Property(property="name", type="string", example="Cameroun"),
     *                     @OA\Property(property="currency_code", type="string", example="XAF"),
     *                     @OA\Property(property="currency_symbol", type="string", example="FCFA"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pays mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="FR"),
     *             @OA\Property(property="name", type="string", example="France"),
     *             @OA\Property(property="currency_code", type="string", example="EUR"),
     *             @OA\Property(property="currency_symbol", type="string", example="€"),
     *             @OA\Property(property="is_active", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
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

        $country = Country::find($id);
        if (!$country) {
            return response()->json(['message' => 'Pays introuvable'], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|size:2|unique:countries,code,' . $id,
            'name' => 'sometimes|string|max:100',
            'currency_code' => 'sometimes|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'is_active' => 'boolean'
        ]);

        $country->update($validated);

        return response()->json($country);
    }

    /**
     * @OA\Delete(
     *     path="/api/countries/{id}",
     *     summary="Supprimer un pays",
     *     description="Supprime un pays (super_admin uniquement)",
     *     operationId="deleteCountry",
     *     tags={"Pays"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du pays à supprimer",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pays supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pays supprimé avec succès")
     *         )
     *     ),
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

        $country = Country::find($id);
        if (!$country) {
            return response()->json(['message' => 'Pays introuvable'], 404);
        }

        $country->delete();

        return response()->json(['message' => 'Pays supprimé avec succès']);
    }
}
