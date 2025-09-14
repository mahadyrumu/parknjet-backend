<?php

namespace App\Http\Controllers\PNJ\API\Driver;

use App\Http\Controllers\Controller;
use App\Services\Driver\MemDriverService;
use App\Http\Requests\Driver\DriverCreateRequest;
use App\Http\Requests\Driver\DriverUpdateRequest;
use Illuminate\Support\Facades\Log;


class DriverController extends Controller
{
    public function getDrivers(MemDriverService $memDriverService, $owner_id)
    {
        try {
            $drivers = $memDriverService->getDrivers($owner_id)
                ->where('isDeleted', 0)
                ->get();
            Log::info("Get drivers");

            return response()->json([
                'success'  => true,
                'data'     => $drivers
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getDriver(MemDriverService $memDriverService, $owner_id, $id)
    {
        try {
            $driver = $memDriverService->getDriver($owner_id, $id)
                ->where('isDeleted', 0)
                ->first();
            Log::info("Get single driver");

            return response()->json([
                'success'  => true,
                'data'     => $driver
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function createDriver(DriverCreateRequest $request, MemDriverService $memDriverService, $owner_id)
    {
        try {
            $full_name  = $request->full_name;
            $email      = $request->email;
            $phone      = $request->phone;

            $newDriver = $memDriverService->createDriver($owner_id, $full_name, $email, $phone);
            Log::info("Create driver ID:" . $newDriver->id);

            return response()->json([
                'success'  => true,
                'data'     => $newDriver,
                'message'  => "The driver " . $newDriver->full_name . " has been added successfully."
            ], ResponseCode["Created"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function updateDriver(DriverUpdateRequest $request, MemDriverService $memDriverService, $owner_id, $id)
    {
        try {
            $full_name  = $request->full_name;
            $email      = $request->email;
            $phone      = $request->phone;

            $driver = $memDriverService->getDriver($owner_id, $id)
                ->where('isDeleted', 0)
                ->first();

            if ($driver) {
                $driver = $memDriverService->updateDriver($owner_id, $full_name, $email, $phone, $driver);
                Log::info("Update driver ID:" . $driver->id);

                return response()->json([
                    'success'  => true,
                    'data'     => $driver,
                    'message'  => "The driver has been updated successfully."
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "The driver you want to update is not found."
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

    public function destroy(MemDriverService $memDriverService, $owner_id, $id)
    {
        try {
            $driver = $memDriverService->getDriver($owner_id, $id)
                ->where('isDeleted', 0)
                ->first();

            if ($driver) {
                $memDriverService->deleteDriver($owner_id, $driver);
                Log::info("Delete driver ID:" . $driver->id);

                return response()->json([
                    'success'  => true,
                    'message'  => "The driver has been deleted successfully."
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "The driver you are trying to delete is not found."
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
}
