<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lot1CustomerActivity extends Model
{
    protected $table = 'customer_activity';
    protected $connection = 'lot1_mysql';
}
