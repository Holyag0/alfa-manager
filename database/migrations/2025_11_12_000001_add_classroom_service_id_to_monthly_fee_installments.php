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
            // Adicionar referência ao serviço para rastreabilidade
            $table->foreignId('classroom_service_id')
                  ->after('monthly_fee_id')
                  ->nullable()
                  ->constrained('classroom_services')
                  ->onDelete('restrict');
            
            // Adicionar índice para consultas rápidas
            $table->index('classroom_service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_fee_installments', function (Blueprint $table) {
            $table->dropForeign(['classroom_service_id']);
            $table->dropIndex(['classroom_service_id']);
            $table->dropColumn('classroom_service_id');
        });
    }
};

