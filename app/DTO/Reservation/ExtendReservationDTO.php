<?php

namespace App\DTO\Reservation;

use stdClass;

class ExtendReservationDTO
{
    public function ExtendReservationData($reservation, $request)
    {
        $vehicle = new stdClass;
        $vehicle->vehicleId = $request->vehicleId;
        $vehicle->makeModel = $reservation->vehicle->makeModel;
        $vehicle->plate = $reservation->vehicle->plate;
        $vehicle->vehicleLength = $reservation->vehicle->vehicleLength;

        $driver = new stdClass;
        $driver->driverId = $request->driverId;
        $driver->email = $reservation->driver->email;
        $driver->full_name = $reservation->driver->full_name;
        $driver->phone = $reservation->driver->phone;

        $payment = new stdClass;
        $payment->stripeToken = $request->stripeToken;
        $payment->cardId = $request->cardId;
        $payment->paymentId = $request->paymentId;

        return (object) [
            "reservationId" => $reservation->id,
            "lotType" => $reservation->lotType,
            "parkingPreference" => $reservation->parkingPreference,
            "paxCount" => $reservation->paxCount,
            "couponCode" => $request->coupon,
            "returnAirline" => $reservation->returnAirline,
            "returnFlightNo" => "",
            "dropOffTime" => $reservation->pickUpTime,
            "pickUpTime" => $request->puDate . " " . $request->puTime,
            "pricing" => $request->pricing,
            "isRegular" => false,
            "vehicle" => $vehicle,
            "driver" => $driver,
            "payment" => $payment,
        ];
    }
}
