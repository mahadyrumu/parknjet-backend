<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\GeneratePassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class PasswordController extends Controller
{
    use GeneratePassword;

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!$this->matches($request->current_password, auth()->user()->password)) {
            return back()->withInput($request->only('current_password'))
            ->withErrors(['current_password' => 'The current password does not match']);
        }

        $request->user()->update([
            'password' => $this->encode($request['password']),
        ]);

        return back()->with('success', 'Your password has been changed.');
    }
}
