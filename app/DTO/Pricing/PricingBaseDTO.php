<?php

namespace App\DTO\Pricing;

use Illuminate\Support\Facades\Log;

class PricingBaseDTO
{
    public $averageRate, $couponDiscountAmount, $isAMember, $paymentType, $stateTax, $extraAmount, $subTotal, $total, $points, $markUpPercent, $walletDays, $couponCode, $affectedAverageRate, $durationInDay;

    public function __construct(
        $isAMember,
        $paymentType
    ) {
        return (object) [
            "averageRate" => $this->averageRate = 0,
            "couponDiscountAmount" => $this->couponDiscountAmount = 0,
            "isAMember" => $this->isAMember = $isAMember,
            "paymentType" => $this->paymentType = $paymentType,
            "stateTax" => $this->stateTax = 0,
            "extraAmount" => $this->extraAmount = 0,
            "subTotal" => $this->subTotal = 0,
            "total" => $this->total = 0,
            "points" => $this->points = 0,
            "markUpPercent" => $this->markUpPercent = 0,
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
        if ($amount != null && $amount > 0) {
            Log::info("Coupon amount set in DTO to be returned to client");
            $this->couponDiscountAmount = round($amount, 2);
            $this->subTotal = round($this->subTotal - $amount, 2);
            $couponTax = $this->couponDiscountAmount * $defaultPricing->stateTaxPercent;
            $this->total = round($this->total - $amount - $couponTax, 2);
            $this->averageRate = round($this->subTotal / $durationInDay, 2);
            $this->stateTax = round($this->subTotal * $defaultPricing->stateTaxPercent, 2);
        } else {
            Log::info("Coupon amount is null !");
        }
    }
}
