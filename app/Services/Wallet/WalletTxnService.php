<?php

namespace App\Services\Wallet;

use App\Models\Backend\MemWalletTxn;
use App\Traits\SequenceUpdate;
use Illuminate\Support\Facades\Log;

class WalletTxnService
{
    use SequenceUpdate;
    
    public function walletTransactionForWallet($userWallet, $comment, $triggerType, $transactionType, $oldBalance, $newBalance, $referral_id)
    {
        $memWalletTxn = new MemWalletTxn;
        $memWalletTxn->description = "Added 1 day for referral";
        $memWalletTxn->newBalance = $newBalance;
        $memWalletTxn->oldBalance = $oldBalance;
        $memWalletTxn->transactionType = $transactionType;
        $memWalletTxn->triggerType = $triggerType;
        $memWalletTxn->createdBy_id = $userWallet->owner_id;
        $memWalletTxn->referral_id = $referral_id;
        $memWalletTxn->comment = $comment;
        $memWalletTxn->flags = 0;
        $memWalletTxn->wallet_id = $userWallet->id;
        $memWalletTxn->version = 0;
        $memWalletTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_txn', 'WalletTransaction_SEQ');
        Log::info("Adding Wallet Transaction for Referral");
        return $memWalletTxn;
    }
    
    public function walletTransactionForReservation($userWallet, $comment, $triggerType, $transactionType, $oldBalance, $newBalance, $reservation_id)
    {
        $memWalletTxn = new MemWalletTxn;
        $memWalletTxn->description = "Transaction for reservation";
        $memWalletTxn->newBalance = $newBalance;
        $memWalletTxn->oldBalance = $oldBalance;
        $memWalletTxn->transactionType = $transactionType;
        $memWalletTxn->triggerType = $triggerType;
        $memWalletTxn->createdBy_id = $userWallet->owner_id;
        $memWalletTxn->reservation_id = $reservation_id;
        $memWalletTxn->comment = $comment;
        $memWalletTxn->flags = 0;
        $memWalletTxn->wallet_id = $userWallet->id;
        $memWalletTxn->version = 0;
        $memWalletTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_txn', 'WalletTransaction_SEQ');
        Log::info("Adding Wallet Transaction for Reservation");
        return $memWalletTxn;
    }

    public function walletTransactionForUser($userWallet, $comment, $triggerType, $transactionType, $oldBalance, $newBalance)
    {
        $memWalletTxn = new MemWalletTxn;
        $memWalletTxn->description = "Transaction by admin";
        $memWalletTxn->newBalance = $newBalance;
        $memWalletTxn->oldBalance = $oldBalance;
        $memWalletTxn->transactionType = $transactionType;
        $memWalletTxn->triggerType = $triggerType;
        $memWalletTxn->createdBy_id = $userWallet->owner_id;
        $memWalletTxn->reservation_id = null;
        $memWalletTxn->comment = $comment;
        $memWalletTxn->flags = 0;
        $memWalletTxn->wallet_id = $userWallet->id;
        $memWalletTxn->version = 0;
        $memWalletTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_txn', 'WalletTransaction_SEQ');
        Log::info("Adding Wallet Transaction");
        return $memWalletTxn;
    }
}