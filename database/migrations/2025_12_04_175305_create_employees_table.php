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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('cpf', 20)->nullable();
            $table->string('rg', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo')->nullable()->comment('Caminho da foto');
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null');
            $table->date('hire_date')->nullable()->comment('Data de contratação');
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para otimização
            $table->index('name');
            $table->index('email');
            $table->index('cpf');
            $table->index('position_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
