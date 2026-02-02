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
        Schema::create('garants', function (Blueprint $table) {
            $table->id();
            $table->string('civilite'); // M, Mme, Mlle
            $table->string('nom');
            $table->string('prenom');
            $table->text('adresse')->nullable();
            $table->date('date_naissance');
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->nullable();
            $table->string('activite')->nullable();
            $table->string('type_piece_identite'); // CNI, Passeport, etc.
            $table->string('numero_piece_identite');
            $table->string('fichier_piece_identite')->nullable(); // Chemin du fichier
            $table->date('date_delivrance_piece_identite')->nullable();
            $table->string('telephone')->nullable();
            $table->timestamps();

            // Index pour la vérification d'unicité (nom, prénom, date_naissance)
            $table->index(['nom', 'prenom', 'date_naissance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garants');
    }
};
