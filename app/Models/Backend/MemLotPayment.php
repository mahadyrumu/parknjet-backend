<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemLotPayment extends Model
{
    use HasFactory;

    protected $table = 'mem_lot_payment';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'created';
    public const UPDATED_AT = 'updated';

}
