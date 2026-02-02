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
        Schema::create('garantie_matricule_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->foreignId('matricule_client_id')->constrained('matricules_clients')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['garantie_id', 'matricule_client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantie_matricule_client');
    }
};
