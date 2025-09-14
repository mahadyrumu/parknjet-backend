<?php

namespace App\Http\Controllers\PNJ\API\Sync;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Reservation\ReservationService;
use App\Services\Sync\LotMemberReservationSyncService;
use App\Models\Backend\MemUser;
use App\Exceptions\NotFoundException;
use App\Exceptions\PNJException;
use Illuminate\Http\Request; 
use App\Jobs\Auth\ReservationChangedEmailJob;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ResyncController extends Controller
{

    private $reservationService;

    public function __construct(
        ReservationService $reservationService,
    ) {
        $this->reservationService = $reservationService;
    }

    public function resync(Request $request)
    {
        try {
            $reservationId  = $request->reservationId;
            
            $reservation = $this->reservationService
            ->getAnonReservation()
            ->where('id', $reservationId)
            ->first();

            if($reservation == null){
                $reservation = $this->reservationService
                ->getMemReservation()
                ->where('id', $reservationId)
                ->first();
            }
            
            $user = MemUser::where('id', $reservation->owner_id)
            ->first();

            $lotMemberReservationSyncService = new LotMemberReservationSyncService;
            $lotMemberReservationSyncService->syncReservationToLot($reservation, $user);

            return response()->json([
                'success'  => true,
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

    public function sendChangeEmail(Request $request)
    {
        try {
            $reservationId  = $request->reservationId;
            $email = $request->email;
            $reservation = $this->reservationService
            ->getAnonReservation()
            ->where('id', $reservationId)
            ->first();

            $qrcode = base64_encode(QrCode::size(200)
            ->format('png')
            ->generate(
                $reservation->id,
            ));    
            ReservationChangedEmailJob::dispatch($email, $qrcode, $reservation);

            return response()->json([
                'success'  => true,
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
