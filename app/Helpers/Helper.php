<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

// not using it
if (!function_exists('genSSH512')) {
    function genSSH512($value)
    {
        $salt = Str::random(1024);

        return hash('sha256', $value);
    }
}

if (!function_exists('dateDuration')) {
    function dateDuration($toDate, $fromDate)
    {
        $toDate = Carbon::parse($toDate);
        $fromDate = Carbon::parse($fromDate);

        $days = $toDate->diffInDays($fromDate);
        return $days;
    }
}

if (!function_exists('ceiling')) {
    function ceiling($number, $significance = 1)
    {
        return (is_numeric($number) && is_numeric($significance)) ? number_format((ceil($number / $significance) * $significance), 2) : false;
    }
}

if (!function_exists('toCents')) {
    function toCents($amount)
    {
        return $amount * 100;
    }
}

if (!function_exists('toDollar')) {
    function toDollar($amount)
    {
        return $amount / 100;
    }
}

if (!function_exists('moneyFormat')) {
    function moneyFormat($amount)
    {
        $money = new Money($amount, new Currency(env('CURRENCY')));
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);
        return $moneyFormatter->format($money);
    }
}

if (!function_exists('moneyParser')) {
    function moneyParser($amount)
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $money = $moneyParser->parse($amount, new Currency(env('CURRENCY')));
        return $money->getAmount();
    }
}

if (!function_exists('isBeforeNow')) {
    function isBeforeNow($date)
    {
        return strtotime(date("Y-m-d H:i:s")) >= strtotime($date) ? 0 : 1;
    }
}

if (!function_exists('divisionByZero')) {
    function divisionByZero($numOne, $numTwo)
    {
        if ($numOne == 0 || $numTwo == 0) {
            return 0;
        } else {
            return $numOne / $numTwo;
        }
    }
}

if (!function_exists('getLotIdFromClaimId')) {
    function getLotIdFromClaimId($claim_id)
    {
        $lot_id = "";
        if ($claim_id >= 50000 && $claim_id < 100000) {
            $lot_id = 2;
        } else if ($claim_id >= 1000 && $claim_id < 50000) {
            $lot_id = 1;
        }
        return $lot_id;
    }
}

if (!function_exists('getNonAuthHTTPHeader')) {
    function getNonAuthHTTPHeader()
    {
        return ['Content-Type' => 'application/json'];
    }
}

if (!function_exists('isError')) {
    function isError($status)
    {
        if ($status->clientError() || $status->serverError()) {
            return true;
        }
        return false;
    }
}

if (!function_exists('removeCreatedAtAndUpdatedAt')) {
    function removeCreatedAtAndUpdatedAt($reservation)
    {
        $createdDates = date("Y-m-d H:i:s", strtotime($reservation->createdDate));
        // $lastModifiedDates = date("Y-m-d H:i:s", strtotime($reservation->lastModifiedDate));
        // unset($reservation['createdDate']);
        if ($reservation->status == 'CANCELLED') {
            $reservation->deleted = true;
        }
        $reservation->createdDate = $createdDates;
        $reservation->source = "PNJ";
        $reservation->driver->fullName = $reservation->driver->full_name;
        unset($reservation['created_at']);
        unset($reservation['lastModifiedDate']);
        unset($reservation->vehicle['createdDate']);
        unset($reservation->vehicle['lastModifiedDate']);
        unset($reservation->driver['createdDate']);
        unset($reservation->driver['lastModifiedDate']);

        if ($reservation->owner) {
            unset($reservation->owner['created']);
            unset($reservation->owner['updated']);
            unset($reservation->owner['email_verified_at']);
            if($reservation->owner->wallet){
                unset($reservation->owner->wallet['createdDate']);
                unset($reservation->owner->wallet['lastModifiedDate']);
            }
        }
        if($reservation->wallet_transaction){
            foreach($reservation->wallet_transaction as $key => $txn){
                unset($txn['createdDate']);
                unset($txn['lastModifiedDate']);
            }
        }
        if($reservation->pre_paid_wallet_txns){
            foreach($reservation->pre_paid_wallet_txns as $key => $txn){
                unset($txn['createdDate']);
                unset($txn['lastModifiedDate']);
            }
        }
        foreach ($reservation->pricing as $key => $pricing) {
            unset($pricing['createdDate']);
            unset($pricing['lastModifiedDate']);
            if ($pricing->payment) {
                unset($pricing->payment['createdDate']);
                unset($pricing->payment['lastModifiedDate']);
                if ($pricing->payment->cardType) {
                    $cardType = $pricing->payment->cardType;
                    $pricing->payment->cardType = isset(Payment::CardType[$cardType]) ? Payment::CardType[$cardType] : $cardType;
                }
            }
        }
        unset($reservation['pricingList']);
        $reservation->pricingList = $reservation->pricing;
        unset($reservation['pricing']);
        return $reservation;
    }
}
