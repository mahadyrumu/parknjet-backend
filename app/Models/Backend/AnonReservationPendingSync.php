<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonReservationPendingSync extends Model
{
    use HasFactory;

    protected $table = 'anon_reservation_pending_sync';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'created';
    public const UPDATED_AT = 'updated';
}
