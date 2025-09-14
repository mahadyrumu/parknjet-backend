<?php

namespace App\Http\Controllers\PNJ\API\ReservationFinder;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\ReservationFinderRequest;
use App\Services\Reservation\ReservationService;
use App\Exceptions\NotFoundException;
use App\Exceptions\PNJException;
 

class ReservationFinderController extends Controller
{
    public function findExistingReservation(ReservationFinderRequest $request, ReservationService $reservationService)
    {
        try {
            $reservationId  = $request->reservationId;
            $driverEmail    = $request->driverEmail;
            $dropOffDate    = $request->dropOffDate;

            $invoiceDetails = $reservationService->reservationShow($reservationId, $driverEmail, $dropOffDate);

            return response()->json([
                'success'  => true,
                'data'     => $invoiceDetails
            ], ResponseCode["Success"]);
        } catch (NotFoundException $e) {
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage()
            ], ResponseCode["Not Found"]);
        } catch (PNJException $e) {
            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage()
            ], ResponseCode["Not Found"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error'    => true,
                'message'  => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
