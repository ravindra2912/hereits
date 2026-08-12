<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;

class SubscriptionCheckTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscription-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription check task for expiring plans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Subscription check start at " . date('Y-m-d H:i:s'));

        $updatedCount = Purchase::where('plan_status', 'active')
            ->where('status', 'paid')
            ->where('end_date', '<', now())
            ->update(['plan_status' => 'expired']);

        Log::info("Subscription check completed: {$updatedCount} plan(s) expired.");

        Log::info("Subscription check end at " . date('Y-m-d H:i:s'));
    }
}
