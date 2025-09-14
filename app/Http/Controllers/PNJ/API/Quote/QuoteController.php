<?php

namespace App\Http\Controllers\PNJ\API\Quote;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\CheckCouponRequest;
use App\Http\Requests\Quote\CheckAvailabilityRequest;
use App\Http\Requests\Quote\QuoteRequest;
use App\Http\Requests\Reservation\ExtendReservationRequest;
use App\Services\Coupon\CouponService;
use App\Services\Pricing\PricingService;
use App\Services\Reservation\ReservationService;
use Illuminate\Support\Facades\Log;
use stdClass;

class QuoteController extends Controller
{
    public function getMemQuotes(QuoteRequest $request, PricingService $pricingService)
    {
        try {
            $lot = $request->lot;
            $doDate = $request->doDate;
            $puDate = $request->puDate;
            $pref = $request->pref;
            $vehicleLength = $request->vehicleLength;
            $couponCode = $request->coupon;
            $walletDays = $request->walletDays;

            $getPricing = $pricingService->getPricing($lot, $doDate, $puDate, $pref, $vehicleLength, $couponCode, $walletDays);
            $pricingData = $pricingService->trimPricingData($getPricing);

            return response()->json([
                'success' => true,
                'data' => $pricingData
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getAnonQuotes(QuoteRequest $request, PricingService $pricingService)
    {
        try {
            $lot = $request->lot;
            $doDate = $request->doDate;
            $puDate = $request->puDate;
            $pref = $request->pref;
            $vehicleLength = $request->vehicleLength;
            $couponCode = $request->coupon;
            $walletDays = $request->walletDays;

            $getPricing = $pricingService->getPricing($lot, $doDate, $puDate, $pref, $vehicleLength, $couponCode, $walletDays);
            $pricingData = $pricingService->trimPricingData($getPricing);

            return response()->json([
                'success' => true,
                'data' => $pricingData
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function checkAvailability(CheckAvailabilityRequest $request, PricingService $pricingService)
    {
        try {
            $lot = $request->lot;
            $doDate = $request->doDate;
            $puDate = $request->puDate;
            $vehicleLength = $request->vehicleLength;
            $couponCode = null;
            $walletDays = 0;

            $pricingData = [];

            if ($lot == "ANY" || $lot == "LOT_1") {

                try {
                    $VALET_LOT_1 = $pricingService->trimPricingData($pricingService->getPricing("LOT_1", $doDate, $puDate, "VALET", $vehicleLength, $couponCode, $walletDays));
                } catch (\Exception $exception) {
                    $VALET_LOT_1 = $exception->getMessage();
                }

                $LOT_1 = new stdClass;
                $LOT_1->VALET = $VALET_LOT_1;

                $pricingData += ["LOT_1" => $LOT_1];
            }
            if ($lot == "ANY" || $lot == "LOT_2") {
                try {
                    $VALET_LOT_2 = $pricingService->trimPricingData($pricingService->getPricing("LOT_2", $doDate, $puDate, "VALET", $vehicleLength, $couponCode, $walletDays));
                } catch (\Exception $exception) {
                    $VALET_LOT_2 = $exception->getMessage();
                }
                try {
                    $SELF_LOT_2 = $pricingService->trimPricingData($pricingService->getPricing("LOT_2", $doDate, $puDate, "SELF", $vehicleLength, $couponCode, $walletDays));
                } catch (\Exception $exception) {
                    $SELF_LOT_2 = $exception->getMessage();
                }

                $LOT_2 = new stdClass;
                $LOT_2->VALET = $VALET_LOT_2;
                $LOT_2->SELF = $SELF_LOT_2;

                $pricingData += ["LOT_2" => $LOT_2];
            }

            return response()->json([
                'success' => true,
                'data' => $pricingData
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function checkCoupon(CheckCouponRequest $request, PricingService $pricingService, CouponService $couponService)
    {
        try {
            $couponDB = $couponService->findByCode($request->coupon);

            if (!$couponService->isCouponValidForReservation($request->lotType, $request->member, true, $request->coupon, $couponDB)) {
                return response()->json([
                    'error' => true,
                    'message' => "Invalid Coupon."
                ], ResponseCode["Forbidden"]);
            }
            return response()->json([
                'success' => true
            ], ResponseCode["Success"]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function extendAnonQuotes(ExtendReservationRequest $request, PricingService $pricingService, ReservationService $reservationService)
    {
        try {
            $reservation = $reservationService->getAnonReservation()
                ->where('id', $request->rsvnId)
                ->first();

            if ($reservation == null) {
                Log::error($reservation);
                throw new PNJException("Reservation " . $request->rsvnId . " not found.");
            }

            $lot = $reservation->lotType;
            $doDate = $reservation->pickUpTime;
            $puDate = $request->puDate . " " . $request->puTime;
            $pref = $reservation->parkingPreference;
            $vehicleLength = $reservation->vehicle->vehicleLength;
            $couponCode = $request->couponCode;
            $walletDays = $request->walletDays;

            $extensionFee = 2;

            $getPricing = $pricingService->getPricing($lot, $doDate, $puDate, $pref, $vehicleLength, $couponCode, $walletDays, $extensionFee);
            $pricingData = $pricingService->trimExtendPricingData($getPricing);

            return response()->json([
                'success' => true,
                'data' => $pricingData,
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function extendMemQuotes(ExtendReservationRequest $request, PricingService $pricingService, ReservationService $reservationService)
    {
        try {
            $reservation = $reservationService->getMemReservation()
                ->where('id', $request->rsvnId)
                ->first();

            if ($reservation == null) {
                Log::error($reservation);
                throw new PNJException("Reservation " . $request->rsvnId . " not found.");
            }

            $lot = $reservation->lotType;
            $doDate = $reservation->pickUpTime;
            $puDate = $request->puDate . " " . $request->puTime;
            $pref = $reservation->parkingPreference;
            $vehicleLength = $reservation->vehicle->vehicleLength;
            $couponCode = $request->couponCode;
            $walletDays = $request->walletDays;

            $extensionFee = 2;

            $getPricing = $pricingService->getPricing($lot, $doDate, $puDate, $pref, $vehicleLength, $couponCode, $walletDays, $extensionFee);
            $pricingData = $pricingService->trimExtendPricingData($getPricing);

            return response()->json([
                'success' => true,
                'data' => $pricingData,
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
