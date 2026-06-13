<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Business;
use App\Models\Purchase;
use App\Models\Transactions;

$business = Business::first();
if (!$business) {
    echo "No business found\n";
    exit;
}

$purchase = Purchase::create([
    'business_id' => $business->id,
    'plan_id' => null,
    'plan_type' => 'subscription',
    'subtotal' => 100,
    'total_amount' => 100,
    'status' => 'pending',
    'plan_status' => 'pending'
]);

$insert = new Transactions();
$insert->business_id = $purchase->business_id;
$insert->purchase_id = $purchase->id;
$insert->amount = $purchase->total_amount;
$insert->payment_type = 'online';
$insert->transaction_date = now();
$insert->payment_id = 'test_pay_' . time();
$insert->status = 'paid';
$insert->save();

echo "Transaction ID generated: " . $insert->id . "\n";

Purchase::where('business_id', $purchase->business_id)
    ->where('plan_status', 'active')
    ->where('plan_type', $purchase->plan_type)
    ->update([
        'plan_status' => 'override',
    ]);

$purchase->update([
    'transaction_id' => $insert->id,
    'status' => 'paid',
    'plan_status' => 'active',
]);

$purchase->refresh();
echo "Purchase Transaction ID after update: " . $purchase->transaction_id . "\n";
