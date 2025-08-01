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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do pacote
            $table->string('category'); // Categoria do pacote
            $table->decimal('price', 10, 2); // Valor do pacote
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status
            $table->text('description')->nullable(); // Descrição opcional
            $table->timestamps();
            
            // Índices para otimização
            $table->index(['status', 'category']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
