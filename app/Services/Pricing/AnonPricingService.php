<?php

namespace App\Services\Pricing;

use App\Models\Backend\AnonPricing;
use App\Traits\SequenceUpdate;

class AnonPricingService
{
    use SequenceUpdate;

    public function getAnonPricing()
    {
        return AnonPricing::with([
            'payment'
        ])->orderBy('id', 'desc');
    }

    public function createAnonPricing($pricing, $reservation_id)
    {
        $anonPricing = new AnonPricing;
        $anonPricing->version = 0;
        $anonPricing->averageRate = $pricing['averageRate'];
        $anonPricing->cityTax = $pricing['cityTax'];
        $anonPricing->discountAmount = $pricing['discountAmount'];
        $anonPricing->discountedDay = $pricing['discountedDay'];
        $anonPricing->discountSubTotal = ($pricing['subTotal'] - $pricing['discountAmount']);
        $anonPricing->portFee = $pricing['portFee'];
        $anonPricing->stateTax = $pricing['stateTax'];
        $anonPricing->subTotal = $pricing['subTotal'];
        $anonPricing->total = $pricing['total'];
        $anonPricing->paymentType = $pricing['paymentType'];
        $anonPricing->reservation_id = $reservation_id;
        // $anonPricing->points = $pricing['points'];       // There is a point column in database but it's empty 
        $anonPricing->extraFee = $pricing['extraFee'];
        $anonPricing->extendFee = $pricing['extendFee'];
        $anonPricing->save();
        $this->updateSequence('backend_mysql', 'anon_pricing', 'AnonPricing_SEQ');
        return $anonPricing;
    }

    public function setPayment($pricingDB, $paymentDB)
    {
        $pricingDB->payment_id = $paymentDB->id;
        $pricingDB->save();
    }

    public function setPricing($paymentDB, $pricingDB)
    {
        $paymentDB->pricing_id = $pricingDB->id;
        $paymentDB->save();
    }
}
