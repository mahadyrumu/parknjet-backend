<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DayOfWeekPricingService
{
    public function findByDayOfTheWeekAndLotType($dayOfTheWeek, $lotType)
    {
        return Cache::remember("admin_dayOfWeek_pricing_" . $dayOfTheWeek . "_" . $lotType, 86400 * 30, function () use ($lotType, $dayOfTheWeek) {
            return DB::connection('backend_mysql')->table('admin_dayOfWeek_pricing')
                ->where('lotType', $lotType)
                ->where('dayOfTheWeek', $dayOfTheWeek)
                ->first();
        });
    }
}
