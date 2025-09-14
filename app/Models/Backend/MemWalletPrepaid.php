<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemWalletPrepaid extends Model
{
    use HasFactory;

    protected $table = 'mem_wallet_prepaid';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function userLotOne()
    {
        return $this->belongsTo(MemUser::class, 'id', 'prePaidWalletLot1_id');
    }

    public function userLotTwo()
    {
        return $this->belongsTo(MemUser::class, 'id', 'prePaidWalletLot2_id');
    }

    public function walletPrepaidTxn()
    {
        return $this->hasMany(MemWalletPrepaidTxn::class, 'prePaidWallet_id', 'id');
    }
}
