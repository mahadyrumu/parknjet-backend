<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MemReservation extends Model
{
    use HasFactory;

    protected $table = 'mem_reservation';
    protected $connection = 'backend_mysql';
    // public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function vehicle()
    {
        return $this->belongsTo(MemReservationVehicle::class, 'vehicle_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(MemReservationDriver::class, 'driver_id', 'id');
    }

    public function pricing()
    {
        return $this->hasMany(MemPricing::class, 'reservation_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(MemUser::class, 'owner_id', 'id');
    }

    public function wallet_transaction()
    {
        return $this->hasMany(MemWalletTxn::class, 'reservation_id', 'id');
    }

    public function pre_paid_wallet_txns()
    {
        return $this->hasMany(MemWalletPrepaidTxn::class, 'reservation_id', 'id');
    }

    // Define accessor for points
    public function getPointsAttribute()
    {
        $pointsToReturn = 0;

        foreach ($this->pricing as $eachPricing) {
            if (!empty($eachPricing->payment)) {
                Log::info("Online Payment found with id " . $eachPricing->payment->id);
                if ($eachPricing->payment->paymentStatus === 'PAID') {
                    Log::info("Reservation paid online, adding points from it");
                    $pointsToReturn += $eachPricing->points ?? 0;
                }
            } elseif (!empty($eachPricing->lotPayment)) {
                Log::info("LOT Payment found with id " . $eachPricing->lotPayment->id);
                if ($eachPricing->lotPayment->paymentStatus === 'PAID') {
                    Log::info("Reservation paid at lot, adding points from it");
                    $pointsToReturn += $eachPricing->points ?? 0;
                }
            }
        }
    
        return $pointsToReturn;
    }
}
