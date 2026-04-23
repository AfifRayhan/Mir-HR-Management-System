<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_template_type_id',
        'format',
        'content',
        'is_active',
    ];

    public function type()
    {
        return $this->belongsTo(ReportTemplateType::class, 'report_template_type_id');
    }
}
