<?php

namespace App\Services\Reward;

use App\Exceptions\PNJException;
use App\Models\Backend\MemReward;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\Log;

class RewardService
{
    public function subtractPoints($points, $triggerType, $reservation)
    {
        Log::info("Looking reward for user PK " . $reservation->owner->id);
        $userReward = null;

        if ($reservation->lotType == LotType['LOT_1']) {
            $userReward = $reservation->owner->rewardLot1;
        } else {
            $userReward = $reservation->owner->reward;
        }

        if ($userReward == null) {
            throw new PNJException("User reward wallet could not be found!");
        }

        Log::info("User reward wallet found with PK " . $userReward->id);
        if ($points > $userReward->points) {
            throw new PNJException("User points are not enough for this transaction");
        }
        Log::info("User Reward current balance BEFORE transaction = " . $userReward->points);
        Log::info("Subtracting " . $points . " points from users rewards");
        $rewardTxnService = new RewardTxnService;
        $rewardTxn = $rewardTxnService->rewardTransactionForReservation(
            $userReward,
            $triggerType,
            TransactionType['DEBIT'],
            $userReward->points,
            $userReward->points - $points,
            $reservation->id
        );
        $userReward->days = $userReward->points - $points;
        $userReward->save();
        Log::info("User Reward current balance AFTER transaction = " . $userReward->points);
        return $rewardTxn;
    }

    public function addPoints($points, $triggerType, $comment, $reservation)
    {
        Log::info("Looking reward for user PK " . $reservation->owner->id);

        $userReward = null;
        if ($reservation->lotType == LotType['LOT_1']) {
            $userReward = $reservation->owner->rewardLot1;
        } else {
            $userReward = $reservation->owner->reward;
        }

        if ($userReward == null) {

            Log::info("User does not have reward attached, creating one");
            $userReward = $this->createReward($reservation->owner);

            if ($reservation->lotType == LotType['LOT_1']) {
                $reservation->owner->rewardLot1_id = $userReward->id;
            } else {
                $reservation->owner->reward_id = $userReward->id;
            }
            $reservation->owner->save();
        }
        Log::info("User reward found with PK " . $userReward->id);

        $rewardTxnService = new RewardTxnService;
        $rewardTxn = $rewardTxnService->rewardTransactionForReservation(
            $userReward,
            $triggerType,
            TransactionType['DEBIT'],
            $userReward->points,
            $userReward->points + $points,
            $reservation->id
        );
        $userReward->points = $userReward->points + $points;
        $userReward->save();

        if ($userReward->points >= 1000) {
            $quotient = $userReward->points / 1000;
            $reminder = $userReward->points % 1000;
            Log::info("User reward quotient " . $quotient);
            Log::info("User reward reminder " . $reminder);

            $walletService = new WalletService;
            $walletService->addForReservation($quotient, TriggerType['MAX_POINTS_REACHED'], "", $reservation);

            $rewardTxn = $this->subtractPoints($userReward->points - $reminder, TriggerType['MAX_POINTS_REACHED'], $reservation);
        }
        return $rewardTxn;
    }

    public function createReward($user)
    {
        $mem_reward = new MemReward();
        $mem_reward->points = 0;
        $mem_reward->owner_id = $user->id;
        $mem_reward->createdBy_id = $user->id;
        $mem_reward->save();
        return $mem_reward;
    }
}
