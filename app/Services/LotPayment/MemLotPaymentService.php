<?php

namespace App\Services\LotPayment;

use App\Models\Backend\MemLotPayment;
use App\Traits\SequenceUpdate;
use Illuminate\Support\Facades\Log;
use Payment;

class MemLotPaymentService
{
    use SequenceUpdate;

    public function createMemLotPayment($anonReservation, $request)
    {
        $memLotPayment = new MemLotPayment();
        $memLotPayment->paymentStatus = Payment::PaymentStatus['PAID'];
        $memLotPayment->amount = $request->amount;
        $memLotPayment->type = $request->type;
        $memLotPayment->paymentDateTime = $request->paymentDateTime;
        $memLotPayment->paymentIdInLotDB = $request->paymentIdInLotDB;
        $memLotPayment->save();
        return $memLotPayment;
    }

    public function addMemLotPayment($memReservation, $request)
    {
        if (!$memReservation->isPaid) {
            foreach ($memReservation->pricing as $key => $eachPricing) {
                if ($eachPricing->paymentType == Payment::PaymentType['NOT_ONLINE']) {
                    $memLotPayment = $this->createMemLotPayment($request, $eachPricing);
                    Log::info("Adding lot payment for pricing with id " . $eachPricing->id . " and reservation id " . $memReservation->id);
                    $eachPricing->lotPayment_id = $memLotPayment->id;
                    $eachPricing->save();
                    Log::info("Added Member lot payment with PK " . $eachPricing->lot_payment->id);
                }
            }
        } else {
            Log::info("Payment already exist for member reservation id " . $memReservation->id);
        }
    }
}
