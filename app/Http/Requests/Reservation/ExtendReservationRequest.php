<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class ExtendReservationRequest extends FormRequest
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
            'rsvnId'  => ['required'],
            'puDate'  => ['required'],
            'puTime'  => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'rsvnId.required'  => 'Reservation Id is required',
            'puDate.required'  => 'Checkout date is required',
            'puTime.required'  => 'Checkout time is required',
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
