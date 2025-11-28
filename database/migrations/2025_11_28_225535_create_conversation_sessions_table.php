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
        Schema::create('conversation_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique(); // Numéro WhatsApp
            $table->string('state')->default('idle'); // État de la conversation
            $table->json('data')->nullable(); // Données temporaires de la session
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('last_activity')->useCurrent();
            $table->timestamps();

            $table->index('phone');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_sessions');
    }
};
