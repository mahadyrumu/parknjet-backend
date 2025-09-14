<?php

namespace App\Services\Report;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    public function generateGeneralReports($request)
    {
        $memReservation = $this->getMemReservation($request);
        $anonReservation = $this->getAnonReservation($request);

        $generalReport['totalReservation'] = $memReservation->count() + $anonReservation->count();
        $generalReport['totalRevenue'] = number_format(($memReservation->sum('online_price') + $memReservation->sum('not_online_price') + $anonReservation->sum('online_price') + $anonReservation->sum('not_online_price')), 2);

        $generalReport['memReservationTotal'] = $memReservation->count();
        $generalReport['memOnlineTotal'] = number_format($memReservation->sum('online_price'), 2);
        $generalReport['memNotOnlineTotal'] = number_format($memReservation->sum('not_online_price'), 2);


        $generalReport['anonReservationTotal'] = $anonReservation->count();
        $generalReport['anonOnlineTotal'] = number_format($anonReservation->sum('online_price'), 2);
        $generalReport['anonNotOnlineTotal'] = number_format($anonReservation->sum('not_online_price'), 2);



        // Generate data range
        $dateRanges = $this->getDatesFromRange($request->startDate, $request->endDate, $format = 'Y-m-d');

        // Group by date
        $dateWiseMemReservation = $memReservation->groupBy(function ($item) {
            return Carbon::parse($item->createdDate)->format('Y-m-d');
        });
        $dateWiseAnonReservation = $anonReservation->groupBy(function ($item) {
            return Carbon::parse($item->createdDate)->format('Y-m-d');
        });

        $reservationSummaries = [];
        $revenueSummaries = [];

        foreach ($dateRanges as $key => $dateRange) {
            $memTotal = 0;
            $memOnlinePrice = 0;
            $memOfflinePrice = 0;
            $memOnline = 0;
            $memOffline = 0;

            if (!isset($dateWiseMemReservation[$dateRange])) {
                $dateWiseMemReservation[$dateRange] = 0;
            } else {
                foreach ($dateWiseMemReservation[$dateRange] as $memReservation) {
                    if ($memReservation->online_price) {
                        $memOnline += 1;
                        $memOnlinePrice += $memReservation->online_price;
                    } elseif ($memReservation->not_online_price) {
                        $memOffline += 1;
                        $memOfflinePrice += $memReservation->not_online_price;
                    }
                    $memTotal += 1;
                }
            }
            $reservationSummaries[$dateRange]['mem_online'] = $memOnline;
            $reservationSummaries[$dateRange]['mem_offline'] = $memOffline;
            $reservationSummaries[$dateRange]['mem_online_price'] = number_format($memOnlinePrice, 2);
            $reservationSummaries[$dateRange]['mem_offline_price'] = number_format($memOfflinePrice, 2);
            $reservationSummaries[$dateRange]['mem_reservation_count'] = $memTotal;

            $anonTotal = 0;
            $anonOnlinePrice = 0;
            $anonOfflinePrice = 0;
            $anonOnline = 0;
            $anonOffline = 0;

            if (!isset($dateWiseAnonReservation[$dateRange])) {
                $dateWiseAnonReservation[$dateRange] = 0;
            } else {
                foreach ($dateWiseAnonReservation[$dateRange] as $anonReservation) {
                    if ($anonReservation->online_price) {
                        $anonOnline += 1;
                        $anonOnlinePrice += $anonReservation->online_price;
                    } elseif ($anonReservation->not_online_price) {
                        $anonOffline += 1;
                        $anonOfflinePrice += $anonReservation->not_online_price;
                    }
                    $anonTotal += 1;
                }
            }

            $reservationSummaries[$dateRange]['anon_online'] = $anonOnline;
            $reservationSummaries[$dateRange]['anon_offline'] = $anonOffline;
            $reservationSummaries[$dateRange]['anon_online_price'] = number_format($anonOnlinePrice, 2);
            $reservationSummaries[$dateRange]['anon_offline_price'] = number_format($anonOfflinePrice, 2);
            $reservationSummaries[$dateRange]['anon_reservation_count'] = $anonTotal;

            $revenueSummaries[$dateRange]['reservation'] = $memTotal + $anonTotal;
            $revenueSummaries[$dateRange]['revenue'] = (number_format($memOnlinePrice + $memOfflinePrice + $anonOnlinePrice + $anonOfflinePrice, 2));
        }

        $report = [];
        $report['generalReport'] = $generalReport;
        $report['reservationSummaries'] = $reservationSummaries;
        $report['revenueSummaries'] = $revenueSummaries;

        return $report;
    }

    public function getMemReservation($request)
    {
        $memReservation = DB::connection('backend_mysql')
            ->table('mem_reservation as r')
            ->select(
                'r.id',
                'r.createdDate',
                DB::raw("COALESCE(SUM(CASE
                    WHEN p.paymentType = 'ONLINE' AND p.payment_id IS NOT NULL THEN p.total
                    ELSE 0
                END), 0) as online_price"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN p.paymentType = 'NOT_ONLINE' AND p.payment_id IS NULL AND p.lotPayment_id IS NOT NULL THEN p.total
                    ELSE 0
                END), 0) as not_online_price")
            )
            ->leftJoin('mem_pricing as p', 'p.reservation_id', '=', 'r.id');

        if ($request->lotType) {
            $memReservation = $memReservation->where('r.lotType', $request->lotType);
        }
        if ($request->startDate && $request->endDate) {
            $memReservation = $memReservation->whereBetween('r.createdDate', [$request->startDate, $request->endDate]);
        }
        return $memReservation = $memReservation->groupBy('r.id', 'r.createdDate')
            ->get();
    }

    public function getAnonReservation($request)
    {
        $anonReservation = DB::connection('backend_mysql')
            ->table('anon_reservation as r')
            ->select(
                'r.id',
                'r.createdDate',
                DB::raw("COALESCE(SUM(CASE
                    WHEN p.paymentType = 'ONLINE' AND p.payment_id IS NOT NULL THEN p.total
                    ELSE 0
                END), 0) as online_price"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN p.paymentType = 'NOT_ONLINE' AND p.payment_id IS NULL AND p.lotPayment_id IS NOT NULL THEN p.total
                    ELSE 0
                END), 0) as not_online_price")
            )
            ->leftJoin('anon_pricing as p', 'p.reservation_id', '=', 'r.id');

        if ($request->lotType) {
            $anonReservation = $anonReservation->where('r.lotType', $request->lotType);
        }
        if ($request->startDate && $request->endDate) {
            $anonReservation = $anonReservation->whereBetween('r.createdDate', [$request->startDate, $request->endDate]);
        }
        return $anonReservation = $anonReservation->groupBy('r.id', 'r.createdDate')
            ->get();
    }

    public function getDatesFromRange($start, $end, $format = 'Y-m-d')
    {
        // Declare an empty array
        $array = array();

        // Use strtotime function
        $Variable1 = strtotime($start);
        $Variable2 = strtotime($end);

        // Use for loop to store dates into array
        for (
            $currentDate = $Variable1;
            $currentDate <= $Variable2;
            $currentDate += (86400)
        ) {
            $Store = date('Y-m-d', $currentDate);
            $array[] = $Store;
        }

        // Return the array elements
        return $array;
    }
}
