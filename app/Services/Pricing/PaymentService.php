<?php

namespace App\Services\Pricing;

use App\Exceptions\PNJException;
use App\Services\PaymentGateway\StripeService;
use App\Services\Pricing\AnonPricingService;
use App\Services\Reservation\ReservationService;
use App\Services\Sync\LotMemberReservationSyncService;
use Illuminate\Support\Facades\Log;
use Payment;

class PaymentService
{
    public function makeAnonPaymentForAnonReservation($anonReservationDB, $request)
    {
        if (count($anonReservationDB->pricing) > 2) {
            Log::error("Exception: Reservation with id = " . $anonReservationDB->id . " has more than 2 pricing, Developer please take a look");
            throw new PNJException("Reservation has more than 2 pricing , Please contact administrator");
        }
        Log::info("Checking if NOT ONLINE payment exist for reservation id " . $anonReservationDB->id);

        $anonPricingService = new AnonPricingService;
        $anonNotOnlinePricingDB = $anonPricingService->getAnonPricing()
            ->where('paymentType', Payment::PaymentType['NOT_ONLINE'])
            ->where('reservation_id', $anonReservationDB->id)
            ->first();
        if ($anonNotOnlinePricingDB != null && $anonNotOnlinePricingDB->lotPayment != null) {
            Log::error("Excepton : LOT Payment has already been made for pricing with id = " . $anonNotOnlinePricingDB->id . " , Payment id = " . $anonNotOnlinePricingDB->lotPayment->id);
            throw new PNJException("LOT Payment has already been made for pricing with id = " . $anonNotOnlinePricingDB->id . " , Payment id = " . $anonNotOnlinePricingDB->lotPayment->id);
        }
        Log::info("NOT ONLINE payment does not exist for reservation id " . $anonReservationDB->id);
        Log::info("Checking if online payment exist for reservation id " . $anonReservationDB->id);

        $anonOnlinePricingDB = $anonPricingService->getAnonPricing()
            ->where('paymentType', Payment::PaymentType['ONLINE'])
            ->where('reservation_id', $anonReservationDB->id)
            ->first();

        if ($anonOnlinePricingDB == null) {
            Log::error("Exception : Reservation has no online pricing saved in database, Please contact administrator ");
            throw new PNJException(" Reservation has no online pricing saved in database, Please contact administrator ");
        }
        Log::info(" Database returned online pricing with id " . $anonOnlinePricingDB->id . " for reservation with id " . $anonReservationDB->id);

        if ($anonOnlinePricingDB != null && $anonOnlinePricingDB->payment != null) {
            Log::error(" Exception : Online Payment has already been made for pricing with id = " . $anonOnlinePricingDB->id . " , Payment id = " . $anonOnlinePricingDB->payment->id);
            throw new PNJException("Online Payment has already been made for pricing with id = " . $anonOnlinePricingDB->id . " , Payment id = " . $anonOnlinePricingDB->payment->id);
        }
        Log::info("ONLINE payment not found for reservation id " . $anonReservationDB->id);

        $stripeService = new StripeService;
        if ($request->paymentId) {
            $stripeCharge = $stripeService->checkPaymentMethodThroughStripeForReservation($request->paymentId, $anonReservationDB);
        } else {
            $stripeCharge = $stripeService->chargeCreditCardThroughStripeForReservation($anonOnlinePricingDB->total, $request->stripeToken, $anonReservationDB);
        }

        $cardType = $stripeService->getCardType($stripeCharge);

        $anonPaymentService = new AnonPaymentService;
        $anonPayment = $anonPaymentService->createAnonPayment($anonOnlinePricingDB, $stripeCharge, Payment::PaymentStatus['PAID'], $cardType);
        $anonOnlinePricingDB->payment_id = $anonPayment->id;
        $anonOnlinePricingDB->save();

        $reservationService = new ReservationService();
        $anonReservationDB = $reservationService->getAnonReservation()
            ->where('id', $anonReservationDB->id)
            ->first();

        $lotMemReservationSyncService = new LotMemberReservationSyncService;
        $lotMemReservationSyncService->syncReservationToLot($anonReservationDB, null);
        return $anonReservationDB;
    }

    public function makeMemberPaymentByNonStoredCard($memberReservationDB, $request, $currentLoggedInUser)
    {
        if ($this->checkIfReservationUnPaid($memberReservationDB)) {
            $memberPricingService = new MemberPricingService;
            $memberOnlinePricingDB = $memberPricingService->getMemPricing()
                ->where('paymentType', Payment::PaymentType['ONLINE'])
                ->where('reservation_id', $memberReservationDB->id)
                ->first();
            $stripeService = new StripeService;
            if ($request->paymentId) {
                $stripeCharge = $stripeService->checkPaymentMethodThroughStripeForReservation($request->paymentId, $memberReservationDB);
            } else {
                $stripeCharge = $stripeService->chargeCreditCardThroughStripeForReservation($memberOnlinePricingDB->total, $request->stripeToken, $memberReservationDB);
            }
            $cardType = $stripeService->getCardType($stripeCharge);
            return $this->updateMemberPricingAndReservation($memberReservationDB, $memberOnlinePricingDB, $currentLoggedInUser, $stripeCharge, $cardType);
        }
        return null;
    }

    public function updateMemberPricingAndReservation($memberReservationDB, $memberOnlinePricingDB, $currentLoggedInUser, $stripeCharge, $cardType)
    {
        $memberPaymentService = new MemberPaymentService;
        $memPayment = $memberPaymentService->createMemberPayment($memberOnlinePricingDB, $stripeCharge, Payment::PaymentStatus['PAID'], $currentLoggedInUser, $cardType);
        $memberOnlinePricingDB->payment_id = $memPayment->id;
        $memberOnlinePricingDB->save();

        $reservationService = new ReservationService();
        $memberReservationDB = $reservationService->getMemReservation()
            ->where('id', $memberReservationDB->id)
            ->where('owner_id', $currentLoggedInUser)
            ->first();
            
        $lotMemReservationSyncService = new LotMemberReservationSyncService;
        $lotMemReservationSyncService->syncReservationToLot($memberReservationDB, $currentLoggedInUser);
        return $memberReservationDB;
    }

    public function checkIfReservationUnPaid($memberReservationDB)
    {
        if (count($memberReservationDB->pricing) > 2) {
            Log::info("Exception: Reservation with id = " . $memberReservationDB->id . " has more than 2 pricing, Developer please take a look");
            throw new PNJException("Reservation has more than 2 pricing , Please contact administrator");
        }
        $memberPricingService = new MemberPricingService;

        //Step 1 : Check if non online / lot payment exists
        $notOnlinePricingDB = $memberPricingService->getMemPricing()
            ->where('paymentType', Payment::PaymentType['NOT_ONLINE'])
            ->where('reservation_id', $memberReservationDB->id)
            ->first();
        if ($notOnlinePricingDB != null && $notOnlinePricingDB->lot_payment != null) {
            Log::info("Exception: LOT Payment has already been made for pricing with id = " . $notOnlinePricingDB->id . ", Payment id = " . $notOnlinePricingDB->lot_payment->id);
            throw new PNJException("LOT Payment has already been made for pricing with id = " . $notOnlinePricingDB->id . ", Payment id = " . $notOnlinePricingDB->lot_payment->id);
        }
        Log::info("NOT ONLINE payment does not exist for reservation id " . $memberReservationDB->id);

        //Step 1 : Check if non online / lot payment exists
        $onlinePricingDB = $memberPricingService->getMemPricing()
            ->where('paymentType', Payment::PaymentType['ONLINE'])
            ->where('reservation_id', $memberReservationDB->id)
            ->first();
        if ($onlinePricingDB == null) {
            Log::info("Exception: Reservation has no online pricing saved in database, Please contact administrator");
            throw new PNJException("Reservation has no online pricing saved in database, Please contact administrator");
        }
        Log::info("Database returned online pricing with id " . $onlinePricingDB->id . " for reservation with id " . $memberReservationDB->id);
        if ($onlinePricingDB != null && $onlinePricingDB->payment != null) {
            Log::info("Exception: Online Payment has already been made for pricing with id = " . $onlinePricingDB->id . ", Payment id = " . $onlinePricingDB->payment->id);
            throw new PNJException("Online Payment has already been made for pricing with id = " . $onlinePricingDB->id . ", Payment id = " . $onlinePricingDB->payment->id);
        }
        Log::info("ONLINE payment not found for reservation id " . $onlinePricingDB->id);

        return true;
    }
}
