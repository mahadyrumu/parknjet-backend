<?php

namespace App\Services\Reservation;

use App\Exceptions\PNJException;
use App\Services\Mail\EmailSenderService;
use App\Services\PaymentGateway\StripeService;
use App\Services\Sync\LotMemberReservationSyncService;
use App\Services\Wallet\PrePaidWalletService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payment;
use Status;

class ReservationCancelService
{
    // cancelReservationAndSendEmailAndSyncToLot
    public function cancelReservationAndSendEmailAndSyncToLot($rsvn, $currentLoggedInUser)
    {
        $reservationDB = $this->cancelReservation($rsvn);
        // send Email notification And Sync To Lot application
        $this->sendEmailAndSyncToLot($reservationDB, $currentLoggedInUser);
        return $reservationDB;
    }

    public function sendEmailAndSyncToLot($reservation, $user)
    {
        $lotMemReservationSyncService = new LotMemberReservationSyncService;
        $lotMemReservationSyncService->syncReservationToLot($reservation, $user);
        $emailSenderService = new EmailSenderService;
        $emailSenderService->sendReservationCancellationEmail($reservation, $user);
    }

    public function refundReservation($reservation)
    {
        Log::info("Reservation Cancellation request received for reservation id " . $reservation->id);
        if ($reservation->status == Status::ReservationStatus['CHECKED_IN']) {
            Log::info("Exception: Cannot Cancelled CHECKED_IN reservation");
            throw new PNJException("Cannot Cancelled CHECKED_IN reservation");
        } elseif ($reservation->status == Status::ReservationStatus['CHECKED_OUT']) {
            Log::info("Exception: Cannot Cancelled CHECKED_OUT reservation");
            throw new PNJException("Cannot Cancelled CHECKED_OUT reservation");
        }

        if (isset($reservation->pricing) && count($reservation->pricing) > 0) {
            foreach ($reservation->pricing as $key => $eachPricing) {
                if ($eachPricing->payment != null && Payment::PaymentStatus['PAID'] == $eachPricing->payment->paymentStatus) {

                    $stripeService = new StripeService;
                    $stripeService->refundPaymentThroughStripe($reservation, $eachPricing->payment);
                }
            }
        }
        return $reservation;
    }

    public function cancelReservation($reservation)
    {
        Log::info("Reservation Cancellation request received for reservation id " . $reservation->id);
        if ($reservation->status == Status::ReservationStatus['CANCELLED']) {
            Log::info("Exception: Reservation is already cancelled");
            throw new PNJException("Reservation is already cancelled");
        } elseif ($reservation->status == Status::ReservationStatus['CHECKED_IN']) {
            Log::info("Exception: Cannot Cancelled CHECKED_IN reservation");
            throw new PNJException("Cannot Cancelled CHECKED_IN reservation");
        } elseif ($reservation->status == Status::ReservationStatus['CHECKED_OUT']) {
            Log::info("Exception: Cannot Cancelled CHECKED_OUT reservation");
            throw new PNJException("Cannot Cancelled CHECKED_OUT reservation");
        }
        $reservation = DB::connection('backend_mysql')->transaction(function () use ($reservation) {

            $reservation->status = Status::ReservationStatus['CANCELLED'];

            if (isset($reservation->pricing) && count($reservation->pricing) > 0) {
                foreach ($reservation->pricing as $key => $eachPricing) {
                    if ($eachPricing->payment != null && Payment::PaymentStatus['PAID'] == $eachPricing->payment->paymentStatus) {

                        $stripeService = new StripeService;
                        $stripeService->refundPaymentThroughStripe($reservation, $eachPricing->payment);
                    }
                }
            }

            $currentWalletDays = 0.0;
            if (isset($reservation->wallet_transaction) && count($reservation->wallet_transaction) > 0) {
                foreach ($reservation->wallet_transaction as $walletTransaction) {
                    $currentWalletDays += ($walletTransaction->oldBalance - $walletTransaction->newBalance);
                }
            }

            if ($currentWalletDays > 0.0) {
                $walletService = new WalletService;
                $walletTransaction = $walletService->addForReservation($currentWalletDays, Payment::TriggerType['RESERVATION_CANCELLATION'], "", $reservation);
            }

            $currentPrepaidWalletDays = 0.0;
            if (isset($reservation->pre_paid_wallet_txns) && count($reservation->pre_paid_wallet_txns) > 0) {
                foreach ($reservation->pre_paid_wallet_txns as $prePaidWalletTxn) {
                    $currentPrepaidWalletDays += ($prePaidWalletTxn->oldBalance - $prePaidWalletTxn->newBalance);
                }
            }

            if ($currentPrepaidWalletDays > 0.0) {
                $prePaidWalletService = new PrePaidWalletService;
                $prePaidWalletTxn = $prePaidWalletService->addForReservation($currentPrepaidWalletDays, Payment::TriggerType['RESERVATION_CANCELLATION'], "", $reservation);
            }

            $reservation->isDeleted = 1;
            $reservation->save();
            return $reservation;
        });

        return $reservation;
    }
    public function refundCancelReservation($reservation)
    {
        Log::info("Reservation Cancellation request received for reservation id " . $reservation->id);

        $reservation->status = Status::ReservationStatus['CANCELLED'];

        if (isset($reservation->pricing) && count($reservation->pricing) > 0) {
            foreach ($reservation->pricing as $key => $eachPricing) {
                if ($eachPricing->payment != null && Payment::PaymentStatus['PAID'] == $eachPricing->payment->paymentStatus) {

                    $stripeService = new StripeService;
                    $stripeService->refundPaymentThroughStripe($reservation, $eachPricing->payment);
                }
            }
        }

        $currentWalletDays = 0.0;
        if (isset($reservation->wallet_transaction) && count($reservation->wallet_transaction) > 0) {
            foreach ($reservation->wallet_transaction as $walletTransaction) {
                $currentWalletDays += ($walletTransaction->oldBalance - $walletTransaction->newBalance);
            }
        }

        if ($currentWalletDays > 0.0) {
            $walletService = new WalletService;
            $walletTransaction = $walletService->addForReservation($currentWalletDays, Payment::TriggerType['RESERVATION_CANCELLATION'], "", $reservation);
        }

        $currentPrepaidWalletDays = 0.0;
        if (isset($reservation->pre_paid_wallet_txns) && count($reservation->pre_paid_wallet_txns) > 0) {
            foreach ($reservation->pre_paid_wallet_txns as $prePaidWalletTxn) {
                $currentPrepaidWalletDays += ($prePaidWalletTxn->oldBalance - $prePaidWalletTxn->newBalance);
            }
        }

        if ($currentPrepaidWalletDays > 0.0) {
            $prePaidWalletService = new PrePaidWalletService;
            $prePaidWalletTxn = $prePaidWalletService->addForReservation($currentPrepaidWalletDays, Payment::TriggerType['RESERVATION_CANCELLATION'], "", $reservation);
        }

        $reservation->isDeleted = 1;
        $reservation->save();

        return $reservation;
    }

}
