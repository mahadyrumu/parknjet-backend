<?php

namespace App\Livewire;

use App\Jobs\Auth\ChangeEmailJob;
use App\Models\Backend\EmailChangeHistory;
use App\Models\Backend\MemUser;
use App\Traits\GeneratePassword;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    use GeneratePassword;

    public $user;
    public $full_name = '';
    public $user_name = '';
    public $phone = '';
    public $password = '';
    public $is_social_auth;

    public function update()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        if ($this->is_social_auth) {
            $this->validate([
                'phone'         => ['max:255'],
            ]);
            try {
                $user = MemUser::find(auth()->user()->id);
                $user->phone = $this->phone;
                $user->save();
                $this->user = $user;
                $this->dispatch('validated');
            } catch (\Exception $e) {
                $this->addError("password-error", $e->getMessage());
            }
        } else {
            $this->validate([
                'full_name'      => ['required', 'string', 'max:255'],
                'user_name' => ['required', 'string', 'max:255', 'unique:' . MemUser::class . ',user_name'],
            ]);

            if (!$this->matches($this->password, auth()->user()->password)) {
                $this->addError("password-error", "Incorrect password!");
            } else {
                try {
                    $user = MemUser::find(auth()->user()->id);
                    $previous_email = $user->user_name;
                    $user->full_name = $this->full_name;
                    $user->isVerified = 0;
                    $user->user_name = $this->user_name;
                    $user->phone = $this->phone;
                    $user->save();

                    if ($previous_email != $this->user_name) {
                        $emailChangeHistory = new EmailChangeHistory();
                        $emailChangeHistory->previous_email = $previous_email;
                        $emailChangeHistory->new_email = $user->user_name;
                        $emailChangeHistory->save();

                        ChangeEmailJob::dispatch($user, $previous_email);
                        // Mail::to($previous_email)->send(new ChangeEmail($user, $previous_email));
                    }
                    $this->user = $user;
                    $this->password = '';
                    $this->addError("success", "Your profile information successfully updated.!");
                    $this->dispatch('validated');
                } catch (\Exception $e) {
                    $this->addError("password-error", $e->getMessage());
                }
            }
        }
    }

    public function render()
    {
        $this->user = MemUser::find(auth()->user()->id);
        $this->full_name = $this->user->full_name;
        $this->user_name = $this->user->user_name;
        $this->phone = $this->user->phone;

        $user = Auth::user();
        if ($user->is_google_auth == 1 || $user->is_meta_auth == 1 || $user->is_apple_auth == 1) {
            $this->is_social_auth = true;
        } else {
            $this->is_social_auth = false;
        }

        return view('livewire.profile');
    }
}
