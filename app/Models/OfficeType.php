<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'order_number',
    ];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
