<?php

namespace App\Models\Backend\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blackout extends Model
{
    use HasFactory;
    
    protected $table = 'admin_blackout';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';
}
