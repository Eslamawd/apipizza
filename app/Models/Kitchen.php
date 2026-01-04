<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kitchen extends Model
{
    //
    
        protected $fillable = [
      
        'restaurant_id',
        'token',
        'qr_code',
    ];

           public static function boot()
    {
        parent::boot();

        static::creating(function ($kitchen) {
            // توليد توكين فريد عند إنشاء الاشتراك
            $kitchen->token = (string) Str::random(64); 
            // يمكن استخدام Str::random(64) بدلاً من UUID
        });
    }

      public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    
          public function getQrCodeAttribute($value)
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }
}
