<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Merchant;

class QrisService
{
    /**
     * Default Official Kurtech Static QRIS Payload (DANA Bisnis)
     */
    public const DEFAULT_KURTECH_PAYLOAD = '00020101021126570011ID.DANA.WWW011893600915393758465102099375846510303UMI51440014ID.CO.QRIS.WWW0215ID10254147450140303UMI5204737253033605802ID5907kurtech6011Kab. Bekasi61051751063048F7F';

    /**
     * Calculate CRC16-CCITT for EMVCo QR Code standard
     */
    public static function crc16(string $str): string
    {
        $crc = 0xFFFF;
        for ($c = 0; $c < strlen($str); $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Parse EMVCo TLV (Tag-Length-Value) QRIS string
     */
    public static function parse(string $payload): array
    {
        $payload = trim($payload);
        $pos = 0;
        $len = strlen($payload);
        $tags = [];

        while ($pos < $len) {
            if ($pos + 4 > $len) break;
            $tag = substr($payload, $pos, 2);
            $valLen = intval(substr($payload, $pos + 2, 2));
            $val = substr($payload, $pos + 4, $valLen);
            $tags[$tag] = $val;
            $pos += 4 + $valLen;
        }

        return $tags;
    }

    /**
     * Convert any Static or Dynamic QRIS payload into an Auto-Nominal Dynamic QRIS (like TriPay)
     */
    public static function makeDynamic(?string $staticPayload, float|int $amount): string
    {
        $base = trim($staticPayload ?? '');
        if (empty($base) || strlen($base) < 20 || !str_starts_with($base, '000201')) {
            $base = Setting::get('qris_kurtech_payload', self::DEFAULT_KURTECH_PAYLOAD);
        }

        $tags = self::parse($base);

        // 1. Point of Initiation Method: 12 (Dynamic QR)
        $tags['01'] = '12';

        // 2. Transaction Amount: Tag 54
        $amountStr = (string) (int) round($amount);
        $tags['54'] = $amountStr;

        // 3. Remove existing CRC (Tag 63)
        unset($tags['63']);

        // Standard EMVCo tag order
        $orderedTagKeys = [
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
            '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36',
            '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47',
            '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58',
            '59', '60', '61', '62'
        ];

        $reconstructed = '';
        foreach ($orderedTagKeys as $k) {
            if (isset($tags[$k])) {
                $val = $tags[$k];
                $len = sprintf('%02d', strlen($val));
                $reconstructed .= $k . $len . $val;
                unset($tags[$k]);
            }
        }

        // Append any unexpected extra tags
        foreach ($tags as $k => $val) {
            $len = sprintf('%02d', strlen($val));
            $reconstructed .= $k . $len . $val;
        }

        // Append Tag 63 with length 04 and calculate CRC16
        $toCrc = $reconstructed . '6304';
        $crc = self::crc16($toCrc);

        return $toCrc . $crc;
    }

    /**
     * Generate Dynamic QRIS string for a specific Transaction
     */
    public static function generateForTransaction(Transaction $trx): string
    {
        $merchant = $trx->merchant;

        // Top up deposit fee selalu wajib menggunakan QRIS resmi Kurtech
        if (!$trx->is_deposit_topup && $trx->gateway_mode === 'custom' && $merchant && $merchant->isCustomGateway() && !empty($merchant->custom_qris_payload)) {
            $basePayload = $merchant->custom_qris_payload;
        } else {
            $basePayload = Setting::get('qris_kurtech_payload', self::DEFAULT_KURTECH_PAYLOAD);
        }

        return self::makeDynamic($basePayload, $trx->total_amount);
    }
}
