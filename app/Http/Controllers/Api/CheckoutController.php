<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|string'
        ]);

        $package = Package::find($request->package_id);

        $tenant = $package->user;
        if ($tenant && !$tenant->canGenerateVoucher()) {
            return response()->json([
                'success' => false,
                'message' => 'Mohon maaf, sistem jaringan kami sedang penuh/perbaikan. Silakan hubungi Admin.'
            ], 403);
        }

        $customer = Customer::firstOrCreate(
            ['phone' => $request->customer_phone],
            ['name' => $request->customer_name]
        );

        $trxId = 'TRX-' . strtoupper(Str::random(10));

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'trx_id' => $trxId,
            'amount' => $package->price,
            'payment_method' => $request->payment_method,
            'status' => 'pending'
        ]);

        $activeGateway = \App\Models\Setting::get('payment_gateway_active', 'tripay');

        if ($activeGateway === 'midtrans') {
            $serverKey = \App\Models\Setting::get('midtrans_server_key');

            if (empty($serverKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Midtrans Server Key is not configured'
                ], 500);
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $trxId,
                    'gross_amount' => $package->price,
                ],
                'customer_details' => [
                    'first_name' => $customer->name,
                    'phone' => $customer->phone,
                ]
            ];

            try {
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                    ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $payload);
                
                $resData = $response->json();

                if ($response->successful() && isset($resData['redirect_url'])) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Invoice created successfully via Midtrans',
                        'data' => [
                            'trx_id' => $trxId,
                            'amount' => $package->price,
                            'payment_url' => $resData['redirect_url']
                        ]
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create Midtrans transaction',
                        'error' => $resData
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Midtrans: ' . $e->getMessage()
                ], 500);
            }
        }

        // Fallback for Tripay or other gateways (dummy for now)
        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => [
                'trx_id' => $trxId,
                'amount' => $package->price,
                'payment_url' => 'https://sandbox.tripay.co.id/checkout/' . $trxId
            ]
        ]);
    }
}
