<?php

namespace App\Services\Wallet;

use App\Models\Backend\MemWalletPrepaidTxn;
use App\Traits\SequenceUpdate;

class PrePaidWalletTxnService
{
    use SequenceUpdate;

    public function prePaidWalletTxnForReservation($prePaidWallet, $comment, $triggerType, $transactionType, $oldBalance, $newBalance, $reservation_id)
    {
        $memWalletPrepaidTxn = new MemWalletPrepaidTxn;
        $memWalletPrepaidTxn->description = "Transaction for reservation";
        $memWalletPrepaidTxn->newBalance = $newBalance;
        $memWalletPrepaidTxn->oldBalance = $oldBalance;
        $memWalletPrepaidTxn->transactionType = $transactionType;
        $memWalletPrepaidTxn->triggerType = $triggerType;
        $memWalletPrepaidTxn->comment = $comment;
        $memWalletPrepaidTxn->prePaidWallet_id = $prePaidWallet->id;
        $memWalletPrepaidTxn->createdBy_id = $prePaidWallet->owner_id;
        $memWalletPrepaidTxn->reservation_id = $reservation_id;
        $memWalletPrepaidTxn->version = 0;
        $memWalletPrepaidTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_prepaid_txn', 'PrePaidWalletTxn_SEQ');
        return $memWalletPrepaidTxn;
    }
    public function prePaidWalletTxnForPackage($prePaidWallet, $comment, $triggerType, $transactionType, $oldBalance, $newBalance, $prePaidPackage, $pricing)
    {
        $memWalletPrepaidTxn = new MemWalletPrepaidTxn;
        $memWalletPrepaidTxn->description = "Transaction for Package";
        $memWalletPrepaidTxn->newBalance = $newBalance;
        $memWalletPrepaidTxn->oldBalance = $oldBalance;
        $memWalletPrepaidTxn->transactionType = $transactionType;
        $memWalletPrepaidTxn->triggerType = $triggerType;
        $memWalletPrepaidTxn->comment = $comment;
        $memWalletPrepaidTxn->prePaidWallet_id = $prePaidWallet->id;
        $memWalletPrepaidTxn->createdBy_id = $prePaidWallet->owner_id;
        $memWalletPrepaidTxn->prePaidPackage_id = $prePaidPackage->id;
        $memWalletPrepaidTxn->packagePricing_id = $pricing->id;
        $memWalletPrepaidTxn->version = 0;
        $memWalletPrepaidTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_prepaid_txn', 'PrePaidWalletTxn_SEQ');
        return $memWalletPrepaidTxn;
    }

    public function prePaidWalletTxn($prePaidWallet, $comment, $triggerType, $transactionType, $oldBalance, $newBalance)
    {
        $memWalletPrepaidTxn = new MemWalletPrepaidTxn;
        $memWalletPrepaidTxn->description = "Transaction by admin";
        $memWalletPrepaidTxn->newBalance = $newBalance;
        $memWalletPrepaidTxn->oldBalance = $oldBalance;
        $memWalletPrepaidTxn->transactionType = $transactionType;
        $memWalletPrepaidTxn->triggerType = $triggerType;
        $memWalletPrepaidTxn->comment = $comment;
        $memWalletPrepaidTxn->prePaidWallet_id = $prePaidWallet->id;
        $memWalletPrepaidTxn->createdBy_id = $prePaidWallet->owner_id;
        $memWalletPrepaidTxn->reservation_id = null;
        $memWalletPrepaidTxn->version = 0;
        $memWalletPrepaidTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_prepaid_txn', 'PrePaidWalletTxn_SEQ');
        return $memWalletPrepaidTxn;
    }
}
