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
        Schema::table('guardians', function (Blueprint $table) {
            $table->foreignId('sibling_group_id')->nullable()->constrained('sibling_groups')->onDelete('set null');
            $table->index('sibling_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            $table->dropForeign(['sibling_group_id']);
            $table->dropIndex(['sibling_group_id']);
            $table->dropColumn('sibling_group_id');
        });
    }
};
