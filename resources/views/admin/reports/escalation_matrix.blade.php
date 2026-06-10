@extends('layouts.app')

@section('page_title', 'Escalation Matrix')

@section('content')
@php
$rp = 'report_viewer';
$resetRoute = route('report_viewer.reports.escalation-matrix');
$typeLabels = ['sla' => 'Response SLA', 'missed' => 'Missed Follow-up'];
$activeFilters = collect([
    ($filters['date_range'] ?? '30') !== '30'  ? 'Period: '.['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','quarter'=>'Quarter','year'=>'Year'][($filters['date_range'] ?? '30')] : null,
    ($filters['telecaller'] ?? 'all') !== 'all' ? 'Telecaller: '.($filterOptions['telecallers']->firstWhere('id', $filters['telecaller'])?->name ?? $filters['telecaller']) : null,
    ($filters['manager']    ?? 'all') !== 'all' ? 'Manager: '.($filterOptions['managers']->firstWhere('id', $filters['manager'])?->name    ?? $filters['manager'])    : null,
    ($filters['type']       ?? 'all') !== 'all' ? 'Type: '.($typeLabels[$filters['type']] ?? $filters['type']) : null,
])->filter()->values();
@endphp

{{-- ══════════════════════════════════════════════════════ STYLES --}}
<style>
.em-section { margin-bottom: 1.25rem; }
.em-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 1px 8px rgba(15,23,42,.07);
    height: 100%;
}
.em-card-title { font-size: .875rem; font-weight: 700; color: #0f172a; margin: 0 0 2px; }
.em-card-sub   { font-size: .72rem;  color: #94a3b8; margin: 0 0 14px; }

/* KPI cards */
.em-kpi {
    background: #fff; border-radius: 14px; padding: 18px 20px 16px;
    box-shadow: 0 1px 6px rgba(15,23,42,.07);
    position: relative; overflow: hidden; height: 100%;
    transition: transform .18s, box-shadow .18s;
}
.em-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(15,23,42,.11); }
.em-kpi::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: 14px 14px 0 0; background: var(--kpi-accent, #6366f1);
}
.em-kpi-icon { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; background: var(--kpi-bg, rgba(99,102,241,.1)); }
.em-kpi-icon .material-icons { font-size: 20px; color: var(--kpi-color, #6366f1); }
.em-kpi-value { font-size: 1.8rem; font-weight: 800; color: var(--kpi-color, #0f172a); line-height: 1.1; }
.em-kpi-label { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-top: 4px; }
.em-kpi-sub   { font-size: .73rem; color: #64748b; margin-top: 3px; }

/* Filter bar */
.rpt-filter-wrap  { background: #fff; border-radius: 16px; box-shadow: 0 1px 8px rgba(15,23,42,.08); overflow: hidden; margin-bottom: 1.25rem; }
.rpt-filter-head  { background: linear-gradient(135deg,#6366f1,#4f46e5); padding: 11px 20px; display: flex; align-items: center; justify-content: space-between; }
.rpt-filter-head-title { display: flex; align-items: center; gap: 7px; color: #fff; font-size: .82rem; font-weight: 700; letter-spacing: .3px; }
.rpt-filter-head-title .material-icons { font-size: 17px; opacity: .9; }
.rpt-filter-body  { padding: 16px 20px 18px; }
.rpt-filter-field { display: flex; flex-direction: column; gap: 5px; min-width: 130px; flex: 1; }
.rpt-filter-lbl   { font-size: .68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 5px; }
.rpt-filter-lbl .material-icons { font-size: 13px; color: #6366f1; }
.rpt-filter-sel {
    border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 7px 32px 7px 12px;
    font-size: .82rem; font-weight: 500; color: #0f172a; background-color: #f8fafc;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
    -webkit-appearance: none; appearance: none; outline: none; cursor: pointer;
    transition: border-color .18s, box-shadow .18s;
}
.rpt-filter-sel:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); background-color: #fff; }
.rpt-filter-sel:hover { border-color: #a5b4fc; background-color: #fff; }
.rpt-filter-div   { width: 1px; background: #e2e8f0; align-self: stretch; margin: 0 4px; flex-shrink: 0; }
.rpt-btn-apply    { background: linear-gradient(135deg,#6366f1,#4f46e5); border: none; color: #fff; border-radius: 9px; padding: 8px 18px; font-size: .82rem; font-weight: 700; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(99,102,241,.35); transition: transform .15s,box-shadow .15s; white-space: nowrap; cursor: pointer; }
.rpt-btn-apply:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(99,102,241,.45); color: #fff; }
.rpt-btn-apply .material-icons { font-size: 15px; }
.rpt-btn-reset { background: #f1f5f9; border: 1.5px solid #e2e8f0; color: #475569; border-radius: 9px; padding: 7px 14px; font-size: .82rem; font-weight: 600; display: flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap; text-decoration: none; }
.rpt-btn-reset:hover { background: #e2e8f0; color: #1e293b; }
.rpt-btn-excel { background: #fff; border: 1.5px solid #e2e8f0; color: #475569; border-radius: 9px; padding: 7px 14px; font-size: .82rem; font-weight: 600; display: flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap; text-decoration: none; }
.rpt-btn-excel:hover { border-color: #10b981; color: #10b981; background: #ecfdf5; }
.rpt-btn-pdf { background: #fff; border: 1.5px solid #e2e8f0; color: #475569; border-radius: 9px; padding: 7px 14px; font-size: .82rem; font-weight: 600; display: flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap; text-decoration: none; }
.rpt-btn-pdf:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.rpt-btn-excel .material-icons, .rpt-btn-pdf .material-icons, .rpt-btn-reset .material-icons { font-size: 14px; }
.rpt-btn-download { background: #fff; border: 1.5px solid #e2e8f0; color: #475569; border-radius: 9px; padding: 7px 14px; font-size: .82rem; font-weight: 600; display: flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap; cursor: pointer; }
.rpt-btn-download:hover { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
.rpt-btn-download::after { display: none; }
.rpt-btn-download .material-icons { font-size: 14px; }
.rpt-dl-menu { border: 1.5px solid #e2e8f0 !important; border-radius: 12px !important; padding: 6px !important; box-shadow: 0 8px 24px rgba(15,23,42,.12) !important; min-width: 152px; }
.rpt-dl-excel, .rpt-dl-pdf { display: flex !important; align-items: center !important; gap: 8px; padding: 8px 12px !important; border-radius: 8px !important; font-size: .82rem !important; font-weight: 600; color: #475569; }
.rpt-dl-excel:hover { color: #10b981 !important; background: #ecfdf5 !important; }
.rpt-dl-pdf:hover { color: #ef4444 !important; background: #fef2f2 !important; }
.rpt-dl-excel .material-icons { font-size: 15px; color: #10b981; }
.rpt-dl-pdf .material-icons { font-size: 15px; color: #ef4444; }

/* Summary ribbon */
.em-ribbon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-radius: 14px; padding: 16px 22px; margin-bottom: 1.25rem;
    display: flex; flex-wrap: wrap; align-items: center; gap: 20px;
    box-shadow: 0 4px 16px rgba(239,68,68,.3);
}
.em-ribbon-stat { display: flex; flex-direction: column; align-items: center; }
.em-ribbon-val  { font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1; }
.em-ribbon-lbl  { font-size: .65rem; font-weight: 600; color: rgba(255,255,255,.65); text-transform: uppercase; letter-spacing: .6px; margin-top: 3px; }
.em-ribbon-div  { width: 1px; height: 36px; background: rgba(255,255,255,.2); flex-shrink: 0; }

/* Table */
.em-table thead th { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 9px 12px; white-space: nowrap; }
.em-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f8fafc; vertical-align: middle; font-size: .825rem; }
.em-table tbody tr:hover { background: #fafbff; }
.em-table tbody tr:last-child td { border-bottom: none; }

/* Type badges */
.type-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
.type-sla    { background: #fee2e2; color: #991b1b; }
.type-missed { background: #fef3c7; color: #92400e; }

/* Insight strips */
.em-insight { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border-radius: 12px; margin-bottom: 10px; border-left: 3px solid; }
.em-insight-warning { background: #fffbeb; border-color: #f59e0b; }
.em-insight-danger  { background: #fff5f5; border-color: #ef4444; }
.em-insight-success { background: #f0fdf4; border-color: #10b981; }
.em-insight-info    { background: #eff6ff; border-color: #6366f1; }
.em-insight .material-icons { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
.em-insight-warning .material-icons { color: #f59e0b; }
.em-insight-danger  .material-icons { color: #ef4444; }
.em-insight-success .material-icons { color: #10b981; }
.em-insight-info    .material-icons { color: #6366f1; }

/* dist bar */
.dist-bar-row   { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.dist-bar-label { font-size: .75rem; font-weight: 600; color: #64748b; min-width: 90px; flex-shrink: 0; }
.dist-bar-track { flex: 1; height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden; }
.dist-bar-fill  { height: 10px; border-radius: 5px; transition: width 1s ease; }
.dist-bar-count { font-size: .75rem; font-weight: 700; min-width: 28px; text-align: right; }

/* Pagination */
.em-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 10px; }
.em-page-info  { font-size: .78rem; color: #64748b; }
.em-page-info strong { color: #0f172a; }
.em-page-btns  { display: flex; align-items: center; gap: 4px; }
.em-page-btn   { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; border-radius: 8px; font-size: .78rem; font-weight: 600; text-decoration: none; border: 1.5px solid #e2e8f0; color: #475569; background: #fff; transition: all .15s; padding: 0 8px; }
.em-page-btn:hover  { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
.em-page-btn.active { background: #6366f1; border-color: #6366f1; color: #fff; }
.em-page-btn.disabled { opacity: .4; pointer-events: none; }
</style>

{{-- ══════════════════════════════════════════════════════ FILTER BAR --}}
<div class="rpt-filter-wrap em-section">
    <div class="rpt-filter-head">
        <div class="rpt-filter-head-title">
            <span class="material-icons">tune</span>
            Advanced Filters — Escalation Matrix
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($activeFilters->count())
            <span style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:rgba(255,255,255,.85);background:rgba(255,255,255,.15);padding:3px 10px;border-radius:20px">
                <span class="material-icons" style="font-size:13px">filter_alt</span>
                {{ $activeFilters->count() }} active
            </span>
            @endif
            <span style="font-size:.72rem;color:rgba(255,255,255,.5)">{{ $summary['total'] }} escalation{{ $summary['total'] !== 1 ? 's' : '' }}</span>
        </div>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                {{-- Time Period --}}
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

                {{-- Telecaller --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">headset_mic</span> Telecaller</label>
                    <select name="telecaller" class="rpt-filter-sel">
                        <option value="all">All Telecallers</option>
                        @foreach (($filterOptions['telecallers'] ?? collect()) as $tc)
                            <option value="{{ $tc->id }}" {{ (string)($filters['telecaller'] ?? 'all') === (string)$tc->id ? 'selected' : '' }}>{{ $tc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Manager --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">manage_accounts</span> Manager</label>
                    <select name="manager" class="rpt-filter-sel">
                        <option value="all">All Managers</option>
                        @foreach (($filterOptions['managers'] ?? collect()) as $mgr)
                            <option value="{{ $mgr->id }}" {{ (string)($filters['manager'] ?? 'all') === (string)$mgr->id ? 'selected' : '' }}>{{ $mgr->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Escalation Type --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">report_problem</span> Escalation Type</label>
                    <select name="type" class="rpt-filter-sel">
                        <option value="all"    {{ ($filters['type'] ?? 'all') === 'all'    ? 'selected' : '' }}>All Types</option>
                        <option value="sla"    {{ ($filters['type'] ?? 'all') === 'sla'    ? 'selected' : '' }}>Response SLA Breach</option>
                        <option value="missed" {{ ($filters['type'] ?? 'all') === 'missed' ? 'selected' : '' }}>Missed Follow-up</option>
                    </select>
                </div>

                <div class="rpt-filter-div d-none d-xl-block"></div>

                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <button type="submit" class="rpt-btn-apply">
                        <span class="material-icons">search</span> Apply
                    </button>
                    <a href="{{ $resetRoute }}" class="rpt-btn-reset">
                        <span class="material-icons">refresh</span> Reset
                    </a>
                    <div class="dropdown">
                        <button class="rpt-btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">file_download</span> Download
                        </button>
                        <ul class="dropdown-menu rpt-dl-menu">
                            <li><a class="dropdown-item rpt-dl-excel" href="{{ route('report_viewer.reports.export', ['report' => 'escalation-matrix', 'format' => 'excel'] + request()->query()) }}">
                                <span class="material-icons">table_view</span> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item rpt-dl-pdf" href="{{ route('report_viewer.reports.export', ['report' => 'escalation-matrix', 'format' => 'pdf'] + request()->query()) }}" target="_blank">
                                <span class="material-icons">picture_as_pdf</span> PDF
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

{{-- ══════════════════════════════════════════════════════ SUMMARY RIBBON --}}
<div class="em-ribbon em-section">
    <div class="em-ribbon-stat">
        <div class="em-ribbon-val">{{ number_format($summary['total']) }}</div>
        <div class="em-ribbon-lbl">Total Escalations</div>
    </div>
    <div class="em-ribbon-div"></div>
    <div class="em-ribbon-stat">
        <div class="em-ribbon-val" style="color:#fca5a5">{{ number_format($summary['sla_count']) }}</div>
        <div class="em-ribbon-lbl">SLA Breaches</div>
    </div>
    <div class="em-ribbon-div"></div>
    <div class="em-ribbon-stat">
        <div class="em-ribbon-val" style="color:#fde68a">{{ number_format($summary['missed_count']) }}</div>
        <div class="em-ribbon-lbl">Missed Follow-ups</div>
    </div>
    <div class="em-ribbon-div"></div>
    <div class="em-ribbon-stat">
        <div class="em-ribbon-val">{{ $summary['sla_rate'] }}%</div>
        <div class="em-ribbon-lbl">SLA Breach Rate</div>
    </div>
    <div class="em-ribbon-div d-none d-lg-block"></div>
    <div class="em-ribbon-stat d-none d-lg-flex">
        <div class="em-ribbon-val" style="color:#fde68a;font-size:1.15rem">{{ $summary['top_manager'] }}</div>
        <div class="em-ribbon-lbl">Top Offending Manager · {{ $summary['top_manager_count'] }}</div>
    </div>
    <div class="em-ribbon-div d-none d-xl-block"></div>
    <div class="em-ribbon-stat d-none d-xl-flex">
        <div class="em-ribbon-val" style="color:#fde68a;font-size:1.15rem">{{ $summary['top_telecaller'] }}</div>
        <div class="em-ribbon-lbl">Top Offending TC · {{ $summary['top_telecaller_count'] }}</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ KPI CARDS --}}
<div class="row g-3 em-section">
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="em-kpi" style="--kpi-accent:#6366f1;--kpi-color:#6366f1;--kpi-bg:rgba(99,102,241,.1)">
            <div class="em-kpi-icon"><span class="material-icons">warning</span></div>
            <div class="em-kpi-value">{{ number_format($summary['total']) }}</div>
            <div class="em-kpi-label">Total Escalations</div>
            <div class="em-kpi-sub">In selected period</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="em-kpi" style="--kpi-accent:#ef4444;--kpi-color:#ef4444;--kpi-bg:rgba(239,68,68,.1)">
            <div class="em-kpi-icon"><span class="material-icons">timer_off</span></div>
            <div class="em-kpi-value">{{ number_format($summary['sla_count']) }}</div>
            <div class="em-kpi-label">SLA Breaches</div>
            <div class="em-kpi-sub">No contact within {{ $slaMinutes }}min</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="em-kpi" style="--kpi-accent:#f59e0b;--kpi-color:#f59e0b;--kpi-bg:rgba(245,158,11,.1)">
            <div class="em-kpi-icon"><span class="material-icons">event_busy</span></div>
            <div class="em-kpi-value">{{ number_format($summary['missed_count']) }}</div>
            <div class="em-kpi-label">Missed Follow-ups</div>
            <div class="em-kpi-sub">Overdue escalations</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="em-kpi" style="--kpi-accent:#f97316;--kpi-color:#f97316;--kpi-bg:rgba(249,115,22,.1)">
            <div class="em-kpi-icon"><span class="material-icons">percent</span></div>
            <div class="em-kpi-value">{{ $summary['sla_rate'] }}%</div>
            <div class="em-kpi-label">SLA Breach Share</div>
            <div class="em-kpi-sub">of total escalations</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="em-kpi" style="--kpi-accent:#8b5cf6;--kpi-color:#8b5cf6;--kpi-bg:rgba(139,92,246,.1)">
            <div class="em-kpi-icon"><span class="material-icons">manage_accounts</span></div>
            <div class="em-kpi-value" style="font-size:1rem;padding-top:6px">{{ Str::limit($summary['top_manager'], 14) }}</div>
            <div class="em-kpi-label">Top Mgr by Escalations</div>
            <div class="em-kpi-sub"><strong style="color:#ef4444">{{ $summary['top_manager_count'] }}</strong> escalations</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="em-kpi" style="--kpi-accent:#06b6d4;--kpi-color:#06b6d4;--kpi-bg:rgba(6,182,212,.1)">
            <div class="em-kpi-icon"><span class="material-icons">headset_mic</span></div>
            <div class="em-kpi-value" style="font-size:1rem;padding-top:6px">{{ Str::limit($summary['top_telecaller'], 14) }}</div>
            <div class="em-kpi-label">Top TC by Escalations</div>
            <div class="em-kpi-sub"><strong style="color:#ef4444">{{ $summary['top_telecaller_count'] }}</strong> escalations</div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ CHARTS ROW 1 --}}
<div class="row g-3 em-section">
    {{-- By Manager bar --}}
    <div class="col-lg-8">
        <div class="em-card">
            <h3 class="em-card-title">Escalations by Manager</h3>
            <p class="em-card-sub">Top managers ranked by escalation count — sorted descending</p>
            <div style="height:260px"><canvas id="emManagerChart"></canvas></div>
        </div>
    </div>
    {{-- Type Doughnut --}}
    <div class="col-lg-4">
        <div class="em-card">
            <h3 class="em-card-title">Escalation Type Split</h3>
            <p class="em-card-sub">SLA breach vs Missed follow-up</p>
            <div style="height:180px;margin-bottom:16px"><canvas id="emTypeChart"></canvas></div>
            <div>
                <div class="dist-bar-row">
                    <span class="dist-bar-label" style="color:#ef4444">SLA Breach</span>
                    <div class="dist-bar-track"><div class="dist-bar-fill" style="background:#ef4444;width:{{ $summary['total']>0?round(($summary['sla_count']/$summary['total'])*100):0 }}%"></div></div>
                    <span class="dist-bar-count" style="color:#ef4444">{{ $summary['sla_count'] }}</span>
                </div>
                <div class="dist-bar-row">
                    <span class="dist-bar-label" style="color:#f59e0b">Missed F/U</span>
                    <div class="dist-bar-track"><div class="dist-bar-fill" style="background:#f59e0b;width:{{ $summary['total']>0?round(($summary['missed_count']/$summary['total'])*100):0 }}%"></div></div>
                    <span class="dist-bar-count" style="color:#f59e0b">{{ $summary['missed_count'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ CHARTS ROW 2 --}}
<div class="row g-3 em-section">
    {{-- Monthly Trend --}}
    <div class="col-lg-8">
        <div class="em-card">
            <h3 class="em-card-title">6-Month Escalation Trend</h3>
            <p class="em-card-sub">SLA breaches vs Missed follow-ups per month over the last 6 months</p>
            <div style="height:240px"><canvas id="emTrendChart"></canvas></div>
        </div>
    </div>
    {{-- By Telecaller --}}
    <div class="col-lg-4">
        <div class="em-card">
            <h3 class="em-card-title">Top Telecallers</h3>
            <p class="em-card-sub">Escalation count per telecaller</p>
            <div style="height:240px"><canvas id="emTelecallerChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ INSIGHTS --}}
@if(count($insights))
<div class="em-card em-section">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-icons" style="color:#6366f1;font-size:20px">auto_awesome</span>
        <h3 class="em-card-title mb-0">Smart Insights</h3>
        <span style="font-size:.68rem;background:#eef2ff;color:#6366f1;padding:2px 8px;border-radius:20px;font-weight:700;border:1px solid #c7d2fe">{{ count($insights) }} alert{{ count($insights)>1?'s':'' }}</span>
    </div>
    @foreach($insights as $ins)
    <div class="em-insight em-insight-{{ $ins['type'] }}">
        <span class="material-icons">{{ $ins['icon'] }}</span>
        <span style="font-size:.82rem;font-weight:500;color:#1e293b;line-height:1.5">{{ $ins['text'] }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- ══════════════════════════════════════════════════════ TABLE --}}
<div class="custom-table em-section">
    <div class="table-header">
        <h3>
            <span class="material-icons me-2" style="vertical-align:-5px;font-size:20px">report_problem</span>
            Escalation Records
        </h3>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark" style="font-size:11px">{{ $totalRows }} total</span>
            <span style="font-size:.72rem;color:#94a3b8">Page {{ $page }} of {{ $lastPage }}</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 em-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Type</th>
                    <th>Lead Code</th>
                    <th>Lead Name</th>
                    <th>Telecaller</th>
                    <th>Manager</th>
                    <th>Escalated At</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagedRows as $row)
                @php
                    $rowNum = (($page - 1) * $perPage) + $loop->index + 1;
                    $isSla  = $row['type'] === 'Response SLA';
                @endphp
                <tr>
                    <td style="color:#94a3b8;font-weight:700;font-size:.8rem">{{ $rowNum }}</td>
                    <td>
                        <span class="type-badge {{ $isSla ? 'type-sla' : 'type-missed' }}">
                            <span class="material-icons" style="font-size:11px">{{ $isSla ? 'timer_off' : 'event_busy' }}</span>
                            {{ $isSla ? 'SLA Breach' : 'Missed F/U' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:.8rem;background:#f1f5f9;padding:2px 7px;border-radius:5px;color:#475569">{{ $row['lead_code'] }}</span>
                    </td>
                    <td style="font-weight:600;color:#0f172a">{{ $row['lead_name'] }}</td>
                    <td>
                        @php
                            $aColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
                            $aColor  = $aColors[abs(crc32($row['telecaller'])) % count($aColors)];
                            $initials = mb_strtoupper(mb_substr($row['telecaller'],0,1)) . (str_contains($row['telecaller'],' ') ? mb_strtoupper(mb_substr(strrchr($row['telecaller'],' '),1,1)) : '');
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:28px;height:28px;border-radius:50%;background:{{ $aColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.65rem;font-weight:800;color:#fff">{{ $initials }}</div>
                            <span style="font-size:.82rem;color:#334155">{{ $row['telecaller'] }}</span>
                        </div>
                    </td>
                    <td style="font-size:.82rem;color:#475569">{{ $row['manager'] }}</td>
                    <td>
                        <span style="font-size:.78rem;color:#64748b;white-space:nowrap">
                            <span class="material-icons" style="font-size:12px;vertical-align:-2px;color:#94a3b8">schedule</span>
                            {{ $row['escalated_at'] }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:.78rem;color:{{ $isSla ? '#dc2626' : '#b45309' }};font-weight:500">{{ $row['detail'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <span class="material-icons d-block mb-2" style="font-size:36px;color:#e2e8f0">check_circle</span>
                        No escalation records found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    @if($lastPage > 1)
    <div class="em-pagination">
        <div class="em-page-info">
            Showing <strong>{{ (($page-1)*$perPage)+1 }}</strong>–<strong>{{ min($page*$perPage,$totalRows) }}</strong>
            of <strong>{{ $totalRows }}</strong> records
        </div>
        <div class="em-page-btns">
            {{-- Prev --}}
            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
               class="em-page-btn {{ $page <= 1 ? 'disabled' : '' }}">
                <span class="material-icons" style="font-size:14px">chevron_left</span>
            </a>

            {{-- Page numbers --}}
            @php
                $start = max(1, $page - 2);
                $end   = min($lastPage, $page + 2);
            @endphp
            @if($start > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" class="em-page-btn">1</a>
                @if($start > 2)<span style="color:#94a3b8;padding:0 4px">…</span>@endif
            @endif
            @for($i = $start; $i <= $end; $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                   class="em-page-btn {{ $i === $page ? 'active' : '' }}">{{ $i }}</a>
            @endfor
            @if($end < $lastPage)
                @if($end < $lastPage - 1)<span style="color:#94a3b8;padding:0 4px">…</span>@endif
                <a href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}" class="em-page-btn">{{ $lastPage }}</a>
            @endif

            {{-- Next --}}
            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
               class="em-page-btn {{ $page >= $lastPage ? 'disabled' : '' }}">
                <span class="material-icons" style="font-size:14px">chevron_right</span>
            </a>
        </div>
    </div>
    @else
    <div class="em-pagination">
        <div class="em-page-info">Showing <strong>{{ $totalRows }}</strong> record{{ $totalRows !== 1 ? 's' : '' }}</div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════ CHARTS SCRIPT --}}
<script>
(function() {
    const byMgr     = @json($byManager);
    const byTc      = @json($byTelecaller);
    const mLabels   = @json($monthLabels);
    const mSla      = @json($monthSla);
    const mMissed   = @json($monthMissed);
    const summary   = @json($summary);

    const RED    = '#ef4444', AMBER = '#f59e0b', INDIGO = '#6366f1', PURPLE = '#8b5cf6', CYAN = '#06b6d4', GREEN = '#10b981';
    const palette = [RED, AMBER, INDIGO, PURPLE, CYAN, GREEN, '#f97316', '#ec4899'];

    function initCharts() {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

        // ── 1. Escalations by Manager (horizontal bar)
        const ctxMgr = document.getElementById('emManagerChart');
        if (ctxMgr) {
            const mgrNames  = Object.keys(byMgr);
            const mgrCounts = Object.values(byMgr);
            if (mgrNames.length) {
                new Chart(ctxMgr, {
                    type: 'bar',
                    data: {
                        labels: mgrNames,
                        datasets: [{
                            label: 'Escalations',
                            data: mgrCounts,
                            backgroundColor: mgrCounts.map((_, i) => palette[i % palette.length] + 'cc'),
                            borderColor:     mgrCounts.map((_, i) => palette[i % palette.length]),
                            borderWidth: 1.5,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x} escalation${ctx.parsed.x !== 1 ? 's' : ''}` } }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 }, stepSize: 1 } },
                            y: { grid: { display: false }, ticks: { color: '#475569', font: { size: 11 } } }
                        }
                    }
                });
            } else {
                ctxMgr.parentElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:260px;color:#94a3b8;font-size:.82rem;flex-direction:column;gap:8px"><span class="material-icons" style="font-size:36px;color:#e2e8f0">bar_chart</span>No data for selected period</div>';
            }
        }

        // ── 2. Type Doughnut
        const ctxType = document.getElementById('emTypeChart');
        if (ctxType) {
            new Chart(ctxType, {
                type: 'doughnut',
                data: {
                    labels: ['SLA Breach', 'Missed Follow-up'],
                    datasets: [{ data: [summary.sla_count, summary.missed_count], backgroundColor: [RED, AMBER], borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                    }
                }
            });
        }

        // ── 3. Monthly Trend line
        const ctxTrend = document.getElementById('emTrendChart');
        if (ctxTrend) {
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: mLabels,
                    datasets: [
                        { label: 'SLA Breaches',     data: mSla,    borderColor: RED,   backgroundColor: 'rgba(239,68,68,.1)',  fill: true, tension: .35, borderWidth: 2, pointRadius: 4, pointBackgroundColor: RED },
                        { label: 'Missed Follow-ups', data: mMissed, borderColor: AMBER, backgroundColor: 'rgba(245,158,11,.08)', fill: true, tension: .35, borderWidth: 2, pointRadius: 4, pointBackgroundColor: AMBER },
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 }, stepSize: 1 } },
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } }
                    }
                }
            });
        }

        // ── 4. Escalations by Telecaller (vertical bar)
        const ctxTc = document.getElementById('emTelecallerChart');
        if (ctxTc) {
            const tcNames  = Object.keys(byTc);
            const tcCounts = Object.values(byTc);
            if (tcNames.length) {
                new Chart(ctxTc, {
                    type: 'bar',
                    data: {
                        labels: tcNames,
                        datasets: [{
                            label: 'Escalations',
                            data: tcCounts,
                            backgroundColor: CYAN + 'bb',
                            borderColor: CYAN,
                            borderWidth: 1.5,
                            borderRadius: 5,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} escalation${ctx.parsed.y !== 1 ? 's' : ''}` } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 }, stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 }, maxRotation: 30 } }
                        }
                    }
                });
            } else {
                ctxTc.parentElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:240px;color:#94a3b8;font-size:.82rem;flex-direction:column;gap:8px"><span class="material-icons" style="font-size:36px;color:#e2e8f0">headset_mic</span>No data</div>';
            }
        }
    }

    if (typeof Chart !== 'undefined') {
        initCharts();
    } else {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        s.onload = initCharts;
        document.head.appendChild(s);
    }
})();
</script>
@endsection
