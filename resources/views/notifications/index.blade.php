@php
    use App\Models\Employee;
    $user = auth()->user();
    $roleName = optional($user?->role)->name ?? 'Unassigned';
    $employee = $user ? Employee::where('user_id', $user->id)->first() : null;
    $isHrAdmin = $roleName === 'HR Admin';
    $isTeamLead = $roleName === 'Team Lead';
    $isReportingManager = $employee ? \App\Models\Employee::where('reporting_manager_id', $employee->id)->exists() : false;
    $useHrLayout = $isHrAdmin;
    $useTeamLeadLayout = ($isTeamLead || $isReportingManager) && !$isHrAdmin;
@endphp

<x-app-layout>
    @push('styles')
    @if($useHrLayout)
        
    @else
        
    @endif
    <style>
        /* ── Notification page specific styles ── */
        .notif-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .notif-page-header h5 {
            margin: 0;
            font-weight: 700;
        }

        .notif-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(16,185,129,.08);
            overflow: hidden;
        }

        /* Section header inside the card */
        .notif-card-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
        }
        .notif-card-header .title {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .notif-card-header .badge-unread {
            background: rgba(255,255,255,.25);
            color: #fff;
            font-size: .72rem;
            font-weight: 600;
            padding: .15rem .55rem;
            border-radius: 999px;
        }
        .btn-mark-all {
            background: rgba(255,255,255,.15);
            border: 1.5px solid rgba(255,255,255,.5);
            color: #fff;
            border-radius: .5rem;
            padding: .35rem .8rem;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }
        .btn-mark-all:hover { background: rgba(255,255,255,.3); color: #fff; }

        /* individual notification item */
        .notif-item {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            border-left: 4px solid transparent;
            padding: .9rem 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: .9rem;
            transition: background .15s;
            cursor: pointer;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #f8fafc; }
        .notif-item.unread { background: #f0fdf4; }
        .notif-item.unread:hover { background: #dcfce7; }

        .notif-item.type-leave_request,
        .notif-item.type-leave_decision     { border-left-color: #f59e0b; }
        .notif-item.type-attendance_request,
        .notif-item.type-attendance_decision{ border-left-color: #06b6d4; }
        .notif-item.type-supervisor_remark  { border-left-color: #ef4444; }
        .notif-item.type-notice             { border-left-color: #6366f1; }

        .notif-icon-wrap {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .icon-bg-leave_request,
        .icon-bg-leave_decision      { background: #fef9c3; }
        .icon-bg-attendance_request,
        .icon-bg-attendance_decision { background: #e0f2fe; }
        .icon-bg-supervisor_remark   { background: #fee2e2; }
        .icon-bg-notice              { background: #ede9fe; }

        .notif-body { flex: 1; min-width: 0; }
        .notif-title {
            font-size: .86rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 .12rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .notif-item.unread .notif-title { color: #065f46; }
        .notif-msg  { font-size: .78rem; color: #64748b; margin: 0 0 .2rem; line-height: 1.4; }
        .notif-time { font-size: .7rem; color: #94a3b8; }

        .notif-dot {
            flex-shrink: 0;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-top: .3rem;
        }

        .notif-empty { padding: 3.5rem 2rem; text-align: center; color: #94a3b8; }
        .notif-empty i { font-size: 2.5rem; display: block; margin-bottom: .5rem; }
    </style>
    @endpush

    {{-- ── Layout wrapper ── --}}
    <div class="{{ $useHrLayout ? 'ui-layout' : 'ui-layout' }}">

        {{-- Sidebar --}}
        @if($useHrLayout)
            @include('partials.ui-sidebar')
        @elseif($useTeamLeadLayout)
            @include('partials.team-lead-sidebar')
        @else
            @include('partials.employee-sidebar')
        @endif

        <main class="{{ $useHrLayout ? 'ui-main' : 'ui-main' }}">

            {{-- Page header --}}
            <div class="notif-page-header">
                <div>
                    <h5><i class="bi bi-bell-fill me-2 text-success"></i>Notifications</h5>
                    <p class="mb-0 small text-muted">
                        {{ $employee?->name ?? $user?->name }}
                        @if($employee) • {{ $employee->designation?->name ?? 'No Designation' }} @endif
                    </p>
                </div>
                <div class="text-end text-sm text-gray-500">
                    <i class="bi bi-calendar-event me-2 text-success"></i>{{ now()->format('l, d M Y') }}
                </div>
            </div>

            {{-- Notification card --}}
            <div class="notif-card">

                {{-- Card header --}}
                <div class="notif-card-header">
                    <div class="title">
                        <i class="bi bi-bell-fill"></i>
                        All Notifications
                        @if($unreadCount > 0)
                            <span class="badge-unread">{{ $unreadCount }} unread</span>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn-mark-all">
                                <i class="bi bi-check2-all"></i> Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Notification list --}}
                @forelse($notifications as $notif)
                    @php
                        $iconMap = [
                            'leave_request'       => 'bi-calendar-plus text-warning',
                            'attendance_request'  => 'bi-clock-history text-info',
                            'leave_decision'      => 'bi-check-circle text-success',
                            'attendance_decision' => 'bi-check2-square text-success',
                            'supervisor_remark'   => 'bi-chat-left-text text-danger',
                            'notice'              => 'bi-megaphone text-primary',
                        ];
                        $icon = $iconMap[$notif->type] ?? 'bi-bell text-secondary';
                    @endphp
                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}" style="display:contents;">
                        @csrf
                        <button type="submit"
                                class="notif-item type-{{ $notif->type }} {{ $notif->isUnread() ? 'unread' : '' }}">

                            {{-- Type icon --}}
                            <div class="notif-icon-wrap icon-bg-{{ $notif->type }}">
                                <i class="bi {{ $icon }}"></i>
                            </div>

                            {{-- Body --}}
                            <div class="notif-body">
                                <p class="notif-title" title="{{ $notif->title }}">{{ Str::limit($notif->title, 80) }}</p>
                                @if($notif->message)
                                    <p class="notif-msg" title="{{ $notif->message }}">{{ Str::limit($notif->message, 120) }}</p>
                                @endif
                                <span class="notif-time">
                                    <i class="bi bi-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>

                            {{-- Unread indicator dot --}}
                            @if($notif->isUnread())
                                <div class="notif-dot"></div>
                            @endif
                        </button>
                    </form>
                @empty
                    <div class="notif-empty">
                        <i class="bi bi-bell-slash"></i>
                        <p class="fw-semibold mb-1">You're all caught up!</p>
                        <p class="small mb-0">No notifications yet.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($notifications->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            @endif

        </main>
    </div>
</x-app-layout>




