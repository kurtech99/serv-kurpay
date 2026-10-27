<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('merchant_code')->unique();
            $table->string('business_name');
            $table->string('api_key', 64)->unique();
            $table->string('secret_key', 64);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('webhook_url')->nullable();
            
            // Rekening Pencairan
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_holder')->nullable();

            // Pengaturan Penarikan Otomatis (Ambang Batas & Weekend)
            $table->boolean('auto_withdraw_enabled')->default(false);
            $table->decimal('auto_withdraw_target', 12, 2)->default(50000);
            $table->boolean('auto_withdraw_weekend')->default(false);

            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
