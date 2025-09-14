<?php

namespace App\Services\BlackOut;

use App\Exceptions\PNJException;
use App\Models\Backend\Admin\Blackout;
use App\Services\Reservation\ReservationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlackOutService
{
    public function getBlackout()
    {
        return Blackout::where('isDeleted', 0);
    }

    public function checkForBlackOut($lotType, $dropOffTime, $pickUpTime, $parkingPreference)
    {
        $this->checkIfDropOffBlackOut($lotType, $dropOffTime, $parkingPreference);
        $this->checkIfPickUpBlackOut($lotType, $pickUpTime, $parkingPreference);
        $this->checkIfMinStayBlackOut($lotType, $dropOffTime, $pickUpTime, $parkingPreference);
        $this->checkIfStayBlackOut($lotType, $dropOffTime, $pickUpTime, $parkingPreference);
        Log::info("No black out found");
    }

    public function isDateBlackOut($lotType, $parkingPreference, $rsvnDate, $blackOutType)
    {
        return $this->getBlackout()
            ->where('lotType', $lotType)
            ->where('blackOutType', $blackOutType)
            ->where('parkingPreference', $parkingPreference)
            ->whereDate('startDate', '<=', $rsvnDate)
            ->whereDate('endDate', '>=', $rsvnDate);
    }

    public function areDateOverlappingBlackOut($dropOffTime, $pickUpTime, $lotType, $parkingPreference, $blackOutType)
    {
        return $this->getBlackout()
            ->where('lotType', $lotType)
            ->where('blackOutType', $blackOutType)
            ->where('parkingPreference', $parkingPreference)
            // ->whereDate('startDate', '<=', $dropOffTime)
            // ->whereDate('endDate', '>=', $pickUpTime)
            ->where(function ($query) use ($dropOffTime, $pickUpTime) {
                $query->whereBetween('startDate', [$dropOffTime, $pickUpTime])
                      ->orWhereBetween('endDate', [$dropOffTime, $pickUpTime])
                      ->orWhere(function ($q) use ($dropOffTime, $pickUpTime) {
                          $q->where('startDate', '<', $dropOffTime)
                            ->where('endDate', '>', $pickUpTime);
                      });
            });
    }

    public function commonMinBlackOut($blackOutLists, $durationInDay, $blackOutType)
    {
        Log::info("MIN STAY " . $blackOutType . " Black out list Size = " . count($blackOutLists));

        if (count($blackOutLists) > 0) {

            foreach ($blackOutLists as $key => $blackOutList) {
                if ($durationInDay < $blackOutList->minDays) {
                    Log::info("MIN STAY " . $blackOutType . " Blackout found with id " . $blackOutList->id . ", MIN stay required is " . $blackOutList->minDays . " but found duration " . $durationInDay);
                    throw new PNJException("MIN Stay " . $blackOutList->minDays . " needed for Reservations which includes dates between " . $blackOutList->startDate . " and " . $blackOutList->endDate);
                }
            }
        }
    }

    public function checkIfMinStayBlackOut($lotType, $dropOffTime, $pickUpTime, $parkingPreference)
    {
        Log::info("Checking for MIN STAY blackout for dropOff date = " . $dropOffTime . ", lotType =  " . $lotType . ", parkingPref = " . $parkingPreference . " , BOType = DROP_OFF");

        $reservationService = new ReservationService;
        $durationInDay = $reservationService->getDurationInDay($dropOffTime, $pickUpTime);

        $blackOutLists = $this->isDateBlackOut($lotType, $parkingPreference, $dropOffTime, "MINIMUM_STAY")->get();
        $this->commonMinBlackOut($blackOutLists, $durationInDay, "DROP_OFF");

        $blackOutLists = $this->isDateBlackOut($lotType, $parkingPreference, $pickUpTime, "MINIMUM_STAY")->get();
        $this->commonMinBlackOut($blackOutLists, $durationInDay, "PICK_UP");

        $blackOutLists = $this->areDateOverlappingBlackOut($dropOffTime, $pickUpTime, $lotType, $parkingPreference, "MINIMUM_STAY")->get();
        $this->commonMinBlackOut($blackOutLists, $durationInDay, "OVERLAP");

        Log::info("NO MIN STAY Black Out found");
    }

    public function commonStayBlackOut($blackOutLists, $blackOutType)
    {
        Log::info("STAY Black out list Size = " . count($blackOutLists));

        if ($blackOutLists != null && count($blackOutLists) > 0) {
            $blackOutlist = $blackOutLists->first();

            Log::info("STAY Blackout found with id " . $blackOutlist->id . " because of " . $blackOutType);
            throw new PNJException("No Reservations allowed which includes dates between " . $blackOutlist->startDate . " and " . $blackOutlist->endDate);
        }
    }

    public function checkIfStayBlackOut($lotType, $dropOffTime, $pickUpTime, $parkingPreference)
    {
        Log::info("Checking for STAY blackout for dropOff date = " . $dropOffTime . ", lotType =  " . $lotType . ", parkingPref = " . $parkingPreference . " , BOType = DROP_OFF");

        $blackOutLists = $this->isDateBlackOut($lotType, $parkingPreference, $dropOffTime, "STAY")->get();
        $this->commonStayBlackOut($blackOutLists, "DROP_OFF_TIME");

        $blackOutLists = $this->isDateBlackOut($lotType, $parkingPreference, $pickUpTime, "STAY")->get();
        $this->commonStayBlackOut($blackOutLists, "PICK_UP_TIME");

        $blackOutLists = $this->areDateOverlappingBlackOut($dropOffTime, $pickUpTime, $lotType, $parkingPreference, "STAY")->get();
        $this->commonStayBlackOut($blackOutLists, "OVERLAPPING Dates");

        Log::info("NO STAY Black Out found ");
    }

    public function commonBlackOut($blackOutLists, $blackOutType)
    {
        Log::info("Black out list Size = " . count($blackOutLists));

        if ($blackOutLists != null && count($blackOutLists) > 0) {
            $blackOutlist = $blackOutLists->first();

            Log::info($blackOutType . " Blackout found with id = " . $blackOutlist->id);
            throw new PNJException("No " . strtolower(str_replace('_', ' ', $blackOutType)) . " are allowed between " . $blackOutlist->startDate . " and " . $blackOutlist->endDate);
        }
    }

    public function checkIfDropOffBlackOut($lotType, $dropOffTime, $parkingPreference)
    {
        Log::info("Checking for DROP_OFF blackout for dropOff date = " . $dropOffTime . ", lotType =  " . $lotType . ", parkingPref = " . $parkingPreference . " , BOType = DROP_OFF");

        $blackOutLists = $this->isDateBlackOut($lotType, $parkingPreference, $dropOffTime, "DROP_OFF")->get();
        Log::info("commonBlackOut 1");
        $this->commonBlackOut($blackOutLists, "DROP_OFF");

        Log::info("NO DROP_OFF Black Out found ");
    }

    public function checkIfPickUpBlackOut($lotType, $pickUpTime, $parkingPreference)
    {
        Log::info("Checking for PICK_UP blackout for dropOff date = " . $pickUpTime . ", lotType =  " . $lotType . ", parkingPref = " . $parkingPreference . " , BOType = PICK_UP");

        $blackOutLists = $this->isDateBlackOut($lotType, $parkingPreference, $pickUpTime, "PICK_UP")->get();
        Log::info("commonBlackOut 2");
        $this->commonBlackOut($blackOutLists, "PICK_UP");

        Log::info("NO PICK_UP Black Out found ");
    }
}
