<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtotal_price',
        'discount_amount',
        'discount_percentage',
        'total_price',
        'status',
        'order_type',
        'restaurant_id',
        'user_id',
        'phone',
        'customer_name',
        'tip_percentage',
        'tips',
        'scheduled_date',
        'scheduled_time',
        'scheduled_for',
        'payment_method',
        'address',
        'latitude',
        'longitude',
        'table_id',
    ];

    protected $casts = [
        'subtotal_price' => 'float',
        'discount_amount' => 'float',
        'discount_percentage' => 'float',
        'total_price' => 'float',
        'tip_percentage' => 'float',
        'tips' => 'float',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
