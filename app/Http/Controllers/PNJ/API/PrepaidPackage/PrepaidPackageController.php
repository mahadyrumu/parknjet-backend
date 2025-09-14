<?php

namespace App\Http\Controllers\PNJ\API\PrepaidPackage;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Services\PrepaidPackage\PrepaidPackagesService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class PrepaidPackageController extends Controller
{
    public function getPrepaidPackages(PrepaidPackagesService $prepaidPackagesService)
    {
        try {
            $prepaidPackages = $prepaidPackagesService->getPrepaidPackage()
                ->where('isDeleted', '!=', 1)
                ->get();
            Log::info("Get prepaid packages");

            return response()->json([
                'success' => true,
                'data' => $prepaidPackages
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getPrepaidPackage(PrepaidPackagesService $prepaidPackagesService, $owner_id, $lot, $package_id)
    {
        try {
            $prepaidPackage = $prepaidPackagesService->getPrepaidPackage()
                ->where('lotType', $lot)
                ->where('id', $package_id)
                ->first();
            Log::info("Get prepaid package");

            return response()->json([
                'success' => true,
                'data' => $prepaidPackage
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function buyPackageForAUser(Request $request, PrepaidPackagesService $prepaidPackagesService, $owner_id, $package_id)
    {
        Log::info("buy Package For A User");
        try {
            $prepaidPackages = $prepaidPackagesService->buyAPackage($request, $owner_id, $package_id);
            Log::info("Prepaid package bought by user:" . $prepaidPackages->user_name);

            return response()->json([
                'success' => true,
                'data' => $prepaidPackages
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
