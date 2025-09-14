<?php

namespace App\Console\Commands;

use App\Models\Backend\AnonReservationPendingSync;
use App\Models\Backend\MemReservationPendingSync;
use App\Models\Backend\MemUser;
use App\Services\Reservation\ReservationService;
use App\Services\Sync\LotMemberReservationSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedSyncJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry:failed-sync-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed Sync request jobs';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $this->info('Failed Sync Reservation jobs Start.');

        $reservationService = new ReservationService;
        $memFailedJobs = MemReservationPendingSync::all();
        Log::info("Total failed Sync Member Reservation: " . count($memFailedJobs));

        foreach ($memFailedJobs as $job) {
            Log::info("Retry failed Sync Member Reservation ID " . $job->reservation_id);
            try {
                // Dispatch the job again
                $reservation = $reservationService
                    ->getMemReservation()
                    ->where('id', $job->reservation_id)
                    ->first();

                if ($reservation != null) {
                    $user = MemUser::where('id', $reservation->owner_id)
                        ->first();
                    if ($user != null) {
                        Log::info("Found failed Sync Member Reservation & User, sending to sync job " . $job->reservation_id);

                        $lotMemberReservationSyncService = new LotMemberReservationSyncService;
                        $lotMemberReservationSyncService->syncReservationToLot($reservation, $user);

                        // Delete from failed jobs table after re-queuing
                        Log::info("Deleting failed Sync Member Reservation " . $job->reservation_id);
                        $job->delete();
                    }
                } else {
                    Log::info("Not Found failed Sync Member Reservation, sending to sync job " . $job->reservation_id);
                }
            } catch (\Exception $e) {
                Log::error("Failed Sync Member Reservation job ID: {$job->id}. Error: {$e->getMessage()}");
            }
        }

        $anonFailedJobs = AnonReservationPendingSync::all();
        Log::info("Total failed Sync Anon Reservation: " . count($anonFailedJobs));

        foreach ($anonFailedJobs as $job) {
            Log::info("Retry failed Sync Anon Reservation ID " . $job->reservation_id);
            try {
                // Dispatch the job again
                $reservation = $reservationService
                    ->getAnonReservation()
                    ->where('id', $job->reservation_id)
                    ->first();

                if ($reservation != null) {
                    Log::info("Found failed Sync Anon Reservation, sending to sync job " . $job->reservation_id);

                    $lotMemberReservationSyncService = new LotMemberReservationSyncService;
                    $lotMemberReservationSyncService->syncReservationToLot($reservation, null);

                    // Delete from failed jobs table after re-queuing
                    Log::info("Deleting failed Sync Anon Reservation " . $job->reservation_id);
                    $job->delete();
                } else {
                    Log::info("Not Found failed Sync Anon Reservation, sending to sync job " . $job->reservation_id);
                }
            } catch (\Exception $e) {
                Log::error("Failed Sync Anon Reservation job ID: {$job->id}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Failed Sync Reservation jobs End.');
    }
}
