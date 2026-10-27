<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use RouterOS\Query;

class SetupMikrotikCommand extends Command
{
    protected $signature = 'mikrotik:setup';
    protected $description = 'Setup Walled Garden on MikroTik via API';

    public function handle(MikrotikService $mikrotikService)
    {
        $this->info('Connecting to MikroTik...');
        
        $client = $mikrotikService->getClient();
        if (!$client) {
            $this->error('Failed to connect to MikroTik. Please check your .env credentials and sockets extension.');
            return;
        }

        $this->info('Successfully connected! Adding Walled Garden rules...');

        $rules = [
            ['dst-host' => '*.tripay.co.id', 'comment' => 'Tripay Gateway'],
            ['dst-host' => '*.xendit.co', 'comment' => 'Xendit Gateway'],
            ['dst-host' => 'api.tripay.co.id'],
            ['dst-host' => 'tripay.co.id']
        ];

        foreach ($rules as $rule) {
            $query = (new Query('/ip/hotspot/walled-garden/add'))
                ->equal('action', 'allow');
                
            if (isset($rule['dst-host'])) {
                $query->equal('dst-host', $rule['dst-host']);
            }
            if (isset($rule['comment'])) {
                $query->equal('comment', $rule['comment']);
            }

            try {
                $client->query($query)->read();
                $this->info('Added rule for: ' . ($rule['dst-host'] ?? 'IP'));
            } catch (\Exception $e) {
                $this->error('Failed adding rule: ' . $e->getMessage());
            }
        }

        $this->info('Walled Garden setup completed!');
    }
}
