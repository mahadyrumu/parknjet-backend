<?php

namespace App\Http\Requests\Auth;

use App\Models\Backend\MemUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class RegisterRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'phone'     => ['max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:' . MemUser::class . ',user_name'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages()
    {
        return [
            'name.required'      => 'Name is required',
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
}
