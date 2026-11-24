<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'in:draft,active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The business name is required.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
