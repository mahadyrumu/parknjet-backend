<?php

namespace App\Services\Vehicle;

use App\Models\Backend\MemVehicle;
use App\Traits\SequenceUpdate;

class MemVehicleService
{
    use SequenceUpdate;

    public function getVehicles($user_id)
    {
        return MemVehicle::Where('owner_id', $user_id);
    }

    public function getVehicle($user_id, $id)
    {
        return MemVehicle::Where('owner_id', $user_id)->where('id', $id);
    }

    public function createVehicle($user_id, $makeModel, $plate, $vehicleLength)
    {
        $memVehicle = new MemVehicle;
        $memVehicle->makeModel          = $makeModel;
        $memVehicle->plate              = $plate;
        $memVehicle->vehicleLength      = $vehicleLength;
        $memVehicle->version            = 0;
        $memVehicle->owner_id           = $user_id;
        $memVehicle->createdBy_id       = $user_id;
        $memVehicle->save();
        $this->updateSequence('backend_mysql', 'mem_vehicle', 'MemberVehicle_SEQ');
        return $memVehicle;
    }

    public function updateVehicle($user_id, $makeModel, $plate, $vehicleLength, $memVehicle)
    {
        $memVehicle->makeModel          = $makeModel;
        $memVehicle->plate              = $plate;
        $memVehicle->vehicleLength      = $vehicleLength;
        $memVehicle->lastModifiedBy_id  = $user_id;
        $memVehicle->update();
        return $memVehicle;
    }

    public function deleteVehicle($user_id, $memVehicle)
    {
        $memVehicle->isDeleted          = 1;
        $memVehicle->lastModifiedBy_id  = $user_id;
        $memVehicle->plate              = $memVehicle->plate . '_' . time();
        $memVehicle->update();
        return $memVehicle;
    }
}
