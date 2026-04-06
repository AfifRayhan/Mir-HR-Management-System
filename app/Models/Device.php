<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ip_address', 'port', 'location', 'device_uid', 'last_sync_at'];

    public function logs()
    {
        return $this->hasMany(DeviceLog::class);
    }
}
