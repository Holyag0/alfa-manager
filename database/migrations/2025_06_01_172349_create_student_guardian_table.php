<?php
// database/migrations/xxxx_xx_xx_create_student_guardian_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_guardian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('guardian_id')->constrained()->onDelete('cascade');
            $table->enum('relationship', ['father', 'mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'brother', 'sister', 'other']);
            $table->boolean('is_primary')->default(false); // Responsável principal
            $table->boolean('is_financial')->default(false); // Responsável financeiro
            $table->boolean('can_pickup')->default(true); // Pode buscar o aluno
            $table->timestamps();
            
            // Índices
            $table->index(['student_id', 'is_primary']);
            $table->unique(['student_id', 'guardian_id']); // Evita duplicatas
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_guardian');
    }
};