<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('offices_all');
            \Illuminate\Support\Facades\Cache::forget('offices_ordered_all');
        });

        static::deleted(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('offices_all');
            \Illuminate\Support\Facades\Cache::forget('offices_ordered_all');
        });
    }

    protected $fillable = [
        'name',
        'office_type_id',
        'address',
        'phone',
        'secondary_phone',
        'email',
        'logo',
        'order_number',
    ];

    public function type()
    {
        return $this->belongsTo(OfficeType::class, 'office_type_id');
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}
