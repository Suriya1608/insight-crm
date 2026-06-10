@extends('layouts.app')

@section('page_title', 'Call Efficiency Report')

@section('content')
@php
$rp = Auth::user()->role === 'report_viewer' ? 'report_viewer' : 'admin';
$activeFilters = collect([
    ($filters['date_range'] ?? '30') !== '30'  ? 'Period: '.['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','quarter'=>'Quarter','year'=>'Year'][($filters['date_range'] ?? '30')] : null,
    ($filters['telecaller'] ?? 'all') !== 'all' ? 'Telecaller: '.($filterOptions['telecallers']->firstWhere('id', $filters['telecaller'])?->name ?? $filters['telecaller']) : null,
    ($filters['manager']    ?? 'all') !== 'all' ? 'Manager: '.($filterOptions['managers']->firstWhere('id', $filters['manager'])?->name    ?? $filters['manager'])    : null,
    ($filters['outcome']    ?? 'all') !== 'all' ? 'Outcome: '.$filters['outcome'] : null,
    ((int)($filters['min_calls'] ?? 0)) > 0     ? 'Min Calls: '.$filters['min_calls'] : null,
])->filter()->values();
@endphp

{{-- ═══════════════════════════════════════════════════════════════ STYLES --}}
<style>
/* ── Base card / layout ─────────────────────────────────── */
.ce-section { margin-bottom: 1.25rem; }
.ce-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 1px 8px rgba(15,23,42,.07);
    height: 100%;
}
.ce-card-title { font-size: .875rem; font-weight: 700; color: #0f172a; margin: 0 0 2px; }
.ce-card-sub   { font-size: .72rem;  color: #94a3b8;   margin: 0 0 14px; }

/* ── KPI cards ─────────────────────────────────────────── */
.ce-kpi {
    background: #fff;
    border-radius: 14px;
    padding: 18px 20px 16px;
    box-shadow: 0 1px 6px rgba(15,23,42,.07);
    position: relative;
    overflow: hidden;
    height: 100%;
    transition: transform .18s, box-shadow .18s;
}
.ce-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(15,23,42,.11); }
.ce-kpi::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
    background: var(--kpi-accent, #6366f1);
}
.ce-kpi-icon {
    width: 42px; height: 42px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 12px;
    background: var(--kpi-bg, rgba(99,102,241,.1));
}
.ce-kpi-icon .material-icons { font-size: 20px; color: var(--kpi-color, #6366f1); }
.ce-kpi-value { font-size: 1.8rem; font-weight: 800; color: var(--kpi-color, #0f172a); line-height: 1.1; }
.ce-kpi-label { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-top: 4px; }
.ce-kpi-sub   { font-size: .73rem; color: #64748b; margin-top: 3px; }

/* ── Filter bar ─────────────────────────────────────────── */
.rpt-filter-wrap { background: #fff; border-radius: 16px; box-shadow: 0 1px 8px rgba(15,23,42,.08); overflow: hidden; margin-bottom: 1.25rem; }
.rpt-filter-head { background: linear-gradient(135deg,#6366f1,#4f46e5); padding: 11px 20px; display: flex; align-items: center; justify-content: space-between; }
.rpt-filter-head-title { display: flex; align-items: center; gap: 7px; color: #fff; font-size: .82rem; font-weight: 700; letter-spacing: .3px; }
.rpt-filter-head-title .material-icons { font-size: 17px; opacity: .9; }
.rpt-filter-body { padding: 16px 20px 18px; }
.rpt-filter-field { display: flex; flex-direction: column; gap: 5px; min-width: 130px; flex: 1; }
.rpt-filter-lbl { font-size: .68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 5px; }
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
.rpt-filter-div { width: 1px; background: #e2e8f0; align-self: stretch; margin: 0 4px; flex-shrink: 0; }
.rpt-btn-apply { background: linear-gradient(135deg,#6366f1,#4f46e5); border: none; color: #fff; border-radius: 9px; padding: 8px 18px; font-size: .82rem; font-weight: 700; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(99,102,241,.35); transition: transform .15s,box-shadow .15s; white-space: nowrap; cursor: pointer; }
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

/* ── Performance table ──────────────────────────────────── */
.ce-table thead th { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 9px 10px; white-space: nowrap; }
.ce-table tbody td { padding: 10px 10px; border-bottom: 1px solid #f8fafc; vertical-align: middle; font-size: .825rem; }
.ce-table tbody tr:hover { background: #fafbff; }
.ce-table tbody tr:last-child td { border-bottom: none; }

.grade-pill { width: 28px; height: 28px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 800; }
.g-A { background: #d1fae5; color: #065f46; }
.g-B { background: #dbeafe; color: #1e40af; }
.g-C { background: #fef3c7; color: #92400e; }
.g-D { background: #fee2e2; color: #991b1b; }

.progress-bar-track { background: #f1f5f9; border-radius: 4px; height: 6px; width: 100%; min-width: 60px; }
.progress-bar-fill  { height: 6px; border-radius: 4px; transition: width .6s ease; }

/* ── Insight strip ──────────────────────────────────────── */
.ce-insight {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 16px; border-radius: 12px; margin-bottom: 10px;
    border-left: 3px solid;
}
.ce-insight-warning { background: #fffbeb; border-color: #f59e0b; }
.ce-insight-danger  { background: #fff5f5; border-color: #ef4444; }
.ce-insight-success { background: #f0fdf4; border-color: #10b981; }
.ce-insight-info    { background: #eff6ff; border-color: #6366f1; }
.ce-insight .material-icons { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
.ce-insight-warning .material-icons { color: #f59e0b; }
.ce-insight-danger  .material-icons { color: #ef4444; }
.ce-insight-success .material-icons { color: #10b981; }
.ce-insight-info    .material-icons { color: #6366f1; }

/* ── Dist ring ──────────────────────────────────────────── */
.dist-ring-wrap { display: flex; flex-direction: column; gap: 10px; }
.dist-bar-row { display: flex; align-items: center; gap: 10px; }
.dist-bar-label { font-size: .75rem; font-weight: 600; color: #64748b; width: 70px; flex-shrink: 0; }
.dist-bar-track { flex: 1; height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden; }
.dist-bar-fill  { height: 10px; border-radius: 5px; transition: width 1s ease; }
.dist-bar-count { font-size: .75rem; font-weight: 700; width: 30px; text-align: right; flex-shrink: 0; }

/* ── Hourly heatmap labels ──────────────────────────────── */
.hourly-bar-wrap { display: flex; align-items: flex-end; gap: 4px; height: 100px; padding-bottom: 20px; position: relative; }
.hourly-bar-col  { display: flex; flex-direction: column; align-items: center; flex: 1; gap: 2px; }
.hourly-bar-inner{ width: 100%; border-radius: 4px 4px 0 0; transition: height .6s ease; cursor: pointer; position: relative; }
.hourly-bar-inner:hover::after {
    content: attr(data-tip);
    position: absolute; bottom: calc(100% + 4px); left: 50%; transform: translateX(-50%);
    background: #0f172a; color: #fff; font-size: 10px; font-weight: 600;
    padding: 3px 7px; border-radius: 6px; white-space: nowrap; pointer-events: none; z-index: 10;
}
.hourly-bar-lbl { font-size: 9px; color: #94a3b8; text-align: center; width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Status/Outcome pill rows ───────────────────────────── */
.so-pill-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.so-pill-label { font-size: .75rem; font-weight: 600; color: #475569; flex: 1; text-transform: capitalize; }
.so-pill-bar-track { width: 100px; height: 7px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
.so-pill-bar-fill  { height: 7px; border-radius: 4px; transition: width .7s ease; }
.so-pill-count { font-size: .72rem; font-weight: 700; min-width: 28px; text-align: right; }

/* ── Summary ribbon ─────────────────────────────────────── */
.ce-ribbon {
    background: linear-gradient(135deg, #1e3a6e 0%, #0f172a 100%);
    border-radius: 14px; padding: 16px 22px; margin-bottom: 1.25rem;
    display: flex; flex-wrap: wrap; align-items: center; gap: 20px;
    box-shadow: 0 4px 16px rgba(15,23,42,.35);
}
.ce-ribbon-stat { display: flex; flex-direction: column; align-items: center; }
.ce-ribbon-val  { font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1; }
.ce-ribbon-lbl  { font-size: .65rem; font-weight: 600; color: rgba(255,255,255,.65); text-transform: uppercase; letter-spacing: .6px; margin-top: 3px; }
.ce-ribbon-div  { width: 1px; height: 36px; background: rgba(255,255,255,.2); flex-shrink: 0; }
</style>

{{-- ═══════════════════════════════════════════════════════════════ FILTER BAR --}}
<div class="rpt-filter-wrap ce-section">
    <div class="rpt-filter-head">
        <div class="rpt-filter-head-title">
            <span class="material-icons">tune</span>
            Advanced Filters — Call Efficiency
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($activeFilters->count())
            <span style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:rgba(255,255,255,.85);background:rgba(255,255,255,.15);padding:3px 10px;border-radius:20px">
                <span class="material-icons" style="font-size:13px">filter_alt</span>
                {{ $activeFilters->count() }} active
            </span>
            @endif
            <span style="font-size:.72rem;color:rgba(255,255,255,.5)">{{ $summary['total_calls'] }} total calls</span>
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

                {{-- Call Outcome --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">task_alt</span> Call Outcome</label>
                    <select name="outcome" class="rpt-filter-sel">
                        <option value="all">All Outcomes</option>
                        @foreach (($filterOptions['outcomes'] ?? collect()) as $out)
                            <option value="{{ $out }}" {{ ($filters['outcome'] ?? 'all') === $out ? 'selected' : '' }}>{{ ucfirst(str_replace('-',' ',$out)) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Min Calls --}}
                <div class="rpt-filter-field" style="max-width:120px">
                    <label class="rpt-filter-lbl"><span class="material-icons">filter_1</span> Min Calls</label>
                    <input type="number" name="min_calls" min="0" placeholder="0"
                        value="{{ $filters['min_calls'] ?? '0' }}"
                        class="rpt-filter-sel" style="background-image:none;padding-right:12px">
                </div>

                <div class="rpt-filter-div d-none d-xl-block"></div>

                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <button type="submit" class="rpt-btn-apply">
                        <span class="material-icons">search</span> Apply
                    </button>
                    <a href="{{ route($rp . '.reports.call-efficiency') }}" class="rpt-btn-reset">
                        <span class="material-icons">refresh</span> Reset
                    </a>
                    <div class="dropdown">
                        <button class="rpt-btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">file_download</span> Download
                        </button>
                        <ul class="dropdown-menu rpt-dl-menu">
                            <li><a class="dropdown-item rpt-dl-excel" href="{{ route($rp . '.reports.export', ['report' => 'call-efficiency', 'format' => 'excel'] + request()->query()) }}">
                                <span class="material-icons">table_view</span> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item rpt-dl-pdf" href="{{ route($rp . '.reports.export', ['report' => 'call-efficiency', 'format' => 'pdf'] + request()->query()) }}" target="_blank">
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

{{-- ═══════════════════════════════════════════════════════════════ SUMMARY RIBBON --}}
<div class="ce-ribbon ce-section">
    <div class="ce-ribbon-stat">
        <div class="ce-ribbon-val">{{ number_format($summary['total_calls']) }}</div>
        <div class="ce-ribbon-lbl">Total Calls</div>
    </div>
    <div class="ce-ribbon-div"></div>
    <div class="ce-ribbon-stat">
        <div class="ce-ribbon-val" style="color:#86efac">{{ number_format($summary['total_completed']) }}</div>
        <div class="ce-ribbon-lbl">Completed</div>
    </div>
    <div class="ce-ribbon-div"></div>
    <div class="ce-ribbon-stat">
        <div class="ce-ribbon-val" style="color:#fca5a5">{{ number_format($summary['total_missed']) }}</div>
        <div class="ce-ribbon-lbl">Missed</div>
    </div>
    <div class="ce-ribbon-div"></div>
    <div class="ce-ribbon-stat">
        <div class="ce-ribbon-val">{{ $summary['overall_rate'] }}%</div>
        <div class="ce-ribbon-lbl">Completion Rate</div>
    </div>
    <div class="ce-ribbon-div"></div>
    <div class="ce-ribbon-stat">
        <div class="ce-ribbon-val">{{ $summary['total_talk_fmt'] }}</div>
        <div class="ce-ribbon-lbl">Total Talk Time</div>
    </div>
    <div class="ce-ribbon-div"></div>
    <div class="ce-ribbon-stat">
        <div class="ce-ribbon-val">{{ $summary['avg_dur_fmt'] }}</div>
        <div class="ce-ribbon-lbl">Avg Duration</div>
    </div>
    <div class="ce-ribbon-div d-none d-lg-block"></div>
    <div class="ce-ribbon-stat d-none d-lg-flex">
        <div class="ce-ribbon-val" style="color:#fde68a">{{ $summary['top_performer'] }}</div>
        <div class="ce-ribbon-lbl">Top Performer · {{ $summary['top_rate'] }}%</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ KPI ROW --}}
<div class="row g-3 ce-section">
    {{-- Total Calls --}}
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="ce-kpi" style="--kpi-accent:#6366f1;--kpi-color:#6366f1;--kpi-bg:rgba(99,102,241,.1)">
            <div class="ce-kpi-icon"><span class="material-icons">call</span></div>
            <div class="ce-kpi-value">{{ number_format($summary['total_calls']) }}</div>
            <div class="ce-kpi-label">Total Calls</div>
            <div class="ce-kpi-sub">{{ $summary['telecaller_count'] }} agents</div>
        </div>
    </div>
    {{-- Completed --}}
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="ce-kpi" style="--kpi-accent:#10b981;--kpi-color:#10b981;--kpi-bg:rgba(16,185,129,.1)">
            <div class="ce-kpi-icon"><span class="material-icons">call_received</span></div>
            <div class="ce-kpi-value">{{ number_format($summary['total_completed']) }}</div>
            <div class="ce-kpi-label">Completed</div>
            <div class="ce-kpi-sub">{{ $summary['overall_rate'] }}% completion</div>
        </div>
    </div>
    {{-- Missed --}}
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="ce-kpi" style="--kpi-accent:#ef4444;--kpi-color:#ef4444;--kpi-bg:rgba(239,68,68,.1)">
            <div class="ce-kpi-icon"><span class="material-icons">call_missed</span></div>
            <div class="ce-kpi-value">{{ number_format($summary['total_missed']) }}</div>
            <div class="ce-kpi-label">Missed / Failed</div>
            <div class="ce-kpi-sub">
                @php $missedPct = $summary['total_calls'] > 0 ? round(($summary['total_missed']/$summary['total_calls'])*100,1) : 0; @endphp
                {{ $missedPct }}% miss rate
            </div>
        </div>
    </div>
    {{-- Avg Duration --}}
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="ce-kpi" style="--kpi-accent:#f59e0b;--kpi-color:#f59e0b;--kpi-bg:rgba(245,158,11,.1)">
            <div class="ce-kpi-icon"><span class="material-icons">timer</span></div>
            <div class="ce-kpi-value" style="font-size:1.35rem">{{ $summary['avg_dur_fmt'] }}</div>
            <div class="ce-kpi-label">Avg Duration</div>
            <div class="ce-kpi-sub">per completed call</div>
        </div>
    </div>
    {{-- Talk Time --}}
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="ce-kpi" style="--kpi-accent:#8b5cf6;--kpi-color:#8b5cf6;--kpi-bg:rgba(139,92,246,.1)">
            <div class="ce-kpi-icon"><span class="material-icons">headset</span></div>
            <div class="ce-kpi-value" style="font-size:1.35rem">{{ $summary['total_talk_fmt'] }}</div>
            <div class="ce-kpi-label">Total Talk Time</div>
            <div class="ce-kpi-sub">{{ number_format($summary['total_talk_mins']) }} minutes</div>
        </div>
    </div>
    {{-- Top Performer --}}
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="ce-kpi" style="--kpi-accent:#06b6d4;--kpi-color:#06b6d4;--kpi-bg:rgba(6,182,212,.1)">
            <div class="ce-kpi-icon"><span class="material-icons">workspace_premium</span></div>
            <div class="ce-kpi-value" style="font-size:1rem;padding-top:4px">{{ $summary['top_performer'] }}</div>
            <div class="ce-kpi-label">Top Performer</div>
            <div class="ce-kpi-sub"><strong style="color:#10b981">{{ $summary['top_rate'] }}%</strong> completion</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ CHARTS ROW 1 --}}
<div class="row g-3 ce-section">
    {{-- Completion % bar chart --}}
    <div class="col-lg-8">
        <div class="ce-card">
            <h3 class="ce-card-title">Completion % by Telecaller</h3>
            <p class="ce-card-sub">Ranked high → low · green ≥70% · amber 40–69% · red &lt;40%</p>
            <div style="height:260px"><canvas id="ceCompletionChart"></canvas></div>
        </div>
    </div>
    {{-- Performance Distribution --}}
    <div class="col-lg-4">
        <div class="ce-card">
            <h3 class="ce-card-title">Performance Distribution</h3>
            <p class="ce-card-sub">Agent tier breakdown</p>
            <div style="height:180px;margin-bottom:16px"><canvas id="ceDistChart"></canvas></div>
            <div class="dist-ring-wrap">
                <div class="dist-bar-row">
                    <span class="dist-bar-label" style="color:#10b981">High ≥70%</span>
                    <div class="dist-bar-track"><div class="dist-bar-fill" style="background:#10b981;width:{{ $summary['telecaller_count']>0?round(($perfDist['high']/$summary['telecaller_count'])*100):0 }}%"></div></div>
                    <span class="dist-bar-count" style="color:#10b981">{{ $perfDist['high'] }}</span>
                </div>
                <div class="dist-bar-row">
                    <span class="dist-bar-label" style="color:#f59e0b">Mid 40-69%</span>
                    <div class="dist-bar-track"><div class="dist-bar-fill" style="background:#f59e0b;width:{{ $summary['telecaller_count']>0?round(($perfDist['average']/$summary['telecaller_count'])*100):0 }}%"></div></div>
                    <span class="dist-bar-count" style="color:#f59e0b">{{ $perfDist['average'] }}</span>
                </div>
                <div class="dist-bar-row">
                    <span class="dist-bar-label" style="color:#ef4444">Low &lt;40%</span>
                    <div class="dist-bar-track"><div class="dist-bar-fill" style="background:#ef4444;width:{{ $summary['telecaller_count']>0?round(($perfDist['low']/$summary['telecaller_count'])*100):0 }}%"></div></div>
                    <span class="dist-bar-count" style="color:#ef4444">{{ $perfDist['low'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ CHARTS ROW 2 --}}
<div class="row g-3 ce-section">
    {{-- Daily Trend --}}
    <div class="col-lg-8">
        <div class="ce-card">
            <h3 class="ce-card-title">Daily Call Trend</h3>
            <p class="ce-card-sub">Total vs Completed calls per day over the selected period</p>
            <div style="height:240px"><canvas id="ceTrendChart"></canvas></div>
        </div>
    </div>
    {{-- Call Volume vs Completion bar --}}
    <div class="col-lg-4">
        <div class="ce-card">
            <h3 class="ce-card-title">Calls vs Completed</h3>
            <p class="ce-card-sub">Volume stacked per telecaller</p>
            <div style="height:240px"><canvas id="ceStackedChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ CHARTS ROW 3 --}}
<div class="row g-3 ce-section">
    {{-- Hourly Heatmap --}}
    <div class="col-lg-5">
        <div class="ce-card">
            <div class="d-flex align-items-start justify-content-between mb-1">
                <div>
                    <h3 class="ce-card-title">Peak Hour Analysis</h3>
                    <p class="ce-card-sub mb-0">Call volume by hour (08:00–20:00) — hover for details</p>
                </div>
                @php $peakIdx = !empty($hourlyTotal) ? array_search(max($hourlyTotal), $hourlyTotal) : 0; @endphp
                <span style="background:#eef2ff;color:#6366f1;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #c7d2fe;white-space:nowrap">
                    Peak: {{ $hourlyLabels[$peakIdx] ?? '—' }}
                </span>
            </div>
            <div style="margin-top:12px">
                @php $maxH = max(array_merge($hourlyTotal,[1])); @endphp
                <div class="hourly-bar-wrap">
                    @foreach($hourlyLabels as $hi => $hlbl)
                    @php
                        $ht = $hourlyTotal[$hi] ?? 0;
                        $hc = $hourlyCompleted[$hi] ?? 0;
                        $hPct = $maxH > 0 ? round(($ht/$maxH)*80) : 0;
                        $hRate = $ht > 0 ? round(($hc/$ht)*100) : 0;
                        $hColor = $hRate >= 70 ? '#10b981' : ($hRate >= 40 ? '#f59e0b' : ($ht > 0 ? '#ef4444' : '#e2e8f0'));
                    @endphp
                    <div class="hourly-bar-col">
                        <div class="hourly-bar-inner"
                             style="height:{{ max($hPct,3) }}px;background:{{ $hColor }}22;border:1px solid {{ $hColor }}55;position:relative;"
                             data-tip="{{ $hlbl }}: {{ $ht }} calls · {{ $hRate }}% done">
                            <div style="position:absolute;bottom:0;left:0;right:0;height:{{ $ht>0?max(round(($hc/$ht)*100),3):0 }}%;background:{{ $hColor }};border-radius:3px 3px 0 0;transition:height .6s ease"></div>
                        </div>
                        <span class="hourly-bar-lbl">{{ substr($hlbl,0,2) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="d-flex gap-3 mt-2" style="font-size:.7rem;color:#94a3b8">
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:#6366f122;border:1px solid #6366f155;margin-right:3px"></span>Volume</span>
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:#10b981;margin-right:3px"></span>Completed fill</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Status + Outcome side by side --}}
    <div class="col-lg-4">
        <div class="ce-card">
            <h3 class="ce-card-title">Call Status Breakdown</h3>
            <p class="ce-card-sub">Distribution by status code</p>
            @php
            $statusColors = ['completed'=>'#10b981','no-answer'=>'#ef4444','busy'=>'#f59e0b','failed'=>'#dc2626','canceled'=>'#94a3b8','missed'=>'#f97316','initiated'=>'#06b6d4'];
            $statusTotal = $statusBreakdown->sum('total');
            @endphp
            <div style="margin-top:4px">
                @forelse($statusBreakdown as $sb)
                @php $sc = $statusColors[$sb->status] ?? '#6366f1'; $sp = $statusTotal > 0 ? round(($sb->total/$statusTotal)*100) : 0; @endphp
                <div class="so-pill-row">
                    <div style="width:9px;height:9px;border-radius:50%;background:{{ $sc }};flex-shrink:0"></div>
                    <span class="so-pill-label">{{ str_replace('-',' ',$sb->status) }}</span>
                    <div class="so-pill-bar-track"><div class="so-pill-bar-fill" style="background:{{ $sc }};width:{{ $sp }}%"></div></div>
                    <span class="so-pill-count" style="color:{{ $sc }}">{{ $sb->total }}</span>
                </div>
                @empty
                <p class="text-muted" style="font-size:.8rem">No data</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="ce-card">
            <h3 class="ce-card-title">Call Outcomes</h3>
            <p class="ce-card-sub">Recorded outcome distribution</p>
            @php $outcomeTotal = $outcomeBreakdown->sum('total'); @endphp
            @if($outcomeBreakdown->count())
            <div style="height:160px;margin-bottom:10px"><canvas id="ceOutcomeChart"></canvas></div>
            @else
            <div style="text-align:center;color:#94a3b8;padding:30px 0;font-size:.8rem">
                <span class="material-icons" style="font-size:32px;display:block;margin-bottom:6px">summarize</span>
                No outcome data recorded
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ AI INSIGHTS --}}
@if(count($insights))
<div class="ce-card ce-section">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-icons" style="color:#6366f1;font-size:20px">auto_awesome</span>
        <h3 class="ce-card-title mb-0">Smart Insights</h3>
        <span style="font-size:.68rem;background:#eef2ff;color:#6366f1;padding:2px 8px;border-radius:20px;font-weight:700;border:1px solid #c7d2fe">{{ count($insights) }} alert{{ count($insights)>1?'s':'' }}</span>
    </div>
    @foreach($insights as $ins)
    <div class="ce-insight ce-insight-{{ $ins['type'] }}">
        <span class="material-icons">{{ $ins['icon'] }}</span>
        <span style="font-size:.82rem;font-weight:500;color:#1e293b;line-height:1.5">{{ $ins['text'] }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ DETAIL TABLE --}}
<div class="custom-table ce-section">
    <div class="table-header">
        <h3>
            <span class="material-icons me-2" style="vertical-align:-5px;font-size:20px">leaderboard</span>
            Call Efficiency Breakdown
        </h3>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark" style="font-size:11px">{{ $rows->count() }} agents</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 ce-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Telecaller</th>
                    <th>Grade</th>
                    <th>Total Calls</th>
                    <th>Completed</th>
                    <th>Missed</th>
                    <th>Avg Duration</th>
                    <th>Talk Time</th>
                    <th style="min-width:140px">Completion %</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $row)
                @php
                    $rate = $row['rate'];
                    $barColor = $rate >= 70 ? '#10b981' : ($rate >= 40 ? '#f59e0b' : '#ef4444');
                @endphp
                <tr>
                    <td style="color:#94a3b8;font-weight:700;font-size:.8rem">#{{ $i+1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $aColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
                                $aColor  = $aColors[crc32($row['name']) % count($aColors)];
                                $initials = mb_strtoupper(mb_substr($row['name'],0,1)) . (str_contains($row['name'],' ') ? mb_strtoupper(mb_substr(strrchr($row['name'],' '),1,1)) : '');
                            @endphp
                            <div style="width:32px;height:32px;border-radius:50%;background:{{ $aColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.7rem;font-weight:800;color:#fff">{{ $initials }}</div>
                            <span style="font-weight:600;color:#0f172a">{{ $row['name'] }}</span>
                        </div>
                    </td>
                    <td><span class="grade-pill g-{{ $row['grade'] }}">{{ $row['grade'] }}</span></td>
                    <td><strong>{{ number_format($row['total']) }}</strong></td>
                    <td style="color:#10b981;font-weight:600">{{ number_format($row['completed']) }}</td>
                    <td style="color:#ef4444;font-weight:600">{{ number_format($row['missed']) }}</td>
                    <td style="font-variant-numeric:tabular-nums;color:#64748b">{{ $row['avg_fmt'] }}</td>
                    <td style="color:#8b5cf6;font-weight:600">{{ $row['talk_fmt'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="background:{{ $barColor }};width:{{ min($rate,100) }}%"></div>
                            </div>
                            <span style="font-size:.8rem;font-weight:700;color:{{ $barColor }};min-width:42px">{{ number_format($rate,1) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <span class="material-icons d-block mb-2" style="font-size:36px;color:#e2e8f0">call_missed</span>
                        No call records found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ CHARTS SCRIPT --}}
<script>
(function() {
    const rows       = @json($rows->values());
    const daily      = @json($dailyTrend->values());
    const hLabels    = @json($hourlyLabels);
    const hTotal     = @json($hourlyTotal);
    const hComp      = @json($hourlyCompleted);
    const outcomes   = @json($outcomeBreakdown->values());
    const distData   = @json($perfDist);

    const GREEN  = '#10b981', AMBER = '#f59e0b', RED = '#ef4444', INDIGO = '#6366f1', PURPLE = '#8b5cf6', CYAN = '#06b6d4';
    const palette = [GREEN, AMBER, RED, INDIGO, PURPLE, CYAN, '#F97316', '#EC4899'];

    function barColors(values) {
        return values.map(v => v >= 70 ? GREEN : v >= 40 ? AMBER : RED);
    }

    function initCharts() {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

        // ── 1. Completion % bar chart
        const ctxComp = document.getElementById('ceCompletionChart');
        if (ctxComp && rows.length) {
            const names  = rows.map(r => r.name);
            const rates  = rows.map(r => parseFloat(r.rate));
            new Chart(ctxComp, {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'Completion %',
                        data: rates,
                        backgroundColor: barColors(rates),
                        borderRadius: 7,
                        borderSkipped: false,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y.toFixed(1)}%  (${rows[ctx.dataIndex].completed}/${rows[ctx.dataIndex].total})`,
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' }, ticks: { callback: v => v + '%', color: '#94a3b8', font: { size: 11 } } },
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } }
                    }
                }
            });
        }

        // ── 2. Performance distribution donut
        const ctxDist = document.getElementById('ceDistChart');
        if (ctxDist) {
            new Chart(ctxDist, {
                type: 'doughnut',
                data: {
                    labels: ['High (≥70%)', 'Mid (40-69%)', 'Low (<40%)'],
                    datasets: [{ data: [distData.high, distData.average, distData.low], backgroundColor: [GREEN, AMBER, RED], borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } }
                }
            });
        }

        // ── 3. Daily trend line chart
        const ctxTrend = document.getElementById('ceTrendChart');
        if (ctxTrend && daily.length) {
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: daily.map(d => d.day),
                    datasets: [
                        { label: 'Total Calls', data: daily.map(d => d.total),     borderColor: INDIGO, backgroundColor: 'rgba(99,102,241,.1)',  fill: true, tension: .35, borderWidth: 2, pointRadius: 3 },
                        { label: 'Completed',   data: daily.map(d => d.completed), borderColor: GREEN,  backgroundColor: 'rgba(16,185,129,.08)', fill: true, tension: .35, borderWidth: 2, pointRadius: 3 },
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 }, maxTicksLimit: 10 } }
                    }
                }
            });
        }

        // ── 4. Stacked Calls vs Completed bar
        const ctxStack = document.getElementById('ceStackedChart');
        if (ctxStack && rows.length) {
            new Chart(ctxStack, {
                type: 'bar',
                data: {
                    labels: rows.map(r => r.name),
                    datasets: [
                        { label: 'Completed', data: rows.map(r => r.completed), backgroundColor: GREEN,  borderRadius: 4, stack: 'a' },
                        { label: 'Missed',    data: rows.map(r => r.missed),    backgroundColor: RED,    borderRadius: 0, stack: 'a' },
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                    scales: {
                        y: { beginAtZero: true, stacked: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                        x: { stacked: true, grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
                    }
                }
            });
        }

        // ── 5. Outcome donut
        const ctxOut = document.getElementById('ceOutcomeChart');
        if (ctxOut && outcomes.length) {
            new Chart(ctxOut, {
                type: 'doughnut',
                data: {
                    labels: outcomes.map(o => o.outcome.replace(/-/g,' ')),
                    datasets: [{ data: outcomes.map(o => o.total), backgroundColor: palette, borderWidth: 2, borderColor: '#fff', hoverOffset: 5 }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 10 } } } }
                }
            });
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
