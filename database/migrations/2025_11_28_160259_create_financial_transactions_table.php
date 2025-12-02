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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();
            $table->enum('type', ['receita', 'despesa']);
            $table->foreignId('category_id')->constrained('financial_categories');
            
            // Referências polimórficas
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->date('transaction_date');
            
            $table->enum('payment_method', ['pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer', 'check', 'other'])->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('type');
            $table->index('transaction_date');
            $table->index('status');
            $table->index('category_id');
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
