<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prize_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prize_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_id')->nullable()->constrained()->nullOnDelete(); // Match lié si applicable
            $table->timestamp('collected_at')->nullable(); // Date de récupération
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prize_winners');
    }
};
