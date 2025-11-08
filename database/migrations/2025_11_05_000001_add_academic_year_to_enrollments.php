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
        Schema::table('enrollments', function (Blueprint $table) {
            // Adicionar campo academic_year após classroom_id
            $table->string('academic_year', 10)->after('classroom_id')->nullable();
            
            // Adicionar índice para melhorar performance de queries por ano
            $table->index('academic_year');
            
            // Índice composto para buscar matrículas de um aluno em um ano específico
            $table->index(['student_id', 'academic_year']);
            
            // Índice composto para buscar matrículas ativas de uma turma em um ano
            $table->index(['classroom_id', 'academic_year', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['classroom_id', 'academic_year', 'status']);
            $table->dropIndex(['student_id', 'academic_year']);
            $table->dropIndex(['academic_year']);
            $table->dropColumn('academic_year');
        });
    }
};


