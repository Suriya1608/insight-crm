@extends('layouts.app')
@section('page_title', 'Period Report')

@section('content')
@php
$rp = Auth::user()->role === 'report_viewer' ? 'report_viewer' : 'admin';
$activeFilters = collect([
    ($filters['date_range'] ?? '30') === 'custom'
        ? 'Custom: '.($filters['from_date'] ?? '').' → '.($filters['to_date'] ?? '')
        : (($filters['date_range'] ?? '30') !== '30' ? 'Period: '.['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','quarter'=>'Quarter','year'=>'Year'][($filters['date_range'] ?? '30')] : null),
    ($filters['source']     ?? 'all') !== 'all' ? 'Source: '.$filters['source'] : null,
    ($filters['telecaller'] ?? 'all') !== 'all' ? 'Telecaller filtered' : null,
    ($filters['manager']    ?? 'all') !== 'all' ? 'Manager filtered' : null,
])->filter()->values();
$today   = now()->format('Y-m-d');
$fromDef = $filters['from_date'] ?? now()->subDays(30)->format('Y-m-d');
$toDef   = $filters['to_date']   ?? $today;
@endphp

<style>
/* ── Insights ── */
.insight-bar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:1rem}
.insight-item{display:flex;align-items:flex-start;gap:8px;flex:1;min-width:240px;border-radius:12px;padding:10px 14px;border-left:3px solid}
.insight-item.success{background:#f0fdf4;border-color:#10b981}
.insight-item.danger {background:#fef2f2;border-color:#ef4444}
.insight-item.warning{background:#fffbeb;border-color:#f59e0b}
.insight-item.info   {background:#f0f9ff;border-color:#0ea5e9}
.insight-item .material-icons{font-size:18px;margin-top:1px;flex-shrink:0}
.insight-item.success .material-icons{color:#10b981}
.insight-item.danger  .material-icons{color:#ef4444}
.insight-item.warning .material-icons{color:#f59e0b}
.insight-item.info    .material-icons{color:#0ea5e9}
.insight-item span:last-child{font-size:.78rem;color:#334155;font-weight:500;line-height:1.45}

/* ── Filter ── */
.rpt-filter-wrap{background:#fff;border-radius:16px;box-shadow:0 1px 8px rgba(15,23,42,.08);overflow:hidden;margin-bottom:1.25rem}
.rpt-filter-head{background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);padding:11px 20px;display:flex;align-items:center;justify-content:space-between}
.rpt-filter-head-title{display:flex;align-items:center;gap:7px;color:#fff;font-size:.82rem;font-weight:700;letter-spacing:.3px}
.rpt-filter-head-title .material-icons{font-size:17px;opacity:.9}
.rpt-filter-body{padding:16px 20px 18px}
.rpt-filter-field{display:flex;flex-direction:column;gap:5px;min-width:130px;flex:1}
.rpt-filter-lbl{font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.6px;display:flex;align-items:center;gap:5px}
.rpt-filter-lbl .material-icons{font-size:13px;color:#6366f1}
.rpt-filter-sel,.rpt-filter-date{border:1.5px solid #e2e8f0;border-radius:9px;padding:7px 12px;font-size:.82rem;font-weight:500;color:#0f172a;background-color:#f8fafc;outline:none;cursor:pointer;transition:border-color .18s,box-shadow .18s,background-color .18s;width:100%}
.rpt-filter-sel{padding-right:32px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;-webkit-appearance:none;appearance:none}
.rpt-filter-sel:focus,.rpt-filter-date:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background-color:#fff}
.rpt-filter-sel:hover,.rpt-filter-date:hover{border-color:#a5b4fc;background-color:#fff}
.date-separator{display:flex;align-items:flex-end;padding-bottom:8px;color:#94a3b8;font-size:.9rem;font-weight:700}
.rpt-filter-div{width:1px;background:#e2e8f0;align-self:stretch;margin:0 4px;flex-shrink:0}
.rpt-btn-apply{background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;color:#fff;border-radius:9px;padding:8px 18px;font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(99,102,241,.35);transition:transform .15s,box-shadow .15s;cursor:pointer}
.rpt-btn-apply:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(99,102,241,.45);color:#fff}
.rpt-btn-apply .material-icons{font-size:15px}
.rpt-btn-reset{background:#f1f5f9;border:1.5px solid #e2e8f0;color:#475569;border-radius:9px;padding:7px 14px;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:5px;transition:all .15s;text-decoration:none}
.rpt-btn-reset:hover{background:#e2e8f0;color:#1e293b}
.rpt-btn-reset .material-icons{font-size:14px}
.rpt-btn-download{background:#fff;border:1.5px solid #e2e8f0;color:#475569;border-radius:9px;padding:7px 14px;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:5px;cursor:pointer;transition:all .15s}
.rpt-btn-download:hover{border-color:#6366f1;color:#6366f1;background:#eef2ff}
.rpt-btn-download::after{display:none}
.rpt-btn-download .material-icons{font-size:14px}
.rpt-dl-menu{border:1.5px solid #e2e8f0 !important;border-radius:12px !important;padding:6px !important;box-shadow:0 8px 24px rgba(15,23,42,.12) !important;min-width:152px}
.rpt-dl-excel,.rpt-dl-pdf{display:flex !important;align-items:center !important;gap:8px;padding:8px 12px !important;border-radius:8px !important;font-size:.82rem !important;font-weight:600;color:#475569}
.rpt-dl-excel:hover{color:#10b981 !important;background:#ecfdf5 !important}
.rpt-dl-pdf:hover  {color:#ef4444 !important;background:#fef2f2 !important}
.rpt-dl-excel .material-icons{font-size:15px;color:#10b981}
.rpt-dl-pdf   .material-icons{font-size:15px;color:#ef4444}
/* Quick date shortcuts */
.quick-dates{display:flex;gap:5px;flex-wrap:wrap;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9}
.quick-date-btn{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0;cursor:pointer;transition:all .15s;text-decoration:none}
.quick-date-btn:hover,.quick-date-btn.active{background:#eef2ff;color:#6366f1;border-color:#c7d2fe}

/* ── Banner ── */
.period-banner{background:linear-gradient(135deg,#1e3a6e,#0f172a);border-radius:16px;padding:16px 22px;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 14px rgba(15,23,42,.35);flex-wrap:wrap;gap:12px}
.period-banner-left .sub{font-size:.7rem;color:rgba(255,255,255,.65);font-weight:600;text-transform:uppercase;letter-spacing:.6px}
.period-banner-left .range{font-size:1.15rem;font-weight:800;color:#fff;margin-top:3px;display:flex;align-items:center;gap:8px}
.period-banner-left .range .material-icons{font-size:19px;opacity:.85}
.period-banner-right{display:flex;gap:20px;text-align:center}
.period-banner-stat .val{font-size:1.5rem;font-weight:800;color:#fff}
.period-banner-stat .lbl{font-size:.68rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.5px}
.period-banner-div{width:1px;background:rgba(255,255,255,.2)}

/* ── KPI cards ── */
.kpi-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 8px rgba(15,23,42,.07);height:100%;position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s}
.kpi-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(15,23,42,.1)}
.kpi-card .accent{position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0}
.kpi-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.kpi-value{font-size:2rem;font-weight:800;color:#0f172a;line-height:1.1}
.kpi-label{font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-top:5px}
.kpi-sub{font-size:.75rem;color:#64748b;margin-top:4px;display:flex;align-items:center;gap:3px}

/* ── Chart card ── */
.chart-card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 1px 8px rgba(15,23,42,.07);height:100%}
.ch{font-size:.9rem;font-weight:700;color:#0f172a;margin:0 0 2px}
.cs{font-size:.73rem;color:#94a3b8;margin:0 0 16px}

/* ── Enhanced Table ── */
.period-table{width:100%;border-collapse:collapse}
.period-table thead th{font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;
    background:linear-gradient(0deg,#f8fafc,#fff);border-bottom:2px solid #e2e8f0;padding:10px 12px;white-space:nowrap;cursor:pointer;user-select:none}
.period-table thead th:hover{color:#6366f1}
.period-table tbody td{padding:10px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle;font-size:.82rem;transition:background .12s}
.period-table tbody tr:last-child td{border-bottom:0}
.period-table tbody tr:hover td{background:#f8faff}
.period-table tbody tr:nth-child(even) td{background:#fafbff}
.period-table tbody tr:nth-child(even):hover td{background:#f0f2ff}
.sort-icon{font-size:12px;vertical-align:middle;opacity:.4}

/* ── DOW Badge ── */
.dow-badge{display:inline-flex;align-items:center;justify-content:center;padding:2px 8px;border-radius:20px;font-size:.68rem;font-weight:700;min-width:34px}
.dow-Mon,.dow-Tue,.dow-Wed,.dow-Thu,.dow-Fri{background:#dbeafe;color:#1e40af}
.dow-Sat{background:#fce7f3;color:#9d174d}
.dow-Sun{background:#fee2e2;color:#991b1b}

/* ── Progress bar ── */
.pb-wrap{display:flex;align-items:center;gap:6px}
.pb-track{flex:1;height:7px;background:#f1f5f9;border-radius:4px;overflow:hidden}
.pb-fill{height:100%;border-radius:4px}

/* ── Volume badge ── */
.vol-badge{display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700}
.vol-badge.peak{background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;box-shadow:0 2px 6px rgba(245,158,11,.4)}
.vol-badge.above{background:#dcfce7;color:#16a34a}
.vol-badge.below{background:#fee2e2;color:#dc2626}
.vol-badge.normal{background:#f1f5f9;color:#64748b}
</style>

{{-- ═══ INSIGHTS STRIP ═══ --}}
@if(!empty($insights ?? []))
<div class="insight-bar">
    @foreach($insights as $ins)
    <div class="insight-item {{ $ins['type'] }}">
        <span class="material-icons">{{ $ins['icon'] }}</span>
        <span>{{ $ins['text'] }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- ═══ FILTER ═══ --}}
<div class="rpt-filter-wrap">
    <div class="rpt-filter-head">
        <div class="rpt-filter-head-title">
            <span class="material-icons">tune</span> Report Filters
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($activeFilters->count())
            <span style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:rgba(255,255,255,.8);background:rgba(255,255,255,.15);padding:3px 10px;border-radius:20px">
                <span class="material-icons" style="font-size:13px">filter_alt</span>{{ $activeFilters->count() }} active
            </span>
            @endif
            <span style="font-size:.72rem;color:rgba(255,255,255,.55)">Period Report</span>
        </div>
    </div>
    <div class="rpt-filter-body">
        <form method="GET" id="periodForm">
            <div class="d-flex flex-wrap gap-3 align-items-end">
                {{-- From Date --}}
                <div class="rpt-filter-field" style="max-width:160px">
                    <label class="rpt-filter-lbl"><span class="material-icons">event</span> From Date</label>
                    <input type="date" id="fromDate" name="from_date" class="rpt-filter-date"
                        value="{{ $fromDef }}" max="{{ $today }}">
                </div>
                {{-- To Date --}}
                <div class="rpt-filter-field" style="max-width:160px">
                    <label class="rpt-filter-lbl"><span class="material-icons">event</span> To Date</label>
                    <input type="date" id="toDate" name="to_date" class="rpt-filter-date"
                        value="{{ $toDef }}" max="{{ $today }}">
                </div>
                <input type="hidden" name="date_range" id="dateRangeHidden" value="{{ $filters['date_range'] ?? '30' }}">

                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">source</span> Source</label>
                    <select name="source" class="rpt-filter-sel">
                        <option value="all">All Sources</option>
                        @foreach (($filterOptions['sources'] ?? collect()) as $src)
                            <option value="{{ $src }}" {{ ($filters['source'] ?? 'all') === $src ? 'selected' : '' }}>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">headset_mic</span> Telecaller</label>
                    <select name="telecaller" class="rpt-filter-sel">
                        <option value="all">All Telecallers</option>
                        @foreach (($filterOptions['telecallers'] ?? collect()) as $tc)
                            <option value="{{ $tc->id }}" {{ (string)($filters['telecaller'] ?? 'all') === (string)$tc->id ? 'selected' : '' }}>{{ $tc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">manage_accounts</span> Manager</label>
                    <select name="manager" class="rpt-filter-sel">
                        <option value="all">All Managers</option>
                        @foreach (($filterOptions['managers'] ?? collect()) as $mgr)
                            <option value="{{ $mgr->id }}" {{ (string)($filters['manager'] ?? 'all') === (string)$mgr->id ? 'selected' : '' }}>{{ $mgr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rpt-filter-div d-none d-md-block"></div>
                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <button type="submit" class="rpt-btn-apply"><span class="material-icons">search</span> Apply</button>
                    <a href="{{ route($rp . '.reports.period') }}" class="rpt-btn-reset"><span class="material-icons">refresh</span> Reset</a>
                    <div class="dropdown">
                        <button class="rpt-btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">file_download</span> Export
                        </button>
                        <ul class="dropdown-menu rpt-dl-menu">
                            <li><a class="dropdown-item rpt-dl-excel" href="{{ route($rp . '.reports.export', ['report' => 'period', 'format' => 'excel'] + request()->query()) }}">
                                <span class="material-icons">table_view</span> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item rpt-dl-pdf" href="{{ route($rp . '.reports.export', ['report' => 'period', 'format' => 'pdf'] + request()->query()) }}" target="_blank">
                                <span class="material-icons">picture_as_pdf</span> PDF Report
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Quick preset shortcuts --}}
            <div class="quick-dates">
                <span style="font-size:.68rem;color:#94a3b8;font-weight:700;align-self:center;text-transform:uppercase;letter-spacing:.5px">Quick:</span>
                @php
                    $presets = [
                        '7'       => ['Last 7 Days',    now()->subDays(7)->format('Y-m-d'),              now()->format('Y-m-d')],
                        '30'      => ['Last 30 Days',   now()->subDays(30)->format('Y-m-d'),             now()->format('Y-m-d')],
                        '90'      => ['Last 90 Days',   now()->subDays(90)->format('Y-m-d'),             now()->format('Y-m-d')],
                        'quarter' => ['This Quarter',   now()->startOfQuarter()->format('Y-m-d'),        now()->format('Y-m-d')],
                        'mtd'     => ['This Month',     now()->startOfMonth()->format('Y-m-d'),          now()->format('Y-m-d')],
                        'year'    => ['This Year',      now()->startOfYear()->format('Y-m-d'),           now()->format('Y-m-d')],
                    ];
                @endphp
                @foreach($presets as $key => [$label, $from, $to])
                <button type="button" class="quick-date-btn {{ ($filters['date_range'] ?? '30') === $key ? 'active' : '' }}"
                    data-from="{{ $from }}" data-to="{{ $to }}" data-key="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>

            @if($activeFilters->count())
            <div class="d-flex flex-wrap gap-2 mt-3 pt-3" style="border-top:1px solid #f1f5f9">
                <span style="font-size:.68rem;color:#94a3b8;font-weight:700;align-self:center;text-transform:uppercase;letter-spacing:.5px">Active:</span>
                @foreach($activeFilters as $chip)
                <span style="background:#eef2ff;color:#6366f1;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;border:1px solid #c7d2fe;display:flex;align-items:center;gap:4px">
                    <span class="material-icons" style="font-size:11px">check_circle</span>{{ $chip }}
                </span>
                @endforeach
            </div>
            @endif
        </form>
    </div>
</div>

{{-- ═══ PERIOD BANNER ═══ --}}
<div class="period-banner mb-3">
    <div class="period-banner-left">
        <div class="sub">Analysing Period</div>
        <div class="range">
            <span class="material-icons">date_range</span>
            {{ $summary['dateFrom'] }}
            <span style="opacity:.5;font-size:.9rem">→</span>
            {{ $summary['dateTo'] }}
        </div>
    </div>
    <div class="period-banner-right">
        <div class="period-banner-stat">
            <div class="val">{{ number_format($summary['totalLeads']) }}</div>
            <div class="lbl">Total Leads</div>
        </div>
        <div class="period-banner-div"></div>
        <div class="period-banner-stat">
            <div class="val">{{ $summary['activeDays'] }}</div>
            <div class="lbl">Active Days</div>
        </div>
        <div class="period-banner-div"></div>
        <div class="period-banner-stat">
            <div class="val">{{ $summary['overallRate'] }}%</div>
            <div class="lbl">Conv. Rate</div>
        </div>
        <div class="period-banner-div"></div>
        <div class="period-banner-stat">
            <div class="val">{{ $summary['avgPerDay'] }}</div>
            <div class="lbl">Avg / Day</div>
        </div>
    </div>
</div>

{{-- ═══ KPI CARDS ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#6366f1,#8b5cf6)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#6366f115,#8b5cf615)"><span class="material-icons" style="color:#6366f1;font-size:24px">groups</span></div>
            <div class="kpi-value" style="color:#6366f1">{{ number_format($summary['totalLeads']) }}</div>
            <div class="kpi-label">Total Leads</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">calendar_today</span>{{ $summary['activeDays'] }} active days</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#10b981,#059669)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#10b98115,#05966915)"><span class="material-icons" style="color:#10b981;font-size:24px">check_circle</span></div>
            <div class="kpi-value" style="color:#10b981">{{ $summary['overallRate'] }}%</div>
            <div class="kpi-label">Conv. Rate</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">emoji_events</span>{{ number_format($summary['totalConverted']) }} won</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#06b6d4,#0ea5e9)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#06b6d415,#0ea5e915)"><span class="material-icons" style="color:#06b6d4;font-size:24px">speed</span></div>
            <div class="kpi-value" style="color:#06b6d4">{{ $summary['avgPerDay'] }}</div>
            <div class="kpi-label">Avg / Day</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">trending_flat</span>on active days</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#f59e0b,#d97706)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b15,#d9770615)"><span class="material-icons" style="color:#f59e0b;font-size:24px">local_fire_department</span></div>
            <div class="kpi-value" style="color:#f59e0b;font-size:1.7rem">{{ $summary['peakRow'] ? number_format($summary['peakRow']['total']) : '—' }}</div>
            <div class="kpi-label">Peak Day</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">event</span>{{ $summary['peakRow'] ? $summary['peakRow']['day'] : 'no data' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#8b5cf6,#7c3aed)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#8b5cf615,#7c3aed15)"><span class="material-icons" style="color:#8b5cf6;font-size:24px">star</span></div>
            <div class="kpi-value" style="color:#8b5cf6;font-size:1.7rem">{{ $summary['bestConvRow'] ? $summary['bestConvRow']['rate'] . '%' : '—' }}</div>
            <div class="kpi-label">Best Conv. Day</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">event</span>{{ $summary['bestConvRow'] ? $summary['bestConvRow']['day'] : 'none' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#ef4444,#dc2626)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#ef444415,#dc262615)"><span class="material-icons" style="color:#ef4444;font-size:24px">trending_down</span></div>
            <div class="kpi-value" style="color:#ef4444">{{ $summary['belowAvg'] }}</div>
            <div class="kpi-label">Slow Days</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">remove_circle</span>below avg volume</div>
        </div>
    </div>
</div>

{{-- ═══ DAILY TREND + CUMULATIVE ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="chart-card">
            <div class="ch">Daily Lead Intake & Conversions</div>
            <div class="cs">Volume bars with conversion line — spot spikes and quiet periods</div>
            <div style="height:250px"><canvas id="dailyChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <div class="ch">Cumulative Lead Growth</div>
            <div class="cs">Running total — pipeline build-up over time</div>
            <div style="height:250px"><canvas id="cumulChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ═══ WEEKLY + DOW ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Weekly Aggregation</div>
            <div class="cs">Total vs converted per week</div>
            <div style="height:240px"><canvas id="weeklyChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Day-of-Week Pattern</div>
            <div class="cs">Which weekdays generate the most leads</div>
            <div style="height:240px"><canvas id="dowChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ═══ PEAK DAYS + DAILY TABLE ═══ --}}
<div class="row g-3">
    <div class="col-md-4">
        <div class="chart-card">
            <div class="ch">Top 5 Peak Days</div>
            <div class="cs">Highest lead volume days in the period</div>
            @php $peakMax = $topDays->max('total') ?: 1; @endphp
            <div class="d-flex flex-column gap-3 mt-2">
                @forelse($topDays as $i => $day)
                @php
                    $pct  = round(($day['total'] / $peakMax) * 100);
                    $cols = ['#6366f1','#10b981','#f59e0b','#06b6d4','#8b5cf6'];
                    $col  = $cols[$i] ?? '#94a3b8';
                    $dowColors = ['Sun'=>['#fee2e2','#991b1b'],'Mon'=>['#dbeafe','#1e40af'],'Tue'=>['#d1fae5','#065f46'],'Wed'=>['#fef3c7','#92400e'],'Thu'=>['#ede9fe','#5b21b6'],'Fri'=>['#ecfdf5','#065f46'],'Sat'=>['#fce7f3','#9d174d']];
                    $dc   = $dowColors[$day['dow']] ?? ['#f1f5f9','#64748b'];
                @endphp
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div style="display:flex;align-items:center;gap:7px">
                            <span style="width:22px;height:22px;border-radius:7px;background:{{ $col }};color:#fff;font-size:.65rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center">#{{ $i+1 }}</span>
                            <span style="font-size:.8rem;font-weight:700;color:#0f172a">{{ $day['day'] }}</span>
                            <span class="dow-badge dow-{{ $day['dow'] }}">{{ $day['dow'] }}</span>
                        </div>
                        <span style="font-size:.86rem;font-weight:800;color:{{ $col }}">{{ number_format($day['total']) }}</span>
                    </div>
                    <div style="background:#f1f5f9;border-radius:6px;height:9px;overflow:hidden">
                        <div style="width:{{ $pct }}%;height:100%;background:{{ $col }};border-radius:6px;transition:width .4s"></div>
                    </div>
                    @if($day['converted'] > 0)
                    <div style="font-size:.68rem;color:#10b981;margin-top:2px;display:flex;align-items:center;gap:3px">
                        <span class="material-icons" style="font-size:11px">check_circle</span>
                        {{ $day['converted'] }} converted · {{ $day['rate'] }}%
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-muted text-center py-3" style="font-size:.82rem">No data available.</div>
                @endforelse
            </div>
            <div class="mt-4 pt-3" style="border-top:1px solid #f1f5f9">
                @foreach([['Days with leads','activeDays','#6366f1'],['Below-avg days','belowAvg','#f59e0b'],['Avg leads/day','avgPerDay','#0f172a']] as [$lbl,$key,$col])
                <div class="d-flex justify-content-between align-items-center {{ !$loop->first ? 'mt-2' : '' }}" style="font-size:.78rem">
                    <span style="color:#64748b">{{ $lbl }}</span>
                    <span style="font-weight:700;color:{{ $col }}">{{ $summary[$key] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="ch">Daily Breakdown</div>
                    <div class="cs">All active days — volume, conversion rate and weekday · sorted by volume</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span style="background:#6366f115;color:#6366f1;font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:20px;border:1px solid #c7d2fe">
                        <span class="material-icons" style="font-size:13px;vertical-align:middle">table_rows</span>
                        {{ $dailyRows->count() }} days
                    </span>
                </div>
            </div>
            <div class="table-responsive" style="max-height:380px;overflow-y:auto">
                <table class="period-table">
                    <thead style="position:sticky;top:0;z-index:1">
                        <tr>
                            <th>#</th>
                            <th>Day</th>
                            <th>Date</th>
                            <th class="text-center">Leads</th>
                            <th class="text-center">Won</th>
                            <th style="min-width:140px">Conv. Rate</th>
                            <th style="min-width:120px">Volume</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $maxDay    = $dailyRows->max('total') ?: 1;
                            $avgPerDay = $summary['avgPerDay'];
                            $sortedRows = $dailyRows->sortByDesc('total');
                        @endphp
                        @forelse ($sortedRows as $idx => $row)
                        @php
                            $rCol = $row['rate'] >= 10 ? '#10b981' : ($row['rate'] >= 5 ? '#f59e0b' : ($row['rate'] > 0 ? '#6366f1' : '#cbd5e1'));
                            $vPct = round(($row['total'] / $maxDay) * 100);
                            $isPeak = $idx === 0;
                            $volStatus = $isPeak ? 'peak' : ($row['total'] > $avgPerDay ? 'above' : ($row['total'] < $avgPerDay ? 'below' : 'normal'));
                        @endphp
                        <tr>
                            <td style="color:#94a3b8;font-size:.75rem;font-weight:700">{{ $idx + 1 }}</td>
                            <td><span class="dow-badge dow-{{ $row['dow'] }}">{{ $row['dow'] }}</span></td>
                            <td style="font-weight:700;color:#0f172a;font-size:.83rem">{{ $row['day'] }}</td>
                            <td class="text-center"><span style="font-weight:800;color:#6366f1;font-size:.88rem">{{ number_format($row['total']) }}</span></td>
                            <td class="text-center">
                                @if($row['converted'] > 0)
                                <span style="font-weight:700;color:#10b981;background:#dcfce7;padding:2px 8px;border-radius:20px;font-size:.75rem">{{ $row['converted'] }}</span>
                                @else
                                <span style="color:#cbd5e1;font-size:.75rem">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="pb-wrap">
                                    <div class="pb-track">
                                        <div class="pb-fill" style="width:{{ min($row['rate'] * 5, 100) }}%;background:{{ $rCol }}"></div>
                                    </div>
                                    <span style="font-size:.75rem;font-weight:800;color:{{ $rCol }};min-width:38px">{{ $row['rate'] }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="pb-wrap">
                                    <div class="pb-track">
                                        <div class="pb-fill" style="width:{{ $vPct }}%;background:#6366f1;opacity:.65"></div>
                                    </div>
                                    <span style="font-size:.73rem;color:#64748b;min-width:28px">{{ $vPct }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="vol-badge {{ $volStatus }}">
                                    @if($volStatus === 'peak')<span class="material-icons" style="font-size:11px">local_fire_department</span>Peak
                                    @elseif($volStatus === 'above')<span class="material-icons" style="font-size:11px">arrow_upward</span>High
                                    @elseif($volStatus === 'below')<span class="material-icons" style="font-size:11px">arrow_downward</span>Low
                                    @else Normal
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div style="color:#94a3b8">
                                    <span class="material-icons" style="font-size:36px;display:block;margin-bottom:8px">inbox</span>
                                    No data for the selected period.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Quick preset buttons ── */
(function () {
    const fromInput = document.getElementById('fromDate');
    const toInput   = document.getElementById('toDate');
    const hidden    = document.getElementById('dateRangeHidden');

    document.querySelectorAll('.quick-date-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            fromInput.value = btn.dataset.from;
            toInput.value   = btn.dataset.to;
            hidden.value    = btn.dataset.key;
            document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    fromInput.addEventListener('change', () => {
        if (toInput.value < fromInput.value) toInput.value = fromInput.value;
        hidden.value = 'custom';
        document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
    });
    toInput.addEventListener('change', () => {
        hidden.value = 'custom';
        document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
    });
})();

/* ── Charts ── */
(function () {
    const days    = @json($dailyRows->pluck('day')->values());
    const totals  = @json($dailyRows->pluck('total')->values());
    const convs   = @json($dailyRows->pluck('converted')->values());
    const cumul   = @json($cumulative);
    const wkLbls  = @json($weeklyRows->pluck('label')->values());
    const wkTot   = @json($weeklyRows->pluck('total')->values());
    const wkConv  = @json($weeklyRows->pluck('converted')->values());
    const dowLbls = @json($dowLabels);
    const dowTot  = @json($dowTotal);
    const dowConv = @json($dowConv);

    const GRID    = { color:'rgba(0,0,0,.04)', drawBorder:false };
    const TICK    = { color:'#94a3b8', font:{ size:10, family:"'Plus Jakarta Sans',sans-serif" } };
    const TOOLTIP = { backgroundColor:'#0f172a', titleColor:'#e2e8f0', bodyColor:'#cbd5e1', cornerRadius:8, padding:10 };

    function init() {
        /* ── Daily ── */
        new Chart(document.getElementById('dailyChart'), {
            data: {
                labels: days,
                datasets: [
                    { type:'bar',  label:'Total Leads', data:totals,
                      backgroundColor:'rgba(99,102,241,.18)', borderColor:'rgba(99,102,241,.5)',
                      borderWidth:1.5, borderRadius:4, yAxisID:'yVol', order:2 },
                    { type:'line', label:'Conversions',  data:convs,
                      borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.12)',
                      fill:true, tension:0.4, borderWidth:2.5,
                      pointBackgroundColor:'#10b981', pointRadius:3, pointHoverRadius:5,
                      yAxisID:'yConv', order:1 },
                ]
            },
            options: {
                maintainAspectRatio:false,
                interaction:{ mode:'index', intersect:false },
                plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 }, usePointStyle:true, pointStyleWidth:10 } }, tooltip:TOOLTIP },
                scales:{
                    yVol:  { type:'linear', position:'left',  beginAtZero:true, ticks:{ ...TICK, precision:0 }, grid:GRID },
                    yConv: { type:'linear', position:'right', beginAtZero:true, ticks:{ ...TICK, precision:0 }, grid:{ display:false } },
                    x:     { ticks:{ ...TICK, maxRotation:45 }, grid:{ display:false } }
                }
            }
        });

        /* ── Cumulative ── */
        new Chart(document.getElementById('cumulChart'), {
            type:'line',
            data:{ labels:days, datasets:[{
                label:'Cumulative Leads', data:cumul,
                borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,.1)',
                fill:true, tension:0.4, borderWidth:2.5, pointRadius:0, pointHoverRadius:5,
            }] },
            options:{
                maintainAspectRatio:false,
                plugins:{ legend:{ display:false }, tooltip:TOOLTIP },
                scales:{
                    y:{ beginAtZero:true, ticks:{ ...TICK, precision:0 }, grid:GRID },
                    x:{ ticks:{ ...TICK, maxRotation:45 }, grid:{ display:false } }
                }
            }
        });

        /* ── Weekly ── */
        new Chart(document.getElementById('weeklyChart'), {
            type:'bar',
            data:{ labels:wkLbls, datasets:[
                { label:'Total',     data:wkTot,  backgroundColor:'rgba(99,102,241,.75)', borderRadius:5, borderSkipped:false },
                { label:'Converted', data:wkConv, backgroundColor:'#10b981',              borderRadius:5, borderSkipped:false },
            ] },
            options:{
                maintainAspectRatio:false,
                interaction:{ mode:'index', intersect:false },
                plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 }, usePointStyle:true } }, tooltip:TOOLTIP },
                scales:{ y:{ beginAtZero:true, ticks:{ ...TICK, precision:0 }, grid:GRID }, x:{ ticks:TICK, grid:{ display:false } } }
            }
        });

        /* ── Day-of-Week ── */
        const dowColors = ['#fee2e2','rgba(99,102,241,.75)','rgba(99,102,241,.75)','rgba(99,102,241,.75)','rgba(99,102,241,.75)','rgba(99,102,241,.75)','#fce7f3'];
        new Chart(document.getElementById('dowChart'), {
            type:'bar',
            data:{ labels:dowLbls, datasets:[
                { label:'Total Leads', data:dowTot,  backgroundColor:dowColors, borderRadius:5, borderSkipped:false },
                { label:'Converted',   data:dowConv, backgroundColor:'#10b981', borderRadius:5, borderSkipped:false },
            ] },
            options:{
                maintainAspectRatio:false,
                interaction:{ mode:'index', intersect:false },
                plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 }, usePointStyle:true } }, tooltip:TOOLTIP },
                scales:{ y:{ beginAtZero:true, ticks:{ ...TICK, precision:0 }, grid:GRID }, x:{ ticks:TICK, grid:{ display:false } } }
            }
        });
    }

    if (typeof Chart !== 'undefined') {
        init();
    } else {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        s.onload = init;
        document.head.appendChild(s);
    }
})();
</script>
@endsection
