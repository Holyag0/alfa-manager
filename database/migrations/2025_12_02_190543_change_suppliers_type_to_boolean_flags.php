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
        Schema::table('suppliers', function (Blueprint $table) {
            // Adicionar novos campos booleanos
            $table->boolean('is_pagante')->default(false)->after('id');
            $table->boolean('is_fornecedor')->default(false)->after('is_pagante');
        });

        // Migrar dados existentes
        DB::table('suppliers')->where('type', 'pagador')->update([
            'is_pagante' => true,
            'is_fornecedor' => false
        ]);

        DB::table('suppliers')->where('type', 'fornecedor')->update([
            'is_pagante' => false,
            'is_fornecedor' => true
        ]);

        // Remover campo antigo e índices
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });

        // Adicionar novos índices
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('is_pagante');
            $table->index('is_fornecedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Recriar campo type
            $table->enum('type', ['pagador', 'fornecedor'])->after('id');
        });

        // Migrar dados de volta
        DB::table('suppliers')->where('is_pagante', true)->where('is_fornecedor', false)->update(['type' => 'pagador']);
        DB::table('suppliers')->where('is_pagante', false)->where('is_fornecedor', true)->update(['type' => 'fornecedor']);
        DB::table('suppliers')->where('is_pagante', true)->where('is_fornecedor', true)->update(['type' => 'pagador']); // Default para pagador se ambos

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['is_pagante']);
            $table->dropIndex(['is_fornecedor']);
            $table->dropColumn(['is_pagante', 'is_fornecedor']);
            $table->index('type');
        });
    }
};
