<?php

namespace App\Services\Sync;
use App\Models\Backend\AnonReservationPendingSync;
use Illuminate\Support\Facades\Log;

class AnonReservationPendingSyncService
{
    public function findByReservation($reservation_id)
    {
        Log::info("Searching failed Sync Anon Reservation ID : " . $reservation_id);
        return AnonReservationPendingSync::where('reservation_id', $reservation_id)
            ->first();
    }

    public function create($reservation_id)
    {
        $reservation = $this->findByReservation($reservation_id);
        if (!isset($reservation->id)) {
            return $reservation;
        }
        Log::info("Creating Pending Sync for failed Sync Anon Reservation ID : " . $reservation_id);
        $anonReservationPendingSync = new AnonReservationPendingSync;
        $anonReservationPendingSync->version = 0;
        $anonReservationPendingSync->reservation_id = $reservation_id;
        $anonReservationPendingSync->save();
        return $anonReservationPendingSync;
    }

}