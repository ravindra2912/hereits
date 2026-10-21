<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppointmentBooking;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Service;
use App\Models\SiteSetting;
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
     * The console command aliases.
     *
     * @var array
     */
    protected $aliases = ['app:subscription-check-task'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription check';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Subscription check start at " . date('y-m-d-h-i-s'));

        $setting = SiteSetting::first('free_product_limit', 'free_service_limit');
        $freeProductLimit = $setting->free_product_limit ?? 20;
        $freeServiceLimit = $setting->free_service_limit ?? 10;

        $purchases = Purchase::select('id', 'business_id', 'plan_status', 'plan_type')
            ->where('plan_status', 'active')->where('status', 'paid')->where('end_date', '<', now())->get();

        foreach ($purchases as $purchase) {
            $purchase->update(['plan_status' => 'expired']);

            $businessSetting = BusinessSetting::select('id', 'business_id', 'subscription_expiry_date', 'product_limit', 'product_limit_expiry_date', 'service_limit', 'service_limit_expiry_date')
                ->where('business_id', $purchase->business_id)->first();

            // if ($purchase->plan_type == 'subscription') {
            //     if ($businessSetting) {
            //         $businessSetting->update(['subscription_expiry_date' => null]);
            //     }
            // }
            if ($purchase->plan_type == 'product') {
                $productIds = Product::where('business_id', $purchase->business_id)
                    ->where('status', 'active')
                    ->orderBy('id', 'asc')
                    ->skip($freeProductLimit)
                    ->take(PHP_INT_MAX)
                    ->pluck('id');

                if ($productIds->isNotEmpty()) {
                    Product::whereIn('id', $productIds)->update(['status' => 'in-active-expiry']);
                    Product::whereIn('parent_product_id', $productIds)
                        ->where('share_type', '!=', 'copied')
                        ->update(['status' => 'in-active-expiry']);
                }

                if ($businessSetting) {
                    $businessSetting->update([
                        'product_limit' => $freeProductLimit,
                    ]);
                }
            } elseif ($purchase->plan_type == 'service') {
                $serviceIds = Service::where('business_id', $purchase->business_id)
                    ->where('status', 'active')
                    ->orderBy('id', 'asc')
                    ->skip($freeServiceLimit)
                    ->take(PHP_INT_MAX)
                    ->pluck('id');

                if ($serviceIds->isNotEmpty()) {
                    Service::whereIn('id', $serviceIds)->update(['status' => 'in-active-expiry']);
                }

                if ($businessSetting) {
                    $businessSetting->update([
                        'service_limit' => $freeServiceLimit,
                    ]);
                }
            }
        }

        Log::info("Subscription check end at " . date('y-m-d-h-i-s'));
    }
}
