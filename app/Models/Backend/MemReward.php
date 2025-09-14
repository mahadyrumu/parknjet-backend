<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemReward extends Model
{
    use HasFactory;

    protected $table = 'mem_reward';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    protected $fillable = [
        'id',
        'isDeleted',
        'version',
        'points',
        'owner_id',
    ];

    public function userLotOne()
    {
        return $this->belongsTo(MemUser::class, 'id', 'rewardLot1_id');
    }

    public function userLotTwo()
    {
        return $this->belongsTo(MemUser::class, 'id', 'reward_id');
    }

    public function rewardTxn()
    {
        return $this->hasMany(MemRewardTxn::class, 'reward_id', 'id');
    }
}
