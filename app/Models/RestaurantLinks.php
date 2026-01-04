<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantLinks extends Model
{
    //
     protected $fillable = [
        'restaurant_id',
        'google_review',
        'facebook',
        'instagram',
        'tiktok',
        'website',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
