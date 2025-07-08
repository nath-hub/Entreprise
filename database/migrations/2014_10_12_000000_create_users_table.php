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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('telephone')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin_entreprise', 'operateur_entreprise', 'consultant_entreprise'])->default('operateur_entreprise');
            $table->enum('permissions', ['can_create_transaction', 'can_view_reports', 'all_permissions'])->nullable();
            $table->string('deux_facteurs_secret')->nullable();
            $table->string('reset_password_token')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('reset_password_expires_at')->nullable();
            $table->timestamp('date_derniere_connexion')->nullable();
            $table->uuid('granted_by')->nullable()->index(); // UUID for the user who granted permissions
            $table->enum('statut', ['actif', 'inactif', 'suspendu', 'bloque', 'en_attente_verification'])->default('inactif');
            $table->enum('langue_preferee', ['fr', 'en'])->default('fr');
            $table->enum('preferences_notifications', ['email_marketing', 'notifications'])->default('notifications');
            $table->string('photo_profil_url')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
