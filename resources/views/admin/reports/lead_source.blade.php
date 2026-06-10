@extends('layouts.app')
@section('page_title', 'Lead Source Report')

@section('content')
@php
$rp = Auth::user()->role === 'report_viewer' ? 'report_viewer' : 'admin';

$activeFilters = collect([
    ($filters['date_range'] ?? '30') !== '30'  ? 'Period: '.['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','quarter'=>'Quarter','year'=>'Year'][($filters['date_range'] ?? '30')] : null,
    ($filters['source']     ?? 'all') !== 'all' ? 'Source: '.$filters['source'] : null,
    ($filters['telecaller'] ?? 'all') !== 'all' ? 'Telecaller filtered' : null,
    ($filters['manager']    ?? 'all') !== 'all' ? 'Manager filtered' : null,
])->filter()->values();

$chartPalette = ['#6366f1','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899','#14b8a6'];
@endphp

<style>
/* ── Insights ── */
.insight-bar { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:1rem; }
.insight-item { display:flex; align-items:flex-start; gap:8px; flex:1; min-width:260px; border-radius:12px; padding:10px 14px; border-left:3px solid; }
.insight-item.success { background:#f0fdf4; border-color:#10b981; }
.insight-item.danger  { background:#fef2f2; border-color:#ef4444; }
.insight-item.warning { background:#fffbeb; border-color:#f59e0b; }
.insight-item.info    { background:#f0f9ff; border-color:#0ea5e9; }
.insight-item .material-icons { font-size:18px; margin-top:1px; flex-shrink:0; }
.insight-item.success .material-icons { color:#10b981; }
.insight-item.danger  .material-icons { color:#ef4444; }
.insight-item.warning .material-icons { color:#f59e0b; }
.insight-item.info    .material-icons { color:#0ea5e9; }
.insight-item span:last-child { font-size:.78rem; color:#334155; font-weight:500; line-height:1.45; }

/* ── Filter ── */
.rpt-filter-wrap { background:#fff; border-radius:16px; box-shadow:0 1px 8px rgba(15,23,42,.08); overflow:hidden; margin-bottom:1.25rem; }
.rpt-filter-head { background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%); padding:11px 20px; display:flex; align-items:center; justify-content:space-between; }
.rpt-filter-head-title { display:flex; align-items:center; gap:7px; color:#fff; font-size:.82rem; font-weight:700; letter-spacing:.3px; }
.rpt-filter-head-title .material-icons { font-size:17px; opacity:.9; }
.rpt-filter-body { padding:16px 20px 18px; }
.rpt-filter-field { display:flex; flex-direction:column; gap:5px; min-width:140px; flex:1; }
.rpt-filter-lbl { font-size:.68rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.6px; display:flex; align-items:center; gap:5px; }
.rpt-filter-lbl .material-icons { font-size:13px; color:#6366f1; }
.rpt-filter-sel { border:1.5px solid #e2e8f0; border-radius:9px; padding:7px 32px 7px 12px; font-size:.82rem; font-weight:500; color:#0f172a; background-color:#f8fafc;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 10px center; -webkit-appearance:none; appearance:none; outline:none; cursor:pointer; transition:border-color .18s,box-shadow .18s; }
.rpt-filter-sel:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); background-color:#fff; }
.rpt-filter-sel:hover { border-color:#a5b4fc; background-color:#fff; }
.rpt-filter-div { width:1px; background:#e2e8f0; align-self:stretch; margin:0 4px; flex-shrink:0; }
.rpt-btn-apply { background:linear-gradient(135deg,#6366f1,#4f46e5); border:none; color:#fff; border-radius:9px; padding:8px 18px; font-size:.82rem; font-weight:700;
    display:flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(99,102,241,.35); transition:transform .15s,box-shadow .15s; cursor:pointer; }
.rpt-btn-apply:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(99,102,241,.45); color:#fff; }
.rpt-btn-apply .material-icons { font-size:15px; }
.rpt-btn-reset { background:#f1f5f9; border:1.5px solid #e2e8f0; color:#475569; border-radius:9px; padding:7px 14px; font-size:.82rem; font-weight:600;
    display:flex; align-items:center; gap:5px; transition:all .15s; text-decoration:none; }
.rpt-btn-reset:hover { background:#e2e8f0; color:#1e293b; }
.rpt-btn-reset .material-icons { font-size:14px; }
.rpt-btn-download { background:#fff; border:1.5px solid #e2e8f0; color:#475569; border-radius:9px; padding:7px 14px; font-size:.82rem; font-weight:600;
    display:flex; align-items:center; gap:5px; cursor:pointer; transition:all .15s; }
.rpt-btn-download:hover { border-color:#6366f1; color:#6366f1; background:#eef2ff; }
.rpt-btn-download::after { display:none; }
.rpt-btn-download .material-icons { font-size:14px; }
.rpt-dl-menu { border:1.5px solid #e2e8f0 !important; border-radius:12px !important; padding:6px !important; box-shadow:0 8px 24px rgba(15,23,42,.12) !important; min-width:152px; }
.rpt-dl-excel,.rpt-dl-pdf { display:flex !important; align-items:center !important; gap:8px; padding:8px 12px !important; border-radius:8px !important; font-size:.82rem !important; font-weight:600; color:#475569; }
.rpt-dl-excel:hover { color:#10b981 !important; background:#ecfdf5 !important; }
.rpt-dl-pdf:hover   { color:#ef4444 !important; background:#fef2f2 !important; }
.rpt-dl-excel .material-icons { font-size:15px; color:#10b981; }
.rpt-dl-pdf   .material-icons { font-size:15px; color:#ef4444; }

/* ── KPI cards ── */
.kpi-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 1px 8px rgba(15,23,42,.07); height:100%; position:relative; overflow:hidden; transition:transform .18s,box-shadow .18s; }
.kpi-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(15,23,42,.1); }
.kpi-card .accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:16px 16px 0 0; }
.kpi-icon  { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; margin-bottom:14px; }
.kpi-value { font-size:2rem; font-weight:800; color:#0f172a; line-height:1.1; }
.kpi-label { font-size:.68rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; margin-top:5px; }
.kpi-sub   { font-size:.75rem; color:#64748b; margin-top:4px; display:flex; align-items:center; gap:3px; }

/* ── Chart card ── */
.chart-card { background:#fff; border-radius:16px; padding:22px; box-shadow:0 1px 8px rgba(15,23,42,.07); height:100%; }
.ch { font-size:.9rem; font-weight:700; color:#0f172a; margin:0 0 2px; }
.cs { font-size:.73rem; color:#94a3b8; margin:0 0 16px; }

/* ── Grade tiles ── */
.grade-tile { border-radius:14px; padding:16px 18px; position:relative; overflow:hidden; transition:transform .15s; }
.grade-tile:hover { transform:scale(1.02); }
.grade-tile-letter { position:absolute; right:12px; top:10px; width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.2rem; opacity:.9; }
.grade-tile-count { font-size:2rem; font-weight:800; line-height:1.1; }
.grade-tile-lbl { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; opacity:.8; margin-top:2px; }
.grade-tile-range { font-size:.68rem; opacity:.6; margin-top:3px; }

/* ── Table ── */
.src-table { width:100%; border-collapse:collapse; }
.src-table thead th { font-size:.68rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px;
    background:linear-gradient(0deg,#f8fafc,#fff); border-bottom:2px solid #e2e8f0; padding:10px 14px; white-space:nowrap; }
.src-table thead th:first-child { border-radius:8px 0 0 0; }
.src-table thead th:last-child  { border-radius:0 8px 0 0; }
.src-table tbody td { padding:11px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; font-size:.82rem; transition:background .12s; }
.src-table tbody tr:last-child td { border-bottom:0; }
.src-table tbody tr:hover td { background:#f8faff; }
.src-table tbody tr:nth-child(even) td { background:#fafbff; }
.src-table tbody tr:nth-child(even):hover td { background:#f0f2ff; }

/* ── Rank badge ── */
.rank-badge { width:26px; height:26px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800; background:#f1f5f9; color:#64748b; }
.rank-badge.rank-1 { background:linear-gradient(135deg,#fbbf24,#f59e0b); color:#fff; box-shadow:0 2px 6px rgba(245,158,11,.4); }
.rank-badge.rank-2 { background:linear-gradient(135deg,#94a3b8,#64748b); color:#fff; }
.rank-badge.rank-3 { background:linear-gradient(135deg,#cd7c3e,#b45309); color:#fff; }

/* ── Grade pills ── */
.grade-pill { width:28px; height:28px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-size:.73rem; font-weight:800; }
.g-A { background:#d1fae5; color:#065f46; }
.g-B { background:#dbeafe; color:#1e40af; }
.g-C { background:#fef3c7; color:#92400e; }
.g-D { background:#fee2e2; color:#991b1b; }

/* ── Progress bars ── */
.progress-bar-wrap { display:flex; align-items:center; gap:7px; }
.progress-bar-track { flex:1; height:7px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
.progress-bar-fill  { height:100%; border-radius:4px; transition:width .3s; }

/* ── Status chips ── */
.stat-chip { display:inline-flex; align-items:center; gap:3px; padding:2px 8px; border-radius:20px; font-size:.7rem; font-weight:700; }
.stat-chip.conv  { background:#dcfce7; color:#16a34a; }
.stat-chip.active { background:#e0f2fe; color:#0369a1; }
.stat-chip.lost  { background:#fee2e2; color:#dc2626; }
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
            <span class="material-icons">tune</span>
            Report Filters
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($activeFilters->count())
            <span style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:rgba(255,255,255,.8);background:rgba(255,255,255,.15);padding:3px 10px;border-radius:20px">
                <span class="material-icons" style="font-size:13px">filter_alt</span>
                {{ $activeFilters->count() }} active
            </span>
            @endif
            <span style="font-size:.72rem;color:rgba(255,255,255,.55)">Lead Source Report</span>
        </div>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="d-flex flex-wrap gap-3 align-items-end">
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">calendar_today</span> Time Period</label>
                    <select name="date_range" class="rpt-filter-sel">
                        <option value="7"       {{ ($filters['date_range'] ?? '30') === '7'       ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30"      {{ ($filters['date_range'] ?? '30') === '30'      ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90"      {{ ($filters['date_range'] ?? '30') === '90'      ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="quarter" {{ ($filters['date_range'] ?? '30') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year"    {{ ($filters['date_range'] ?? '30') === 'year'    ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
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
                    <a href="{{ route($rp . '.reports.lead-source') }}" class="rpt-btn-reset"><span class="material-icons">refresh</span> Reset</a>
                    <div class="dropdown">
                        <button class="rpt-btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">file_download</span> Export
                        </button>
                        <ul class="dropdown-menu rpt-dl-menu">
                            <li><a class="dropdown-item rpt-dl-excel" href="{{ route($rp . '.reports.export', ['report' => 'lead-source', 'format' => 'excel'] + request()->query()) }}">
                                <span class="material-icons">table_view</span> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item rpt-dl-pdf" href="{{ route($rp . '.reports.export', ['report' => 'lead-source', 'format' => 'pdf'] + request()->query()) }}" target="_blank">
                                <span class="material-icons">picture_as_pdf</span> PDF Report
                            </a></li>
                        </ul>
                    </div>
                </div>
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

{{-- ═══ KPI CARDS ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#6366f1,#8b5cf6)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#6366f115,#8b5cf615)">
                <span class="material-icons" style="color:#6366f1;font-size:24px">category</span>
            </div>
            <div class="kpi-value" style="color:#6366f1">{{ number_format($summary['totalSources']) }}</div>
            <div class="kpi-label">Active Sources</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">hub</span>unique channels</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#06b6d4,#0ea5e9)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#06b6d415,#0ea5e915)">
                <span class="material-icons" style="color:#06b6d4;font-size:24px">groups</span>
            </div>
            <div class="kpi-value" style="color:#06b6d4">{{ number_format($summary['totalLeads']) }}</div>
            <div class="kpi-label">Total Leads</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">schedule</span>selected period</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#10b981,#059669)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#10b98115,#05966915)">
                <span class="material-icons" style="color:#10b981;font-size:24px">emoji_events</span>
            </div>
            <div class="kpi-value" style="color:#10b981">{{ number_format($summary['totalConverted']) }}</div>
            <div class="kpi-label">Converted</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">percent</span>{{ $summary['overallRate'] }}% overall rate</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#8b5cf6,#7c3aed)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#8b5cf615,#7c3aed15)">
                <span class="material-icons" style="color:#8b5cf6;font-size:24px">star</span>
            </div>
            <div class="kpi-value" style="color:#8b5cf6;font-size:1.1rem;line-height:1.5">{{ $summary['bestSource'] ? ucfirst($summary['bestSource']['source']) : '—' }}</div>
            <div class="kpi-label">Best Converting</div>
            <div class="kpi-sub">
                @if($summary['bestSource'])
                    <span style="color:#8b5cf6;font-weight:700">{{ $summary['bestSource']['rate'] }}%</span>&nbsp;conv. rate
                @else no data @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#f59e0b,#d97706)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b15,#d9770615)">
                <span class="material-icons" style="color:#f59e0b;font-size:24px">trending_up</span>
            </div>
            <div class="kpi-value" style="color:#f59e0b;font-size:1.1rem;line-height:1.5">{{ $summary['topVolSource'] ? ucfirst($summary['topVolSource']['source']) : '—' }}</div>
            <div class="kpi-label">Highest Volume</div>
            <div class="kpi-sub">
                @if($summary['topVolSource'])
                    <span style="color:#f59e0b;font-weight:700">{{ number_format($summary['topVolSource']['total']) }}</span>&nbsp;leads
                @else no data @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="accent" style="background:linear-gradient(90deg,#ef4444,#dc2626)"></div>
            <div class="kpi-icon" style="background:linear-gradient(135deg,#ef444415,#dc262615)">
                <span class="material-icons" style="color:#ef4444;font-size:24px">hourglass_bottom</span>
            </div>
            <div class="kpi-value" style="color:#ef4444">{{ $summary['avgDaysAll'] ?? '—' }}</div>
            <div class="kpi-label">Avg Days to Win</div>
            <div class="kpi-sub"><span class="material-icons" style="font-size:13px;color:#94a3b8">timeline</span>across all sources</div>
        </div>
    </div>
</div>

{{-- ═══ GRADE TILES + DOUGHNUT ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-md-5">
        <div class="chart-card">
            <div class="ch">Source Quality Tiers</div>
            <div class="cs">Graded A–D by conversion rate (A ≥10%, B ≥5%, C ≥1%, D &lt;1%)</div>
            <div class="row g-2 mt-1">
                @foreach(['A'=>['#d1fae5','#065f46','#10b981'],'B'=>['#dbeafe','#1e40af','#6366f1'],'C'=>['#fef3c7','#92400e','#f59e0b'],'D'=>['#fee2e2','#991b1b','#ef4444']] as $g=>$cols)
                <div class="col-6">
                    <div class="grade-tile" style="background:{{ $cols[0] }}">
                        <div class="grade-tile-letter" style="background:{{ $cols[2] }}22;color:{{ $cols[1] }}">{{ $g }}</div>
                        <div class="grade-tile-count" style="color:{{ $cols[1] }}">{{ $summary['gradeCounts'][$g] }}</div>
                        <div class="grade-tile-lbl" style="color:{{ $cols[1] }}">Grade {{ $g }} Sources</div>
                        <div class="grade-tile-range" style="color:{{ $cols[1] }}">
                            @if($g==='A') ≥ 10% conversion @elseif($g==='B') 5–9.9% @elseif($g==='C') 1–4.9% @else < 1% @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="chart-card">
            <div class="ch">Source Share of Total Leads</div>
            <div class="cs">Volume distribution across all channels — hover for details</div>
            <div class="row g-0" style="height:230px">
                <div class="col-6">
                    <canvas id="sourceDoughnut" style="height:230px"></canvas>
                </div>
                <div class="col-6 d-flex flex-column justify-content-center gap-2 ps-3">
                    @foreach($rows->take(8) as $i => $row)
                    <div style="display:flex;align-items:center;gap:7px">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $chartPalette[$i % count($chartPalette)] }};flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,.2)"></span>
                        <span style="font-size:.72rem;color:#0f172a;font-weight:600;flex:1;min-width:0" class="text-truncate" title="{{ ucfirst($row['source']) }}">{{ ucfirst($row['source']) }}</span>
                        <span style="font-size:.72rem;font-weight:700;color:#64748b;background:#f1f5f9;padding:1px 6px;border-radius:20px">{{ $row['share'] }}%</span>
                    </div>
                    @endforeach
                    @if($rows->count() > 8)
                    <div style="font-size:.7rem;color:#94a3b8;margin-top:2px">+ {{ $rows->count() - 8 }} more sources</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ VOLUME vs RATE CHARTS ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Volume by Source</div>
            <div class="cs">Lead intake per channel — highest first</div>
            <div style="height:300px">
                <canvas id="volumeChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Conversion Rate by Source</div>
            <div class="cs">Win rate per channel — green ≥10%, amber ≥5%, red &lt;5%</div>
            <div style="height:300px">
                <canvas id="rateChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ═══ MONTHLY TREND ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="chart-card">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <div class="ch">Top 5 Sources — 6-Month Volume Trend</div>
                    <div class="cs">How lead intake per channel evolved month by month</div>
                </div>
                <span style="background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px">Last 6 months</span>
            </div>
            <div style="height:250px">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ═══ DETAILED SOURCE TABLE ═══ --}}
<div class="chart-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="ch">Full Source Performance Matrix</div>
            <div class="cs">All channels ranked by volume — conversion rate, pipeline health and quality grade</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span style="background:#6366f115;color:#6366f1;font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:20px;border:1px solid #c7d2fe">
                <span class="material-icons" style="font-size:13px;vertical-align:middle">table_rows</span>
                {{ $rows->count() }} sources
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="src-table">
            <thead>
                <tr>
                    <th style="width:44px">#</th>
                    <th>Source</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Converted</th>
                    <th class="text-center">Active</th>
                    <th class="text-center">Lost</th>
                    <th style="min-width:160px">Conv. Rate</th>
                    <th style="min-width:130px">Share of Total</th>
                    <th class="text-center">Avg Days</th>
                    <th class="text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $row)
                @php
                    $rCol  = $row['rate'] >= 10 ? '#10b981' : ($row['rate'] >= 5 ? '#f59e0b' : '#ef4444');
                    $color = $chartPalette[$i % count($chartPalette)];
                    $rankClass = $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : ''));
                @endphp
                <tr>
                    <td>
                        <span class="rank-badge {{ $rankClass }}">
                            @if($i === 0)<span class="material-icons" style="font-size:13px">emoji_events</span>@else{{ $i + 1 }}@endif
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.2)"></span>
                            <span style="font-weight:700;color:#0f172a;font-size:.84rem">{{ ucfirst($row['source']) }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span style="font-weight:800;color:#0f172a;font-size:.88rem">{{ number_format($row['total']) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="stat-chip conv">
                            <span class="material-icons" style="font-size:11px">check_circle</span>
                            {{ number_format($row['converted']) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="stat-chip active">{{ number_format($row['active']) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="stat-chip lost">{{ number_format($row['lost']) }}</span>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width:{{ min($row['rate'] * 5, 100) }}%;background:{{ $rCol }}"></div>
                            </div>
                            <span style="font-size:.78rem;font-weight:800;color:{{ $rCol }};min-width:42px">{{ $row['rate'] }}%</span>
                        </div>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width:{{ $row['share'] }}%;background:#6366f1;opacity:.75"></div>
                            </div>
                            <span style="font-size:.78rem;font-weight:700;color:#6366f1;min-width:38px">{{ $row['share'] }}%</span>
                        </div>
                    </td>
                    <td class="text-center">
                        @if($row['avg_days'] !== null)
                            <span style="font-size:.78rem;font-weight:700;color:#f59e0b;background:#fffbeb;padding:2px 8px;border-radius:20px;border:1px solid #fde68a">{{ $row['avg_days'] }}d</span>
                        @else
                            <span class="text-muted" style="font-size:.78rem">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="grade-pill g-{{ $row['grade'] }}">{{ $row['grade'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div style="color:#94a3b8">
                            <span class="material-icons" style="font-size:36px;display:block;margin-bottom:8px">inbox</span>
                            No data for the selected filters.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ COURSE ENQUIRY ANALYTICS ═══ --}}
<div class="chart-card mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="ch" style="display:flex;align-items:center;gap:8px">
                <span class="material-icons" style="color:#8b5cf6;font-size:20px">school</span>
                Course Enquiry Analytics
            </div>
            <div class="cs">Lead enquiries broken down by course — separate from channel/source data</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span style="background:#8b5cf615;color:#8b5cf6;font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:20px;border:1px solid #ddd6fe">
                <span class="material-icons" style="font-size:13px;vertical-align:middle">table_rows</span>
                {{ ($courseRows ?? collect())->count() }} courses
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="src-table">
            <thead>
                <tr>
                    <th style="width:44px">#</th>
                    <th>Course</th>
                    <th class="text-center">Enquiries</th>
                    <th class="text-center">Converted</th>
                    <th class="text-center">Active</th>
                    <th class="text-center">Lost</th>
                    <th style="min-width:160px">Conv. Rate</th>
                    <th style="min-width:130px">Share of Total</th>
                    <th class="text-center">Avg Days</th>
                    <th class="text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courseRows ?? [] as $i => $row)
                @php
                    $rCol  = $row['rate'] >= 10 ? '#10b981' : ($row['rate'] >= 5 ? '#f59e0b' : '#ef4444');
                    $courseColors = ['#8b5cf6','#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6'];
                    $color = $courseColors[$i % count($courseColors)];
                    $rankClass = $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : ''));
                @endphp
                <tr>
                    <td>
                        <span class="rank-badge {{ $rankClass }}">
                            @if($i === 0)<span class="material-icons" style="font-size:13px">emoji_events</span>@else{{ $i + 1 }}@endif
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.2)"></span>
                            <span style="font-weight:700;color:#0f172a;font-size:.84rem">{{ $row['course'] }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span style="font-weight:800;color:#0f172a;font-size:.88rem">{{ number_format($row['total']) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="stat-chip conv">
                            <span class="material-icons" style="font-size:11px">check_circle</span>
                            {{ number_format($row['converted']) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="stat-chip active">{{ number_format($row['active']) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="stat-chip lost">{{ number_format($row['lost']) }}</span>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width:{{ min($row['rate'] * 5, 100) }}%;background:{{ $rCol }}"></div>
                            </div>
                            <span style="font-size:.78rem;font-weight:800;color:{{ $rCol }};min-width:42px">{{ $row['rate'] }}%</span>
                        </div>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width:{{ $row['share'] }}%;background:#8b5cf6;opacity:.75"></div>
                            </div>
                            <span style="font-size:.78rem;font-weight:700;color:#8b5cf6;min-width:38px">{{ $row['share'] }}%</span>
                        </div>
                    </td>
                    <td class="text-center">
                        @if($row['avg_days'] !== null)
                            <span style="font-size:.78rem;font-weight:700;color:#f59e0b;background:#fffbeb;padding:2px 8px;border-radius:20px;border:1px solid #fde68a">{{ $row['avg_days'] }}d</span>
                        @else
                            <span class="text-muted" style="font-size:.78rem">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="grade-pill g-{{ $row['grade'] }}">{{ $row['grade'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div style="color:#94a3b8">
                            <span class="material-icons" style="font-size:36px;display:block;margin-bottom:8px">school</span>
                            No course enquiry data for the selected filters.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const rows      = @json($rows->values());
    const palette   = @json($chartPalette);
    const monthLbls = @json($monthLabels);
    const src5      = @json($sourceMonthData);
    const GRID = { color: 'rgba(0,0,0,.04)', drawBorder: false };
    const TICK = { color: '#94a3b8', font: { size: 10, family: "'Plus Jakarta Sans',sans-serif" } };
    const TOOLTIP = {
        backgroundColor: '#0f172a',
        titleColor: '#e2e8f0',
        bodyColor: '#cbd5e1',
        cornerRadius: 8,
        padding: 10,
        displayColors: true,
    };

    function init() {
        const labels  = rows.map(r => r.source);
        const totals  = rows.map(r => r.total);
        const rates   = rows.map(r => r.rate);
        const colors  = rows.map((_, i) => palette[i % palette.length]);

        /* ── Doughnut ── */
        new Chart(document.getElementById('sourceDoughnut'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: totals,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8,
                    hoverBorderWidth: 0,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...TOOLTIP,
                        callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} leads (${rows[ctx.dataIndex]?.share}%)` }
                    }
                }
            }
        });

        /* ── Volume horizontal bar ── */
        new Chart(document.getElementById('volumeChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Total Leads',
                    data: totals,
                    backgroundColor: colors.map(c => c + 'cc'),
                    borderColor: colors,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: TOOLTIP },
                scales: {
                    x: { beginAtZero: true, ticks: { ...TICK, precision: 0 }, grid: GRID },
                    y: { ticks: { ...TICK, font: { size: 9 } }, grid: { display: false } }
                }
            }
        });

        /* ── Conversion Rate horizontal bar ── */
        const rateColors = rates.map(r => r >= 10 ? '#10b981' : r >= 5 ? '#f59e0b' : '#ef4444');
        const rateBgColors = rates.map(r => r >= 10 ? '#10b98133' : r >= 5 ? '#f59e0b33' : '#ef444433');
        new Chart(document.getElementById('rateChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Conversion %',
                    data: rates,
                    backgroundColor: rateBgColors,
                    borderColor: rateColors,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...TOOLTIP, callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}%` } } },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: Math.max(...rates, 5) + 3,
                        ticks: { ...TICK, callback: v => v + '%' }, grid: GRID
                    },
                    y: { ticks: { ...TICK, font: { size: 9 } }, grid: { display: false } }
                }
            }
        });

        /* ── Monthly Trend line ── */
        const trendDatasets = src5.map((s, i) => ({
            label: s.source,
            data: s.data,
            borderColor: palette[i % palette.length],
            backgroundColor: palette[i % palette.length] + '20',
            fill: false,
            tension: 0.4,
            borderWidth: 2.5,
            pointBackgroundColor: palette[i % palette.length],
            pointRadius: 4,
            pointHoverRadius: 6,
        }));
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: { labels: monthLbls, datasets: trendDatasets },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11, family: "'Plus Jakarta Sans',sans-serif" }, usePointStyle: true, pointStyleWidth: 10 } },
                    tooltip: TOOLTIP,
                },
                scales: {
                    y: { beginAtZero: true, ticks: { ...TICK, precision: 0 }, grid: GRID },
                    x: { ticks: TICK, grid: { display: false } }
                }
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
