<?php

namespace App\Services\PrepaidPackage;

use App\Models\Backend\MemPrepaidPackagePayment;
use App\Traits\SequenceUpdate;
use Payment;

class PrepaidPackagePaymentService
{
    use SequenceUpdate;

    public function createPrePaidPackagePayment($prePaidPackage, $charge, $paymentStatus, $owner, $cardType)
    {
        $prePaidPackagePayment = new MemPrepaidPackagePayment();
        $prePaidPackagePayment->version = 0;
        $prePaidPackagePayment->cardType = isset(Payment::CardType[$cardType]) ? Payment::CardType[$cardType] : $cardType;
        $prePaidPackagePayment->paymentStatus = $paymentStatus;
        $prePaidPackagePayment->stripeChargeId = $charge->id;
        $prePaidPackagePayment->stripeChargeJson = json_encode($charge);
        $prePaidPackagePayment->createdBy_id = $owner->id;
        $prePaidPackagePayment->owner_id = $owner->id;
        $prePaidPackagePayment->prePaidPackage_id = $prePaidPackage->id;
        $prePaidPackagePayment->save();
        $this->updateSequence('backend_mysql', 'mem_prepaid_package_payment', 'PrePaidPackagePayment_SEQ');
        return $prePaidPackagePayment;
    }
}
