<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OnlinePaymentRangeService
{
    public function getPaymentRangeIfAvailable($lotType, $dropOffTime)
    {
        $lotPaymentRanges = Cache::remember("admin_online_payment_period_" . $lotType . "_" . $dropOffTime, 86400 * 30, function () use ($lotType, $dropOffTime) {
            return DB::connection('backend_mysql')->table('admin_online_payment_period')
                ->where('lotType', $lotType)
                ->whereDate('startDate', '<=', $dropOffTime)
                ->get();
        });

        $dropOffTime = date("Y-m-d", strtotime($dropOffTime));
        foreach ($lotPaymentRanges as $key => $range) {
            $startDate = date("Y-m-d", strtotime($range->startDate));
            $endDate = date("Y-m-d", strtotime($range->endDate));

            // After the startDate and Before the endDate
            if ($dropOffTime > $startDate && $dropOffTime < $endDate) {
                return $range->message;
            }
        }
        return 0;
    }
}
