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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->string('reference')->nullable(); // Ex: "JUNHO - 2025"
            $table->enum('status', ['draft', 'processing', 'completed'])->default('draft');
            $table->decimal('total_gross', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2)->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
