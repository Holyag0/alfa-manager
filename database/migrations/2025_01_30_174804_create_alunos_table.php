<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->date('data_nascimento');
            $table->string('matricula')->unique();
            $table->string('email')->unique();
            $table->string('telefone');
            $table->string('pai');
            $table->string('mae');
            $table->string('status');
            $table->string('file_foto')->nullable();
            $table->unsignedBigInteger('endereco_id');
            $table->unsignedBigInteger('responsavel_id');
            $table->timestamps();

            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete('cascade');
            $table->foreign('responsavel_id')->references('id')->on('responsaveis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};