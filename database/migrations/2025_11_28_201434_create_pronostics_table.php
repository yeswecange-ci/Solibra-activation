<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pronostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            $table->integer('predicted_score_a'); // Score prédit équipe A
            $table->integer('predicted_score_b'); // Score prédit équipe B
            $table->boolean('is_winner')->default(false); // A gagné le pronostic ?
            $table->timestamps();

            // Un user ne peut faire qu'un seul pronostic par match
            $table->unique(['user_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pronostics');
    }
};
