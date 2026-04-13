<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;

class NotificationService
{
    /**
     * Notify the reporting manager, department head (incharge), and all HR Admin users
     * when an employee submits a request.
     */
    public static function notifyManagers(
        Employee $employee,
        string   $type,
        string   $title,
        string   $message,
        string   $url,
        string   $hrUrl = null
    ): void {
        $recipientUserIds = collect();

        // 1. Reporting manager
        if ($employee->reporting_manager_id) {
            $manager = Employee::find($employee->reporting_manager_id);
            if ($manager && $manager->user_id) {
                $recipientUserIds->push($manager->user_id);
            }
        }

        // 2. Department head (incharge)
        if ($employee->department_id) {
            $dept = Department::find($employee->department_id);
            if ($dept && $dept->incharge_id) {
                $incharge = Employee::find($dept->incharge_id);
                if ($incharge && $incharge->user_id) {
                    $recipientUserIds->push($incharge->user_id);
                }
            }
        }

        // 3. All HR Admin users (role named "HR Admin")
        $hrRole = Role::where('name', 'HR Admin')->first();
        if ($hrRole) {
            $hrUserIds = User::where('role_id', $hrRole->id)
                ->where('status', 'active')
                ->pluck('id');
            $recipientUserIds = $recipientUserIds->merge($hrUserIds);
        }

        // Exclude the employee themselves (in case they are a manager/HR)
        if ($employee->user_id) {
            $recipientUserIds = $recipientUserIds->filter(fn($id) => $id !== $employee->user_id);
        }

        foreach ($recipientUserIds->unique() as $userId) {
            $finalUrl = $url;

            // If an HR URL was provided, check if the recipient is an HR Admin
            if ($hrUrl && $hrRole) {
                $user = User::find($userId);
                if ($user && $user->role_id === $hrRole->id) {
                    $finalUrl = $hrUrl;
                }
            }

            Notification::create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'url'     => $finalUrl,
            ]);
        }
    }

    /**
     * Notify a single employee (via their linked user account).
     */
    public static function notifyEmployee(
        Employee $employee,
        string   $type,
        string   $title,
        string   $message,
        string   $url
    ): void {
        if (!$employee->user_id) {
            return;
        }

        Notification::create([
            'user_id' => $employee->user_id,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'url'     => $url,
        ]);
    }

    /**
     * Notify ALL active employees who have a linked user account.
     * Used for company-wide notices and events.
     */
    public static function notifyAllEmployees(
        string $type,
        string $title,
        string $message,
        string $url
    ): void {
        $userIds = Employee::where('status', 'active')
            ->whereNotNull('user_id')
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'url'     => $url,
            ]);
        }
    }
}
