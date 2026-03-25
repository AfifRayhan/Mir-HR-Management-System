<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'punch_time',
        'status',
        'device_name',
        'machine_id',
    ];
}
