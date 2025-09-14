<?php

namespace App\Http\Controllers\PNJ\API\PickupRequest;

use App\Models\Lot1CustomerActivity;
use App\Models\Lot2CustomerActivity;
use App\Services\Reservation\ReservationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PickupRequest\PickupRequest;
use App\Services\PickupRequest\PickupRequestService;
use App\Exceptions\NotFoundException;
 

class PickupRequestController extends Controller
{
    public function getPickupRequest(ReservationService $reservationService, $ownerId)
    {
        $reservationDetails = collect();

        try {
            // Get all reservations for the owner in one query
            $reservations = $reservationService->getMemAllReservation($ownerId)->get();

            // Get all claimIds in one query
            $claimIds = $reservations->pluck('claimId')->toArray();

            // Get all Lot1 and Lot2 customer activities in one query each
            $lot1Activities = Lot1CustomerActivity::whereIn('claimId', $claimIds)
                ->orderBy('customer_activity.dateUpDated', 'desc')
                ->get();

            $lot2Activities = Lot2CustomerActivity::whereIn('claimId', $claimIds)
                ->orderBy('customer_activity.dateUpDated', 'desc')
                ->get();

            // Merge the results
            $reservationDetails = $lot1Activities->concat($lot2Activities);

            return response()->json([
                'success' => true,
                'data' => $reservationDetails
            ], ResponseCode["Success"]);
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' => $ex->getMessage(),
            ], ResponseCode["Forbidden"]);
        }
    }


    public function createPickupRequest(PickupRequest $request, PickupRequestService $pickupRequestService)
    {
        try {
            $claimId  = $request->claimId;
            $phone    = $request->phone;
            $minutes  = $request->minutes;
            $island   = $request->island;

            $pickupRequestService->pickupRequestInsertOrUpdate(
                $claimId,
                $phone,
                substr($island, 0, -1),
                $minutes
            );

            return response()->json([
                'success'  => true,
                'message'  => "Requested for pickup successfully."
            ], ResponseCode["Success"]);
        } catch (NotFoundException $e) {
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage()
            ], ResponseCode["Not Found"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
