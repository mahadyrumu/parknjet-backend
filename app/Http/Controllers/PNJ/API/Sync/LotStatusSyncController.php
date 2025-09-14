<?php

namespace App\Http\Controllers\PNJ\API\Sync;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Services\LotPayment\AnonLotPaymentService;
use App\Services\LotPayment\MemLotPaymentService;
use App\Services\Referral\ReferralService;
use App\Services\Reservation\ReservationService;
use App\Services\Reward\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LotStatusSyncController extends Controller
{
    public function updateReservationStatus(Request $request, ReservationService $reservationService, ReferralService $referralService, $rsvnId)
    {
        Log::info("Received " . $request->status . " sync request for Reservation with id " . $rsvnId . ", ClaimId = " . $request->claimId . " with actual time " . $request->actualTime);

        if ($request->status == ReservationStatus['TEMP'] || $request->status == ReservationStatus['CHECKED_IN'] || $request->status == ReservationStatus['CHECKED_OUT']) {
            Log::info("Finding anon reservation with id " .  $rsvnId);

            $reservation = $reservationService
                ->getAnonReservation()
                ->where('id', $rsvnId)
                ->first();

            if ($reservation != null) {
                Log::info("Anon Reservation found, Updating its status to " .  $request->status);
                $reservationService->updateStatus($reservation, $request->status, $request->actualTime, $request->claimId);
                Log::info("Done! Reservation status updated to " . ReservationStatus['CHECKED_IN']);
            } else {
                Log::info("Anon reservation with id " . $rsvnId . " not found, Now trying to find it in Member reservation");
                $memberReservationDB = $reservationService
                    ->getMemReservation()
                    ->where('id', $rsvnId)
                    ->first();
                if ($memberReservationDB != null) {
                    Log::info("Member Reservation found, Updating its status to " .  $request->status);
                    $reservationService->updateStatus($memberReservationDB, $request->status, $request->actualTime, $request->claimId);

                    Log::info("Done! Member Reservation status updated to " .  $request->status);
                    $referralService->checkedOutReservation($memberReservationDB->owner, $memberReservationDB);

                    if ($request->status == ReservationStatus['CHECKED_OUT']) {
                        // Here add the points to the reward of the user
                        $points = $memberReservationDB->points;
                        Log::info(" points " .  $points);
                        if ($points != null && $points != 0) {
                            $rewardService = new RewardService;
                            $rewardService->addPoints($memberReservationDB->points, TriggerType['RESERVATION_CHECKED_OUT'], "", $memberReservationDB);
                        }
                        // do we need to add transaction to the member? implement after confirmation
                    }
                } else {
                    Log::error("No ANON or Member reservation found with reservation id " . $rsvnId . ", Call Sagar! ");
                }
            }
        } else {
            throw new PNJException("Only TEMP, CHECKED_IN or CHECKED_OUT status are allowed but found " . $request->status);
        }
    }

    public function updateReservationPayment(Request $request, ReservationService $reservationService, AnonLotPaymentService $anonLotPaymentService, MemLotPaymentService $memLotPaymentService)
    {
        $reservationId  = $request->reservationId;
        Log::info("Received add payment sync request for Reservation with id " . $reservationId);

        Log::info("Finding anon reservation with id " . $reservationId);

        $anonReservationDB = $reservationService
            ->getAnonReservation()
            ->where('id', $reservationId)
            ->first();

        if ($anonReservationDB != null) {
            $anonReservationDB = $reservationService->setPaymentInfo($anonReservationDB);

            Log::info("Anon Reservation found, Adding payment for reservation id " .  $reservationId);
            $anonLotPaymentService->addAnonLotPayment($anonReservationDB, $request);
        } else {
            Log::info("Anon reservation with id " . $reservationId . " not found, Now trying to find it in Member reservation");
            $memberReservationDB = $reservationService
                ->getMemReservation()
                ->where('id', $reservationId)
                ->first();

            if ($memberReservationDB != null) {
                $memberReservationDB = $reservationService->setPaymentInfo($memberReservationDB);

                Log::info("Member Reservation found, Adding payment to reservation id " . $reservationId);
                $memLotPaymentService->addMemLotPayment($memberReservationDB, $request);
            } else {
                Log::error("No ANON or Member reservation found with id " . $reservationId);
            }
        }
    }
}
