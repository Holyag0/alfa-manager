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
        Schema::create('sibling_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do grupo de irmãos
            $table->text('description')->nullable(); // Descrição opcional
            $table->boolean('is_active')->default(true); // Status do grupo
            $table->timestamps();
            
            // Índices para otimização
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sibling_groups');
    }
};
