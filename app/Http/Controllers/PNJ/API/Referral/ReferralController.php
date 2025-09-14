<?php

namespace App\Http\Controllers\PNJ\API\Referral;

use App\Http\Controllers\Controller;
use App\Traits\CheckUser;
use App\Mail\ReferralEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Referral\ReferralRequest;
use App\Jobs\Auth\ReferralEmailJob;
use App\Services\User\UserService;
use App\Services\Referral\ReferralService;


class ReferralController extends Controller
{
    use CheckUser;

    public function getAllMemberReferral(ReferralService $ReferralService, $owner_id)
    {
        try {
            $referrals = $ReferralService->getReferralBy($owner_id)->get();
            if ($referrals) {
                Log::info("Get referrals");

                return response()->json([
                    'success'  => true,
                    'data'     => $referrals
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "No referral found for this user."
                ], ResponseCode["Not Found"]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getMemberReferral(ReferralService $ReferralService, $owner_id, $email)
    {
        try {
            $memberReferrals = $ReferralService->getReferralBy($owner_id)
                ->where('referredUserName', strtolower($email))->get();
            if ($memberReferrals) {
                Log::info("Get member referrals");

                return response()->json([
                    'success'  => true,
                    'data'     => $memberReferrals
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "There is no referral associated to this email."
                ], ResponseCode["Not Found"]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function createReferral(ReferralRequest $request, ReferralService $ReferralService, UserService $userService, $owner_id)
    {
        try {
            $email = $request->email;
            $findUserByEmail = $this->checkEmail($email);
            if ($findUserByEmail) {
                return response()->json([
                    'error'    => true,
                    'message'  => $email . " already exists."
                ], ResponseCode["Forbidden"]);
            }

            $findUserByReferredEmail = $ReferralService->getReferralBy($owner_id)
                ->where('referredUserName', $email)->first();
            if ($findUserByReferredEmail) {
                return response()->json([
                    'error'    => true,
                    'message'  => $email . " already referred."
                ], ResponseCode["Forbidden"]);
            }

            $referral = $ReferralService->createReferral($owner_id, $email);
            Log::info("Referred email:" . $referral->referredUserName);
            $user = $userService->getUser($owner_id);

            ReferralEmailJob::dispatch($email, $user->full_name);
            // Mail::to($email)->send(new ReferralEmail($user->full_name));

            return response()->json([
                'success'  => true,
                'data'     => $referral,
                'message'  => "Invited with this email successfully."
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
