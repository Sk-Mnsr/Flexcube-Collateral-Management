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
        Schema::create('matricules_clients', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique(); // Matricule Flexcube
            $table->string('nom');
            $table->enum('nature_juridique', ['Entreprise', 'Particulier']);
            $table->string('secteur_activite')->nullable();
            $table->timestamp('sync_flexcube_at')->nullable(); // Dernière synchronisation avec Flexcube
            $table->timestamps();

            $table->index('matricule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matricules_clients');
    }
};
