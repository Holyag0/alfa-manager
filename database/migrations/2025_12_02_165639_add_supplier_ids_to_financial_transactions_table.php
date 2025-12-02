<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            // Foreign keys para suppliers (pagante para receitas, fornecedor para despesas)
            $table->foreignId('payer_supplier_id')->nullable()->after('payer_document')->constrained('suppliers')->onDelete('set null');
            $table->foreignId('payee_supplier_id')->nullable()->after('payee_document')->constrained('suppliers')->onDelete('set null');
            
            $table->index('payer_supplier_id');
            $table->index('payee_supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['payer_supplier_id']);
            $table->dropForeign(['payee_supplier_id']);
            $table->dropIndex(['payer_supplier_id']);
            $table->dropIndex(['payee_supplier_id']);
            $table->dropColumn(['payer_supplier_id', 'payee_supplier_id']);
        });
    }
};
