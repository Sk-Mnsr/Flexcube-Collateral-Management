<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter la colonne client_id dans garanties
        Schema::table('garanties', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('garant_id')->constrained('clients')->onDelete('set null');
        });

        // Migrer les données existantes de garantie_client vers garanties
        // Prendre le premier client pour chaque garantie (le plus ancien)
        $garantiesWithClients = DB::table('garantie_client')
            ->select('garantie_id', DB::raw('MIN(client_id) as client_id'))
            ->groupBy('garantie_id')
            ->get();

        foreach ($garantiesWithClients as $row) {
            DB::table('garanties')
                ->where('id', $row->garantie_id)
                ->update(['client_id' => $row->client_id]);
        }

        // Supprimer la table pivot garantie_client
        Schema::dropIfExists('garantie_client');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table pivot garantie_client
        Schema::create('garantie_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['garantie_id', 'client_id']);
        });

        // Migrer les données de garanties.client_id vers garantie_client
        $garantiesWithClient = DB::table('garanties')
            ->whereNotNull('client_id')
            ->select('id', 'client_id')
            ->get();

        foreach ($garantiesWithClient as $garantie) {
            DB::table('garantie_client')->insert([
                'garantie_id' => $garantie->id,
                'client_id' => $garantie->client_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Supprimer la colonne client_id de garanties
        Schema::table('garanties', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
