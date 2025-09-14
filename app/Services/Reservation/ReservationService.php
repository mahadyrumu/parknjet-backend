<?php

namespace App\Services\Reservation;

use App\Models\Backend\AnonReservation;
use App\Models\Backend\MemReservation;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NotFoundException;
use App\Exceptions\PNJException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReservationService
{

    public function getMemReservation()
    {
        return MemReservation::with([
            'vehicle',
            'driver',
            'pricing.payment',
            'pricing.lot_payment'
        ]);
    }

    public function getAnonReservation()
    {
        return AnonReservation::with([
            'vehicle',
            'driver',
            'pricing.payment',
            'pricing.lot_payment'
        ]);
    }

    public function getMemAllReservation($userId)
    {
        $reservations = MemReservation::with([
            'vehicle',
            'driver',
            'pricing.payment',
            'pricing.lot_payment'
        ])
            ->Where('owner_id', $userId);

        foreach ($reservations as $key => $reservation) {
            $this->setPaymentInfo($reservation);
        }

        return $reservations;
    }

    public function getAnonAllReservation($rsvnId)
    {
        $reservations = AnonReservation::with([
            'vehicle',
            'driver',
            'pricing.payment',
            'pricing.lot_payment'
        ])
            ->Where('id', $rsvnId)
            ->get();

        foreach ($reservations as $key => $reservation) {
            $this->setPaymentInfo($reservation);
        }

        return $reservations;
    }

    public function setPaymentInfo($reservation) {
        if (count($reservation->pricing) > 0) {
            foreach ($reservation->pricing as $key => $pricing) {
                if ($pricing->payment) {
                    $reservation->isPaid = true;
                    $reservation->paymentType = $pricing->paymentType;
                    $reservation->paymentTotal = $pricing->total;
                    $reservation->points = $pricing->points;
                }
                if ($pricing->lot_payment) {
                    $reservation->isPaid = true;
                    $reservation->paymentType = $pricing->paymentType;
                    $reservation->paymentTotal = $pricing->lot_payment->amount;
                    $reservation->points = $pricing->points;
                }
            }
        }
    }

    public function getDurationInDay($dropOffTime, $pickUpTime)
    {
        // $dropOffTime = new DateTime($dropOffTime);
        // $pickUpTime = new DateTime($pickUpTime);

        // $halfDay = 0.5;

        // $days = $pickUpTime->diff($dropOffTime)->days + 1;
        // $pnjDays = (float) $days;

        // if ($dropOffTime->format('a') === 'pm') {
        //     $pnjDays -= $halfDay;
        // }
        // if ($pickUpTime->format('a') === 'am') {
        //     $pnjDays -= $halfDay;
        // }
        // Log::info("No of days = " . $pnjDays);
        // return $pnjDays;


        $HALF_DAY = 0.5;

        // Calculate the number of full days between the dates
        $dropOffDate = date_create(date('Y-m-d', strtotime($dropOffTime)));
        $pickUpDate = date_create(date('Y-m-d', strtotime($pickUpTime)));
        $daysDiff = date_diff($dropOffDate, $pickUpDate)->days + 1;

        $pnjDays = (float) $daysDiff;

        // Adjust for half-day conditions
        if (date('A', strtotime($dropOffTime)) == 'PM') {
            $pnjDays -= $HALF_DAY;
        }
        if (date('A', strtotime($pickUpTime)) == 'AM') {
            $pnjDays -= $HALF_DAY;
        }

        Log::info("No of days = " . $pnjDays);
        return $pnjDays;
    }

    public function getMemberReservationByIdDriverEmailDropOffDate($id, $driverEmail, $dropOffDate)
    {
        $reservation = $this->getMemReservation()
            ->where('id', $id)
            ->first();
        if ($reservation == null) {
            Log::info("Exception : Member Reservation with id " . $id . " not found");
            return null;
        }

        Log::info("Member Reservation with id " . $id . " found in DB with driver email = " . $reservation->driver->email . " and local date = " . $reservation->dropOffTime);

        if (strtolower($reservation->driver->email) != strtolower($driverEmail)) {
            Log::info("Exception : Member Reservation with id = " . $id . " and driver email = " . $driverEmail . " not found!");
            return null;
            // throw new PNJException(" Member Reservation with id = " . $id . " and driver email = " . $driverEmail . " not found!");
        }
        Log::info("Member Reservation with id " . $id . " and driver email " . $driverEmail . " found in DB");

        if (date("Y-m-d", strtotime($reservation->dropOffTime)) != date("Y-m-d", strtotime($dropOffDate))) {
            Log::info("Exception : Member Reservation with id = " . $id . " and driver email = " . $driverEmail . " and dropOffDate = " . $dropOffDate . " not found!");
            return null;
            // throw new PNJException(" Member Reservation with id = " . $id . " and driver email = " . $driverEmail . " and dropOffDate = " . $dropOffDate . " not found!");
        }
        return $reservation;
    }

    public function getAnonReservationByIdDriverEmailDropOffDate($id, $driverEmail, $dropOffDate)
    {
        $reservation = $this->getAnonReservation()
            ->where('id', $id)
            ->first();
        if ($reservation == null) {
            Log::info("Exception : Anon Reservation with id " . $id . " not found");
            return null;
        }

        Log::info("Anon Reservation with id " . $id . " found in DB with driver email = " . $reservation->driver->email . " and local date = " . $reservation->dropOffTime);

        if (strtolower($reservation->driver->email) != strtolower($driverEmail)) {
            Log::info("Exception : Anon Reservation with id = " . $id . " and driver email = " . $driverEmail . " not found!");
            return null;
            // throw new PNJException(" Anon Reservation with id = " . $id . " and driver email = " . $driverEmail . " not found!");
        }
        Log::info("Anon Reservation with id " . $id . " and driver email " . $driverEmail . " found in DB");

        if (date("Y-m-d", strtotime($reservation->dropOffTime)) != date("Y-m-d", strtotime($dropOffDate))) {
            Log::info("Exception : Anon Reservation with id = " . $id . " and driver email = " . $driverEmail . " and dropOffDate = " . $dropOffDate . " not found!");
            return null;
            // throw new PNJException(" Anon Reservation with id = " . $id . " and driver email = " . $driverEmail . " and dropOffDate = " . $dropOffDate . " not found!");
        }
        return $reservation;
    }

    public function getReservationByIdAndDriverEmailAndDropOffDate($id, $driverEmail, $dropOffDate)
    {
        $reservation = $this->getMemberReservationByIdDriverEmailDropOffDate($id, $driverEmail, $dropOffDate);

        if ($reservation == null) {
            Log::info("No member reservation found, Now searching for anon reservation");
            $reservation = $this->getAnonReservationByIdDriverEmailDropOffDate($id, $driverEmail, $dropOffDate);
        }
        if ($reservation == null) {
            Log::info("Exception : Anon or Member Reservation with id " . $id . " not found");
            return null;
        }

        return $reservation;
    }

    public function processMemCreateReservation($reservationData)
    {
        Log::info("Creating Member Reservation");
        $reservationCreateService = new ReservationCreateService();
        $currentLoggedInUser = auth()->user();
        if (!$currentLoggedInUser) {
            throw new PNJException("Unauthorized User!");
        }
        if ($reservationData->payment->stripeToken || $reservationData->payment->paymentId) {
            Log::info("Paid Member Reservation");
            return $reservationCreateService->createPaidReservation($reservationData, $currentLoggedInUser);
        }
        // User Pay at lot
        Log::info("Unpaid Member Reservation");
        return $reservationCreateService->createNonPaidReservation($reservationData, $currentLoggedInUser);
    }

    public function processAnonCreateReservation($reservationData)
    {
        Log::info("Creating Anon Reservation");
        $reservationCreateService = new ReservationCreateService();
        if ($reservationData->payment->stripeToken || $reservationData->payment->paymentId) {
            // Guest Pay Online Non saved CC
            Log::info("Paid Anon Reservation");
            return $reservationCreateService->createPaidReservation($reservationData, null);
        }
        // Guest Pay at lot
        Log::info("Unpaid Anon Reservation");
        return $reservationCreateService->createNonPaidReservation($reservationData, null);
    }

    public function processMemCancelReservation($request, $rsvn_id)
    {
        $reservationCancelService = new ReservationCancelService();
        $user = auth()->user();
        if ($user) {
            $reservation = $this->getMemberReservationByIdDriverEmailDropOffDate($rsvn_id, $request->driverEmail, $request->dropOffDate);
        }
        if ($reservation != null) {
            Log::info("Member Reservation found with PK " . $reservation->id . " Cancelling it now.");
            return $reservationCancelService->cancelReservationAndSendEmailAndSyncToLot($reservation, $user);
        } else {
            Log::info("Exception : Member Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " not found");
            throw new PNJException("Member Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " not found !");
        }
    }

    public function processMemRefundOnCanceledReservation($request, $rsvn_id)
    {
        $reservationCancelService = new ReservationCancelService();
        $user = auth()->user();
        if ($user) {
            $reservation = $this->getMemberReservationByIdDriverEmailDropOffDate($rsvn_id, $request->driverEmail, $request->dropOffDate);
        }
        if ($reservation != null) {
            Log::info("Member Reservation found with PK " . $reservation->id . " Refunding it now.");
            return $reservationCancelService->refundCancelReservation($reservation);
        } else {
            Log::info("Exception : Member Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " not found");
            throw new PNJException("Member Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " not found !");
        }
    }

    public function processRefundReservation($rsvn_id)
    {
        $reservation = $this->getMemReservation()
            ->where('id', $rsvn_id)
            ->first();
        if ($reservation == null) {
            $reservation = $this->getAnonReservation()
                ->where('id', $rsvn_id)
                ->first();
            Log::info("Anon Reservation found with PK " . $reservation->id . " Cancelling it now.");
        } else {
            Log::info("Member Reservation found with PK " . $reservation->id . " Cancelling it now.");
        }
        if ($reservation == null) {
            throw new PNJException("Cannot find reservation $rsvn_id ");
        } else {
            $reservationCancelService = new ReservationCancelService();
            return $reservationCancelService->refundReservation($reservation);
        }
    }

    public function processAnonCancelReservation($request, $rsvn_id)
    {
        $reservationCancelService = new ReservationCancelService();
        $reservation = $this->getAnonReservationByIdDriverEmailDropOffDate($rsvn_id, $request->driverEmail, $request->dropOffDate);
        if ($reservation != null) {
            Log::info("Anon Reservation found with PK " . $reservation->id . " Cancelling it now.");
            return $reservationCancelService->cancelReservationAndSendEmailAndSyncToLot($reservation, null);
        } else {
            Log::info("Exception : Anon Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " is not found");
            $reservation = $this->getMemReservation()
                ->where('id', $rsvn_id)
                ->first();
            if ($reservation != null) {
                Log::info("Exception : Member Reservation by id " . $rsvn_id . " is found");
                throw new PNJException("This is a member reservation and you have to login to cancel this.");
            } else {
                Log::info("Exception : Reservation by id " . $rsvn_id . " is not found");
                throw new PNJException("The reservation does not exist.");
            }
        }
    }

    public function processAnonRefundOnCanceledReservation($request, $rsvn_id)
    {
        $reservationCancelService = new ReservationCancelService();
        $reservation = $this->getAnonReservationByIdDriverEmailDropOffDate($rsvn_id, $request->driverEmail, $request->dropOffDate);
        if ($reservation != null) {
            Log::info("Anon Reservation found with PK " . $reservation->id . " Refunding it now.");
            return $reservationCancelService->refundCancelReservation($reservation);
        } else {
            Log::info("Exception : Anon Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " not found");
            throw new PNJException("Anon Reservation by id " . $rsvn_id . ", driver email = " . $request->driverEmail . " and dropOffDate = " . $request->dropOffDate . " not found !");
        }
    }

    public function processMemPaymentReservation($request, $rsvnID)
    {
        $reservationPaymentService = new ReservationPaymentService();
        $currentLoggedInUser = auth()->user()->id;
        if (!$currentLoggedInUser) {
            throw new PNJException("Unauthorized User!");
        }
        if ($currentLoggedInUser) {
            return $reservationPaymentService->createMemPaidReservation($rsvnID, $request, $currentLoggedInUser);
        }
    }

    public function processAnonPaymentReservation($request, $rsvnID)
    {
        $reservationPaymentService = new ReservationPaymentService();
        return $reservationPaymentService->createAnonPaidReservation($rsvnID, $request);
    }

    public function processMemCreateExtendReservation($extendReservation)
    {
        $extendReservationCreateService = new ExtendReservationCreateService();
        $currentLoggedInUser = auth()->user();
        if (!$currentLoggedInUser) {
            throw new PNJException("Unauthorized User!");
        }
        if ($extendReservation->payment->stripeToken || $extendReservation->payment->paymentId) {
            return $extendReservationCreateService->createPaidReservation($extendReservation, $currentLoggedInUser);
        } else {
            throw new PNJException("Payment Info Not Found.");
        }
    }

    public function processAnonCreateExtendReservation($extendReservation)
    {
        $extendReservationCreateService = new ExtendReservationCreateService();
        if ($extendReservation->payment->stripeToken || $extendReservation->payment->paymentId) {
            return $extendReservationCreateService->createPaidReservation($extendReservation, null);
        } else {
            throw new PNJException("Payment Info Not Found.");
        }
    }

    public function reservationPricingCalculation($reservation)
    {
        $this->setPaymentInfo($reservation);

        $nonOnlinePayment = 0;
        $onlinePayment = 0;

        foreach ($reservation->pricing as $key => $pricing) {
            if ($pricing->paymentType == 'ONLINE') {
                $onlinePayment = $pricing->total;
            }
            if ($pricing->paymentType == 'NOT_ONLINE') {
                $nonOnlinePayment = $pricing->total;
            }
        }
        $save = $nonOnlinePayment - $onlinePayment;
        return round($save, 2);
    }

    public function reservationWalletDaysCalculation($reservation)
    {
        $discountDays = 0;
        foreach ($reservation->wallet_transaction as $key => $walletTransaction) {
            $difference = $walletTransaction->oldBalance - $walletTransaction->newBalance;
            $discountDays += $difference;
        }

        foreach ($reservation->pre_paid_wallet_txns as $key => $prePaidWalletTxn) {
            $difference = $prePaidWalletTxn->oldBalance - $prePaidWalletTxn->newBalance;
            $discountDays += $difference;
        }
        return $discountDays;
    }

    public function reservationShow($reservationId, $driverEmail, $dropOffDate)
    {
        $reservation = $this->getReservationByIdAndDriverEmailAndDropOffDate($reservationId, $driverEmail, $dropOffDate);

        if ($reservation != null) {
            $reservation->save = $this->reservationPricingCalculation($reservation);
            $qrcode = base64_encode(QrCode::size(200)
                ->format('png')
                ->generate(
                    $reservationId,
                ));
            $reservation->durationInDay = $this->getDurationInDay($reservation->dropOffTime, $reservation->pickUpTime);

            if ($reservation->owner_id) {
                $reservation->discountDays = $this->reservationWalletDaysCalculation($reservation);
            }
            return [
                'qrcode' => $qrcode,
                'reservation' => $reservation,
            ];
        } else {
            throw new NotFoundException("Oops! We can't find this reservation. If you are already a member please login and try again!");
        }
    }

    public function updateStatus($reservation, $newStatus, $actualTime, $claimId)
    {
        if ($newStatus == ReservationStatus['CHECKED_IN']) {
            $reservation->claimId = $claimId;
            $reservation->status = ReservationStatus['CHECKED_IN'];
            $reservation->actualDropOffTime = $actualTime;
        } elseif ($newStatus == ReservationStatus['CHECKED_OUT']) {
            $reservation->status = ReservationStatus['CHECKED_OUT'];
            $reservation->actualPickUpTime = $actualTime;
        }
        $reservation->save();
    }
}
