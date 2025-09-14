<?php

namespace App\DTO\Pricing;

use Payment;

class AnonPricingDTO
{
    public $paymentType, $coupon, $discountedDay, $discountAmount, $stateTax, $averageRate, $total, $points, $subTotal, $cityTax, $portFee, $extraFee, $extendFee;

    public function anonPricing($reservationDTO, $reservationPricingDTO, $coupon, $paymentType = null)
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
        $this->discountedDay = $reservationPricingDTO->anonNonOnlinePayPricing->couponDiscountedDays;
        $this->discountAmount = round($reservationPricingDTO->anonNonOnlinePayPricing->couponDiscountAmount, 2);
        $this->stateTax = round($reservationPricingDTO->anonNonOnlinePayPricing->stateTax ?? 0, 2);
        $this->averageRate = round($reservationPricingDTO->anonNonOnlinePayPricing->averageRate, 2);
        $this->subTotal = round($reservationPricingDTO->anonNonOnlinePayPricing->subTotal, 2);
        $this->total = round($reservationPricingDTO->anonNonOnlinePayPricing->total, 2);

        return [
            'discountedDay' => $this->discountedDay,
            'discountAmount' => $this->discountAmount,
            'stateTax' => $this->stateTax,
            'averageRate' => $this->averageRate,
            'subTotal' => $this->subTotal,
            'total' => $this->total,
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
        $this->discountedDay = $reservationPricingDTO->anonOnlinePayPricing->couponDiscountedDays;
        $this->discountAmount = round($reservationPricingDTO->anonOnlinePayPricing->couponDiscountAmount, 2);
        $this->stateTax = round($reservationPricingDTO->anonOnlinePayPricing->stateTax ?? 0, 2);
        $this->averageRate = round($reservationPricingDTO->anonOnlinePayPricing->averageRate, 2);
        $this->subTotal = round($reservationPricingDTO->anonOnlinePayPricing->subTotal, 2);
        $this->total = round($reservationPricingDTO->anonOnlinePayPricing->total, 2);

        return [
            'discountedDay' => $this->discountedDay,
            'discountAmount' => $this->discountAmount,
            'stateTax' => $this->stateTax,
            'averageRate' => $this->averageRate,
            'subTotal' => $this->subTotal,
            'total' => $this->total,
            'paymentType' => $this->paymentType,
            'coupon' => $this->coupon,
            'cityTax' => $this->cityTax,
            'portFee' => $this->portFee,
            'extraFee' => $this->extraFee,
            'extendFee' => $this->extendFee,
        ];
    }
}
