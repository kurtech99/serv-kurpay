<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('withdrawal_id')->unique();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            
            // Nominal
            $table->decimal('amount_requested', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(10000);
            $table->decimal('amount_transferred', 12, 2);
            
            // Rekening Tujuan
            $table->string('bank_target');
            $table->string('account_target');
            $table->string('holder_target');
            
            // Status: pending, completed, rejected
            $table->string('status')->default('pending')->index();
            $table->string('proof_receipt')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_auto')->default(false);
            
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
