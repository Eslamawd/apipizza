<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'logo',
        'token',
        'cover',
        'phone',
        'address',
        'latitude',
        'longitude',
        'qr_code',
    ];

          public function getCoverAttribute($value)
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }
          public function getLogoAttribute($value)
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

          public function getQrCodeAttribute($value)
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
       public static function boot()
    {
        parent::boot();

        static::creating(function ($restaurant) {
            // توليد توكين فريد عند إنشاء الاشتراك
            $restaurant->token = (string) Str::random(64); 
            // يمكن استخدام Str::random(64) بدلاً من UUID
        });
    }

    public function kitchens()
{
    return $this->hasMany(Kitchen::class);
}

public function cashiers()
{
    return $this->hasMany(Cashier::class);
}
public function getLogoPathAttribute()
{
    if (!$this->attributes['logo']) return null;

    return storage_path('app/public/' . $this->attributes['logo']);
}
public function inventoryItems()
{
    return $this->hasMany(InventoryItem::class);


}

public function links()
{
    return $this->hasOne(RestaurantLinks::class);
}

}
