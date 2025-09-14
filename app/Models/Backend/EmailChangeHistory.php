<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'email_change_history';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'created';
    public const UPDATED_AT = 'updated';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'previous_email',
        'new_email',
        'action_count',
    ];
}
