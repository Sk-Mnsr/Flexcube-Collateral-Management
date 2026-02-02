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
        Schema::create('types_garanties', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Ex: CAU-HYP, GAR-HYP
            $table->string('libelle');
            $table->string('type'); // Matérielle (réelle), Personnelle, Financière, Divers
            $table->text('description')->nullable();
            $table->decimal('decote_pourcentage', 5, 2)->default(0); // Décote en %
            $table->decimal('ponderation_pourcentage', 5, 2)->default(100); // Pondération en % (100 - décote)
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_garanties');
    }
};
