<?php

namespace App\Services\Pricing;

use App\DTO\Pricing\ExtendReservationPricingDTO;
use App\DTO\Pricing\ReservationPricingDTO;
use App\Exceptions\PNJException;
use App\Services\BlackOut\BlackOutService;
use App\Services\Coupon\CouponService;
use App\Services\Reservation\ReservationService;
use App\Services\Reservation\JwtService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Log;
use stdClass;

class PricingService
{
    public function trimPricingData($getPricing)
    {
        $pricingData = new stdClass;
        $pricingData->cityTax = $getPricing->cityTax;
        $pricingData->portFee = $getPricing->portFee;
        $pricingData->currency = $getPricing->currency;
        $pricingData->lotType = $getPricing->lotType;
        $pricingData->extraFee = $getPricing->extraFee;
        $pricingData->isCouponValid = $getPricing->coupon;

        $pricingData->online = $getPricing->memberOnlinePayPricing;
        $pricingData->onlineReference = $getPricing->memberOnlineReferencePricing;
        $pricingData->nonOnline = $getPricing->memberNotOnlinePayPricing;
        $pricingData->nonOnlineReference = $getPricing->memberNonOnlineReferencePricing;
        $pricingData->online->savedAmountByBeingMember = round($getPricing->memberNonOnlineReferencePricing->subTotal - $getPricing->memberOnlinePayPricing->subTotal, 2);

        if (!auth()->user()) {
            $pricingData->onlineAnon = $getPricing->anonOnlinePayPricing;
            $pricingData->nonOnlineAnon = $getPricing->anonNonOnlinePayPricing;
        }
        return $pricingData;
    }

    public function trimExtendPricingData($getPricing)
    {
        $pricingData = new stdClass;
        $pricingData->coupon = $getPricing->coupon;
        $pricingData->extendFee = $getPricing->extendFee;

        if (auth()->user()) {
            $pricingData->online = $getPricing->memberOnlinePayPricing;
        } else {
            $pricingData->onlineAnon = $getPricing->anonOnlinePayPricing;
        }
        return $pricingData;
    }

    public function getPricing($lotType, $dropOffTime, $pickUpTime, $pref, $vehicleLength, $couponCode, $walletDays, $extendfees = false)
    {
        Log::info($lotType . " " . $dropOffTime . " " . $pickUpTime . " " . $pref . " " . $vehicleLength . " " . $couponCode . " " . $walletDays . " " . $extendfees);

        $doDate = date("Y-m-d", strtotime($dropOffTime));
        $puDate = date("Y-m-d", strtotime($pickUpTime));
        $today = date("Y-m-d");

        if ($doDate > $puDate) {
            Log::info("Exception: pickup time can not be before than the drop off time");
            throw new PNJException("Exception: pickup time can not be before than the drop off time");
        }

        if ($today > $doDate) {
            Log::info("Exception: dropoff time can not be before than the today");
            throw new PNJException("Exception: dropoff time can not be before than the today");
        }

        $blackOutService = new BlackOutService;
        $blackOutService->checkForBlackOut($lotType, $doDate, $puDate, $pref);

        $onlinePaymentService = new OnlinePaymentRangeService;
        $onlinePaymentRange = $onlinePaymentService->getPaymentRangeIfAvailable($lotType, $dropOffTime);

        $defaultPricingService = new DefaultPricingService;
        $defaultPricing = $defaultPricingService->getDefaultPricing($lotType);

        $extraFee = 0;

        if ($doDate == date("Y-m-d")) {
            if ($defaultPricing->sameDayExtraFee != null) {
                $extraFee = $defaultPricing->sameDayExtraFee;
            }
        }

        try {
            if (!$extendfees) {
                $reservationPricingDTO = new ReservationPricingDTO($lotType, $vehicleLength, $dropOffTime, $pickUpTime, $pref, $extraFee);
                $reservationPricingDTO->setDefaultPricing($defaultPricing);
            } else {
                $reservationPricingDTO = new ExtendReservationPricingDTO($extendfees);
                $reservationPricingDTO->setDefaultPricing($defaultPricing);
            }
        } catch (\Exception $exception) {
            throw new PNJException($exception->getMessage());
        }

        $reservationPricingDTO->onlinePaymentRange = $onlinePaymentRange;

        if ($couponCode != null) {
            $couponCode = trim($couponCode);

            $couponService = new CouponService;
            $couponDB = $couponService->findByCode($couponCode);

            if (!$couponService->isCouponValidForReservation($lotType, (auth()->user() ? true : false), true, $couponCode, $couponDB)) {
                $couponCode = false;
                $reservationPricingDTO->coupon = false;
            } else {
                $reservationPricingDTO->coupon = true;
            }
        }

        $reservationService = new ReservationService;
        $originalDurationInDay = $reservationService->getDurationInDay($dropOffTime, $pickUpTime);

        if ($originalDurationInDay > env('MAX_DURATION')) {
            Log::info("User requested reservation duration " . $originalDurationInDay . " is greater max allowed " . env('MAX_DURATION'));
            throw new PNJException("User requested reservation duration " . $originalDurationInDay . " is greater max allowed " . env('MAX_DURATION'));
        }

        $walletDiscountDays = $this->calculateWalletDays($walletDays, $originalDurationInDay);
        Log::info("The wallet discount days are " . $walletDiscountDays);

        //Calculate Pricing if its member and paying online
        $originalSubTotalForMemberOnlinePay = 0;
        $originalSubTotalForMemberOnlinePay = $this->calculateSubTotal($dropOffTime, $pickUpTime, $originalSubTotalForMemberOnlinePay, $defaultPricing, $originalDurationInDay, $vehicleLength, $pref, $lotType);

        // $originalDurationInDay = toCents($originalDurationInDay);

        $originalAverageRate = 0;
        if ($originalDurationInDay != 0) {
            Log::info("originalDurationInDay greater 1");
            $originalAverageRate = ceil($originalSubTotalForMemberOnlinePay / $originalDurationInDay);
        }

        Log::info("Original subtotal for original duration " . $originalDurationInDay . " without coupon is " . $originalSubTotalForMemberOnlinePay);

        Log::info("memberOnlinePayPricing Duration before coupon applied " . $originalDurationInDay);
        $discountedDays = $this->getDiscountedDaysForACoupon($lotType, true, true, $couponCode, $originalDurationInDay);
        Log::info("memberOnlinePayPricing Coupon Discounted days " . $discountedDays);

        $memberOnlinePayDurationInDay = $originalDurationInDay - $discountedDays - $walletDiscountDays;
        if ($memberOnlinePayDurationInDay < 0) {
            $memberOnlinePayDurationInDay = 0;
        }
        Log::info("memberOnlinePayPricing Duration after coupon applied " . $memberOnlinePayDurationInDay);

        $subTotalForMemberOnlinePay = 0;
        $subTotalForMemberOnlinePay = ceil($originalAverageRate * $memberOnlinePayDurationInDay);
        Log::info("Member Online Pay subtotal for duration " . $memberOnlinePayDurationInDay . " after coupon is " . $subTotalForMemberOnlinePay);

        $averageRate = $this->calculateAverageRate($memberOnlinePayDurationInDay, $subTotalForMemberOnlinePay, $defaultPricing);
        $jwtService = new JwtService;
        $this->getMemberOnlinePayPricing($jwtService, $reservationPricingDTO, $averageRate, $defaultPricing, $memberOnlinePayDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType);

        if (!$extendfees) {
            //Calculate Reference Pricing if its member and paying online
            $referenceOnlinePricingSubTotal = $this->calculateReferencePricingSubTotal($defaultPricing, $memberOnlinePayDurationInDay, $vehicleLength, $pref);

            $this->getMemberOnlineReferencePayPricing($jwtService, $reservationPricingDTO, $referenceOnlinePricingSubTotal, $defaultPricing, $memberOnlinePayDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType);

            //Calculate Pricing if its member and not paying online
            Log::info("memberNotOnlinePayPricing Duration before coupon applied " . $originalDurationInDay);
            $discountedDays = $this->getDiscountedDaysForACoupon($lotType, true, false, $couponCode, $originalDurationInDay);
            Log::info("memberNotOnlinePayPricing Coupon Discounted days " . $discountedDays);

            $memberNotOnlineDurationInDay = $originalDurationInDay - $discountedDays - $walletDiscountDays;
            if ($memberNotOnlineDurationInDay < 0) {
                $memberNotOnlineDurationInDay = 0;
            }

            $subTotalForMemberNotOnlinePay = $this->calculateSubTotalForMemberNotOnlinePay($memberNotOnlineDurationInDay, $originalAverageRate, $defaultPricing);
            $memberNotOnlineAverageRate = $this->calculateMemberNotOnlineAverageRate($memberNotOnlineDurationInDay, $subTotalForMemberNotOnlinePay, $defaultPricing, $averageRate);

            $this->getMemberNotOnlineReferencePayPricing($jwtService, $reservationPricingDTO, $memberNotOnlineAverageRate, $defaultPricing, $memberNotOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType);


            //Calculate Reference Pricing if its member and not paying online
            $this->getMemberNonOnlineReferencePricing($jwtService, $reservationPricingDTO, $referenceOnlinePricingSubTotal, $subTotalForMemberNotOnlinePay, $defaultPricing, $memberOnlinePayDurationInDay, $memberNotOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType);
        }

        if (!auth()->user()) {

            //Calculate Pricing if its anon member and paying online
            Log::info("anonOnlinePayPricing Duration before coupon applied " . $originalDurationInDay);
            $discountedDays = $this->getDiscountedDaysForACoupon($lotType, false, true, $couponCode, $originalDurationInDay);
            Log::info("anonOnlinePayPricing Coupon Discounted days " . $discountedDays);
            $anonOnlineDurationInDay = $originalDurationInDay - $discountedDays;
            if ($anonOnlineDurationInDay < 0) {
                $anonOnlineDurationInDay = 0;
            }
            Log::info("anonOnlinePayPricing Duration after coupon applied " . $anonOnlineDurationInDay);
            $subTotalForAnonOnlinePay = $this->calculateSubTotalForAnonOnlinePay($averageRate, $anonOnlineDurationInDay, $memberOnlinePayDurationInDay, $subTotalForMemberOnlinePay, $defaultPricing);
            $nonMemberAverageRate = $this->calculateAnonMemberAverageRate($anonOnlineDurationInDay, $subTotalForAnonOnlinePay, $defaultPricing, $averageRate);
            $this->getAnonOnlinePayPricing($jwtService, $reservationPricingDTO, $nonMemberAverageRate, $defaultPricing, $anonOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $lotType);

            if (!$extendfees) {
                //Calculate Pricing if its anon member and not paying online
                Log::info("anonNonOnlinePayPricing Duration before coupon applied " . $originalDurationInDay);
                $discountedDays = $this->getDiscountedDaysForACoupon($lotType, false, false, $couponCode, $originalDurationInDay);
                Log::info("anonNonOnlinePayPricing Coupon Discounted days " . $discountedDays);
                $anonNonOnlineDurationInDay = $originalDurationInDay - $discountedDays;
                if ($anonNonOnlineDurationInDay < 0) {
                    $anonNonOnlineDurationInDay = 0;
                }
                Log::info("anonNonOnlinePayPricing Duration after coupon applied " . $anonNonOnlineDurationInDay);
                $subTotalForNonMemberNotOnlinePay = $this->calculateSubTotalForAnonNotOnlinePay($originalAverageRate, $anonNonOnlineDurationInDay, $memberOnlinePayDurationInDay, $subTotalForMemberOnlinePay, $defaultPricing);
                $nonMemberNotOnlineAverageRate = $this->calculateAnonMemberNotOnlineAverageRate($anonNonOnlineDurationInDay, $subTotalForNonMemberNotOnlinePay, $defaultPricing, $nonMemberAverageRate, $averageRate);
                $this->getAnonNotOnlinePayPricing($jwtService, $reservationPricingDTO, $nonMemberNotOnlineAverageRate, $defaultPricing, $anonNonOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $lotType);
            }
        }
        // Log::info((array)$reservationPricingDTO);

        return $reservationPricingDTO;
    }

    public function calculateSubTotal($dropOffTime, $pickUpTime, $subTotal, $defaultPricing, $durationInDay, $vehicleLength, $pref, $lotType)
    {
        Log::info("Duration in days = " . $durationInDay);
        Log::info("Floored version = " . floor($durationInDay));

        $halfDayBasedOnDropOff = true;
        $i = 0;

        if ($durationInDay != floor($durationInDay)) {
            Log::info("Pricing has half days");
            if (date("A", strtotime($dropOffTime)) == 'PM') {
                Log::info("Half day is based on drop off time");
                $i = 1;
            } else if (date("A", strtotime($pickUpTime)) == 'AM') {
                Log::info("Half day is based on pickup time");
                $halfDayBasedOnDropOff = false;
            }
        }

        for (; $durationInDay != floor($durationInDay) ? ($halfDayBasedOnDropOff ? $i <= floor($durationInDay) : $i < floor($durationInDay)) : $i < floor($durationInDay); $i++) {
            $localDate = Carbon::parse($dropOffTime)->addDays($i)->toDateString();
            Log::info("Date to add : " . $localDate);

            $datePricingService = new DatePricingService;
            $byDatePricing = $datePricingService->findByDateAndLotType($localDate, $lotType);

            if ($byDatePricing === null) {
                // $dayOfWeek = date('l', strtotime($localDate));
                $dayOfWeek = date("w", strtotime($localDate));
                $dayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek;
                Log::info("Day value = " . $dayOfWeek);

                $dayOfWeekPricingService = new DayOfWeekPricingService;
                $byDayOfTheWeekPricing = $dayOfWeekPricingService->findByDayOfTheWeekAndLotType($dayOfWeek, $lotType);

                if ($byDayOfTheWeekPricing === null) {
                    if ($defaultPricing !== null) {
                        $subTotal = toCents($defaultPricing->memberOnlinePayValetParkStandardVehicleRate) + $subTotal;
                    } else {
                        throw new PNJException(" Pricing not configured. Please contact administrator.");
                    }
                } else {
                    $subTotal = toCents($byDayOfTheWeekPricing->onlinePayValetParkStandardVehicleRate) + $subTotal;
                }
            } else {
                $subTotal = toCents($byDatePricing->onlinePayValetParkStandardVehicleRate) + $subTotal;
            }
        }

        if ($durationInDay != round($durationInDay)) {
            $halfPrice = null;
            Log::info(" Subtotal before half day calculation = " . $subTotal);
            $localDate = null;
            if (date("A", strtotime($dropOffTime)) == 'PM') {
                Log::info("Getting half day based on dropoff time");
                $localDate = date("Y-m-d", strtotime($dropOffTime));
            } else if (date("A", strtotime($pickUpTime)) == 'AM') {
                Log::info("Getting half day based on pickup time");
                $localDate = date("Y-m-d", strtotime($pickUpTime));
            }

            Log::info("Finding half date pricing for " . $localDate);

            $datePricingService = new DatePricingService;
            $byDatePricing = $datePricingService->findByDateAndLotType($localDate, $lotType);

            if ($byDatePricing == null) {
                $dayOfWeek = date("w", strtotime($localDate));
                $dayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek;
                Log::info("Day value = " . $dayOfWeek);
                Log::info("Date pricing null, Looking for Day of week pricing :" . date("l", strtotime($localDate)));

                $dayOfWeekPricingService = new DayOfWeekPricingService;
                $byDayOfTheWeekPricing = $dayOfWeekPricingService->findByDayOfTheWeekAndLotType($dayOfWeek, $lotType);

                if ($byDayOfTheWeekPricing == null) {
                    Log::info("Day of week pricing null using default pricing , Rate per day  = " . toCents($defaultPricing->memberOnlinePayValetParkStandardVehicleRate));
                    if ($defaultPricing != null) {
                        $halfPrice = ceil(toCents($defaultPricing->memberOnlinePayValetParkStandardVehicleRate) / 2);
                        Log::info("1 Half day price " . $halfPrice);
                        $subTotal = $halfPrice + $subTotal;
                    } else {
                        throw new PNJException(" Pricing not configured. Please contact administrator.");
                    }
                } else {
                    Log::info("Day of week pricing found with id " . $byDayOfTheWeekPricing->id . ", Rate per day  = " . toCents($byDayOfTheWeekPricing->onlinePayValetParkStandardVehicleRate));
                    $halfPrice = ceil(toCents($byDayOfTheWeekPricing->onlinePayValetParkStandardVehicleRate) / 2);
                    Log::info("2 Half day price " . $halfPrice);
                    $subTotal = $halfPrice + $subTotal;
                }
            } else {
                Log::info("Date pricing found with id " . $byDatePricing->id . ", Rate per day  = " . toCents($byDatePricing->onlinePayValetParkStandardVehicleRate));
                $halfPrice = ceil(toCents($byDatePricing->onlinePayValetParkStandardVehicleRate) / 2);
                Log::info("3 Half day price " . $halfPrice);
                $subTotal = $halfPrice + $subTotal;
            }
            Log::info("Subtotal after half day calculation " . $subTotal);
        }

        if ($vehicleLength == 'LARGE') {
            $subTotal = ceil($subTotal + ($subTotal * $defaultPricing->largeVehicleMarkUpPercent));
        } else if ($vehicleLength == 'EXTRA_LARGE') {
            $subTotal = ceil($subTotal + ($subTotal * $defaultPricing->extraLargeVehicleMarkUpPercent));
        }

        if ($pref == "SELF") {
            $subTotal = ceil($subTotal + ($subTotal * $defaultPricing->selfParkMarkUpPercent));
        }

        return $subTotal;
    }

    public function getDiscountedDaysForACoupon($lotType, $isMember, $isPayingOnline, $couponCode, $durationInDay)
    {
        Log::info("Checking if coupon code " . $couponCode . " is valid and is applicable to current user with member flag " . $isMember);
        $discountedDays = 0.0;
        $couponDB = null;

        if ($couponCode != null && strlen(trim($couponCode)) > 0) {

            $couponService = new CouponService;
            $couponDB = $couponService->findByCode($couponCode);
            if ($couponService->isCouponValidForReservation($lotType, $isMember, $isPayingOnline, $couponCode, $couponDB)) {
                Log::info("Applying days coupon code = " . $couponDB->code . " to durationIndays = " . $durationInDay);
                $discountedDays = $durationInDay - $couponDB->promotion->days;
                Log::info("Discounted days is " . $couponDB->promotion->days . " for coupon " . $couponCode . " , After discount new duration should be " . $discountedDays);
                $discountedDays = $couponDB->promotion->days;
            }
        } else {
            Log::info("Coupon code is NULL or empty! , Hence no coupon applied");
        }
        return $discountedDays;
    }

    public function calculateADR($defaultPricing, $durationInDay, $averageRate)
    {
        Log::info("Calculating Pauls pricing , Current averageRate " . $averageRate . " and duration " . $durationInDay);

        if ($durationInDay > $defaultPricing->maxDuration) {

            $MIN_ADR = $defaultPricing->minRate;
            $SEVENTY_PERCENT_RESERVATION_ADR = ceil($averageRate * 0.7);
            Log::info("Fixed MIN_ADR = " . $MIN_ADR . " and 70% = " . $SEVENTY_PERCENT_RESERVATION_ADR);
            $min_adr_double = max($MIN_ADR, $SEVENTY_PERCENT_RESERVATION_ADR);
            $MIN_ADR = $min_adr_double;
            Log::info("NEW MIN ADR = " . $MIN_ADR);
            $MAX_DISCOUNT = ($averageRate - $MIN_ADR);
            $calculatedCurve = pow(($durationInDay / $defaultPricing->maxDuration), $defaultPricing->curveValue);
            $DISCOUNT = ceil($MAX_DISCOUNT * $calculatedCurve);
            $DISCOUNT_ADR = ($averageRate - $DISCOUNT);
            if ($DISCOUNT_ADR < $MIN_ADR) {
                $DISCOUNT_ADR = $MIN_ADR;
            }
            $averageRate = $DISCOUNT_ADR;
        } else {
            Log::info("Duration is less than MIN");
        }

        Log::info("Final Reservation ADR = " . $averageRate);
        return $averageRate;
    }

    public function applyCouponOnAmount($lotType, $isMember, $isPayingOnline, $couponCode, $subTotal)
    {
        Log::info("Checking if coupon code " . $couponCode . " is valid and is applicable to current user with member flag " . $isMember);
        $couponAmount = 0;
        $couponDB = null;
        if ($couponCode != null && strlen(trim($couponCode)) > 0) {

            $couponService = new CouponService;
            $couponDB = $couponService->findByCode($couponCode);
            if ($couponService->isCouponValidForReservation($lotType, $isMember, $isPayingOnline, $couponCode, $couponDB)) {
                Log::info("Applying Amount coupon code = " . $couponDB->code . " to subtotal = " . $subTotal);
                $couponAmount = $subTotal * $couponDB->promotion->discountInPercentage;
                Log::info("Calculated coupon amount = " . $couponAmount);
            }
            return $couponAmount;
        } else {
            Log::info("Coupon code is NULL or empty! , Hence no coupon applied");
            return $couponAmount;
        }
    }

    public function calculateReferencePricingSubTotal($defaultPricing, $durationInDay, $vehicleLength, $pref)
    {
        Log::info("Duration in days = " . $durationInDay);
        $subTotal = toCents($defaultPricing->referencePricingRate) * $durationInDay;

        if ($vehicleLength == "LARGE") {
            $subTotal = ceil($subTotal + ($subTotal * $defaultPricing->largeVehicleMarkUpPercent));
        } elseif ($vehicleLength == "EXTRA_LARGE") {
            $subTotal = ceil($subTotal + ($subTotal * $defaultPricing->extraLargeVehicleMarkUpPercent));
        }

        if ($pref == "SELF") {
            $subTotal = ceil($subTotal + ($subTotal * $defaultPricing->selfParkMarkUpPercent));
        }

        return $subTotal;
    }

    public function calculateWalletDays($walletDays, $originalDurationInDay)
    {
        $walletDiscountDays = 0;
        if ($walletDays != null && $walletDays >= 0) {
            $theRealWalletDaysToBeUsed = $walletDays;

            if ($originalDurationInDay - $walletDays < 0) {
                $theRealWalletDaysToBeUsed = $originalDurationInDay;
            }
            $walletDiscountDays = $theRealWalletDaysToBeUsed;
            //originalDurationInDay = $originalDurationInDay - $walletDiscountDays;
        }
        return $walletDiscountDays;
    }

    public function calculateAverageRate($memberOnlinePayDurationInDay, $subTotalForMemberOnlinePay, $defaultPricing)
    {
        $averageRate = 0;
        if ($memberOnlinePayDurationInDay > 0) {
            $averageRate = ceil($subTotalForMemberOnlinePay / $memberOnlinePayDurationInDay);
            Log::info("Average Rate for Member online pay is " . $averageRate);
            $averageRate = $this->calculateADR($defaultPricing, $memberOnlinePayDurationInDay, $averageRate);
            if ($averageRate < toCents($defaultPricing->minRate)) {
                Log::info("Average Rate calculation after ADR is less than minRate in DB hence setting it to minRate from DB " . $averageRate);
                $averageRate = toCents($defaultPricing->minRate);
            }
        }
        return $averageRate;
    }

    public function calculateMemberNotOnlineAverageRate($memberNotOnlineDurationInDay, $subTotalForMemberNotOnlinePay, $defaultPricing, $averageRate)
    {
        $memberNotOnlineAverageRate = 0;
        if ($memberNotOnlineDurationInDay > 0) {
            $memberNotOnlineAverageRate = ceil($subTotalForMemberNotOnlinePay / $memberNotOnlineDurationInDay);
            Log::info("Average Rate for Member not paying online is " . $memberNotOnlineAverageRate);
            $memberNotOnlineAverageRate = $this->calculateADR($defaultPricing, $memberNotOnlineDurationInDay, $memberNotOnlineAverageRate);
            if ($memberNotOnlineAverageRate < toCents($defaultPricing->minRate)) {
                Log::info("Average Rate for Member not paying online after ADR is less than minRate in DB hence setting it to minRate from DB " . $averageRate);
                $memberNotOnlineAverageRate = toCents($defaultPricing->minRate);
            }
        }
        return $memberNotOnlineAverageRate;
    }

    public function calculateSubTotalForMemberNotOnlinePay($memberNotOnlineDurationInDay, $originalAverageRate, $defaultPricing)
    {
        Log::info("memberNotOnlinePayPricing Duration after coupon and discounted days applied " . $memberNotOnlineDurationInDay);
        $subTotalForMemberNotOnlinePay = ceil($originalAverageRate * $memberNotOnlineDurationInDay);
        Log::info("Member Not Online Pay subtotal for  duration " . $memberNotOnlineDurationInDay . " after coupon is " . $subTotalForMemberNotOnlinePay);

        $subTotalForMemberNotOnlinePay = ceil($subTotalForMemberNotOnlinePay + ($subTotalForMemberNotOnlinePay * $defaultPricing->nonOnlinePayMarkUpPercent));
        Log::info("Member Not Online Pay subtotal for  duration " . $memberNotOnlineDurationInDay . " after adding $ for not paying online is " . $subTotalForMemberNotOnlinePay);

        return $subTotalForMemberNotOnlinePay;
    }

    public function calculateSubTotalForAnonOnlinePay($averageRate, $anonOnlineDurationInDay, $memberOnlinePayDurationInDay, $subTotalForMemberOnlinePay, $defaultPricing)
    {
        $subTotalForAnonOnlinePay = ceil($averageRate * $anonOnlineDurationInDay);
        Log::info("Anon user paying online subtotal for duration " . $memberOnlinePayDurationInDay . " after coupon is " . $subTotalForMemberOnlinePay);

        $subTotalForAnonOnlinePay = ceil($subTotalForAnonOnlinePay + ($subTotalForAnonOnlinePay * ($defaultPricing->nonMemberMarkUpPercent)));
        return $subTotalForAnonOnlinePay;
    }

    public function calculateAnonMemberAverageRate($anonOnlineDurationInDay, $subTotalForAnonOnlinePay, $defaultPricing, $averageRate)
    {
        $nonMemberAverageRate = 0;
        if ($anonOnlineDurationInDay > 0) {
            $nonMemberAverageRate = ceil($subTotalForAnonOnlinePay / $anonOnlineDurationInDay);
            Log::info("Average Rate for anon Member paying online is " . $nonMemberAverageRate);
            $nonMemberAverageRate = $this->calculateADR($defaultPricing, $anonOnlineDurationInDay, $nonMemberAverageRate);
            if ($nonMemberAverageRate < toCents($defaultPricing->minRate)) {
                Log::info("Average Rate for anon Member paying online after ADR is less than minRate in DB hence setting it to minRate from DB " . $averageRate);
                $nonMemberAverageRate = toCents($defaultPricing->minRate);
            }
        }
        Log::info("Average Rate for anon member paying online after Paul's curve is " . $nonMemberAverageRate);
        return $nonMemberAverageRate;
    }

    public function calculateSubTotalForAnonNotOnlinePay($originalAverageRate, $anonNonOnlineDurationInDay, $memberOnlinePayDurationInDay, $subTotalForMemberOnlinePay, $defaultPricing)
    {
        $subTotalForNonMemberNotOnlinePay = 0;
        $subTotalForNonMemberNotOnlinePay = ceil($originalAverageRate * $anonNonOnlineDurationInDay);
        Log::info("Anon user not paying online subtotal for  duration " . $memberOnlinePayDurationInDay . " after coupon is " . $subTotalForMemberOnlinePay);

        // subTotalForNonMemberNotOnlinePay = calculateSubTotal(dropOffTime, subTotalForNonMemberNotOnlinePay, defaultPricing, anonOnlineDurationInDay, vehicleLength, pref);

        $subTotalForNonMemberNotOnlinePay = ceil($subTotalForNonMemberNotOnlinePay + ($subTotalForNonMemberNotOnlinePay * $defaultPricing->nonMemberMarkUpPercent));
        $subTotalForNonMemberNotOnlinePay = ceil($subTotalForNonMemberNotOnlinePay + ($subTotalForNonMemberNotOnlinePay * $defaultPricing->nonOnlinePayMarkUpPercent));
        return $subTotalForNonMemberNotOnlinePay;
    }

    public function calculateAnonMemberNotOnlineAverageRate($anonNonOnlineDurationInDay, $subTotalForNonMemberNotOnlinePay, $defaultPricing, $nonMemberAverageRate, $averageRate)
    {
        $nonMemberNotOnlineAverageRate = 0;
        if ($anonNonOnlineDurationInDay > 0) {
            $nonMemberNotOnlineAverageRate = ceil($subTotalForNonMemberNotOnlinePay / $anonNonOnlineDurationInDay);
            Log::info("Average Rate for anon Member not paying online is " . $nonMemberNotOnlineAverageRate);
            $nonMemberNotOnlineAverageRate = $this->calculateADR($defaultPricing, $anonNonOnlineDurationInDay, $nonMemberNotOnlineAverageRate);
            if ($nonMemberNotOnlineAverageRate < toCents($defaultPricing->minRate)) {
                Log::info("Average Rate for anon Member not  paying online  after ADR is less than minRate in DB hence setting it to minRate from DB " . $averageRate);
                $nonMemberNotOnlineAverageRate = toCents($defaultPricing->minRate);
            }
        }
        Log::info("Average Rate for anon member not paying online after Paul's curve is " . $nonMemberAverageRate);
        return $nonMemberNotOnlineAverageRate;
    }

    public function getMemberOnlinePayPricing($jwtService, $reservationPricingDTO, $averageRate, $defaultPricing, $memberOnlinePayDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType)
    {
        Log::info("Average Rate for Member online after Paul's curve  pay is  " . $averageRate);
        $reservationPricingDTO->setMemberOnlinePayAverageRate($averageRate, toCents($defaultPricing->stateTaxPercent), $memberOnlinePayDurationInDay);
        $reservationPricingDTO->memberOnlinePayPricing->setDiscountedDays($couponCode, $originalDurationInDay, $discountedDays, $reservationPricingDTO->memberOnlinePayPricing);
        $reservationPricingDTO->memberOnlinePayPricing->markUpPercent = 0;
        $reservationPricingDTO->memberOnlinePayPricing->walletDays = $walletDiscountDays;
        $couponAmount = $this->applyCouponOnAmount($lotType, true, true, $couponCode, $reservationPricingDTO->memberOnlinePayPricing->subTotal);
        $reservationPricingDTO->memberOnlinePayPricing->setCouponAmount($couponCode, $couponAmount, $memberOnlinePayDurationInDay, $defaultPricing);
        $jwt = $jwtService->generateJWT($lotType, $reservationPricingDTO->memberOnlinePayPricing->total, $couponCode, $walletDiscountDays, $originalDurationInDay);
        $reservationPricingDTO->memberOnlinePayPricing->signature = $jwt;
    }

    public function getMemberOnlineReferencePayPricing($jwtService, $reservationPricingDTO, $referenceOnlinePricingSubTotal, $defaultPricing, $memberOnlinePayDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType)
    {
        $referenceAverageRate = 0;
        if ($memberOnlinePayDurationInDay > 0) {
            $referenceAverageRate = ceil($referenceOnlinePricingSubTotal / $memberOnlinePayDurationInDay);
            Log::info("Reference Average Rate is " . $referenceAverageRate);
        }

        $reservationPricingDTO->setMemberOnlinePayReferenceAvarageRate($referenceAverageRate, toCents($defaultPricing->stateTaxPercent), $memberOnlinePayDurationInDay);
        $reservationPricingDTO->memberOnlineReferencePricing->setDiscountedDays($couponCode, $originalDurationInDay, $discountedDays, $reservationPricingDTO->memberOnlineReferencePricing);
        $reservationPricingDTO->memberOnlineReferencePricing->markUpPercent = 0;
        $reservationPricingDTO->memberOnlineReferencePricing->walletDays = $walletDiscountDays;
        $couponAmount = $this->applyCouponOnAmount($lotType, true, true, $couponCode, $reservationPricingDTO->memberOnlineReferencePricing->subTotal);
        $reservationPricingDTO->memberOnlineReferencePricing->setCouponAmount($couponCode, $couponAmount, $memberOnlinePayDurationInDay, $defaultPricing);
        $jwt = $jwtService->generateJWT($lotType, $reservationPricingDTO->memberOnlineReferencePricing->total, $couponCode, $walletDiscountDays, $originalDurationInDay);
        $reservationPricingDTO->memberOnlineReferencePricing->signature = $jwt;
    }

    public function getMemberNotOnlineReferencePayPricing($jwtService, $reservationPricingDTO, $memberNotOnlineAverageRate, $defaultPricing, $memberNotOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType)
    {
        Log::info("Average Rate for Member Not paying online after Paul's curve is " . $memberNotOnlineAverageRate);
        $reservationPricingDTO->setMemberNotOnlinePayAverageRate($memberNotOnlineAverageRate, toCents($defaultPricing->stateTaxPercent), $memberNotOnlineDurationInDay);
        $reservationPricingDTO->memberNotOnlinePayPricing->setDiscountedDays($couponCode, $originalDurationInDay, $discountedDays, $reservationPricingDTO->memberNotOnlinePayPricing);
        $reservationPricingDTO->memberNotOnlinePayPricing->markUpPercent = $defaultPricing->nonOnlinePayMarkUpPercent * 100;
        $reservationPricingDTO->memberNotOnlinePayPricing->walletDays = $walletDiscountDays;
        $couponAmount = $this->applyCouponOnAmount($lotType, true, false, $couponCode, $reservationPricingDTO->memberNotOnlinePayPricing->subTotal);
        $reservationPricingDTO->memberNotOnlinePayPricing->setCouponAmount($couponCode, $couponAmount, $memberNotOnlineDurationInDay, $defaultPricing);
        $jwt = $jwtService->generateJWT($lotType, $reservationPricingDTO->memberNotOnlinePayPricing->total, $couponCode, $walletDiscountDays, $originalDurationInDay);
        $reservationPricingDTO->memberNotOnlinePayPricing->signature = $jwt;
    }

    public function getMemberNonOnlineReferencePricing($jwtService, $reservationPricingDTO, $referenceOnlinePricingSubTotal, $subTotalForMemberNotOnlinePay, $defaultPricing, $memberOnlinePayDurationInDay, $memberNotOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $walletDiscountDays, $lotType)
    {
        $referenceNonOnlinePricingSubTotal = ceil($referenceOnlinePricingSubTotal + ($subTotalForMemberNotOnlinePay * $defaultPricing->nonOnlinePayMarkUpPercent));
        $referenceAverageRate = 0;
        if ($memberOnlinePayDurationInDay > 0) {
            $referenceAverageRate = ceil($referenceNonOnlinePricingSubTotal / $memberOnlinePayDurationInDay);
            Log::info("Reference Average Rate is " . $referenceAverageRate);
        }
        $reservationPricingDTO->setMemberNotOnlinePayReferenceAvarageRate($referenceAverageRate, toCents($defaultPricing->stateTaxPercent), $memberNotOnlineDurationInDay);
        $reservationPricingDTO->memberNonOnlineReferencePricing->setDiscountedDays($couponCode, $originalDurationInDay, $discountedDays, $reservationPricingDTO->memberNonOnlineReferencePricing);
        $reservationPricingDTO->memberNonOnlineReferencePricing->markUpPercent = $defaultPricing->nonOnlinePayMarkUpPercent * 100;
        $reservationPricingDTO->memberNonOnlineReferencePricing->walletDays = $walletDiscountDays;
        $couponAmount = $this->applyCouponOnAmount($lotType, true, false, $couponCode, $reservationPricingDTO->memberNonOnlineReferencePricing->subTotal);
        $reservationPricingDTO->memberNonOnlineReferencePricing->setCouponAmount($couponCode, $couponAmount, $memberNotOnlineDurationInDay, $defaultPricing);
        $jwt = $jwtService->generateJWT($lotType, $reservationPricingDTO->memberNonOnlineReferencePricing->total, $couponCode, $walletDiscountDays, $originalDurationInDay);
        $reservationPricingDTO->memberNonOnlineReferencePricing->signature = $jwt;
    }

    public function getAnonOnlinePayPricing($jwtService, $reservationPricingDTO, $nonMemberAverageRate, $defaultPricing, $anonOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $lotType)
    {
        $reservationPricingDTO->setNonMemberOnlinePayAverageRate($nonMemberAverageRate, toCents($defaultPricing->stateTaxPercent), $anonOnlineDurationInDay);
        $reservationPricingDTO->anonOnlinePayPricing->setDiscountedDays($couponCode, $originalDurationInDay, $discountedDays, $reservationPricingDTO->anonOnlinePayPricing);
        $reservationPricingDTO->anonOnlinePayPricing->markUpPercent = $defaultPricing->nonMemberMarkUpPercent * 100;
        $couponAmount = $this->applyCouponOnAmount($lotType, false, true, $couponCode, $reservationPricingDTO->anonOnlinePayPricing->subTotal);
        $reservationPricingDTO->anonOnlinePayPricing->setCouponAmount($couponCode, $couponAmount, $anonOnlineDurationInDay, $defaultPricing);
        $jwt = $jwtService->generateJWT($lotType, $reservationPricingDTO->anonOnlinePayPricing->total, $couponCode, $discountedDays, $originalDurationInDay);
        $reservationPricingDTO->anonOnlinePayPricing->signature = $jwt;
    }

    public function getAnonNotOnlinePayPricing($jwtService, $reservationPricingDTO, $nonMemberNotOnlineAverageRate, $defaultPricing, $anonNonOnlineDurationInDay, $couponCode, $originalDurationInDay, $discountedDays, $lotType)
    {
        $reservationPricingDTO->setNonMemberNonOnlinePayAverageRate($nonMemberNotOnlineAverageRate, toCents($defaultPricing->stateTaxPercent), $anonNonOnlineDurationInDay);
        $reservationPricingDTO->anonNonOnlinePayPricing->setDiscountedDays($couponCode, $originalDurationInDay, $discountedDays, $reservationPricingDTO->anonNonOnlinePayPricing);
        $reservationPricingDTO->anonNonOnlinePayPricing->markUpPercent = $defaultPricing->nonMemberMarkUpPercent * 100;
        $reservationPricingDTO->anonNonOnlinePayPricing->markUpPercent += $defaultPricing->nonOnlinePayMarkUpPercent * 100;
        $couponAmount = $this->applyCouponOnAmount($lotType, false, false, $couponCode, $reservationPricingDTO->anonNonOnlinePayPricing->subTotal);
        $reservationPricingDTO->anonNonOnlinePayPricing->setCouponAmount($couponCode, $couponAmount, $anonNonOnlineDurationInDay, $defaultPricing);
        $jwt = $jwtService->generateJWT($lotType, $reservationPricingDTO->anonNonOnlinePayPricing->total, $couponCode, $discountedDays, $originalDurationInDay);
        $reservationPricingDTO->anonNonOnlinePayPricing->signature = $jwt;
    }
}
