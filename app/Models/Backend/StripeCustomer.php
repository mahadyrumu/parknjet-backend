<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeCustomer extends Model
{
    use HasFactory;

    protected $table = 'stripe_customer';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';
}
