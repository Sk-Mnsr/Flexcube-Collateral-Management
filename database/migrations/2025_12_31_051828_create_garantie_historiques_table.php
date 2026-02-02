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
        Schema::create('garantie_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garantie_id')->constrained('garanties')->onDelete('cascade');
            $table->string('ancien_statut');
            $table->string('nouveau_statut');
            $table->foreignId('utilisateur_id')->constrained('users')->onDelete('set null');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->index('garantie_id');
            $table->index('nouveau_statut');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantie_historiques');
    }
};
