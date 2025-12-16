<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Atualizar ENUM do campo "method" para incluir "boleto"
        if (Schema::hasTable('monthly_fee_payments')) {
            DB::statement("
                ALTER TABLE `monthly_fee_payments`
                MODIFY `method` ENUM('pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer', 'check', 'boleto')
                NOT NULL DEFAULT 'cash'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter ENUM para o estado original (sem 'boleto')
        if (Schema::hasTable('monthly_fee_payments')) {
            DB::statement("
                ALTER TABLE `monthly_fee_payments`
                MODIFY `method` ENUM('pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer', 'check')
                NOT NULL DEFAULT 'cash'
            ");
        }
    }
};


