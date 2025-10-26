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
        Schema::table('enrollment_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_by_guardian_id')->nullable()->after('enrollment_id');
            $table->foreign('paid_by_guardian_id')->references('id')->on('guardians')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_payments', function (Blueprint $table) {
            $table->dropForeign(['paid_by_guardian_id']);
            $table->dropColumn('paid_by_guardian_id');
        });
    }
};
