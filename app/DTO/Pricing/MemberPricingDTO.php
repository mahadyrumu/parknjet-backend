<?php

namespace App\DTO\Pricing;

use Payment;

class MemberPricingDTO
{
    public $paymentType, $coupon, $discountedDay, $discountAmount, $stateTax, $averageRate, $total, $points, $subTotal, $cityTax, $portFee, $extraFee, $extendFee;

    public function memberPricing($reservationDTO, $reservationPricingDTO, $coupon, $paymentType = null)
    {
        if ($paymentType) {
            $this->paymentType = $paymentType;
        } else {
            $this->paymentType = $reservationDTO->pricing['paymentType'];
        }
        $this->cityTax = $reservationPricingDTO->cityTax ?? 0;
        $this->portFee = $reservationPricingDTO->portFee ?? 0;
        $this->extraFee = $reservationPricingDTO->extraFee ?? 0;
        $this->extendFee = $reservationPricingDTO->extendFee ?? 0;
        if ($this->paymentType == Payment::PaymentType['ONLINE']) {
            return $this->populateBasedOnOnlinePayment($reservationPricingDTO, $coupon, $reservationDTO->isRegular);
        } else if ($this->paymentType == Payment::PaymentType['NOT_ONLINE']) {
            return $this->populateNotOnlinePayment($reservationDTO, $reservationPricingDTO, $coupon);
        }
    }

    private function populateNotOnlinePayment($reservationDTO, $reservationPricingDTO, $coupon)
    {
        $this->coupon = $coupon;
        if ($reservationDTO->isRegular) {
            $this->discountedDay = $reservationPricingDTO->memberNonOnlineReferencePricing->couponDiscountedDays;
            $this->discountAmount = round($reservationPricingDTO->memberNonOnlineReferencePricing->couponDiscountAmount, 2);
            $this->stateTax = round($reservationPricingDTO->memberNonOnlineReferencePricing->stateTax ?? 0, 2);
            $this->averageRate = round($reservationPricingDTO->memberNonOnlineReferencePricing->averageRate, 2);
            $this->total = round($reservationPricingDTO->memberNonOnlineReferencePricing->total, 2);
            $this->points = $reservationPricingDTO->memberNonOnlineReferencePricing->points;
            $this->subTotal = round($reservationPricingDTO->memberNonOnlineReferencePricing->subTotal, 2);
        } else {
            $this->discountedDay = $reservationPricingDTO->memberNotOnlinePayPricing->couponDiscountedDays;
            $this->discountAmount = round($reservationPricingDTO->memberNotOnlinePayPricing->couponDiscountAmount, 2);
            $this->stateTax = round($reservationPricingDTO->memberNotOnlinePayPricing->stateTax ?? 0, 2);
            $this->averageRate = round($reservationPricingDTO->memberNotOnlinePayPricing->averageRate, 2);
            $this->total = round($reservationPricingDTO->memberNotOnlinePayPricing->total, 2);
            $this->points = $reservationPricingDTO->memberNotOnlinePayPricing->points;
            $this->subTotal = round($reservationPricingDTO->memberNotOnlinePayPricing->subTotal, 2);
        }
        return [
            'discountedDay' => $this->discountedDay,
            'discountAmount' => $this->discountAmount,
            'stateTax' => $this->stateTax,
            'averageRate' => $this->averageRate,
            'total' => $this->total,
            'points' => $this->points,
            'subTotal' => $this->subTotal,
            'paymentType' => $this->paymentType,
            'coupon' => $this->coupon,
            'cityTax' => $this->cityTax,
            'portFee' => $this->portFee,
            'extraFee' => $this->extraFee,
            'extendFee' => $this->extendFee,
        ];
    }

    private function populateBasedOnOnlinePayment($reservationPricingDTO, $coupon, $isRegular)
    {
        $this->paymentType = Payment::PaymentType['ONLINE'];
        $this->coupon = $coupon;
        if ($isRegular) {
            $this->discountedDay = $reservationPricingDTO->memberOnlineReferencePricing->couponDiscountedDays;
            $this->discountAmount = round($reservationPricingDTO->memberOnlineReferencePricing->couponDiscountAmount, 2);
            $this->stateTax = round($reservationPricingDTO->memberOnlineReferencePricing->stateTax ?? 0, 2);
            $this->averageRate = round($reservationPricingDTO->memberOnlineReferencePricing->averageRate, 2);
            $this->total = round($reservationPricingDTO->memberOnlineReferencePricing->total, 2);
            $this->points = $reservationPricingDTO->memberOnlineReferencePricing->points;
            $this->subTotal = round($reservationPricingDTO->memberOnlineReferencePricing->subTotal, 2);
        } else {
            $this->discountedDay = $reservationPricingDTO->memberOnlinePayPricing->couponDiscountedDays;
            $this->discountAmount = round($reservationPricingDTO->memberOnlinePayPricing->couponDiscountAmount, 2);
            $this->stateTax = round($reservationPricingDTO->memberOnlinePayPricing->stateTax ?? 0, 2);
            $this->averageRate = round($reservationPricingDTO->memberOnlinePayPricing->averageRate, 2);
            $this->total = round($reservationPricingDTO->memberOnlinePayPricing->total, 2);
            $this->points = $reservationPricingDTO->memberOnlinePayPricing->points;
            $this->subTotal = round($reservationPricingDTO->memberOnlinePayPricing->subTotal, 2);
        }
        return [
            'discountedDay' => $this->discountedDay,
            'discountAmount' => $this->discountAmount,
            'stateTax' => $this->stateTax,
            'averageRate' => $this->averageRate,
            'total' => $this->total,
            'points' => $this->points,
            'subTotal' => $this->subTotal,
            'paymentType' => $this->paymentType,
            'coupon' => $this->coupon,
            'cityTax' => $this->cityTax,
            'portFee' => $this->portFee,
            'extraFee' => $this->extraFee,
            'extendFee' => $this->extendFee,
        ];
    }
}
