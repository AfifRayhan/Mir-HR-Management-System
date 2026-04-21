<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterTime extends Model
{
    protected $fillable = [
        'group_slug',
        'shift_key',
        'display_label',
        'start_time',
        'end_time',
        'badge_class',
        'is_off_day',
        'is_overnight',
    ];

    protected $casts = [
        'is_off_day' => 'boolean',
        'is_overnight' => 'boolean',
    ];

    public function scopeForGroup($query, $groupSlug)
    {
        return $query->where('group_slug', $groupSlug);
    }
}
