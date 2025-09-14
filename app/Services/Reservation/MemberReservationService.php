<?php

namespace App\Services\Reservation;
use App\Models\Backend\MemReservation;
use App\Services\Pricing\MemberPricingService;
use Status;

class MemberReservationService
{
    public $owner, $vehicle, $driver, $pricingList;
    public function memberReservation($reservationDTO, $driver, $vehicle, $pricing, $currentLoggedInUser)
    {
        $memReservation = new MemReservation;

        $memReservation->confirmationCode = rand(10000, 100000);
        $memReservation->dropOffTime = date("Y-m-d H:i:s", strtotime($reservationDTO->dropOffTime));
        $memReservation->isDeleted = 0;
        $memReservation->lotType = $reservationDTO->lotType;
        $memReservation->paidTillTime = date("Y-m-d H:i:s", strtotime($reservationDTO->pickUpTime));
        $memReservation->parkingPreference = $reservationDTO->parkingPreference;
        $memReservation->paxCount = $reservationDTO->paxCount;
        $memReservation->pickUpTime = date("Y-m-d H:i:s", strtotime($reservationDTO->pickUpTime));
        $memReservation->returnAirline = $reservationDTO->returnAirline;
        $memReservation->returnFlightNo = '';
        $memReservation->status = isset($reservationDTO->reservationId) ? Status::ReservationStatus['EXTENDED'] : Status::ReservationStatus['NEW'];
        $memReservation->version = 0;
        $memReservation->createdDate = date("Y-m-d H:i:s");
        $memReservation->createdBy_id = $currentLoggedInUser;
        $memReservation->driver_id = $driver->id;
        $memReservation->owner_id = $currentLoggedInUser;
        $memReservation->vehicle_id = $vehicle->id;
        $memReservation->reservation_id = isset($reservationDTO->reservationId) ?? null;
        $memReservation->save();

        $memberPricingService = new MemberPricingService;
        $memReservation->pricingList = $memberPricingService->createMemPricing($pricing, $currentLoggedInUser, $memReservation->id);
        
        return $memReservation;
    }
}
