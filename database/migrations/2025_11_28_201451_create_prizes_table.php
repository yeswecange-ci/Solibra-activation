<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du lot (ex: T-shirt, Bon d'achat)
            $table->text('description')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete(); // Partenaire donateur
            $table->integer('quantity'); // Quantité disponible
            $table->integer('distributed_count')->default(0); // Quantité distribuée
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
