<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('cpf')->unique();
            $table->string('rg')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('status')->default('active');
            $table->string('guardian_type')->default('titular');
            $table->string('occupation')->nullable();
            $table->string('workplace')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
}; 