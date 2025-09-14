<?php

namespace App\Services\Mail;

use App\Exceptions\PNJException;
use App\Jobs\Prepaid\BuyPackageJob;
use App\Jobs\Reservation\CancellationEmail;
use App\Jobs\Reservation\ConfirmationEmail;
use App\Mail\ReservationEmail;
use App\Models\Backend\EmailChangeHistory;
use App\Services\Reservation\ReservationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use stdClass;

class EmailSenderService
{
    public function sendReservationConfirmationEmail($reservation, $pricing, $user)
    {
        $mailData = new stdClass;
        $discountDays = 0;

        $reservationService = new ReservationService();
        $reservationService->reservationPricingCalculation($reservation);

        if (isset($user)) {
            $to = $user->user_name;
            $mailData->user = $user;
            $reservationType = "Member";
            $discountDays = $reservationService->reservationWalletDaysCalculation($reservation);
        } else {
            $to = $reservation->driver->email;
            $mailData->user = null;
            $reservationType = "Anon";
        }
        $mailData->discountDays = $discountDays + $pricing->discountedDay;
        try {
            Log::info("Sending confirmation email to " . $to . " for " . $reservationType . " reservation id " . $reservation->id);

            $reservationSummaryUrl = env('APP_URL') . '/reservations/' . $reservation->id . '/invoice';
            $subject = env('APP_NAME') . " " . str_replace('_', ' ', $reservation->lotType) . "  Reservation Confirmation : " . $reservation->id;
            $from = env('MAIL_FROM_ADDRESS');
            $dropOffDate = $reservation->dropOffTime;
            $pickUpDate = $reservation->pickUpTime;
            $durationInDay = $reservationService->getDurationInDay($dropOffDate, $pickUpDate);

            if ($reservation->lotType == LotType['LOT_1']) {
                $lotAddress = "Park N Jet Lot-1 \n 18220 8th Ave S SeaTac\n Seattle WA 98148";
            }
            if ($reservation->lotType == LotType['LOT_2']) {
                $lotAddress = "Park N Jet Lot-2 \n 1244 S 140th Street\n Seattle WA 98168";
            }

            $reservationSummaryUrl .= "?id=" . $reservation->id . "&email=" . $reservation->driver->email . "&dodate=" .  date("Y-m-d", strtotime($dropOffDate));

            $mailData->subject = $subject;
            $mailData->to = $to;
            $mailData->from = $from;
            $mailData->lotAddress = $lotAddress;
            $mailData->dropOffDate = $dropOffDate;
            $mailData->durationInDay = $durationInDay;
            $mailData->pickUpDate = $pickUpDate;
            $mailData->reservation = $reservation;
            $mailData->pricing = $pricing;
            $mailData->summaryUrl = $reservationSummaryUrl;

            if ($reservation->lotType == LotType['LOT_1'] || $reservation->lotType == LotType['LOT_2']) {
                // Mail::to($mailData->to)->send(new ReservationEmail($mailData, 'emails.reservation.confirm_reservation'));
                ConfirmationEmail::dispatch($mailData, 'emails.reservation.confirm_reservation');
            }

            Log::info("Successfully sent reservation confirmation email to " . $to . " for " . $reservationType . " reservation id " . $reservation->id);
        } catch (\Exception $ex) {
            Log::error("Failed To send confirmation email to " . $to . " for " . $reservationType . " reservation id " . $reservation->id);
            Log::error($ex->getMessage());
        }
    }

    public function sendReservationCancellationEmail($reservation, $user = null)
    {
        $mailData = new stdClass;
        if (isset($user)) {
            $to = $reservation->owner->user_name;
            // $to = $user->user_name;
            $mailData->user = $user;
            $reservationType = "Member";
        } else {
            $to = $reservation->driver->email;
            $reservationType = "Anon";
        }
        try {
            Log::info("Sending confirmation email to " . $to . " for " . $reservationType . " reservation id " . $reservation->id);
            $reservationSummaryUrl = env('APP_URL') . '/reservations/' . $reservation->id . '/invoice';
            $subject = env('APP_NAME') . " " . str_replace('_', ' ', $reservation->lotType) . "  Reservation Cancelled : " . $reservation->id;
            $from = env('MAIL_FROM_ADDRESS');
            $dropOffDate = $reservation->dropOffTime;
            $pickUpDate = $reservation->pickUpTime;
            $reservationService = new ReservationService();
            $durationInDay = $reservationService->getDurationInDay($dropOffDate, $pickUpDate);

            if ($reservation->lotType == LotType['LOT_1']) {
                $lotAddress = "Park N Jet Lot-1 \n 18220 8th Ave S SeaTac\n Seattle WA 98148";
            }
            if ($reservation->lotType == LotType['LOT_2']) {
                $lotAddress = "Park N Jet Lot-2 \n 1244 S 140th Street\n Seattle WA 98168";
            }

            $reservationSummaryUrl .= "?id=" . $reservation->id . "&email=" . $reservation->driver->email . "&dodate=" . date("Y-m-d", strtotime($dropOffDate));

            $mailData->subject = $subject;
            $mailData->to = $to;
            $mailData->from = $from;
            $mailData->lotAddress = $lotAddress;
            $mailData->dropOffDate = $dropOffDate;
            $mailData->pickUpDate = $pickUpDate;
            $mailData->durationInDay = $durationInDay;
            $mailData->reservation = $reservation;
            $mailData->summaryUrl = $reservationSummaryUrl;

            // Mail::to($mailData->to)->send(new ReservationEmail($mailData, 'emails.reservation.cancel_reservation'));
            CancellationEmail::dispatch($mailData, 'emails.reservation.cancel_reservation');

            Log::info("Successfully sent reservation cancellation email to " . $to . " for " . $reservationType . " reservation id " . $reservation->id);
        } catch (\Exception $ex) {
            Log::error("Failed To send cancellation email to " . $to . " for " . $reservationType . " reservation id " . $reservation->id);
            Log::error($ex->getMessage());
        }
    }

    public function sendEmailOnChange($previous_email, $new_email)
    {
        $emailChangeHistory = new EmailChangeHistory();
        $emailChangeHistory->previous_email = $previous_email;
        $emailChangeHistory->new_email = $new_email;
        $emailChangeHistory->save();
    }

    public function sendPrepaidPackageConfirmationEmail($user, $prePaidPackageDB)
    {
        $mailData = new stdClass;
        try {

            $subject = env('APP_NAME') . " : PrePaid Package Purchase Confirmation!";
            $from = env('MAIL_FROM_ADDRESS');
            $to = $user->user_name;
            Log::info("Sending PrePaid Package confirmation email to " . $to);

            if ($prePaidPackageDB->lotType == LotType['LOT_1']) {
                $lotAddress = "Park N Jet Lot-1 \n 18220 8th Ave S SeaTac\n Seattle WA 98148";
                $lotId = "Lot 1";
            }
            if ($prePaidPackageDB->lotType == LotType['LOT_2']) {
                $lotAddress = "Park N Jet Lot-2 \n 1244 S 140th Street\n Seattle WA 98168";
                $lotId = "Lot 2";
            }

            $mailData->subject = $subject;
            $mailData->to = $to;
            $mailData->from = $from;
            $mailData->lotAddress = $lotAddress;
            $mailData->lotId = $lotId;
            $mailData->days = $prePaidPackageDB->days;
            $mailData->expirationDate = date("Y-m-d H:i:s A", strtotime(Carbon::now()->addMonths($prePaidPackageDB->expirationDate)));
            $mailData->price = $prePaidPackageDB->price;
            $mailData->savings = $prePaidPackageDB->savings;

            // Mail::to($mailData->to)->send(new ReservationEmail($mailData, 'emails.prepaid_package'));
            BuyPackageJob::dispatch($mailData, 'emails.prepaid_package');

            Log::info("Successfully sent PrePaid Package confirmation email to " . $to);
        } catch (\Exception $ex) {
            Log::error("Failed To send PrePaid Package confirmation email to " . $to);
            Log::error($ex->getMessage());
            throw new PNJException($ex->getMessage());
        }
    }
}
