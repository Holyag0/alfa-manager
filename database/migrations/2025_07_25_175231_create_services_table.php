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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do serviço
            $table->decimal('price', 10, 2); // Valor do serviço
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status
            $table->string('category'); // Categoria do serviço
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
        Schema::dropIfExists('services');
    }
};
