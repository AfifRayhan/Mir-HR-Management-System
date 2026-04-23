<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplateType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key_tags',
    ];

    public function templates()
    {
        return $this->hasMany(ReportTemplate::class);
    }
}
