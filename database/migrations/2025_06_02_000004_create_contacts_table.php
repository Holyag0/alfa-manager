<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
            $table->string('type'); // celular, fixo, whatsapp, etc.
            $table->string('value'); // número, email, etc.
            $table->boolean('is_primary')->default(false);
            $table->string('contact_for')->nullable(); // trabalho, pessoal, emergência, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
}; 