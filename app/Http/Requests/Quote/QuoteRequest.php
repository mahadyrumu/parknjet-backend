<?php

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
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
        ];
    }
}
