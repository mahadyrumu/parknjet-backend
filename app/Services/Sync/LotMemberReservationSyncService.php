<?php

namespace App\Services\Sync;

use App\DTO\Sync\ReservationLotSyncDTO;
use App\Jobs\LotSyncJob;
use Illuminate\Support\Facades\Log;
use stdClass;

class LotMemberReservationSyncService
{
    public function syncReservationToLot($reservation, $user)
    {
        $lotSyncURL = null;
        $rsvn = new stdClass;
        $reservation = removeCreatedAtAndUpdatedAt($reservation);

        if ($user) {
            $reservationType = "MemberReservation";
            $lot1MemberReservationSyncURL = env('LOT1_MEMBER_RESERVATION_SYNC_URL');
            $lot2MemberReservationSyncURL = env('LOT2_MEMBER_RESERVATION_SYNC_URL');

            if ($reservation->lotType == LotType['LOT_1']) {
                $lotSyncURL = $lot1MemberReservationSyncURL;
            } else if ($reservation->lotType == LotType['LOT_2']) {
                $lotSyncURL = $lot2MemberReservationSyncURL;
            }

            $reservationLotSyncDTO = new ReservationLotSyncDTO;
            $reservationLotSyncDTO->from($reservation);
            Log::info("Member Reservation lot map sync url = " . $lotSyncURL);
            $reservationPendingSyncService = new MemberReservationPendingSyncService;
            $rsvn->memberReservation = $reservation;
            $rsvn->owner = $reservation->owner;
        } else {
            $reservationType = "AnonReservation";
            $lot1AnonReservationSyncURL = env('LOT1_ANON_RESERVATION_SYNC_URL');
            $lot2AnonReservationSyncURL = env('LOT2_ANON_RESERVATION_SYNC_URL');

            if ($reservation->lotType == LotType['LOT_1']) {
                $lotSyncURL = $lot1AnonReservationSyncURL;
            } else if ($reservation->lotType == LotType['LOT_2']) {
                $lotSyncURL = $lot2AnonReservationSyncURL;
            }

            Log::info("Anon Reservation lot map sync url = " . $lotSyncURL);
            Log::info("Making Request to sync reservation id " . $reservation->id);
            $reservationPendingSyncService = new AnonReservationPendingSyncService;
            $rsvn = $reservation->toArray();
        }

        //Job here
        LotSyncJob::dispatch($lotSyncURL, $rsvn, $reservation, $reservationPendingSyncService, $reservationType);
    }
}