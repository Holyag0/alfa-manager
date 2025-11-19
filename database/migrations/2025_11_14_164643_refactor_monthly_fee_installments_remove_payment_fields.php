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
        // Remover campos de pagamento de monthly_fee_installments
        // Estes campos devem estar apenas em monthly_fee_payments
        Schema::table('monthly_fee_installments', function (Blueprint $table) {
            $table->dropColumn([
                'base_amount',
                'sibling_discount',
                'punctuality_discount',
                'other_discounts',
                'interest_amount',
                'fine_amount',
                'final_amount',
                'paid_date',
            ]);
        });

        // Adicionar campos mais detalhados em monthly_fee_payments
        Schema::table('monthly_fee_payments', function (Blueprint $table) {
            // Separar descontos em campos específicos
            $table->decimal('sibling_discount', 10, 2)->default(0)->after('discount_applied');
            $table->decimal('punctuality_discount', 10, 2)->default(0)->after('sibling_discount');
            $table->decimal('other_discounts', 10, 2)->default(0)->after('punctuality_discount');
            
            // Remover discount_applied (agora temos campos específicos)
            $table->dropColumn('discount_applied');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter alterações em monthly_fee_payments
        Schema::table('monthly_fee_payments', function (Blueprint $table) {
            $table->decimal('discount_applied', 10, 2)->default(0)->after('original_installment_amount');
            $table->dropColumn(['sibling_discount', 'punctuality_discount', 'other_discounts']);
        });

        // Reverter alterações em monthly_fee_installments
        Schema::table('monthly_fee_installments', function (Blueprint $table) {
            $table->decimal('base_amount', 10, 2)->after('installment_number');
            $table->decimal('sibling_discount', 10, 2)->default(0)->after('base_amount');
            $table->decimal('punctuality_discount', 10, 2)->default(0)->after('sibling_discount');
            $table->decimal('other_discounts', 10, 2)->default(0)->after('punctuality_discount');
            $table->decimal('interest_amount', 10, 2)->default(0)->after('other_discounts');
            $table->decimal('fine_amount', 10, 2)->default(0)->after('interest_amount');
            $table->decimal('final_amount', 10, 2)->after('fine_amount');
            $table->date('paid_date')->nullable()->after('due_date');
        });
    }
};
