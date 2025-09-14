<?php

namespace App\Services\Driver;

use App\Models\Backend\MemDriver;
use App\Traits\SequenceUpdate;

class MemDriverService
{
    use SequenceUpdate;

    public function getDrivers($user_id)
    {
        return MemDriver::Where('owner_id', $user_id);
    }

    public function getDriver($user_id, $id)
    {
        return MemDriver::Where('owner_id', $user_id)->where('id', $id);
    }

    public function createDriver($user_id, $full_name, $email, $phone)
    {
        $memDriver = new MemDriver;
        $memDriver->full_name          = $full_name;
        $memDriver->email              = $email;
        $memDriver->phone              = $phone;
        $memDriver->version            = 0;
        $memDriver->owner_id           = $user_id;
        $memDriver->createdBy_id       = $user_id;
        $memDriver->save();
        $this->updateSequence('backend_mysql', 'mem_driver', 'MemberDriver_SEQ');
        return $memDriver;
    }

    public function updateDriver($user_id, $full_name, $email, $phone, $memDriver)
    {
        $memDriver->full_name          = $full_name;
        $memDriver->email              = $email;
        $memDriver->phone              = $phone;
        $memDriver->lastModifiedBy_id  = $user_id;
        $memDriver->update();
        return $memDriver;
    }

    public function deleteDriver($user_id, $memDriver)
    {
        $memDriver->isDeleted          = 1;
        $memDriver->lastModifiedBy_id  = $user_id;
        $memDriver->email              = $memDriver->email . '_' . time();
        $memDriver->update();
        return $memDriver;
    }
}
