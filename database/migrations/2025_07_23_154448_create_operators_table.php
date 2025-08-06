<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('name', 50);
            $table->string('code', 20); 
            $table->uuid('country_id')->index(); 

            $table->string('api_endpoint', 255)->nullable();
            $table->decimal('commission_rate', 5, 4)->default(0.01);
            $table->boolean('is_active')->default(true);

            $table->foreign('country_id')->references('id')->on('countries');
            $table->unique(['code', 'country_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
