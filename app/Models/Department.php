<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'incharge_id',
        'description',
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
