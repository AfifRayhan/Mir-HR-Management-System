<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRate extends Model
{
    use HasFactory;

    protected $fillable = ['grade_id', 'designation_id', 'rate'];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
}
