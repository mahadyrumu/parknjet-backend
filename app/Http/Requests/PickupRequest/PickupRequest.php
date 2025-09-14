<?php

namespace App\Http\Requests\PickupRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
 

class PickupRequest extends FormRequest
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
            'claimId'  => ['required', 'max:255'],
            'phone'    => ['max:255'],
            'minutes'  => ['required'],
            'island'   => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'claimId.required'  => 'Claim ID is required',
            'minutes.required'  => 'Minutes is required',
            'island.required'   => 'Island is required',
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
