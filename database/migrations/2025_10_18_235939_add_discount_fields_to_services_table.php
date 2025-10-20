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
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('has_discount')->default(false)->after('price'); // Se tem desconto ativo
            $table->decimal('discount_amount', 10, 2)->default(0)->after('has_discount'); // Valor do desconto em reais (apenas valor fixo)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['has_discount', 'discount_amount']);
        });
    }
};
