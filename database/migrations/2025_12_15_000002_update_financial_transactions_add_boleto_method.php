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
        if (Schema::hasTable('financial_transactions')) {
            DB::statement("
                ALTER TABLE `financial_transactions`
                MODIFY `payment_method` ENUM('pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer', 'check', 'boleto', 'other')
                NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('financial_transactions')) {
            DB::statement("
                ALTER TABLE `financial_transactions`
                MODIFY `payment_method` ENUM('pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer', 'check', 'other')
                NULL
            ");
        }
    }
};


