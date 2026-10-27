<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->text('custom_qris_payload')->nullable()->after('custom_nmid');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('qr_string')->nullable()->after('gateway_mode');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['custom_qris_payload']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['qr_string']);
        });
    }
};
