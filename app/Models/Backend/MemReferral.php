<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class MemReferral extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'mem_referral';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'created';
    public const UPDATED_AT = 'updated';

    protected $fillable = [
        'id',
        'created',
        'updated',
        'version',
        'referredUserName',
        'firstReservation_id',
        'referredBy_id',
        'referredUser_id',
    ];

    public function referredUser()
    {
        return $this->belongsTo(MemUser::class, 'id', 'referredUser_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(MemUser::class, 'id', 'referredBy_id');
    }
}
