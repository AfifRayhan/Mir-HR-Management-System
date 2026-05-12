<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    
    protected $_isReportingManager = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'user_menu_item');
    }

    /**
     * Check if the user's role has access to a given menu item by slug.
     * HR Admin role always has full access.
     */
    public function hasMenuAccess(string $slug): bool
    {
        $role = $this->role;

        // Slugs specifically for Team Leads / Reporting Managers
        $teamLeadOnlySlugs = [
            'team-lead-leave-request',
            'team-leave',
            'team-lead-leave-apps',
            'team-lead-leave-history',
            'team-lead-remarks',
            'team-lead-attendance-approvals',
        ];

        // Slugs that should be hidden for Team Leads (use TL counterparts instead)
        $employeeOnlySlugs = [
            'employee-leave-request',
        ];

        if ($this->isReportingManager()) {
            if (in_array($slug, $teamLeadOnlySlugs)) {
                return true;
            }
            if (in_array($slug, $employeeOnlySlugs)) {
                return false;
            }
        }

        // Check role permissions
        $hasRoleAccess = $role ? $role->menuItems()->where('slug', $slug)->exists() : false;

        if ($hasRoleAccess) {
            return true;
        }

        // Check individual user permissions
        return $this->menuItems()->where('slug', $slug)->exists();
    }

    /**
     * Check if the user is a reporting manager (has direct reports or is a department head).
     */
    public function isReportingManager(): bool
    {
        if ($this->_isReportingManager !== null) {
            return $this->_isReportingManager;
        }

        $employee = $this->employee;
        if (!$employee) {
            return $this->_isReportingManager = false;
        }

        $hasReports = $employee->directReports()->exists();
        $isDeptHead = \App\Models\Department::where('incharge_id', $employee->id)->exists();

        return $this->_isReportingManager = ($hasReports || $isDeptHead);
    }

    public function approvedLeaveApplications()
    {
        return $this->hasMany(LeaveApplication::class, 'approved_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
