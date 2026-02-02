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
        Schema::table('garanties', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère si elle existe
            if (Schema::hasColumn('garanties', 'filiale_id')) {
                $table->dropForeign(['filiale_id']);
                $table->dropColumn('filiale_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garanties', function (Blueprint $table) {
            // Recréer la colonne filiale_id (sans contrainte car la table filiales n'existe plus)
            $table->foreignId('filiale_id')->nullable();
        });
    }
};
