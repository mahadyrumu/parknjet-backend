<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class UpdateProfileRequest extends FormRequest
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
            'full_name'  => ['required', 'string', 'max:255'],
            'user_name'  => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:backend_mysql.mem_user,user_name,' . $this->route('id')
            ],
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'  => 'Full name is required',
            'user_name.required'  => 'Email is required',
            'user_name.email'     => 'Email format is not valid',
            'user_name.unique'    => 'This Email Already Taken.',
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
