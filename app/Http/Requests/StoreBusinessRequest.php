<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'operating_hours' => ['nullable', 'string', 'max:1000'],
            'products' => ['nullable', 'string', 'max:2000'],
            'services' => ['nullable', 'string', 'max:2000'],
            'additional_info' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a business name.',
            'name.max' => 'The business name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
