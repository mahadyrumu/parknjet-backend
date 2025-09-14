<?php

namespace App\Services\Sync;

use App\Models\Backend\MemReservationPendingSync;
use Illuminate\Support\Facades\Log;

class MemberReservationPendingSyncService
{
    public function findByReservation($reservation_id)
    {
        Log::info("Searching failed Sync Member Reservation ID : " . $reservation_id);
        return MemReservationPendingSync::where('reservation_id', $reservation_id)
            ->first();
    }

    public function create($reservation_id)
    {
        $reservation = $this->findByReservation($reservation_id);
        if (isset($reservation->id)) {
            return $reservation;
        }
        Log::info("Creating Pending Sync for failed Sync Member Reservation ID : " . $reservation_id);
        $memReservationPendingSync = new MemReservationPendingSync;
        $memReservationPendingSync->version = 0;
        $memReservationPendingSync->reservation_id = $reservation_id;
        $memReservationPendingSync->save();
        return $memReservationPendingSync;
    }

}