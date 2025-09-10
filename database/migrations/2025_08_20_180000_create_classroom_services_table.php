<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['classroom_id', 'service_id']);
            $table->index('is_active');
            
            // Garantir que um serviço não seja duplicado para a mesma turma
            $table->unique(['classroom_id', 'service_id'], 'unique_classroom_service');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_services');
    }
};
