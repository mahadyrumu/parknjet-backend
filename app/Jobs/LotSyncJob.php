<?php

namespace App\Jobs;

use App\Exceptions\PNJException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LotSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lotSyncURL;
    protected $rsvn;
    protected $reservation;
    protected $reservationPendingSyncService;
    protected $reservationType;

    /**
     * Create a new job instance.
     */
    public function __construct($lotSyncURL, $rsvn, $reservation, $reservationPendingSyncService, $reservationType)
    {
        $this->lotSyncURL = $lotSyncURL;
        $this->rsvn = $rsvn;
        $this->reservation = $reservation;
        $this->reservationPendingSyncService = $reservationPendingSyncService;
        $this->reservationType = $reservationType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("LotSyncJob Started");
        try {
            $response = Http::withHeaders(getNonAuthHTTPHeader())->post($this->lotSyncURL, $this->rsvn);
            if (isError($response)) {
                Log::info("Reservation sync failed with status code " . $response->status());
            }
            Log::info("Reservation id " . $this->reservation->id . " synced with lot with status code " . $response->status());

            if ($response->status() >= 500) {
                throw new PNJException("LotSyncJob failed with " . $response->status());
            }

            $pendingSyncDB = $this->reservationPendingSyncService->findByReservation($this->reservation->id);
            if ($pendingSyncDB != null) {
                $pendingSyncDB->delete();
                Log::info("Member Reservation Pending sync with id " . $pendingSyncDB->id . " deleted after successful sync ");
            }
        } catch (\Exception $exception) {
            Log::info("Reservation with id " . $this->reservation->id . " sync to url " . $this->lotSyncURL);
            Log::info("Reservation sync failed with cause " . $exception->getMessage());

            $pendingSyncDB = $this->reservationPendingSyncService->findByReservation($this->reservation->id);
            if ($pendingSyncDB == null) {
                $pendingSyncDB = $this->reservationPendingSyncService->create($this->reservation->id);
                Log::info($this->reservationType . " with id " . $this->reservation->id . " saved in pending synced record for future sync, Pending id is " . $pendingSyncDB->id);
            } else {
                Log::info($this->reservationType . " with id " . $this->reservation->id . " already waiting to be sync");
            }
        }
    }
}
