<?php

namespace App\Http\Controllers\PNJ\API\Auth;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\Backend\MemUser;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\Request;
use App\Services\Auth\AuthenticationService;
use App\Traits\GeneratePassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Cookie;

use stdClass;

class AuthenticationController extends Controller
{
    use GeneratePassword;

    public function signIn(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $user = MemUser::where('user_name', $request->email)->first();
            Log::info("Login user:" . $request->email);

            if ($request->client == 'mobile') {
                $token = $user->createToken('PNJ Mobile App')->plainTextToken;
                $expiredAt = "";
            } else {
                $token = $user->createToken('WEB_APP', expiresAt: now()->addHours(24))->plainTextToken;
                $expiredAt = $user->tokens->last()->expires_at;
            }

            return response()->json([
                'success' => true,
                'token' => $token,
                'user_id' => $user->id,
                'expired_at' => $expiredAt
            ], ResponseCode["Success"]);
        } catch (NotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], ResponseCode["Not Found"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function signOut(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            Log::info("Logout user:" . $request->user()->user_name);

            foreach (Cookie::get() as $name => $value) {
                Cookie::queue(Cookie::forget($name));
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.'
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function register(RegisterRequest $request, AuthenticationService $authenticateService)
    {
        try {
            $name = $request->name;
            $phone = $request->phone;
            $email = $request->email;
            $password = $request->password;
            $coupon = $request->couponCode;

            $newUser = $authenticateService->register($name, $phone, $email, $password, $coupon);
            Log::info("New user:" . $newUser->user_name);
            $token = $newUser->createToken('WEB_APP', expiresAt: now()->addHours(24))->plainTextToken;
            $expiredAt = $newUser->tokens->last()->expires_at;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user_id' => $newUser->id,
                'expired_at' => $expiredAt
            ], ResponseCode["Created"]);
        } catch (PNJException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], ResponseCode["Not Found"]);
        } catch (\Exception $ex) {
            Log::error($ex);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request, AuthenticationService $authenticateService)
    {
        try {
            $user_name = $request->user_name;

            $message = $authenticateService->forgotPassword($user_name);
            Log::info("User request for reset password:" . $user_name);

            return response()->json([
                'success' => true,
                'message' => $message,
            ], ResponseCode["Success"]);
        } catch (NotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], ResponseCode["Not Found"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getToken(Request $request, SocialAuthService $socialAuthService)
    {
        try {
            $provider = $request->provider;
            $token = $request->access_token;

            if ($request->client == 'mobile') {
                $userInfo = Socialite::driver($provider)->userFromToken($token);
            } else {
                if ($provider == "apple") {
                    $userInfo = Socialite::driver("sign-in-with-apple")
                        ->stateless()->user();
                } else {
                    $userInfo = Socialite::driver($provider)->stateless()->user();
                }
            }

            // get the provider's user. (In the provider server)


            // search for a user in our server with the email
            $user = MemUser::where('user_name', $userInfo->email)->first();

            //  if there is no record with these data, create a new user
            if ($user == null) {
                $user = $socialAuthService->createUser($userInfo, $provider);
            } else {
                $user = $socialAuthService->updateUser($user, $provider);
            }

            Log::info("Social auth login:" . $user->user_name);
            Auth::login($user);

            if ($request->client == 'mobile') {
                $token = $user->createToken('PNJ Mobile App')->plainTextToken;
                $expiredAt = "";
            } else {
                $token = $user->createToken('WEB_APP', expiresAt: now()->addHours(24))->plainTextToken;
                $expiredAt = $user->tokens->last()->expires_at;
            }

            // return the token for usage
            return response()->json([
                'success' => true,
                'token' => $token,
                'user_id' => $user->id,
                'expired_at' => $expiredAt
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function appleSignIn(Request $request, SocialAuthService $socialAuthService)
    {
        try {
            $userInfo = new stdClass;
            $userInfo->name = $request->name;
            $userInfo->email = $request->email;

            $provider = $request->provider;

            // search for a user in our server with the email
            $user = MemUser::where('user_name', $request->email)->first();

            //  if there is no record with these data, create a new user
            if ($user == null) {
                $user = $socialAuthService->createUser($userInfo, $provider);
            } else {
                $user = $socialAuthService->updateUser($user, $provider);
            }

            Log::info("Social auth login:" . $user->user_name);
            Auth::login($user);

            $token = $user->createToken('WEB_APP', expiresAt: now()->addHours(24))->plainTextToken;
            $expiredAt = $user->tokens->last()->expires_at;

            // return the token for usage
            return response()->json([
                'success' => true,
                'token' => $token,
                'user_id' => $user->id,
                'expired_at' => $expiredAt
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            $token = $request->user()->createToken('WEB_APP', expiresAt: now()->addHours(24))->plainTextToken;
            $expiredAt = $request->user()->tokens->last()->expires_at;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user_id' => $request->user()->id,
                'expired_at' => $expiredAt
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
