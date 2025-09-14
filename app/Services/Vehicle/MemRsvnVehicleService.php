<?php

namespace App\Services\Vehicle;

use App\Models\Backend\MemReservationVehicle;
use App\Traits\SequenceUpdate;

class MemRsvnVehicleService
{
    use SequenceUpdate;

    public function getVehicle($user_id)
    {
        return MemReservationVehicle::Where('owner_id', $user_id);
    }

    public function createMemRsvnVehicle($request)
    {
        $memReservationVehicle = new MemReservationVehicle;
        $memReservationVehicle->makeModel          = $request->makeModel;
        $memReservationVehicle->plate              = $request->plate;
        $memReservationVehicle->vehicleLength      = $request->vehicleLength;
        $memReservationVehicle->version            = 0;
        $memReservationVehicle->createdBy_id       = $request->createdBy_id ? $request->createdBy_id : $request->owner_id;
        $memReservationVehicle->save();
        $this->updateSequence('backend_mysql', 'mem_reservation_vehicle', 'ReservationVehicle_SEQ');
        return $memReservationVehicle;
    }

    public function updateVehicle($user_id, $request, $memReservationVehicle)
    {
        $memReservationVehicle->makeModel          = $request->makeModel;
        $memReservationVehicle->plate              = $request->plate;
        $memReservationVehicle->vehicleLength      = $request->vehicleLength;
        $memReservationVehicle->version            = 0;
        $memReservationVehicle->createdBy_id       = $user_id;
        $memReservationVehicle->lastModifiedBy_id  = $user_id;
        $memReservationVehicle->update();
        return $memReservationVehicle;
    }

    public function deleteVehicle($user_id, $memReservationVehicle)
    {
        $memReservationVehicle->isDeleted          = 1;
        $memReservationVehicle->lastModifiedBy_id  = $user_id;
        $memReservationVehicle->plate              = $memReservationVehicle->plate . '_' . time();
        $memReservationVehicle->update();
        return $memReservationVehicle;
    }
}
