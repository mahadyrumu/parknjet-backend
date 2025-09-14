<?php

namespace App\Models\Backend;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class MemUser extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'mem_user';
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
        'version',
        'role',
        'reward_id',
        'wallet_id',
        'full_name',
        'user_name',
        'phone',
        'isVerified',
        'is_google_auth',
        'is_meta_auth',
        'is_apple_auth',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Return email address only...
        return $this->user_name;
    }

    public function prepaidWallet()
    {
        return $this->belongsTo(MemWalletPrepaid::class, 'id', 'owner_id');
    }

    public function prepaidWalletLot1()
    {
        return $this->belongsTo(MemWalletPrepaid::class, 'prePaidWalletLot1_id', 'id');
    }

    public function prepaidWalletLot2()
    {
        return $this->belongsTo(MemWalletPrepaid::class, 'prePaidWalletLot2_id', 'id');
    }

    public function walletLot1()
    {
        return $this->belongsTo(MemWallet::class, 'walletLot1_id', 'id');
    }

    public function wallet()
    {
        return $this->belongsTo(MemWallet::class, 'wallet_id', 'id');
    }

    public function stripeAccount()
    {
        return $this->belongsTo(StripeCustomer::class, 'id', 'createdBy_id');
    }

    public function RewardLotOne()
    {
        return $this->belongsTo(MemReward::class, 'rewardLot1_id', 'id');
    }

    public function RewardLotTwo()
    {
        return $this->belongsTo(MemReward::class, 'reward_id', 'id');
    }

    public function walletLotOne()
    {
        return $this->belongsTo(MemWallet::class, 'walletLot1_id', 'id');
    }

    public function walletLotTwo()
    {
        return $this->belongsTo(MemWallet::class, 'wallet_id', 'id');
    }

    public function walletLotOnePrepaid()
    {
        return $this->belongsTo(MemWalletPrepaid::class, 'prePaidWalletLot1_id', 'id');
    }

    public function walletLotTwoPrepaid()
    {
        return $this->belongsTo(MemWalletPrepaid::class, 'prePaidWalletLot2_id', 'id');
    }
}
