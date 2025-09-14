<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\Auth\ChangeEmailJob;
use App\Models\Backend\EmailChangeHistory;
use App\Traits\GeneratePassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use GeneratePassword;
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        if (!$this->matches($request->password, auth()->user()->password)) {
            return Redirect::route('profile.edit')->with('error', "Woops! your password didn't match");
        } else {
            try {
                $user = $request->user();
                $previous_email = $user->user_name;

                if ($request->user()->isDirty('user_name')) {
                    $request->user()->isVerified = 0;
                }

                $user->full_name = $request->full_name;
                $user->user_name = $request->user_name;
                $user->phone = $request->phone;
                $user->save();

                if ($previous_email != $request->user_name) {
                    $emailChangeHistory = new EmailChangeHistory();
                    $emailChangeHistory->previous_email = $previous_email;
                    $emailChangeHistory->new_email = $user->user_name;
                    $emailChangeHistory->save();

                    ChangeEmailJob::dispatch($user, $previous_email);
                    // Mail::to($previous_email)->send(new ChangeEmail($user, $previous_email));
                }

                return Redirect::route('profile.edit')->with('success', 'Your profile information successfully updated.');
            } catch (\Exception $e) {
                return Redirect::route('profile.edit')->with('error', 'Woops! Something went wrong');
            }
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('index');
    }
}
