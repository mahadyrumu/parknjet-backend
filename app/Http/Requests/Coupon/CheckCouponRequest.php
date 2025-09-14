<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class CheckCouponRequest extends FormRequest
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
            'lotType'   => ['required'],
            'coupon'    => ['required'],
            'member'    => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'lotType.required'      => 'Lot Type is required',
            'coupon.required'       => 'Coupon is required',
            'member.required'       => 'Member is required',
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
