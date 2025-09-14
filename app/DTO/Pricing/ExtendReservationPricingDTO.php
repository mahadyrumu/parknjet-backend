<?php

namespace App\DTO\Pricing;

use Illuminate\Support\Facades\Log;

class ExtendReservationPricingDTO
{
    public $extendFee, $coupon, $walletDays, $memberOnlinePayPricing, $anonOnlinePayPricing;
    // public $lotType, $cityTax, $portFee, $extraFee, $currency, $markUpPercent, $coupon;
    // private $vehicleLength, $dropOffTime, $pickUpTime, $pref, $couponCode, $stateTax;
    private $couponCode, $isAMember, $paymentType, $extraAmount, $subTotal, $total, $averageRate, $couponDiscountAmount, $points, $durationInDay, $couponDiscountedDays;
    // public $onlinePaymentRange, $memberOnlineReferencePricing, $memberNotOnlinePayPricing, $memberNonOnlineReferencePricing, $anonOnlinePayPricing, $anonNonOnlinePayPricing = [];

    public function __construct($extendFee) {
        $this->extendFee = $extendFee;
        $this->memberOnlinePayPricing = $this->PricingBaseDTO(true, "ONLINE");
        $this->anonOnlinePayPricing = $this->PricingBaseDTO(false, "ONLINE");
    }

    public function setDefaultPricing($defaultPricing)
    {
        $this->extendFee = $defaultPricing->extendFee;
    }

    public function pricingBaseDTO($isAMember, $paymentType)
    {
        return (object) [
            "averageRate" => $this->averageRate = 0,
            "couponDiscountAmount" => $this->couponDiscountAmount = 0,
            "isAMember" => $this->isAMember = $isAMember,
            "paymentType" => $this->paymentType = $paymentType,
            // "stateTax" => $this->stateTax = 0,
            "extraAmount" => $this->extraAmount = 0,
            "subTotal" => $this->subTotal = 0,
            "total" => $this->total = 0,
            "points" => $this->points = 0,
            // "markUpPercent" => $this->markUpPercent = 0,
            "walletDays" => $this->walletDays = 0,
        ];
    }

    public function setDiscountedDays($couponCode, $durationInDay, $couponDiscountedDays, $responsePricing)
    {
        $responsePricing->couponCode = $couponCode;
        $responsePricing->durationInDay = $durationInDay;
        if ($couponDiscountedDays > 0) {
            $responsePricing->couponDiscountedDays = $couponDiscountedDays;
        } else {
            $responsePricing->couponDiscountedDays = 0;
        }
    }

    public function setCouponAmount($couponCode, $amount, $durationInDay, $defaultPricing)
    {
        $this->couponCode = $couponCode;
        Log::info("Coupon amount : " . $amount);
        if ($amount != null && $amount > 0) {
            Log::info("Coupon amount set in DTO to be returned to client");
            $this->couponDiscountAmount = $amount;
            $this->subTotal = $this->subTotal - $amount;
            // $couponTax = $this->couponDiscountAmount * $defaultPricing->stateTaxPercent;
            $couponTax = $this->couponDiscountAmount;
            $this->total = $this->total - $amount - $couponTax;
            $this->averageRate = $this->subTotal / $durationInDay;
            // $this->stateTax = $this->subTotal * $defaultPricing->stateTaxPercent;
        } else {
            Log::info("Coupon amount is null !");
        }
    }

    public function setMemberOnlinePayAverageRate($averageRate, $stateTaxPercent, $memberOnlinePayDurationInDay)
    {
        $this->memberOnlinePayPricing->durationInDay = $memberOnlinePayDurationInDay;
        $this->memberOnlinePayPricing->averageRate = round(toDollar($averageRate), 2);
        $this->memberOnlinePayPricing->subTotal = round(toDollar($averageRate * $this->memberOnlinePayPricing->durationInDay), 2);
        // $this->memberOnlinePayPricing->stateTax = round(toDollar($this->memberOnlinePayPricing->subTotal * $stateTaxPercent), 2);
        // $this->memberOnlinePayPricing->total = round($this->memberOnlinePayPricing->subTotal + $this->extendFee + $this->memberOnlinePayPricing->stateTax, 2);
        $this->memberOnlinePayPricing->total = round($this->memberOnlinePayPricing->subTotal + $this->extendFee, 2);
        $this->memberOnlinePayPricing->points = intval(($this->memberOnlinePayPricing->subTotal * 100) / 10);
    }

    public function setNonMemberOnlinePayAverageRate($averageRate, $stateTaxPercent, $anonOnlineDurationInDay)
    {
        $this->anonOnlinePayPricing->durationInDay = $anonOnlineDurationInDay;
        $this->anonOnlinePayPricing->averageRate = round(toDollar($averageRate), 2);
        $this->anonOnlinePayPricing->subTotal = round(toDollar($averageRate * $this->anonOnlinePayPricing->durationInDay), 2);
        // $this->anonOnlinePayPricing->stateTax = round(toDollar($this->anonOnlinePayPricing->subTotal * $stateTaxPercent), 2);
        // $this->anonOnlinePayPricing->total = round($this->anonOnlinePayPricing->subTotal + $this->cityTax + $this->portFee + $this->extraFee + $this->anonOnlinePayPricing->stateTax, 2);
        $this->anonOnlinePayPricing->total = round($this->anonOnlinePayPricing->subTotal + $this->extendFee, 2);
        $this->anonOnlinePayPricing->extraAmount = round($this->anonOnlinePayPricing->subTotal - $this->memberOnlinePayPricing->subTotal, 2);
    }
}
