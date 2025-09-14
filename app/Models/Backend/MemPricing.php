<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemPricing extends Model
{
    use HasFactory;

    protected $table = 'mem_pricing';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function payment()
    {
        return $this->belongsTo(MemPayment::class, 'id', 'pricing_id');
    }

    public function lot_payment()
    {
        return $this->belongsTo(MemLotPayment::class, 'lotPayment_id', 'id');
    }
}
