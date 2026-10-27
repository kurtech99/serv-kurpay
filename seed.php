<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Package;
use Carbon\Carbon;

$customer = Customer::first() ?? Customer::create(['phone' => '08123456789']);
$package = Package::first() ?? Package::create(['name' => 'Paket Dummy', 'price' => 5000, 'mikrotik_profile' => 'default']);

for ($i = 6; $i >= 1; $i--) { // Past 6 days
    $date = Carbon::now()->subDays($i);
    
    // Create 1-3 random transactions per day
    $trxCount = rand(1, 3);
    for ($j = 0; $j < $trxCount; $j++) {
        Transaction::create([
            'trx_id' => 'SIMUL-' . $date->format('Ymd') . '-' . rand(1000, 9999),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'amount' => rand(2, 5) * 2000, // 4000, 6000, 8000, 10000
            'payment_method' => ['qris', 'dana', 'bca'][rand(0, 2)],
            'status' => 'paid',
            'created_at' => $date->copy()->addHours(rand(8, 20)),
            'updated_at' => $date->copy()->addHours(rand(8, 20)),
        ]);
    }
}
echo "Seeded dummy transactions for past 7 days!\n";
