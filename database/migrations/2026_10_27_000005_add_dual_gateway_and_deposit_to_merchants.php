<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->string('gateway_mode')->default('kurtech')->after('status'); // 'kurtech' or 'custom'
            $table->string('custom_qris_image')->nullable()->after('gateway_mode');
            $table->string('custom_nmid')->nullable()->after('custom_qris_image');
            $table->string('custom_qris_name')->nullable()->after('custom_nmid');
            $table->decimal('fee_deposit_balance', 15, 2)->default(0)->after('custom_qris_name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('gateway_mode')->default('kurtech')->after('status');
            $table->boolean('is_deposit_topup')->default(false)->after('gateway_mode');
            $table->decimal('deposit_fee_deducted', 12, 2)->default(0)->after('is_deposit_topup');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_mode',
                'custom_qris_image',
                'custom_nmid',
                'custom_qris_name',
                'fee_deposit_balance',
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_mode',
                'is_deposit_topup',
                'deposit_fee_deducted',
            ]);
        });
    }
};
