<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntrepriseRequest extends FormRequest
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
            'nom_entreprise'                => 'required|string|max:255|unique:entreprises,nom_entreprise',
            'nom_commercial'                => 'nullable|string|max:255',
            'numero_identification_fiscale' => 'nullable|string|unique:entreprises,numero_identification_fiscale',
            'numero_registre_commerce'      => 'nullable|string|unique:entreprises,numero_registre_commerce',
            'numero_telephone'              => 'nullable|string|unique:entreprises,numero_telephone',
            'type_entreprise'               => 'required|in:SARL,SA,EURL,Auto-entrepreneur,Association,Individuel',
            'secteur_activite'              => 'nullable|string|max:100',
            'description_activite'          => 'nullable|string',
            'adresse_siege_social'          => 'nullable|string|max:255',
            'ville_siege_social'            => 'nullable|string|max:100',
            'code_postal_siege_social'      => 'nullable|string|max:20',
            'pays_siege_social'             => "required|string|in:Bénin,Cameroun,Côte d'Ivoire,Guinée-Bissau,Guinée-Conakry,Mali,Niger,République Centrafricaine,Congo-Brazzaville,Sénégal,Tchad,Togo,Ouganda,Afrique du Sud,Botswana,MoneyGhana,Rwanda,Soudan,Zambie,MoneyBénin",
            'email_contact_principal'       => 'nullable|email|unique:entreprises,email_contact_principal',
            'telephone_contact_principal'   => 'nullable|string|max:50',
            'site_web_url'                  => 'nullable|url|max:255',
            'logo_url'                      => 'nullable|url|max:255', // Si vous uploadez une image, ce sera 'file|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            'numero_siren'                  => 'nullable|string|unique:entreprises,numero_siren',
            'numero_siret'                  => 'nullable|string|unique:entreprises,numero_siret',
            'numero_tva_intracommunautaire' => 'nullable|string|unique:entreprises,numero_tva_intracommunautaire',
            'capital_social'                => 'nullable|string|max:255', 
            'annee_creation_entreprise' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),

            'motif_statut'               => 'nullable|string',

            'rccm_file'                 => 'nullable|file|mimes:pdf,jpg,png|max:10240', // 10MB
            'attestation_fiscale_file'  => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'statuts_societe_file'      => 'nullable|file|mimes:pdf|max:10240',
            'declaration_regularite_file' => 'nullable|file|mimes:pdf|max:10240',
            'attestation_immatriculation_file' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'date_expiration_rccm'      => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'date_expiration_attestation_fiscale'      => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'date_maj_statuts'      => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'date_emission_declaration_regularite'      => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'date_emission_attestation_immatriculation'      => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
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
