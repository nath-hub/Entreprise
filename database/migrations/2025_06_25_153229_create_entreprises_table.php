<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entreprises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index(); 

            $table->string('nom_entreprise')->unique();
            $table->string('nom_commercial')->nullable();
            $table->string('numero_identification_fiscale')->unique()->nullable();
            $table->string('numero_registre_commerce')->unique()->nullable();
            $table->string('numero_telephone')->unique()->nullable(); 
            $table->enum('type_entreprise', ['SARL', 'SA', 'EURL', 'Auto-entrepreneur', 'Association', 'Individuel'])->default('SARL');
            $table->string('secteur_activite')->unique()->nullable();
            $table->string('description_activite')->nullable();
            $table->string('adresse_siege_social')->nullable();
            $table->string('ville_siege_social')->nullable();
            $table->string('code_postal_siege_social')->nullable();
            $table->string('pays_siege_social')->default('France');
            $table->string('email_contact_principal')->unique()->nullable();
            $table->string('telephone_contact_principal')->nullable();
            $table->string('site_web_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('numero_siren')->unique()->nullable();
            $table->string('numero_siret')->unique()->nullable();
            $table->string('numero_tva_intracommunautaire')->unique()->nullable();
            $table->string('capital_social')->nullable();
            $table->year('date_creation_entreprise')->nullable();

            //Informations Légales et KYC/B 
            $table->enum('statut_kyb', ['en_attente', 'approuve', 'rejete', 'en_revision'])->default('en_attente');
            $table->string('motif_statut')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};
