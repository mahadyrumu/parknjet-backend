<?php

namespace App\Http\Controllers\PNJ\API\Admin;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use App\Services\Wallet\PrePaidWalletService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AdminUserController extends Controller
{
    public function users(Request $request, UserService $userService)
    {
        $users = $userService->getUsersWithAll();

        if ($request->userName) {
            $users = $users->where('user_name', $request->userName);
        }
        if ($request->phone) {
            $users = $users->where('phone', $request->phone);
        }
        if ($request->fullNameStart) {
            $users = $users->where('full_name', 'like', $request->fullNameStart . '%');
        }
        if ($request->searchText) {
            $users = $users->where('full_name', 'like', '%' . $request->searchText . '%')
                ->orWhere('user_name', 'like', '%' . $request->searchText . '%');
        }
        if ($request->fullNameEnd) {
            $users = $users->where('full_name', 'like', '%' . $request->fullNameEnd);
        }
        if ($request->fullName) {
            $users = $users->where('full_name', $request->fullName);
        }
        if ($request->role && $request->deleted) {
            $users = $users->where('role', $request->role)->where('is_deleted', $request->deleted);
        }
        if ($request->role) {
            $users = $users->where('role', $request->role);
        }
        if ($request->deleted) {
            $users = $users->where('is_deleted', $request->is_deleted);
        }
        if ($request->days && $request->lotType && $request->type && $request->triggerType && $request->isAdd && $request->userId) {
            Log::info("User ID to redeem days = " . $request->userId);

            $user = $users->where('id', $request->userId)
                ->first();
            if (!$user) {
                throw new ResourceNotFoundException("No Such User");
            }
            try {
                if ($request->type === WalletType['EARNED']) {
                    return $this->addOrRemoveDaysForUserWallet($request->days, $request->lotType, $request->triggerType, filter_var($request->isAdd, FILTER_VALIDATE_BOOLEAN), $user, $request->comment);
                } else {
                    return $this->addOrRemoveDaysForUserPrepaidWallet($request->days, $request->lotType, $request->triggerType, filter_var($request->isAdd, FILTER_VALIDATE_BOOLEAN), $user, $request->comment);
                }
            } catch (PNJException $e) {
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
        } else {
            $users = $users->first();
            if($users){
                $users["userName"] = $users["user_name"];
                $users["fullName"] = $users["full_name"];
                $users["reward"] = $users["RewardLotTwo"];
                $users["rewardLot1"] = $users["RewardLotOne"];
                $users["wallet"] = $users["walletLotTwo"];
                $users["walletLot1"] = $users["walletLotOne"];
                $users["prePaidWalletLot1"] = $users["walletLotOnePrepaid"];
                $users["prePaidWalletLot2"] = $users["walletLotTwoPrepaid"];

                if($users["wallet"]){
                   $users["wallet"]["currentBalance"] = $users["wallet"]["days"];
                }

               if($users["walletLot1"]){
                   $users["walletLot1"]["currentBalance"] = $users["walletLot1"]["days"];
               }

               if($users["prePaidWalletLot1"]){
                   $users["prePaidWalletLot1"]["currentBalance"] = $users["prePaidWalletLot1"]["days"];
               }

               if($users["prePaidWalletLot2"]){
                   $users["prePaidWalletLot2"]["currentBalance"] = $users["prePaidWalletLot2"]["days"];
               }

             }
            return response()->json([$users], Response::HTTP_OK);
        }
    }

    public function addOrRemoveDaysForUserWallet($days, $lotType, $triggerType, $isAdd, $user, $comment)
    {
        $walletService = new WalletService();
        $walletTransaction = null;
        if ($isAdd) {
            $walletTransaction = $walletService->addForUser($days, $triggerType, $comment, $lotType, $user);
        } else {
            $walletTransaction = $walletService->subtractForUser($days, $triggerType, $comment, $lotType, $user);
        }

        return response()->json($walletTransaction->wallet, Response::HTTP_OK);
    }

    public function addOrRemoveDaysForUserPrepaidWallet($days, $lotType, $triggerType, $isAdd, $user, $comment)
    {
        $prePaidWalletService = new PrePaidWalletService();
        $prePaidWalletTxn = null;

        if ($isAdd) {
            $prePaidWalletTxn = $prePaidWalletService->addForUser($days, $triggerType, $comment, $lotType, $user);
        } else {
            $prePaidWalletTxn = $prePaidWalletService->subtractForUser($days, $triggerType, $comment, $lotType, $user);
        }

        return response()->json($prePaidWalletTxn->prepaidWallet, Response::HTTP_OK);
    }
}
