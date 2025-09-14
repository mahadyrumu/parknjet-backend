<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemRewardTxn extends Model
{
    use HasFactory;

    protected $table = 'mem_reward_txn';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function reward()
    {
        return $this->belongsTo(MemReward::class,'reward_id','id');
    }
}
