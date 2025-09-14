<?php

namespace App\Http\Requests\Auth;

use App\Traits\CheckUser;
use App\Traits\GeneratePassword;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Exceptions\NotFoundException;
 

class LoginRequest extends FormRequest
{
    use GeneratePassword, CheckUser;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email'     => ['required', 'string', 'email'],
            'password'  => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'     => 'Email is required',
            'email.email'        => 'Email format is not valid',
            'password.required'  => 'Password is required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error'    => true,
            'message'  => $validator->errors(),
        ], ResponseCode["Unprocessable Content"]));
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $user = $this->checkEmail($this->email);

        if (!$user || !$this->matches($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            // return & throw an error that credentials do not match
            throw new NotFoundException(trans('auth.failed'));
        }

        // Login to user that matches the email & password
        Auth::login($user);

        // Store user's last login time 
        $user->lastLoggedIn = now();
        $user->save();

        // set this cookie for validate user from sveltekit application
        // Cookie::queue('user-login', 'Yes', 120, null, null, false, false);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}
