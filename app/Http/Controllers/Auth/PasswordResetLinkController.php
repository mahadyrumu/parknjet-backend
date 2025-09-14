<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\Auth\EmailVerificationJob;
use App\Traits\CheckUser;
use App\Traits\GeneratePassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    use CheckUser, GeneratePassword;
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_name' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.

        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );

        $user = $this->checkEmail($request->only('user_name'));

        if ($user) {
            $verify = $this->checkEmailForPasswordReset($request->only('user_name'));
            if ($verify->exists()) {
                $verify->delete();
            }
            $token = $this->encode($request->user_name);
            $password_reset = $this->insertPasswordReset($request->user_name, $token);
            if ($password_reset) {
                // Mail::to($request->user_name)->send(new ResetPasswordMail($request->user_name, $token));
                EmailVerificationJob::dispatch($request->user_name, $user, $token);

                return back()->with('status', __('passwords.sent'));
                // trans('passwords.sent')
            }
        }
        return back()->withInput($request->only('user_name'))
            ->withErrors(['user_name' => trans('auth.failed')]);

        // return $status == Password::RESET_LINK_SENT
        //     ? back()->with('status', __($status))
        //     : back()->withInput($request->only('user_name'))
        //     ->withErrors(['user_name' => __($status)]);
    }
}
