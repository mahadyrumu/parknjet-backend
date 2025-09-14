<?php

namespace App\Services\PickupRequest;

use App\Models\Dispatch;
use App\Models\Lot1CustomerActivity;
use App\Models\Lot2CustomerActivity;
use App\Services\Dispatch\DispatchCommonService;
use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Log;

class PickupRequestService
{
    // for create or update pickup request details
    public function pickupRequestInsertOrUpdate($claimId, $phone, $island, $delay)
    {
        if ($phone == null) {
            $phone = "";
        }

        // claim id validation
        Log::info("Seaching reservationId for claimId " . $claimId);
        if (getLotIdFromClaimId($claimId) == 1) {

            $reservationDetails = Lot1CustomerActivity::where('claimId', $claimId)
                ->join('reservation_info', 'customer_activity.reservation_id', '=', 'reservation_info.id')
                ->select('reservation_info.reservation_id')
                ->orderBy('customer_activity.dateUpDated', 'desc')
                ->first();
            Log::info($reservationDetails);

            if ($reservationDetails != null) {
                $reservationId = $reservationDetails->reservation_id;
            }
            Log::info("Found at lot1, reservationId " . $reservationId);
        } elseif (getLotIdFromClaimId($claimId) == 2) {

            $reservationDetails = Lot2CustomerActivity::where('claimId', $claimId)
                ->join('reservation_info', 'customer_activity.reservation_id', '=', 'reservation_info.id')
                ->select('reservation_info.reservation_id')
                ->orderBy('customer_activity.dateUpDated', 'desc')
                ->first();
            Log::info($reservationDetails);

            if ($reservationDetails != null) {
                $reservationId = $reservationDetails->reservation_id;
            }
            Log::info("Found at lot2, reservationId " . $reservationId);
        } else {
            throw new NotFoundException("Claim ID is invalid. Please provide a valid claim ID.");
        }

        $dispatch_data['phone'] = $phone;
        $dispatch_data['cid'] = $claimId;
        $dispatch_data['island'] = $island;
        $dispatch_data['delay'] = $delay;

        $comment = implode('|', $dispatch_data);

        $DispatchCommonService = new DispatchCommonService;
        $dispatch_arr = $DispatchCommonService->getDispatchData($claimId, $reservationId, $island, $delay, $phone);

        $dispatchInfo = Dispatch::where('cid', $claimId)
            ->where('rsvn', $reservationId)
            ->where('type', 'pu')
            ->orderBy('start', 'desc')
            ->first();

        if ($dispatchInfo) {
            $dispatch = $dispatchInfo->update([
                'phone' => $phone,
                'island' => $island,
                'delay' => $delay,
                'comment' => $comment
            ]);
            Log::info("Update information of request for pickup");
        } else {
            $dispatch = Dispatch::insert($dispatch_arr);
            Log::info("Request for pickup");
        }
        return $dispatch;
    }
}
