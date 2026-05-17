<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('sections_all');
            \Illuminate\Support\Facades\Cache::forget('sections_ordered_all');
            \Illuminate\Support\Facades\Cache::forget('sections_with_dept_all');
        });

        static::deleted(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('sections_all');
            \Illuminate\Support\Facades\Cache::forget('sections_ordered_all');
            \Illuminate\Support\Facades\Cache::forget('sections_with_dept_all');
        });
    }

    protected $fillable = [
        'department_id',
        'name',
        'description',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
