<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonReservation extends Model
{
    use HasFactory;

    protected $table = 'anon_reservation';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function vehicle()
    {
        return $this->belongsTo(AnonVehicle::class, 'vehicle_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(AnonDriver::class, 'driver_id', 'id');
    }

    public function pricing()
    {
        return $this->hasMany(AnonPricing::class, 'reservation_id', 'id');
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

}
