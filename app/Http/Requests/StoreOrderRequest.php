<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id'             => 'required|exists:restaurants,id',
            'table_id'                  => 'nullable|exists:tables,id',
            'phone'                     => 'nullable|string|max:20',
            'address'                   => 'nullable|string|max:255',
            'latitude'                  => 'nullable|numeric',
            'longitude'                 => 'nullable|numeric',
            'customer_name'             => 'nullable|string|max:100',
            'tip_percentage'            => 'nullable|numeric|min:0|max:100',
            'tips'                      => 'nullable|numeric|min:0',
            'scheduled_date'            => 'nullable|date',
            'scheduled_time'            => 'nullable|date_format:H:i',
            'scheduled_for'             => 'nullable|date',
            'payment_token'             => 'nullable|string',
            'items'                     => 'required|array|min:1',
            'items.*.item_id'           => 'required|exists:items,id',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.comment'           => 'nullable|string|max:255',
            'items.*.options'           => 'nullable|array',
            'items.*.options.*.id'      => 'required|exists:item_options,id',
            'items.*.options.*.position' => 'required|in:whole,right,left',
            'order_type'                => 'required|in:pickup,delivery',
        ];
    }
}
