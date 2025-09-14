<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class ReservationFinderRequest extends FormRequest
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
            'driverEmail'    => ['required', 'string', 'email', 'max:255'],
            'reservationId'  => ['required', 'max:255'],
            'dropOffDate'    => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'driverEmail.required'    => 'Email is required',
            'driverEmail.email'       => 'Email format is not valid',
            'reservationId.required'  => 'Reservation ID is required',
            'dropOffDate.required'    => 'Check in date is required',
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
