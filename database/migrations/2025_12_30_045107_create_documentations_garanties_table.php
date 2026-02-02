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
        Schema::create('documentations_garanties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->enum('type_documentation', ['texte', 'fichier']);
            $table->string('nom');
            $table->decimal('valeur', 15, 2)->nullable(); // Valeur si applicable
            $table->text('description')->nullable();
            $table->string('chemin_fichier')->nullable(); // Chemin du fichier si type = fichier
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentations_garanties');
    }
};
