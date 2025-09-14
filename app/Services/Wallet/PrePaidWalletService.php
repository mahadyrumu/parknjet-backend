<?php

namespace App\Services\Wallet;

use App\Exceptions\PNJException;
use App\Models\Backend\MemWalletPrepaid;
use App\Traits\SequenceUpdate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Payment;

class PrePaidWalletService
{
    use SequenceUpdate;
    
    public function subtractForReservation($days, $triggerType, $comment, $reservation)
    {
        $userPrepaidWallet = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userPrepaidWallet = $reservation->owner->prepaidWalletLot1;
        } else {
            $userPrepaidWallet = $reservation->owner->prePaidWalletLot2;
        }

        if ($userPrepaidWallet == null) {
            throw new PNJException("User Prepaid wallet could not be found!");
        }

        Log::info("User Prepaid Wallet found with PK " . $userPrepaidWallet->id);

        if ($days > $userPrepaidWallet->days) {
            throw new PNJException("User prepaid wallet days are not enough for this transaction");
        }

        $prePaidWalletTxnService = new PrePaidWalletTxnService;
        $prePaidWalletTxn = $prePaidWalletTxnService->prePaidWalletTxnForReservation($userPrepaidWallet, $comment, $triggerType, Payment::TransactionType['DEBIT'], $userPrepaidWallet->days, $userPrepaidWallet->days - $days, $reservation->id);
        $userPrepaidWallet->days = $userPrepaidWallet->days - $days;
        $userPrepaidWallet->save();
        return $prePaidWalletTxn;
    }

    public function addForReservation($days, $triggerType, $comment, $reservation)
    {
        $userPrepaidWallet = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userPrepaidWallet = $reservation->owner->prepaidWalletLot1;
        } else {
            $userPrepaidWallet = $reservation->owner->prePaidWalletLot2;
        }

        if ($userPrepaidWallet == null && $reservation->lotType == LotType['LOT_1']) {
            Log::info("User has no prepaid wallet for lot 1 creating one");
            $userPrepaidWallet = $this->createPrePaidWallet($reservation->owner);
            $reservation->owner->prepaidWalletLot1 = $userPrepaidWallet->id;
            $reservation->owner->save();
        }

        if ($userPrepaidWallet == null && $reservation->lotType == LotType['LOT_2']) {
            Log::info("User has no prepaid wallet for lot 2 creating one");
            $userPrepaidWallet = $this->createPrePaidWallet($reservation->owner);
            $reservation->owner->prePaidWalletLot2 = $userPrepaidWallet->id;
            $reservation->owner->save();
        }
        Log::info("User prepaid wallet found with PK " . $userPrepaidWallet->id);

        Log::info("Adding " . $days . " days to users prepaid wallet");

        $prePaidWalletTxnService = new PrePaidWalletTxnService;
        $prePaidWalletTxn = $prePaidWalletTxnService->prePaidWalletTxnForReservation($userPrepaidWallet, $comment, $triggerType, Payment::TransactionType['DEBIT'], $userPrepaidWallet->days, $userPrepaidWallet->days + $days, $reservation->id);
        $userPrepaidWallet->days = $userPrepaidWallet->days + $days;
        $userPrepaidWallet->save();

        return $prePaidWalletTxn;
    }

    public function addForUser($days, $triggerType, $comment, $lotType, $user)
    {
        $userPrepaidWallet = null;
        if ($lotType == LotType['LOT_1']) {
            $userPrepaidWallet = $user->prepaidWalletLot1;
        } else {
            $userPrepaidWallet = $user->prePaidWalletLot2;
        }

        if ($userPrepaidWallet == null && $lotType == LotType['LOT_1']) {
            Log::info("User has no wallet creating one");
            $userPrepaidWallet = $this->createPrePaidWallet($user);
            $user->prePaidWalletLot1_id = $userPrepaidWallet->id;
            $user->save();
        }

        if ($userPrepaidWallet == null && $lotType == LotType['LOT_2']) {
            Log::info("User has no wallet creating one");
            $userPrepaidWallet = $this->createPrePaidWallet($user);
            $user->prePaidWalletLot2_id = $userPrepaidWallet->id;
            $user->save();
        }
        Log::info("User prepaid wallet found with PK " . $userPrepaidWallet->id);

        Log::info("Adding " . $days . " days to users prepaid wallet");

        $prePaidWalletTxnService = new PrePaidWalletTxnService;
        $prePaidWalletTxn = $prePaidWalletTxnService->prePaidWalletTxn(
            $userPrepaidWallet, $comment, $triggerType, 
            Payment::TransactionType['DEBIT'], 
            $userPrepaidWallet->days, 
            $userPrepaidWallet->days + $days);
        $userPrepaidWallet->days = $userPrepaidWallet->days + $days;
        $userPrepaidWallet->save();

        return $prePaidWalletTxn;
    }

    public function subtractForUser($days, $triggerType, $comment, $lotType, $user)
    {
        $userPrepaidWallet = null;
        if ($lotType == LotType['LOT_1']) {
            $userPrepaidWallet = $user->prepaidWalletLot1;
        } else {
            $userPrepaidWallet = $user->prePaidWalletLot2;
        }

        if ($userPrepaidWallet == null) {
            throw new PNJException("User prepaid wallet could not be found!");
        }
        
        Log::info("User prepaid wallet found with PK " . $userPrepaidWallet->id);

        Log::info("Subtracting " . $days . " days from users prepaid wallet");

        if ($days > $userPrepaidWallet->days) {
            throw new PNJException("User prepaid wallet days are not enough for this transaction");
        }

        $prePaidWalletTxnService = new PrePaidWalletTxnService;

        $prePaidWalletTxn = $prePaidWalletTxnService->prePaidWalletTxn(
            $userPrepaidWallet, $comment, $triggerType, 
            Payment::TransactionType['DEBIT'], 
            $userPrepaidWallet->days, 
            $userPrepaidWallet->days - $days);

        $userPrepaidWallet->days = $userPrepaidWallet->days - $days;
        $userPrepaidWallet->save();

        return $prePaidWalletTxn;
    }

    public function createPrePaidWallet($user)
    {
        $memWalletPrepaid = new MemWalletPrepaid;
        $memWalletPrepaid->version = 1;
        $memWalletPrepaid->days = 0;
        $memWalletPrepaid->expirationDate = date("Y-m-d h:m:s");
        $memWalletPrepaid->owner_id = $user->id;
        $memWalletPrepaid->createdBy_id = $user->id;
        $memWalletPrepaid->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_prepaid', 'PrePaidWallet_SEQ');
        return $memWalletPrepaid;
    }

    public function createNewWalletAndAddPrePaidPackage($currentLoggedInUser, $prePaidPackageDB, $tax, $totalAmount)
    {
        $newPrePaidWallet = $this->createPrePaidWallet($currentLoggedInUser);
        $this->addPrePaidPackage($newPrePaidWallet, $prePaidPackageDB, $tax, $totalAmount);
        return $newPrePaidWallet;
    }

    public function addPrePaidPackage($prePaidWalletDB, $prePaidPackageDB, $tax, $totalAmount)
    {
        $days = $prePaidWalletDB->days ?? 0;
        $prePaidWalletTxnPricingService = new PrePaidWalletTxnPricingService;
        $prepaidWalletTxnPricing = $prePaidWalletTxnPricingService->createPrepaidWalletTxnPricing($prePaidPackageDB->price, $tax, $totalAmount, $prePaidPackageDB);

        $prePaidWalletTxnService = new PrePaidWalletTxnService;
        $prePaidWalletTxn = $prePaidWalletTxnService->prePaidWalletTxnForPackage($prePaidWalletDB, "", TriggerType['PREPAID_PACKAGE'], Payment::TransactionType['CREDIT'], $days, $days + $prePaidPackageDB->days, $prePaidPackageDB, $prepaidWalletTxnPricing);

        $prePaidWalletTxnPricingService->setTransaction($prepaidWalletTxnPricing, $prePaidWalletTxn->id);
        $this->updatePrepaidWallet($prePaidWalletDB, $prePaidWalletTxn->version, $prePaidWalletTxn->newBalance, $prePaidPackageDB->expirationDurationInMonths);

        return $prePaidWalletDB;
    }

    public function updatePrepaidWallet($prePaidWalletDB, $version, $newBalance, $expirationDurationInMonths)
    {
        $prePaidWalletDB->version = $version + 1;
        $prePaidWalletDB->days = $newBalance;
        $prePaidWalletDB->expirationDate = date("Y-m-d H:i:s A", strtotime(Carbon::now()->addMonths($expirationDurationInMonths)));
        $prePaidWalletDB->save();
    }
}
