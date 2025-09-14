<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemWalletPrepaidTxn extends Model
{
    use HasFactory;

    protected $table = 'mem_wallet_prepaid_txn';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function prepaidWallet()
    {
        return $this->belongsTo(MemWalletPrepaid::class,'prePaidWallet_id','id');
    }
}
