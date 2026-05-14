<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeSpecialRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_group',
        'rate',
        'is_eid_special',
    ];
}
