<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonPricing extends Model
{
    use HasFactory;

    protected $table = 'anon_pricing';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function payment()
    {
        return $this->belongsTo(AnonPayment::class, 'payment_id', 'id');
    }

    public function lot_payment()
    {
        return $this->belongsTo(AnonLotPayment::class, 'lotPayment_id', 'id');
    }
}
