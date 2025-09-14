<?php

namespace App\DTO\Point;

class pointDTO
{
    public function prepaidDays($userDetails)
    {
        return [
            'lot1PrepaidDays' => $userDetails->walletLotOnePrepaid ?? [],
            'lot2PrepaidDays' => $userDetails->walletLotTwoPrepaid ?? [],
        ];
    }

    public function pointsAndDays($userDetails)
    {
        return [
            'lot1Wallet' => $userDetails->walletLotOne->walletTxn ?? [],
            'lot2Wallet' => $userDetails->walletLotTwo->walletTxn ?? [],
            'lot1Reward' => $userDetails->rewardLotOne->rewardTxn ?? [],
            'lot2Reward' => $userDetails->rewardLotTwo->rewardTxn ?? [],
            'lot1Prepaid' => $userDetails->walletLotOnePrepaid->walletPrepaidTxn ?? [],
            'lot2Prepaid' => $userDetails->walletLotTwoPrepaid->walletPrepaidTxn ?? [],
        ];
    }
}
