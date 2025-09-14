<?php

namespace App\Services\Driver;

use App\Models\Backend\MemReservationDriver;
use App\Traits\SequenceUpdate;

class MemRsvnDriverService
{
    use SequenceUpdate;

    public function getDriver($user_id)
    {
        return MemReservationDriver::Where('owner_id', $user_id);
    }

    public function createMemRsvnDriver($request)
    {
        $memReservationDriver = new MemReservationDriver;
        $memReservationDriver->full_name          = $request->full_name;
        $memReservationDriver->email              = $request->email;
        $memReservationDriver->phone              = $request->phone;
        $memReservationDriver->version            = 0;
        $memReservationDriver->createdBy_id       = $request->createdBy_id ? $request->createdBy_id : $request->owner_id;
        $memReservationDriver->save();
        $this->updateSequence('backend_mysql', 'mem_reservation_driver', 'ReservationDriver_SEQ');
        return $memReservationDriver;
    }

    public function updateDriver($user_id, $request, $memReservationDriver)
    {
        $memReservationDriver->full_name          = $request->full_name;
        $memReservationDriver->email              = $request->email;
        $memReservationDriver->phone              = $request->phone;
        $memReservationDriver->version            = 0;
        $memReservationDriver->createdBy_id       = $user_id;
        $memReservationDriver->lastModifiedBy_id  = $user_id;
        $memReservationDriver->update();
        return $memReservationDriver;
    }

    public function deleteDriver($user_id, $memReservationDriver)
    {
        $memReservationDriver->isDeleted          = 1;
        $memReservationDriver->lastModifiedBy_id  = $user_id;
        $memReservationDriver->email              = $memReservationDriver->email . '_' . time();
        $memReservationDriver->update();
        return $memReservationDriver;
    }
}
