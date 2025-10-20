<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar o ENUM para incluir 'refunded'
        DB::statement("ALTER TABLE enrollment_invoices MODIFY COLUMN status ENUM('pending', 'paid', 'overdue', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover 'refunded' do ENUM
        DB::statement("ALTER TABLE enrollment_invoices MODIFY COLUMN status ENUM('pending', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};