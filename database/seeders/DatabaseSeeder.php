<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SuperAdmin Kurtech
        $admin = User::updateOrCreate(
            ['username' => 'admin@kurtech'],
            [
                'name' => 'Super Admin Kurtech',
                'username' => 'admin@kurtech',
                'email' => 'admin@kurtech',
                'password' => Hash::make('999Bekasi@'),
                'role' => 'superadmin',
                'phone' => '083826164767',
                'status' => 'active',
            ]
        );

        // 2. Demo Merchant
        $tenantUser = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Mitra Tenant',
                'username' => 'admin',
                'email' => 'admin@kurpay',
                'password' => Hash::make('kurpay1234'),
                'role' => 'merchant',
                'phone' => '083826164767',
                'status' => 'active',
            ]
        );

        $merchant = Merchant::updateOrCreate(
            ['user_id' => $tenantUser->id],
            [
                'merchant_code' => 'KP-DEMO01',
                'business_name' => 'Berkah WiFi Network',
                'api_key' => 'kp_live_demo1234567890abcdef',
                'secret_key' => 'kp_sec_demo1234567890abcdef',
                'balance' => 0,
                'bank_name' => 'Bank BCA',
                'bank_account' => '7285513366',
                'bank_holder' => 'ANGGI KURNIAWAN',
                'auto_withdraw_enabled' => false,
                'auto_withdraw_target' => 50000,
                'auto_withdraw_weekend' => false,
                'status' => 'active',
            ]
        );

        // 3. Default Gateway Settings
        $defaults = [
            'customer_fee_percent' => '0.3',
            'withdraw_min' => '50000',
            'withdraw_max' => '500000',
            'withdraw_fee' => '10000',
            'withdraw_open_time' => '08:00',
            'withdraw_close_time' => '14:00',
            'dana_secret' => 'kurtech_dana_2026',
            'merchant_qris_name' => 'kurtech',
            'merchant_nmid' => 'ID1025414745014',
            'admin_whatsapp' => '6283826164767',
        ];

        foreach ($defaults as $k => $v) {
            Setting::set($k, $v);
        }
    }
}
