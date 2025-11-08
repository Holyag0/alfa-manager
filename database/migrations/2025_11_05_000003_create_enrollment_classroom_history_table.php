<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta tabela registra TODOS os vínculos entre matrícula e turma,
     * preservando o histórico mesmo quando há transferências dentro do ano.
     */
    public function up(): void
    {
        Schema::create('enrollment_classroom_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->date('start_date')->comment('Data de início nesta turma');
            $table->date('end_date')->nullable()->comment('Data de saída desta turma (null = ainda está)');
            $table->enum('reason', ['enrollment', 'transfer', 'promotion'])->default('enrollment')
                ->comment('enrollment=matrícula inicial, transfer=transferência, promotion=promoção');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index('enrollment_id');
            $table->index('classroom_id');
            $table->index(['enrollment_id', 'classroom_id']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_classroom_history');
    }
};


