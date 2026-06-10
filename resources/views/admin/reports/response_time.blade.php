@extends('layouts.app')

@section('page_title', 'Lead Response Time Report')

@section('content')
@php
$rp = Auth::user()->role === 'report_viewer' ? 'report_viewer' : 'admin';
$periodMap   = ['7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','quarter'=>'Quarter','year'=>'Year'];
$activeFilters = collect([
    ($filters['date_range'] ?? '30') !== '30'   ? 'Period: '.($periodMap[$filters['date_range']] ?? $filters['date_range']) : null,
    ($filters['source']     ?? 'all') !== 'all' ? 'Source: '.$filters['source']     : null,
    ($filters['telecaller'] ?? 'all') !== 'all' ? 'Telecaller: '.($filterOptions['telecallers']->firstWhere('id',$filters['telecaller'])?->name ?? $filters['telecaller']) : null,
    ($filters['manager']    ?? 'all') !== 'all' ? 'Manager: '.($filterOptions['managers']->firstWhere('id',$filters['manager'])?->name ?? $filters['manager'])             : null,
    ($filters['rt_bucket']  ?? 'all') !== 'all' ? 'Bucket: '.['fast'=>'Fast (<'.$slaMinutes.'m)','slow'=>'Slow (>'.$slaMinutes.'m)','none'=>'No Response'][$filters['rt_bucket']] : null,
    ($filters['sort']       ?? 'newest') !== 'newest' ? 'Sort: '.['asc'=>'Fastest first','desc'=>'Slowest first'][$filters['sort']] : null,
])->filter()->values();
@endphp

{{-- ══════════════════════════════════════════════════════════════ STYLES --}}
<style>
/* ── Base cards ──────────────────────────────────── */
.rt-section { margin-bottom: 1.25rem; }
.rt-card {
    background: #fff; border-radius: 16px; padding: 20px 22px;
    box-shadow: 0 1px 8px rgba(15,23,42,.07); height: 100%;
}
.rt-card-title { font-size: .875rem; font-weight: 700; color: #0f172a; margin: 0 0 2px; }
.rt-card-sub   { font-size: .72rem;  color: #94a3b8;   margin: 0 0 14px; }

/* ── Filter bar ──────────────────────────────────── */
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

/* ── KPI cards ───────────────────────────────────── */
.rt-kpi {
    background: #fff; border-radius: 14px; padding: 18px 20px 16px;
    box-shadow: 0 1px 6px rgba(15,23,42,.07); position: relative; overflow: hidden;
    height: 100%; transition: transform .18s, box-shadow .18s;
}
.rt-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(15,23,42,.11); }
.rt-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:14px 14px 0 0; background: var(--ka,#6366f1); }
.rt-kpi-icon { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; margin-bottom:12px; }
.rt-kpi-icon .material-icons { font-size: 20px; }
.rt-kpi-value { font-size: 1.75rem; font-weight: 800; line-height: 1.1; }
.rt-kpi-label { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin-top: 4px; }
.rt-kpi-sub   { font-size: .73rem; color: #64748b; margin-top: 3px; }

/* ── Summary ribbon ──────────────────────────────── */
.rt-ribbon {
    background: linear-gradient(135deg,#1e3a6e,#0f172a); border-radius: 14px;
    padding: 16px 22px; margin-bottom: 1.25rem;
    display: flex; flex-wrap: wrap; align-items: center; gap: 20px;
    box-shadow: 0 4px 16px rgba(15,23,42,.35);
}
.rt-ribbon-stat { display: flex; flex-direction: column; align-items: center; }
.rt-ribbon-val  { font-size: 1.4rem; font-weight: 800; color: #fff; line-height: 1; }
.rt-ribbon-lbl  { font-size: .63rem; font-weight: 600; color: rgba(255,255,255,.65); text-transform: uppercase; letter-spacing: .6px; margin-top: 3px; }
.rt-ribbon-div  { width: 1px; height: 36px; background: rgba(255,255,255,.2); flex-shrink: 0; }

/* ── SLA gauge ───────────────────────────────────── */
.sla-gauge-wrap { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 10px 0; }
.sla-gauge-ring { position: relative; width: 160px; height: 160px; }
.sla-gauge-ring svg { transform: rotate(-90deg); }
.sla-gauge-ring .track { fill: none; stroke: #f1f5f9; stroke-width: 14; }
.sla-gauge-ring .fill  { fill: none; stroke-width: 14; stroke-linecap: round; transition: stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1); }
.sla-gauge-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.sla-gauge-pct   { font-size: 2rem; font-weight: 800; line-height: 1; }
.sla-gauge-tag   { font-size: .65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }

/* ── Bucket bar ──────────────────────────────────── */
.bucket-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.bucket-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.bucket-label { font-size: .75rem; font-weight: 600; color: #475569; width: 80px; flex-shrink: 0; }
.bucket-track { flex: 1; height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden; }
.bucket-fill  { height: 10px; border-radius: 5px; transition: width 1s ease; }
.bucket-count { font-size: .75rem; font-weight: 700; min-width: 28px; text-align: right; }

/* ── Telecaller breakdown table ─────────────────── */
.tc-mini-table thead th { font-size: .68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 8px 10px; white-space: nowrap; }
.tc-mini-table tbody td { padding: 9px 10px; border-bottom: 1px solid #f8fafc; vertical-align: middle; font-size: .82rem; }
.tc-mini-table tbody tr:last-child td { border-bottom: none; }

/* ── Response time badge ─────────────────────────── */
.rt-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .7rem; font-weight: 700; padding: 3px 9px;
    border-radius: 20px; white-space: nowrap;
}
.rt-badge-excellent { background: #d1fae5; color: #065f46; }
.rt-badge-good      { background: #cffafe; color: #155e75; }
.rt-badge-acceptable{ background: #fef3c7; color: #92400e; }
.rt-badge-slow      { background: #ffedd5; color: #9a3412; }
.rt-badge-critical  { background: #fee2e2; color: #991b1b; }
.rt-badge-none      { background: #f1f5f9; color: #64748b; }

/* ── Insight strip ───────────────────────────────── */
.rt-insight { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border-radius: 12px; margin-bottom: 10px; border-left: 3px solid; }
.rt-insight-warning { background: #fffbeb; border-color: #f59e0b; }
.rt-insight-danger  { background: #fff5f5; border-color: #ef4444; }
.rt-insight-success { background: #f0fdf4; border-color: #10b981; }
.rt-insight-info    { background: #eff6ff; border-color: #6366f1; }
.rt-insight .material-icons { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
.rt-insight-warning .material-icons { color: #f59e0b; }
.rt-insight-danger  .material-icons { color: #ef4444; }
.rt-insight-success .material-icons { color: #10b981; }
.rt-insight-info    .material-icons { color: #6366f1; }

/* ── Detail table ────────────────────────────────── */
.rt-detail-table thead th { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 9px 10px; white-space: nowrap; }
.rt-detail-table tbody td { padding: 10px 10px; border-bottom: 1px solid #f8fafc; vertical-align: middle; font-size: .825rem; }
.rt-detail-table tbody tr:hover { background: #fafbff; }
.rt-detail-table tbody tr:last-child td { border-bottom: none; }

/* ── Pagination ──────────────────────────────────── */
.rt-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 10px; }
.rt-pagination-info { font-size: .78rem; color: #64748b; font-weight: 500; }
.rt-page-links { display: flex; gap: 4px; flex-wrap: wrap; }
.rt-page-btn {
    min-width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 8px; font-size: .78rem; font-weight: 600; text-decoration: none;
    border: 1.5px solid #e2e8f0; color: #475569; background: #fff;
    transition: all .15s; padding: 0 8px;
}
.rt-page-btn:hover     { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
.rt-page-btn.active    { background: #6366f1; border-color: #6366f1; color: #fff; }
.rt-page-btn.disabled  { opacity: .4; pointer-events: none; }

/* ── SLA compliance progress bar ─────────────────── */
.sla-bar-track { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
.sla-bar-fill  { height: 8px; border-radius: 4px; transition: width .8s ease; }
</style>

{{-- ══════════════════════════════════════════════════════════════ FILTER BAR --}}
<div class="rpt-filter-wrap rt-section">
    <div class="rpt-filter-head">
        <div class="rpt-filter-head-title">
            <span class="material-icons">tune</span>
            Advanced Filters — Response Time
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($activeFilters->count())
            <span style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:rgba(255,255,255,.85);background:rgba(255,255,255,.15);padding:3px 10px;border-radius:20px">
                <span class="material-icons" style="font-size:13px">filter_alt</span>
                {{ $activeFilters->count() }} active
            </span>
            @endif
            <span style="font-size:.72rem;color:rgba(255,255,255,.5)">{{ $summary['total_leads'] }} leads · SLA {{ $slaMinutes }}min</span>
        </div>
    </div>

    <div class="rpt-filter-body">
        <form method="GET">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                {{-- Time Period --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">calendar_today</span> Time Period</label>
                    <select name="date_range" class="rpt-filter-sel">
                        <option value="7"       {{ ($filters['date_range']??'30')==='7'       ?'selected':'' }}>Last 7 Days</option>
                        <option value="30"      {{ ($filters['date_range']??'30')==='30'      ?'selected':'' }}>Last 30 Days</option>
                        <option value="90"      {{ ($filters['date_range']??'30')==='90'      ?'selected':'' }}>Last 90 Days</option>
                        <option value="quarter" {{ ($filters['date_range']??'30')==='quarter' ?'selected':'' }}>This Quarter</option>
                        <option value="year"    {{ ($filters['date_range']??'30')==='year'    ?'selected':'' }}>This Year</option>
                    </select>
                </div>

                {{-- Source --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">source</span> Source</label>
                    <select name="source" class="rpt-filter-sel">
                        <option value="all">All Sources</option>
                        @foreach(($filterOptions['sources']??collect()) as $src)
                            <option value="{{ $src }}" {{ ($filters['source']??'all')===$src?'selected':'' }}>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Telecaller --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">headset_mic</span> Telecaller</label>
                    <select name="telecaller" class="rpt-filter-sel">
                        <option value="all">All Telecallers</option>
                        @foreach(($filterOptions['telecallers']??collect()) as $tc)
                            <option value="{{ $tc->id }}" {{ (string)($filters['telecaller']??'all')===(string)$tc->id?'selected':'' }}>{{ $tc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Manager --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">manage_accounts</span> Manager</label>
                    <select name="manager" class="rpt-filter-sel">
                        <option value="all">All Managers</option>
                        @foreach(($filterOptions['managers']??collect()) as $mgr)
                            <option value="{{ $mgr->id }}" {{ (string)($filters['manager']??'all')===(string)$mgr->id?'selected':'' }}>{{ $mgr->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Response Bucket --}}
                <div class="rpt-filter-field">
                    <label class="rpt-filter-lbl"><span class="material-icons">speed</span> Response Bucket</label>
                    <select name="rt_bucket" class="rpt-filter-sel">
                        <option value="all"  {{ ($filters['rt_bucket']??'all')==='all'  ?'selected':'' }}>All Responses</option>
                        <option value="fast" {{ ($filters['rt_bucket']??'all')==='fast' ?'selected':'' }}>Fast (&lt;{{ $slaMinutes }}min SLA)</option>
                        <option value="slow" {{ ($filters['rt_bucket']??'all')==='slow' ?'selected':'' }}>Slow (&gt;{{ $slaMinutes }}min)</option>
                        <option value="none" {{ ($filters['rt_bucket']??'all')==='none' ?'selected':'' }}>No Response</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="rpt-filter-field" style="max-width:160px">
                    <label class="rpt-filter-lbl"><span class="material-icons">sort</span> Sort By</label>
                    <select name="sort" class="rpt-filter-sel">
                        <option value="newest" {{ ($filters['sort']??'newest')==='newest' ?'selected':'' }}>Newest First</option>
                        <option value="asc"    {{ ($filters['sort']??'newest')==='asc'    ?'selected':'' }}>Fastest First</option>
                        <option value="desc"   {{ ($filters['sort']??'newest')==='desc'   ?'selected':'' }}>Slowest First</option>
                    </select>
                </div>

                <div class="rpt-filter-div d-none d-xxl-block"></div>

                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <button type="submit" class="rpt-btn-apply">
                        <span class="material-icons">search</span> Apply
                    </button>
                    <a href="{{ route($rp.'.reports.response-time') }}" class="rpt-btn-reset">
                        <span class="material-icons">refresh</span> Reset
                    </a>
                    <div class="dropdown">
                        <button class="rpt-btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">file_download</span> Download
                        </button>
                        <ul class="dropdown-menu rpt-dl-menu">
                            <li><a class="dropdown-item rpt-dl-excel" href="{{ route($rp.'.reports.export', ['report'=>'response-time','format'=>'excel']+request()->query()) }}">
                                <span class="material-icons">table_view</span> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item rpt-dl-pdf" href="{{ route($rp.'.reports.export', ['report'=>'response-time','format'=>'pdf']+request()->query()) }}" target="_blank">
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

{{-- ══════════════════════════════════════════════════════════════ SUMMARY RIBBON --}}
<div class="rt-ribbon rt-section">
    <div class="rt-ribbon-stat">
        <div class="rt-ribbon-val">{{ number_format($summary['total_leads']) }}</div>
        <div class="rt-ribbon-lbl">Total Leads</div>
    </div>
    <div class="rt-ribbon-div"></div>
    <div class="rt-ribbon-stat">
        <div class="rt-ribbon-val" style="color:#86efac">{{ number_format($summary['responded']) }}</div>
        <div class="rt-ribbon-lbl">Responded</div>
    </div>
    <div class="rt-ribbon-div"></div>
    <div class="rt-ribbon-stat">
        <div class="rt-ribbon-val" style="color:#fca5a5">{{ number_format($summary['never_responded']) }}</div>
        <div class="rt-ribbon-lbl">No Response</div>
    </div>
    <div class="rt-ribbon-div"></div>
    <div class="rt-ribbon-stat">
        <div class="rt-ribbon-val">{{ $summary['sla_compliance'] }}%</div>
        <div class="rt-ribbon-lbl">SLA Compliance</div>
    </div>
    <div class="rt-ribbon-div"></div>
    <div class="rt-ribbon-stat">
        <div class="rt-ribbon-val">{{ $summary['avg_fmt'] }}</div>
        <div class="rt-ribbon-lbl">Avg Response</div>
    </div>
    <div class="rt-ribbon-div"></div>
    <div class="rt-ribbon-stat">
        <div class="rt-ribbon-val">{{ $summary['median_fmt'] }}</div>
        <div class="rt-ribbon-lbl">Median Response</div>
    </div>
    <div class="rt-ribbon-div d-none d-lg-block"></div>
    <div class="rt-ribbon-stat d-none d-lg-flex">
        <div class="rt-ribbon-val" style="color:#fde68a">{{ $summary['fastest_fmt'] }}</div>
        <div class="rt-ribbon-lbl">Fastest Response</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ KPI CARDS --}}
<div class="row g-3 rt-section">
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="rt-kpi" style="--ka:#6366f1">
            <div class="rt-kpi-icon" style="background:#6366f118"><span class="material-icons" style="color:#6366f1">groups</span></div>
            <div class="rt-kpi-value" style="color:#6366f1">{{ number_format($summary['total_leads']) }}</div>
            <div class="rt-kpi-label">Total Leads</div>
            <div class="rt-kpi-sub">{{ $summary['response_rate'] }}% response rate</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="rt-kpi" style="--ka:#10b981">
            <div class="rt-kpi-icon" style="background:#10b98118"><span class="material-icons" style="color:#10b981">mark_chat_read</span></div>
            <div class="rt-kpi-value" style="color:#10b981">{{ number_format($summary['responded']) }}</div>
            <div class="rt-kpi-label">Responded</div>
            <div class="rt-kpi-sub">{{ $summary['within_sla'] }} within SLA</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="rt-kpi" style="--ka:#ef4444">
            <div class="rt-kpi-icon" style="background:#ef444418"><span class="material-icons" style="color:#ef4444">phone_missed</span></div>
            <div class="rt-kpi-value" style="color:#ef4444">{{ number_format($summary['never_responded']) }}</div>
            <div class="rt-kpi-label">No Response</div>
            <div class="rt-kpi-sub">leads never contacted</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        @php $slaColor = $summary['sla_compliance'] >= 75 ? '#10b981' : ($summary['sla_compliance'] >= 50 ? '#f59e0b' : '#ef4444'); @endphp
        <div class="rt-kpi" style="--ka:{{ $slaColor }}">
            <div class="rt-kpi-icon" style="background:{{ $slaColor }}18"><span class="material-icons" style="color:{{ $slaColor }}">verified</span></div>
            <div class="rt-kpi-value" style="color:{{ $slaColor }}">{{ $summary['sla_compliance'] }}%</div>
            <div class="rt-kpi-label">SLA Compliance</div>
            <div class="rt-kpi-sub">within {{ $slaMinutes }}min threshold</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="rt-kpi" style="--ka:#f59e0b">
            <div class="rt-kpi-icon" style="background:#f59e0b18"><span class="material-icons" style="color:#f59e0b">timer</span></div>
            <div class="rt-kpi-value" style="font-size:1.3rem;padding-top:4px;color:#f59e0b">{{ $summary['avg_fmt'] }}</div>
            <div class="rt-kpi-label">Avg Response Time</div>
            <div class="rt-kpi-sub">Median: {{ $summary['median_fmt'] }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="rt-kpi" style="--ka:#8b5cf6">
            <div class="rt-kpi-icon" style="background:#8b5cf618"><span class="material-icons" style="color:#8b5cf6">rocket_launch</span></div>
            <div class="rt-kpi-value" style="font-size:1.3rem;padding-top:4px;color:#8b5cf6">{{ $summary['fastest_fmt'] }}</div>
            <div class="rt-kpi-label">Fastest Response</div>
            <div class="rt-kpi-sub">Slowest: {{ $summary['slowest_fmt'] }}</div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ ANALYTICS ROW 1 --}}
<div class="row g-3 rt-section">
    {{-- Bucket distribution --}}
    <div class="col-lg-5">
        <div class="rt-card">
            <h3 class="rt-card-title">Response Time Distribution</h3>
            <p class="rt-card-sub">How quickly leads are being contacted across all agents</p>
            @php $maxBucket = max(array_column($bucketDist,'count') ?: [1]); @endphp
            <div style="margin-top:6px">
                @foreach($bucketDist as $b)
                <div class="bucket-row">
                    <div class="bucket-dot" style="background:{{ $b['color'] }}"></div>
                    <span class="bucket-label">{{ $b['label'] }}</span>
                    <div class="bucket-track">
                        <div class="bucket-fill" style="background:{{ $b['color'] }};width:{{ $maxBucket>0?round(($b['count']/$maxBucket)*100):0 }}%"></div>
                    </div>
                    <span class="bucket-count" style="color:{{ $b['color'] }}">{{ $b['count'] }}</span>
                </div>
                @endforeach
            </div>
            <div style="margin-top:16px"><canvas id="rtBucketChart" style="height:160px"></canvas></div>
        </div>
    </div>

    {{-- SLA Gauge --}}
    <div class="col-lg-3">
        <div class="rt-card d-flex flex-column align-items-center justify-content-center" style="text-align:center">
            <h3 class="rt-card-title mb-1">SLA Compliance</h3>
            <p class="rt-card-sub mb-3">Leads responded within {{ $slaMinutes }}min</p>
            @php
                $slaVal  = $summary['sla_compliance'];
                $slaClr  = $slaVal >= 75 ? '#10b981' : ($slaVal >= 50 ? '#f59e0b' : '#ef4444');
                $circ    = 2 * pi() * 68; // r=68
                $offset  = $circ * (1 - $slaVal / 100);
            @endphp
            <div class="sla-gauge-ring">
                <svg viewBox="0 0 160 160" width="160" height="160">
                    <circle class="track" cx="80" cy="80" r="68"/>
                    <circle class="fill" cx="80" cy="80" r="68"
                        stroke="{{ $slaClr }}"
                        stroke-dasharray="{{ $circ }}"
                        stroke-dashoffset="{{ $offset }}"
                        id="slaGaugeFill"/>
                </svg>
                <div class="sla-gauge-center">
                    <span class="sla-gauge-pct" style="color:{{ $slaClr }}">{{ $slaVal }}%</span>
                    <span class="sla-gauge-tag">SLA</span>
                </div>
            </div>
            <div class="d-flex gap-4 mt-2">
                <div>
                    <div style="font-size:1.25rem;font-weight:800;color:#10b981">{{ $summary['within_sla'] }}</div>
                    <div style="font-size:.65rem;color:#94a3b8;font-weight:600;text-transform:uppercase">Within SLA</div>
                </div>
                <div>
                    <div style="font-size:1.25rem;font-weight:800;color:#ef4444">{{ $summary['responded'] - $summary['within_sla'] }}</div>
                    <div style="font-size:.65rem;color:#94a3b8;font-weight:600;text-transform:uppercase">SLA Breach</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Telecaller SLA donut --}}
    <div class="col-lg-4">
        <div class="rt-card">
            <h3 class="rt-card-title">SLA Compliance by Telecaller</h3>
            <p class="rt-card-sub">% of leads contacted within {{ $slaMinutes }}min per agent</p>
            <div style="height:160px;margin-bottom:10px"><canvas id="rtTcSlaChart"></canvas></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ ANALYTICS ROW 2 --}}
<div class="row g-3 rt-section">
    {{-- Daily trend --}}
    <div class="col-lg-8">
        <div class="rt-card">
            <h3 class="rt-card-title">Daily Average Response Time Trend</h3>
            <p class="rt-card-sub">Average minutes to first contact per day over the selected period</p>
            <div style="height:220px"><canvas id="rtTrendChart"></canvas></div>
        </div>
    </div>

    {{-- Telecaller breakdown table --}}
    <div class="col-lg-4">
        <div class="rt-card" style="padding:18px 0 0">
            <div style="padding:0 20px 12px;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h3 class="rt-card-title mb-0">Telecaller Summary</h3>
                    <p class="rt-card-sub mb-0">Avg response &amp; SLA per agent</p>
                </div>
            </div>
            <div style="overflow-x:auto">
                <table class="table mb-0 tc-mini-table">
                    <thead><tr><th>Agent</th><th>Total</th><th>Avg</th><th>SLA%</th></tr></thead>
                    <tbody>
                        @forelse($tcBreakdown as $tc)
                        @php $tcSlaCl = $tc['sla_pct'] >= 75 ? '#10b981' : ($tc['sla_pct'] >= 50 ? '#f59e0b' : '#ef4444'); @endphp
                        <tr>
                            <td style="font-weight:600;color:#0f172a;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $tc['name'] }}</td>
                            <td style="color:#94a3b8">{{ $tc['total'] }}</td>
                            <td style="color:#f59e0b;font-weight:600;font-variant-numeric:tabular-nums">
                                {{ $tc['avg_mins'] !== null ? ($tc['avg_mins']<60?$tc['avg_mins'].'m':round($tc['avg_mins']/60,1).'h') : '—' }}
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <div class="sla-bar-track" style="width:50px">
                                        <div class="sla-bar-fill" style="background:{{ $tcSlaCl }};width:{{ $tc['sla_pct'] }}%"></div>
                                    </div>
                                    <span style="font-size:.72rem;font-weight:700;color:{{ $tcSlaCl }}">{{ $tc['sla_pct'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.8rem">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ INSIGHTS --}}
@if(count($insights))
<div class="rt-card rt-section">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-icons" style="color:#6366f1;font-size:20px">auto_awesome</span>
        <h3 class="rt-card-title mb-0">Smart Insights</h3>
        <span style="font-size:.68rem;background:#eef2ff;color:#6366f1;padding:2px 8px;border-radius:20px;font-weight:700;border:1px solid #c7d2fe">{{ count($insights) }} alert{{ count($insights)>1?'s':'' }}</span>
    </div>
    @foreach($insights as $ins)
    <div class="rt-insight rt-insight-{{ $ins['type'] }}">
        <span class="material-icons">{{ $ins['icon'] }}</span>
        <span style="font-size:.82rem;font-weight:500;color:#1e293b;line-height:1.5">{{ $ins['text'] }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════ DETAIL TABLE + PAGINATION --}}
<div class="custom-table rt-section">
    <div class="table-header">
        <h3>
            <span class="material-icons me-2" style="vertical-align:-5px;font-size:20px">manage_search</span>
            Lead Response Detail
        </h3>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark" style="font-size:11px">{{ $paginatedRows->total() }} records</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 rt-detail-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Lead Code</th>
                    <th>Lead Name</th>
                    <th>Telecaller</th>
                    <th>Manager</th>
                    <th>Source</th>
                    <th>Lead Created</th>
                    <th>First Response</th>
                    <th>Response Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedRows as $idx => $row)
                @php
                    $rm = $row['response_minutes'];
                    if ($rm === null) {
                        $badge = ['class'=>'rt-badge-none','icon'=>'remove_circle_outline','label'=>'No Response'];
                    } elseif ($rm < 5) {
                        $badge = ['class'=>'rt-badge-excellent','icon'=>'bolt','label'=>round($rm,2).' min'];
                    } elseif ($rm < 30) {
                        $badge = ['class'=>'rt-badge-good','icon'=>'check_circle','label'=>round($rm,2).' min'];
                    } elseif ($rm < 60) {
                        $badge = ['class'=>'rt-badge-acceptable','icon'=>'schedule','label'=>round($rm,2).' min'];
                    } elseif ($rm < 240) {
                        $badge = ['class'=>'rt-badge-slow','icon'=>'hourglass_bottom','label'=>round($rm/60,1).' hr'];
                    } else {
                        $badge = ['class'=>'rt-badge-critical','icon'=>'timer_off','label'=>($rm<1440?round($rm/60,1).' hr':round($rm/1440,1).' d')];
                    }
                    $globalIdx = (($paginatedRows->currentPage()-1) * $paginatedRows->perPage()) + $idx + 1;
                @endphp
                <tr>
                    <td style="color:#94a3b8;font-weight:700;font-size:.8rem">{{ $globalIdx }}</td>
                    <td><span style="font-size:.75rem;font-weight:700;background:#eef2ff;color:#6366f1;padding:2px 8px;border-radius:6px;letter-spacing:.3px">{{ $row['lead_code'] }}</span></td>
                    <td style="font-weight:600;color:#0f172a;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $row['lead_name'] }}">{{ $row['lead_name'] }}</td>
                    <td style="color:#475569">{{ $row['telecaller'] }}</td>
                    <td style="color:#64748b;font-size:.78rem">{{ $row['manager'] }}</td>
                    <td><span style="font-size:.72rem;background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:6px">{{ $row['source'] }}</span></td>
                    <td style="color:#64748b;font-size:.78rem;font-variant-numeric:tabular-nums">{{ $row['created_at'] }}</td>
                    <td style="color:#64748b;font-size:.78rem;font-variant-numeric:tabular-nums">
                        {{ $row['first_response_at'] ?? '—' }}
                    </td>
                    <td>
                        <span class="rt-badge {{ $badge['class'] }}">
                            <span class="material-icons" style="font-size:12px">{{ $badge['icon'] }}</span>
                            {{ $badge['label'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <span class="material-icons d-block mb-2" style="font-size:36px;color:#e2e8f0">timer_off</span>
                        No records found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($paginatedRows->lastPage() > 1)
    <div class="rt-pagination">
        <div class="rt-pagination-info">
            Showing {{ $paginatedRows->firstItem() }}–{{ $paginatedRows->lastItem() }}
            of {{ $paginatedRows->total() }} records
            &nbsp;·&nbsp; Page {{ $paginatedRows->currentPage() }} of {{ $paginatedRows->lastPage() }}
        </div>
        <div class="rt-page-links">
            {{-- Prev --}}
            <a href="{{ $paginatedRows->previousPageUrl() ?? '#' }}"
               class="rt-page-btn {{ $paginatedRows->onFirstPage() ? 'disabled' : '' }}">
                <span class="material-icons" style="font-size:14px">chevron_left</span>
            </a>

            {{-- Page numbers with ellipsis --}}
            @php
                $cur   = $paginatedRows->currentPage();
                $last  = $paginatedRows->lastPage();
                $range = collect(range(max(1,$cur-2), min($last,$cur+2)));
            @endphp
            @if($range->first() > 1)
                <a href="{{ $paginatedRows->url(1) }}" class="rt-page-btn">1</a>
                @if($range->first() > 2)<span style="font-size:.8rem;color:#94a3b8;align-self:center;padding:0 2px">…</span>@endif
            @endif
            @foreach($range as $p)
                <a href="{{ $paginatedRows->url($p) }}"
                   class="rt-page-btn {{ $p == $cur ? 'active' : '' }}">{{ $p }}</a>
            @endforeach
            @if($range->last() < $last)
                @if($range->last() < $last - 1)<span style="font-size:.8rem;color:#94a3b8;align-self:center;padding:0 2px">…</span>@endif
                <a href="{{ $paginatedRows->url($last) }}" class="rt-page-btn">{{ $last }}</a>
            @endif

            {{-- Next --}}
            <a href="{{ $paginatedRows->nextPageUrl() ?? '#' }}"
               class="rt-page-btn {{ !$paginatedRows->hasMorePages() ? 'disabled' : '' }}">
                <span class="material-icons" style="font-size:14px">chevron_right</span>
            </a>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════ CHARTS SCRIPT --}}
<script>
(function () {
    const buckets   = @json($bucketDist);
    const daily     = @json($dailyTrend->values());
    const tcData    = @json($tcBreakdown->values());
    const slaVal    = {{ $summary['sla_compliance'] }};

    const GREEN  = '#10b981', CYAN = '#06b6d4', AMBER = '#f59e0b',
          ORANGE = '#f97316', RED  = '#ef4444', DARK  = '#991b1b', GRAY = '#94a3b8';

    function initCharts() {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

        // ── 1. Bucket bar chart (horizontal)
        const ctxB = document.getElementById('rtBucketChart');
        if (ctxB && buckets.length) {
            new Chart(ctxB, {
                type: 'bar',
                data: {
                    labels: buckets.map(b => b.label),
                    datasets: [{
                        label: 'Leads',
                        data: buckets.map(b => b.count),
                        backgroundColor: buckets.map(b => b.color),
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                        y: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
                    }
                }
            });
        }

        // ── 2. Telecaller SLA donut
        const ctxTc = document.getElementById('rtTcSlaChart');
        if (ctxTc && tcData.length) {
            const colors = [GREEN, AMBER, RED, '#6366F1', '#8B5CF6', CYAN, ORANGE, '#EC4899'];
            new Chart(ctxTc, {
                type: 'doughnut',
                data: {
                    labels: tcData.map(t => t.name),
                    datasets: [{
                        data: tcData.map(t => t.sla_pct),
                        backgroundColor: colors.slice(0, tcData.length),
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 10 } } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.toFixed(1)}% SLA` } }
                    }
                }
            });
        }

        // ── 3. Daily avg response trend
        const ctxT = document.getElementById('rtTrendChart');
        if (ctxT && daily.length) {
            const slaLineVal = {{ $slaMinutes }};
            new Chart(ctxT, {
                type: 'line',
                data: {
                    labels: daily.map(d => d.day),
                    datasets: [
                        {
                            label: 'Avg Response (min)',
                            data: daily.map(d => d.avg),
                            borderColor: '#6366F1',
                            backgroundColor: 'rgba(99,102,241,.1)',
                            fill: true,
                            tension: .35,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: daily.map(d => d.avg <= slaLineVal ? GREEN : RED),
                        },
                        {
                            label: `SLA Threshold (${slaLineVal}min)`,
                            data: daily.map(() => slaLineVal),
                            borderColor: AMBER,
                            borderDash: [5, 4],
                            borderWidth: 1.5,
                            pointRadius: 0,
                            fill: false,
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0
                                    ? ` Avg: ${ctx.parsed.y} min  ·  ${daily[ctx.dataIndex]?.count ?? 0} leads`
                                    : ` SLA: ${ctx.parsed.y} min`
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 }, callback: v => v + 'm' } },
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 }, maxTicksLimit: 10 } }
                    }
                }
            });
        }
    }

    if (typeof Chart !== 'undefined') {
        initCharts();
    } else {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        s.onload = initCharts;
        document.head.appendChild(s);
    }

    // Animate SLA gauge ring on load
    window.addEventListener('load', () => {
        const fill = document.getElementById('slaGaugeFill');
        if (fill) {
            const circ = 2 * Math.PI * 68;
            fill.style.transition = 'stroke-dashoffset 1.4s cubic-bezier(.4,0,.2,1)';
            fill.style.strokeDashoffset = circ * (1 - slaVal / 100);
        }
    });
})();
</script>
@endsection
