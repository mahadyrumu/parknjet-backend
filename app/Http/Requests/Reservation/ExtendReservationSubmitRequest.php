<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class ExtendReservationSubmitRequest extends FormRequest
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
            'puDate'        => 'required',
            'puTime'        => 'required',
            'pricing'       => 'required',
            'stripeToken'   => 'required_without:paymentId',
            'paymentId'     => 'required_without:stripeToken',
        ];
    }

    public function messages()
    {
        return [
            'puDate.required'  => 'New checkout date is required',
            'puTime.required'  => 'New checkout time is required',
            'stripeToken.required_without'  => 'No payment record is found',
            'paymentId.required_without'    => 'No payment record is found',
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
