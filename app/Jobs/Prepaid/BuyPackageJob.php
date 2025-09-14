<?php

namespace App\Jobs\Prepaid;

use App\Mail\ReservationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BuyPackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $blade;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $blade)
    {
        $this->data     = $data;
        $this->blade    = $blade;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Started buy prepaid package mail queue");
            Mail::to($this->data->to)->send(new ReservationEmail($this->data, $this->blade));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            Log::error("Failed to send mail in email " . $this->data->to);
        }
    }
}
