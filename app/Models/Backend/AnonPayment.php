<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonPayment extends Model
{
    use HasFactory;

    protected $table = 'anon_payment';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    protected $hidden = ['stripeChargeJson'];
}
