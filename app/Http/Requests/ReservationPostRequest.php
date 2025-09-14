<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fullName'      => 'required|max:255',
            'phone'         => 'required|max:255',
            'email'         => 'nullable|email',
            'amount'        => 'required',
            'parkingLot'    => 'required',
        ];
    }
    public function messages()
    {
        return [
            'fullName.required'     => 'Provide Driver Full Name.',
            'phone.required'        => 'Provide Driver Phone Number.',
            'email.email'           => 'Provide Valid Email Address.',
            'amount.required'       => 'Provide Amount.',
            'parkingLot.required'   => 'Select Parking Lot.',
        ];
    }
}
