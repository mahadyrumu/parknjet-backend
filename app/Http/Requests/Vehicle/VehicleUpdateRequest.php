<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
 

class VehicleUpdateRequest extends FormRequest
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
            'makeModel' => ['required', 'max:255'],
            'plate' => [
                'required',
                'max:10',
                'unique:backend_mysql.mem_vehicle,plate,' . $this->route('id')
            ],
            'vehicleLength' => [
                'required',
                Rule::in(["STANDARD", "LARGE", "EXTRA_LARGE"])
            ],
        ];
    }

    public function messages()
    {
        return [
            'makeModel.required'        => 'Make and Model is required',
            'plate.required'            => 'License Plate is required',
            'plate.unique'              => 'This License Plate Already Taken.',
            'vehicleLength.required'    => 'Vehicle Length is required',
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
