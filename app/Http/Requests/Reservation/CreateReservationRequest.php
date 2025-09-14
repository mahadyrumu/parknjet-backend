<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
 

class CreateReservationRequest extends FormRequest
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
            'doDate' => 'required|date',
            'puDate' => 'required|date|after:doDate',
            'pref' => 'required|'. Rule::in(["VALET", "SELF"]),
            'vehicleLength' => 'required|'. Rule::in(["STANDARD", "LARGE", "EXTRA_LARGE"]),
            'lot' => 'required|'. Rule::in(["LOT_1", "LOT_2"]),
            'driverId' => 'required_without_all:email,fullName,phone',
            'email' => 'required_without:driverId',
            'fullName' => 'required_without:driverId',
            'phone' => 'required_without:driverId',
            'vehicleId' => 'required_without_all:makeModel,licensePlate',
            'makeModel' => 'required_without:vehicleId',
            'licensePlate' => 'required_without:vehicleId',
            'pricing' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'doDate.required'   => 'Check-in date is required',
            'puDate.required'   => 'Check-out date is required',
            'doDate.date'       => 'Check-in date is not a valid date.',
            'puDate.date'       => 'Check-out date is not a valid date.',
            'puDate.after'      => 'Check-out date must be a date after Check-in date.',
            'pref.required'     => 'Parking type is required.',
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
