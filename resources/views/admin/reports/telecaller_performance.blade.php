@extends('layouts.app')

@section('page_title', 'Telecaller Performance')

@php
$IC = [
    'filter'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'search'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>',
    'refresh-cw' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H3v5"/></svg>',
    'download'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline stroke-linecap="round" stroke-linejoin="round" points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3" stroke-linecap="round"/></svg>',
    'plus'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="12" x2="12" y1="5" y2="19" stroke-linecap="round"/><line x1="5" x2="19" y1="12" y2="12" stroke-linecap="round"/></svg>',
    'edit'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>',
    'trash'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
    'trending-up' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline stroke-linecap="round" stroke-linejoin="round" points="16 7 22 7 22 13"/></svg>',
    'phone'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'users'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'list'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="8" x2="21" y1="6" y2="6" stroke-linecap="round"/><line x1="8" x2="21" y1="12" y2="12" stroke-linecap="round"/><line x1="8" x2="21" y1="18" y2="18" stroke-linecap="round"/><line x1="3" x2="3.01" y1="6" y2="6" stroke-linecap="round"/><line x1="3" x2="3.01" y1="12" y2="12" stroke-linecap="round"/><line x1="3" x2="3.01" y1="18" y2="18" stroke-linecap="round"/></svg>',
    'book'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
    'external-link' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline stroke-linecap="round" stroke-linejoin="round" points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3" stroke-linecap="round"/></svg>',
    'calendar'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6" stroke-linecap="round"/><line x1="8" x2="8" y1="2" y2="6" stroke-linecap="round"/><line x1="3" x2="21" y1="10" y2="10" stroke-linecap="round"/></svg>',
    'bar-chart'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="18" x2="18" y1="20" y2="10" stroke-linecap="round"/><line x1="12" x2="12" y1="20" y2="4" stroke-linecap="round"/><line x1="6" x2="6" y1="20" y2="14" stroke-linecap="round"/></svg>',
    'clock'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline stroke-linecap="round" stroke-linejoin="round" points="12 6 12 12 16 14"/></svg>',
    'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline stroke-linecap="round" stroke-linejoin="round" points="22 4 12 14.01 9 11.01"/></svg>',
    'alert-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12" stroke-linecap="round"/><line x1="12" x2="12.01" y1="16" y2="16" stroke-linecap="round"/></svg>',
    'star'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon stroke-linecap="round" stroke-linejoin="round" points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'table'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="3" x2="21" y1="9" y2="9" stroke-linecap="round"/><line x1="3" x2="21" y1="15" y2="15" stroke-linecap="round"/><line x1="9" x2="9" y1="3" y2="21" stroke-linecap="round"/><line x1="15" x2="15" y1="3" y2="21" stroke-linecap="round"/></svg>',
    'source'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><ellipse cx="12" cy="5" rx="9" ry="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 1.66-4.03 3-9 3S3 13.66 3 12"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/></svg>',
    'headphones' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>',
];
function ico($IC, $name, $size=14) {
    if(!isset($IC[$name])) return '';
    return str_replace('<svg ','<svg width="'.$size.'" height="'.$size.'" ',$IC[$name]);
}
$rp = Auth::user()->role === 'report_viewer' ? 'report_viewer' : 'admin';
@endphp

@section('content')

@php
$tcActiveFilters = collect([
    ($filters['date_range'] ?? '30') !== '30'  ? 'Period: '.['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','quarter'=>'Quarter','year'=>'Year'][($filters['date_range'] ?? '30')] : null,
    ($filters['source']     ?? 'all') !== 'all' ? 'Source: '.$filters['source'] : null,
    ($filters['telecaller'] ?? 'all') !== 'all' ? 'Telecaller filtered' : null,
])->filter()->values();
@endphp

{{-- ── KPI StatRow (8 stats across 2 rows) ── --}}
<div class="tp-kpi-grid mb-3">
    <div class="tp-sr tp-sr-or">
        <div class="tp-sr-icon">{!! ico($IC,'users',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Active Telecallers</div>
            <div class="tp-sr-val">{{ $summary['total_telecallers'] }}</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#E0F2FE;color:#0284C7;">{!! ico($IC,'phone',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Total Calls</div>
            <div class="tp-sr-val">{{ number_format($summary['total_calls']) }}</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#ECFDF5;color:#10B981;">{!! ico($IC,'check-circle',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Converted</div>
            <div class="tp-sr-val">{{ number_format($summary['total_converted']) }}</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#FFFBEB;color:#D97706;">{!! ico($IC,'clock',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Talk Time</div>
            <div class="tp-sr-val">{{ $summary['total_talk_fmt'] }}</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#F5F3FF;color:#7C3AED;">{!! ico($IC,'bar-chart',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Avg Answer Rate</div>
            <div class="tp-sr-val">{{ $summary['avg_answer_rate'] }}%</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#E0F2FE;color:#0284C7;">{!! ico($IC,'trending-up',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Avg Conv. Rate</div>
            <div class="tp-sr-val">{{ $summary['avg_conversion_rate'] }}%</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#FEF2F2;color:#EF4444;">{!! ico($IC,'alert-circle',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Pending Follow-ups</div>
            <div class="tp-sr-val">{{ number_format($summary['total_pending_fu']) }}</div>
        </div>
    </div>
    <div class="tp-sr tp-sr-wh">
        <div class="tp-sr-icon" style="background:#FFFBEB;color:#D97706;">{!! ico($IC,'star',15) !!}</div>
        <div>
            <div class="tp-sr-lbl">Top Performer</div>
            <div class="tp-sr-val" style="font-size:13px;line-height:1.3;">{{ $summary['top_performer'] }}</div>
        </div>
    </div>
</div>

{{-- ── 2-column: filter left | charts+table right ── --}}
<div class="tp-body">

    {{-- LEFT: filter panel ── --}}
    <div class="tp-left-panel">

        <div class="tp-panel-head">
            <div class="tp-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'filter',13) !!}</span>
            <span class="tp-panel-title">Report Filters</span>
        </div>

        <form method="GET" class="tp-filter-form">
            <div>
                <label class="tp-fi-lbl">{!! ico($IC,'calendar',11) !!} Time Period</label>
                <select name="date_range" class="tp-fi">
                    <option value="7"       {{ ($filters['date_range'] ?? '30') === '7'       ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30"      {{ ($filters['date_range'] ?? '30') === '30'      ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90"      {{ ($filters['date_range'] ?? '30') === '90'      ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="quarter" {{ ($filters['date_range'] ?? '30') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year"    {{ ($filters['date_range'] ?? '30') === 'year'    ? 'selected' : '' }}>This Year</option>
                </select>
            </div>
            <div>
                <label class="tp-fi-lbl">{!! ico($IC,'source',11) !!} Lead Source</label>
                <select name="source" class="tp-fi">
                    <option value="all">All Sources</option>
                    @foreach (($filterOptions['sources'] ?? collect()) as $src)
                        <option value="{{ $src }}" {{ ($filters['source'] ?? 'all') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tp-fi-lbl">{!! ico($IC,'headphones',11) !!} Telecaller</label>
                <select name="telecaller" class="tp-fi">
                    <option value="all">All Telecallers</option>
                    @foreach (($filterOptions['telecallers'] ?? collect()) as $tc)
                        <option value="{{ $tc->id }}" {{ (string)($filters['telecaller'] ?? 'all') === (string)$tc->id ? 'selected' : '' }}>{{ $tc->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="tp-apply-btn">
                {!! ico($IC,'search',12) !!} Apply Filters
            </button>
            <a href="{{ route($rp . '.reports.telecaller-performance') }}" class="tp-reset-btn">
                {!! ico($IC,'refresh-cw',11) !!} Reset
            </a>

            @if($tcActiveFilters->count())
            <div style="padding-top:8px;border-top:1px solid #F0F0F0;">
                <div style="font-size:9px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Active Filters</div>
                @foreach($tcActiveFilters as $chip)
                <span style="background:#FFF7ED;color:#FF5C00;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:20px;border:1px solid #FED7AA;display:inline-flex;align-items:center;gap:3px;margin-bottom:4px;">
                    {!! ico($IC,'check-circle',9) !!} {{ $chip }}
                </span>
                @endforeach
            </div>
            @endif
        </form>

        <div style="height:1px;background:#F0F0F0;margin:0 12px;"></div>

        {{-- Action links ── --}}
        <div class="tp-panel-head" style="padding-top:10px;">
            <div class="tp-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'download',13) !!}</span>
            <span class="tp-panel-title">Export</span>
        </div>
        <div style="padding:0 12px 14px;display:flex;flex-direction:column;gap:7px;">
            <a href="{{ route($rp . '.reports.telecaller-lead-activity', request()->query()) }}"
               style="display:flex;align-items:center;gap:7px;padding:8px 11px;border-radius:9px;border:1px solid #F0F0F0;background:#FEFEFE;text-decoration:none;color:#374151;font-size:12px;font-weight:600;font-family:'Poppins',sans-serif;transition:all .15s;">
                {!! ico($IC,'external-link',13) !!} Lead Activity
            </a>
            <a href="{{ route($rp . '.reports.export', ['report' => 'telecaller-performance', 'format' => 'excel'] + request()->query()) }}"
               style="display:flex;align-items:center;gap:7px;padding:8px 11px;border-radius:9px;border:1px solid #D1FAE5;background:#ECFDF5;text-decoration:none;color:#065F46;font-size:12px;font-weight:600;font-family:'Poppins',sans-serif;transition:all .15s;">
                {!! ico($IC,'download',13) !!} Excel (.xlsx)
            </a>
            <a href="{{ route($rp . '.reports.export', ['report' => 'telecaller-performance', 'format' => 'pdf'] + request()->query()) }}" target="_blank"
               style="display:flex;align-items:center;gap:7px;padding:8px 11px;border-radius:9px;border:1px solid #FECACA;background:#FEF2F2;text-decoration:none;color:#991B1B;font-size:12px;font-weight:600;font-family:'Poppins',sans-serif;transition:all .15s;">
                {!! ico($IC,'download',13) !!} PDF
            </a>
        </div>
    </div>

    {{-- RIGHT: charts + table ── --}}
    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Charts Row 1 ── --}}
        <div class="tp-charts-row">
            <div class="tp-chart-card" style="flex:2 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Call Activity by Telecaller</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Answered vs Missed per agent</div>
                    </div>
                </div>
                <div style="height:240px;padding:0 16px 16px;"><canvas id="callChart"></canvas></div>
            </div>
            <div class="tp-chart-card" style="flex:1 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Performance Distribution</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Team score segmentation</div>
                    </div>
                </div>
                <div style="height:175px;padding:0 16px 8px;display:flex;align-items:center;justify-content:center;"><canvas id="distChart"></canvas></div>
                <div style="display:flex;justify-content:center;gap:10px;padding:0 16px 16px;font-size:10.5px;font-family:'Poppins',sans-serif;flex-wrap:wrap;">
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:2px;background:#10B981;display:inline-block;"></span>High (A)</span>
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:2px;background:#F59E0B;display:inline-block;"></span>Average (B/C)</span>
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:2px;background:#EF4444;display:inline-block;"></span>Needs Attn (D)</span>
                </div>
            </div>
        </div>

        {{-- Charts Row 2 ── --}}
        <div class="tp-charts-row">
            <div class="tp-chart-card" style="flex:1 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Conversion Rate</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Per telecaller — leads converted %</div>
                    </div>
                </div>
                <div style="height:220px;padding:0 16px 16px;"><canvas id="convChart"></canvas></div>
            </div>
            <div class="tp-chart-card" style="flex:1 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Follow-up Completion</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">% of scheduled follow-ups completed</div>
                    </div>
                </div>
                <div style="height:220px;padding:0 16px 16px;"><canvas id="fuChart"></canvas></div>
            </div>
            <div class="tp-chart-card" style="flex:1 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Efficiency Score</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Composite score out of 100</div>
                    </div>
                </div>
                <div style="height:220px;padding:0 16px 16px;"><canvas id="effChart"></canvas></div>
            </div>
        </div>

        {{-- Charts Row 3 ── --}}
        <div class="tp-charts-row">
            <div class="tp-chart-card" style="flex:1 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Total Talk Time</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Minutes on answered calls</div>
                    </div>
                </div>
                <div style="height:220px;padding:0 16px 16px;"><canvas id="talkChart"></canvas></div>
            </div>
            <div class="tp-chart-card" style="flex:1 1 0;">
                <div class="tp-chart-head">
                    <div class="tp-acc"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#1D1D1D;">Monthly Lead &amp; Call Trend</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Assigned, Converted &amp; Calls — last 6 months</div>
                    </div>
                </div>
                <div style="height:220px;padding:0 16px 16px;"><canvas id="trendChart"></canvas></div>
            </div>
        </div>

        {{-- Performance Table ── --}}
        <div class="tp-table-card">
            <div class="tp-table-head">
                <div style="display:flex;align-items:center;gap:9px;">
                    <div class="tp-acc"></div>
                    <span style="color:#FF5C00;display:flex;">{!! ico($IC,'table',14) !!}</span>
                    <div>
                        <div style="font-size:13.5px;font-weight:700;color:#1D1D1D;">Telecaller Performance Breakdown</div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Ranked by efficiency score</div>
                    </div>
                </div>
                <span class="tp-badge">{{ $rows->count() }} telecallers</span>
            </div>
            <div class="tp-tbl-wrap">
                <table class="tp-tbl">
                    <thead>
                        <tr>
                            <th style="width:44px;">#</th>
                            <th>Telecaller</th>
                            <th style="text-align:center;width:44px;">Grade</th>
                            <th style="text-align:center;">Assigned</th>
                            <th style="text-align:center;">Conv.</th>
                            <th style="text-align:center;">Active</th>
                            <th style="text-align:center;">Lost</th>
                            <th style="text-align:center;">Calls</th>
                            <th style="text-align:center;">Answered</th>
                            <th style="text-align:center;">Missed</th>
                            <th style="text-align:center;">Answer %</th>
                            <th style="text-align:center;">Avg Talk</th>
                            <th style="text-align:center;">Calls/Lead</th>
                            <th style="text-align:center;">Followup %</th>
                            <th style="text-align:center;">Pending</th>
                            <th style="text-align:center;">Conv %</th>
                            <th style="min-width:140px;">Score</th>
                            <th style="text-align:center;">Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                        @php
                            $rank       = $i + 1;
                            $score      = $r['efficiency_score'];
                            $scoreColor = $score >= 70 ? '#10B981' : ($score >= 40 ? '#F59E0B' : '#EF4444');
                            $scoreBg    = $score >= 70 ? 'rgba(16,185,129,.12)' : ($score >= 40 ? 'rgba(245,158,11,.12)' : 'rgba(239,68,68,.12)');
                            $convColor  = $r['conversion_rate'] >= 50 ? '#10B981' : ($r['conversion_rate'] >= 25 ? '#F59E0B' : '#EF4444');
                            $convBg     = $r['conversion_rate'] >= 50 ? 'rgba(16,185,129,.10)' : ($r['conversion_rate'] >= 25 ? 'rgba(245,158,11,.10)' : 'rgba(239,68,68,.10)');
                            $fuColor    = $r['followup_rate'] >= 70 ? '#10B981' : ($r['followup_rate'] >= 40 ? '#F59E0B' : '#EF4444');
                            $fuBg       = $r['followup_rate'] >= 70 ? 'rgba(16,185,129,.10)' : ($r['followup_rate'] >= 40 ? 'rgba(245,158,11,.10)' : 'rgba(239,68,68,.10)');
                            $ansColor   = $r['answer_rate'] >= 75 ? '#10B981' : ($r['answer_rate'] >= 50 ? '#F59E0B' : '#EF4444');
                            $ansBg      = $r['answer_rate'] >= 75 ? 'rgba(16,185,129,.10)' : ($r['answer_rate'] >= 50 ? 'rgba(245,158,11,.10)' : 'rgba(239,68,68,.10)');
                            $gradeColors = ['A'=>['bg'=>'#D1FAE5','c'=>'#065F46'],'B'=>['bg'=>'#DBEAFE','c'=>'#1E40AF'],'C'=>['bg'=>'#FEF3C7','c'=>'#92400E'],'D'=>['bg'=>'#FEE2E2','c'=>'#991B1B']];
                            $gc = $gradeColors[$r['grade']] ?? ['bg'=>'#F3F4F6','c'=>'#6B7280'];
                            $rankColors = ['#F59E0B','#9CA3AF','#B45309'];
                            $rankSymbols = ['1st','2nd','3rd'];
                        @endphp
                        <tr>
                            <td style="text-align:center;">
                                @if ($rank <= 3)
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:7px;background:{{ $rank===1?'#FFF7ED':($rank===2?'#F9FAFB':'#FFF7ED') }};color:{{ $rankColors[$rank-1] }};font-size:10px;font-weight:800;">{{ $rankSymbols[$rank-1] }}</span>
                                @else
                                    <span style="font-size:11px;font-weight:700;color:#9CA3AF;">#{{ $rank }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;">{{ $r['name'] }}</div>
                                <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">{{ number_format($r['total_talk_mins']) }} min talk</div>
                            </td>
                            <td style="text-align:center;">
                                <span style="width:26px;height:26px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;background:{{ $gc['bg'] }};color:{{ $gc['c'] }};">{{ $r['grade'] }}</span>
                            </td>
                            <td style="text-align:center;font-weight:600;">{{ number_format($r['assigned']) }}</td>
                            <td style="text-align:center;"><span style="font-weight:700;color:#10B981;">{{ number_format($r['converted']) }}</span></td>
                            <td style="text-align:center;"><span style="color:#FF5C00;">{{ number_format($r['active']) }}</span></td>
                            <td style="text-align:center;"><span style="color:#EF4444;">{{ number_format($r['lost']) }}</span></td>
                            <td style="text-align:center;font-weight:600;">{{ number_format($r['calls']) }}</td>
                            <td style="text-align:center;color:#10B981;">{{ number_format($r['answered']) }}</td>
                            <td style="text-align:center;color:#EF4444;">{{ number_format($r['missed']) }}</td>
                            <td style="text-align:center;">
                                <span style="background:{{ $ansBg }};color:{{ $ansColor }};font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap;">{{ $r['answer_rate'] }}%</span>
                            </td>
                            <td style="text-align:center;font-size:11px;color:#9CA3AF;">{{ $r['avg_talk_time'] }}</td>
                            <td style="text-align:center;">
                                <span style="background:#F4F6F8;border:1px solid #F0F0F0;font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;color:#4B5563;">{{ $r['calls_per_lead'] }}x</span>
                            </td>
                            <td style="text-align:center;">
                                <span style="background:{{ $fuBg }};color:{{ $fuColor }};font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap;">{{ $r['followup_rate'] }}%</span>
                            </td>
                            <td style="text-align:center;">
                                @if ($r['pending_followups'] > 0)
                                    <span style="background:rgba(239,68,68,.12);color:#DC2626;font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;">{{ $r['pending_followups'] }}</span>
                                @else
                                    <span style="color:#9CA3AF;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span style="background:{{ $convBg }};color:{{ $convColor }};font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap;">{{ $r['conversion_rate'] }}%</span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="flex:1;height:7px;border-radius:99px;background:#F0F0F0;overflow:hidden;">
                                        <div style="height:100%;border-radius:99px;background:{{ $scoreColor }};width:{{ min(100,$score) }}%;transition:width .4s;"></div>
                                    </div>
                                    <span style="font-size:11.5px;font-weight:800;color:{{ $scoreColor }};min-width:26px;">{{ $score }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route($rp . '.reports.telecaller-lead-activity', array_merge(request()->query(), ['telecaller' => $r['id']])) }}"
                                   class="tp-btn-link" title="View lead activity">{!! ico($IC,'external-link',13) !!}</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="18">
                                <div class="tp-empty">
                                    <div style="width:56px;height:56px;border-radius:14px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#FF5C00;opacity:.6;">{!! ico($IC,'bar-chart',28) !!}</div>
                                    <div style="font-size:14px;font-weight:700;color:#1D1D1D;margin-bottom:4px;">No telecaller data found</div>
                                    <div style="font-size:12px;color:#9CA3AF;">Try adjusting your filters</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Score legend ── --}}
            <div class="tp-legend">
                <span style="display:inline-flex;align-items:center;gap:5px;">
                    <span style="width:20px;height:20px;border-radius:6px;background:#D1FAE5;color:#065F46;font-size:10px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">A</span>
                    Score ≥ 70: High Performer
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;">
                    <span style="width:20px;height:20px;border-radius:6px;background:#DBEAFE;color:#1E40AF;font-size:10px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">B</span>
                    Score 40–69: Average
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;">
                    <span style="width:20px;height:20px;border-radius:6px;background:#FEF3C7;color:#92400E;font-size:10px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">C</span>
                    Score 20–39: Below Average
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;">
                    <span style="width:20px;height:20px;border-radius:6px;background:#FEE2E2;color:#991B1B;font-size:10px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">D</span>
                    Score &lt; 20: Needs Attention
                </span>
                <span style="margin-left:auto;color:#9CA3AF;font-size:10px;">Score = Conversion (40%) + Followup (35%) + Answer (25%)</span>
            </div>
        </div>

    </div>{{-- end right col --}}

</div>{{-- end tp-body --}}

<style>
.tp-kpi-grid,.tp-body,.tp-left-panel,.tp-table-card,.tp-tbl,.tp-legend,.tp-filter-form,.tp-chart-card { font-family:'Poppins',sans-serif!important; }

/* ── KPI row ── */
.tp-kpi-grid { display:grid;grid-template-columns:repeat(8,1fr);gap:10px; }
@media(max-width:1400px){ .tp-kpi-grid{ grid-template-columns:repeat(4,1fr); } }
@media(max-width:900px){ .tp-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
.tp-sr { display:flex;align-items:center;gap:9px;padding:10px 11px;border-radius:10px; }
.tp-sr-or { background:#FF5C00;box-shadow:0 4px 14px rgba(255,92,0,.22); }
.tp-sr-wh { background:#FEFEFE;border:1px solid #F0F0F0;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.tp-sr-icon { width:30px;height:30px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
.tp-sr-or .tp-sr-icon { background:rgba(255,255,255,.18);color:#fff; }
.tp-sr-wh .tp-sr-icon { background:#FFF7ED;color:#FF5C00; }
.tp-sr-lbl { font-size:8.5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px; }
.tp-sr-or .tp-sr-lbl { color:rgba(255,255,255,.75); }
.tp-sr-wh .tp-sr-lbl { color:#9CA3AF; }
.tp-sr-val { font-size:19px;font-weight:800;line-height:1; }
.tp-sr-or .tp-sr-val { color:#fff; }
.tp-sr-wh .tp-sr-val { color:#1D1D1D; }

/* ── 2-col layout ── */
.tp-body { display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start; }
@media(max-width:1024px){ .tp-body{ grid-template-columns:1fr; } }

/* ── Left panel ── */
.tp-left-panel { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden;position:sticky;top:16px; }
.tp-panel-head { display:flex;align-items:center;gap:7px;padding:12px 14px 10px; }
.tp-acc { width:3px;height:20px;background:#FF5C00;border-radius:2px;flex-shrink:0; }
.tp-panel-title { font-size:12px;font-weight:700;color:#1D1D1D; }

/* ── Filter form ── */
.tp-filter-form { padding:4px 12px 14px;display:flex;flex-direction:column;gap:9px; }
.tp-fi-lbl { font-size:9.5px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:4px;margin-bottom:4px; }
.tp-fi { width:100%;height:34px;border-radius:8px;border:1px solid #E5E7EB;font-size:12.5px;color:#1D1D1D;background:#FAFBFC;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s,box-shadow .15s;box-sizing:border-box; }
.tp-fi:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09);background:#fff; }
.tp-apply-btn { width:100%;background:#FF5C00;color:#fff;border:none;border-radius:8px;padding:8px;font-size:12.5px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;font-family:'Poppins',sans-serif!important; }
.tp-apply-btn:hover { background:#e05200; }
.tp-reset-btn { width:100%;background:#FEFEFE;color:#374151;border:1px solid #E5E7EB;border-radius:8px;padding:7px;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;text-decoration:none;font-family:'Poppins',sans-serif!important; }
.tp-reset-btn:hover { background:#F3F4F6; }

/* ── Chart cards ── */
.tp-charts-row { display:flex;gap:12px;flex-wrap:wrap; }
.tp-chart-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden;min-width:0; }
.tp-chart-head { display:flex;align-items:center;gap:9px;padding:14px 16px 10px; }

/* ── Table card ── */
.tp-table-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.tp-table-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 18px;border-bottom:1px solid #F0F0F0;background:linear-gradient(135deg,#FAFBFC,#FEFEFE); }
.tp-badge { background:#FFF7ED;color:#FF5C00;border:1px solid #FED7AA;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px; }
.tp-tbl-wrap { overflow-y:auto;overflow-x:auto;max-height:540px; }
.tp-tbl-wrap::-webkit-scrollbar { width:5px;height:5px; }
.tp-tbl-wrap::-webkit-scrollbar-thumb { background:#D1D5DB;border-radius:4px; }
.tp-tbl-wrap::-webkit-scrollbar-thumb:hover { background:#FF5C00; }
.tp-tbl { width:100%;border-collapse:separate;border-spacing:0; }
.tp-tbl thead th { position:sticky;top:0;z-index:2;background:#F4F6F8;color:#9CA3AF;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:10px 11px;white-space:nowrap;border-bottom:2px solid #F0F0F0; }
.tp-tbl tbody td { padding:10px 11px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F4F6F8; }
.tp-tbl tbody tr:last-child td { border-bottom:none; }
.tp-tbl tbody tr:nth-child(even) td { background:#FAFBFC; }
.tp-tbl tbody tr:hover td { background:#FFF7ED!important; }
.tp-tbl tbody tr:hover td:first-child { border-left:3px solid #FF5C00;padding-left:12px; }

/* ── Action button ── */
.tp-btn-link { width:28px;height:28px;border-radius:7px;border:1px solid #E5E7EB;background:#F9FAFB;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;color:#FF5C00;border-color:#FED7AA; }
.tp-btn-link:hover { background:#FFF7ED;transform:translateY(-1px); }

/* ── Legend ── */
.tp-empty { text-align:center;padding:52px 16px; }
.tp-legend { padding:12px 18px;border-top:1px solid #F0F0F0;display:flex;align-items:center;flex-wrap:wrap;gap:12px;font-size:11px;color:#9CA3AF;background:#FAFBFC;font-family:'Poppins',sans-serif; }
</style>

<script>
(function () {
    function _init() {
        const rows        = @json($rows);
        const dist        = @json($perfDist);
        const monthLabels = @json($monthLabels);
        const monthAsgn   = @json($monthAssigned);
        const monthConv   = @json($monthConverted);
        const monthCalls  = @json($monthCalls);

        const names = rows.map(r => r.name);
        const GRID  = { color: 'rgba(0,0,0,.04)' };
        const TICK  = { color: '#9CA3AF', font: { size: 10, family: "'Poppins',sans-serif" } };

        /* ── 1. Call Activity ── */
        new Chart(document.getElementById('callChart'), {
            type: 'bar',
            data: {
                labels: names,
                datasets: [
                    { label: 'Answered', data: rows.map(r => r.answered), backgroundColor: '#10B981', borderRadius: 4 },
                    { label: 'Missed',   data: rows.map(r => r.missed),   backgroundColor: '#EF4444', borderRadius: 4 },
                ]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11, family: "'Poppins',sans-serif" } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { ...TICK, precision: 0 }, grid: GRID },
                    x: { ticks: TICK, grid: { display: false } }
                }
            }
        });

        /* ── 2. Performance Distribution doughnut ── */
        new Chart(document.getElementById('distChart'), {
            type: 'doughnut',
            data: {
                labels: ['High (A)', 'Average (B/C)', 'Needs Attention (D)'],
                datasets: [{
                    data: [dist.high, dist.average, dist.low],
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} telecaller(s)` } }
                }
            }
        });

        /* ── 3. Conversion Rate ── */
        new Chart(document.getElementById('convChart'), {
            type: 'bar',
            data: {
                labels: names,
                datasets: [{
                    label: 'Conversion %',
                    data: rows.map(r => r.conversion_rate),
                    backgroundColor: rows.map(r => r.conversion_rate >= 50 ? '#10B981' : r.conversion_rate >= 25 ? '#F59E0B' : '#EF4444'),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 100, ticks: { ...TICK, callback: v => v + '%' }, grid: GRID },
                    y: { ticks: TICK, grid: { display: false } }
                }
            }
        });

        /* ── 4. Follow-up Rate ── */
        new Chart(document.getElementById('fuChart'), {
            type: 'bar',
            data: {
                labels: names,
                datasets: [{
                    label: 'Followup %',
                    data: rows.map(r => r.followup_rate),
                    backgroundColor: rows.map(r => r.followup_rate >= 70 ? '#FF5C00' : r.followup_rate >= 40 ? '#F59E0B' : '#9CA3AF'),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 100, ticks: { ...TICK, callback: v => v + '%' }, grid: GRID },
                    y: { ticks: TICK, grid: { display: false } }
                }
            }
        });

        /* ── 5. Efficiency Score ── */
        new Chart(document.getElementById('effChart'), {
            type: 'bar',
            data: {
                labels: names,
                datasets: [{
                    label: 'Efficiency Score',
                    data: rows.map(r => r.efficiency_score),
                    backgroundColor: rows.map(r =>
                        r.efficiency_score >= 70 ? 'rgba(16,185,129,.85)' :
                        r.efficiency_score >= 40 ? 'rgba(245,158,11,.85)' : 'rgba(239,68,68,.85)'
                    ),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 100, ticks: TICK, grid: GRID },
                    y: { ticks: TICK, grid: { display: false } }
                }
            }
        });

        /* ── 6. Talk Time ── */
        new Chart(document.getElementById('talkChart'), {
            type: 'bar',
            data: {
                labels: names,
                datasets: [{
                    label: 'Talk Time (min)',
                    data: rows.map(r => r.total_talk_mins),
                    backgroundColor: 'rgba(255,92,0,.75)',
                    borderRadius: 4,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { ...TICK, callback: v => v + 'm' }, grid: GRID },
                    x: { ticks: TICK, grid: { display: false } }
                }
            }
        });

        /* ── 7. Monthly Trend ── */
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Assigned',
                        data: monthAsgn,
                        borderColor: '#FF5C00', backgroundColor: 'rgba(255,92,0,.08)',
                        borderWidth: 2, tension: 0.35, fill: true, pointRadius: 4,
                    },
                    {
                        label: 'Converted',
                        data: monthConv,
                        borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,.08)',
                        borderWidth: 2, tension: 0.35, fill: true, pointRadius: 4,
                    },
                    {
                        label: 'Calls',
                        data: monthCalls,
                        borderColor: '#9CA3AF', backgroundColor: 'transparent',
                        borderWidth: 2, borderDash: [4,3], tension: 0.35, fill: false, pointRadius: 3,
                    },
                ]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11, family: "'Poppins',sans-serif" } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { ...TICK, precision: 0 }, grid: GRID },
                    x: { ticks: TICK, grid: { display: false } }
                }
            }
        });
    }

    if (typeof Chart !== 'undefined') {
        _init();
    } else {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        s.onload = _init;
        document.head.appendChild(s);
    }
})();
</script>
@endsection
