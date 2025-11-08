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
        // Popular academic_year nas matrículas existentes
        // Usar o ano da data de matrícula ou o ano atual
        DB::table('enrollments')->whereNull('academic_year')->update([
            'academic_year' => DB::raw("COALESCE(YEAR(enrollment_date), YEAR(CURDATE()))")
        ]);
        
        // Log da operação
        $count = DB::table('enrollments')->whereNotNull('academic_year')->count();
        \Log::info("Academic year populated for {$count} enrollments");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não é necessário reverter, pois a coluna será removida pela migration anterior
    }
};


