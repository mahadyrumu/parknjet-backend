<?php

namespace App\Services\Auth;

use App\Exceptions\PNJException;
use App\Models\Backend\MemReward;
use App\Models\Backend\MemUser;
use App\Models\Backend\MemWallet;
use App\Services\Coupon\CouponService;
use App\Services\Pricing\PricingService;
use App\Services\Wallet\WalletService;
use App\Traits\CheckUser;
use App\Traits\GeneratePassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\Mail\ResetPassword as ResetPasswordMail;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NotFoundException;
use App\Jobs\Auth\EmailVerificationJob;
use App\Traits\SequenceUpdate;

class AuthenticationService
{
    use CheckUser, GeneratePassword, SequenceUpdate;

    public $newUser;

    public function register($name, $phone, $email, $password, $coupon)
    {
        
        if ($coupon != null) {
            Log::info("Validating Coupon :" . $coupon);
            $couponService = new CouponService();    
            $couponDB = $couponService->findByCode($coupon);
            // Log::info($couponDB);
            if ($couponService->isCouponValidForRegistration($couponDB, $coupon) == false) {
                throw new PNJException("Invalid Signup Coupon " . $coupon);
            }
        }
        
        try {
            DB::connection('backend_mysql')->transaction(function () use ($name, $phone, $email, $password, $coupon) {

                $reward = MemReward::create([
                    'isDeleted' => 0,
                    'version' => 1,
                    'points' => 0,
                ]);

                $wallet = MemWallet::create([
                    'isDeleted' => 0,
                    'version' => 1,
                    'days' => 0,
                ]);

                $user = MemUser::create([
                    'version' => 1,
                    'role' => 'ROLE_USER',
                    'reward_id' => $reward->id,
                    'wallet_id' => $wallet->id,
                    'full_name' => $name,
                    'phone' => $phone,
                    'user_name' => $email,
                    'isVerified' => 0,
                    'is_google_auth' => 0,
                    'is_meta_auth' => 0,
                    'is_apple_auth' => 0,
                    'password' => $this->encode($password),
                ]);

                $reward->owner_id = $user->id;
                $reward->update();
                $this->updateSequence('backend_mysql', 'mem_reward', 'Reward_SEQ');

                $wallet->owner_id = $user->id;
                $wallet->update();
                $this->updateSequence('backend_mysql', 'mem_wallet', 'Wallet_SEQ');

                $this->updateSequence('backend_mysql', 'mem_user', 'User_SEQ');
                $this->newUser = $user;

                try {
                    if ($coupon != null) {
                        Log::info("Signing up user with Registration Coupon :" . $coupon);
                        $couponService = new CouponService();    
                        $couponDB = $couponService->findByCode($coupon);
                        $walletService = new WalletService;
                        $walletService->addForUser($couponDB->promotion->days, TriggerType['SIGNUP_COUPON'], "Extra day upon signup", $couponDB->promotion->lotType, $user);
                        $couponService->incrementCouponRedeemCount($couponDB);
                    }
                } catch (\Exception $ex) {
                    Log::error("Error while adding days to wallet : " . $ex->getMessage());
                    throw new PNJException($ex->getMessage());
                }

                event(new Registered($user));

                Auth::login($user);
            });
            
            
            
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            throw new PNJException($ex->getMessage());
        }
        return $this->newUser;
    }

    public function forgotPassword($user_name)
    {
        $user = $this->checkEmail($user_name);

        if ($user) {
            $verify = $this->checkEmailForPasswordReset($user_name);
            if ($verify->exists()) {
                $verify->delete();
            }
            $token = $this->encode($user_name);
            $password_reset = $this->insertPasswordReset($user_name, $token);
            if ($password_reset) {

                EmailVerificationJob::dispatch($user_name, $user, $token);
                // Mail::to($user_name)->send(new ResetPasswordMail($user, $token));
                return trans('passwords.sent');
            }
        }
        throw new NotFoundException("This email is not found in our records.");
    }
}
