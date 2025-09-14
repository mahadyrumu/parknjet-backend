<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DefaultPricingService
{
    public function getDefaultPricing($lotType)
    {
        $defaultPricing = Cache::remember('admin_default_pricing_' . $lotType, 86400 * 30, function () use ($lotType) {
            return DB::connection('backend_mysql')->table('admin_default_pricing')
                ->where('lotType', $lotType)
                ->first();
        });

        if ($defaultPricing == null) {
            return " Pricing not configured. Please contact administrator.";
        }
        return $defaultPricing;
    }
}
