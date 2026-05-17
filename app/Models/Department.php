<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('departments_all');
            \Illuminate\Support\Facades\Cache::forget('departments_ordered_all');
            \Illuminate\Support\Facades\Cache::forget('sections_with_dept_all');
        });

        static::deleted(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('departments_all');
            \Illuminate\Support\Facades\Cache::forget('departments_ordered_all');
            \Illuminate\Support\Facades\Cache::forget('sections_with_dept_all');
        });
    }

    protected $fillable = [
        'name',
        'short_name',
        'incharge_id',
        'description',
        'order_sequence',
    ];

    public function incharge()
    {
        return $this->belongsTo(Employee::class, 'incharge_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
