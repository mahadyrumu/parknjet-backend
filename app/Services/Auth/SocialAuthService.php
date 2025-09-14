<?php

namespace App\Services\Auth;

use App\Models\Backend\MemReward;
use App\Models\Backend\MemUser;
use App\Models\Backend\MemWallet;
use App\Traits\GeneratePassword;
use App\Traits\SequenceUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthService
{
    use GeneratePassword, SequenceUpdate;

    public function callback($provider)
    {
        try {
            if ($provider == "apple") {
                $userInfo = Socialite::driver("sign-in-with-apple")
                    ->user();
            } else {
                $userInfo = Socialite::driver($provider)->user();
            }

            $user = MemUser::where('user_name', $userInfo->email)->first();
            if ($user) {
                Log::info("old user");
                Auth::login($user);
                if ($provider == 'google') {
                    if ($user->is_google_auth == 0) {
                        $user->is_google_auth = 1;
                        $user->save();
                    }
                } elseif ($provider == 'apple') {
                    if ($user->is_apple_auth == 0) {
                        $user->is_apple_auth = 1;
                        $user->save();
                    }
                } else {
                    if ($user->is_meta_auth == 0) {
                        $user->is_meta_auth = 1;
                        $user->save();
                    }
                }
                Log::info("This for development purpuse - " . $userInfo->token);
                Log::info("Successfully authenticated by social auth");
            } else {
                Log::info("new user");
                $user = $this->createUser($userInfo, $provider);
            }
            Auth::login($user);
            return $user;
        } catch (\Exception $ex) {
            return $ex->getMessage();
            // throw new PNJException("Exception: " . $ex->getMessage());
        }
    }

    public function isSocialAuth($user)
    {
        return ($user->is_google_auth == 1 || $user->is_meta_auth == 1 || $user->is_apple_auth == 1);
    }

    public function createUser($userInfo, $provider)
    {
        // create a new reward
        $reward = MemReward::create([
            'isDeleted'     => 0,
            'version'       => 1,
            'points'        => 0,
        ]);

        // create a new wallet
        $wallet = MemWallet::create([
            'isDeleted'     => 0,
            'version'       => 1,
            'days'          => 0,
        ]);

        // create a new user
        $password = Str::password();
        $user                    = new MemUser();
        $user->full_name         = $userInfo->name;
        $user->user_name         = $userInfo->email;
        $user->role              = 'ROLE_USER';
        $user->reward_id         = $reward->id;
        $user->wallet_id         = $wallet->id;
        if ($provider == 'google') {
            $user->is_google_auth = 1;
        } elseif ($provider == 'apple') {
            $user->is_apple_auth = 1;
        } else {
            $user->is_meta_auth = 1;
        }
        $user->isVerified        = 1;
        $user->password          = $this->encode($password);
        $user->save();

        $reward->owner_id = $user->id;
        $reward->update();

        $wallet->owner_id = $user->id;
        $wallet->update();
        
        $this->updateSequence('backend_mysql', 'mem_reward', 'Reward_SEQ');
        $this->updateSequence('backend_mysql', 'mem_wallet', 'Wallet_SEQ');
        $this->updateSequence('backend_mysql', 'mem_user', 'User_SEQ');

        return $user;
    }

    public function updateUser($user, $provider)
    {
        if ($provider == 'google') {
            if ($user->is_google_auth == 0) {
                $user->is_google_auth = 1;
                $user->update();
            }
        } elseif ($provider == 'apple') {
            if ($user->is_apple_auth == 0) {
                $user->is_apple_auth = 1;
                $user->update();
            }
        } else {
            if ($user->is_meta_auth == 0) {
                $user->is_meta_auth = 1;
                $user->update();
            }
        }
        return $user;
    }
}
