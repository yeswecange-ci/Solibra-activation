<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique(); // Numéro WhatsApp = identifiant unique
            $table->string('name'); // Nom/Pseudo du joueur
            $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete(); // Village choisi
            $table->timestamp('opted_in_at')->nullable(); // Date d'opt-in
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
