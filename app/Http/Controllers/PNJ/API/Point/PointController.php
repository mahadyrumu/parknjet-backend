<?php

namespace App\Http\Controllers\PNJ\API\Point;

use App\DTO\Point\pointDTO;
use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Services\Point\PointService;
use Illuminate\Support\Facades\Log;


class PointController extends Controller
{
    public function getPrepaidDays(PointService $pointService, pointDTO $pointDTO, $owner_id)
    {
        try {
            $userDetails = $pointService->getPrepaidDays()
                ->find($owner_id);

            $prepaidDays = $pointDTO->prepaidDays($userDetails);
            Log::info("Get prepaid days");

            return response()->json([
                'success'  => true,
                'data'     => $prepaidDays
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }

    public function getPointsAndDays(PointService $pointService, pointDTO $pointDTO, $owner_id)
    {
        try {
            $userDetails = $pointService->getPointsAndDays()
                ->find($owner_id);

            $pointsDays = $pointDTO->pointsAndDays($userDetails);
            Log::info("Get point days");

            return response()->json([
                'success'  => true,
                'data'     => $pointsDays
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
