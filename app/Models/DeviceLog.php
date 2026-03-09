<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['employee_code', 'punch_time', 'device_id'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
