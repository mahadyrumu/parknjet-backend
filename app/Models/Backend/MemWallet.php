<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemWallet extends Model
{
    use HasFactory;

    protected $table = 'mem_wallet';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    protected $fillable = [
        'id',
        'isDeleted',
        'version',
        'days',
        'owner_id',
    ];

    public function userLotOne()
    {
        return $this->belongsTo(MemUser::class, 'id', 'walletLot1_id');
    }

    public function userLotTwo()
    {
        return $this->belongsTo(MemUser::class, 'id', 'wallet_id');
    }

    public function walletTxn()
    {
        return $this->hasMany(MemWalletTxn::class, 'wallet_id', 'id');
    }
}
