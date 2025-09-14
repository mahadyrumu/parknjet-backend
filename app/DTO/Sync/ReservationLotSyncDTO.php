<?php

namespace App\DTO\Sync;

class ReservationLotSyncDTO
{
    public $memberUserDTO;

    public function from($memberReservation)
    {
        $owner = $memberReservation->owner;
        $this->memberUserDTO = [
            'id' => $owner->id,
            'fullName' => $owner->full_name,
            'userName' => $owner->user_name,
            'phone' => $owner->phone
        ];
        return [
            'memberReservation' => $memberReservation,
            'owner' => $this->memberUserDTO
        ];
    }
}
