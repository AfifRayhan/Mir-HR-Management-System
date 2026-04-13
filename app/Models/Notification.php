<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'read_at',
        'source_id',
        'source_type',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Scope to only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * The user who receives this notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The source model that generated this notification (e.g. Notice).
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Return a Bootstrap icon class based on notification type.
     */
    public function iconClass(): string
    {
        return match($this->type) {
            'leave_request'       => 'bi-calendar-check text-warning',
            'attendance_request'  => 'bi-clock-history text-info',
            'leave_decision'      => 'bi-check-circle text-success',
            'attendance_decision' => 'bi-check2-square text-success',
            'supervisor_remark'   => 'bi-chat-left-text text-danger',
            'notice'              => 'bi-megaphone text-primary',
            default               => 'bi-bell text-secondary',
        };
    }

    /**
     * Return a colour class for the left border based on notification type.
     */
    public function borderColorClass(): string
    {
        return match($this->type) {
            'leave_request', 'leave_decision'             => 'border-warning',
            'attendance_request', 'attendance_decision'   => 'border-info',
            'supervisor_remark'                           => 'border-danger',
            'notice'                                      => 'border-primary',
            default                                       => 'border-secondary',
        };
    }
}
