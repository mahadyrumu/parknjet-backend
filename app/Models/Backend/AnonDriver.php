<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonDriver extends Model
{
    use HasFactory;
    
    protected $table = 'anon_driver';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $appends=['fullName'];

    public function getFullNameAttribute()
    {
        return $this->attributes['full_name'];
    }
    
}
