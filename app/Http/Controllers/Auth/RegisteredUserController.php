<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Backend\MemReward;
use App\Models\Backend\MemUser;
use App\Models\Backend\MemWallet;
use App\Providers\RouteServiceProvider;
use App\Traits\GeneratePassword;
use App\Traits\SequenceUpdate;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use GeneratePassword, SequenceUpdate;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['max:255'],
            'email'         => ['required', 'string', 'max:255', 'unique:' . MemUser::class . ',user_name'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            DB::connection('backend_mysql')->transaction(function () use ($request) {

                $reward = MemReward::create([
                    'isDeleted'     => 0,
                    'version'       => 1,
                    'points'        => 0,
                ]);

                $wallet = MemWallet::create([
                    'isDeleted'     => 0,
                    'version'       => 1,
                    'days'          => 0,
                ]);

                $user = MemUser::create([
                    'version'       => 1,
                    'role'          => 'ROLE_USER',
                    'reward_id'     => $reward->id,
                    'wallet_id'     => $wallet->id,
                    'full_name'     => $request->name,
                    'phone'         => $request->phone,
                    'user_name'     => $request->email,
                    'is_google_auth'     => 0,
                    'is_meta_auth'     => 0,
                    'is_apple_auth'     => 0,
                    'password'      => $this->encode($request->password),
                ]);
                Log::debug($user);

                $reward->owner_id = $user->id;
                $reward->update();
                $wallet->owner_id = $user->id;
                $wallet->update();

                event(new Registered($user));

                Auth::login($user);
            });
            $this->updateSequence('backend_mysql', 'mem_reward', 'Reward_SEQ');
            $this->updateSequence('backend_mysql', 'mem_wallet', 'Wallet_SEQ');
            $this->updateSequence('backend_mysql', 'mem_user', 'User_SEQ');

        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
