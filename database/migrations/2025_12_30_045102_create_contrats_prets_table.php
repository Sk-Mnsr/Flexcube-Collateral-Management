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
        Schema::create('contrats_prets', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pret')->unique(); // N° de prêt du contrat Flexcube
            $table->decimal('montant_accorde', 15, 2);
            $table->date('date_mise_en_place');
            $table->date('date_maturite')->nullable();
            $table->string('statut'); // actif, annulé, soldé
            $table->string('code_gestionnaire')->nullable();
            $table->string('code_agence')->nullable();
            $table->string('matricule_client'); // Matricule du client
            $table->string('nom_client')->nullable(); // Informations supplémentaires depuis Flexcube
            $table->enum('nature_juridique', ['Entreprise', 'Particulier'])->nullable();
            $table->string('secteur_activite')->nullable();
            $table->timestamp('sync_flexcube_at')->nullable(); // Dernière synchronisation avec Flexcube
            $table->timestamps();

            $table->index('numero_pret');
            $table->index('matricule_client');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats_prets');
    }
};
