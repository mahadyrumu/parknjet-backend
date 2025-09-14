<?php

namespace App\Http\Controllers\PNJ\API\Admin;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Report\GeneralReportResource;
use App\Services\Report\ReportsService;
use App\Services\Reservation\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminReportController extends Controller
{
    public function generalReports(Request $request, ReportsService $reportsService)
    {
        try {
            $generalReport = $reportsService->generateGeneralReports($request);
            return GeneralReportResource::collection($generalReport);
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
    }
}
