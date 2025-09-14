<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Jobs\Reservation\ConfirmationEmail;
use App\Models\Backend\MemReservation;
use App\Models\Backend\MemUser;
use App\Models\Lot1CustomerActivity;
use App\Models\Lot2CustomerActivity;
use App\Services\Reservation\ReservationService;
use App\Services\Sync\LotMemberReservationSyncService;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;

class TestController extends Controller
{
    private $reservationService;

    public function __construct(
        ReservationService $reservationService,
    ) {
        $this->reservationService = $reservationService;
    }

    public function cardTokenGenerate()
    {
        try {
            $stripe = new \Stripe\StripeClient('sk_test_5eC39HqLyjWDarjtT1zdp8dc');
            // $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            return $stripe->tokens->create([
                'card' => [
                    'number' => '5555555555554444',
                    'exp_month' => '5',
                    'exp_year' => '2026',
                    'cvc' => '123',
                ],
            ]);
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }

    public function guest_reservation($rsvnId)
    {
        return $this->reservationService
            ->getAnonReservation()
            ->where('id', $rsvnId)
            ->first();
    }

    public function member_reservation($rsvnId)
    {
        $reservation = $this->reservationService
            ->getMemReservation()
            ->where('id', $rsvnId)
            ->first();

        $reservation = removeCreatedAtAndUpdatedAt($reservation);
        return $reservation;
    }

    public function test_mail()
    {
        $user = auth()->user();
        $reservation = MemReservation::where('id', 950294)->first();
        $pricing = $reservation->pricing->first();

        // return [$user, $reservation, $pricing];

        $reservationSummaryUrl = env('UIURL') . "/reservations/summary";
        $subject = env('APP_NAME') . " " . $reservation->lotType . "  Reservation Confirmation :" . $reservation->id;
        $to = $user->user_name;
        $from = env('MAIL_FROM_ADDRESS');
        $dropOffDate = $reservation->dropOffTime;
        $pickUpDate = $reservation->pickUpTime;

        if ($reservation->lotType == LotType['LOT_1']) {
            $lotAddress = "Park N Jet Lot-1 \n 18220 8th Ave S SeaTac\n Seattle WA 98148";
        }
        if ($reservation->lotType == LotType['LOT_2']) {
            $lotAddress = "Park N Jet Lot-2 \n 1244 S 140th Street\n Seattle WA 98168";
        }

        $discountDays = 0;

        foreach ($reservation->wallet_transaction as $key => $walletTransaction) {
            $difference = $walletTransaction->oldBalance - $walletTransaction->newBalance;
            $discountDays += $difference;
        }

        foreach ($reservation->pre_paid_wallet_txns as $key => $prePaidWalletTxn) {
            $difference = $prePaidWalletTxn->oldBalance - $prePaidWalletTxn->newBalance;
            $discountDays += $difference;
        }

        $dropOffDate = date("Y-m-d", strtotime($dropOffDate));
        $reservationSummaryUrl .= "?id=" . $reservation->id . "&email=" . $reservation->driver->email . "&dodate=" . $dropOffDate;

        $mailData = new stdClass;
        $mailData->subject = $subject;
        $mailData->to = $to;
        $mailData->from = $from;
        $mailData->lotAddress = $lotAddress;
        $mailData->discountDays = $discountDays;
        $mailData->dropOffDate = $dropOffDate;
        $mailData->pickUpDate = $pickUpDate;
        $mailData->reservation = $reservation;
        $mailData->pricing = $pricing;
        $mailData->summaryUrl = $reservationSummaryUrl;
        $mailData->user = $user;

        if ($reservation->lotType == LotType['LOT_1']) {
            ConfirmationEmail::dispatch($mailData, 'emails.reservation.LOT_1');
            // return view('emails.reservation.LOT_1')
            //     ->with('data', $mailData);
        }
        if ($reservation->lotType == LotType['LOT_2']) {
            ConfirmationEmail::dispatch($mailData, 'emails.reservation.LOT_2');
            // return view('emails.reservation.LOT_2')
            //     ->with('data', $mailData);
        }
    }

    public function test_apple_pay()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        // return $stripe->applePayDomains->create(['domain_name' => 'https://plg.parknjetseatac.com']);
        return $stripe->applePayDomains->create(['domain_name' => 'https://b5c9-103-52-141-133.ngrok-free.app']);
    }

    public function member_reservation_sync($rsvnId)
    {
        $reservation = $this->reservationService
            ->getMemReservation()
            ->where('id', $rsvnId)
            ->first();
        $user = MemUser::where('id', $reservation->owner_id)
            ->first();

        $lotMemberReservationSyncService = new LotMemberReservationSyncService;
        $lotMemberReservationSyncService->syncReservationToLot($reservation, $user);
    }

    public function findSequence(Request $request)
    {

        $seqs = [
            array(
                'table' => 'anon_driver',
                'seq' => 'AnonDriver_SEQ'
            ),
            //Not Using on laravel
            array(
                'table' => 'anon_lot_payment',
                'seq' => 'AnonLotPayment_SEQ'
            ),
            array(
                'table' => 'anon_payment',
                'seq' => 'AnonPayment_SEQ'
            ),
            array(
                'table' => 'anon_pricing',
                'seq' => 'AnonPricing_SEQ'
            ),
            array(
                'table' => 'anon_vehicle',
                'seq' => 'AnonVehicle_SEQ'
            ),
            //Not Using on laravel
            array(
                'table' => 'mem_authToken',
                'seq' => 'AuthToken_SEQ'
            ),
            array(
                'table' => 'mem_driver',
                'seq' => 'MemberDriver_SEQ'
            ),
            //Not Using on laravel
            array(
                'table' => 'mem_lot_payment',
                'seq' => 'MemberLotPayment_SEQ'
            ),
            array(
                'table' => 'mem_payment',
                'seq' => 'MemberPayment_SEQ'
            ),
            array(
                'table' => 'mem_pricing',
                'seq' => 'MemberPricing_SEQ'
            ),
            array(
                'table' => 'mem_referral',
                'seq' => 'MemberReferral_SEQ'
            ),
            array(
                'table' => 'mem_vehicle',
                'seq' => 'MemberVehicle_SEQ'
            ),
            array(
                'table' => 'mem_prepaid_package_payment',
                'seq' => 'PrePaidPackagePayment_SEQ'
            ),
            array(
                'table' => 'mem_wallet_prepaid',
                'seq' => 'PrePaidWallet_SEQ'
            ),
            array(
                'table' => 'mem_wallet_prepaid_txn',
                'seq' => 'PrePaidWalletTxn_SEQ'
            ),
            array(
                'table' => 'mem_wallet_prepaid_txn_pricing',
                'seq' => 'PrepaidWalletTxnPricing_SEQ'
            ),
            array(
                'table' => 'mem_reservation_driver',
                'seq' => 'ReservationDriver_SEQ'
            ),
            array(
                'table' => 'mem_reservation_vehicle',
                'seq' => 'ReservationVehicle_SEQ'
            ),
            array(
                'table' => 'mem_reward',
                'seq' => 'Reward_SEQ'
            ),
            //Not Using on laravel
            array(
                'table' => 'mem_reward_txn',
                'seq' => 'RewardTransaction_SEQ'
            ),
            array(
                'table' => 'stripe_customer',
                'seq' => 'StripeCustomer_SEQ'
            ),
            array(
                'table' => 'mem_user',
                'seq' => 'User_SEQ'
            ),
            //Not Using on laravel
            array(
                'table' => 'mem_verification_token',
                'seq' => 'VerificationToken_SEQ'
            ),
            array(
                'table' => 'mem_wallet',
                'seq' => 'Wallet_SEQ'
            ),
            array(
                'table' => 'mem_wallet_txn',
                'seq' => 'WalletTransaction_SEQ'
            ),
        ];

        echo "<pre>";
        foreach ($seqs as $key => $seq) {
            $next_val = DB::connection('backend_mysql')
                ->table($seq["seq"])
                ->first()->next_val;
            $id = DB::connection('backend_mysql')
                ->table($seq["table"])
                ->select('id')
                ->orderBy('id', 'desc')
                ->first()->id;
            echo $seq["table"] . " Total " . $id . " Seq " . $next_val . "<br><br>";
            echo ($next_val - $id) . "<br><br>";
            if ($request->action == 1) {
                DB::connection('backend_mysql')
                    ->table($seq["seq"])
                    ->update(['next_val' => $id + 1]);
            }
        }
    }

    public function retrievePaymentIntents($lot, $pid)
    {
        $paymentId = $pid;
        if ($lot == LotType['LOT_1']) {
            $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
        } elseif ($lot == LotType['LOT_2']) {
            $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
        }
        return $paymentIntent = $stripe->paymentIntents->retrieve($paymentId, []);

        // if ($paymentIntent) {

        //     $paymentMethodsUpdate = $stripe->paymentIntents->update($paymentId, [
        //         "description" => "Reservation Id = " . $reservation->id . " and LotId = " . $reservation->lotType
        //     ]);
        // }
        // return $paymentMethodsUpdate['charges']['data'][0];
    }

    function test_reseravation(ReservationService $reservationService, $reservationId)
    {
        $anonReservationDB = $reservationService
            ->getAnonReservation()
            ->where('id', $reservationId)
            ->first();

        if ($anonReservationDB != null) {
            $anonReservationDB->type = "Anon";
            return $anonReservationDB;
        } else {
            $memberReservationDB = $reservationService
                ->getMemReservation()
                ->where('id', $reservationId)
                ->first();
            if ($memberReservationDB != null) {
                $memberReservationDB->type = "Mem";
                return $memberReservationDB;
            }
        }
        return "Not found";
    }

    function test_fix_payment_sync(ReservationService $reservationService, $reservationId)
    {
        $anonReservationDB = $reservationService
            ->getAnonReservation()
            ->where('id', $reservationId)
            ->first();

        if ($anonReservationDB != null) {
            $lotMemReservationSyncService = new LotMemberReservationSyncService;
            $lotMemReservationSyncService->syncReservationToLot($anonReservationDB, false);
            return $anonReservationDB;
        } else {
            $memberReservationDB = $reservationService
                ->getMemReservation()
                ->where('id', $reservationId)
                ->first();
            if ($memberReservationDB != null) {
                $lotMemReservationSyncService = new LotMemberReservationSyncService;
                $lotMemReservationSyncService->syncReservationToLot($memberReservationDB, true);
                return $memberReservationDB;
            }
        }
        return "Not found";
    }
}
