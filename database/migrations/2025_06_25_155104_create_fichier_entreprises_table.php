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
        Schema::create('fichier_entreprises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id')->index();
            $table->uuid('user_id')->index();

            $table->string('url_rccm')->nullable();
            $table->year('date_expiration_rccm')->nullable();
            $table->string('url_attestation_fiscale')->nullable();
            $table->year('date_expiration_attestation_fiscale')->nullable();
            $table->string('url_statuts_societe')->nullable();
            $table->year('date_maj_statuts')->nullable();
            $table->string('url_declaration_regularite')->nullable();
            $table->year('date_emission_declaration_regularite')->nullable();
            $table->string('url_attestation_immatriculation')->nullable();
            $table->year('date_emission_attestation_immatriculation')->nullable();
            $table->enum('statut_fichier', ['en_attente', 'approuve', 'rejete'])->default('en_attente');

            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichier_entreprises');
    }
};
