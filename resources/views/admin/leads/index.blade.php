@extends('layouts.app')

@section('page_title', 'Lead Management')

@php
$IC = [
    'filter'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'search'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>',
    'refresh-cw' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H3v5"/></svg>',
    'download'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline stroke-linecap="round" stroke-linejoin="round" points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3" stroke-linecap="round"/></svg>',
    'upload'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline stroke-linecap="round" stroke-linejoin="round" points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15" stroke-linecap="round"/></svg>',
    'users'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'user-plus'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14" stroke-linecap="round"/><line x1="22" x2="16" y1="11" y2="11" stroke-linecap="round"/></svg>',
    'trending-up' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline stroke-linecap="round" stroke-linejoin="round" points="16 7 22 7 22 13"/></svg>',
    'phone'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'list'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="8" x2="21" y1="6" y2="6" stroke-linecap="round"/><line x1="8" x2="21" y1="12" y2="12" stroke-linecap="round"/><line x1="8" x2="21" y1="18" y2="18" stroke-linecap="round"/><line x1="3" x2="3.01" y1="6" y2="6" stroke-linecap="round"/><line x1="3" x2="3.01" y1="12" y2="12" stroke-linecap="round"/><line x1="3" x2="3.01" y1="18" y2="18" stroke-linecap="round"/></svg>',
    'eye'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>',
    'edit'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m15 5 4 4"/></svg>',
    'check'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="20 6 9 17 4 12"/></svg>',
    'x'          => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18"/><path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12"/></svg>',
    'calendar'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6" stroke-linecap="round"/><line x1="8" x2="8" y1="2" y2="6" stroke-linecap="round"/><line x1="3" x2="21" y1="10" y2="10" stroke-linecap="round"/></svg>',
    'copy'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="13" height="13" x="9" y="9" rx="2" ry="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
    'merge'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h8M8 12h5m-5 6h8M16 6l4 4-4 4"/></svg>',
    'alert'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13" stroke-linecap="round"/><line x1="12" x2="12.01" y1="17" y2="17" stroke-linecap="round"/></svg>',
    'person-plus'=> '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14" stroke-linecap="round"/><line x1="22" x2="16" y1="11" y2="11" stroke-linecap="round"/></svg>',
    'save'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="17 21 17 13 7 13 7 21"/><polyline stroke-linecap="round" stroke-linejoin="round" points="7 3 7 8 15 8"/></svg>',
    'chevron-down'=> '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="6 9 12 15 18 9"/></svg>',
    'chevron-up'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="18 15 12 9 6 15"/></svg>',
    'tune'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="4" x2="4" y1="21" y2="14" stroke-linecap="round"/><line x1="4" x2="4" y1="10" y2="3" stroke-linecap="round"/><line x1="12" x2="12" y1="21" y2="12" stroke-linecap="round"/><line x1="12" x2="12" y1="8" y2="3" stroke-linecap="round"/><line x1="20" x2="20" y1="21" y2="16" stroke-linecap="round"/><line x1="20" x2="20" y1="12" y2="3" stroke-linecap="round"/><line x1="1" x2="7" y1="14" y2="14" stroke-linecap="round"/><line x1="9" x2="15" y1="8" y2="8" stroke-linecap="round"/><line x1="17" x2="23" y1="16" y2="16" stroke-linecap="round"/></svg>',
    'inbox'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="22 12 16 12 14 15 10 15 8 12 2 12"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',
];
function ico($IC, $name, $size=14) {
    if(!isset($IC[$name])) return '';
    return str_replace('<svg ','<svg width="'.$size.'" height="'.$size.'" ',$IC[$name]);
}
@endphp

@section('header_actions')
    @php
        $exportParams = array_merge(
            request()->only(['search','manager_id','telecaller_id','status','date_range','date_from','date_to','service_id','source','gender','state','city','district','followup','no_activity_days','sla','is_duplicate','is_active','aged_min','aged_max']),
            ['scope' => $scope]
        );
    @endphp
    <a href="{{ route('admin.leads.import.form') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#FEFEFE;color:#374151!important;border:1px solid #E5E7EB;border-radius:8px;font-weight:600;padding:7px 14px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
        {!! ico($IC,'upload',14) !!} Import Leads
    </a>
    <a href="{{ route('admin.leads.export', $exportParams) }}"
        style="display:inline-flex;align-items:center;gap:6px;background:#16a34a;color:#fff!important;border:none;border-radius:8px;font-weight:600;padding:7px 14px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
        {!! ico($IC,'download',14) !!} Export Excel
    </a>
    <a href="{{ route('admin.leads.export', array_merge($exportParams, ['format'=>'pdf'])) }}"
        style="display:inline-flex;align-items:center;gap:6px;background:#dc2626;color:#fff!important;border:none;border-radius:8px;font-weight:600;padding:7px 14px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
        {!! ico($IC,'download',14) !!} Export PDF
    </a>
@endsection

@section('content')

@php
    $advKeys = ['manager_id','telecaller_id','status','service_id','source','gender','state','city','district','followup','no_activity_days','sla','is_duplicate','is_active','aged_min','aged_max'];
    $hasAdv  = collect($advKeys)->some(fn($k) => request()->filled($k));
    $activeCount = count($activeFilters ?? []);
@endphp

{{-- ── KPI StatRow — full width top ── --}}
<div class="lm-stat-grid">
    <div class="lm-sr lm-sr-or">
        <div class="lm-sr-icon">{!! ico($IC,'users',15) !!}</div>
        <div><div class="lm-sr-lbl">Total Leads</div><div class="lm-sr-val">{{ number_format($stats['total']) }}</div></div>
    </div>
    <div class="lm-sr lm-sr-wh">
        <div class="lm-sr-icon" style="background:#FFFBEB;color:#D97706;">{!! ico($IC,'user-plus',15) !!}</div>
        <div><div class="lm-sr-lbl">Unassigned</div><div class="lm-sr-val">{{ number_format($stats['unassigned']) }}</div></div>
    </div>
    <div class="lm-sr lm-sr-wh">
        <div class="lm-sr-icon" style="background:#ECFDF5;color:#10B981;">{!! ico($IC,'person-plus',15) !!}</div>
        <div><div class="lm-sr-lbl">Assigned</div><div class="lm-sr-val">{{ number_format($stats['assigned']) }}</div></div>
    </div>
    <div class="lm-sr lm-sr-wh">
        <div class="lm-sr-icon" style="background:#EFF6FF;color:#2563EB;">{!! ico($IC,'check',15) !!}</div>
        <div><div class="lm-sr-lbl">Converted</div><div class="lm-sr-val">{{ number_format($stats['converted']) }}</div></div>
    </div>
    <div class="lm-sr lm-sr-wh">
        <div class="lm-sr-icon" style="background:#FEF2F2;color:#DC2626;">{!! ico($IC,'x',15) !!}</div>
        <div><div class="lm-sr-lbl">Lost</div><div class="lm-sr-val">{{ number_format($stats['lost']) }}</div></div>
    </div>
    <div class="lm-sr lm-sr-wh">
        <div class="lm-sr-icon" style="background:#F5F3FF;color:#7C3AED;">{!! ico($IC,'copy',15) !!}</div>
        <div><div class="lm-sr-lbl">Duplicates</div><div class="lm-sr-val">{{ number_format($stats['duplicates']) }}</div></div>
    </div>
</div>

{{-- ── Scope Tabs ── --}}
<div class="lm-scope-nav">
    @php
        $scopes = [
            ['route' => route('admin.leads.all'),        'scope' => 'all',        'label' => 'All Leads',  'count' => $stats['total'],      'color' => '#FF5C00'],
            ['route' => route('admin.leads.unassigned'), 'scope' => 'unassigned', 'label' => 'Unassigned', 'count' => $stats['unassigned'], 'color' => '#D97706'],
            ['route' => route('admin.leads.assigned'),   'scope' => 'assigned',   'label' => 'Assigned',   'count' => $stats['assigned'],   'color' => '#10B981'],
            ['route' => route('admin.leads.converted'),  'scope' => 'converted',  'label' => 'Converted',  'count' => $stats['converted'],  'color' => '#2563EB'],
            ['route' => route('admin.leads.lost'),       'scope' => 'lost',       'label' => 'Lost',       'count' => $stats['lost'],       'color' => '#DC2626'],
            ['route' => route('admin.leads.duplicates'), 'scope' => 'duplicates', 'label' => 'Duplicates', 'count' => $stats['duplicates'], 'color' => '#7C3AED'],
        ];
    @endphp
    @foreach($scopes as $s)
    <a href="{{ $s['route'] }}"
       class="lm-scope-link {{ $scope === $s['scope'] ? 'active' : '' }}"
       style="{{ $scope === $s['scope'] ? 'background:'.$s['color'].';color:#fff;border-color:transparent;box-shadow:0 3px 10px '.$s['color'].'30;' : '' }}">
        <span class="lm-scope-lbl">{{ $s['label'] }}</span>
        <span class="lm-scope-cnt" style="{{ $scope === $s['scope'] ? 'background:rgba(255,255,255,.9);color:'.$s['color'].';' : '' }}">{{ number_format($s['count']) }}</span>
    </a>
    @endforeach
</div>

{{-- ── 2-column: filter left | table right ── --}}
<div class="lm-body">

    {{-- LEFT — filter panel ── --}}
    <div class="lm-left-panel">

        {{-- Filter heading ── --}}
        <div class="lm-panel-head">
            <div class="lm-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'filter',13) !!}</span>
            <span class="lm-panel-title">Filter Leads</span>
            @if($activeCount > 0)
                <span class="lm-active-badge">{{ $activeCount }}</span>
            @endif
        </div>

        <form method="GET" id="filterForm" class="lm-filter-form">

            {{-- Search ── --}}
            <div class="lm-fi-wrap">
                <span class="lm-fi-ico">{!! ico($IC,'search',13) !!}</span>
                <input type="text" name="search" class="lm-fi"
                       value="{{ request('search') }}"
                       placeholder="Code, name, phone, email…">
            </div>

            {{-- Telecaller ── --}}
            <div>
                <label class="lm-fi-lbl">Telecaller</label>
                <select name="telecaller_id" class="lm-fi">
                    <option value="">All Telecallers</option>
                    @foreach($telecallers as $t)
                        <option value="{{ $t->id }}" {{ request('telecaller_id')==$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Manager ── --}}
            <div>
                <label class="lm-fi-lbl">Manager</label>
                <select name="manager_id" class="lm-fi">
                    <option value="">All Managers</option>
                    @foreach($managers as $m)
                        <option value="{{ $m->id }}" {{ request('manager_id')==$m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status ── --}}
            <div>
                <label class="lm-fi-lbl">Status</label>
                <select name="status" class="lm-fi">
                    <option value="">All Statuses</option>
                    @foreach(['new'=>'New','assigned'=>'Assigned','contacted'=>'Contacted','interested'=>'Interested','follow_up'=>'Follow-up','not_interested'=>'Not Interested','converted'=>'Converted'] as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status')===$val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Service ── --}}
            <div>
                <label class="lm-fi-lbl">Service</label>
                <select name="service_id" class="lm-fi">
                    <option value="">All Services</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ request('service_id')==$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date range ── --}}
            <div>
                <label class="lm-fi-lbl">{!! ico($IC,'calendar',11) !!} Date Range</label>
                <select name="date_range" class="lm-fi" id="dateRangeSelect">
                    <option value="">Any Date</option>
                    <option value="today"  {{ request('date_range')=='today'  ? 'selected' : '' }}>Today</option>
                    <option value="7"      {{ request('date_range')=='7'      ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30"     {{ request('date_range')=='30'     ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="custom" {{ request('date_range')=='custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div id="dateFromWrap" style="{{ request('date_range')==='custom' ? '' : 'display:none' }}">
                <label class="lm-fi-lbl">From Date</label>
                <input type="date" name="date_from" class="lm-fi" value="{{ request('date_from') }}">
            </div>
            <div id="dateToWrap" style="{{ request('date_range')==='custom' ? '' : 'display:none' }}">
                <label class="lm-fi-lbl">To Date</label>
                <input type="date" name="date_to" class="lm-fi" value="{{ request('date_to') }}">
            </div>

            {{-- Advanced toggle ── --}}
            <button type="button" class="lm-adv-toggle {{ $hasAdv ? 'active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#advFilters"
                aria-expanded="{{ $hasAdv ? 'true' : 'false' }}">
                {!! ico($IC,'tune',12) !!}
                <span>Advanced</span>
                @if($hasAdv)<span class="lm-adv-on">ON</span>@endif
                <span class="lm-adv-chevron" id="advChevron">{!! ico($IC, $hasAdv ? 'chevron-up' : 'chevron-down', 12) !!}</span>
            </button>

            {{-- Advanced panel ── --}}
            <div class="collapse {{ $hasAdv ? 'show' : '' }}" id="advFilters">
                <div style="display:flex;flex-direction:column;gap:9px;padding-top:4px;">

                    <div>
                        <label class="lm-fi-lbl">Follow-Up</label>
                        <select name="followup" class="lm-fi">
                            <option value="">Any</option>
                            <option value="today"     {{ request('followup')==='today'     ? 'selected' : '' }}>Due Today</option>
                            <option value="overdue"   {{ request('followup')==='overdue'   ? 'selected' : '' }}>Overdue</option>
                            <option value="this_week" {{ request('followup')==='this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="none"      {{ request('followup')==='none'      ? 'selected' : '' }}>No Follow-up Set</option>
                        </select>
                    </div>

                    <div>
                        <label class="lm-fi-lbl">Source</label>
                        <select name="source" class="lm-fi">
                            <option value="">All Sources</option>
                            @foreach($sources as $src)
                                <option value="{{ $src }}" {{ request('source')===$src ? 'selected' : '' }}>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="lm-fi-lbl">Gender</label>
                        <select name="gender" class="lm-fi">
                            <option value="">All Genders</option>
                            <option value="male"   {{ request('gender')==='male'   ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender')==='female' ? 'selected' : '' }}>Female</option>
                            <option value="other"  {{ request('gender')==='other'  ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="lm-fi-lbl">SLA Status</label>
                        <select name="sla" class="lm-fi">
                            <option value="">Any</option>
                            <option value="escalated" {{ request('sla')==='escalated' ? 'selected' : '' }}>Escalated</option>
                            <option value="1"         {{ request('sla')==='1'         ? 'selected' : '' }}>Level 1+</option>
                            <option value="2"         {{ request('sla')==='2'         ? 'selected' : '' }}>Level 2+</option>
                        </select>
                    </div>

                    <div>
                        <label class="lm-fi-lbl">State</label>
                        <input type="text" name="state" class="lm-fi" placeholder="e.g. Tamil Nadu" value="{{ request('state') }}">
                    </div>

                    <div>
                        <label class="lm-fi-lbl">City</label>
                        <input type="text" name="city" class="lm-fi" placeholder="e.g. Chennai" value="{{ request('city') }}">
                    </div>

                    <div>
                        <label class="lm-fi-lbl">District</label>
                        <input type="text" name="district" class="lm-fi" placeholder="e.g. Coimbatore" value="{{ request('district') }}">
                    </div>

                    <div>
                        <label class="lm-fi-lbl">No Activity (Days)</label>
                        <input type="number" name="no_activity_days" class="lm-fi" min="1" max="365" placeholder="e.g. 7" value="{{ request('no_activity_days') }}">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label class="lm-fi-lbl">Min Age (d)</label>
                            <input type="number" name="aged_min" class="lm-fi" min="0" placeholder="7" value="{{ request('aged_min') }}">
                        </div>
                        <div>
                            <label class="lm-fi-lbl">Max Age (d)</label>
                            <input type="number" name="aged_max" class="lm-fi" min="0" placeholder="30" value="{{ request('aged_max') }}">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label class="lm-fi-lbl">Duplicate</label>
                            <select name="is_duplicate" class="lm-fi">
                                <option value="">All</option>
                                <option value="1" {{ request('is_duplicate')==='1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ request('is_duplicate')==='0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="lm-fi-lbl">Active</label>
                            <select name="is_active" class="lm-fi">
                                <option value="">All</option>
                                <option value="1" {{ request('is_active')==='1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active')==='0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <button type="submit" class="lm-apply-btn">
                {!! ico($IC,'search',12) !!} Apply Filters
            </button>
            <a href="{{ route('admin.leads.' . $scope) }}" class="lm-reset-btn">
                {!! ico($IC,'refresh-cw',11) !!} Reset
            </a>
            @if($activeCount > 0)
                <small style="font-size:10.5px;color:#9CA3AF;text-align:center;">{{ number_format($leads->total()) }} result{{ $leads->total() !== 1 ? 's' : '' }} found</small>
            @endif
        </form>

        {{-- Divider --}}
        <div style="height:1px;background:#F0F0F0;margin:4px 12px 8px;"></div>

        {{-- Bulk Assign ── --}}
        <div class="lm-panel-head" style="padding-bottom:8px;">
            <div class="lm-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'person-plus',13) !!}</span>
            <span class="lm-panel-title">Bulk Assign</span>
        </div>
        <div style="padding:0 12px 14px;">
            <p style="font-size:10.5px;color:#9CA3AF;margin:0 0 8px;">Select rows from the table, then assign below.</p>
            <form method="POST" action="{{ route('admin.leads.bulk-assign') }}" id="bulkAssignForm" style="display:flex;flex-direction:column;gap:9px;">
                @csrf
                <div>
                    <label class="lm-fi-lbl">Manager</label>
                    <select class="lm-fi" name="manager_id">
                        <option value="">Keep unchanged</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lm-fi-lbl">Telecaller</label>
                    <select class="lm-fi" name="telecaller_id">
                        <option value="">Keep unchanged</option>
                        @foreach ($telecallers as $telecaller)
                            <option value="{{ $telecaller->id }}">{{ $telecaller->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="lm-apply-btn" style="background:#374151;">
                    {!! ico($IC,'person-plus',12) !!} Bulk Assign
                </button>
            </form>
        </div>
    </div>

    {{-- RIGHT — table ── --}}
    <div class="lm-table-card">
        {{-- SHead ── --}}
        <div class="lm-table-head">
            <div style="display:flex;align-items:center;gap:9px;">
                <div class="lm-acc"></div>
                <span style="color:#FF5C00;display:flex;">{!! ico($IC,'list',14) !!}</span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#1D1D1D;">{{ $title }}</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">{{ number_format($leads->total()) }} record{{ $leads->total() !== 1 ? 's' : '' }} found</div>
                </div>
            </div>
            <span class="lm-badge">{{ number_format($leads->total()) }}</span>
        </div>

        {{-- Table ── --}}
        <div class="lm-tbl-wrap">
            <table class="lm-tbl">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" class="lm-check" id="selectAllLeads"></th>
                        <th style="width:36px;">#</th>
                        <th>Lead</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Manager</th>
                        <th>Telecaller</th>
                        <th style="text-align:right;padding-right:14px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $index => $lead)
                        @php
                            $initials = strtoupper(substr($lead->name ?? 'L', 0, 1));
                            $stClass  = 'lm-status-' . str_replace('_', '-', $lead->status);
                            $avatarColors = ['#FF5C00','#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4'];
                            $avBg = $avatarColors[abs(($lead->id ?? $index) % count($avatarColors))];
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="lm-check lead-checkbox" value="{{ $lead->id }}"></td>
                            <td style="color:#9CA3AF;font-size:11px;font-weight:600;">{{ ($leads->currentPage() - 1) * $leads->perPage() + $index + 1 }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:9px;">
                                    <div style="width:32px;height:32px;border-radius:9px;background:{{ $avBg }};color:#fff;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $initials }}</div>
                                    <div>
                                        <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;line-height:1.2;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                            {{ $lead->name }}
                                            {{-- Duplicate badges --}}
                                            @if($scope === 'duplicates')
                                                @php $isPhoneDup = $duplicatePhones->contains($lead->phone); $isEmailDup = $duplicateEmails->contains($lead->email); @endphp
                                                @if($isPhoneDup && $isEmailDup)
                                                    <span class="lm-dup-badge">PHONE+EMAIL</span>
                                                @elseif($isPhoneDup)
                                                    <span class="lm-dup-badge">DUP PHONE</span>
                                                @elseif($isEmailDup)
                                                    <span class="lm-dup-badge" style="background:#fef3c7;color:#92400e;border-color:#fde68a;">DUP EMAIL</span>
                                                @endif
                                            @elseif($lead->is_duplicate)
                                                <span class="lm-dup-badge">DUPLICATE</span>
                                            @endif
                                            {{-- SLA badges --}}
                                            @if($lead->sla_level >= 2)
                                                <span class="lm-sla-l2">SLA L2</span>
                                            @elseif($lead->sla_level >= 1)
                                                <span class="lm-sla-l1">SLA L1</span>
                                            @elseif($lead->sla_escalated_at)
                                                <span class="lm-sla-esc">ESCALATED</span>
                                            @endif
                                        </div>
                                        <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:2px;">
                                            <span style="font-size:10.5px;color:#9CA3AF;">{{ $lead->lead_code }}</span>
                                            <x-aging-badge :days="$lead->days_aged" />
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:12px;color:#374151;font-weight:500;">{{ $lead->phone ?? '—' }}</td>
                            <td style="font-size:11.5px;color:#9CA3AF;max-width:140px;">{{ $lead->service?->name ?? '—' }}</td>
                            <td>
                                <span class="lm-status {{ $stClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:#374151;">{{ $lead->assignedBy?->name ?? '—' }}</td>
                            <td style="font-size:12px;color:#374151;">{{ $lead->assignedUser?->name ?? '—' }}</td>
                            <td style="padding-right:14px;">
                                <div style="display:flex;gap:5px;align-items:center;justify-content:flex-end;">
                                    <a href="{{ route('admin.leads.show', encrypt($lead->id)) }}" class="lm-btn lm-btn-view" title="View">
                                        {!! ico($IC,'eye',13) !!}
                                    </a>
                                    <button class="lm-btn lm-btn-mgr assign-manager-btn"
                                        data-id="{{ encrypt($lead->id) }}"
                                        data-manager-id="{{ $lead->assigned_by ?? '' }}"
                                        data-bs-toggle="modal" data-bs-target="#assignManagerModal"
                                        title="Assign Manager">
                                        {!! ico($IC,'edit',13) !!}
                                    </button>
                                    <button class="lm-btn lm-btn-tc assign-telecaller-btn"
                                        data-id="{{ encrypt($lead->id) }}"
                                        data-telecaller-id="{{ $lead->assigned_to ?? '' }}"
                                        data-bs-toggle="modal" data-bs-target="#assignTelecallerModal"
                                        title="Assign Telecaller">
                                        {!! ico($IC,'phone',13) !!}
                                    </button>
                                    @if($scope === 'duplicates')
                                        <button class="lm-btn lm-btn-merge merge-btn"
                                            data-source="{{ $lead->id }}"
                                            data-bs-toggle="modal" data-bs-target="#mergeModal"
                                            title="Merge Lead">
                                            {!! ico($IC,'merge',13) !!}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="lm-empty">
                                    <div style="width:56px;height:56px;border-radius:14px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#FF5C00;opacity:.6;">{!! ico($IC,'inbox',28) !!}</div>
                                    <div style="font-size:14px;font-weight:700;color:#1D1D1D;margin-bottom:4px;">No leads found</div>
                                    <div style="font-size:12px;color:#9CA3AF;">Try adjusting your filters or scope</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination ── --}}
        <div class="lm-pager">
            <small style="color:#9CA3AF;font-size:11.5px;">
                Showing {{ number_format($leads->firstItem() ?? 0) }}–{{ number_format($leads->lastItem() ?? 0) }}
                of <strong style="color:#1D1D1D;">{{ number_format($leads->total()) }}</strong> results
            </small>
            {{ $leads->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>{{-- end lm-body --}}

{{-- ── Assign Manager Modal ── --}}
<div class="modal fade" id="assignManagerModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="assignManagerForm">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);border:none;padding:16px 20px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,.20);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;">
                            {!! ico($IC,'edit',16) !!}
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:#fff;font-weight:700;font-size:14px;font-family:'Poppins',sans-serif;">Assign Manager</h5>
                            <p style="color:rgba(255,255,255,.75);font-size:11.5px;margin:0;font-family:'Poppins',sans-serif;">Select a manager for this lead</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <label class="lm-fi-lbl" style="display:block;margin-bottom:5px;">Select Manager</label>
                    <select class="lm-fi" name="manager_id" required style="width:100%;">
                        <option value="">— Choose Manager —</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer" style="border-top:1px solid #F0F0F0;padding:12px 20px;gap:8px;">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="lm-apply-btn" style="width:auto;padding:7px 16px;">{!! ico($IC,'save',12) !!} Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Assign Telecaller Modal ── --}}
<div class="modal fade" id="assignTelecallerModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="assignTelecallerForm">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);border:none;padding:16px 20px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,.20);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;">
                            {!! ico($IC,'phone',16) !!}
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:#fff;font-weight:700;font-size:14px;font-family:'Poppins',sans-serif;">Assign Telecaller</h5>
                            <p style="color:rgba(255,255,255,.75);font-size:11.5px;margin:0;font-family:'Poppins',sans-serif;">Select a telecaller for this lead</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <label class="lm-fi-lbl" style="display:block;margin-bottom:5px;">Select Telecaller</label>
                    <select class="lm-fi" name="telecaller_id" required style="width:100%;">
                        <option value="">— Choose Telecaller —</option>
                        @foreach ($telecallers as $telecaller)
                            <option value="{{ $telecaller->id }}">{{ $telecaller->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer" style="border-top:1px solid #F0F0F0;padding:12px 20px;gap:8px;">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="lm-apply-btn" style="width:auto;padding:7px 16px;">{!! ico($IC,'save',12) !!} Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Merge Modal ── --}}
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="mergeForm">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);border:none;padding:16px 20px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,.20);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;">
                            {!! ico($IC,'merge',16) !!}
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:#fff;font-weight:700;font-size:14px;font-family:'Poppins',sans-serif;">Merge Duplicate Lead</h5>
                            <p style="color:rgba(255,255,255,.75);font-size:11.5px;margin:0;font-family:'Poppins',sans-serif;">Move all activities to the target lead</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:12px 14px;margin-bottom:14px;">
                        <span style="color:#D97706;flex-shrink:0;margin-top:1px;">{!! ico($IC,'alert',16) !!}</span>
                        <p style="margin:0;font-size:12.5px;color:#92400e;font-family:'Poppins',sans-serif;">Select the <strong>target</strong> lead to keep. All activities, call logs, and follow-ups from the source will be moved to the target. This cannot be undone.</p>
                    </div>
                    <label class="lm-fi-lbl" style="display:block;margin-bottom:5px;">Target Lead ID (merge INTO)</label>
                    <input type="number" class="lm-fi" id="mergeTargetId"
                        style="width:100%;"
                        placeholder="Enter target Lead ID" required min="1">
                </div>
                <div class="modal-footer" style="border-top:1px solid #F0F0F0;padding:12px 20px;gap:8px;">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="lm-apply-btn" style="width:auto;padding:7px 16px;background:#D97706;">{!! ico($IC,'merge',12) !!} Confirm Merge</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.lm-stat-grid,.lm-body,.lm-left-panel,.lm-table-card,.lm-tbl,.lm-pager,.lm-filter-form,.lm-scope-nav { font-family:'Poppins',sans-serif!important; }

/* ── KPI StatRow ── */
.lm-stat-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:14px; }
@media(max-width:1200px){ .lm-stat-grid{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:768px){ .lm-stat-grid{ grid-template-columns:repeat(2,1fr); } }
.lm-sr { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px; }
.lm-sr-or { background:#FF5C00;box-shadow:0 4px 14px rgba(255,92,0,.22); }
.lm-sr-wh { background:#FEFEFE;border:1px solid #F0F0F0;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.lm-sr-icon { width:32px;height:32px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
.lm-sr-or .lm-sr-icon { background:rgba(255,255,255,.18);color:#fff; }
.lm-sr-wh .lm-sr-icon { background:#FFF7ED;color:#FF5C00; }
.lm-sr-lbl { font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1px; }
.lm-sr-or .lm-sr-lbl { color:rgba(255,255,255,.75); }
.lm-sr-wh .lm-sr-lbl { color:#9CA3AF; }
.lm-sr-val { font-size:20px;font-weight:800;line-height:1; }
.lm-sr-or .lm-sr-val { color:#fff; }
.lm-sr-wh .lm-sr-val { color:#1D1D1D; }

/* ── Scope Tabs ── */
.lm-scope-nav { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px; }
.lm-scope-link { display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:10px;font-size:12px;font-weight:600;color:#374151;text-decoration:none;border:1px solid #F0F0F0;background:#FEFEFE;transition:all .15s;white-space:nowrap; }
.lm-scope-link:hover:not(.active) { background:#FFF7ED;border-color:#FED7AA;color:#FF5C00; }
.lm-scope-cnt { font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;background:#F3F4F6;color:#6B7280; }

/* ── 2-col layout ── */
.lm-body { display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start; }
@media(max-width:900px){ .lm-body{ grid-template-columns:1fr; } }

/* ── Left panel ── */
.lm-left-panel { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.lm-panel-head { display:flex;align-items:center;gap:7px;padding:12px 14px 10px; }
.lm-acc { width:3px;height:20px;background:#FF5C00;border-radius:2px;flex-shrink:0; }
.lm-panel-title { font-size:12px;font-weight:700;color:#1D1D1D;flex:1; }
.lm-active-badge { background:#FF5C00;color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px; }

/* ── Filter form ── */
.lm-filter-form { padding:0 12px 8px;display:flex;flex-direction:column;gap:9px; }
.lm-fi-lbl { font-size:9.5px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:4px;margin-bottom:4px; }
.lm-fi-wrap { position:relative; }
.lm-fi-ico { position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9CA3AF;pointer-events:none;display:flex; }
.lm-fi { width:100%;height:34px;border-radius:8px;border:1px solid #E5E7EB;font-size:12px;color:#1D1D1D;background:#FAFBFC;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s,box-shadow .15s;box-sizing:border-box;-webkit-appearance:none;appearance:none; }
.lm-fi-wrap .lm-fi { padding-left:32px; }
.lm-fi:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09);background:#fff; }
select.lm-fi { cursor:pointer; }
.lm-adv-toggle { width:100%;display:flex;align-items:center;gap:6px;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px;padding:7px 10px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;font-family:'Poppins',sans-serif!important;transition:all .15s; }
.lm-adv-toggle:hover { background:#FFF7ED;border-color:#FED7AA;color:#FF5C00; }
.lm-adv-toggle.active { border-color:#FF5C00;color:#FF5C00; }
.lm-adv-toggle span { flex:1; }
.lm-adv-on { background:#FF5C00;color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:8px; }
.lm-adv-chevron { display:flex;align-items:center;margin-left:auto; }
.lm-apply-btn { width:100%;background:#FF5C00;color:#fff;border:none;border-radius:8px;padding:8px;font-size:12.5px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;font-family:'Poppins',sans-serif!important; }
.lm-apply-btn:hover { background:#e05200; }
.lm-reset-btn { width:100%;background:#FEFEFE;color:#374151;border:1px solid #E5E7EB;border-radius:8px;padding:7px;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;text-decoration:none;font-family:'Poppins',sans-serif!important; }
.lm-reset-btn:hover { background:#F3F4F6; }

/* ── Right table card ── */
.lm-table-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.lm-table-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 18px;border-bottom:1px solid #F0F0F0;background:linear-gradient(135deg,#FAFBFC,#FEFEFE); }
.lm-badge { background:#FFF7ED;color:#FF5C00;border:1px solid #FED7AA;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px; }
.lm-tbl-wrap { overflow-y:auto;overflow-x:auto;max-height:560px; }
.lm-tbl-wrap::-webkit-scrollbar { width:5px; }
.lm-tbl-wrap::-webkit-scrollbar-thumb { background:#D1D5DB;border-radius:4px; }
.lm-tbl-wrap::-webkit-scrollbar-thumb:hover { background:#FF5C00; }
.lm-tbl { width:100%;border-collapse:separate;border-spacing:0; }
.lm-tbl thead th { position:sticky;top:0;z-index:2;background:#F4F6F8;color:#9CA3AF;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:10px 13px;white-space:nowrap;border-bottom:2px solid #F0F0F0; }
.lm-tbl tbody td { padding:11px 13px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F4F6F8; }
.lm-tbl tbody tr:last-child td { border-bottom:none; }
.lm-tbl tbody tr:nth-child(even) td { background:#FAFBFC; }
.lm-tbl tbody tr:hover td { background:#FFF7ED!important; }
.lm-tbl tbody tr:hover td:first-child { border-left:3px solid #FF5C00;padding-left:11px; }

/* ── Status pills ── */
.lm-status { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:10.5px;font-weight:700;white-space:nowrap; }
.lm-status-new { background:#EFF6FF;color:#1D4ED8; }
.lm-status-assigned { background:#F0FDF4;color:#15803D; }
.lm-status-contacted { background:#FEF9C3;color:#854D0E; }
.lm-status-interested { background:#F0F9FF;color:#0369A1; }
.lm-status-follow-up { background:#FEF3C7;color:#92400E; }
.lm-status-not-interested { background:#FEF2F2;color:#B91C1C; }
.lm-status-converted { background:#DCFCE7;color:#15803D; }
.lm-status-merged { background:#F3F4F6;color:#6B7280; }

/* ── Badges ── */
.lm-dup-badge { background:#FFF7ED;color:#EA580C;border:1px solid #FED7AA;font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;white-space:nowrap; }
.lm-sla-l2 { background:#FEF2F2;color:#DC2626;border:1px solid #FECACA;font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px; }
.lm-sla-l1 { background:#FFF7ED;color:#EA580C;border:1px solid #FED7AA;font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px; }
.lm-sla-esc { background:#FEFCE8;color:#CA8A04;border:1px solid #FDE68A;font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px; }

/* ── Action buttons ── */
.lm-btn { width:28px;height:28px;border-radius:7px;border:1px solid #E5E7EB;background:#F9FAFB;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;color:#6B7280; }
.lm-btn:hover { transform:translateY(-1px); }
.lm-btn-view { color:#1D4ED8;border-color:#BFDBFE; }
.lm-btn-view:hover { background:#EFF6FF;color:#1D4ED8; }
.lm-btn-mgr { color:#15803D;border-color:#BBF7D0; }
.lm-btn-mgr:hover { background:#F0FDF4; }
.lm-btn-tc { color:#FF5C00;border-color:#FED7AA; }
.lm-btn-tc:hover { background:#FFF7ED; }
.lm-btn-merge { color:#C2410C;border-color:#FED7AA; }
.lm-btn-merge:hover { background:#FFF7ED; }

/* ── Checkbox ── */
.lm-check { width:15px;height:15px;accent-color:#FF5C00;cursor:pointer; }

/* ── Pagination ── */
.lm-pager { padding:10px 16px;border-top:1px solid #F0F0F0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:9px;background:#FAFBFC; }
.lm-pager .page-link { background:#FEFEFE;border-color:#E5E7EB;color:#374151;font-size:11.5px;border-radius:7px;padding:4px 9px;font-family:'Poppins',sans-serif!important; }
.lm-pager .page-item.active .page-link { background:#FF5C00;border-color:#FF5C00;color:#fff; }
.lm-pager .page-item.disabled .page-link { opacity:.4; }

/* ── Empty ── */
.lm-empty { text-align:center;padding:52px 16px; }
</style>

<script>
(function () {
    // Custom date range toggle
    const drs = document.getElementById('dateRangeSelect');
    const dfw = document.getElementById('dateFromWrap');
    const dtw = document.getElementById('dateToWrap');
    drs?.addEventListener('change', function () {
        const show = this.value === 'custom';
        dfw.style.display = show ? '' : 'none';
        dtw.style.display = show ? '' : 'none';
    });

    // Advanced chevron toggle
    const advFilters = document.getElementById('advFilters');
    const advChevron = document.getElementById('advChevron');
    advFilters?.addEventListener('show.bs.collapse', () => {
        if (advChevron) advChevron.innerHTML = '{!! addslashes(ico($IC,'chevron-up',12)) !!}';
    });
    advFilters?.addEventListener('hide.bs.collapse', () => {
        if (advChevron) advChevron.innerHTML = '{!! addslashes(ico($IC,'chevron-down',12)) !!}';
    });

    // Select all checkboxes
    const selectAll  = document.getElementById('selectAllLeads');
    const checkboxes = () => Array.from(document.querySelectorAll('.lead-checkbox'));
    selectAll?.addEventListener('change', function () {
        checkboxes().forEach(cb => cb.checked = this.checked);
    });

    // Bulk assign — inject hidden lead_id inputs
    const bulkForm = document.getElementById('bulkAssignForm');
    bulkForm?.addEventListener('submit', function () {
        checkboxes().filter(cb => cb.checked).forEach(cb => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'lead_ids[]';
            inp.value = cb.value;
            this.appendChild(inp);
        });
    });

    // Assign Manager modal
    document.querySelectorAll('.assign-manager-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('assignManagerForm').action =
                '{{ url("admin/leads") }}/' + this.dataset.id + '/assign-manager';
            const sel = document.querySelector('#assignManagerModal select[name="manager_id"]');
            Array.from(sel.options).forEach(o => { o.disabled = false; o.selected = false; o.textContent = o.textContent.replace(' (current)', ''); });
            const cur = this.dataset.managerId;
            if (cur) {
                const opt = sel.querySelector('option[value="' + cur + '"]');
                if (opt) { opt.selected = true; opt.disabled = true; opt.textContent += ' (current)'; }
            }
        });
    });
    document.getElementById('assignManagerModal')?.addEventListener('hidden.bs.modal', function () {
        Array.from(this.querySelectorAll('select[name="manager_id"] option')).forEach(o => {
            o.disabled = false; o.textContent = o.textContent.replace(' (current)', '');
        });
    });

    // Assign Telecaller modal
    document.querySelectorAll('.assign-telecaller-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('assignTelecallerForm').action =
                '{{ url("admin/leads") }}/' + this.dataset.id + '/reassign-telecaller';
            const sel = document.querySelector('#assignTelecallerModal select[name="telecaller_id"]');
            Array.from(sel.options).forEach(o => { o.disabled = false; o.selected = false; o.textContent = o.textContent.replace(' (current)', ''); });
            const cur = this.dataset.telecallerId;
            if (cur) {
                const opt = sel.querySelector('option[value="' + cur + '"]');
                if (opt) { opt.selected = true; opt.disabled = true; opt.textContent += ' (current)'; }
            }
        });
    });
    document.getElementById('assignTelecallerModal')?.addEventListener('hidden.bs.modal', function () {
        Array.from(this.querySelectorAll('select[name="telecaller_id"] option')).forEach(o => {
            o.disabled = false; o.textContent = o.textContent.replace(' (current)', '');
        });
    });

    // Merge modal
    document.querySelectorAll('.merge-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const sourceId = this.dataset.source;
            document.getElementById('mergeTargetId').value = '';
            const form = document.getElementById('mergeForm');
            form.onsubmit = function (e) {
                e.preventDefault();
                const targetId = document.getElementById('mergeTargetId').value;
                if (!targetId) return;
                form.action = '{{ url("admin/leads") }}/' + sourceId + '/merge/' + targetId;
                form.submit();
            };
        });
    });
})();
</script>
@endsection
