@extends('layouts.app')
@section('page_title', 'Conversion Report')

@section('content')
@php $rp = Auth::user()->role === 'report_viewer' ? 'report_viewer' : 'admin'; @endphp

<style>
.kpi-card { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 6px rgba(15,23,42,.07); height:100%; position:relative; overflow:hidden; }
.kpi-card .accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:14px 14px 0 0; }
.kpi-icon { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; margin-bottom:12px; }
.kpi-value { font-size:2rem; font-weight:800; color:#0f172a; line-height:1.1; }
.kpi-label { font-size:.68rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; margin-top:4px; }
.kpi-sub   { font-size:.75rem; color:#64748b; margin-top:3px; }

.chart-card { background:#fff; border-radius:14px; padding:20px; box-shadow:0 1px 6px rgba(15,23,42,.07); height:100%; }
.ch { font-size:.9rem; font-weight:700; color:#0f172a; margin:0 0 2px; }
.cs { font-size:.73rem; color:#94a3b8; margin:0 0 14px; }

.conv-table thead th { font-size:.7rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:8px 12px; white-space:nowrap; }
.conv-table tbody td { padding:9px 12px; border-bottom:1px solid #f8fafc; vertical-align:middle; font-size:.82rem; }
.conv-table tbody tr:last-child td { border-bottom:0; }
.conv-table tbody tr:hover { background:#fafbff; }

.insight-pill { border-radius:10px; padding:11px 14px; display:flex; gap:10px; align-items:flex-start; }
.insight-pill.warning { background:#fffbeb; border-left:3px solid #f59e0b; }
.insight-pill.success { background:#ecfdf5; border-left:3px solid #10b981; }
.insight-pill.danger  { background:#fef2f2; border-left:3px solid #ef4444; }
.insight-pill.info    { background:#eff6ff; border-left:3px solid #6366f1; }
.insight-pill .material-icons { font-size:18px; margin-top:1px; flex-shrink:0; }
.insight-pill.warning .material-icons { color:#f59e0b; }
.insight-pill.success .material-icons { color:#10b981; }
.insight-pill.danger  .material-icons { color:#ef4444; }
.insight-pill.info    .material-icons { color:#6366f1; }

/* ── Filter bar ── */
.filter-wrap {
    background:#fff;
    border-radius:16px;
    box-shadow:0 1px 8px rgba(15,23,42,.08);
    overflow:hidden;
    margin-bottom:1.25rem;
}
.filter-header {
    background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);
    padding:12px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.filter-header-title {
    display:flex; align-items:center; gap:8px;
    color:#fff; font-size:.82rem; font-weight:700; letter-spacing:.3px;
}
.filter-header-title .material-icons { font-size:17px; opacity:.9; }
.filter-body { padding:16px 20px 18px; }

.filter-field { display:flex; flex-direction:column; gap:5px; }
.filter-label {
    font-size:.68rem; font-weight:700; color:#64748b;
    text-transform:uppercase; letter-spacing:.6px;
    display:flex; align-items:center; gap:5px;
}
.filter-label .material-icons { font-size:13px; color:#6366f1; }
.filter-select {
    border:1.5px solid #e2e8f0;
    border-radius:9px;
    padding:7px 32px 7px 12px;
    font-size:.82rem;
    font-weight:500;
    color:#0f172a;
    background-color:#f8fafc;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 10px center;
    -webkit-appearance:none;
    appearance:none;
    outline:none;
    transition:border-color .18s, box-shadow .18s, background-color .18s;
    cursor:pointer;
}
.filter-select:focus {
    border-color:#6366f1;
    box-shadow:0 0 0 3px rgba(99,102,241,.12);
    background-color:#fff;
}
.filter-select:hover { border-color:#a5b4fc; background-color:#fff; }

.filter-divider {
    width:1px; background:#e2e8f0; align-self:stretch; margin:0 4px;
    flex-shrink:0;
}

.btn-apply {
    background:linear-gradient(135deg,#6366f1,#4f46e5);
    border:none; color:#fff; border-radius:9px;
    padding:8px 20px; font-size:.82rem; font-weight:700;
    display:flex; align-items:center; gap:6px;
    box-shadow:0 2px 8px rgba(99,102,241,.35);
    transition:transform .15s, box-shadow .15s;
    white-space:nowrap;
}
.btn-apply:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(99,102,241,.45); color:#fff; }
.btn-apply .material-icons { font-size:15px; }

.btn-reset {
    background:#f1f5f9; border:1.5px solid #e2e8f0; color:#475569;
    border-radius:9px; padding:7px 16px; font-size:.82rem; font-weight:600;
    display:flex; align-items:center; gap:5px;
    transition:background .15s, border-color .15s;
    white-space:nowrap; text-decoration:none;
}
.btn-reset:hover { background:#e2e8f0; border-color:#cbd5e1; color:#1e293b; }
.btn-reset .material-icons { font-size:14px; }

.btn-export-excel {
    background:#fff; border:1.5px solid #e2e8f0; color:#475569;
    border-radius:9px; padding:7px 14px; font-size:.82rem; font-weight:600;
    display:flex; align-items:center; gap:5px;
    transition:all .15s; white-space:nowrap; text-decoration:none;
}
.btn-export-excel:hover { border-color:#10b981; color:#10b981; background:#ecfdf5; }
.btn-export-excel .material-icons { font-size:14px; }

.btn-export-pdf {
    background:#fff; border:1.5px solid #e2e8f0; color:#475569;
    border-radius:9px; padding:7px 14px; font-size:.82rem; font-weight:600;
    display:flex; align-items:center; gap:5px;
    transition:all .15s; white-space:nowrap; text-decoration:none;
}
.btn-export-pdf:hover { border-color:#ef4444; color:#ef4444; background:#fef2f2; }
.btn-export-pdf .material-icons { font-size:14px; }
.rpt-btn-download { background:#fff; border:1.5px solid #e2e8f0; color:#475569; border-radius:9px; padding:7px 14px; font-size:.82rem; font-weight:600; display:flex; align-items:center; gap:5px; transition:all .15s; white-space:nowrap; cursor:pointer; }
.rpt-btn-download:hover { border-color:#6366f1; color:#6366f1; background:#eef2ff; }
.rpt-btn-download::after { display:none; }
.rpt-btn-download .material-icons { font-size:14px; }
.rpt-dl-menu { border:1.5px solid #e2e8f0 !important; border-radius:12px !important; padding:6px !important; box-shadow:0 8px 24px rgba(15,23,42,.12) !important; min-width:152px; }
.rpt-dl-excel,.rpt-dl-pdf { display:flex !important; align-items:center !important; gap:8px; padding:8px 12px !important; border-radius:8px !important; font-size:.82rem !important; font-weight:600; color:#475569; }
.rpt-dl-excel:hover { color:#10b981 !important; background:#ecfdf5 !important; }
.rpt-dl-pdf:hover { color:#ef4444 !important; background:#fef2f2 !important; }
.rpt-dl-excel .material-icons { font-size:15px; color:#10b981; }
.rpt-dl-pdf .material-icons { font-size:15px; color:#ef4444; }

@php
    $activeFilters = collect([
        $filters['date_range'] !== '30'  ? 'Period: ' . ['7'=>'7d','30'=>'30d','90'=>'90d','quarter'=>'Quarter','year'=>'Year'][$filters['date_range']] : null,
        $filters['source'] !== 'all'     ? 'Source: ' . $filters['source'] : null,
        $filters['telecaller'] !== 'all' ? 'Telecaller filter active' : null,
        $filters['manager'] !== 'all'    ? 'Manager filter active' : null,
    ])->filter()->values();
@endphp
</style>

{{-- ═══ FILTER BAR ═══ --}}
<div class="filter-wrap">
    <div class="filter-header">
        <div class="filter-header-title">
            <span class="material-icons">tune</span>
            Report Filters
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($activeFilters->count())
                <span style="font-size:.72rem;color:rgba(255,255,255,.75)">
                    <span class="material-icons" style="font-size:13px;vertical-align:-2px">filter_alt</span>
                    {{ $activeFilters->count() }} filter{{ $activeFilters->count() > 1 ? 's' : '' }} active
                </span>
            @endif
            <span style="font-size:.72rem;color:rgba(255,255,255,.6)">Conversion Report</span>
        </div>
    </div>

    <div class="filter-body">
        <form method="GET">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                {{-- Time Period --}}
                <div class="filter-field" style="min-width:148px;flex:1">
                    <label class="filter-label">
                        <span class="material-icons">calendar_today</span> Time Period
                    </label>
                    <select name="date_range" class="filter-select">
                        <option value="7"       {{ ($filters['date_range'] ?? '30') === '7'       ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30"      {{ ($filters['date_range'] ?? '30') === '30'      ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90"      {{ ($filters['date_range'] ?? '30') === '90'      ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="quarter" {{ ($filters['date_range'] ?? '30') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year"    {{ ($filters['date_range'] ?? '30') === 'year'    ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>

                {{-- Source --}}
                <div class="filter-field" style="min-width:140px;flex:1">
                    <label class="filter-label">
                        <span class="material-icons">source</span> Lead Source
                    </label>
                    <select name="source" class="filter-select">
                        <option value="all">All Sources</option>
                        @foreach (($filterOptions['sources'] ?? collect()) as $src)
                            <option value="{{ $src }}" {{ ($filters['source'] ?? 'all') === $src ? 'selected' : '' }}>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Telecaller --}}
                <div class="filter-field" style="min-width:148px;flex:1">
                    <label class="filter-label">
                        <span class="material-icons">headset_mic</span> Telecaller
                    </label>
                    <select name="telecaller" class="filter-select">
                        <option value="all">All Telecallers</option>
                        @foreach (($filterOptions['telecallers'] ?? collect()) as $tc)
                            <option value="{{ $tc->id }}" {{ (string)($filters['telecaller'] ?? 'all') === (string)$tc->id ? 'selected' : '' }}>{{ $tc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Manager --}}
                <div class="filter-field" style="min-width:140px;flex:1">
                    <label class="filter-label">
                        <span class="material-icons">manage_accounts</span> Manager
                    </label>
                    <select name="manager" class="filter-select">
                        <option value="all">All Managers</option>
                        @foreach (($filterOptions['managers'] ?? collect()) as $mgr)
                            <option value="{{ $mgr->id }}" {{ (string)($filters['manager'] ?? 'all') === (string)$mgr->id ? 'selected' : '' }}>{{ $mgr->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-divider d-none d-md-block"></div>

                {{-- Actions --}}
                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <button type="submit" class="btn-apply">
                        <span class="material-icons">search</span> Apply
                    </button>
                    <a href="{{ route($rp . '.reports.conversion') }}" class="btn-reset">
                        <span class="material-icons">refresh</span> Reset
                    </a>
                    <div class="dropdown">
                        <button class="rpt-btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">file_download</span> Download
                        </button>
                        <ul class="dropdown-menu rpt-dl-menu">
                            <li><a class="dropdown-item rpt-dl-excel" href="{{ route($rp . '.reports.export', ['report' => 'conversion', 'format' => 'excel'] + request()->query()) }}">
                                <span class="material-icons">table_view</span> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item rpt-dl-pdf" href="{{ route($rp . '.reports.export', ['report' => 'conversion', 'format' => 'pdf'] + request()->query()) }}" target="_blank">
                                <span class="material-icons">picture_as_pdf</span> PDF
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Active filter chips --}}
            @if($activeFilters->count())
            <div class="d-flex flex-wrap gap-2 mt-3 pt-3" style="border-top:1px solid #f1f5f9">
                <span style="font-size:.7rem;color:#94a3b8;font-weight:600;align-self:center">ACTIVE:</span>
                @foreach($activeFilters as $chip)
                <span style="background:#eef2ff;color:#6366f1;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:20px;border:1px solid #c7d2fe">
                    {{ $chip }}
                </span>
                @endforeach
            </div>
            @endif
        </form>
    </div>
</div>

{{-- ═══ KPI CARDS — rate/velocity metrics, NOT raw status counts ═══ --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="accent" style="background:#6366f1"></div>
            <div class="kpi-icon" style="background:#6366f115">
                <span class="material-icons" style="color:#6366f1;font-size:22px">percent</span>
            </div>
            <div class="kpi-value" style="color:#6366f1">{{ $summary['convRate'] }}%</div>
            <div class="kpi-label">Conversion Rate</div>
            <div class="kpi-sub">{{ number_format($summary['converted']) }} of {{ number_format($summary['total']) }} leads won</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="accent" style="background:#06b6d4"></div>
            <div class="kpi-icon" style="background:#06b6d415">
                <span class="material-icons" style="color:#06b6d4;font-size:22px">touch_app</span>
            </div>
            <div class="kpi-value" style="color:#06b6d4">{{ $summary['contactRate'] }}%</div>
            <div class="kpi-label">Contact Rate</div>
            <div class="kpi-sub">{{ number_format($summary['contacted']) }} leads moved past 'New'</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="accent" style="background:#f59e0b"></div>
            <div class="kpi-icon" style="background:#f59e0b15">
                <span class="material-icons" style="color:#f59e0b;font-size:22px">schedule</span>
            </div>
            <div class="kpi-value" style="color:#f59e0b">{{ $summary['avgDaysToConvert'] ?? '—' }}</div>
            <div class="kpi-label">Avg Days to Convert</div>
            <div class="kpi-sub">days from creation to win</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="accent" style="background:#10b981"></div>
            <div class="kpi-icon" style="background:#10b98115">
                <span class="material-icons" style="color:#10b981;font-size:22px">bolt</span>
            </div>
            <div class="kpi-value" style="color:#10b981">{{ $summary['velocity'] }}</div>
            <div class="kpi-label">Lead Velocity</div>
            <div class="kpi-sub">avg leads/day in period</div>
        </div>
    </div>
</div>

{{-- ═══ CONVERSION RATE TREND + LEAD AGE HEALTH ═══ --}}
<div class="row g-3 mb-3">
    {{-- Monthly conversion RATE trend — % not counts --}}
    <div class="col-md-7">
        <div class="chart-card">
            <div class="ch">Monthly Conversion Rate Trend</div>
            <div class="cs">Conversion % and Contact % per month — how quality evolves over time</div>
            <div style="height:230px">
                <canvas id="rateTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Lead Age Health --}}
    <div class="col-md-5">
        <div class="chart-card">
            <div class="ch">Pipeline Age Health</div>
            <div class="cs">How long active (unconverted) leads have been waiting</div>
            @php
                $ageTotal = array_sum($ageHealth);
                $ageDefs = [
                    'fresh' => ['label' => 'Fresh (0–7 days)',  'color' => '#10b981', 'icon' => 'local_florist'],
                    'warm'  => ['label' => 'Warm (8–30 days)',  'color' => '#06b6d4', 'icon' => 'whatshot'],
                    'aging' => ['label' => 'Aging (31–60 days)','color' => '#f59e0b', 'icon' => 'timelapse'],
                    'stale' => ['label' => 'Stale (60+ days)',  'color' => '#ef4444', 'icon' => 'hourglass_disabled'],
                ];
            @endphp
            <div class="d-flex flex-column gap-3 mt-1">
                @foreach ($ageDefs as $key => $def)
                @php
                    $cnt = $ageHealth[$key];
                    $pct = $ageTotal > 0 ? round(($cnt / $ageTotal) * 100) : 0;
                @endphp
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:.78rem;font-weight:600;color:#0f172a;display:flex;align-items:center;gap:6px">
                            <span class="material-icons" style="font-size:15px;color:{{ $def['color'] }}">{{ $def['icon'] }}</span>
                            {{ $def['label'] }}
                        </span>
                        <span style="font-size:.8rem;font-weight:700;color:{{ $def['color'] }}">{{ number_format($cnt) }}
                            <span class="text-muted fw-normal" style="font-size:.7rem">({{ $pct }}%)</span>
                        </span>
                    </div>
                    <div style="background:#f1f5f9;border-radius:6px;height:9px;overflow:hidden">
                        <div style="width:{{ $pct }}%;height:100%;background:{{ $def['color'] }};border-radius:6px;transition:width .6s ease"></div>
                    </div>
                </div>
                @endforeach
                @if($ageTotal === 0)
                <div class="text-center text-muted py-3" style="font-size:.82rem">No active pipeline leads.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══ DAY-OF-WEEK PATTERN + SMART INSIGHTS ═══ --}}
<div class="row g-3 mb-3">
    {{-- Day-of-week intake & conversion --}}
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Day-of-Week Lead Pattern</div>
            <div class="cs">Which days generate leads and conversions</div>
            <div style="height:220px">
                <canvas id="dowChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Smart Auto-Insights --}}
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Smart Insights</div>
            <div class="cs">Auto-generated from current data — actionable observations</div>
            <div class="d-flex flex-column gap-2 mt-1">
                @foreach ($insights as $ins)
                <div class="insight-pill {{ $ins['type'] }}">
                    <span class="material-icons">{{ $ins['icon'] }}</span>
                    <span style="font-size:.81rem;color:#374151;line-height:1.5">{{ $ins['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ═══ SOURCE QUALITY + TELECALLER LEADERBOARD ═══ --}}
<div class="row g-3">
    {{-- Source Quality --}}
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Source Quality Matrix</div>
            <div class="cs">Volume vs conversion rate per source — identify high-ROI channels</div>
            @if($sourceRows->count())
            <div class="table-responsive">
                <table class="table conv-table mb-0 mt-1">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th class="text-center">Volume</th>
                            <th class="text-center">Won</th>
                            <th style="min-width:120px">Conv. Rate</th>
                            <th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sourceRows->sortByDesc('rate') as $row)
                        @php
                            $rate  = $row->rate;
                            $rCol  = $rate >= 10 ? '#10b981' : ($rate >= 5 ? '#f59e0b' : '#ef4444');
                            if ($rate >= 10)      { $grade = 'A'; $gCls = 'g-A'; }
                            elseif ($rate >= 5)   { $grade = 'B'; $gCls = 'g-B'; }
                            elseif ($rate >= 1)   { $grade = 'C'; $gCls = 'g-C'; }
                            else                  { $grade = 'D'; $gCls = 'g-D'; }
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:#0f172a;max-width:130px">
                                <div class="text-truncate" title="{{ $row->source }}">{{ $row->source }}</div>
                            </td>
                            <td class="text-center">{{ number_format($row->total) }}</td>
                            <td class="text-center fw-bold" style="color:#10b981">{{ number_format($row->converted) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="flex:1;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                                        <div style="width:{{ min($rate * 5, 100) }}%;height:100%;background:{{ $rCol }};border-radius:4px"></div>
                                    </div>
                                    <span style="font-size:.75rem;font-weight:700;color:{{ $rCol }};min-width:36px">{{ $rate }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="grade-pill {{ $gCls }}" style="width:24px;height:24px;border-radius:6px;font-size:.68rem;display:inline-flex;align-items:center;justify-content:center;font-weight:800">{{ $grade }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4" style="font-size:.85rem">No source data available.</div>
            @endif
        </div>
    </div>

    {{-- Telecaller Leaderboard --}}
    <div class="col-md-6">
        <div class="chart-card">
            <div class="ch">Telecaller Performance Matrix</div>
            <div class="cs">Ranked by conversion rate — identify top performers</div>
            @if($telecallerRows->count())
            <div class="table-responsive">
                <table class="table conv-table mb-0 mt-1">
                    <thead>
                        <tr>
                            <th style="width:36px">#</th>
                            <th>Telecaller</th>
                            <th class="text-center">Leads</th>
                            <th class="text-center">Won</th>
                            <th style="min-width:110px">Win Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($telecallerRows->sortByDesc('rate')->values() as $i => $row)
                        @php
                            $rate  = $row->rate;
                            $rCol  = $rate >= 10 ? '#10b981' : ($rate >= 5 ? '#f59e0b' : '#ef4444');
                            $rankIcons  = ['workspace_premium','military_tech','emoji_events'];
                            $rankColors = ['#f59e0b','#94a3b8','#b45309'];
                        @endphp
                        <tr>
                            <td class="text-center">
                                @if($i < 3 && $row->converted > 0)
                                    <span class="material-icons" style="font-size:16px;color:{{ $rankColors[$i] }}">{{ $rankIcons[$i] }}</span>
                                @else
                                    <span class="text-muted" style="font-size:.75rem">#{{ $i + 1 }}</span>
                                @endif
                            </td>
                            <td style="font-weight:600;color:#0f172a">{{ $row->name }}</td>
                            <td class="text-center">{{ number_format($row->total) }}</td>
                            <td class="text-center fw-bold" style="color:#10b981">{{ number_format($row->converted) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="flex:1;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                                        <div style="width:{{ min($rate * 5, 100) }}%;height:100%;background:{{ $rCol }};border-radius:4px"></div>
                                    </div>
                                    <span style="font-size:.75rem;font-weight:700;color:{{ $rCol }};min-width:36px">{{ $rate }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4" style="font-size:.85rem">No telecaller data available.</div>
            @endif
        </div>
    </div>
</div>

{{-- ═══ COURSE QUALITY MATRIX ═══ --}}
<div class="row g-3 mt-0">
    <div class="col-12">
        <div class="chart-card">
            <div class="ch">Course Quality Matrix</div>
            <div class="cs">Volume vs conversion rate per course — identify high-demand programmes</div>
            @if($courseRows->count())
            <div class="table-responsive">
                <table class="table conv-table mb-0 mt-1">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th class="text-center">Volume</th>
                            <th class="text-center">Won</th>
                            <th style="min-width:120px">Conv. Rate</th>
                            <th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courseRows as $row)
                        @php
                            $rate  = $row->rate;
                            $rCol  = $rate >= 10 ? '#10b981' : ($rate >= 5 ? '#f59e0b' : '#ef4444');
                            if ($rate >= 10)      { $grade = 'A'; $gCls = 'g-A'; }
                            elseif ($rate >= 5)   { $grade = 'B'; $gCls = 'g-B'; }
                            elseif ($rate >= 1)   { $grade = 'C'; $gCls = 'g-C'; }
                            else                  { $grade = 'D'; $gCls = 'g-D'; }
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:#0f172a">
                                {{ $row->course }}
                            </td>
                            <td class="text-center">{{ number_format($row->total) }}</td>
                            <td class="text-center fw-bold" style="color:#10b981">{{ number_format($row->converted) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="flex:1;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                                        <div style="width:{{ min($rate * 5, 100) }}%;height:100%;background:{{ $rCol }};border-radius:4px"></div>
                                    </div>
                                    <span style="font-size:.75rem;font-weight:700;color:{{ $rCol }};min-width:36px">{{ $rate }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="grade-pill {{ $gCls }}" style="width:24px;height:24px;border-radius:6px;font-size:.68rem;display:inline-flex;align-items:center;justify-content:center;font-weight:800">{{ $grade }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4" style="font-size:.85rem">No course data available.</div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const monthLabels    = @json($monthLabels);
    const monthRate      = @json($monthRate);
    const monthContacted = @json($monthContacted);
    const monthVolume    = @json($monthVolume);
    const dowLabels      = @json($dowLabels);
    const dowTotal       = @json($dowTotal);
    const dowConv        = @json($dowConv);

    const GRID = { color: 'rgba(0,0,0,.04)' };
    const TICK = { color: '#94a3b8', font: { size: 10 } };

    function init() {
        /* ── Monthly Conversion Rate Trend (dual axis: rate % + volume bar) ── */
        new Chart(document.getElementById('rateTrendChart'), {
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Lead Volume',
                        data: monthVolume,
                        backgroundColor: 'rgba(99,102,241,.12)',
                        borderColor: 'rgba(99,102,241,.3)',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'yVol',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Conversion Rate %',
                        data: monthRate,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,.1)',
                        fill: true, tension: 0.4, borderWidth: 2.5,
                        pointBackgroundColor: '#10b981', pointRadius: 4,
                        yAxisID: 'yRate',
                        order: 1,
                    },
                    {
                        type: 'line',
                        label: 'Contact Rate %',
                        data: monthContacted,
                        borderColor: '#06b6d4',
                        backgroundColor: 'transparent',
                        fill: false, tension: 0.4, borderWidth: 2,
                        borderDash: [4, 3],
                        pointBackgroundColor: '#06b6d4', pointRadius: 3,
                        yAxisID: 'yRate',
                        order: 1,
                    },
                ]
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true } } },
                scales: {
                    yRate: {
                        type: 'linear', position: 'left',
                        beginAtZero: true,
                        ticks: { ...TICK, callback: v => v + '%' },
                        grid: GRID,
                    },
                    yVol: {
                        type: 'linear', position: 'right',
                        beginAtZero: true,
                        ticks: { ...TICK, precision: 0 },
                        grid: { display: false },
                    },
                    x: { ticks: TICK, grid: { display: false } }
                }
            }
        });

        /* ── Day-of-Week ── */
        new Chart(document.getElementById('dowChart'), {
            type: 'bar',
            data: {
                labels: dowLabels,
                datasets: [
                    { label: 'Total Leads', data: dowTotal, backgroundColor: 'rgba(99,102,241,.75)', borderRadius: 5, borderSkipped: false },
                    { label: 'Converted',   data: dowConv,  backgroundColor: '#10b981',              borderRadius: 5, borderSkipped: false },
                ]
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true } } },
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

<style>
.grade-pill { width:28px;height:28px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800; }
.g-A { background:#d1fae5;color:#065f46; }
.g-B { background:#dbeafe;color:#1e40af; }
.g-C { background:#fef3c7;color:#92400e; }
.g-D { background:#fee2e2;color:#991b1b; }
</style>
@endsection
