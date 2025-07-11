<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnparkVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // allow everyone to unpark for now.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // no extra fields are needed.
        return [];
    }
}
