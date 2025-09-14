<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\CheckUser;
use App\Traits\GeneratePassword;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Jobs\Auth\PasswordChangeMailJob;

class NewPasswordController extends Controller
{
    use GeneratePassword, CheckUser;

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $invalidToken = null;
        $verify = $this->checkTokenForPasswordReset($request->email, $request->token);

        if (!($verify->exists())) {
            $invalidToken = $request->token;
        }
        return view('auth.reset-password', ['request' => $request, 'invalidToken' => $invalidToken]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $user = $this->checkEmail($request->user_name);
        $verify = $this->checkTokenForPasswordReset($request->user_name, $request->token);
        if ($user) {
            if ($verify->exists()) {

                $difference = Carbon::now()->diffInSeconds($verify->first()->created_at);
                if ($difference > 172800) {
                    return back()->with('status', __('passwords.token'));
                }

                $user->forceFill([
                    'password' => $this->encode($request->password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));

                $verify->delete();

                PasswordChangeMailJob::dispatch($request->user_name, $user->full_name);
                // Mail::to($request->user_name)->send(new PasswordChangeConfirmationMail($user->full_name));
                
                return redirect(env('APP_URL') . '/login');
            }
            return back()->withInput($request->only('user_name'))
                ->withErrors(['user_name' => trans('passwords.token')]);
        }
        return back()->withInput($request->only('user_name'))
            ->withErrors(['user_name' => trans('passwords.user')]);
    }
}
