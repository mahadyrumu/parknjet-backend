<?php

namespace App\Services\Wallet;

use App\Exceptions\PNJException;
use App\Models\Backend\MemWallet;
use Illuminate\Support\Facades\Log;
use Payment;

class WalletService
{
    public function applyReferralBonus($referral, $reservation)
    {
        Log::info("Applying Referral Bonus to wallet of user " . $referral->referredUserName . " with user PK " . $referral->referredUser->id);

        $userWallet = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userWallet = $referral->referredUser->walletLot1;
        } else {
            $userWallet = $referral->referredUser->wallet;
        }

        if ($userWallet == null) {

            Log::info("User has no wallet creating one");
            $userWallet = $this->createWallet($referral->referredUser);

            if ($reservation->lotType == LotType['LOT_1']) {
                $referral->referredUser->walletLot1_id = $userWallet->id;
            } else {
                $referral->referredUser->wallet = $userWallet->id;
            }
            $referral->referredUser->save();
        }

        Log::info("User wallet found with PK " . $userWallet->id);

        Log::info("Adding " . Wallet['REFERRAL_BONUS_DAYS'] . " days to users wallet");
        Log::info("Old balance in days " . $userWallet->days);

        $walletTxnService = new WalletTxnService;
        $walletTxn = $walletTxnService->walletTransactionForWallet(
            $userWallet,
            "",
            TriggerType['REFERRAL'],
            Payment::TransactionType['DEBIT'],
            $userWallet->days,
            $userWallet->days + Wallet['REFERRAL_BONUS_DAYS'],
            $reservation->id
        );
        $userWallet->days = $userWallet->days + Wallet['REFERRAL_BONUS_DAYS'];
        Log::info("New balance in days " . $userWallet->days);
        Log::info("Transaction List size = " . count($userWallet->walletTxn));
        $userWallet->save();
        Log::info("Referred bonus of " . Wallet['REFERRAL_BONUS_DAYS'] . " added to users wallet with PK " . $userWallet->id);


        Log::info("Applying Referral Bonus to wallet of user's referrer " . $referral->referredBy->user_name . " with user PK ". $referral->referredBy->id);
        
        $userReferredByWallet = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userReferredByWallet = $referral->referredBy->walletLot1;
        } else {
            $userReferredByWallet = $referral->referredBy->wallet;
        }

        if ($userReferredByWallet == null) {

            Log::info("User has no wallet creating one");
            $userReferredByWallet = $this->createWallet($referral->referredBy);

            if ($reservation->lotType == LotType['LOT_1']) {
                $referral->referredBy->walletLot1_id = $userReferredByWallet->id;
            } else {
                $referral->referredBy->wallet = $userReferredByWallet->id;
            }
            $referral->referredBy->save();
        }

        Log::info("User Referred by wallet found with PK " . $userReferredByWallet->id);
        Log::info("Adding " . Wallet['REFERRAL_BONUS_DAYS'] . " days to users wallet");
        Log::info("Old balance in days " . $userReferredByWallet->days);

        $walletReferredByTxn = $walletTxnService->walletTransactionForWallet(
            $userReferredByWallet,
            "",
            TriggerType['REFERRAL'],
            Payment::TransactionType['DEBIT'],
            $userReferredByWallet->days,
            $userReferredByWallet->days + Wallet['REFERRAL_BONUS_DAYS'],
            $referral->id
        );
        $userReferredByWallet->days = $userReferredByWallet->days + Wallet['REFERRAL_BONUS_DAYS'];
        Log::info("New balance in days " . $userReferredByWallet->days);
        Log::info("Transaction List size = " . count($userReferredByWallet->walletTxn));
        $userReferredByWallet->save();
        Log::info("Referred bonus of " . Wallet['REFERRAL_BONUS_DAYS'] . " added to users wallet with PK " . $userReferredByWallet->id);
        return $walletTxn;
    }

    public function subtractForReservation($days, $triggerType, $comment, $reservation)
    {
        $userWallet = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userWallet = $reservation->owner->walletLot1;
        } else {
            $userWallet = $reservation->owner->wallet;
        }

        if ($userWallet == null) {
            throw new PNJException("User wallet could not be found!");
        }

        Log::info("User wallet found with PK " . $userWallet->id);

        if ($days > $userWallet->days) {
            throw new PNJException("User wallet days are not enough for this transaction");
        }

        $walletTxnService = new WalletTxnService;
        $walletTxn = $walletTxnService->walletTransactionForReservation($userWallet, $comment, $triggerType, Payment::TransactionType['DEBIT'], $userWallet->days, $userWallet->days - $days, $reservation->id);
        $userWallet->days = $userWallet->days - $days;
        $userWallet->save();

        return $walletTxn;
    }

    public function subtractForUser($days, $triggerType, $comment, $lotType, $user)
    {
        $userWallet = null;
        if ($lotType == LotType['LOT_1']) {
            $userWallet = $user->walletLot1;
        } else {
            $userWallet = $user->wallet;
        }

        if ($userWallet == null) {
            throw new PNJException("User wallet could not be found!");
        }

        Log::info("User wallet found with PK " . $userWallet->id);

        if ($days > $userWallet->days) {
            throw new PNJException("User wallet days are not enough for this transaction");
        }

        $walletTxnService = new WalletTxnService;
        $walletTxn = $walletTxnService->walletTransactionForUser(
            $userWallet,
            $comment,
            $triggerType,
            Payment::TransactionType['DEBIT'],
            $userWallet->days,
            $userWallet->days - $days
        );
        $userWallet->days = $userWallet->days - $days;
        $userWallet->save();

        return $walletTxn;
    }

    public function addForReservation($days, $triggerType, $comment, $reservation)
    {
        $userWallet = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userWallet = $reservation->owner->walletLot1;
        } else {
            $userWallet = $reservation->owner->wallet;
        }

        if ($userWallet == null) {

            Log::info("User has no wallet creating one");
            $userWallet = $this->createWallet($reservation->owner);

            if ($reservation->lotType == LotType['LOT_1']) {
                $reservation->owner->walletLot1_id = $userWallet->id;
            } else {
                $reservation->owner->wallet = $userWallet->id;
            }
            $reservation->owner->save();
        }

        Log::info("User wallet found with PK " . $userWallet->id);

        Log::info("Adding " . $days . " days to users wallet");
        $walletTxnService = new WalletTxnService;
        $walletTxn = $walletTxnService->walletTransactionForReservation($userWallet, $comment, $triggerType, Payment::TransactionType['DEBIT'], $userWallet->days, $userWallet->days + $days, $reservation->id);
        $userWallet->days = $userWallet->days + $days;
        $userWallet->save();
        return $walletTxn;
    }

    public function addForUser($days, $triggerType, $comment, $lotType, $user)
    {
        $userWallet = null;
        if ($lotType == LotType['LOT_1']) {
            $userWallet = $user->walletLot1;
        } else {
            $userWallet = $user->wallet;
        }

        if ($userWallet == null) {
            Log::info("User has no wallet creating one");
            $userWallet = $this->createWallet($user);

            if ($lotType == LotType['LOT_1']) {
                $user->walletLot1_id = $userWallet->id;
            } else {
                $user->wallet = $userWallet->id;
            }

            $user->save();
        }
        Log::info("User wallet found with PK " . $userWallet->id);

        Log::info("Adding " . $days . " days to users wallet");
        $walletTxnService = new WalletTxnService;
        $walletTxn = $walletTxnService->walletTransactionForUser(
            $userWallet,
            $comment,
            $triggerType,
            Payment::TransactionType['DEBIT'],
            $userWallet->days,
            $userWallet->days + $days
        );
        $userWallet->days = $userWallet->days + $days;
        $userWallet->save();
        return $walletTxn;
    }

    public function createWallet($user)
    {
        $mem_wallet = new MemWallet;
        $mem_wallet->days = 0;
        $mem_wallet->owner_id = $user->id;
        $mem_wallet->createdBy_id = $user->id;
        $mem_wallet->save();
        return $mem_wallet;
    }
}
