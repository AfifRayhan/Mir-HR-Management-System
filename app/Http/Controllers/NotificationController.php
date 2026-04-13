<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a full paginated list of the user's notifications.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'user'));
    }

    /**
     * Mark a single notification as read and redirect to its target URL.
     */
    public function markRead(Notification $notification)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security: ensure the notification belongs to the logged-in user
        if ($notification->user_id !== $user->id) {
            abort(403);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        // Redirect logic
        $target = $notification->url ?: route('notifications.index');

        // Smart redirection for notices, attendance, and leave requests
        $roleName = optional($user->role)->name;

        if ($notification->type === 'notice') {
            if ($roleName === 'HR Admin') {
                $target = route('settings.notices.index');
            } else {
                $target = route('employee-dashboard') . '#notices-events';
            }
        } elseif ($notification->type === 'attendance_request') {
            if ($roleName === 'HR Admin') {
                $target = route('personnel.attendances.approvals');
            } else {
                $target = route('team-lead.attendances.approvals');
            }
        } elseif ($notification->type === 'leave_request') {
            if ($roleName === 'HR Admin') {
                $target = route('personnel.leave-applications.index');
            } else {
                $target = route('team-lead.leave-applications.index');
            }
        } elseif ($notification->type === 'supervisor_remark') {
            $target = route('employee-dashboard') . '#supervisor-remarks';
        }

        return redirect($target);
    }

    /**
     * Mark all of the user's notifications as read.
     */
    public function markAllRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
