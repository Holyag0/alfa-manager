<?php
// database/migrations/xxxx_xx_xx_create_students_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique(); // Matrícula única
            $table->string('name');
            $table->date('birth_date');
            $table->string('cpf', 11)->unique()->nullable();
            $table->string('rg', 20)->nullable();
            $table->enum('gender', ['M', 'F', 'other'])->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->json('address')->nullable(); // {street, number, district, city, zipcode}
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'transferred', 'graduated'])->default('active');
            $table->date('enrollment_date');
            $table->text('notes')->nullable(); // Observações
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para performance
            $table->index(['status', 'enrollment_date']);
            $table->index('registration_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};