<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TripayService
{
    private $apiKey;
    private $privateKey;
    private $merchantCode;
    private $isProduction;

    public function __construct()
    {
        $this->apiKey = trim(Setting::get('saas_tripay_api_key', '') ?: (Setting::get('tripay_api_key', '') ?: (env('TRIPAY_API_KEY', '') ?: 'st9S81BeOMeYC8Qu0Lafc14xl3o6HbXdgCNzmOoa')));
        $this->privateKey = trim(Setting::get('saas_tripay_private_key', '') ?: (Setting::get('tripay_private_key', '') ?: (env('TRIPAY_PRIVATE_KEY', '') ?: '2OrGv-u5WiO-9YnDy-WMlBv-syMwS')));
        $this->merchantCode = trim(Setting::get('saas_tripay_merchant_code', '') ?: (Setting::get('tripay_merchant_code', '') ?: (env('TRIPAY_MERCHANT_CODE', '') ?: 'T53399')));
        
        $envSetting = strtolower(Setting::get('saas_tripay_environment', '') ?: Setting::get('tripay_environment', 'production'));
        $this->isProduction = ($envSetting !== 'sandbox'); 
    }

    public function getBaseUrl()
    {
        return $this->isProduction 
            ? 'https://tripay.co.id/api' 
            : 'https://tripay.co.id/api-sandbox';
    }

    public function getChannels()
    {
        static $staticChannels = null;
        if ($staticChannels !== null) {
            return $staticChannels;
        }

        try {
            $channels = Cache::remember('tripay_channels_list_' . ($this->isProduction ? 'prod' : 'sand'), 86400, function () {
                $response = Http::withoutVerifying()->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey
                ])->timeout(3)->get($this->getBaseUrl() . '/merchant/payment-channel');

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['success']) && !empty($json['data'])) {
                        return $json['data'];
                    }
                }
                return null;
            });

            if (!empty($channels)) {
                $staticChannels = $channels;
                return $channels;
            }
        } catch (\Exception $e) {
            Log::error('Tripay getChannels Exception: ' . $e->getMessage());
        }

        // Standard active channel fallback (matching billing portal)
        return [
            [
                'code' => 'QRIS2',
                'name' => 'QRIS (Semua Bank & E-Wallet)',
                'group' => 'E-Wallet',
                'type' => 'qr',
                'icon_url' => asset('images/qris.svg'),
                'active' => true,
                'badge' => 'Rekomendasi (Instant)'
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'group' => 'E-Wallet',
                'type' => 'direct',
                'icon_url' => asset('images/dana.svg'),
                'active' => true,
                'badge' => ''
            ],
            [
                'code' => 'OVO',
                'name' => 'OVO',
                'group' => 'E-Wallet',
                'type' => 'direct',
                'icon_url' => asset('images/ovo.svg'),
                'active' => true,
                'badge' => ''
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'group' => 'E-Wallet',
                'type' => 'direct',
                'icon_url' => asset('images/shopeepay.svg'),
                'active' => true,
                'badge' => ''
            ],
        ];
    }

    public function requestClosedPayment($trx, $method = 'QRIS2')
    {
        // Auto-resolve QRIS variant (QRIS2 / QRIS / QRISC) based on active Tripay channels
        if (in_array(strtoupper($method), ['QRIS', 'QRIS2', 'QRISC'])) {
            $channels = $this->getChannels() ?? [];
            $channelCodes = array_map(function($c) {
                return strtoupper($c['code'] ?? '');
            }, $channels);

            if (!empty($channelCodes)) {
                if (!in_array(strtoupper($method), $channelCodes)) {
                    if (in_array('QRIS2', $channelCodes)) {
                        $method = 'QRIS2';
                    } elseif (in_array('QRIS', $channelCodes)) {
                        $method = 'QRIS';
                    } elseif (in_array('QRISC', $channelCodes)) {
                        $method = 'QRISC';
                    }
                }
            } else {
                if ($method === 'QRIS') {
                    $method = 'QRIS2';
                }
            }
        }
        $user = $trx->user;
        $amount = (int) ($trx->amount);
        $merchantRef = $trx->trx_id;

        if ($amount < 1000) {
            session()->flash('tripay_last_error', 'Nominal transaksi minimal Rp 1.000 untuk Tripay.');
            return null;
        }

        $signature = hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);

        $name = !empty($user->name) ? trim($user->name) : 'Pelanggan Kurtech';
        $email = $user->email ?? 'customer@kurtech.my.id';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'customer@kurtech.my.id';
        }

        $rawPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '083826164767');
        if (substr($rawPhone, 0, 2) === '62' && strlen($rawPhone) >= 11) {
            $phone = '0' . substr($rawPhone, 2);
        } elseif (substr($rawPhone, 0, 2) === '08' && strlen($rawPhone) >= 10) {
            $phone = $rawPhone;
        } else {
            $phone = '083826164767';
        }

        $serverDomain = request()->getHost() ?: 'remote.kurtech.my.id';
        $protocol = request()->secure() ? 'https://' : 'http://';
        $callbackUrl = $protocol . $serverDomain . '/api/callback/tripay';
        $returnUrl = route('vpn.payment', $trx->trx_id);

        $pkgName = 'Layanan VPN Remote ' . ($trx->requested_ports ?: 1) . ' Port (' . ($trx->duration_months ?: 1) . ' Bulan)';

        $data = [
            'method'         => $method,
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'callback_url'   => $callbackUrl,
            'return_url'     => $returnUrl,
            'order_items'    => [
                [
                    'sku'      => 'VPN',
                    'name'     => $pkgName,
                    'price'    => $amount,
                    'quantity' => 1
                ]
            ],
            'expired_time' => (time() + 86400),
            'signature'    => $signature
        ];

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json'
            ])->timeout(15)->post($this->getBaseUrl() . '/transaction/create', $data);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success']) && !empty($json['data'])) {
                    session()->forget('tripay_last_error');
                    return $json['data'];
                }
            }

            $errMsg = $response->json('message') ?? ($response->body() ?: 'Server Tripay tidak merespon.');
            Log::error('Tripay Create Transaction Error: ' . $errMsg);
            session()->flash('tripay_last_error', $errMsg);
            return null;
        } catch (\Exception $e) {
            Log::error('Tripay Request Exception: ' . $e->getMessage());
            session()->flash('tripay_last_error', $e->getMessage());
            return null;
        }
    }

    public function handleCallback($request)
    {
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, $this->privateKey);

        if ($signature !== $request->header('X-Callback-Signature')) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $data = json_decode($json);

        if ($data && ($data->status === 'PAID')) {
            return [
                'success' => true,
                'trx_id' => $data->merchant_ref,
                'status' => 'completed'
            ];
        }

        return ['success' => false, 'message' => 'Not paid event'];
    }

    public function checkStatusFromTripay($trx)
    {
        $ref = '';
        if (!empty($trx->checkout_url)) {
            $ref = basename(parse_url($trx->checkout_url, PHP_URL_PATH));
        } elseif (!empty($trx->qr_url)) {
            $ref = basename(parse_url($trx->qr_url, PHP_URL_PATH));
        }

        if (empty($ref)) {
            return null;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->timeout(4)->get($this->getBaseUrl() . '/transaction/detail', [
                'reference' => $ref
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success']) && !empty($json['data']['status'])) {
                    return strtoupper($json['data']['status']);
                }
            }
        } catch (\Exception $e) {
            Log::error('Tripay checkStatusFromTripay Error: ' . $e->getMessage());
        }

        return null;
    }
}
