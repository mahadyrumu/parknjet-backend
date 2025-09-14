<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemDriver extends Model
{
    use HasFactory;

    protected $table = 'mem_driver';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    protected $fillable = [
        'id',
        'full_name',
        'email',
        'phone',
        'lastModifiedBy_id',
    ];

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
