<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class PaymentIntentRequest extends FormRequest
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
            'email'  => ['required', 'email'],
            'amount'  => ['required'],
            'lotType'  => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'    => 'Email is required',
            'email.email'       => 'Email format is invalid',
            'amount.required'   => 'Amount is required',
            'lotType.required'  => 'Lot type is required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => true,
            'message' => $validator->errors(),
        ], ResponseCode["Unprocessable Content"]));
    }
}
