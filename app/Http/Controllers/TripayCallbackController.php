<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VpnTransaction;
use App\Services\TripayService;
use App\Services\VpnService;
use App\Models\ActivityLog;

class TripayCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $tripayService = new TripayService();
        $result = $tripayService->handleCallback($request);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        $trx = VpnTransaction::where('trx_id', $result['trx_id'])->first();
        
        if (!$trx) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if ($trx->status === 'completed') {
            return response()->json(['success' => true, 'message' => 'Already paid']);
        }

        if ($result['status'] === 'completed') {
            // Provision the VPN account & ports using centralized service
            $provisionResult = VpnService::provision($trx, 30);
            
            if (!$provisionResult['status']) {
                \Log::error('Tripay Callback Provisioning Failed for Trx: ' . $trx->trx_id . ' Error: ' . $provisionResult['message']);
                return response()->json(['success' => false, 'message' => $provisionResult['message']], 500);
            }

            ActivityLog::create([
                'user_id' => $trx->user_id,
                'category' => 'VPN Remote',
                'action' => 'Pembuatan Akun VPN Otomatis (Tripay)',
                'details' => "Akun VPN {$trx->requested_username} dan {$trx->requested_ports} port berhasil dibuat. Status pembayaran: PAID."
            ]);
        }

        return response()->json(['success' => true]);
    }
}
