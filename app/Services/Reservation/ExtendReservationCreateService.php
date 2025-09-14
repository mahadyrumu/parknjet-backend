<?php

namespace App\Services\Reservation;

use App\DTO\Pricing\AnonPricingDTO;
use App\DTO\Pricing\MemberPricingDTO;
use App\Exceptions\PNJException;
use App\Services\Coupon\CouponService;
use App\Services\Driver\AnonDriverService;
use App\Services\Driver\MemDriverService;
use App\Services\Driver\MemRsvnDriverService;
use App\Services\Mail\EmailSenderService;
use App\Services\PaymentGateway\StripeService;
use App\Services\Pricing\AnonPaymentService;
use App\Services\Pricing\AnonPricingService;
use App\Services\Pricing\MemberPaymentService;
use App\Services\Pricing\MemberPricingService;
use App\Services\Pricing\PricingService;
use App\Services\Sync\LotMemberReservationSyncService;
use App\Services\User\UserService;
use App\Services\Vehicle\AnonVehicleService;
use App\Services\Vehicle\MemRsvnVehicleService;
use App\Services\Vehicle\MemVehicleService;
use App\Services\Wallet\PrePaidWalletService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payment;

class ExtendReservationCreateService
{
    // create Paid Reservation StoredCC
    public function createPaidReservation($rsvn, $currentLoggedInUser)
    {
        $memberReservationDB = $this->createReservation($rsvn, Payment::PaymentType['NOT_ONLINE']);
        $memberPricing = null;
        foreach ($memberReservationDB->pricing as $key => $eachMemberPricing) {
            if ($eachMemberPricing->paymentType == Payment::PaymentType['ONLINE']) {
                $memberPricing = $eachMemberPricing;
            }
        }
        // send Email notification And Sync To Lot application
        $this->sendEmailAndSyncToLot($memberReservationDB, $memberPricing, $currentLoggedInUser);
        return $memberReservationDB;
    }

    public function sendEmailAndSyncToLot($memberReservationDB, $memberPricing, $currentLoggedInUser)
    {
        $emailSenderService = new EmailSenderService;
        $emailSenderService->sendReservationConfirmationEmail($memberReservationDB, $memberPricing, $currentLoggedInUser);
        $lotMemReservationSyncService = new LotMemberReservationSyncService;
        $lotMemReservationSyncService->syncReservationToLot($memberReservationDB, $currentLoggedInUser);
    }

    public function createReservation($rsvn, $calculatePaymentType)
    {
        $reservation = DB::connection('backend_mysql')->transaction(function () use ($rsvn, $calculatePaymentType) {
            Log::info("Creating reservation, Client Request");
            Log::info((array) $rsvn);
            // Checking user request self parking for lot1
            $this->doNotAllowSELFParkingForLot1($rsvn->lotType, $rsvn->parkingPreference);
            if (auth()->user()) {
                // for auth user
                $currentLoggedInUser = auth()->id();
                if ($rsvn->driver) {
                    $memDriverService = new MemDriverService;
                    if ($rsvn->driver->driverId) {
                        // Getting existing driver
                        $reservationDriver = $memDriverService->getDrivers($currentLoggedInUser)
                            ->where('id', $rsvn->driver->driverId)
                            ->first();
                    } else {
                        // Getting existing driver
                        $reservationDriver = $memDriverService->createDriver($currentLoggedInUser, $rsvn->driver->full_name, $rsvn->driver->email, $rsvn->driver->phone);
                    }
                }
                if ($rsvn->vehicle) {
                    $memVehicleService = new MemVehicleService;
                    if ($rsvn->vehicle->vehicleId) {
                        // Getting existing Vehicle
                        $reservationVehicle = $memVehicleService->getVehicles($currentLoggedInUser)
                            ->where('id', $rsvn->vehicle->vehicleId)
                            ->first();
                    } else {
                        // Getting existing Vehicle
                        $reservationVehicle = $memVehicleService->createVehicle($currentLoggedInUser, $rsvn->vehicle->makeModel, $rsvn->vehicle->plate, $rsvn->vehicle->vehicleLength);
                    }
                }
            } else {
                // for Guest
                if ($rsvn->driver) {
                    $anonDriverService = new AnonDriverService;
                    $reservationDriver = $anonDriverService->createDriver($rsvn->driver);
                }
                if ($rsvn->vehicle) {
                    $anonVehicleService = new AnonVehicleService;
                    $reservationVehicle = $anonVehicleService->createVehicle($rsvn->vehicle);
                }
            }
            try {
                Log::info("Driver with id " . $reservationDriver->id . " found in database");
            } catch (\Throwable $th) {
                throw new PNJException("Driver with id " . $rsvn->driver->driverId . " is not found");
            }
            try {
                Log::info("Vehicle with id " . $reservationVehicle->id . " found in database");
            } catch (\Throwable $th) {
                throw new PNJException("Vehicle with id " . $rsvn->vehicle->vehicleId . " is not found");
            }

            $reservationPricingDTO = null;
            $reservation = null;
            $couponDB = null;
            $extensionFee = 2;
            $pricingService = new PricingService;
            $couponService = new CouponService;

            if ($rsvn->couponCode) {
                // $reservation = $rsvn;
                // Re-Price calculate with coupon
                $reservationPricingDTO = $pricingService->getPricing($rsvn->lotType, $rsvn->dropOffTime, $rsvn->pickUpTime, $rsvn->parkingPreference, $reservationVehicle->vehicleLength, $rsvn->couponCode, $rsvn->pricing['walletDays'], $extensionFee);
                // Find coupon on DB
                $couponDB = $couponService->findByCode($rsvn->couponCode);
            } else {
                // Re-Price calculate without coupon
                $reservationPricingDTO = $pricingService->getPricing($rsvn->lotType, $rsvn->dropOffTime, $rsvn->pickUpTime, $rsvn->parkingPreference, $reservationVehicle->vehicleLength, null, $rsvn->pricing['walletDays'], $extensionFee);
            }
            $paymentType = "Non Paid";

            if (auth()->user()) {

                $memberPricingDTO = new MemberPricingDTO;
                $memberPricingService = new MemberPricingService;
                // Get Price DTO from what user choose as payment type
                $memberPricingFirst = $memberPricingDTO->memberPricing($rsvn, $reservationPricingDTO, $couponDB);
                // Compare user price with actual calculated price
                $this->comparePricing($rsvn->pricing, $memberPricingFirst);
                // Get Price DTO from opposite payment type of what user choose
                // $memberPricingSecond = $memberPricingDTO->memberPricing($rsvn, $reservationPricingDTO, $couponDB, $calculatePaymentType);

                $memRsvnDriverService = new MemRsvnDriverService;
                // Create Driver or restore Driver
                $reservationDriverDB = $memRsvnDriverService->createMemRsvnDriver($reservationDriver);

                $memRsvnVehicleService = new MemRsvnVehicleService;
                // Create Vehicle or restore Vehicle
                $reservationVehicleDB = $memRsvnVehicleService->createMemRsvnVehicle($reservationVehicle);

                $memberReservationService = new MemberReservationService;
                // Create Member Reservation
                $reservation = $memberReservationService->memberReservation($rsvn, $reservationDriverDB, $reservationVehicleDB, $memberPricingFirst, $currentLoggedInUser);
                // Store opposite payment type info
                // $memberReservationService->pricingList = $memberPricingService->createMemPricing($memberPricingSecond, $currentLoggedInUser, $reservation->id);

                $userService = new UserService;
                // Get Price DTO from what user choose as payment type
                $user = $userService->getUser($currentLoggedInUser);

                // Subtract Wallet Days
                $this->subtractWalletDaysIfExist($rsvn->pricing['walletDays'], $reservation, $user);

                $responseDTOPricing = $reservationPricingDTO->memberOnlinePayPricing;
            } else {

                $anonPricingDTO = new AnonPricingDTO;
                $anonPricingService = new AnonPricingService;
                // Get Price DTO from what user choose as payment type
                $anonPricingFirst = $anonPricingDTO->anonPricing($rsvn, $reservationPricingDTO, $couponDB);
                // Compare user price with actual calculated price
                $this->comparePricing($rsvn->pricing, $anonPricingFirst);
                // Get Price DTO from opposite payment type of what user choose
                // $anonPricingSecond = $anonPricingDTO->anonPricing($rsvn, $reservationPricingDTO, $couponDB, $calculatePaymentType);

                $anonReservationService = new AnonReservationService;
                // Create Anon Reservation
                $reservation = $anonReservationService->anonReservation($rsvn, $anonPricingFirst, $reservationDriver, $reservationVehicle);
                // Store opposite payment type info
                // $anonReservationService->pricingList = $anonPricingService->createAnonPricing($anonPricingSecond, $reservation->id);
                $responseDTOPricing = $reservationPricingDTO->anonOnlinePayPricing;
            }

            if ($rsvn->pricing['paymentType'] == Payment::PaymentType['ONLINE']) {
                $paymentType = "Paid";
                $stripeService = new StripeService;
                if (auth()->user()) {
                    $customerId = $user->stripeAccount ? $user->stripeAccount->customerId : null;
                    if ($rsvn->payment->cardId && $customerId) {
                        $stripeCharge = $stripeService->chargeStoredCreditCardThroughStripe($rsvn->lotType, $memberPricingFirst['total'], $customerId, $rsvn->payment['cardId'], $user->user_name, $reservation->id);
                    } elseif ($rsvn->payment->paymentId) {
                        $stripeCharge = $stripeService->checkPaymentMethodThroughStripeForReservation($rsvn->payment->paymentId, $reservation);
                    } else {
                        $stripeCharge = $stripeService->chargeCreditCardThroughStripeForReservation($memberPricingFirst['total'], $rsvn->payment->stripeToken, $reservation);
                    }

                    $memberPaymentService = new MemberPaymentService;
                    if ($rsvn->payment->paymentId) {
                        $memberPayment = $memberPaymentService->createMemberPayment($reservation->pricingList, $stripeCharge, Payment::PaymentStatus['PAID'], $currentLoggedInUser, $stripeCharge['card']['brand']);
                    } else {
                        $memberPayment = $memberPaymentService->createMemberPayment($reservation->pricingList, $stripeCharge, Payment::PaymentStatus['PAID'], $currentLoggedInUser, $stripeCharge['source']['brand']);
                    }

                    $memberPricingService->setPayment($reservation->pricingList, $memberPayment);

                    $responseDTOPricing = $reservationPricingDTO->memberOnlinePayPricing;
                    Log::info($paymentType . " reservation with id " . $reservation->id . " created successfully for user id {} " . $user->id);
                } else {
                    $anonPaymentService = new AnonPaymentService;
                    $anonPayment = $anonPaymentService->makeAnonPayment($reservation->pricingList, $rsvn->payment, $reservation);
                    $anonPricingService->setPayment($reservation->pricingList, $anonPayment);
                    $anonPricingService->setPricing($anonPayment, $reservation->pricingList);

                    $responseDTOPricing = $reservationPricingDTO->anonOnlinePayPricing;
                    Log::info($paymentType . " reservation with id " . $reservation->id . " created successfully");
                }
            }

            if ($rsvn->couponCode) {
                if ($couponDB != null) {
                    if ($responseDTOPricing->couponDiscountAmount > 0) {
                        $couponDB->timesRedeemed = $couponDB->timesRedeemed + 1;
                        $couponDB->save();
                        Log::info("Coupon code " . $reservation->couponCode . " redeem count incremented for coupon with id " . $couponDB->id);
                    } else {
                        Log::info("Coupon code " . $reservation->couponCode . " found in DB with id " . $couponDB->id . " but coupon discount amount is not greater than 0 hence redeem count is not incremented, Now checking for discounted days");
                        if ($responseDTOPricing->couponDiscountedDays > 0) {
                            $couponDB->timesRedeemed = $couponDB->timesRedeemed + 1;
                            $couponDB->save();
                            Log::info("Coupon discounted days " . $responseDTOPricing->couponDiscountedDays . "  Coupon code " . $couponDB->id . " redeem count incremented for coupon with id " . $couponDB->id);
                        } else {
                            Log::info("Coupon code " . $reservation->couponCode . " found in DB with id " . $couponDB->id . " but BOTH coupon discount amount and discount days are 0 hence redeem count is not incremented");
                        }
                    }
                } else {
                    Log::info("Coupon code " . $reservation->couponCode . " redeem count not incremented since it was not found in DB ");
                }
            }
            return $reservation;
        });
        return $reservation;
    }

    public function doNotAllowSELFParkingForLot1($lotType, $parkingPreference)
    {
        if ($lotType == "LOT_1" && $parkingPreference == "SELF") {
            Log::info("SELF parking request made for LOT_1, Throwing PNJBadRequestException since Lot1 does not allow SELF parking");
            throw new PNJException("LOT1 does not allow SELF parking");
        }
    }

    public function comparePricing($pricingFromClient, $calculatedPricing)
    {
        Log::info("Comparing pricing,  Client Total = " . $pricingFromClient['total'] . " and Server total = " . $calculatedPricing['total']);

        if ($pricingFromClient['total'] != $calculatedPricing['total']) {
            throw new PNJException("Price calculated by server based on reservation info does not match with price you send,  Client total = " . $pricingFromClient['total'] . " and server Total = " . $calculatedPricing['total']);
        }
        Log::info("Pricing matches!");
    }

    private function subtractWalletDaysIfExist($walletDays, $memberReservation, $user)
    {
        if ($walletDays != null && $walletDays > 0) {
            $this->subtractWalletDays($walletDays, $memberReservation, $user);
        }
    }

    private function subtractWalletDays($walletDays, $memberReservation, $user)
    {
        // Log::info("memberReservation");
        // Log::info((array) $memberReservation);
        // Log::info((array) $memberReservation->lotType);
        Log::info($user);
        Log::info($user->prepaidWalletLot1);
        Log::info($user->prepaidWalletLot1->days);
        Log::info(isBeforeNow($user->prepaidWalletLot1->expirationDate));
        if (
            $memberReservation->lotType == LotType['LOT_1']
            && $user->prepaidWalletLot1 != null
            && $user->prepaidWalletLot1->days > 0
            && isBeforeNow($user->prepaidWalletLot1->expirationDate) == false
        ) {
            $prePaidWalletService = new PrePaidWalletService;
            if ($user->prepaidWalletLot1->days >= $walletDays) {
                // use only prepaid days to pay the reservation
                $prePaidWalletTxn = $prePaidWalletService->subtractForReservation($walletDays, TriggerType['RESERVATION_PAID'], "", $memberReservation);
            } else {
                // use prepaid days first and then wallet days

                $daysFromPrepaidPackage = $user->prepaidWalletLot1->days;
                $prePaidWalletTxn = $prePaidWalletService->subtractForReservation($daysFromPrepaidPackage, TriggerType['RESERVATION_PAID'], "", $memberReservation);

                $remainingDaysToRedeem = $walletDays - $daysFromPrepaidPackage;

                $walletService = new WalletService;
                $walletService->subtractForReservation($remainingDaysToRedeem, TriggerType['RESERVATION_PAID'], "", $memberReservation);
            }
        } else if (
            $memberReservation->lotType == LotType['LOT_1']
            && $user->prepaidWalletLot2 != null
            && $user->prepaidWalletLot2->days > 0
            && isBeforeNow($user->prepaidWalletLot2->expirationDate) == false
        ) {
            $prePaidWalletService = new PrePaidWalletService;
            if ($user->prepaidWalletLot2->days >= $walletDays) {
                // use only prepaid days to pay the reservation
                $prePaidWalletTxn = $prePaidWalletService->subtractForReservation($walletDays, TriggerType['RESERVATION_PAID'], "", $memberReservation);
            } else {
                // use prepaid days first and then wallet days

                $daysFromPrepaidPackage = $user->prepaidWalletLot2->days;
                $prePaidWalletTxn = $prePaidWalletService->subtractForReservation($daysFromPrepaidPackage, TriggerType['RESERVATION_PAID'], "", $memberReservation);

                $remainingDaysToRedeem = $walletDays - $daysFromPrepaidPackage;

                $walletService = new WalletService;
                $walletService->subtractForReservation($remainingDaysToRedeem, TriggerType['RESERVATION_PAID'], "", $memberReservation);
            }
        } else {
            $walletService = new WalletService;
            $walletService->subtractForReservation($walletDays, TriggerType['RESERVATION_PAID'], "", $memberReservation);
        }
    }
}
