<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'price',
        'old_price',
        'image',
        'category_id',
    ];

    
    protected $appends = ['options_grouped'];
   

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(ItemOption::class);
    }
    public function getOptionsGroupedAttribute()
{
    if (!$this->relationLoaded('options')) {
        return collect();
    }

    return $this->options->groupBy('option_type');
}

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

           public function getImageAttribute($value)
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }
}
