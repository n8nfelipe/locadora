<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:vehicle_categories'],
            'description' => ['nullable', 'string'],
            'daily_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
