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
        // Remover o índice existente antes de criar a constraint única
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
        });

        // Criar constraint única parcial onde source_type e source_id não são null
        // Compatível com MySQL 8.0+ e PostgreSQL
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL/MariaDB: criar índice único usando coluna virtual gerada
            // Isso permite constraint única parcial mesmo quando valores são null
            DB::statement('
                ALTER TABLE financial_transactions 
                ADD COLUMN source_unique_key VARCHAR(255) AS (
                    CASE 
                        WHEN source_type IS NOT NULL AND source_id IS NOT NULL 
                        THEN CONCAT(source_type, "-", source_id) 
                        ELSE NULL 
                    END
                ) STORED,
                ADD UNIQUE INDEX financial_transactions_source_unique (source_unique_key)
            ');
        } else {
            // PostgreSQL suporta índices únicos parciais diretamente
            DB::statement('
                CREATE UNIQUE INDEX financial_transactions_source_unique 
                ON financial_transactions (source_type, source_id) 
                WHERE source_type IS NOT NULL AND source_id IS NOT NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Remover coluna virtual e índice único
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->dropIndex('financial_transactions_source_unique');
            });
            DB::statement('ALTER TABLE financial_transactions DROP COLUMN source_unique_key');
        } else {
            // Remover a constraint única (PostgreSQL)
            DB::statement('DROP INDEX IF EXISTS financial_transactions_source_unique');
        }

        // Recriar o índice normal
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->index(['source_type', 'source_id']);
        });
    }
};
