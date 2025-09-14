<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    public $timestamps = false;
    protected $table = 'dispatch';
    protected $fillable = ['cid', 'lot_id', 'phone', 'island', 'delay', 'comment'];
    protected $connection = 'dispatch_mysql';
}
