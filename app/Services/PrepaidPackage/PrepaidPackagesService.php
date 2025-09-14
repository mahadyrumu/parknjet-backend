<?php

namespace App\Services\PrepaidPackage;

use App\Exceptions\PNJException;
use App\Models\Backend\Admin\PrepaidPackage;
use App\Services\Mail\EmailSenderService;
use App\Services\PaymentGateway\StripeService;
use App\Services\User\UserService;
use App\Services\Wallet\PrePaidWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payment;

class PrepaidPackagesService
{
    public function getPrepaidPackage()
    {
        return PrepaidPackage::orderBy('id', 'asc');
    }

    public function buyAPackage($request, $owner_id, $package_id)
    {
        if ($request->stripeToken == null && $request->paymentId == null) {
            throw new PNJException("Payment Info Not Found.");
        } else {

            $currentLoggedInUser = auth()->user();
            if (!$currentLoggedInUser) {
                throw new PNJException("Unauthorized User!");
            }

            $this->buyPackageAndSendEmail($request, $currentLoggedInUser, $package_id);
            return $currentLoggedInUser;
        }
    }

    public function buyPackageAndSendEmail($request, $user, $package_id)
    {
        $prePaidPackageDB = $this->getPrepaidPackage()
            ->where('id', $package_id)
            ->first();
        $user = $this->buyPackageUsingStripe($request, $prePaidPackageDB, $user, $package_id);
        $emailSenderService = new EmailSenderService;
        $emailSenderService->sendPrepaidPackageConfirmationEmail($user, $prePaidPackageDB);
        return $user;
    }

    public function buyPackageUsingStripe($request, $prePaidPackageDB, $currentLoggedInUser, $package_id)
    {
        Log::info("Request received to buy Pre Paid Package with id = " . $package_id . " for userId = " . $currentLoggedInUser->id . " and Stripe Token = " . $request->stripeToken . " Or Payment Intent = " . $request->paymentId);

        if ($prePaidPackageDB == null) {
            throw new PNJException(" Prepaid Package with Id = " . $package_id . " Not found!");
        }

        $taxAmount = ($prePaidPackageDB->price * 10) / 100;
        Log::info("Tax amount is " . $taxAmount . " for prepaid package payment");

        $finalAmount = $prePaidPackageDB->price + $taxAmount;
        Log::info("Charging " . $finalAmount . " from stripe for prepaid package payment");

        $prePaidWallet = null;
        if ($prePaidPackageDB->lotType == LotType['LOT_1']) {
            $prePaidWallet = $currentLoggedInUser->prePaidWalletLot1;
        } else if ($prePaidPackageDB->lotType == LotType['LOT_2']) {
            $prePaidWallet = $currentLoggedInUser->prePaidWalletLot2;
        } else {
            throw new PNJException("PrePaidPackage with Id = " . $prePaidPackageDB->id . " is mis-configured, Its lotId is not set correctly");
        }

        DB::connection('backend_mysql')->transaction(function () use ($request, $prePaidWallet, $prePaidPackageDB, $currentLoggedInUser, $taxAmount, $finalAmount) {
            if ($prePaidWallet == null) {
                Log::info(" PrePaidWallet is null for userId = " . $currentLoggedInUser->id . " , Creating new PrePaidWallet");
                $prePaidWalletService = new PrePaidWalletService;
                $prePaidWalletDB = $prePaidWalletService->createNewWalletAndAddPrePaidPackage($currentLoggedInUser, $prePaidPackageDB, $taxAmount, $finalAmount);
                Log::info("New PrePaidWallet created with Id = " . $prePaidWalletDB->id . " for user id = " . $currentLoggedInUser->id);
                Log::info("New Transaction size for this wallet is " . count($prePaidWalletDB->walletPrepaidTxn));
                $userService = new UserService;
                if ($prePaidPackageDB->lotType == LotType['LOT_1']) {
                    $userService->setPrePaidWalletLot1($currentLoggedInUser, $prePaidWalletDB->id);
                } else if ($prePaidPackageDB->lotType == LotType['LOT_2']) {
                    $userService->setPrePaidWalletLot2($currentLoggedInUser, $prePaidWalletDB->id);
                }
            } else {
                // $userDB = $currentLoggedInUser;
                Log::info("PrePaidWallet found with id = " . $prePaidWallet->id . " for User with id " . $currentLoggedInUser->id);
                $prePaidWalletService = new PrePaidWalletService;
                $prePaidWalletDB = $prePaidWalletService->addPrePaidPackage($prePaidWallet, $prePaidPackageDB, $taxAmount, $finalAmount);
                Log::info("New Transaction size for this wallet is " . count($prePaidWalletDB->walletPrepaidTxn));
            }
            $stripeService = new StripeService;
            if ($request->paymentId) {
                $stripeCharge = $stripeService->checkPaymentMethodThroughStripeForPackage($request->paymentId, $prePaidPackageDB);
            } else {
                $stripeCharge = $stripeService->chargeCreditCardThroughStripeForPackage($finalAmount, $request->stripeToken, $prePaidPackageDB);
            }
            $cardType = $stripeService->getCardType($stripeCharge);
            $prepaidPackagePaymentService = new PrepaidPackagePaymentService;

            $prePaidPackagePaymentDB = $prepaidPackagePaymentService->createPrePaidPackagePayment($prePaidPackageDB, $stripeCharge, Payment::PaymentStatus['PAID'], $currentLoggedInUser, $cardType);
            Log::info("PrePaidPackagePayment created in DB with ID = " . $prePaidPackagePaymentDB->id);
        });

        return $currentLoggedInUser;
    }
}
