<?php

namespace App\Services\Reward;

use App\Models\Backend\MemRewardTxn;
use App\Traits\SequenceUpdate;
use Illuminate\Support\Facades\Log;

class RewardTxnService
{
    use SequenceUpdate;

    public function rewardTransactionForReservation($userReward, $triggerType, $transactionType, $oldBalance, $newBalance, $reservation_id)
    {
        $memRewardTxn = new MemRewardTxn();
        $memRewardTxn->description = "Transaction for reservation";
        $memRewardTxn->newBalance = $newBalance;
        $memRewardTxn->oldBalance = $oldBalance;
        $memRewardTxn->transactionType = $transactionType;
        $memRewardTxn->triggerType = $triggerType;
        $memRewardTxn->createdBy_id = $userReward->owner_id;
        $memRewardTxn->reservation_id = $reservation_id;
        $memRewardTxn->comment = "Transaction for reservation";
        $memRewardTxn->reward_id = $userReward->id;
        $memRewardTxn->version = 0;
        $memRewardTxn->save();
        $this->updateSequence('backend_mysql', 'mem_reward_txn', 'RewardTransaction_SEQ');
        Log::info("Adding Reward Transaction for Reservation");
        return $memRewardTxn;
    }

}
