<?php

namespace App\DTO\Reservation;

use stdClass;

class ReservationDTO
{
    public function ReservationData($request)
    {
        $vehicle = new stdClass;
        $vehicle->vehicleId = $request->vehicleId;
        $vehicle->makeModel = $request->makeModel;
        $vehicle->plate = $request->licensePlate;
        $vehicle->vehicleLength = $request->vehicleLength;

        $driver = new stdClass;
        $driver->driverId = $request->driverId;
        $driver->email = $request->email;
        $driver->full_name = $request->fullName;
        $driver->phone = $request->phone;

        $payment = new stdClass;
        $payment->stripeToken = $request->stripeToken;
        $payment->cardId = $request->cardId;
        $payment->paymentId = $request->paymentId;

        return (object) [
            "lotType" => $request->lot,
            "parkingPreference" => $request->pref,
            "paxCount" => $request->paxCount,
            "couponCode" => $request->coupon,
            "returnAirline" => $request->returnFlight,
            "returnFlightNo" => "",
            "dropOffTime" => $request->doDate,
            "pickUpTime" => $request->puDate,
            "pricing" => $request->pricing,
            "isRegular" => $request->isRegular,
            "vehicle" => $vehicle,
            "driver" => $driver,
            "payment" => $payment,
        ];
    }
}
