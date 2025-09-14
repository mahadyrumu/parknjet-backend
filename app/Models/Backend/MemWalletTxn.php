<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemWalletTxn extends Model
{
    use HasFactory;

    protected $table = 'mem_wallet_txn';
    protected $connection = 'backend_mysql';
    public const CREATED_AT = 'createdDate';
    public const UPDATED_AT = 'lastModifiedDate';

    public function wallet()
    {
        return $this->belongsTo(MemWallet::class, 'wallet_id', 'id');
    }
}
