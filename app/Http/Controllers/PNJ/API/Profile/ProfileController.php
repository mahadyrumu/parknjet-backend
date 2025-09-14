<?php

namespace App\Http\Controllers\PNJ\API\Profile;

use App\Http\Controllers\Controller;
use App\Services\Mail\EmailSenderService;
use App\Services\User\UserService;
use App\Traits\GeneratePassword;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Jobs\Auth\ChangeEmailJob;
use App\Jobs\Auth\PasswordChangeMailJob;


class ProfileController extends Controller
{
    use GeneratePassword;

    public function getUser(UserService $userService, $id)
    {
        try {
            $user = $userService->getUser($id);
            Log::info("Get user:" . $user->user_name);

            return response()->json([
                'success'  => true,
                'data'     => $user
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function updateUser(UpdateProfileRequest $request, EmailSenderService $emailSenderService, UserService $userService, $id)
    {
        try {
            $user = $userService->getUser($id);
            if ($user->is_google_auth == 0 && $user->is_meta_auth == 0 && $user->is_apple_auth == 0) {
                if ($request->password == "") {
                    return response()->json([
                        'error'    => true,
                        'message'  => "Password is required"
                    ], ResponseCode["Unprocessable Content"]);
                }
                if (!$this->matches($request->password, $user->password)) {
                    return response()->json([
                        'error'    => true,
                        'message'  => "Password is incorrect. Please try again with correct password."
                    ], ResponseCode["Unprocessable Content"]);
                }
            }

            $fullName       = $request->full_name;
            $email          = $request->user_name;
            $phone          = $request->phone;
            $previousEmail  = $user->user_name;

            $user = $userService->updateUser($fullName, $email, $phone, $user);
            Log::info("Update user:" . $user->user_name);

            if ($previousEmail != $email) {
                $emailSenderService->sendEmailOnChange($previousEmail, $email);

                ChangeEmailJob::dispatch($user, $previousEmail);
                // Mail::to($previousEmail)->send(new ChangeEmail($user, $previousEmail));
            }

            return response()->json([
                'success'  => true,
                'data'     => $user,
                'message'  => "Your profile has been updated successfully."
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request, UserService $userService, $id)
    {
        try {
            $user = $userService->getUser($id);

            if (!$this->matches($request->current_password, $user->password)) {
                return response()->json([
                    'error'    => true,
                    'message'  => "Password is incorrect. Please try again with correct password."
                ], ResponseCode["Unprocessable Content"]);
            } else {
                $userService->updatePassword($user, $request->password);
                Log::info("Update password of user:" . $user->user_name);

                PasswordChangeMailJob::dispatch($user->user_name, $user->full_name);
                // Mail::to($user->user_name)->send(new PasswordChangeConfirmation($user->full_name));

                return response()->json([
                    'success' => true,
                    'message' => "Your password has been updated successfully."
                ], ResponseCode["Success"]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
