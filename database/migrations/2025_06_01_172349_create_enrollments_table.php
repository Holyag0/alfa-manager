<?php
// database/migrations/xxxx_xx_xx_create_enrollments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
            $table->date('enrollment_date'); // Data da matrícula
            $table->date('end_date')->nullable(); // Data de saída (transferência/conclusão)
            $table->enum('status', ['active', 'transferred', 'completed', 'dropped'])->default('active');
            $table->text('notes')->nullable(); // Observações da matrícula
            $table->timestamps();
            
            // Índices
            $table->index(['student_id', 'status']);
            $table->index(['classroom_id', 'status']);
            
            // Um aluno só pode ter uma matrícula ativa por vez
            $table->unique(['student_id', 'status'], 'unique_active_enrollment');
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollments');
    }
};