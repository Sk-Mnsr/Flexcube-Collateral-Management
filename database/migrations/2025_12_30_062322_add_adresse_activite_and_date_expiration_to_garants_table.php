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
        Schema::table('garants', function (Blueprint $table) {
            $table->text('adresse_activite')->nullable()->after('activite');
            $table->date('date_expiration_piece_identite')->nullable()->after('date_delivrance_piece_identite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garants', function (Blueprint $table) {
            $table->dropColumn(['adresse_activite', 'date_expiration_piece_identite']);
        });
    }
};
