<?php

namespace App\Services\Coupon;

use App\Models\Backend\Admin\Coupon;
use Illuminate\Support\Facades\Log;

class CouponService
{
    public function findByCode($couponCode)
    {
        return Coupon::with('promotion')
            ->where('code', $couponCode)
            ->first();
    }

    public function isCouponValidForReservation($lotType, $isMember, $isPayingOnline, $couponCode, $couponDB)
    {
        if ($couponDB != null) {
            $promotion = $couponDB->promotion;

            if ($lotType != $couponDB->promotion->lotType) {
                Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " and promotion id " . $promotion->id . " does not match LotType, Promotion lotType is " . $promotion->lotType . " while user lotType is " . $lotType . "  ,so cannot apply coupon anymore ");
                return false;
            }

            $today = date("Y-m-d");
            $fromDate = date("Y-m-d", strtotime($promotion->fromDate));
            $toDate = date("Y-m-d", strtotime($promotion->toDate));

            // After the startDate and Before the endDate
            if ($today < $fromDate) {
                Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " and promotion id " . $promotion->id . " has not yet started, Promotion from date " . $promotion->fromDate . " But todays date is " . $today . "  ,so cannot apply coupon anymore ");
                return false;
            }

            if ($today > $toDate) {
                Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " and promotion id " . $promotion->id . " has ended, Promotion to date " . $promotion->toDate . " But todays date is " . $today . "  ,so cannot apply coupon anymore ");
                return false;
            }

            if ($isMember && $promotion->couponType == "NON_MEMBER") {
                Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " does not match with isMember flag, Coupon DB coupon type = " . $promotion->couponType . " while user request isMember value " . $isMember . " , so cannot apply coupon anymore");
                return false;
            }

            if (!$isMember && $promotion->couponType == "MEMBER") {
                Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " does not match with isMember flag, Coupon DB coupon type = " . $promotion->couponType . " while user request isMember value " . $isMember . " , so cannot apply coupon anymore");
                return false;
            }

            if ($couponDB->timesRedeemed >= $couponDB->maxRedemptions) {
                Log::info("Number of redeem " . $couponDB->timesRedeemed . " reached max allow " . $couponDB->maxRedemptions . ", so cannot apply coupon anymore");
                return false;
            }

            return true;
        } else {
            Log::info("Coupon code " . $couponCode . " not found in DB");
            return false;
        }
    }

    public function isCouponValidForRegistration($couponDB, $couponCode)
    {
        if ($couponDB == null) {
            Log::info("Not a valid coupon " . $couponCode);
            return false;
        }
        $promotion = $couponDB->promotion;

        if ($promotion == null) {
            Log::info("Couldnt find a promotion for coupon code " . $couponCode);
            return false;
        }

        if ($promotion->days == null || $promotion->days <= 0) {
            Log::info("Promotion " . $promotion->id . ", days are not valid");
            return false;
        }

        if ($promotion->couponType != "SIGNUP") {
            Log::info("coupon is not a signup coupon " . $couponCode);
        }

        $today = date("Y-m-d");
        $fromDate = date("Y-m-d", strtotime($promotion->fromDate));
        $toDate = date("Y-m-d", strtotime($promotion->toDate));

        // After the startDate and Before the endDate
        if ($today < $fromDate) {
            Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " and promotion id " . $promotion->id . " has not yet started, Promotion from date " . $promotion->fromDate . " But todays date is " . $today . "  ,so cannot apply coupon anymore ");
            return false;
        }

        if ($today > $toDate) {
            Log::info("Coupon code " . $couponDB->code . " with id " . $couponDB->id . " and promotion id " . $promotion->id . " has ended, Promotion to date " . $promotion->toDate . " But todays date is " . $today . "  ,so cannot apply coupon anymore ");
            return false;
        }

        if ($couponDB->timesRedeemed >= $couponDB->maxRedemptions) {
            Log::info("Number of redeem " . $couponDB->timesRedeemed . " reached max allow " . $couponDB->maxRedemptions . ", so cannot apply coupon anymore");
            return false;
        }

        return true;
    }

    public function incrementCouponRedeemCount($couponDB)
    {
        $couponDB->timesRedeemed = $couponDB->timesRedeemed + 1;
        $couponDB->save();
    }

}
