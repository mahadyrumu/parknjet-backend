<?php

namespace App\Services\Reservation;

use App\Models\Backend\AnonReservation;
use App\Services\Pricing\AnonPricingService;
use Status;

class AnonReservationService
{
    public $owner, $vehicle, $driver, $pricingList;

    public function anonReservation($reservationDTO, $pricing, $driver, $vehicle)
    {
        $anonReservation = new AnonReservation;
        $anonReservation->confirmationCode = rand(10000, 100000);
        $anonReservation->dropOffTime = date("Y-m-d H:i:s", strtotime($reservationDTO->dropOffTime));
        $anonReservation->isDeleted = 0;
        $anonReservation->lotType = $reservationDTO->lotType;
        $anonReservation->paidTillTime = date("Y-m-d H:i:s", strtotime($reservationDTO->pickUpTime));
        $anonReservation->parkingPreference = $reservationDTO->parkingPreference;
        $anonReservation->paxCount = $reservationDTO->paxCount;
        $anonReservation->pickUpTime = date("Y-m-d H:i:s", strtotime($reservationDTO->pickUpTime));
        $anonReservation->returnAirline = $reservationDTO->returnAirline;
        $anonReservation->returnFlightNo = '';
        $anonReservation->status = isset($reservationDTO->reservationId) ? Status::ReservationStatus['EXTENDED'] : Status::ReservationStatus['NEW'];
        $anonReservation->version = 0;
        $anonReservation->driver_id = $driver->id;
        $anonReservation->vehicle_id = $vehicle->id;
        $anonReservation->reservation_id = isset($reservationDTO->reservationId) ?? null;
        $anonReservation->save();

        $anonPricingService = new AnonPricingService;
        $anonReservation->pricingList = $anonPricingService->createAnonPricing($pricing, $anonReservation->id);

        return $anonReservation;
    }
}
