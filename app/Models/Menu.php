<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    //
    
    protected $fillable = [
        'name',
        'name_en',
        'image',
        'restaurant_id',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function categories()
{
    return $this->hasMany(Category::class);
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
