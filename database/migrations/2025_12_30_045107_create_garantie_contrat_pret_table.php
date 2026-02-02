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
        Schema::create('garantie_contrat_pret', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->foreignId('contrat_pret_id')->constrained('contrats_prets')->onDelete('cascade');
            $table->decimal('pourcentage_utilisation', 5, 2); // Pourcentage utilisé sur la valeur réelle
            $table->decimal('montant_utilise', 15, 2); // Montant utilisé calculé
            $table->timestamps();

            // Une garantie peut être liée plusieurs fois au même prêt si nécessaire, mais on évite les doublons
            $table->unique(['garantie_id', 'contrat_pret_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantie_contrat_pret');
    }
};
