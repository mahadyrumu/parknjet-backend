<?php

namespace App\Http\Requests\Driver;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class DriverCreateRequest extends FormRequest
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
    public function rules()
    {
        return [
            'full_name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email',
                'unique:backend_mysql.mem_driver,email,' . $this->email . ',id,owner_id,' . $this->route('owner_id') . ',isDeleted,0'
            ],
            'phone' => ['required', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'  => 'Full name is required',
            'email.required'      => 'Email is required',
            'email.email'         => 'Email format is not valid',
            'email.unique'        => 'This Email Already Taken.',
            'phone.required'      => 'Phone number is required',
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
