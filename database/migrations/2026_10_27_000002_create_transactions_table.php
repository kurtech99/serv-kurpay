<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id')->unique();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->nullable()->index();
            $table->string('service_name')->default('Pembelian Voucher WiFi');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Nominal
            $table->decimal('amount', 12, 2);
            $table->decimal('customer_fee', 12, 2)->default(0);
            $table->integer('unique_code')->default(0);
            $table->decimal('total_amount', 12, 2)->index();
            $table->decimal('merchant_net', 12, 2);
            
            // Status: pending, paid, expired, cancelled
            $table->string('status')->default('pending')->index();
            
            // Webhook Callback
            $table->string('callback_url')->nullable();
            $table->string('callback_status')->default('pending');
            $table->text('callback_response')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
