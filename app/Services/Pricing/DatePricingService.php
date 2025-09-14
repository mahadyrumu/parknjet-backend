<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DatePricingService
{
    public function findByDateAndLotType($date, $lotType)
    {
        return Cache::remember("admin_date_pricing_" . $date . "_" . $lotType, 86400 * 30, function () use ($date, $lotType) {
            return DB::connection('backend_mysql')->table('admin_date_pricing')
                ->where('date', $date)
                ->where('lotType', $lotType)
                ->first();
        });
    }
}
