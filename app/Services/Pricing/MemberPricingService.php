<?php

namespace App\Services\Pricing;

use App\Models\Backend\MemPricing;
use App\Traits\SequenceUpdate;

class MemberPricingService
{
    use SequenceUpdate;

    public function createMemPricing($pricing, $owner, $reservation_id)
    {
        $memPricing = new MemPricing;
        $memPricing->version = 0;
        $memPricing->averageRate = $pricing['averageRate'];
        $memPricing->cityTax = $pricing['cityTax'];
        $memPricing->discountAmount = $pricing['discountAmount'];
        $memPricing->discountSubTotal = ($pricing['subTotal'] - $pricing['discountAmount']);
        $memPricing->discountedDay = $pricing['discountedDay'];
        $memPricing->portFee = $pricing['portFee'];
        $memPricing->stateTax = $pricing['stateTax'];
        $memPricing->subTotal = $pricing['subTotal'];
        $memPricing->total = $pricing['total'];
        $memPricing->paymentType = $pricing['paymentType'];
        $memPricing->createdBy_id = $owner;
        $memPricing->reservation_id = $reservation_id;
        $memPricing->points = $pricing['points'];
        $memPricing->extraFee = $pricing['extraFee'];
        $memPricing->extendFee = $pricing['extendFee'];
        $memPricing->save();
        $this->updateSequence('backend_mysql', 'mem_pricing', 'MemberPricing_SEQ');
        return $memPricing;
    }

    public function getMemPricing()
    {
        return MemPricing::orderBy('id', 'asc');
    }

    public function setPayment($pricingsDB, $paymentDB)
    {
        $pricingsDB->payment_id = $paymentDB->id;
        $pricingsDB->save();
    }

    public function setPricing($paymentDB, $pricingDB)
    {
        $paymentDB->pricing_id = $pricingDB->id;
        $paymentDB->save();
    }
}
