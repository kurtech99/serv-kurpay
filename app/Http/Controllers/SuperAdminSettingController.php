<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SuperAdminSettingController extends Controller
{
    public function paymentIndex()
    {
        $midtransServerKey = Setting::get('saas_midtrans_server_key', '');
        $midtransClientKey = Setting::get('saas_midtrans_client_key', '');
        
        $tripayApiKey = Setting::get('saas_tripay_api_key', '');
        $tripayPrivateKey = Setting::get('saas_tripay_private_key', '');
        $tripayMerchantCode = Setting::get('saas_tripay_merchant_code', '');
        $tripayEnvironment = Setting::get('saas_tripay_environment', 'production');

        $xenditSecretKey = Setting::get('saas_xendit_secret_key', '');

        $ipaymuVa = Setting::get('saas_ipaymu_va', '');
        $ipaymuApiKey = Setting::get('saas_ipaymu_api_key', '');

        $gatewayActive = Setting::get('saas_payment_gateway_active', 'none');

        $manualDanaActive = Setting::get('saas_payment_manual_dana_active', '1');
        $manualBankActive = Setting::get('saas_payment_manual_bank_active', '1');
        $manualNumber = Setting::get('saas_payment_manual_number', '083826164767');
        $manualBank = Setting::get('saas_payment_manual_bank', 'BCA');
        $manualAccount = Setting::get('saas_payment_manual_account', '1234567890');
        $manualName = Setting::get('saas_payment_manual_name', 'Kurtech');

        return view('superadmin.settings.payment', compact(
            'midtransServerKey', 'midtransClientKey', 'tripayApiKey', 'tripayPrivateKey', 'tripayMerchantCode', 'tripayEnvironment',
            'xenditSecretKey', 'ipaymuVa', 'ipaymuApiKey', 'gatewayActive',
            'manualDanaActive', 'manualBankActive', 'manualNumber', 'manualBank', 'manualAccount', 'manualName'
        ));
    }

    public function updatePayment(Request $request)
    {
        Setting::set('saas_midtrans_server_key', $request->saas_midtrans_server_key);
        Setting::set('saas_midtrans_client_key', $request->saas_midtrans_client_key);
        
        Setting::set('saas_tripay_api_key', $request->saas_tripay_api_key);
        Setting::set('saas_tripay_private_key', $request->saas_tripay_private_key);
        Setting::set('saas_tripay_merchant_code', $request->saas_tripay_merchant_code);
        Setting::set('saas_tripay_environment', $request->saas_tripay_environment ?? 'production');

        Setting::set('saas_xendit_secret_key', $request->saas_xendit_secret_key);

        Setting::set('saas_ipaymu_va', $request->saas_ipaymu_va);
        Setting::set('saas_ipaymu_api_key', $request->saas_ipaymu_api_key);

        Setting::set('saas_payment_gateway_active', $request->saas_payment_gateway_active ?? 'none');

        Setting::set('saas_payment_manual_dana_active', $request->has('saas_payment_manual_dana_active') ? '1' : '0');
        Setting::set('saas_payment_manual_bank_active', $request->has('saas_payment_manual_bank_active') ? '1' : '0');
        Setting::set('saas_payment_manual_number', $request->saas_payment_manual_number);
        Setting::set('saas_payment_manual_bank', $request->saas_payment_manual_bank);
        Setting::set('saas_payment_manual_account', $request->saas_payment_manual_account);
        Setting::set('saas_payment_manual_name', $request->saas_payment_manual_name);

        return back()->with('success', 'Pengaturan Payment Gateway SAAS berhasil disimpan.');
    }

    public function appIndex()
    {
        $appName = Setting::get('app_name', 'HOTSPOT');
        $appLogo = Setting::get('app_logo', '');

        return view('superadmin.settings.app', compact('appName', 'appLogo'));
    }

    public function updateApp(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Setting::set('app_name', $request->app_name);

        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('logos', 'public');
            Setting::set('app_logo', $path);
        }

        return back()->with('success', 'Pengaturan Aplikasi berhasil disimpan.');
    }

    public function smtpIndex()
    {
        $smtpHost = Setting::get('smtp_host', env('MAIL_HOST', '127.0.0.1'));
        $smtpPort = Setting::get('smtp_port', env('MAIL_PORT', '2525'));
        $smtpUsername = Setting::get('smtp_username', env('MAIL_USERNAME', ''));
        $smtpPassword = Setting::get('smtp_password', env('MAIL_PASSWORD', ''));
        $smtpEncryption = Setting::get('smtp_encryption', env('MAIL_ENCRYPTION', 'tls'));
        $smtpFromAddress = Setting::get('smtp_from_address', env('MAIL_FROM_ADDRESS', 'no-reply@vpnkurtech.com'));
        $smtpFromName = Setting::get('smtp_from_name', env('MAIL_FROM_NAME', 'VPN KURTECH'));

        return view('superadmin.settings.smtp', compact(
            'smtpHost', 'smtpPort', 'smtpUsername', 'smtpPassword',
            'smtpEncryption', 'smtpFromAddress', 'smtpFromName'
        ));
    }

    public function updateSmtp(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|string',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
            'smtp_from_address' => 'required|email',
            'smtp_from_name' => 'required|string',
        ]);

        Setting::set('smtp_host', $request->smtp_host);
        Setting::set('smtp_port', $request->smtp_port);
        Setting::set('smtp_username', $request->smtp_username);
        Setting::set('smtp_password', $request->smtp_password);
        Setting::set('smtp_encryption', $request->smtp_encryption);
        Setting::set('smtp_from_address', $request->smtp_from_address);
        Setting::set('smtp_from_name', $request->smtp_from_name);

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $env = file_get_contents($envPath);

            $env = preg_replace('/^MAIL_MAILER=.*$/m', 'MAIL_MAILER=smtp', $env);
            $env = preg_replace('/^MAIL_HOST=.*$/m', 'MAIL_HOST=' . $request->smtp_host, $env);
            $env = preg_replace('/^MAIL_PORT=.*$/m', 'MAIL_PORT=' . $request->smtp_port, $env);
            $env = preg_replace('/^MAIL_USERNAME=.*$/m', 'MAIL_USERNAME=' . $request->smtp_username, $env);
            $env = preg_replace('/^MAIL_PASSWORD=.*$/m', 'MAIL_PASSWORD=' . $request->smtp_password, $env);
            $env = preg_replace('/^MAIL_ENCRYPTION=.*$/m', 'MAIL_ENCRYPTION=' . $request->smtp_encryption, $env);
            $env = preg_replace('/^MAIL_FROM_ADDRESS=.*$/m', 'MAIL_FROM_ADDRESS="' . $request->smtp_from_address . '"', $env);
            $env = preg_replace('/^MAIL_FROM_NAME=.*$/m', 'MAIL_FROM_NAME="' . $request->smtp_from_name . '"', $env);

            file_put_contents($envPath, $env);
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        }

        return back()->with('success', 'Pengaturan SMTP (Email) berhasil disimpan.');
    }
}
