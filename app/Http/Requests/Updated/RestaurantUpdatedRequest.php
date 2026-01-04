<?php

namespace App\Http\Requests\Updated;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantUpdatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // اسمح بالتحديث (ممكن تضيف شرط ملكية لاحقاً)
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:restaurant,coffee',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'logo' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'cover' => 'sometimes|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
