<?php

namespace App\Http\Controllers\PNJ\API\Reservation;

use App\DTO\Reservation\ExtendReservationDTO;
use App\DTO\Reservation\ReservationDTO;
use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\CancelReservationRequest;
use App\Http\Requests\Reservation\CreateReservationRequest;
use App\Http\Requests\Reservation\PaymentReservationRequest;
use App\Http\Requests\Reservation\ExtendReservationRequest;
use App\Http\Requests\Reservation\ExtendReservationSubmitRequest;
use App\Jobs\Auth\ReservationDetailsEmailJob;
use App\Mail\ReservationDetailsEmail;
use App\Services\Pricing\PricingService;
use App\Services\Reservation\ReservationCancelService;
use App\Services\PaymentGateway\StripeService;
use App\Services\Reservation\ReservationService;
use App\Services\Reservation\JwtService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function createMemReservation(CreateReservationRequest $request, ReservationService $reservationService, $mem_id)
    {
        try {
            $jwtService = new JwtService;
            $decodeJWTData = $jwtService->decodeJWT($request->signature); // Decode jwt
            $issuedAt = Carbon::createFromTimestamp($decodeJWTData['iat']);

            if ($request->paymentId) {
                $stripeService = new StripeService;
                // Check if the time is expired (1 hour passed)
                if ($issuedAt->floatDiffInHours(Carbon::now()) >= 1) {
                    throw new PNJException("Sorry! please try again. The time is expired.");
                }
                // Get client total which is already paid using apple pay/google pay
                $clientTotal = $stripeService->checkPaymentMethodThroughStripeForPricing($request->paymentId, $request->lot);
                // Check if the amount is mismatched
                if ($clientTotal != $decodeJWTData['reservationData']['total']) {
                    throw new PNJException("Price calculated by server based on reservation info does not match with price you send,  Client total = " . $clientTotal . " and server Total = " . $decodeJWTData['reservationData']['total']);
                }
            }

            $reservationDTO = new ReservationDTO();
            // Format the data well so that it works for all 10 API scenario
            $reservationData = $reservationDTO->ReservationData($request);

            // process reservation request based on the data
            $newReservation = $reservationService->processMemCreateReservation($reservationData);

            session()->forget('reservation_info');

            return response()->json([
                'success'  => true,
                'data'     => $newReservation
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function createAnonReservation(CreateReservationRequest $request, ReservationService $reservationService)
    {
        try {
            $jwtService = new JwtService;
            $decodeJWTData = $jwtService->decodeJWT($request->signature); // Decode jwt
            $issuedAt = Carbon::createFromTimestamp($decodeJWTData['iat']);

            if ($request->paymentId) {
                $stripeService = new StripeService;
                // Check if the time is expired (1 hour passed)
                if ($issuedAt->floatDiffInHours(Carbon::now()) >= 1) {
                    throw new PNJException("Sorry! please try again. The time is expired.");
                }
                // Get client total which is already paid using apple pay/google pay
                $clientTotal = $stripeService->checkPaymentMethodThroughStripeForPricing($request->paymentId, $request->lot);
                // Check if the amount is mismatched
                if ($clientTotal != $decodeJWTData['reservationData']['total']) {
                    throw new PNJException("Price calculated by server based on reservation info does not match with price you send,  Client total = " . $clientTotal . " and server Total = " . $decodeJWTData['reservationData']['total']);
                }
            }

            $reservationDTO = new ReservationDTO();
            // Format the data well so that it works for all 10 API scenario
            $reservationData = $reservationDTO->ReservationData($request);

            // process reservation request based on the data
            $newReservation = $reservationService->processAnonCreateReservation($reservationData);

            session()->forget('reservation_info');

            return response()->json([
                'success'  => true,
                'data'     => $newReservation
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function cancelMemReservation(CancelReservationRequest $request, ReservationService $reservationService, $mem_id, $rsvn_id)
    {
        try {
            $reservationCancel = $reservationService->processMemCancelReservation($request, $rsvn_id);

            return response()->json([
                'success'  => true,
                'data'     => $reservationCancel
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function refundReservation(ReservationService $reservationService, $rsvn_id)
    {
        try {
            $reservationCancel = $reservationService->processRefundReservation($rsvn_id);

            return response()->json([
                'success'  => true,
                'data'     =>  $reservationCancel
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function cancelAnonReservation(CancelReservationRequest $request, ReservationService $reservationService, $rsvn_id)
    {
        try {
            $reservationCancel = $reservationService->processAnonCancelReservation($request, $rsvn_id);

            return response()->json([
                'success'  => true,
                'data'     =>  $reservationCancel
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    function refundMemCancelReservation(CancelReservationRequest $request, ReservationService $reservationService, $mem_id, $rsvn_id)
    {
        try {
            $refundReservationCancel = $reservationService->processMemRefundOnCanceledReservation($request, $rsvn_id);

            return response()->json([
                'success'  => true,
                'data'     =>  $refundReservationCancel
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    function refundAnonCancelReservation(CancelReservationRequest $request, ReservationService $reservationService, $rsvn_id)
    {
        try {
            $refundReservationCancel = $reservationService->processAnonRefundOnCanceledReservation($request, $rsvn_id);

            return response()->json([
                'success'  => true,
                'data'     =>  $refundReservationCancel
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function paymentMemReservation(PaymentReservationRequest $request, ReservationService $reservationService, $mem_id, $rsvnID)
    {
        try {
            $reservation = $reservationService->processMemPaymentReservation($request, $rsvnID);
            $reservation = $reservationService->reservationShow($reservation->id, $reservation->driver->email, $reservation->dropOffTime);

            return response()->json([
                'success'  => true,
                'data'     => $reservation
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function paymentAnonReservation(PaymentReservationRequest $request, ReservationService $reservationService, $rsvnID)
    {
        try {
            $reservation = $reservationService->processAnonPaymentReservation($request, $rsvnID);
            $reservation = $reservationService->reservationShow($reservation->id, $reservation->driver->email, $reservation->dropOffTime);

            return response()->json([
                'success'  => true,
                'data'     => $reservation
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getUserReservations(ReservationService $reservationService, $owner_id)
    {
        try {
            $reservations = $reservationService
                ->getMemAllReservation($owner_id)
                ->orderBy('id', 'desc')
                ->get();

            if ($reservations) {
                foreach ($reservations as $key => $reservation) {
                    $reservation['dropOffTime'] = date('m/d/Y h:i A', strtotime($reservation->dropOffTime));
                    $reservation['pickUpTime'] = date('m/d/Y h:i A', strtotime($reservation->pickUpTime));
                }

                return response()->json([
                    'success'  => true,
                    'data'     => $reservations
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "Cannot find the reservation for this owner " . $owner_id . "."
                ], ResponseCode["Not Found"]);
            }
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function sendReservationDetails(ReservationService $reservationService, $owner_id, $rsvnId)
    {
        try {
            $reservation = $reservationService
                ->getMemReservation()
                ->where('owner_id', $owner_id)
                ->where('id', $rsvnId)
                ->first();

            if ($reservation) {
                $reservation->save = $reservationService->reservationPricingCalculation($reservation);
                $qrcode = base64_encode(QrCode::size(200)
                    ->format('png')
                    ->generate(
                        $reservation->id,
                    ));
                $reservation->durationInDay = $reservationService->getDurationInDay($reservation->dropOffTime, $reservation->pickUpTime);
                $reservation->discountDays = $reservationService->reservationWalletDaysCalculation($reservation);

                ReservationDetailsEmailJob::dispatch(Auth()->user()->user_name, $qrcode, $reservation);
                // Mail::to(Auth()->user()->user_name)->send(new ReservationDetailsEmail($qrcode, $reservation));

                return response()->json([
                    'success'  => true,
                    'message'  => "Reservation details sent successfully."
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "Cannot find the reservation " . $rsvnId . " or you don't have permission to view this reservation."
                ], ResponseCode["Not Found"]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function extendMemReservation(ExtendReservationSubmitRequest $request, ReservationService $reservationService, $owner_id, $rsvnId)
    {
        try {
            $reservation = $reservationService
                ->getMemReservation()
                ->where('owner_id', $owner_id)
                ->where('id', $rsvnId)
                ->first();

            if ($reservation == null) {
                Log::error($reservation);
                throw new PNJException("Reservation " . $rsvnId . " not found.");
            }

            $extendReservationDTO = new ExtendReservationDTO();
            // Format the data well
            $extendReservationData = $extendReservationDTO->ExtendReservationData($reservation, $request);
            // process reservation request based on the data
            $newReservation = $reservationService->processMemCreateExtendReservation($extendReservationData);

            return response()->json([
                'success'  => true,
                'data'     => $newReservation
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function extendAnonReservation(ExtendReservationSubmitRequest $request, ReservationService $reservationService, $rsvnId)
    {
        try {
            $reservation = $reservationService
                ->getAnonReservation()
                ->where('id', $rsvnId)
                ->first();

            if ($reservation == null) {
                Log::error($reservation);
                throw new PNJException("Reservation " . $rsvnId . " not found.");
            }

            $extendReservationDTO = new ExtendReservationDTO();
            // Format the data well
            $extendReservationData = $extendReservationDTO->ExtendReservationData($reservation, $request);
            // process reservation request based on the data
            $newReservation = $reservationService->processAnonCreateExtendReservation($extendReservationData);

            return response()->json([
                'success'  => true,
                'data'     => $newReservation
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
