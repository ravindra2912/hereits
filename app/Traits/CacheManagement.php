<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheManagement
{
    /**
     * Clear all cached frontend database queries for a specific business.
     *
     * @param int|string $businessId
     * @return void
     */
    public function clearBusinessProductCache($businessId)
    {
        $this->clearBusinessCustomeHomeSectionCache($businessId);
    }

    public function clearBusinessServiceCache($businessId)
    {
        $this->clearBusinessCustomeHomeSectionCache($businessId);
    }

    public function clearBusinessExpertCache($businessId)
    {
        $this->clearBusinessCustomeHomeSectionCache($businessId);
    }

    public function clearBusinessCategoryCache($businessId)
    {
        Cache::forget("business_{$businessId}_product_categories");
        Cache::forget("business_{$businessId}_product_categories_parent");
        Cache::forget("business_{$businessId}_service_categories");
        $this->clearBusinessCustomeHomeSectionCache($businessId);
    }

    public function clearBusinessBannerCache($businessId)
    {
        Cache::forget("business_{$businessId}_banners");
    }

    public function clearBusinessGalleryCache($businessId)
    {
        Cache::forget("business_{$businessId}_galleries");
    }

    public function clearBusinessHomePopupCache($businessId)
    {
        Cache::forget("business_{$businessId}_home_popups");
    }

    public function clearBusinessLegalPageCache($businessId)
    {
        Cache::forget("business_{$businessId}_active_legal_pages");
    }

    public function clearBusinessBlogCache($businessId)
    {
        Cache::forget("business_{$businessId}_blogs");
    }

    private function clearBusinessCustomeHomeSectionCache($businessId)
    {
        Cache::forget("business_{$businessId}_custom_home_sections");
    }
}
