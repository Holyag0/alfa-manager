<?php
// database/migrations/xxxx_xx_xx_create_classrooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 1°A, 2°B, 3°C
            $table->string('grade_level'); // 1°, 2°, 3°, etc
            $table->string('section'); // A, B, C, etc
            $table->year('school_year'); // 2024, 2025
            $table->integer('max_students')->default(30); // Capacidade máxima
            $table->integer('current_students')->default(0); // Alunos atuais
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null'); // Professor responsável
            $table->json('schedule')->nullable(); // Horários das aulas
            $table->string('room_number')->nullable(); // Sala física (101, 102, etc)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['school_year', 'status']);
            $table->index(['grade_level', 'section']);
            $table->unique(['name', 'school_year']); // Nome da turma único por ano
        });
    }

    public function down()
    {
        Schema::dropIfExists('classrooms');
    }
};