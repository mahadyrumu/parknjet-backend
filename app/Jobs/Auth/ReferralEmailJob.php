<?php

namespace App\Jobs\Auth;

use App\Mail\ReferralEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ReferralEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $full_name;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $full_name)
    {
        $this->email = $email;
        $this->full_name = $full_name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new ReferralEmail($this->full_name));
    }
}
