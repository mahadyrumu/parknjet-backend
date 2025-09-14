<?php

namespace App\Http\Controllers\PNJ\API\Vehicle;

use App\Http\Controllers\Controller;
use App\Services\Vehicle\MemVehicleService;
use App\Http\Requests\Vehicle\VehicleCreateRequest;
use App\Http\Requests\Vehicle\VehicleUpdateRequest;
use Illuminate\Support\Facades\Log;


class VehicleController extends Controller
{
    public function getVehicles(MemVehicleService $memVehicleService, $owner_id)
    {
        try {
            $vehicles = $memVehicleService->getVehicles($owner_id)
                ->where('isDeleted', 0)
                ->get();
            Log::info("Get vehicles");

            return response()->json([
                'success'  => true,
                'data'     => $vehicles
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getVehicle(MemVehicleService $memVehicleService, $owner_id, $id)
    {
        try {
            $vehicle = $memVehicleService->getVehicle($owner_id, $id)
                ->where('isDeleted', 0)
                ->first();
            Log::info("Get single vehicle");

            return response()->json([
                'success'  => true,
                'data'     => $vehicle
            ], ResponseCode["Success"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function createVehicle(VehicleCreateRequest $request, MemVehicleService $memVehicleService, $owner_id)
    {
        try {
            $makeModel      = $request->makeModel;
            $plate          = $request->plate;
            $vehicleLength  = $request->vehicleLength;

            $newVehicle = $memVehicleService->createVehicle($owner_id, $makeModel, $plate, $vehicleLength);
            Log::info("Create vehicle ID:" . $newVehicle->id);

            return response()->json([
                'success'  => true,
                'data'     => $newVehicle,
                'message'  => "The vehicle " . $newVehicle->makeModel . " has been added successfully."
            ], ResponseCode["Created"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function updateVehicle(VehicleUpdateRequest $request, MemVehicleService $memVehicleService, $owner_id, $id)
    {
        try {
            $makeModel      = $request->makeModel;
            $plate          = $request->plate;
            $vehicleLength  = $request->vehicleLength;

            $vehicle = $memVehicleService->getVehicle($owner_id, $id)
                ->where('isDeleted', 0)
                ->first();

            if ($vehicle) {
                $vehicle = $memVehicleService->updateVehicle($owner_id, $makeModel, $plate, $vehicleLength, $vehicle);
                Log::info("Update vehicle ID:" . $vehicle->id);

                return response()->json([
                    'success'  => true,
                    'data'     => $vehicle,
                    'message'  => "The vehicle has been updated successfully."
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "The vehicle you want to update is not found."
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

    public function destroy(MemVehicleService $memVehicleService, $owner_id, $id)
    {
        try {
            $vehicle = $memVehicleService->getVehicle($owner_id, $id)
                ->where('isDeleted', 0)
                ->first();

            if ($vehicle) {
                $memVehicleService->deleteVehicle($owner_id, $vehicle);
                Log::info("Delete vehicle ID:" . $vehicle->id);

                return response()->json([
                    'success'  => true,
                    'message'  => "The vehicle has been deleted successfully."
                ], ResponseCode["Success"]);
            } else {
                return response()->json([
                    'error'    => true,
                    'message'  => "The vehicle you are trying to delete is not found."
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
