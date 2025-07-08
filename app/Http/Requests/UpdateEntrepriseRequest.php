<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntrepriseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom_entreprise'                => 'sometimes|required|string|max:255|unique:entreprises,nom_entreprise',
            'nom_commercial'                => 'sometimes|required|string|max:255',
            'numero_identification_fiscale' => 'sometimes|required|string|unique:entreprises,numero_identification_fiscale',
            'numero_registre_commerce'      => 'sometimes|required|string|unique:entreprises,numero_registre_commerce',
            'numero_telephone'              => 'sometimes|required|string|unique:entreprises,numero_telephone',
            'type_entreprise'               => 'sometimes|required|in:SARL,SA,EURL,Auto-entrepreneur,Association,Individuel',
            'secteur_activite'              => 'sometimes|required|string|max:100',
            'description_activite'          => 'sometimes|required|string',
            'adresse_siege_social'          => 'sometimes|required|string|max:255',
            'ville_siege_social'            => 'sometimes|required|string|max:100',
            'code_postal_siege_social'      => 'sometimes|required|string|max:20',
            'pays_siege_social'             => 'sometimes|required|in:Bénin,Cameroun,Côte d\'Ivoire,Guinée-Bissau,Guinée-Conakry,Mali,Niger,République Centrafricaine,Congo-Brazzaville,Sénégal,Tchad,Togo,Ouganda,Afrique du Sud,Botswana,MoneyGhana,Rwanda,Soudan,Zambie,MoneyBénin',
            'email_contact_principal'       => 'sometimes|required|email|unique:entreprises,email_contact_principal',
            'telephone_contact_principal'   => 'sometimes|required|string|max:50',
            'site_web_url'                  => 'sometimes|required|url|max:255',
            'logo_url'                      => 'sometimes|required|url|max:255', // Si vous uploadez une image, ce sera 'file|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            'numero_siren'                  => 'sometimes|required|string|unique:entreprises,numero_siren',
            'numero_siret'                  => 'sometimes|required|string|unique:entreprises,numero_siret',
            'numero_tva_intracommunautaire' => 'sometimes|required|string|unique:entreprises,numero_tva_intracommunautaire',
            'capital_social'                => 'sometimes|required|string|max:255', 
            'annee_creation_entreprise' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'statut_kyb'                 => 'sometimes|required|in:en_attente,approuve,rejete,en_revision', // Défini par défaut à 'en_attente'
            'motif_statut'               => 'sometimes|required|string',

            'rccm_file'                 => 'sometimes|required|file|mimes:pdf,jpg,png|max:10240', // 10MB
            'attestation_fiscale_file'  => 'sometimes|required|file|mimes:pdf,jpg,png|max:10240',
            'statuts_societe_file'      => 'sometimes|required|file|mimes:pdf|max:10240',
            'declaration_regularite_file' => 'sometimes|required|file|mimes:pdf|max:10240',
            'attestation_immatriculation_file' => 'sometimes|required|file|mimes:pdf,jpg,png|max:10240',
            'date_expiration_rccm'      => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'date_expiration_attestation_fiscale'      => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'date_maj_statuts'      => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'date_emission_declaration_regularite'      => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'date_emission_attestation_immatriculation'      => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
        ];
    }


    public function messages(): array
    {
        return [
            // Règles générales
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'unique' => 'Cette valeur pour :attribute est déjà utilisée.',
            'email' => 'Veuillez entrer une adresse email valide.',
            'url' => 'L\'URL du champ :attribute est invalide.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'min' => 'Le champ :attribute doit être au moins :min.',
            'size' => 'Le champ :attribute doit contenir exactement :size caractères.',
            'before_or_equal' => 'La date :attribute doit être antérieure ou égale à aujourd\'hui.',

            // Messages spécifiques
            'numero_siren.size' => 'Le SIREN doit contenir exactement 9 chiffres.',
            'numero_siret.size' => 'Le SIRET doit contenir exactement 14 chiffres.',
            'numero_tva_intracommunautaire.regex' => 'Le numéro de TVA intracommunautaire est invalide (format: XX123456789).',
            'numero_telephone.regex' => 'Le numéro de téléphone est invalide.',
            'capital_social.numeric' => 'Le capital social doit être un nombre.',

            // Fichiers
            'file' => 'Le champ :attribute doit être un fichier.',
            'mimes' => 'Le fichier :attribute doit être de type :values.',
            'uploaded' => 'Le fichier :attribute n\'a pas pu être uploadé.',
            'max' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        ];
    }
}
