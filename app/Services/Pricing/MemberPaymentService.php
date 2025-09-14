<?php

namespace App\Services\Pricing;
use App\Models\Backend\MemPayment;
use App\Traits\SequenceUpdate;
use Payment;

class MemberPaymentService
{
    use SequenceUpdate;
    
    public function createMemberPayment($pricing, $charge, $paymentStatus, $owner, $cardType)
    {
        $memPayment = new MemPayment;
        $memPayment->isDeleted = 0;
        $memPayment->version = 0;
        $memPayment->cardType = isset(Payment::CardType[$cardType]) ? Payment::CardType[$cardType] : $cardType;
        $memPayment->paymentStatus = $paymentStatus;
        $memPayment->stripeChargeId = $charge->id;
        $memPayment->stripeChargeJson = json_encode($charge);
        $memPayment->createdBy_id = $owner;
        $memPayment->owner_id = $owner;
        $memPayment->pricing_id = $pricing->id;
        $memPayment->save();
        $this->updateSequence('backend_mysql', 'mem_payment', 'MemberPayment_SEQ');
        return $memPayment;
    }
}
