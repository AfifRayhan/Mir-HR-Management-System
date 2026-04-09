<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $unreadNotificationCount = 0;

        if (Auth::check()) {
            $unreadNotificationCount = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count();
        }

        $view->with('unreadNotificationCount', $unreadNotificationCount);
    }
}
