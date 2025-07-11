<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // adjust as needed (e.g. policies)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'make'  => ['sometimes', 'string', 'max:50'],
            'model' => ['sometimes', 'string', 'max:50'],
            'color' => ['sometimes', 'string', 'max:20'],
        ];
    }
}
