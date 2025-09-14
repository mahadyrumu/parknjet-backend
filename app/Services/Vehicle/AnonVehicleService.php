<?php

namespace App\Services\Vehicle;

use App\Models\Backend\AnonVehicle;
use App\Traits\SequenceUpdate;

class AnonVehicleService
{
    use SequenceUpdate;

    public function getVehicle($email)
    {
        return AnonVehicle::Where('email', $email);
    }

    public function createVehicle($request)
    {
        $anonVehicle = new AnonVehicle;
        $anonVehicle->makeModel          = $request->makeModel;
        $anonVehicle->plate              = $request->plate;
        $anonVehicle->vehicleLength      = $request->vehicleLength;
        $anonVehicle->version            = 0;
        $anonVehicle->save();
        $this->updateSequence('backend_mysql', 'anon_vehicle', 'AnonVehicle_SEQ');
        return $anonVehicle;
    }

    public function updateVehicle($request, $anonVehicle)
    {
        $anonVehicle->makeModel          = $request->makeModel;
        $anonVehicle->plate              = $request->plate;
        $anonVehicle->vehicleLength      = $request->vehicleLength;
        $anonVehicle->version            = 0;
        $anonVehicle->update();
        return $anonVehicle;
    }

    public function deleteVehicle($user_id, $anonVehicle)
    {
        $anonVehicle->isDeleted          = 1;
        $anonVehicle->lastModifiedBy_id  = $user_id;
        $anonVehicle->plate              = $anonVehicle->plate . '_' . time();
        $anonVehicle->update();
        return $anonVehicle;
    }
}
