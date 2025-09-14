<?php

namespace App\Services\User;

use App\Models\Backend\MemUser;
use App\Traits\GeneratePassword;

class UserService
{
    use GeneratePassword;

    public function getUsers()
    {
        return MemUser::orderBy('id', 'asc');
    }

    public function getUsersWithAll()
    {
        return MemUser::with([
            'walletLotOnePrepaid',
            'walletLotTwoPrepaid',
            'RewardLotOne',
            'RewardLotTwo',
            'walletLotOne',
            'walletLotTwo'
        ]);
    }

    public function getUser($id)
    {
        return MemUser::where('id', $id)
            ->first();
    }

    public function updateUser($fullName, $email, $phone, $user)
    {
        if ($user->user_name != $email) {
            $user->isVerified = 0;
            $user->email_verified_at = null;
        }
        $user->full_name  = $fullName;
        $user->user_name  = $email;
        $user->phone      = $phone;
        $user->update();
        return $user;
    }

    public function updatePassword($user, $newPassword)
    {
        $user->password = $this->encode($newPassword);
        $user->update();
    }

    public function setPrePaidWalletLot1($user, $prePaidWallet_id)
    {
        $user->prePaidWalletLot1_id = $prePaidWallet_id;
        $user->update();
    }

    public function setPrePaidWalletLot2($user, $prePaidWallet_id)
    {
        $user->prePaidWalletLot2_id = $prePaidWallet_id;
        $user->update();
    }

    public function getUserWithWallet($userId)
    {
        return MemUser::with('prepaidWallet')
            ->where('id', $userId)
            ->first();
    }
}
