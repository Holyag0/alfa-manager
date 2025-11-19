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
        Schema::table('monthly_fee_installments', function (Blueprint $table) {
            // Remover foreign key primeiro
            $table->dropForeign(['invoice_id']);
            // Remover colunas
            $table->dropColumn(['invoice_id', 'notes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_fee_installments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->constrained('enrollment_invoices')->onDelete('set null');
            $table->text('notes')->nullable();
        });
    }
};
