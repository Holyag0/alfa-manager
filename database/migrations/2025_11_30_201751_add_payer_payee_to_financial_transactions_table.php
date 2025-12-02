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
            // Campos para Receitas (quem pagou - pagante)
            $table->string('payer_name')->nullable()->after('notes');
            $table->string('payer_document', 20)->nullable()->after('payer_name');
            
            // Campos para Despesas (quem vai receber - fornecedor)
            $table->string('payee_name')->nullable()->after('payer_document');
            $table->string('payee_document', 20)->nullable()->after('payee_name');
            
            $table->index('payer_name');
            $table->index('payee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['payer_name']);
            $table->dropIndex(['payee_name']);
            $table->dropColumn(['payer_name', 'payer_document', 'payee_name', 'payee_document']);
        });
    }
};
