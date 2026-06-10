@extends('layouts.app')

@section('page_title', 'Course Intakes')

@section('content')
<style>
/* ── Page header ──────────────────────────────────────── */
.ci-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
}
.ci-header-title { font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
.ci-header-sub   { font-size: .82rem; color: #64748b; margin: 4px 0 0; }
.ci-add-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border-radius: 10px; font-size: .85rem; font-weight: 700;
    background: linear-gradient(135deg,#6366f1,#4f46e5); color: #fff; border: none;
    text-decoration: none; box-shadow: 0 4px 12px rgba(99,102,241,.35);
    transition: transform .15s, box-shadow .15s; white-space: nowrap;
}
.ci-add-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(99,102,241,.45); color: #fff; }
.ci-add-btn .material-icons { font-size: 18px; }

/* ── Export button ────────────────────────────────────── */
.ci-export-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 18px; border-radius: 10px; font-size: .85rem; font-weight: 600;
    background: #fff; color: #374151; border: 1.5px solid #e2e8f0;
    cursor: pointer; transition: all .15s; white-space: nowrap;
    box-shadow: 0 1px 4px rgba(15,23,42,.06);
}
.ci-export-btn:hover { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
.ci-export-btn::after { display: none; }
.ci-export-btn .material-icons { font-size: 18px; color: #6366f1; }
.ci-dl-menu { border: 1.5px solid #e2e8f0 !important; border-radius: 12px !important; padding: 6px !important; box-shadow: 0 8px 24px rgba(15,23,42,.12) !important; min-width: 160px; }
.ci-dl-item { display: flex !important; align-items: center !important; gap: 8px; padding: 8px 12px !important; border-radius: 8px !important; font-size: .82rem !important; font-weight: 600; color: #475569; }
.ci-dl-item:hover { background: #f8fafc !important; color: #0f172a !important; }

/* ── Year tabs ────────────────────────────────────────── */
.ci-year-bar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    background: #fff; border-radius: 14px; padding: 14px 20px;
    box-shadow: 0 1px 6px rgba(15,23,42,.07); border: 1px solid #e2e8f0;
    margin-bottom: 22px;
}
.ci-year-label { font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; white-space: nowrap; }
.ci-year-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 9px; font-size: .82rem; font-weight: 600;
    text-decoration: none; border: 1.5px solid #e2e8f0; color: #475569;
    background: #f8fafc; transition: all .15s;
}
.ci-year-btn:hover { border-color: #a5b4fc; color: #6366f1; background: #eef2ff; }
.ci-year-btn.active {
    background: linear-gradient(135deg,#6366f1,#4f46e5); color: #fff;
    border-color: transparent; box-shadow: 0 3px 10px rgba(99,102,241,.35);
}
.ci-year-btn .ci-cur-badge {
    font-size: .65rem; font-weight: 700; padding: 1px 7px; border-radius: 20px;
    background: rgba(255,255,255,.25); color: #fff;
}
.ci-year-btn:not(.active) .ci-cur-badge { background: #eef2ff; color: #6366f1; }

/* ── KPI stats ────────────────────────────────────────── */
.ci-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 14px; margin-bottom: 16px; }
.ci-kpi {
    background: #fff; border-radius: 14px; padding: 18px 20px;
    border: 1px solid #e2e8f0; box-shadow: 0 1px 6px rgba(15,23,42,.06);
    position: relative; overflow: hidden;
}
.ci-kpi::before { content:''; position:absolute; top:0;left:0;right:0;height:3px; background:var(--ka,#6366f1); border-radius:14px 14px 0 0; }
.ci-kpi-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px; background:var(--ki,rgba(99,102,241,.1)); }
.ci-kpi-icon .material-icons { font-size:20px; color:var(--kc,#6366f1); }
.ci-kpi-val { font-size:1.8rem;font-weight:800;color:var(--kc,#0f172a);line-height:1.1; }
.ci-kpi-lbl { font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-top:4px; }
.ci-kpi-bar { height:4px;background:#f1f5f9;border-radius:2px;margin-top:10px;overflow:hidden; }
.ci-kpi-bar-fill { height:4px;border-radius:2px;background:var(--ka,#6366f1); transition:width .8s ease; }

/* ── Quota cards ──────────────────────────────────────── */
.ci-quota-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px; }
@media (max-width:600px) { .ci-quota-row { grid-template-columns: 1fr; } }
.ci-quota-card { border-radius: 14px; padding: 18px 20px; border: 1.5px solid; }
.ci-quota-card.mgmt   { background:#eef2ff; border-color:#c7d2fe; }
.ci-quota-card.coun   { background:#ecfdf5; border-color:#a7f3d0; }
.ci-quota-head { display:flex;align-items:center;gap:8px;margin-bottom:14px; }
.ci-quota-badge { width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#fff; }
.ci-quota-badge.mgmt { background:#6366f1; }
.ci-quota-badge.coun { background:#10b981; }
.ci-quota-name { font-size:.82rem;font-weight:700;color:#0f172a; }
.ci-quota-stats { display:flex;gap:24px; }
.ci-quota-stat-lbl { font-size:.65rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px; }
.ci-quota-stat-val { font-size:1.4rem;font-weight:800;line-height:1; }
.ci-quota-fill { margin-top:12px; }
.ci-quota-fill-track { height:6px;background:rgba(0,0,0,.07);border-radius:3px;overflow:hidden; }
.ci-quota-fill-bar   { height:6px;border-radius:3px;transition:width .8s ease; }

/* ── Table card ───────────────────────────────────────── */
.ci-table-card { background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 8px rgba(15,23,42,.06); }
.ci-table-head {
    background: linear-gradient(135deg,#1e3a6e,#0f172a);
    padding: 16px 22px; display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
}
.ci-table-head-left { display:flex;align-items:center;gap:10px; }
.ci-table-head-left .material-icons { color:#fff;font-size:20px; }
.ci-table-head-title { color:#fff;font-weight:700;font-size:.95rem; }
.ci-table-head-sub   { color:rgba(255,255,255,.5);font-size:.75rem;margin-top:1px; }
.ci-count-badge {
    background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.2);
    border-radius:20px;font-size:.72rem;font-weight:700;padding:3px 11px;
}

table.ci-table { width:100%;border-collapse:collapse; }
table.ci-table thead th { font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.6px;padding:10px 14px;border-bottom:2px solid #e2e8f0;white-space:nowrap; }
table.ci-table thead .th-mgmt { background:#f5f3ff;color:#6366f1; }
table.ci-table thead .th-coun { background:#f0fdf4;color:#10b981; }
table.ci-table thead .th-group { text-align:center;font-size:.72rem;font-weight:700;padding:9px 14px;border-bottom:1px solid #e2e8f0; }
table.ci-table tbody tr { border-bottom:1px solid #f1f5f9;transition:background .12s; }
table.ci-table tbody tr:hover { background:#fafbff; }
table.ci-table tbody tr:last-child { border-bottom:none; }
table.ci-table tbody td { padding:13px 14px;font-size:.84rem;vertical-align:middle; }
.ci-course-name { font-weight:700;color:#0f172a;font-size:.88rem; }
.ci-course-sub  { font-size:.72rem;color:#94a3b8;margin-top:2px; }
.ci-num { font-weight:700;font-size:.88rem; }
.ci-num-green  { color:#10b981; }
.ci-num-blue   { color:#6366f1; }
.ci-num-red    { color:#ef4444; }
.ci-num-muted  { color:#0f172a; }
.ci-fill-wrap { display:flex;align-items:center;gap:8px;min-width:120px; }
.ci-fill-track { flex:1;height:7px;background:#f1f5f9;border-radius:4px;overflow:hidden; }
.ci-fill-bar   { height:7px;border-radius:4px;transition:width .7s ease; }
.ci-fill-pct   { font-size:.72rem;font-weight:700;color:#64748b;min-width:28px; }
.ci-action-btn {
    width:30px;height:30px;border-radius:8px;border:1.5px solid;display:inline-flex;
    align-items:center;justify-content:center;text-decoration:none;transition:all .15s;cursor:pointer;background:transparent;
}
.ci-action-btn.edit  { border-color:#c7d2fe;color:#6366f1; }
.ci-action-btn.edit:hover  { background:#6366f1;color:#fff;border-color:#6366f1; }
.ci-action-btn.del   { border-color:#fecaca;color:#ef4444; }
.ci-action-btn.del:hover   { background:#ef4444;color:#fff;border-color:#ef4444; }
.ci-action-btn .material-icons { font-size:15px; }

/* ── Pagination ───────────────────────────────────────── */
.ci-pagination { padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:10px; }
.ci-pagination-info { font-size:.78rem;color:#64748b;font-weight:500; }
.ci-page-links { display:flex;gap:4px;flex-wrap:wrap; }
.ci-page-btn {
    min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;
    border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none;
    border:1.5px solid #e2e8f0;color:#475569;background:#fff;transition:all .15s;padding:0 8px;
}
.ci-page-btn:hover    { border-color:#6366f1;color:#6366f1;background:#eef2ff; }
.ci-page-btn.active   { background:#6366f1;border-color:#6366f1;color:#fff;box-shadow:0 2px 8px rgba(99,102,241,.3); }
.ci-page-btn.disabled { opacity:.4;pointer-events:none; }

/* ── Empty state ──────────────────────────────────────── */
.ci-empty { text-align:center;padding:60px 20px; }
.ci-empty .material-icons { font-size:52px;color:#cbd5e1;display:block;margin-bottom:12px; }
.ci-empty-title { font-size:1rem;font-weight:700;color:#334155;margin-bottom:6px; }
.ci-empty-sub   { font-size:.82rem;color:#94a3b8; }
</style>

{{-- ── Page header ─────────────────────────────────────────────── --}}
<div class="ci-header">
    <div>
        <h2 class="ci-header-title">Course Intakes</h2>
        <p class="ci-header-sub">Seat allocation per quota per academic year</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if($selectedYear)
        <div class="dropdown">
            <button class="ci-export-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-icons">file_download</span>
                Export
            </button>
            <ul class="dropdown-menu shadow ci-dl-menu">
                <li>
                    <a class="dropdown-item ci-dl-item"
                       href="{{ route('admin.course-intakes.export', ['format'=>'excel', 'year_id'=>$selectedYear->id]) }}">
                        <span class="material-icons" style="font-size:16px;color:#10b981;">table_view</span>
                        Excel (.xlsx)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item ci-dl-item"
                       href="{{ route('admin.course-intakes.export', ['format'=>'pdf', 'year_id'=>$selectedYear->id]) }}"
                       target="_blank">
                        <span class="material-icons" style="font-size:16px;color:#ef4444;">picture_as_pdf</span>
                        PDF
                    </a>
                </li>
            </ul>
        </div>
        @endif
        <a href="{{ route('admin.course-intakes.create') }}" class="ci-add-btn">
            <span class="material-icons">add</span>
            Add Intake
        </a>
    </div>
</div>

{{-- ── Flash messages ───────────────────────────────────────────── --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" style="border-radius:12px;border:none;background:#ecfdf5;color:#065f46;box-shadow:0 1px 6px rgba(16,185,129,.15)">
        <span class="material-icons" style="font-size:18px;">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" style="border-radius:12px;border:none;background:#fef2f2;color:#991b1b;box-shadow:0 1px 6px rgba(239,68,68,.15)">
        <span class="material-icons" style="font-size:18px;">error</span>
        {{ session('error') }}
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Year tabs ────────────────────────────────────────────────── --}}
<div class="ci-year-bar">
    <span class="ci-year-label">Academic Year</span>
    @forelse ($years as $y)
        <a href="{{ route('admin.course-intakes.index', ['year_id' => $y->id]) }}"
           class="ci-year-btn {{ $selectedYear?->id === $y->id ? 'active' : '' }}">
            <span class="material-icons" style="font-size:14px;">event</span>
            {{ $y->name }}
            @if ($y->is_active)
                <span class="ci-cur-badge">Current</span>
            @endif
        </a>
    @empty
        <span style="font-size:.82rem;color:#94a3b8;">
            No academic years found.
            <a href="{{ route('admin.academic-years.create') }}" style="color:#6366f1;font-weight:600;">Add one first.</a>
        </span>
    @endforelse
</div>

@if (!$selectedYear)
    <div class="ci-table-card">
        <div class="ci-empty">
            <span class="material-icons">event_seat</span>
            <div class="ci-empty-title">No Academic Year Selected</div>
            <div class="ci-empty-sub">Choose a year above to view its seat allocation.</div>
        </div>
    </div>
@elseif ($allIntakes->isEmpty())
    <div class="ci-table-card">
        <div class="ci-empty">
            <span class="material-icons">event_seat</span>
            <div class="ci-empty-title">No Intakes for {{ $selectedYear->name }}</div>
            <div class="ci-empty-sub mb-3">Define seat allocations to get started.</div>
            <a href="{{ route('admin.course-intakes.create') }}" class="ci-add-btn" style="display:inline-flex;margin-top:10px;">
                <span class="material-icons">add</span> Add Intake
            </a>
        </div>
    </div>
@else
    @php
        $totalMgmtSeats = $allIntakes->sum('management_seats');
        $totalCounSeats = $allIntakes->sum('counselling_seats');
        $totalSeats     = $totalMgmtSeats + $totalCounSeats;
        $totalMgmtEnr   = $allIntakes->sum('management_enrolled');
        $totalCounEnr   = $allIntakes->sum('counselling_enrolled');
        $totalEnrolled  = $totalMgmtEnr + $totalCounEnr;
        $totalBalance   = $totalSeats - $totalEnrolled;
        $fillPct        = $totalSeats > 0 ? round($totalEnrolled / $totalSeats * 100) : 0;
        $mgmtBalance    = $totalMgmtSeats - $totalMgmtEnr;
        $counBalance    = $totalCounSeats - $totalCounEnr;
        $mgmtFillPct    = $totalMgmtSeats > 0 ? round($totalMgmtEnr / $totalMgmtSeats * 100) : 0;
        $counFillPct    = $totalCounSeats  > 0 ? round($totalCounEnr  / $totalCounSeats  * 100) : 0;
        $fillColor      = $fillPct >= 90 ? '#ef4444' : ($fillPct >= 70 ? '#f59e0b' : '#10b981');
    @endphp

    {{-- ── KPI Cards ──────────────────────────────────────────────── --}}
    <div class="ci-kpi-grid">
        {{-- Total Seats --}}
        <div class="ci-kpi" style="--ka:#6366f1;--ki:rgba(99,102,241,.1);--kc:#6366f1">
            <div class="ci-kpi-icon"><span class="material-icons">event_seat</span></div>
            <div class="ci-kpi-val">{{ number_format($totalSeats) }}</div>
            <div class="ci-kpi-lbl">Total Seats</div>
        </div>
        {{-- Enrolled --}}
        <div class="ci-kpi" style="--ka:#10b981;--ki:rgba(16,185,129,.1);--kc:#10b981">
            <div class="ci-kpi-icon"><span class="material-icons">how_to_reg</span></div>
            <div class="ci-kpi-val">{{ number_format($totalEnrolled) }}</div>
            <div class="ci-kpi-lbl">Enrolled</div>
        </div>
        {{-- Balance --}}
        <div class="ci-kpi" style="--ka:{{ $totalBalance <= 0 ? '#ef4444' : '#f59e0b' }};--ki:{{ $totalBalance <= 0 ? 'rgba(239,68,68,.1)' : 'rgba(245,158,11,.1)' }};--kc:{{ $totalBalance <= 0 ? '#ef4444' : '#f59e0b' }}">
            <div class="ci-kpi-icon"><span class="material-icons">chair</span></div>
            <div class="ci-kpi-val">{{ number_format($totalBalance) }}</div>
            <div class="ci-kpi-lbl">Seats Available</div>
        </div>
        {{-- Fill Rate --}}
        <div class="ci-kpi" style="--ka:{{ $fillColor }};--ki:{{ $fillPct >= 90 ? 'rgba(239,68,68,.1)' : ($fillPct >= 70 ? 'rgba(245,158,11,.1)' : 'rgba(16,185,129,.1)') }};--kc:{{ $fillColor }}">
            <div class="ci-kpi-icon"><span class="material-icons">donut_large</span></div>
            <div class="ci-kpi-val">{{ $fillPct }}%</div>
            <div class="ci-kpi-lbl">Fill Rate</div>
            <div class="ci-kpi-bar"><div class="ci-kpi-bar-fill" style="width:{{ $fillPct }}%;background:{{ $fillColor }}"></div></div>
        </div>
        {{-- Courses --}}
        <div class="ci-kpi" style="--ka:#8b5cf6;--ki:rgba(139,92,246,.1);--kc:#8b5cf6">
            <div class="ci-kpi-icon"><span class="material-icons">school</span></div>
            <div class="ci-kpi-val">{{ $allIntakes->count() }}</div>
            <div class="ci-kpi-lbl">Courses</div>
        </div>
    </div>

    {{-- ── Quota Summary Cards ──────────────────────────────────────── --}}
    <div class="ci-quota-row">
        {{-- Management Quota --}}
        <div class="ci-quota-card mgmt">
            <div class="ci-quota-head">
                <div class="ci-quota-badge mgmt">M</div>
                <div class="ci-quota-name">Management Quota</div>
            </div>
            <div class="ci-quota-stats">
                <div>
                    <div class="ci-quota-stat-lbl">Seats</div>
                    <div class="ci-quota-stat-val" style="color:#0f172a">{{ number_format($totalMgmtSeats) }}</div>
                </div>
                <div>
                    <div class="ci-quota-stat-lbl">Enrolled</div>
                    <div class="ci-quota-stat-val" style="color:#10b981">{{ number_format($totalMgmtEnr) }}</div>
                </div>
                <div>
                    <div class="ci-quota-stat-lbl">Balance</div>
                    <div class="ci-quota-stat-val" style="color:{{ $mgmtBalance <= 0 ? '#ef4444' : '#6366f1' }}">{{ number_format($mgmtBalance) }}</div>
                </div>
            </div>
            <div class="ci-quota-fill mt-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:.68rem;color:#64748b;font-weight:600">Fill rate</span>
                    <span style="font-size:.68rem;font-weight:700;color:#6366f1">{{ $mgmtFillPct }}%</span>
                </div>
                <div class="ci-quota-fill-track">
                    <div class="ci-quota-fill-bar" style="width:{{ $mgmtFillPct }}%;background:#6366f1"></div>
                </div>
            </div>
        </div>
        {{-- Counselling Quota --}}
        <div class="ci-quota-card coun">
            <div class="ci-quota-head">
                <div class="ci-quota-badge coun">C</div>
                <div class="ci-quota-name">Counselling Quota</div>
            </div>
            <div class="ci-quota-stats">
                <div>
                    <div class="ci-quota-stat-lbl">Seats</div>
                    <div class="ci-quota-stat-val" style="color:#0f172a">{{ number_format($totalCounSeats) }}</div>
                </div>
                <div>
                    <div class="ci-quota-stat-lbl">Enrolled</div>
                    <div class="ci-quota-stat-val" style="color:#10b981">{{ number_format($totalCounEnr) }}</div>
                </div>
                <div>
                    <div class="ci-quota-stat-lbl">Balance</div>
                    <div class="ci-quota-stat-val" style="color:{{ $counBalance <= 0 ? '#ef4444' : '#10b981' }}">{{ number_format($counBalance) }}</div>
                </div>
            </div>
            <div class="ci-quota-fill mt-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:.68rem;color:#64748b;font-weight:600">Fill rate</span>
                    <span style="font-size:.68rem;font-weight:700;color:#10b981">{{ $counFillPct }}%</span>
                </div>
                <div class="ci-quota-fill-track">
                    <div class="ci-quota-fill-bar" style="width:{{ $counFillPct }}%;background:#10b981"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table Card ───────────────────────────────────────────────── --}}
    <div class="ci-table-card">
        {{-- Table header --}}
        <div class="ci-table-head">
            <div class="ci-table-head-left">
                <span class="material-icons">table_chart</span>
                <div>
                    <div class="ci-table-head-title">Intake Details — {{ $selectedYear->name }}</div>
                    <div class="ci-table-head-sub">Course-wise seat allocation and fill status</div>
                </div>
            </div>
            <span class="ci-count-badge">{{ $allIntakes->count() }} course{{ $allIntakes->count() !== 1 ? 's' : '' }}</span>
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto">
            <table class="ci-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align:middle;min-width:180px;border-bottom:2px solid #e2e8f0;">#&nbsp;&nbsp;Course</th>
                        <th colspan="3" class="th-group th-mgmt" style="border-left:2px solid #c7d2fe;">
                            <span style="display:inline-flex;align-items:center;gap:5px;">
                                <span style="width:18px;height:18px;border-radius:5px;background:#6366f1;color:#fff;font-size:.65rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">M</span>
                                Management Quota
                            </span>
                        </th>
                        <th colspan="3" class="th-group th-coun" style="border-left:2px solid #a7f3d0;">
                            <span style="display:inline-flex;align-items:center;gap:5px;">
                                <span style="width:18px;height:18px;border-radius:5px;background:#10b981;color:#fff;font-size:.65rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">C</span>
                                Counselling Quota
                            </span>
                        </th>
                        <th rowspan="2" style="vertical-align:middle;min-width:130px;border-bottom:2px solid #e2e8f0;">Overall Fill</th>
                        <th rowspan="2" style="vertical-align:middle;border-bottom:2px solid #e2e8f0;"></th>
                    </tr>
                    <tr>
                        <th class="th-mgmt text-center" style="border-left:2px solid #c7d2fe;">Seats</th>
                        <th class="th-mgmt text-center">Enrolled</th>
                        <th class="th-mgmt text-center">Balance</th>
                        <th class="th-coun text-center" style="border-left:2px solid #a7f3d0;">Seats</th>
                        <th class="th-coun text-center">Enrolled</th>
                        <th class="th-coun text-center">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($intakes as $i => $intake)
                        @php
                            $pct      = $intake->total_seats > 0
                                ? round($intake->total_enrolled / $intake->total_seats * 100) : 0;
                            $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#10b981');
                            $rowNum   = ($intakes->currentPage() - 1) * $intakes->perPage() + $i + 1;
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="width:24px;height:24px;border-radius:7px;background:#f1f5f9;color:#94a3b8;font-size:.72rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $rowNum }}</span>
                                    <div>
                                        <div class="ci-course-name">{{ $intake->course?->name ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            {{-- Management --}}
                            <td class="text-center" style="background:#fafaff;border-left:2px solid #e0e7ff;">
                                <span class="ci-num ci-num-muted">{{ $intake->management_seats }}</span>
                            </td>
                            <td class="text-center" style="background:#fafaff;">
                                <span class="ci-num ci-num-green">{{ $intake->management_enrolled }}</span>
                            </td>
                            <td class="text-center" style="background:#fafaff;">
                                <span class="ci-num {{ $intake->management_balance <= 0 ? 'ci-num-red' : 'ci-num-blue' }}">{{ $intake->management_balance }}</span>
                            </td>
                            {{-- Counselling --}}
                            <td class="text-center" style="background:#f0fdf9;border-left:2px solid #a7f3d0;">
                                <span class="ci-num ci-num-muted">{{ $intake->counselling_seats }}</span>
                            </td>
                            <td class="text-center" style="background:#f0fdf9;">
                                <span class="ci-num ci-num-green">{{ $intake->counselling_enrolled }}</span>
                            </td>
                            <td class="text-center" style="background:#f0fdf9;">
                                <span class="ci-num {{ $intake->counselling_balance <= 0 ? 'ci-num-red' : 'ci-num-green' }}">{{ $intake->counselling_balance }}</span>
                            </td>
                            {{-- Fill rate --}}
                            <td>
                                <div class="ci-fill-wrap">
                                    <div class="ci-fill-track">
                                        <div class="ci-fill-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                                    </div>
                                    <span class="ci-fill-pct" style="color:{{ $barColor }}">{{ $pct }}%</span>
                                </div>
                            </td>
                            {{-- Actions --}}
                            <td>
                                <div style="display:flex;gap:5px;align-items:center;">
                                    <a href="{{ route('admin.course-intakes.edit', $intake->encrypted_id) }}" class="ci-action-btn edit" title="Edit">
                                        <span class="material-icons">edit</span>
                                    </a>
                                    <form action="{{ route('admin.course-intakes.destroy', $intake->encrypted_id) }}" method="POST"
                                          onsubmit="return confirm('Remove this intake? Historical lead data is preserved.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ci-action-btn del" title="Delete">
                                            <span class="material-icons">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ───────────────────────────────────────────── --}}
        @if ($intakes instanceof \Illuminate\Pagination\LengthAwarePaginator && $intakes->lastPage() > 1)
        <div class="ci-pagination">
            <div class="ci-pagination-info">
                Showing <strong>{{ $intakes->firstItem() }}–{{ $intakes->lastItem() }}</strong>
                of <strong>{{ $intakes->total() }}</strong> courses
                &nbsp;·&nbsp; Page {{ $intakes->currentPage() }} of {{ $intakes->lastPage() }}
            </div>
            <div class="ci-page-links">
                {{-- Prev --}}
                <a href="{{ $intakes->previousPageUrl() ?? '#' }}"
                   class="ci-page-btn {{ $intakes->onFirstPage() ? 'disabled' : '' }}">
                    <span class="material-icons" style="font-size:15px;">chevron_left</span>
                </a>

                @php
                    $cur  = $intakes->currentPage();
                    $last = $intakes->lastPage();
                    $range = collect(range(max(1, $cur - 2), min($last, $cur + 2)));
                @endphp

                @if ($range->first() > 1)
                    <a href="{{ $intakes->url(1) }}" class="ci-page-btn">1</a>
                    @if ($range->first() > 2)
                        <span style="color:#94a3b8;padding:0 4px;align-self:center;">…</span>
                    @endif
                @endif

                @foreach ($range as $p)
                    <a href="{{ $intakes->url($p) }}" class="ci-page-btn {{ $p === $cur ? 'active' : '' }}">{{ $p }}</a>
                @endforeach

                @if ($range->last() < $last)
                    @if ($range->last() < $last - 1)
                        <span style="color:#94a3b8;padding:0 4px;align-self:center;">…</span>
                    @endif
                    <a href="{{ $intakes->url($last) }}" class="ci-page-btn">{{ $last }}</a>
                @endif

                {{-- Next --}}
                <a href="{{ $intakes->nextPageUrl() ?? '#' }}"
                   class="ci-page-btn {{ !$intakes->hasMorePages() ? 'disabled' : '' }}">
                    <span class="material-icons" style="font-size:15px;">chevron_right</span>
                </a>
            </div>
        </div>
        @endif
    </div>
@endif
@endsection
