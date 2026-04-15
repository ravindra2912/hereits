<?php

namespace App\Traits;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

trait ManageListingLimits
{
    /**
     * Get the effective limit for a specific type (product or service).
     *
     * @param int $business_id
     * @param string $type 'product' or 'service'
     * @return int
     */
    public function getEffectiveLimit($business_id, $type)
    {
        $settings = getBusinessSettings($business_id);

        $limitField = $type . '_limit';
        $expiryField = $type . '_limit_expiry_date';

        // Ensure properties exist on the standardized object
        $limit = property_exists($settings, $limitField) ? $settings->$limitField : 0;
        $expiry = property_exists($settings, $expiryField) ? $settings->$expiryField : null;

        // If expired or never set, use free limit
        if ($expiry == null || Carbon::parse($expiry)->isPast()) {
            $siteSettingField = 'free_' . $type . '_limit';
            $site_setting = SiteSetting::first([$siteSettingField]);
            $limit = $site_setting ? $site_setting->$siteSettingField : 0;
        }

        return $limit;
    }

    /**
     * Check if business has reached its limit.
     * 
     * @param int $business_id
     * @param string $modelClass The model class (Product::class or Service::class)
     * @param string $type 'product' or 'service'
     * @return bool|string Returns error message if limit reached, false otherwise.
     */
    public function checkListingLimit($business_id, $modelClass, $type)
    {
        $limit = $this->getEffectiveLimit($business_id, $type);
        $currentCount = $modelClass::where('business_id', $business_id)->count();

        if ($currentCount >= $limit) {
            return 'You have reached your ' . $type . ' listing limit of ' . $limit . '. Please buy more limit.';
        }

        return false;
    }
}
