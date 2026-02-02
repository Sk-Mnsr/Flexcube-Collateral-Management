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
        Schema::create('garanties', function (Blueprint $table) {
            $table->id();
            $table->string('reference_unique')->unique(); // GAR-YYYY-XXXXXX
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('emplacement')->nullable();
            $table->foreignId('type_garantie_id')->constrained('types_garanties')->onDelete('restrict');
            $table->foreignId('garant_id')->constrained('garants')->onDelete('restrict');
            $table->decimal('valeur', 15, 2); // Valeur saisie
            $table->decimal('valeur_reelle', 15, 2); // Valeur réelle après décote
            $table->enum('statut', [
                'normal',
                'contentieux',
                'realisation',
                'mutation_tiers',
                'mutation_cofina',
                'vendu',
                'main_leve',
                'dation'
            ])->default('normal');
            $table->date('date_creation');
            $table->date('date_expiration')->nullable();
            $table->foreignId('modifie_par')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('date_modification')->nullable();
            $table->timestamps();

            $table->index('reference_unique');
            $table->index('statut');
            $table->index('date_creation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garanties');
    }
};
