<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'year',
        'title',
        'all_office',
        'office_id',
        'from_date',
        'to_date',
        'total_days',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'all_office' => 'boolean',
        'is_active' => 'boolean',
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
