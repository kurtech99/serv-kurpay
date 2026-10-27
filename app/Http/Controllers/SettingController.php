<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function whatsappIndex()
    {
        $userId = auth()->id();

        // Ping Node JS untuk start session jika belum jalan
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 3]);
            $client->post('http://127.0.0.1:3001/start-session', [
                'json' => ['tenant_id' => $userId]
            ]);
        } catch (\Exception $e) {
            // Abaikan jika bot mati
        }

        $status = Setting::get('wa_status', 'DISCONNECTED', $userId);
        $qrCode = Setting::get('wa_qr_code', '', $userId);
        
        // Jika request via AJAX (untuk Polling QR Code)
        if (request()->ajax()) {
            return response()->json([
                'status' => $status,
                'qrCode' => $qrCode
            ]);
        }
        
        $greeting = Setting::get('wa_greeting', null, $userId);
        $homeWifiEnabled = Setting::get('wa_home_wifi_enabled', '1', $userId);
        $homeReply = Setting::get('wa_home_reply', null, $userId);
        $danaInstructions = Setting::get('wa_dana_instructions', null, $userId);
        $bcaInstructions = Setting::get('wa_bca_instructions', null, $userId);
        $successMessage = Setting::get('wa_success_message', null, $userId);

        return view('settings.whatsapp', compact('status', 'qrCode', 'greeting', 'homeWifiEnabled', 'homeReply', 'danaInstructions', 'bcaInstructions', 'successMessage'));
    }

    public function updateWhatsapp(Request $request)
    {
        $userId = auth()->id();
        
        $request->validate([
            'wa_greeting' => 'required',
            'wa_home_reply' => 'required',
            'wa_dana_instructions' => 'required',
            'wa_bca_instructions' => 'required',
            'wa_success_message' => 'required'
        ]);

        Setting::set('wa_greeting', $request->wa_greeting, $userId);
        Setting::set('wa_home_wifi_enabled', $request->has('wa_home_wifi_enabled') ? '1' : '0', $userId);
        Setting::set('wa_home_reply', $request->wa_home_reply, $userId);
        Setting::set('wa_dana_instructions', $request->wa_dana_instructions, $userId);
        Setting::set('wa_bca_instructions', $request->wa_bca_instructions, $userId);
        Setting::set('wa_success_message', $request->wa_success_message, $userId);

        return back()->with('success', 'Template chat WhatsApp berhasil disimpan.');
    }

    public function paymentIndex()
    {
        $userId = auth()->id();
        
        $active = Setting::get('payment_gateway_active', null, $userId);
        $tripayApiKey = Setting::get('tripay_api_key', null, $userId);
        $tripayPrivateKey = Setting::get('tripay_private_key', null, $userId);
        $tripayMerchantCode = Setting::get('tripay_merchant_code', null, $userId);
        $midtransServerKey = Setting::get('midtrans_server_key', null, $userId);
        $midtransClientKey = Setting::get('midtrans_client_key', null, $userId);
        $xenditApiKey = Setting::get('xendit_api_key', null, $userId);

        // Checkbox manual payment
        $danaActive = Setting::get('payment_manual_dana_active', '1', $userId);
        $bcaActive = Setting::get('payment_manual_bca_active', '1', $userId);

        // Rekening detail
        $danaNumber = Setting::get('payment_manual_dana_number', '083826164767', $userId);
        $bankBrand = Setting::get('payment_manual_bank_brand', 'BCA', $userId);
        $bankNumber = Setting::get('payment_manual_bank_number', '1234567890', $userId);
        $bankName = Setting::get('payment_manual_bank_name', 'Kurtech', $userId);

        return view('settings.payment', compact(
            'active', 'tripayApiKey', 'tripayPrivateKey', 'tripayMerchantCode',
            'midtransServerKey', 'midtransClientKey', 'xenditApiKey',
            'danaActive', 'bcaActive', 'danaNumber', 'bankBrand', 'bankNumber', 'bankName'
        ));
    }

    public function updatePayment(Request $request)
    {
        $userId = auth()->id();
        
        Setting::set('payment_gateway_active', $request->payment_gateway_active, $userId);
        Setting::set('tripay_api_key', $request->tripay_api_key, $userId);
        Setting::set('tripay_private_key', $request->tripay_private_key, $userId);
        Setting::set('tripay_merchant_code', $request->tripay_merchant_code, $userId);
        Setting::set('midtrans_server_key', $request->midtrans_server_key, $userId);
        Setting::set('midtrans_client_key', $request->midtrans_client_key, $userId);
        Setting::set('xendit_api_key', $request->xendit_api_key, $userId);

        // Update status DANA/BCA
        Setting::set('payment_manual_dana_active', $request->has('payment_manual_dana_active') ? '1' : '0', $userId);
        Setting::set('payment_manual_bca_active', $request->has('payment_manual_bca_active') ? '1' : '0', $userId);

        // Update nomor & nama rekening
        Setting::set('payment_manual_dana_number', $request->payment_manual_dana_number, $userId);
        Setting::set('payment_manual_bank_brand', $request->payment_manual_bank_brand, $userId);
        Setting::set('payment_manual_bank_number', $request->payment_manual_bank_number, $userId);
        Setting::set('payment_manual_bank_name', $request->payment_manual_bank_name, $userId);

        return back()->with('success', 'Pengaturan Payment Gateway & Manual berhasil disimpan.');
    }

    // Memutuskan koneksi WhatsApp via API Bot WA
    public function whatsappLogout()
    {
        $userId = auth()->id();
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $client->post('http://127.0.0.1:3001/logout', [
                'json' => ['tenant_id' => $userId]
            ]);
            
            // Set status terputus di database lokal
            Setting::set('wa_status', 'DISCONNECTED');
            Setting::set('wa_qr_code', '');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memutus koneksi Bot WA: ' . $e->getMessage() . '. Coba matikan session WA secara manual jika bot offline.');
        }

        return back()->with('success', 'Koneksi diputuskan. Silakan tunggu QR Code baru muncul untuk scan ulang.');
    }

    // API untuk dihubungi oleh Bot WA Node.js
    public function apiUpdateQr(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required',
            'status' => 'required',
            'qr_code' => 'nullable'
        ]);

        Setting::set('wa_status', $request->status, $request->tenant_id);
        Setting::set('wa_qr_code', $request->qr_code ?? '', $request->tenant_id);

        return response()->json(['status' => 'success']);
    }

    // API untuk menyuplai settings ke Bot WA Node.js
    public function apiGetBotSettings(Request $request)
    {
        $tenantId = $request->tenant_id;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'wa_greeting' => Setting::get('wa_greeting', null, $tenantId),
                'wa_home_wifi_enabled' => Setting::get('wa_home_wifi_enabled', '1', $tenantId),
                'wa_home_reply' => Setting::get('wa_home_reply', null, $tenantId),
                'wa_dana_instructions' => Setting::get('wa_dana_instructions', null, $tenantId),
                'wa_bca_instructions' => Setting::get('wa_bca_instructions', null, $tenantId),
                'wa_success_message' => Setting::get('wa_success_message', null, $tenantId),
                'payment_manual_dana_active' => Setting::get('payment_manual_dana_active', '1', $tenantId),
                'payment_manual_bca_active' => Setting::get('payment_manual_bca_active', '1', $tenantId),
                'payment_dana_number' => Setting::get('payment_manual_dana_number', '083826164767', $tenantId),
                'payment_bank_brand' => Setting::get('payment_manual_bank_brand', 'BCA', $tenantId),
                'payment_bank_number' => Setting::get('payment_manual_bank_number', '1234567890', $tenantId),
                'payment_bank_name' => Setting::get('payment_manual_bank_name', 'Kurtech', $tenantId),
            ]
        ]);
    }
}
