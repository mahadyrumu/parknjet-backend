<?php

namespace App\Services\Wallet;

use App\Models\Backend\MemWalletPrepaidTxnPricing;
use App\Traits\SequenceUpdate;

class PrePaidWalletTxnPricingService
{
    use SequenceUpdate;
    
    public function createPrepaidWalletTxnPricing($price, $tax, $totalAmount, $prePaidWallet)
    {
        $memWalletPrepaidTxn = new MemWalletPrepaidTxnPricing();
        $memWalletPrepaidTxn->amount = $price;
        $memWalletPrepaidTxn->tax = $tax;
        $memWalletPrepaidTxn->totalAmount = $totalAmount;
        $memWalletPrepaidTxn->createdBy_id = $prePaidWallet->owner_id;
        // $memWalletPrepaidTxn->transaction_id = $reservation_id;
        $memWalletPrepaidTxn->version = 1;
        $memWalletPrepaidTxn->save();
        $this->updateSequence('backend_mysql', 'mem_wallet_prepaid_txn_pricing', 'PrepaidWalletTxnPricing_SEQ');
        return $memWalletPrepaidTxn;
    }

    public function setTransaction($memWalletPrepaidTxn, $transaction_id)
    {
        $memWalletPrepaidTxn->transaction_id = $transaction_id;
        $memWalletPrepaidTxn->save();
    }
}
