<?php
// database/migrations/xxxx_xx_xx_create_guardians_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf', 11)->unique()->nullable();
            $table->string('rg', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'other'])->nullable();
            $table->string('phone', 15);
            $table->string('email')->nullable();
            $table->string('profession')->nullable();
            $table->json('address')->nullable(); // {street, number, district, city, zipcode}
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('phone');
            $table->index('cpf');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guardians');
    }
};