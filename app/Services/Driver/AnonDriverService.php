<?php

namespace App\Services\Driver;

use App\Models\Backend\AnonDriver;
use App\Traits\SequenceUpdate;

class AnonDriverService
{
    use SequenceUpdate;

    public function getDriver($user_id)
    {
        return AnonDriver::Where('owner_id', $user_id);
    }

    public function createDriver($request)
    {
        $anonDriver = new AnonDriver;
        $anonDriver->full_name = $request->full_name;
        $anonDriver->email = $request->email;
        $anonDriver->phone = $request->phone;
        $anonDriver->version = 0;
        $anonDriver->save();
        $this->updateSequence('backend_mysql', 'anon_driver', 'AnonDriver_SEQ');
        return $anonDriver;
    }

    public function updateDriver($request, $anonDriver)
    {
        $anonDriver->full_name = $request->full_name;
        $anonDriver->email = $request->email;
        $anonDriver->phone = $request->phone;
        $anonDriver->version = 0;
        $anonDriver->update();
        return $anonDriver;
    }

    public function deleteDriver($user_id, $anonDriver)
    {
        $anonDriver->isDeleted         = 1;
        $anonDriver->lastModifiedBy_id = $user_id;
        $anonDriver->email             = $anonDriver->email . '_' . time();
        $anonDriver->update();
        return $anonDriver;
    }
}
