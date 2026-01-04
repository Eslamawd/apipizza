<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // استخدام الـ Facade الصحيح

class InventoryItem extends Model
{
    /**
     * الحقول المسموح بتعيين قيم لها بشكل جماعي (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'unit',
        'quantity',
        'total_price',
        // حقول أسعار الوزن
        'unit_price_per_kg', 
        'unit_price_per_gram',
        // حقول أسعار الحجم الجديدة
        'unit_price_per_liter',
        'unit_price_per_ml',
        // حقول أسعار الطول الجديدة
        'unit_price_per_meter',
        'unit_price_per_cm',
        
        'restaurant_id', 
        'received_at',
        'expires_at',
    ];

    /**
     * تحويل التواريخ تلقائياً إلى Carbon Instances.
     */
    protected $dates = [
        'received_at',
        'expires_at',
    ];

    /**
     * العلاقة: صنف المخزون ينتمي إلى مطعم واحد.
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}