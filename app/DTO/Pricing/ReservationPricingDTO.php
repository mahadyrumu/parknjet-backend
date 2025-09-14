<?php

namespace App\DTO\Pricing;
use Illuminate\Support\Facades\Log;

class ReservationPricingDTO
{
    public $lotType, $cityTax, $portFee, $extraFee, $currency, $walletDays, $markUpPercent, $coupon;
    private $vehicleLength, $dropOffTime, $pickUpTime, $pref, $couponCode;
    public $onlinePaymentRange, $memberOnlinePayPricing, $memberOnlineReferencePricing, $memberNotOnlinePayPricing, $memberNonOnlineReferencePricing, $anonOnlinePayPricing, $anonNonOnlinePayPricing;

    public function __construct(
        $lotType,
        $vehicleLength,
        $dropOffTime,
        $pickUpTime,
        $pref,
        $extraFee
    ) {
        $this->lotType = $lotType;
        $this->vehicleLength = $vehicleLength;
        $this->dropOffTime = $dropOffTime;
        $this->pickUpTime = $pickUpTime;
        $this->pref = $pref;
        $this->extraFee = $extraFee;

        $this->memberOnlinePayPricing = new PricingBaseDTO(true, "ONLINE");
        $this->memberOnlineReferencePricing = new PricingBaseDTO(true, "ONLINE");
        $this->memberNotOnlinePayPricing = new PricingBaseDTO(true, "NOT_ONLINE");
        $this->memberNonOnlineReferencePricing = new PricingBaseDTO(true, "NOT_ONLINE");
        $this->anonOnlinePayPricing = new PricingBaseDTO(false, "ONLINE");
        $this->anonNonOnlinePayPricing = new PricingBaseDTO(false, "NOT_ONLINE");
    }

    public function setDefaultPricing($defaultPricing)
    {
        $this->currency = env('CURRENCY');
        if ($defaultPricing != null) {
            $this->cityTax = $defaultPricing->cityTax;
            $this->portFee = $defaultPricing->portFee;
        }
    }

    public function setMemberOnlinePayAverageRate($averageRate, $stateTaxPercent, $memberOnlinePayDurationInDay)
    {
        $this->memberOnlinePayPricing->durationInDay = $memberOnlinePayDurationInDay;
        $this->memberOnlinePayPricing->averageRate = round(toDollar($averageRate), 2);
        $this->memberOnlinePayPricing->affectedAverageRate = divisionByZero($this->extraFee, $this->memberOnlinePayPricing->durationInDay) + $this->memberOnlinePayPricing->averageRate;
        $affectedSubTotal = round($this->memberOnlinePayPricing->affectedAverageRate * $this->memberOnlinePayPricing->durationInDay, 2);
        $subTotal = round(toDollar($averageRate * $this->memberOnlinePayPricing->durationInDay), 2);
        $this->memberOnlinePayPricing->subTotal = $affectedSubTotal;
        $this->memberOnlinePayPricing->stateTax = round(toDollar($subTotal * $stateTaxPercent), 2);
        $this->memberOnlinePayPricing->total = round($affectedSubTotal + $this->cityTax + $this->portFee + $this->memberOnlinePayPricing->stateTax, 2);
        $this->memberOnlinePayPricing->affectedAverageRate = round($this->memberOnlinePayPricing->affectedAverageRate, 2);
        $this->memberOnlinePayPricing->points = intval(($subTotal * 100) / 10);
    }

    public function setMemberOnlinePayReferenceAvarageRate($averageRate, $stateTaxPercent, $memberOnlinePayDurationInDay)
    {
        $this->memberOnlineReferencePricing->durationInDay = $memberOnlinePayDurationInDay;
        $this->memberOnlineReferencePricing->averageRate = round(toDollar($averageRate), 2);
        $this->memberOnlineReferencePricing->affectedAverageRate = divisionByZero($this->extraFee, $this->memberOnlineReferencePricing->durationInDay) + $this->memberOnlineReferencePricing->averageRate;
        $affectedSubTotal = round($this->memberOnlineReferencePricing->affectedAverageRate * $this->memberOnlineReferencePricing->durationInDay, 2);
        $subTotal = round(toDollar($averageRate * $this->memberOnlineReferencePricing->durationInDay), 2);
        $this->memberOnlineReferencePricing->subTotal = $affectedSubTotal;
        $this->memberOnlineReferencePricing->stateTax = round(toDollar($subTotal * $stateTaxPercent), 2);
        $this->memberOnlineReferencePricing->total = round($affectedSubTotal + $this->cityTax + $this->portFee + $this->memberOnlineReferencePricing->stateTax, 2);
        $this->memberOnlineReferencePricing->affectedAverageRate = round($this->memberOnlineReferencePricing->affectedAverageRate, 2);
        $subTotalDiff = $this->memberOnlineReferencePricing->subTotal - $this->memberOnlinePayPricing->subTotal;
        Log::info("memberOnlineReferencePricing->subTotal = " . $this->memberOnlineReferencePricing->subTotal);
        Log::info("memberOnlinePayPricing->subTotal = " . $this->memberOnlinePayPricing->subTotal);
        Log::info("Subtotal Diff = " . $subTotalDiff);
        $subTotalDiffPoints = intval($subTotalDiff * 110);
        Log::info("subTotalDiffPoints = " . $subTotalDiffPoints);
        Log::info("memberOnlinePayPricing.points = " . $this->memberOnlinePayPricing->points);
        $this->memberOnlineReferencePricing->points = $subTotalDiffPoints + $this->memberOnlinePayPricing->points;
    }

    public function setMemberNotOnlinePayAverageRate($averageRate, $stateTaxPercent, $memberNotOnlineDurationInDay)
    {
        $this->memberNotOnlinePayPricing->durationInDay = $memberNotOnlineDurationInDay;
        $this->memberNotOnlinePayPricing->averageRate = round(toDollar($averageRate), 2);
        $this->memberNotOnlinePayPricing->affectedAverageRate = divisionByZero($this->extraFee, $this->memberNotOnlinePayPricing->durationInDay) + $this->memberNotOnlinePayPricing->averageRate;
        $affectedSubTotal = round($this->memberNotOnlinePayPricing->affectedAverageRate * $this->memberNotOnlinePayPricing->durationInDay, 2);
        $subTotal = round(toDollar($averageRate * $this->memberNotOnlinePayPricing->durationInDay), 2);
        $this->memberNotOnlinePayPricing->subTotal = $affectedSubTotal;
        $this->memberNotOnlinePayPricing->stateTax = round(toDollar($subTotal * $stateTaxPercent), 2);
        $this->memberNotOnlinePayPricing->total = round($affectedSubTotal + $this->cityTax + $this->portFee + $this->memberNotOnlinePayPricing->stateTax, 2);
        $this->memberNotOnlinePayPricing->affectedAverageRate = round($this->memberNotOnlinePayPricing->affectedAverageRate, 2);
        $this->memberNotOnlinePayPricing->extraAmount = round($this->memberNotOnlinePayPricing->subTotal - $this->memberOnlinePayPricing->subTotal, 2);
        $this->memberNotOnlinePayPricing->points = $this->memberOnlinePayPricing->points;
    }

    public function setMemberNotOnlinePayReferenceAvarageRate($averageRate, $stateTaxPercent, $memberNotOnlineDurationInDay)
    {
        $this->memberNonOnlineReferencePricing->durationInDay = $memberNotOnlineDurationInDay;
        $this->memberNonOnlineReferencePricing->averageRate = round(toDollar($averageRate), 2);
        $this->memberNonOnlineReferencePricing->affectedAverageRate = divisionByZero($this->extraFee, $this->memberNonOnlineReferencePricing->durationInDay) + $this->memberNonOnlineReferencePricing->averageRate;
        $affectedSubTotal = round($this->memberNonOnlineReferencePricing->affectedAverageRate * $this->memberNonOnlineReferencePricing->durationInDay, 2);
        $subTotal = round(toDollar($averageRate * $this->memberNonOnlineReferencePricing->durationInDay), 2);
        $this->memberNonOnlineReferencePricing->subTotal = $affectedSubTotal;
        $this->memberNonOnlineReferencePricing->stateTax = round(toDollar($subTotal * $stateTaxPercent), 2);
        $this->memberNonOnlineReferencePricing->total = round($affectedSubTotal + $this->cityTax + $this->portFee + $this->memberNonOnlineReferencePricing->stateTax, 2);
        $this->memberNonOnlineReferencePricing->affectedAverageRate = round($this->memberNonOnlineReferencePricing->affectedAverageRate, 2);
        $this->memberNonOnlineReferencePricing->extraAmount = round($this->memberNonOnlineReferencePricing->subTotal - $this->memberOnlinePayPricing->subTotal, 2);
        $this->memberNonOnlineReferencePricing->points = $this->memberOnlineReferencePricing->points;
    }

    public function setNonMemberOnlinePayAverageRate($averageRate, $stateTaxPercent, $anonOnlineDurationInDay)
    {
        $this->anonOnlinePayPricing->durationInDay = $anonOnlineDurationInDay;
        $this->anonOnlinePayPricing->averageRate = round(toDollar($averageRate), 2);
        $this->anonOnlinePayPricing->affectedAverageRate = divisionByZero($this->extraFee, $this->anonOnlinePayPricing->durationInDay) + $this->anonOnlinePayPricing->averageRate;
        $affectedSubTotal = round($this->anonOnlinePayPricing->affectedAverageRate * $this->anonOnlinePayPricing->durationInDay, 2);
        $subTotal = round(toDollar($averageRate * $this->anonOnlinePayPricing->durationInDay), 2);
        $this->anonOnlinePayPricing->subTotal = $affectedSubTotal;
        $this->anonOnlinePayPricing->stateTax = round(toDollar($subTotal * $stateTaxPercent), 2);
        $this->anonOnlinePayPricing->total = round($affectedSubTotal + $this->cityTax + $this->portFee + $this->anonOnlinePayPricing->stateTax, 2);
        $this->anonOnlinePayPricing->affectedAverageRate = round($this->anonOnlinePayPricing->affectedAverageRate, 2);
        $this->anonOnlinePayPricing->extraAmount = round($this->anonOnlinePayPricing->subTotal - $this->memberOnlinePayPricing->subTotal, 2);
    }

    public function setNonMemberNonOnlinePayAverageRate($averageRate, $stateTaxPercent, $anonNonOnlineDurationInDay)
    {
        $this->anonNonOnlinePayPricing->durationInDay = $anonNonOnlineDurationInDay;
        $this->anonNonOnlinePayPricing->averageRate = round(toDollar($averageRate), 2);
        $this->anonNonOnlinePayPricing->affectedAverageRate = divisionByZero($this->extraFee, $this->anonNonOnlinePayPricing->durationInDay) + $this->anonNonOnlinePayPricing->averageRate;
        $affectedSubTotal = round($this->anonNonOnlinePayPricing->affectedAverageRate * $this->anonNonOnlinePayPricing->durationInDay, 2);
        $subTotal = round(toDollar($averageRate * $this->anonNonOnlinePayPricing->durationInDay), 2);
        $this->anonNonOnlinePayPricing->subTotal = $affectedSubTotal;
        $this->anonNonOnlinePayPricing->stateTax = round(toDollar($subTotal * $stateTaxPercent), 2);
        $this->anonNonOnlinePayPricing->total = round($affectedSubTotal + $this->cityTax + $this->portFee + $this->anonNonOnlinePayPricing->stateTax, 2);
        $this->anonNonOnlinePayPricing->affectedAverageRate = round($this->anonNonOnlinePayPricing->affectedAverageRate, 2);
        Log::info("anonNonOnlinePayPricing->subTotal = " . $this->anonNonOnlinePayPricing->subTotal);
        Log::info("memberOnlinePayPricing->subTotal = " . $this->memberOnlinePayPricing->subTotal);
        $this->anonNonOnlinePayPricing->extraAmount = round($this->anonNonOnlinePayPricing->subTotal - $this->memberOnlinePayPricing->subTotal, 2);
        Log::info("anonNonOnlinePayPricing->extraAmount = " . $this->anonNonOnlinePayPricing->extraAmount);
    }
}
