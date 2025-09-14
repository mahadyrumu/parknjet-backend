<?php

namespace App\Services\Referral;

use App\Models\Backend\MemReferral;
use App\Services\Wallet\WalletService;
use App\Traits\SequenceUpdate;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    use SequenceUpdate;

    public function getReferral()
    {
        return MemReferral::orderBy('id', 'desc');
    }

    public function getReferralBy($owner_id)
    {
        return MemReferral::where('referredBy_id', $owner_id);
    }

    public function createReferral($owner_id, $email)
    {
        $referral = new MemReferral();
        $referral->isDeleted = 0;
        $referral->version = 0;
        $referral->referredUserName = $email;
        $referral->referredBy_id = $owner_id;
        $referral->save();
        $this->updateSequence('backend_mysql', 'mem_referral', 'MemberReferral_SEQ');
        return $referral;
    }

    public function checkedOutReservation($user, $memberReservationDB)
    {
        Log::info("Checking if user with PK " . $user->id . " was referred and if its first reservation !");
        $referralByUserName = $this->getReferral()
            ->where('referredUserName', $user->user_name)
            ->first();

        if ($referralByUserName != null) {
            Log::info("Member referral found with PK " . $referralByUserName->id . ", Setting its first reservation to id " . $memberReservationDB->id);
            $this->setFirstReservation($referralByUserName, $memberReservationDB);
            $walletService = new WalletService;
            $walletService->applyReferralBonus($referralByUserName, $memberReservationDB);
        } else {
            Log::info("No member referral found for userName / email " . $user->user_name . " with NULL first reservation");
        }
    }

    public function setFirstReservation($referral, $memberReservationDB)
    {
        $referral->firstReservation_id = $memberReservationDB->id;
    }
}
