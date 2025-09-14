<?php

namespace App\Jobs\Auth;

use App\Mail\ReservationChangedEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ReservationChangedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_name;
    protected $qrcode;
    protected $reservation;

    /**
     * Create a new job instance.
     */
    public function __construct($user_name, $qrcode, $reservation)
    {
        $this->user_name = $user_name;
        $this->qrcode = $qrcode;
        $this->reservation = $reservation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user_name)->send(new ReservationChangedEmail($this->qrcode, $this->reservation));
    }
}
