<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckExpiredVpnAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vpn:check-expired';
    protected $description = 'Check and disable expired VPN accounts in DB and MikroTik';

    public function handle()
    {
        $expiredAccounts = \App\Models\VpnAccount::where('status', 'active')
            ->where('expired_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredAccounts as $account) {
            $server = $account->server;
            if ($server) {
                // Disable on Mikrotik and disconnect active session
                \App\Services\MikrotikService::disableVpnAccount($server, $account->username);
            }

            // Update status to expired
            $account->update(['status' => 'expired']);
            $count++;
        }

        $this->info("Successfully disabled {$count} expired VPN accounts.");
    }
}
