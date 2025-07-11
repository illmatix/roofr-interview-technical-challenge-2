<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParkVehicleRequest extends FormRequest
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
            'vehicle_type'  => ['required', 'in:motorcycle,car,van'],
            'vehicle_id'    => ['nullable', 'exists:vehicles,id'],
            'license_plate' => ['required_without:vehicle_id', 'string', 'max:10'],
            'make'          => ['nullable', 'string', 'max:50'],
            'model'         => ['nullable', 'string', 'max:50'],
            'color'         => ['nullable', 'string', 'max:20'],
        ];
    }
}
