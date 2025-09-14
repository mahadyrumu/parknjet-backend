<?php

namespace App\Services\Reservation;

use App\Exceptions\PNJException;
use App\Services\Pricing\PaymentService;
use Illuminate\Support\Facades\Log;

class ReservationPaymentService
{
    public function createMemPaidReservation($rsvnId, $request, $currentLoggedInUser)
    {
        // payForRsvnUsingNonStoredCC
        $reservationService = new ReservationService();
        $memberReservationDB = $reservationService->getMemReservation()
            ->where('id', $rsvnId)
            ->where('owner_id', $currentLoggedInUser)
            ->first();

        if ($memberReservationDB == null) {
            throw new PNJException("Reservation with id = " . $rsvnId . " not found for owner username = " . auth()->user()->full_name);
        }

        $paymentService = new PaymentService();
        return $paymentService->makeMemberPaymentByNonStoredCard($memberReservationDB, $request, $currentLoggedInUser);
    }

    public function createAnonPaidReservation($rsvnId, $request)
    {
        $reservationService = new ReservationService();
        $anonReservation = $reservationService->getAnonReservation()
            ->where('id', $rsvnId)
            ->first();

        if ($anonReservation == null) {
            throw new PNJException("Reservation with id = " . $rsvnId . " not found ");
        }

        Log::info("Payment request for reservation id = " . $anonReservation->id . " received");
        $paymentService = new PaymentService();
        return $paymentService->makeAnonPaymentForAnonReservation($anonReservation, $request);
    }
}
