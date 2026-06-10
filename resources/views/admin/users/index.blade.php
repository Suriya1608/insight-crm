@extends('layouts.app')

@section('page_title', 'User Management')

@php
/* ── Inline SVG icons (no CDN, no font dependency) ── */
$IC = [
    'shield-check'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>',
    'user-cog'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 14.9a2 2 0 0 0 0-2.8l-.9-.9 1-1.7a2 2 0 0 0-2.8-2.8L15 8.6l-1.7-1a2 2 0 0 0-2.8 2.8l.9.9-1 1.7a2 2 0 0 0 2.8 2.8l1.7-1 .9.9a2 2 0 0 0 2.8 0z"/></svg>',
    'headphones'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>',
    'circle-check'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>',
    'bar-chart'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="18" x2="18" y1="20" y2="10" stroke-linecap="round"/><line x1="12" x2="12" y1="20" y2="4" stroke-linecap="round"/><line x1="6" x2="6" y1="20" y2="14" stroke-linecap="round"/></svg>',
    'users'         => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4" stroke-linecap="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'user-plus'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4" stroke-linecap="round"/><line x1="19" x2="19" y1="8" y2="14" stroke-linecap="round"/><line x1="22" x2="16" y1="11" y2="11" stroke-linecap="round"/></svg>',
    'filter'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'search'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8" stroke-linecap="round"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>',
    'refresh-cw'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H3v5"/></svg>',
    'pencil'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m15 5 4 4"/></svg>',
    'log-out'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline stroke-linecap="round" stroke-linejoin="round" points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12" stroke-linecap="round"/></svg>',
    'key-round'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/></svg>',
    'unlock'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>',
    'lock'          => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    'user-x'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4" stroke-linecap="round"/><line x1="17" x2="22" y1="8" y2="13" stroke-linecap="round"/><line x1="22" x2="17" y1="8" y2="13" stroke-linecap="round"/></svg>',
];
function ico($IC, $name, $size = 14) {
    if (!isset($IC[$name])) return '';
    return str_replace('<svg ', '<svg width="'.$size.'" height="'.$size.'" ', $IC[$name]);
}
@endphp

@section('header_actions')
    <a href="{{ route('admin.users.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff!important;border:none;border-radius:8px;font-weight:600;padding:7px 14px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
        {!! ico($IC,'user-plus',14) !!}
        Add User
    </a>
@endsection

@section('content')

@php
    $total = max(1, ($counts['admins'] ?? 0) + ($counts['managers'] ?? 0) + ($counts['telecallers'] ?? 0) + ($counts['report_viewers'] ?? 0));
@endphp

{{-- ── KPI StatRow — full width top ── --}}
<div class="um-kpi-grid mb-3">
    <div class="um-sr um-sr-or">
        <div class="um-sr-icon">{!! ico($IC,'shield-check',15) !!}</div>
        <div><div class="um-sr-lbl">Admin Users</div><div class="um-sr-val">{{ $counts['admins'] ?? 0 }}</div></div>
    </div>
    <div class="um-sr um-sr-wh">
        <div class="um-sr-icon" style="background:#FFFBEB;color:#D97706;">{!! ico($IC,'user-cog',15) !!}</div>
        <div><div class="um-sr-lbl">Managers</div><div class="um-sr-val">{{ $counts['managers'] ?? 0 }}</div></div>
    </div>
    <div class="um-sr um-sr-wh">
        <div class="um-sr-icon" style="background:#ECFDF5;color:#10B981;">{!! ico($IC,'headphones',15) !!}</div>
        <div><div class="um-sr-lbl">Telecallers</div><div class="um-sr-val">{{ $counts['telecallers'] ?? 0 }}</div></div>
    </div>
    <div class="um-sr um-sr-wh">
        <div class="um-sr-icon" style="background:#F0FDF4;color:#16A34A;">{!! ico($IC,'circle-check',15) !!}</div>
        <div><div class="um-sr-lbl">Active Accounts</div><div class="um-sr-val">{{ $counts['active'] ?? 0 }}</div></div>
    </div>
    <div class="um-sr um-sr-wh">
        <div class="um-sr-icon" style="background:#F5F3FF;color:#7C3AED;">{!! ico($IC,'bar-chart',15) !!}</div>
        <div><div class="um-sr-lbl">Report Viewers</div><div class="um-sr-val">{{ $counts['report_viewers'] ?? 0 }}</div></div>
    </div>
</div>

{{-- ── 2-column: filter left | table right ── --}}
<div class="um-body">

    {{-- LEFT — single panel: role tabs + filter ── --}}
    <div class="um-left-panel">

        {{-- Role navigation ── --}}
        <div class="um-panel-head">
            <div class="um-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'users',13) !!}</span>
            <span class="um-panel-title">User Roles</span>
        </div>
        <nav class="um-nav">
            @php
                $roles = [
                    ['route' => route('admin.users.admins'),         'scope' => 'admin',         'label' => 'Admin Users',    'icon' => 'shield-check', 'count' => $counts['admins'] ?? 0,          'color' => '#FF5C00' ],
                    ['route' => route('admin.users.managers'),       'scope' => 'manager',       'label' => 'Managers',       'icon' => 'user-cog',     'count' => $counts['managers'] ?? 0,        'color' => '#D97706' ],
                    ['route' => route('admin.users.telecallers'),    'scope' => 'telecaller',    'label' => 'Telecallers',    'icon' => 'headphones',   'count' => $counts['telecallers'] ?? 0,    'color' => '#10B981' ],
                    ['route' => route('admin.users.report-viewers'), 'scope' => 'report_viewer', 'label' => 'Report Viewers', 'icon' => 'bar-chart',    'count' => $counts['report_viewers'] ?? 0, 'color' => '#7C3AED' ],
                ];
            @endphp
            @foreach($roles as $r)
            <a href="{{ $r['route'] }}"
               class="um-nav-link {{ $scope === $r['scope'] ? 'on' : '' }}"
               style="{{ $scope === $r['scope'] ? 'background:'.$r['color'].';color:#fff;border-color:transparent;box-shadow:0 3px 10px '.$r['color'].'30;' : '' }}">
                <span class="um-nav-ico" style="{{ $scope === $r['scope'] ? 'background:rgba(255,255,255,.20);color:#fff;' : 'background:'.$r['color'].'18;color:'.$r['color'].';' }}">{!! ico($IC,$r['icon'],13) !!}</span>
                <span class="um-nav-lbl">{{ $r['label'] }}</span>
                <span class="um-nav-cnt" style="{{ $scope === $r['scope'] ? 'background:rgba(255,255,255,.25);color:#fff;' : '' }}">{{ $r['count'] }}</span>
            </a>
            @endforeach
        </nav>

        {{-- Divider --}}
        <div style="height:1px;background:#F0F0F0;margin:0 12px;"></div>

        {{-- Filter inputs ── --}}
        <div class="um-panel-head" style="padding-top:10px;">
            <div class="um-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'filter',13) !!}</span>
            <span class="um-panel-title">Filters</span>
        </div>
        <form method="GET" action="{{ url()->current() }}" class="um-filter-form">
            <div class="um-fi-wrap">
                <span class="um-fi-ico">{!! ico($IC,'search',13) !!}</span>
                <input type="text" name="search" class="um-fi"
                       value="{{ request('search') }}"
                       placeholder="Name, email or phone…">
            </div>
            <div>
                <label class="um-fi-lbl">Status</label>
                <select name="status" class="um-fi">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="um-apply-btn">
                {!! ico($IC,'search',12) !!} Apply Filters
            </button>
            @if(request('search') || (request('status') !== null && request('status') !== ''))
            <a href="{{ url()->current() }}" class="um-reset-btn">
                {!! ico($IC,'refresh-cw',11) !!} Reset
            </a>
            @endif
        </form>
    </div>

    {{-- RIGHT — table ── --}}
    <div class="um-table-card">
        {{-- SHead --}}
        <div class="um-table-head">
            <div style="display:flex;align-items:center;gap:9px;">
                <div class="um-acc"></div>
                <span style="color:#FF5C00;display:flex;">{!! ico($IC,'users',14) !!}</span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#1D1D1D;">{{ $title ?? 'Users' }}</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">{{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }} found</div>
                </div>
            </div>
            <span class="um-badge">{{ $users->total() }}</span>
        </div>

        {{-- Table --}}
        <div class="um-tbl-wrap">
            <table class="um-tbl">
                <thead>
                    <tr>
                        <th style="width:38px;">#</th>
                        <th>Employee ID</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Online</th>
                        <th>Created</th>
                        <th style="text-align:right;padding-right:14px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    @php
                        $words    = array_filter(explode(' ', $user->name));
                        $initials = strtoupper((isset($words[0]) ? $words[0][0] : '') . (count($words) > 1 ? end($words)[0] : ''));
                        $roleColorMap = ['admin' => '#FF5C00', 'manager' => '#D97706', 'telecaller' => '#10B981', 'report_viewer' => '#7C3AED'];
                        $roleLabelMap = ['admin' => 'Admin', 'manager' => 'Manager', 'telecaller' => 'Telecaller', 'report_viewer' => 'Report Viewer'];
                        $roleBgMap    = ['admin' => '#FFF7ED', 'manager' => '#FFFBEB', 'telecaller' => '#ECFDF5', 'report_viewer' => '#F5F3FF'];
                        $rc  = $roleColorMap[$user->role] ?? '#9CA3AF';
                        $rbg = $roleBgMap[$user->role]   ?? '#F9FAFB';
                        $rl  = $roleLabelMap[$user->role] ?? ucwords(str_replace('_', ' ', $user->role));
                        $isLocked = $user->locked_until && \Carbon\Carbon::parse($user->locked_until)->isFuture();
                        $avatarColors = ['#FF5C00','#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4'];
                        $avBg = $avatarColors[abs(($user->id ?? $index) % count($avatarColors))];
                    @endphp
                    <tr>
                        <td style="color:#9CA3AF;font-size:11px;font-weight:600;">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                        <td>
                            @if($user->employee_id)
                                <span class="um-emp-id">{{ $user->employee_id }}</span>
                            @else
                                <span style="color:#9CA3AF;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:9px;">
                                <div style="width:32px;height:32px;border-radius:9px;background:{{ $avBg }};color:#fff;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $initials }}</div>
                                <div>
                                    <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;line-height:1.2;">{{ $user->name }}</div>
                                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">{{ $user->email }}</div>
                                    @if($user->phone)
                                    <div style="font-size:11px;color:#9CA3AF;">{{ $user->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="background:{{ $rbg }};color:{{ $rc }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;">{{ $rl }}</span>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
                                <button class="um-status-btn toggle-status-btn {{ $user->status ? 'is-active' : 'is-inactive' }}"
                                    data-id="{{ $user->id }}">
                                    <span class="um-status-dot"></span>
                                    <span class="um-status-label">{{ $user->status ? 'Active' : 'Inactive' }}</span>
                                </button>
                                @if($isLocked)
                                <span class="um-locked-badge">{!! ico($IC,'lock',10) !!} Locked</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="um-presence {{ $user->presence_state === 'online' ? 'is-online' : 'is-offline' }}"
                                  data-user-id="{{ $user->id }}">
                                <span class="um-presence-dot"></span>
                                <span class="um-presence-label">{{ ucfirst($user->presence_state) }}</span>
                            </span>
                        </td>
                        <td style="font-size:11.5px;color:#9CA3AF;">{{ optional($user->created_at)->format('d M Y') }}</td>
                        <td style="padding-right:14px;">
                            <div style="display:flex;gap:5px;align-items:center;justify-content:flex-end;">
                                <a href="{{ route('admin.users.edit', encrypt($user->id)) }}"
                                   class="um-btn um-btn-edit" title="Edit User">{!! ico($IC,'pencil',13) !!}</a>
                                <button class="um-btn um-btn-logout force-logout-btn"
                                    data-id="{{ $user->id }}" title="Force Logout">{!! ico($IC,'log-out',13) !!}</button>
                                <button class="um-btn um-btn-reset reset-password-btn"
                                    data-id="{{ $user->id }}"
                                    data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                    title="Reset Password">{!! ico($IC,'key-round',13) !!}</button>
                                @if($isLocked)
                                <button class="um-btn um-btn-unlock unlock-account-btn"
                                    data-id="{{ $user->id }}" title="Unlock Account">{!! ico($IC,'unlock',13) !!}</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="um-empty">
                                <div style="width:56px;height:56px;border-radius:14px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#FF5C00;opacity:.6;">{!! ico($IC,'user-x',28) !!}</div>
                                <div style="font-size:14px;font-weight:700;color:#1D1D1D;margin-bottom:4px;">No users found</div>
                                <div style="font-size:12px;color:#9CA3AF;">Try adjusting your search or filter criteria</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="um-pager">
            <small style="color:#9CA3AF;font-size:11.5px;">
                Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
            </small>
            {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>{{-- end um-body --}}

{{-- Reset Password Modal --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="resetPasswordForm">
            @csrf
            <input type="hidden" id="resetPasswordUserId">
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);border:none;padding:16px 20px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,.20);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;">
                            {!! ico($IC,'key-round',16) !!}
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:#fff;font-weight:700;font-size:14px;font-family:'Poppins',sans-serif;">Reset Password</h5>
                            <p style="color:rgba(255,255,255,.75);font-size:11.5px;margin:0;font-family:'Poppins',sans-serif;">Set a new password for this user</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <label class="um-fi-lbl" style="display:block;margin-bottom:5px;">New Password</label>
                    <input type="password" id="newPasswordInput" class="um-fi" required minlength="6"
                           placeholder="Enter new password (min. 6 characters)" style="width:100%;">
                </div>
                <div class="modal-footer" style="border-top:1px solid #F0F0F0;padding:12px 20px;gap:8px;">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="um-apply-btn" style="width:auto;padding:7px 16px;">Update Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.um-kpi-grid,.um-body,.um-left-panel,.um-table-card,.um-tbl,.um-pager,.um-filter-form { font-family:'Poppins',sans-serif!important; }

/* ── KPI row ── */
.um-kpi-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:12px; }
@media(max-width:1200px){ .um-kpi-grid{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:768px){ .um-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
.um-sr { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px; }
.um-sr-or { background:#FF5C00;box-shadow:0 4px 14px rgba(255,92,0,.22); }
.um-sr-wh { background:#FEFEFE;border:1px solid #F0F0F0;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.um-sr-icon { width:32px;height:32px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
.um-sr-or .um-sr-icon { background:rgba(255,255,255,.18);color:#fff; }
.um-sr-wh .um-sr-icon { background:#FFF7ED;color:#FF5C00; }
.um-sr-lbl { font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1px; }
.um-sr-or .um-sr-lbl { color:rgba(255,255,255,.75); }
.um-sr-wh .um-sr-lbl { color:#9CA3AF; }
.um-sr-val { font-size:20px;font-weight:800;line-height:1; }
.um-sr-or .um-sr-val { color:#fff; }
.um-sr-wh .um-sr-val { color:#1D1D1D; }

/* ── 2-col layout ── */
.um-body { display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start; }
@media(max-width:900px){ .um-body{ grid-template-columns:1fr; } }

/* ── Left panel (ONE card) ── */
.um-left-panel { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.um-panel-head { display:flex;align-items:center;gap:7px;padding:12px 14px 10px; }
.um-acc { width:3px;height:20px;background:#FF5C00;border-radius:2px;flex-shrink:0; }
.um-panel-title { font-size:12px;font-weight:700;color:#1D1D1D; }

/* ── Role nav ── */
.um-nav { padding:0 10px 8px;display:flex;flex-direction:column;gap:5px; }
.um-nav-link { display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:10px;border:1px solid #F0F0F0;background:#FEFEFE;text-decoration:none;transition:all .15s;color:#374151; }
.um-nav-link:hover:not(.on) { background:#FFF7ED;border-color:#FED7AA; }
.um-nav-link.on, .um-nav-link.on .um-nav-lbl, .um-nav-link.on .um-nav-cnt, .um-nav-link.on svg { color:#fff!important; }
.um-nav-ico { width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.um-nav-lbl { flex:1;font-size:12px;font-weight:600; }
.um-nav-cnt { font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;background:#F3F4F6;color:#6B7280; }

/* ── Filter form ── */
.um-filter-form { padding:8px 12px 14px;display:flex;flex-direction:column;gap:9px; }
.um-fi-lbl { font-size:9.5px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px; }
.um-fi-wrap { position:relative; }
.um-fi-ico { position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9CA3AF;pointer-events:none;display:flex; }
.um-fi { width:100%;height:34px;border-radius:8px;border:1px solid #E5E7EB;font-size:12.5px;color:#1D1D1D;background:#FAFBFC;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s,box-shadow .15s;box-sizing:border-box; }
.um-fi-wrap .um-fi { padding-left:32px; }
.um-fi:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09);background:#fff; }
.um-apply-btn { width:100%;background:#FF5C00;color:#fff;border:none;border-radius:8px;padding:8px;font-size:12.5px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;font-family:'Poppins',sans-serif!important; }
.um-apply-btn:hover { background:#e05200; }
.um-reset-btn { width:100%;background:#FEFEFE;color:#374151;border:1px solid #E5E7EB;border-radius:8px;padding:7px;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;text-decoration:none;font-family:'Poppins',sans-serif!important; }
.um-reset-btn:hover { background:#F3F4F6; }

/* ── Right table card ── */
.um-table-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.um-table-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 18px;border-bottom:1px solid #F0F0F0;background:linear-gradient(135deg,#FAFBFC,#FEFEFE); }
.um-badge { background:#FFF7ED;color:#FF5C00;border:1px solid #FED7AA;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px; }
.um-tbl-wrap { overflow-y:auto;overflow-x:auto;max-height:520px; }
.um-tbl-wrap::-webkit-scrollbar { width:5px; }
.um-tbl-wrap::-webkit-scrollbar-thumb { background:#D1D5DB;border-radius:4px; }
.um-tbl-wrap::-webkit-scrollbar-thumb:hover { background:#FF5C00; }
.um-tbl { width:100%;border-collapse:separate;border-spacing:0; }
.um-tbl thead th { position:sticky;top:0;z-index:2;background:#F4F6F8;color:#9CA3AF;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:10px 13px;white-space:nowrap;border-bottom:2px solid #F0F0F0; }
.um-tbl tbody td { padding:11px 13px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F4F6F8; }
.um-tbl tbody tr:last-child td { border-bottom:none; }
.um-tbl tbody tr:nth-child(even) td { background:#FAFBFC; }
.um-tbl tbody tr:hover td { background:#FFF7ED!important; }
.um-tbl tbody tr:hover td:first-child { border-left:3px solid #FF5C00;padding-left:15px; }
.um-emp-id { display:inline-block;background:#F4F6F8;border:1px solid #F0F0F0;border-radius:6px;padding:2px 8px;font-size:10.5px;font-weight:700;letter-spacing:.5px;color:#4B5563;font-family:monospace!important; }

/* ── Action buttons ── */
.um-btn { width:28px;height:28px;border-radius:7px;border:1px solid #E5E7EB;background:#F9FAFB;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;color:#6B7280; }
.um-btn:hover { transform:translateY(-1px); }
.um-btn-edit { color:#FF5C00;border-color:#FED7AA; }
.um-btn-edit:hover { background:#FFF7ED; }
.um-btn-logout { color:#D97706;border-color:#FDE68A; }
.um-btn-logout:hover { background:#FFFBEB; }
.um-btn-reset:hover { background:#F3F4F6; }
.um-btn-unlock { color:#16A34A;border-color:#BBF7D0; }
.um-btn-unlock:hover { background:#F0FDF4; }

/* ── Status ── */
.um-status-btn { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;border:none;cursor:pointer;font-size:11.5px;font-weight:600;transition:all .2s;font-family:'Poppins',sans-serif!important; }
.um-status-dot { width:6px;height:6px;border-radius:50%;display:inline-block; }
.um-status-btn.is-active  { background:rgba(16,185,129,.12);color:#059669; }
.um-status-btn.is-active .um-status-dot  { background:#10b981;box-shadow:0 0 0 2px rgba(16,185,129,.3); }
.um-status-btn.is-inactive{ background:rgba(239,68,68,.09);color:#dc2626; }
.um-status-btn.is-inactive .um-status-dot{ background:#ef4444; }
.um-locked-badge { display:inline-flex;align-items:center;gap:3px;background:rgba(239,68,68,.1);color:#dc2626;border-radius:20px;padding:2px 8px;font-size:10.5px;font-weight:600; }

/* ── Presence ── */
.um-presence { display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600; }
.um-presence-dot { width:7px;height:7px;border-radius:50%;display:inline-block; }
.um-presence.is-online  { color:#059669; }
.um-presence.is-online .um-presence-dot  { background:#10b981;box-shadow:0 0 0 2px rgba(16,185,129,.25);animation:um-pulse 2s infinite; }
.um-presence.is-offline { color:#9CA3AF; }
.um-presence.is-offline .um-presence-dot { background:#94a3b8; }
@keyframes um-pulse { 0%,100%{box-shadow:0 0 0 2px rgba(16,185,129,.25);}50%{box-shadow:0 0 0 4px rgba(16,185,129,.1);} }

/* ── Misc ── */
.um-empty { text-align:center;padding:52px 16px; }
.um-pager { padding:10px 16px;border-top:1px solid #F0F0F0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:9px;background:#FAFBFC; }
.um-pager .page-link { background:#FEFEFE;border-color:#E5E7EB;color:#374151;font-size:11.5px;border-radius:7px;padding:4px 9px;font-family:'Poppins',sans-serif!important; }
.um-pager .page-item.active .page-link { background:#FF5C00;border-color:#FF5C00;color:#fff; }
.um-pager .page-item.disabled .page-link { opacity:.4; }
</style>

<script>
(function () {
    const csrf = '{{ csrf_token() }}';

    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const res = await fetch("{{ route('admin.users.toggle') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ id: this.dataset.id })
            });
            const data = await res.json();
            const label = this.querySelector('.um-status-label');
            if (data.status) {
                this.classList.replace('is-inactive', 'is-active');
                if (label) label.textContent = 'Active';
            } else {
                this.classList.replace('is-active', 'is-inactive');
                if (label) label.textContent = 'Inactive';
            }
        });
    });

    document.querySelectorAll('.force-logout-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (!confirm('Force logout this user now?')) return;
            await fetch("{{ route('admin.users.force-logout') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ id: this.dataset.id })
            });
            alert('Force logout request completed.');
            window.location.reload();
        });
    });

    document.querySelectorAll('.reset-password-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('resetPasswordUserId').value = this.dataset.id;
            document.getElementById('newPasswordInput').value = '';
        });
    });

    document.getElementById('resetPasswordForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const res = await fetch("{{ route('admin.users.reset-password') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                id: document.getElementById('resetPasswordUserId').value,
                password: document.getElementById('newPasswordInput').value
            })
        });
        if (!res.ok) { alert('Failed to reset password.'); return; }
        alert('Password reset successfully.');
        bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'))?.hide();
    });

    document.querySelectorAll('.unlock-account-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (!confirm('Unlock this account and reset failed login attempts?')) return;
            const res = await fetch("{{ route('admin.users.unlock') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ id: this.dataset.id })
            });
            if (res.ok) { alert('Account unlocked successfully.'); window.location.reload(); }
            else { alert('Failed to unlock account.'); }
        });
    });

    const presenceUrl = @json(route('admin.users.presence-snapshot'));
    async function refreshPresence() {
        try {
            const res = await fetch(presenceUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const { presence = {} } = await res.json();
            document.querySelectorAll('[data-user-id]').forEach(el => {
                const uid = el.dataset.userId;
                if (!(uid in presence)) return;
                const isOnline = presence[uid] === 'online';
                el.className = 'um-presence ' + (isOnline ? 'is-online' : 'is-offline');
                const label = el.querySelector('.um-presence-label');
                if (label) label.textContent = isOnline ? 'Online' : 'Offline';
            });
        } catch (_) {}
    }
    refreshPresence();
    setInterval(refreshPresence, 20000);
})();
</script>

@endsection
