<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveBalance;
use App\Models\LeaveApplication;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'office_id',
        'total_days_per_year',
        'max_consecutive_days',
        'carry_forward',
        'sort_order',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function applications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
}
