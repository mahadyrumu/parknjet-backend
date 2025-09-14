<?php

namespace App\Services\Point;

use App\Models\Backend\MemUser;

class PointService
{
    public function getPrepaidDays()
    {
        return MemUser::with([
            'walletLotOnePrepaid',
            'walletLotTwoPrepaid',
        ]);
    }

    public function getPointsAndDays()
    {
        return MemUser::with([
            'walletLotOne.walletTxn' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'walletLotTwo.walletTxn' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'rewardLotOne.rewardTxn' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'rewardLotTwo.rewardTxn' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'walletLotOnePrepaid.walletPrepaidTxn' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'walletLotTwoPrepaid.walletPrepaidTxn' => function ($query) {
                $query->orderBy('id', 'desc');
            },
        ]);
    }
}
