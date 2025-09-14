<?php

namespace App\Services\LotPayment;

use App\Models\Backend\AnonLotPayment;
use App\Traits\SequenceUpdate;
use Illuminate\Support\Facades\Log;
use Payment;

class AnonLotPaymentService
{
    use SequenceUpdate;

    public function createAnonLotPayment($anonReservation, $request)
    {
        $anonLotPayment = new AnonLotPayment;
        $anonLotPayment->paymentStatus = Payment::PaymentStatus['PAID'];
        $anonLotPayment->amount = $request->amount;
        $anonLotPayment->type = $request->type;
        $anonLotPayment->paymentDateTime = $request->paymentDateTime;
        $anonLotPayment->paymentIdInLotDB = $request->paymentIdInLotDB;
        $anonLotPayment->save();
        return $anonLotPayment;
    }

    public function addAnonLotPayment($anonReservation, $request)
    {
        if (!$anonReservation->isPaid) {
            foreach ($anonReservation->pricing as $key => $eachPricing) {
                if ($eachPricing->paymentType == Payment::PaymentType['NOT_ONLINE']) {
                    $anonLotPayment = $this->createAnonLotPayment($request, $eachPricing);
                    Log::info("Adding lot payment for pricing with id " . $eachPricing->id . " and reservation id " . $anonReservation->id);
                    $eachPricing->lotPayment_id = $anonLotPayment->id;
                    $eachPricing->save();
                    Log::info("Added Anon lot payment with PK " . $eachPricing->lot_payment->id);
                }
            }
        } else {
            Log::info("Payment already exist for anon reservation " . $anonReservation->id);
        }
    }
}
