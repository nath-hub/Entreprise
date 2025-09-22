<?php

namespace App\Http\Controllers;

use App\Helpers\InternalHttpClient;
use App\Models\Entreprise;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntrepriseRequest;
use App\Http\Requests\UpdateEntrepriseRequest;
use App\Models\FichierEntreprise;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EntrepriseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/entreprises",
     *     tags={"Entreprises"},
     *     summary="Créer une entreprise avec documents",
     *     operationId="storeEntreprise",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nom_entreprise", "type_entreprise", "pays_siege_social"},
     *                 @OA\Property(property="nom_entreprise", type="string", maxLength=255, example="MaSuperEntreprise"),
     *                 @OA\Property(property="nom_commercial", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="numero_identification_fiscale", type="string", nullable=true),
     *                 @OA\Property(property="numero_registre_commerce", type="string", nullable=true),
     *                 @OA\Property(property="numero_telephone", type="string", nullable=true),
     *                 @OA\Property(property="type_entreprise", type="string", enum={"SARL", "SA", "EURL", "Auto-entrepreneur", "Association", "Individuel"}, example="SARL"),
     *                 @OA\Property(property="secteur_activite", type="string", maxLength=100, nullable=true),
     *                 @OA\Property(property="description_activite", type="string", nullable=true),
     *                 @OA\Property(property="adresse_siege_social", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="ville_siege_social", type="string", maxLength=100, nullable=true),
     *                 @OA\Property(property="code_postal_siege_social", type="string", maxLength=20, nullable=true),
     *                 @OA\Property(
     *                     property="pays_siege_social",
     *                     type="string",
     *                     example="Cameroun",
     *                     enum={
     *                         "Bénin","Cameroun","Côte d'Ivoire","Guinée-Bissau","Guinée-Conakry",
     *                         "Mali","Niger","République Centrafricaine","Congo-Brazzaville",
     *                         "Sénégal","Tchad","Togo","Ouganda","Afrique du Sud","Botswana",
     *                         "MoneyGhana","Rwanda","Soudan","Zambie","MoneyBénin"
     *                     }
     *                 ),
     *                 @OA\Property(property="email_contact_principal", type="string", format="email", nullable=true),
     *                 @OA\Property(property="telephone_contact_principal", type="string", maxLength=50, nullable=true),
     *                 @OA\Property(property="site_web_url", type="string", format="url", nullable=true),
     *                 @OA\Property(property="logo_url", type="string", format="url", nullable=true),
     *                 @OA\Property(property="numero_siren", type="string", nullable=true),
     *                 @OA\Property(property="numero_siret", type="string", nullable=true),
     *                 @OA\Property(property="numero_tva_intracommunautaire", type="string", nullable=true),
     *                 @OA\Property(property="capital_social", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="annee_creation_entreprise", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="motif_statut", type="string", nullable=true),

     *                 @OA\Property(property="rccm_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="attestation_fiscale_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="statuts_societe_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="declaration_regularite_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="attestation_immatriculation_file", type="string", format="binary", nullable=true),

     *                 @OA\Property(property="date_expiration_rccm", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_expiration_attestation_fiscale", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_maj_statuts", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_emission_declaration_regularite", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_emission_attestation_immatriculation", type="integer", minimum=1900, maximum=2100, nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Entreprise créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise et fichiers créés avec succès."),
     *             @OA\Property(property="entreprise", type="object"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="code", type="string", example="ENTREPRISE_CREATED")
     *         )
     *     ),
     *       @OA\Response(
     *         response=404,
     *         description="Entreprise introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise introuvable."),
     *             @OA\Property(property="code", type="string", example="ENT_NOT_FOUND"),
     *             @OA\Property(property="field", type="string", example="id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié."),
     *             @OA\Property(property="code", type="string", example="UNAUTHENTICATED"),
     *             @OA\Property(property="status", type="number", example="401")
     *         )
     *     ),
     *        @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès non autorisé"),
     *             @OA\Property(property="code", type="string", example="FORBIDDEN"),
     *             @OA\Property(property="status", type="number", example="403")
     *         )
     *     )
     * )
     */

    public function store(StoreEntrepriseRequest $request)
    {
        $user = auth()->user();

        $entrepriseData = $request->except([
            'rccm_file',
            'attestation_fiscale_file',
            'statuts_societe_file',
            'declaration_regularite_file',
            'attestation_immatriculation_file',
        ]);

        $entrepriseData2 = $request->except([
            'rccm_file',
            'attestation_fiscale_file',
            'statuts_societe_file',
            'declaration_regularite_file',
            'attestation_immatriculation_file',
        ]);

        $user2 = User::on('mysql_prod')->where('email', $user->email)->first();

        $entrepriseData['user_id'] = $user->id;
        $entrepriseData2['user_id'] = $user2->id;

        $entreprise = Entreprise::on('mysql_sandbox')->create($entrepriseData);
        $entreprise2 = Entreprise::on('mysql_prod')->create($entrepriseData2);

        $fichierData = [
            'statut_fichier' => 'en_attente',
        ];

        $fileFieldsMap = [
            'rccm_file' => 'url_rccm',
            'attestation_fiscale_file' => 'url_attestation_fiscale',
            'statuts_societe_file' => 'url_statuts_societe',
            'declaration_regularite_file' => 'url_declaration_regularite',
            'attestation_immatriculation_file' => 'url_attestation_immatriculation',
        ];

        foreach ($fileFieldsMap as $requestFileKey => $dbUrlColumn) {
            if ($request->hasFile($requestFileKey)) {
                $file = $request->file($requestFileKey);

                $path = $file->store('entreprises_docs/' . $entreprise->id, 'public');

                // Store the full URL instead of just the path
                $fichierData[$dbUrlColumn] = asset('storage/' . $path);
            }
        }

        $fichierData['date_expiration_rccm'] = $request->input('date_expiration_rccm');
        $fichierData['date_expiration_attestation_fiscale'] = $request->input('date_expiration_attestation_fiscale');
        $fichierData['date_maj_statuts'] = $request->input('date_maj_statuts');
        $fichierData['date_emission_declaration_regularite'] = $request->input('date_emission_declaration_regularite');
        $fichierData['date_emission_attestation_immatriculation'] = $request->input('date_emission_attestation_immatriculation');

        $fichierData['user_id'] = $user->id;
        $fichierData['entreprise_id'] = $entreprise->id;

        $fichierData2 = $fichierData;
        $fichierData2['user_id'] = $user2->id;
        $fichierData2['entreprise_id'] = $entreprise2->id;

        FichierEntreprise::on('mysql_sandbox')->create($fichierData);

        FichierEntreprise::on('mysql_prod')->create($fichierData2);

        $authServiceUrl = config('services.services_notifications.url');
        $httpClient = new InternalHttpClient();
        $httpClient->post($request, $authServiceUrl, 'api/merchant-welcome', [
            'id' => $user->id,
            'environment' => 'sandbox',
            'data' => $entreprise->nom_entreprise
        ], ['read:users']);

        return response()->json([
            'message' => 'Entreprise et fichiers créés avec succès.',
            'entreprise' => $entreprise,
            'status' => 201,
            'code' => 'ENTREPRISE_CREATED',
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/entreprises/{id}",
     *     tags={"Entreprises"},
     *     summary="Afficher les détails d'une entreprise",
     *     description="Récupère une entreprise avec ses fichiers et son utilisateur",
     *     operationId="showEntreprise",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'entreprise",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entreprise trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="entreprise", type="object")
     *         )
     *     ),

     *       @OA\Response(
     *         response=404,
     *         description="Entreprise introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise introuvable."),
     *             @OA\Property(property="code", type="string", example="ENT_NOT_FOUND"),
     *             @OA\Property(property="field", type="string", example="id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié."),
     *             @OA\Property(property="code", type="string", example="UNAUTHENTICATED"),
     *             @OA\Property(property="status", type="number", example="401")
     *         )
     *     ),
     *        @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès non autorisé"),
     *             @OA\Property(property="code", type="string", example="FORBIDDEN"),
     *             @OA\Property(property="status", type="number", example="403")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        try {
            $entreprise = Entreprise::findOrFail($id);
            return response()->json($entreprise, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Entreprise introuvable.',
                'code' => 'ENT_NOT_FOUND',
                'field' => 'id'
            ], 404);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/entreprises/me/company",
     *     tags={"Entreprises"},
     *     summary="Afficher les détails d'une entreprise",
     *     description="Récupère une entreprise avec ses fichiers et son utilisateur",
     *     operationId="showEntrepriseByToken",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Entreprise trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="entreprise", type="object")
     *         )
     *     ),
     *       @OA\Response(
     *         response=404,
     *         description="Entreprise introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise introuvable."),
     *             @OA\Property(property="code", type="string", example="ENT_NOT_FOUND"),
     *             @OA\Property(property="field", type="string", example="id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié."),
     *             @OA\Property(property="code", type="string", example="UNAUTHENTICATED"),
     *             @OA\Property(property="status", type="number", example="401")
     *         )
     *     ),
     *        @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès non autorisé"),
     *             @OA\Property(property="code", type="string", example="FORBIDDEN"),
     *             @OA\Property(property="status", type="number", example="403")
     *         )
     *     )
     * )
     */

    public function showByToken()
    {

        $user = auth()->user();

        try {
            $entreprise = Entreprise::with('fichiers', 'user')->where('user_id', $user->id)->firstOrFail();


            if ($this->authorize('view', $entreprise)) {
                return response()->json($entreprise, 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Entreprise introuvable.',
                'code' => 'ENT_NOT_FOUND',
                'field' => 'id'
            ], 404);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/entreprises/{id}",
     *     tags={"Entreprises"},
     *     summary="Mettre à jour une entreprise et ses fichiers",
     *     description="Met à jour les informations d'une entreprise ainsi que ses documents. L'utilisateur doit être authentifié et autorisé.",
     *     operationId="updateEntreprise",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'entreprise à mettre à jour",
     *         @OA\Schema(type="string", example="dkjnfdkjwwoio32hdwc8s9ebcjscdjs")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nom_entreprise", type="string", maxLength=255, example="MaSuperEntreprise"),
     *                 @OA\Property(property="nom_commercial", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="numero_identification_fiscale", type="string", nullable=true),
     *                 @OA\Property(property="numero_registre_commerce", type="string", nullable=true),
     *                 @OA\Property(property="numero_telephone", type="string", nullable=true),
     *                 @OA\Property(property="type_entreprise", type="string", enum={"SARL", "SA", "EURL", "Auto-entrepreneur", "Association", "Individuel"}, example="SARL"),
     *                 @OA\Property(property="secteur_activite", type="string", maxLength=100, nullable=true),
     *                 @OA\Property(property="description_activite", type="string", nullable=true),
     *                 @OA\Property(property="adresse_siege_social", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="ville_siege_social", type="string", maxLength=100, nullable=true),
     *                 @OA\Property(property="code_postal_siege_social", type="string", maxLength=20, nullable=true),
     *                 @OA\Property(
     *                     property="pays_siege_social",
     *                     type="string",
     *                     example="Cameroun",
     *                     enum={
     *                         "Bénin","Cameroun","Côte d'Ivoire","Guinée-Bissau","Guinée-Conakry",
     *                         "Mali","Niger","République Centrafricaine","Congo-Brazzaville",
     *                         "Sénégal","Tchad","Togo","Ouganda","Afrique du Sud","Botswana",
     *                         "MoneyGhana","Rwanda","Soudan","Zambie","MoneyBénin"
     *                     }
     *                 ),
     *                 @OA\Property(property="email_contact_principal", type="string", format="email", nullable=true),
     *                 @OA\Property(property="telephone_contact_principal", type="string", maxLength=50, nullable=true),
     *                 @OA\Property(property="site_web_url", type="string", format="url", nullable=true),
     *                 @OA\Property(property="logo_url", type="string", format="url", nullable=true),
     *                 @OA\Property(property="numero_siren", type="string", nullable=true),
     *                 @OA\Property(property="numero_siret", type="string", nullable=true),
     *                 @OA\Property(property="numero_tva_intracommunautaire", type="string", nullable=true),
     *                 @OA\Property(property="capital_social", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="annee_creation_entreprise", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="statut_kyb", type="string", enum={"en_attente","approuve","rejete","en_revision"}, example="en_attente"),
     *                 @OA\Property(property="motif_statut", type="string", nullable=true),

     *                 @OA\Property(property="rccm_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="attestation_fiscale_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="statuts_societe_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="declaration_regularite_file", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="attestation_immatriculation_file", type="string", format="binary", nullable=true),

     *                 @OA\Property(property="date_expiration_rccm", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_expiration_attestation_fiscale", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_maj_statuts", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_emission_declaration_regularite", type="integer", minimum=1900, maximum=2100, nullable=true),
     *                 @OA\Property(property="date_emission_attestation_immatriculation", type="integer", minimum=1900, maximum=2100, nullable=true)
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entreprise mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise et fichiers mis à jour avec succès."),
     *             @OA\Property(property="entreprise", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entreprise introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise introuvable."),
     *             @OA\Property(property="code", type="string", example="ENT_NOT_FOUND"),
     *             @OA\Property(property="field", type="string", example="id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié."),
     *             @OA\Property(property="code", type="string", example="UNAUTHENTICATED"),
     *             @OA\Property(property="status", type="number", example="401")
     *         )
     *     ),
     *        @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès non autorisé"),
     *             @OA\Property(property="code", type="string", example="FORBIDDEN"),
     *             @OA\Property(property="status", type="number", example="403")
     *         )
     *     ),
     *
     * )
     */

    public function update(UpdateEntrepriseRequest $request, $id)
    {
        try {

            $entreprise = Entreprise::on('mysql_sandbox')->with('fichiers')->findOrFail($id);
            $entreprise2 = Entreprise::on('mysql_prod')->with('fichiers')->findOrFail($id);

            $this->authorize('update', $entreprise);

            DB::on('mysql_sandbox')->beginTransaction();

            $dataEntreprise = $request->except([
                'rccm_file',
                'attestation_fiscale_file',
                'statuts_societe_file',
                'declaration_regularite_file',
                'attestation_immatriculation_file',
            ]);

            $entreprise->update($dataEntreprise);
            $entreprise2->update($dataEntreprise);

            $fichierEntreprise = $entreprise->fichiers;
            $fichierEntreprise2 = $entreprise2->fichiers;

            $fileFieldsMap = [
                'rccm_file' => 'url_rccm',
                'attestation_fiscale_file' => 'url_attestation_fiscale',
                'statuts_societe_file' => 'url_statuts_societe',
                'declaration_regularite_file' => 'url_declaration_regularite',
                'attestation_immatriculation_file' => 'url_attestation_immatriculation',
            ];

            $updatedFileUrls = [];

            foreach ($fileFieldsMap as $fileInput => $dbField) {

                if ($request->hasFile($fileInput)) {
                    if ($fichierEntreprise->$dbField) {
                        $oldPath = parse_url($fichierEntreprise->$dbField, PHP_URL_PATH);
                        $oldPath = str_replace('/storage/', '', $oldPath);
                        Storage::disk('public')->delete($oldPath);
                    }

                    $path = $request->file($fileInput)->store(
                        "entreprises_docs/{$entreprise->id}",
                        'public'
                    );
                    $fileUpdates[$dbField] = asset('storage/' . $path);
                }
            }

            $fileUpdates['date_expiration_rccm'] = $request->input('date_expiration_rccm') ?? $fichierEntreprise['date_expiration_rccm'];
            $fileUpdates['date_expiration_attestation_fiscale'] = $request->input('date_expiration_attestation_fiscale') ?? $fichierEntreprise['date_expiration_attestation_fiscale'];
            $fileUpdates['date_maj_statuts'] = $request->input('date_maj_statuts') ?? $fichierEntreprise['date_maj_statuts'];
            $fileUpdates['date_emission_declaration_regularite'] = $request->input('date_emission_declaration_regularite') ?? $fichierEntreprise['date_emission_declaration_regularite'];
            $fileUpdates['date_emission_attestation_immatriculation'] = $request->input('date_emission_attestation_immatriculation') ?? $fichierEntreprise['date_emission_attestation_immatriculation'];

            $dateFields = [
                'date_expiration_rccm',
                'date_expiration_attestation_fiscale',
                'date_maj_statuts',
                'date_emission_declaration_regularite',
                'date_emission_attestation_immatriculation'
            ];

            foreach ($dateFields as $dateField) {
                if ($request->has($dateField)) {
                    $fileUpdates[$dateField] = $request->input($dateField);
                }
            }

            DB::commit();

            $entreprise->refresh()->load('fichiers');


            return response()->json([
                'message' => 'Entreprise et fichiers mis à jour avec succès.',
                'entreprise' => $entreprise->load('fichiers'),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Entreprise introuvable.',
                'code' => 'ENT_NOT_FOUND',
                'field' => 'id'
            ], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/entreprises/{id}",
     *     tags={"Entreprises"},
     *     summary="Supprimer une entreprise et ses fichiers",
     *     description="Supprime une entreprise, ses fichiers associés et son dossier de stockage. Nécessite une autorisation.",
     *     operationId="deleteEntreprise",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'entreprise à supprimer",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entreprise supprimée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise et fichiers supprimés avec succès."),
     *             @OA\Property(property="code", type="string", example="ENT_DELETED"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entreprise introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entreprise introuvable."),
     *             @OA\Property(property="code", type="string", example="ENT_NOT_FOUND"),
     *             @OA\Property(property="field", type="string", example="id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non authentifié."),
     *             @OA\Property(property="code", type="string", example="UNAUTHENTICATED"),
     *             @OA\Property(property="status", type="number", example="401")
     *         )
     *     ),
     *        @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès non autorisé"),
     *             @OA\Property(property="code", type="string", example="FORBIDDEN"),
     *             @OA\Property(property="status", type="number", example="403")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $entreprise = Entreprise::findOrFail($id);

        $this->authorize('delete', $entreprise);

        if (!$entreprise) {
            return response()->json([
                'message' => 'Entreprise introuvable.',
                'code' => 'ENT_NOT_FOUND',
                'field' => 'id'
            ], 404);
        }

        DB::beginTransaction();

        if ($entreprise->fichiers && Storage::disk('public')->exists('entreprises_docs/' . $entreprise->id)) {
            Storage::disk('public')->deleteDirectory('entreprises_docs/' . $entreprise->id);
        }

        if ($entreprise->fichiers) {
            $entreprise->fichiers->delete();
        }

        $entreprise->delete();

        DB::commit();

        return response()->json([
            'message' => 'Entreprise et fichiers supprimés avec succès.',
            'code' => 'ENT_DELETED',
            'status' => 200,
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/entreprises/update/status/{id}",
     *     tags={"Entreprises"},
     *     summary="Modifier une entreprises",
     *     description="Met à jour le status d’une entreprises. Les données sont envoyées en multipart/form-data.",
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
     *                     enum={"en_attente", "approuve", "rejete"},
     *                     example="rejete"
     *                 ),
     *                 @OA\Property(
     *                     property="motif_statut",
     *                     type="string",
     *                     example="Donnees mal renseigner"
     *                 ),
     *             )
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Entreprises mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="succès"),
     *             @OA\Property(property="message", type="string", example="Entreprises mis à jour avec succès."),
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
        $user = $request->user();

        if ($user && $user->role !== 'super_admin') {
            return response()->json(['message' => 'Accès refusé', 'status' => 403, 'code' => 'PERMISSION_DENIED'], 403);
        }

        $request->validate([
            'status' => 'required|in:en_attente,approuve,rejete',
            'motif_statut' => 'nullable|string|max:255',
        ]);

        $entrepriseSandbox = Entreprise::on('mysql_sandbox')->with('fichiers')->find($id);

        $entrepriseProd = Entreprise::on('mysql_prod')->with('fichiers')->find($id);

        if ($entrepriseSandbox) {
            $entrepriseProdMatch = Entreprise::on('mysql_prod')
                ->where('nom_entreprise', $entrepriseSandbox->nom_entreprise)
                ->first();

            if ($entrepriseProdMatch) {
                $entrepriseProdMatch->update([
                    'statut_kyb' => $request->input('status'),
                    'motif_statut' => $request->input('motif_statut'),
                ]);

                $entrepriseProdMatch->fichiers()->update([
                    'statut_fichier' => $request->input('status'),
                ]);
            }

            $entrepriseSandbox->update([
                'statut_kyb' => $request->input('status'),
                'motif_statut' => $request->input('motif_statut'),
            ]);

            $entrepriseSandbox->fichiers()->update([
                'statut_fichier' => $request->input('status'),
            ]);
        } elseif ($entrepriseProd) {
            $entrepriseSandboxMatch = Entreprise::on('mysql_sandbox')
                ->where('nom_entreprise', $entrepriseProd->nom_entreprise)
                ->first();

            if ($entrepriseSandboxMatch) {
                $entrepriseSandboxMatch->update([
                    'statut_kyb' => $request->input('status'),
                    'motif_statut' => $request->input('motif_statut'),
                ]);

                $entrepriseSandboxMatch->fichiers()->update([
                    'statut_fichier' => $request->input('status'),
                ]);
            }

            $entrepriseProd->update([
                'statut_kyb' => $request->input('status'),
                'motif_statut' => $request->input('motif_statut'),
            ]);

            $entrepriseProd->fichiers()->update([
                'statut_fichier' => $request->input('status'),
            ]);
        } else {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        $authServiceUrl = config('services.services_notifications.url');
        $httpClient = new InternalHttpClient();
        $httpClient->post($request, $authServiceUrl, 'api/merchant-kyc-validation', [
            'id' => $user->id,
            'data' => [$entrepriseSandbox]
        ], ['read:users']);

        return response()->json([
            'code' => 200,
            'status' => 'succès',
            'message' => 'Entreprise mis à jour avec succès.',
            'data' => $entrepriseSandbox
        ]);
    }
}
