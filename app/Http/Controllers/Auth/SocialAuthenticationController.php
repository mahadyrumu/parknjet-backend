<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAuthService;
use App\Traits\GeneratePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthenticationController extends Controller
{
    use GeneratePassword;

    public function redirectTo($provider)
    {
        return Socialite::driver($provider)->redirect(['redirectpage' => request()->pagename]);
    }

    public function login_apple()
    {
        return Socialite::driver("sign-in-with-apple")
            ->scopes(["name", "email"])
            ->redirect();
    }

    public function callback_apple(Request $request)
    {
        try {

            $socialAuthService = new SocialAuthService;
            $socialAuthService->callback('apple');

            // Construct redirect URL
            $redirect = 'intent://callback?' . http_build_query($request->all()) . '#Intent;package=' . env('ANDROID_PACKAGE_IDENTIFIER') . ';scheme=signinwithapple;end';

            // Log the redirection URL
            Log::info('Redirecting to ' . $redirect);

            // Perform redirection
            return Redirect::away($redirect, 307);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'error' => true,
                "message" => $ex->getMessage()
            ], 401);
        }
    }
}
