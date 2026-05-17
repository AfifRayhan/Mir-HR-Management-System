<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('designations_all');
            \Illuminate\Support\Facades\Cache::forget('designations_ordered_all');
            \Illuminate\Support\Facades\Cache::forget('designations_ordered_name_all');
        });

        static::deleted(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('designations_all');
            \Illuminate\Support\Facades\Cache::forget('designations_ordered_all');
            \Illuminate\Support\Facades\Cache::forget('designations_ordered_name_all');
        });
    }

    protected $fillable = [
        'name',
        'short_name',
        'priority',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
