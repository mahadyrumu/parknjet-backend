<?php

namespace App\Jobs\Auth;

use App\Mail\ChangeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ChangeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $previous_email;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $previous_email)
    {
        $this->user = $user;
        $this->previous_email = $previous_email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->previous_email)->send(new ChangeEmail($this->user, $this->previous_email));
    }
}
