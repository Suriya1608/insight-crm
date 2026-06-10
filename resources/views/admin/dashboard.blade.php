@extends('layouts.app')

@section('page_title', 'Admin Dashboard')

@section('content')
<style>
/* ── Hero Banner ─────────────────────────────────────────────────────────────── */
@keyframes admBlobFloat { 0%,100%{transform:translateY(0) scale(1);} 50%{transform:translateY(-14px) scale(1.05);} }
@keyframes admPulse     { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.5;transform:scale(1.8);} }
@keyframes admFadeUp    { from{opacity:0;transform:translateY(16px);} to{opacity:1;transform:translateY(0);} }

.adm-hero {
    background: #1D1D1D;
    border-radius: 14px; padding: 24px 28px; color: #fff;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.28);
    margin-bottom: 20px;
    animation: admFadeUp .5s ease both;
}
.adm-blob { position:absolute; border-radius:50%; pointer-events:none; }
.adm-blob-1 { width:260px; height:260px; background:rgba(255,92,0,.10); top:-80px; right:-60px; animation:admBlobFloat 8s ease-in-out infinite; }
.adm-blob-2 { width:160px; height:160px; background:rgba(255,140,74,.07); bottom:-60px; right:160px; animation:admBlobFloat 10s ease-in-out infinite reverse; }
.adm-blob-3 { width:100px; height:100px; background:rgba(255,255,255,.04); top:24px; left:260px; animation:admBlobFloat 7s ease-in-out infinite 2s; }
.adm-blob-4 { width:60px;  height:60px;  background:rgba(255,92,0,.08); bottom:20px; left:180px; animation:admBlobFloat 6s ease-in-out infinite 1s; }

.adm-hero-inner { display:flex; align-items:stretch; gap:24px; position:relative; z-index:1; }

.adm-profile-card {
    background: rgba(255,255,255,.10); backdrop-filter:blur(10px);
    border: 1px solid rgba(255,255,255,.18); border-radius:18px;
    padding: 22px 26px; display:flex; flex-direction:column; align-items:center; gap:6px;
    min-width:210px; flex-shrink:0;
}
.adm-avatar {
    width:62px; height:62px; border-radius:50%;
    background: linear-gradient(135deg,#FF5C00,#FF8C4A);
    border: 3px solid rgba(255,92,0,.40);
    display:flex; align-items:center; justify-content:center;
    font-size:22px; font-weight:800; color:#fff;
    box-shadow: 0 6px 20px rgba(0,0,0,.32); margin-bottom:4px;
}
.adm-profile-name { font-size:14px; font-weight:700; color:#fff; text-align:center; line-height:1.3; }
.adm-profile-role {
    display:flex; align-items:center; gap:4px;
    font-size:10px; font-weight:700; color:rgba(255,160,100,.85);
    text-transform:uppercase; letter-spacing:1px; margin-top:2px;
}
.adm-meta-row {
    display:flex; align-items:center; margin-top:10px;
    background:rgba(0,0,0,.20); border-radius:10px; padding:8px 12px;
    width:100%; justify-content:space-around;
}
.adm-meta-item { text-align:center; }
.adm-meta-val  { font-size:20px; font-weight:800; color:#fff; line-height:1; }
.adm-meta-key  { font-size:9px; font-weight:600; color:rgba(255,160,100,.65); text-transform:uppercase; letter-spacing:.5px; margin-top:2px; }
.adm-meta-sep  { width:1px; height:28px; background:rgba(255,255,255,.18); margin:0 6px; }

.adm-hero-right { flex:1; display:flex; flex-direction:column; justify-content:space-between; gap:18px; }

.adm-pill-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.adm-pill {
    display:flex; align-items:center; gap:6px;
    font-size:12px; font-weight:600;
    background:rgba(255,255,255,.12); padding:5px 14px;
    border-radius:20px; backdrop-filter:blur(4px);
    border:1px solid rgba(255,255,255,.10);
}
.adm-pill-danger { background:rgba(239,68,68,.22); border-color:rgba(239,68,68,.35); }
.adm-live-dot { width:7px; height:7px; background:#4ade80; border-radius:50%; animation:admPulse 1.4s infinite; flex-shrink:0; }

.adm-rings { display:flex; gap:22px; align-items:center; }
.adm-ring-wrap { position:relative; width:86px; height:86px; display:flex; align-items:center; justify-content:center; }
.adm-ring-lbl  { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.adm-ring-val  { font-size:18px; font-weight:800; color:#fff; line-height:1; }
.adm-ring-sub  { font-size:9px; font-weight:600; color:rgba(255,200,160,.80); text-transform:uppercase; letter-spacing:.5px; }

.adm-kpi-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.adm-kpi-pill {
    background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.15);
    border-radius:12px; padding:10px 14px;
    display:flex; align-items:center; gap:10px;
}
.adm-kpi-pill-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.adm-kpi-pill-val { font-size:18px; font-weight:800; color:#fff; line-height:1; }
.adm-kpi-pill-lbl { font-size:9px; font-weight:600; color:rgba(255,160,100,.75); text-transform:uppercase; letter-spacing:.4px; }

@media(max-width:768px) {
    .adm-hero-inner { flex-direction:column; gap:16px; }
    .adm-profile-card { min-width:unset; width:100%; }
    .adm-rings { display:none; }
    .adm-hero { padding:20px; }
    .adm-kpi-row { grid-template-columns:1fr 1fr; }
}

/* ── StatRow KPI Cards — telecaller pattern ── */
.adm-stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
@media(max-width:960px){ .adm-stat-grid{ grid-template-columns:repeat(2,1fr); } }

.adm-sr { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; transition:all .15s; }
.adm-sr-or { background:#FF5C00; box-shadow:0 4px 14px rgba(255,92,0,.22); }
.adm-sr-wh { background:#FEFEFE; border:1px solid #F0F0F0; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.adm-sr-icon { width:32px; height:32px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.adm-sr-or .adm-sr-icon { background:rgba(255,255,255,.18); color:#fff; }
.adm-sr-wh .adm-sr-icon { background:#FFF7ED; color:#FF5C00; }
.adm-sr-icon .material-icons { font-size:15px!important; }
.adm-sr-lbl { font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:1px; }
.adm-sr-or .adm-sr-lbl { color:rgba(255,255,255,.75); }
.adm-sr-wh .adm-sr-lbl { color:#9CA3AF; }
.adm-sr-val { font-size:20px; font-weight:800; line-height:1; }
.adm-sr-or .adm-sr-val { color:#fff; }
.adm-sr-wh .adm-sr-val { color:#1D1D1D; }

/* ── Section labels — telecaller orange-bar style ── */
.dash-section-label {
    font-size:11px; font-weight:700; color:#1D1D1D;
    display:flex; align-items:center; gap:8px;
    margin:16px 0 12px; padding-left:0;
}
.dash-section-label::before { content:''; width:3px; height:16px; background:#FF5C00; border-radius:2px; flex-shrink:0; }
.dash-section-label::after  { content:''; flex:1; height:1px; background:#F0F0F0; }

/* ── Chart cards — SHead pattern ── */
.chart-card {
    background:#FEFEFE!important; border:1px solid #F0F0F0!important;
    border-radius:14px!important; padding:0!important;
    overflow:hidden!important; box-shadow:0 2px 8px rgba(0,0,0,.04)!important;
}
.chart-header {
    display:flex; flex-direction:column; justify-content:center;
    padding:14px 20px 12px 23px!important;
    border-bottom:1px solid #F0F0F0!important;
    background:linear-gradient(135deg,#FAFBFC,#FFFFFF)!important;
    margin-bottom:0!important; position:relative;
}
.chart-header::before {
    content:''; position:absolute; left:0; top:0; bottom:0;
    width:3px; background:#FF5C00; border-radius:2px 0 0 2px;
}
.chart-header h3 { font-size:13.5px; font-weight:700; color:#1D1D1D!important; margin:0; }
.chart-header p  { font-size:11px; color:#9CA3AF!important; margin:2px 0 0; }
.chart-card > *:not(.chart-header) { padding:16px 20px; }

/* ── Health / Funnel / Tables ── */
.health-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.health-item { background:#F9FAFB; border-radius:10px; padding:12px 14px; border:1px solid #F0F0F0; }
.health-item .hi-val { font-size:22px; font-weight:800; line-height:1; margin-bottom:3px; }
.health-item .hi-lbl { font-size:10px; font-weight:700; color:#9CA3AF; text-transform:uppercase; letter-spacing:.5px; }
.funnel-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.funnel-row .f-lbl { font-size:11px; font-weight:600; color:#6B7280; width:80px; flex-shrink:0; text-align:right; }
.funnel-row .f-bg  { flex:1; background:#F3F4F6; border-radius:6px; height:22px; overflow:hidden; }
.funnel-row .f-bar { height:100%; border-radius:6px; display:flex; align-items:center; padding-left:8px;
    font-size:11px; font-weight:700; color:rgba(255,255,255,.9); min-width:24px; transition:width .6s ease; }
.funnel-row .f-cnt { font-size:12px; font-weight:700; color:#374151; width:32px; text-align:right; flex-shrink:0; }
.perf-table { width:100%; border-collapse:collapse; font-size:12px; }
.perf-table th { font-size:9.5px; font-weight:700; color:#9CA3AF; text-transform:uppercase; letter-spacing:.7px;
    padding:0 12px 10px; border-bottom:2px solid #F0F0F0; background:#F4F6F8; }
.perf-table td { padding:10px 12px; border-bottom:1px solid #F4F6F8; vertical-align:middle; color:#374151; }
.perf-table tr:last-child td { border-bottom:none; }
.perf-table tr:hover td { background:#FFF7ED!important; }
.rank-chip { width:22px; height:22px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; }
.gauge-center { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; }
.gauge-center .g-pct { font-size:26px; font-weight:800; color:#1D1D1D; line-height:1; }
.gauge-center .g-lbl { font-size:10px; font-weight:700; color:#9CA3AF; text-transform:uppercase; letter-spacing:.5px; margin-top:2px; }
</style>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- HERO BANNER                                                                  --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
@php
    $h = now()->hour;
    $greeting = $h < 12 ? 'Good Morning' : ($h < 17 ? 'Good Afternoon' : 'Good Evening');
    $greetIcon = $h < 12 ? 'wb_sunny' : ($h < 17 ? 'light_mode' : 'nights_stay');
    $adminName = auth()->user()->name ?? 'Admin';
    $adminInitials = collect(explode(' ', $adminName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
@endphp

<div class="adm-hero">
    <div class="adm-blob adm-blob-1"></div>
    <div class="adm-blob adm-blob-2"></div>
    <div class="adm-blob adm-blob-3"></div>
    <div class="adm-blob adm-blob-4"></div>

    <div class="adm-hero-inner">

        {{-- Profile Card --}}
        <div class="adm-profile-card">
            <div class="adm-avatar">{{ $adminInitials }}</div>
            <div class="adm-profile-name">{{ $greeting }}, {{ $adminName }}</div>
            <div class="adm-profile-role">
                <span class="material-icons" style="font-size:12px;">admin_panel_settings</span>
                Admin Panel
            </div>
            <div class="adm-meta-row">
                <div class="adm-meta-item">
                    <div class="adm-meta-val">{{ $totalLeads }}</div>
                    <div class="adm-meta-key">Leads</div>
                </div>
                <div class="adm-meta-sep"></div>
                <div class="adm-meta-item">
                    <div class="adm-meta-val">{{ $conversionsThisMonth }}</div>
                    <div class="adm-meta-key">Conv.</div>
                </div>
                <div class="adm-meta-sep"></div>
                <div class="adm-meta-item">
                    <div class="adm-meta-val">{{ $conversionRateThisMonth }}%</div>
                    <div class="adm-meta-key">Rate</div>
                </div>
            </div>
            <div class="adm-meta-row" style="margin-top:6px; background:rgba(255,255,255,.07)">
                <div class="adm-meta-item">
                    <div class="adm-meta-val" style="font-size:17px">{{ $totalManagers }}</div>
                    <div class="adm-meta-key" style="color:rgba(255,160,100,.55)">Managers</div>
                </div>
                <div class="adm-meta-sep"></div>
                <div class="adm-meta-item">
                    <div class="adm-meta-val" style="font-size:17px">{{ $totalTelecallers }}</div>
                    <div class="adm-meta-key" style="color:rgba(255,160,100,.55)">Telecallers</div>
                </div>
            </div>
        </div>

        {{-- Right side --}}
        <div class="adm-hero-right">

            {{-- Pills row --}}
            <div class="adm-pill-row">
                <div class="adm-pill">
                    <span class="material-icons" style="font-size:14px;">{{ $greetIcon }}</span>
                    {{ now()->format('l, d F Y') }}
                </div>
                <div class="adm-pill">
                    <div class="adm-live-dot"></div>
                    Live Dashboard
                </div>
                @if($activeCallsNow > 0)
                <div class="adm-pill adm-pill-danger">
                    <span class="material-icons" style="font-size:14px;">call</span>
                    {{ $activeCallsNow }} active call{{ $activeCallsNow > 1 ? 's' : '' }}
                </div>
                @endif
                @if($overdueFollowups > 0)
                <div class="adm-pill adm-pill-danger">
                    <span class="material-icons" style="font-size:14px;">notifications_active</span>
                    {{ $overdueFollowups }} overdue follow-up{{ $overdueFollowups > 1 ? 's' : '' }}
                </div>
                @endif
            </div>

            {{-- Rings + KPI pills --}}
            <div class="d-flex align-items-center gap-4 flex-wrap">

                {{-- SVG Rings --}}
                @php
                    $circ = 201.1;
                    $callsOffset = max(0, $circ - min(1, $totalLeads / max(1, 200)) * $circ);
                    $convOffset  = max(0, $circ - min(1, $conversionRateThisMonth / 100) * $circ);
                @endphp
                <div class="adm-rings">
                    <div class="adm-ring-wrap">
                        <svg width="86" height="86" viewBox="0 0 86 86">
                            <circle cx="43" cy="43" r="32" fill="none" stroke="rgba(255,255,255,.10)" stroke-width="6"/>
                            <circle cx="43" cy="43" r="32" fill="none" stroke="#FF5C00" stroke-width="6"
                                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ round($callsOffset,1) }}"
                                stroke-linecap="round" transform="rotate(-90 43 43)"/>
                        </svg>
                        <div class="adm-ring-lbl">
                            <span class="adm-ring-val">{{ $totalLeads }}</span>
                            <span class="adm-ring-sub">Leads</span>
                        </div>
                    </div>
                    <div class="adm-ring-wrap">
                        <svg width="86" height="86" viewBox="0 0 86 86">
                            <circle cx="43" cy="43" r="32" fill="none" stroke="rgba(255,255,255,.10)" stroke-width="6"/>
                            <circle cx="43" cy="43" r="32" fill="none" stroke="rgba(255,140,74,.70)" stroke-width="6"
                                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ round($convOffset,1) }}"
                                stroke-linecap="round" transform="rotate(-90 43 43)"/>
                        </svg>
                        <div class="adm-ring-lbl">
                            <span class="adm-ring-val">{{ $conversionRateThisMonth }}%</span>
                            <span class="adm-ring-sub">Conv.</span>
                        </div>
                    </div>
                </div>

                {{-- Quick-stat pills --}}
                <div class="adm-kpi-row" style="flex:1">
                    <div class="adm-kpi-pill">
                        <div class="adm-kpi-pill-icon" style="background:rgba(16,185,129,.25)">
                            <span class="material-icons" style="font-size:16px;color:#4ade80">add_circle</span>
                        </div>
                        <div>
                            <div class="adm-kpi-pill-val">{{ $newLeadsToday }}</div>
                            <div class="adm-kpi-pill-lbl">Today</div>
                        </div>
                    </div>
                    <div class="adm-kpi-pill">
                        <div class="adm-kpi-pill-icon" style="background:rgba(245,158,11,.25)">
                            <span class="material-icons" style="font-size:16px;color:#fbbf24">event_available</span>
                        </div>
                        <div>
                            <div class="adm-kpi-pill-val">{{ $followupsToday }}</div>
                            <div class="adm-kpi-pill-lbl">Follow-ups</div>
                        </div>
                    </div>
                    <div class="adm-kpi-pill">
                        <div class="adm-kpi-pill-icon" style="background:rgba(239,68,68,.22)">
                            <span class="material-icons" style="font-size:16px;color:#f87171">phone_missed</span>
                        </div>
                        <div>
                            <div class="adm-kpi-pill-val">{{ $missedCallsToday }}</div>
                            <div class="adm-kpi-pill-lbl">Missed</div>
                        </div>
                    </div>
                    <div class="adm-kpi-pill">
                        <div class="adm-kpi-pill-icon" style="background:rgba(255,92,0,.22)">
                            <span class="material-icons" style="font-size:16px;color:#FF8C4A">timer</span>
                        </div>
                        <div>
                            @php $mins=intdiv($avgCallDurationSeconds,60); $secs=$avgCallDurationSeconds%60; @endphp
                            <div class="adm-kpi-pill-val">{{ $mins }}m{{ str_pad($secs,2,'0',STR_PAD_LEFT) }}s</div>
                            <div class="adm-kpi-pill-lbl">Avg Call</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- KPI StatRow Cards — distinct metrics not shown in hero                        --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}
<div class="dash-section-label">Operations Overview</div>
<div class="adm-stat-grid">
    {{-- Calls This Month — orange (primary) --}}
    <div class="adm-sr adm-sr-or">
        <div class="adm-sr-icon"><span class="material-icons">phone_forwarded</span></div>
        <div><div class="adm-sr-lbl">Calls This Month</div><div class="adm-sr-val">{{ number_format($callsThisMonth) }}</div></div>
    </div>
    {{-- Unassigned Leads --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#FEF2F2;color:#ef4444;"><span class="material-icons">person_off</span></div>
        <div>
            <div class="adm-sr-lbl">Unassigned Leads</div>
            <div class="adm-sr-val" style="color:{{ $unassignedLeads > 0 ? '#ef4444' : '#10b981' }}">{{ $unassignedLeads }}</div>
        </div>
    </div>
    {{-- WhatsApp Messages Today --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#F0FDF4;color:#16a34a;"><span class="material-icons">chat</span></div>
        <div><div class="adm-sr-lbl">WhatsApp Today</div><div class="adm-sr-val">{{ $waMessagesToday }}</div></div>
    </div>
    {{-- New Leads This Month --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#E0F2FE;color:#0891b2;"><span class="material-icons">trending_up</span></div>
        <div><div class="adm-sr-lbl">New Leads (Month)</div><div class="adm-sr-val">{{ $newLeadsThisMonth }}</div></div>
    </div>
    {{-- Lost / Dropped This Month --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#FFF7ED;color:#f97316;"><span class="material-icons">do_not_disturb</span></div>
        <div><div class="adm-sr-lbl">Lost / Dropped (Month)</div><div class="adm-sr-val">{{ $lostThisMonth }}</div></div>
    </div>
    {{-- Never Contacted --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#F5F3FF;color:#7c3aed;"><span class="material-icons">voice_over_off</span></div>
        <div><div class="adm-sr-lbl">Never Contacted</div><div class="adm-sr-val" style="color:{{ $neverContactedLeads > 0 ? '#7c3aed' : '#10b981' }}">{{ $neverContactedLeads }}</div></div>
    </div>
    {{-- Active Staff Today --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#ECFDF5;color:#10b981;"><span class="material-icons">support_agent</span></div>
        <div><div class="adm-sr-lbl">Staff Active Today</div><div class="adm-sr-val">{{ $activeStaffToday }}</div></div>
    </div>
    {{-- Follow-ups Done Today --}}
    <div class="adm-sr adm-sr-wh">
        <div class="adm-sr-icon" style="background:#FFFBEB;color:#d97706;"><span class="material-icons">check_circle</span></div>
        <div><div class="adm-sr-lbl">Follow-ups Done Today</div><div class="adm-sr-val">{{ $followupsDoneToday }}</div></div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════ --}}
{{-- CHARTS SECTION                                                               --}}
{{-- ════════════════════════════════════════════════════════════════════════════ --}}

{{-- Row: Trend + Health --}}
<div class="dash-section-label">Performance Trends</div>
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card" style="margin-bottom:0;height:100%">
            <div class="chart-header">
                <h3>Leads vs Conversions</h3>
                <p>Daily new leads and conversions &mdash; last 14 days</p>
            </div>
            <div style="height:240px"><canvas id="trendChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card" style="margin-bottom:0;height:100%">
            <div class="chart-header">
                <h3>System Health</h3>
                <p>Live operational snapshot</p>
            </div>
            <div class="health-grid-2">
                <div class="health-item">
                    <div class="hi-val" style="color:#FF5C00">{{ $totalLeads }}</div>
                    <div class="hi-lbl">Total Leads</div>
                </div>
                <div class="health-item">
                    <div class="hi-val" style="color:#10b981">{{ $conversionsThisMonth }}</div>
                    <div class="hi-lbl">Conversions</div>
                </div>
                <div class="health-item">
                    <div class="hi-val" style="color:{{ $activeCallsNow > 0 ? '#ef4444' : '#06b6d4' }}">{{ $activeCallsNow }}</div>
                    <div class="hi-lbl">Live Calls</div>
                </div>
                <div class="health-item">
                    <div class="hi-val" style="color:{{ $overdueFollowups > 0 ? '#f59e0b' : '#10b981' }}">{{ $overdueFollowups }}</div>
                    <div class="hi-lbl">Overdue</div>
                </div>
                <div class="health-item">
                    <div class="hi-val" style="color:#10b981">{{ $totalManagers }}</div>
                    <div class="hi-lbl">Managers</div>
                </div>
                <div class="health-item">
                    <div class="hi-val" style="color:#f59e0b">{{ $totalTelecallers }}</div>
                    <div class="hi-lbl">Telecallers</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row: Gauge + Funnel + Call Volume --}}
<div class="dash-section-label">Lead Pipeline &amp; Calls</div>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="chart-card" style="margin-bottom:0;text-align:center;">
            <div class="chart-header">
                <h3>Conversion Rate</h3>
                <p>This month</p>
            </div>
            <div style="position:relative;height:170px;display:flex;align-items:center;justify-content:center;">
                <canvas id="gaugeChart" style="max-width:170px;max-height:170px;"></canvas>
                <div class="gauge-center">
                    <div class="g-pct">{{ $conversionRateThisMonth }}%</div>
                    <div class="g-lbl">Converted</div>
                </div>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:8px;">
                <span style="font-weight:700;color:#FF5C00">{{ $conversionsThisMonth }}</span> converted &middot;
                <span style="font-weight:700;color:#1D1D1D">{{ $totalLeads }}</span> total
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="chart-card" style="margin-bottom:0;">
            <div class="chart-header">
                <h3>Lead Status Funnel</h3>
                <p>Current distribution</p>
            </div>
            @php
                $fColors = ['#FF5C00','#f59e0b','#06b6d4','#10b981','#10b981','#ef4444'];
                $fMax    = max(array_merge($leadStatusValues,[1]));
            @endphp
            @foreach($leadStatusLabels as $fi => $fl)
            @php $fpct = max(round($leadStatusValues[$fi]/$fMax*100), 4); @endphp
            <div class="funnel-row">
                <span class="f-lbl">{{ $fl }}</span>
                <div class="f-bg">
                    <div class="f-bar" style="width:{{ $fpct }}%;background:{{ $fColors[$fi % count($fColors)] }}">
                        @if($fpct > 18){{ $leadStatusValues[$fi] }}@endif
                    </div>
                </div>
                <span class="f-cnt">{{ $leadStatusValues[$fi] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card" style="margin-bottom:0;">
            <div class="chart-header">
                <h3>Call Volume</h3>
                <p>Total calls per day &mdash; last 14 days</p>
            </div>
            <div style="height:220px"><canvas id="callVolumeChart"></canvas></div>
        </div>
    </div>
</div>

{{-- Row: Sources + WhatsApp --}}
<div class="dash-section-label">Channels &amp; Sources</div>
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="chart-card" style="margin-bottom:0;">
            <div class="chart-header">
                <h3>Lead Sources</h3>
                <p>Distribution by acquisition channel</p>
            </div>
            <div style="height:280px"><canvas id="sourceLeadChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="chart-card" style="margin-bottom:0;">
            <div class="chart-header">
                <h3>WhatsApp Chat Volume</h3>
                <p>Inbound vs outbound messages &mdash; last 14 days</p>
            </div>
            <div style="height:280px"><canvas id="waVolumeChart"></canvas></div>
        </div>
    </div>
</div>

{{-- Row: Telecaller Performance --}}
@if(count($telecallerNames) > 0)
<div class="dash-section-label">Team Performance</div>
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card" style="margin-bottom:0;">
            <div class="chart-header">
                <h3>Telecaller Activity</h3>
                <p>Calls made &amp; conversions — last 30 days</p>
            </div>
            <div style="height:{{ max(200, count($telecallerNames) * 46 + 60) }}px">
                <canvas id="telecallerPerfChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card" style="margin-bottom:0;">
            <div class="chart-header">
                <h3>Top Performers</h3>
                <p>Ranked by calls (last 30 days)</p>
            </div>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th>Name</th>
                        <th style="text-align:right">Calls</th>
                        <th style="text-align:right">Conv.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($telecallerNames as $ti => $tn)
                    @php
                        $rColors = ['#f59e0b','#94a3b8','#cd7c2f'];
                        $rBgs    = ['#fef9c3','#f1f5f9','#fdf0e0'];
                    @endphp
                    <tr>
                        <td>
                            @if($ti < 3)
                            <span class="rank-chip" style="background:{{ $rBgs[$ti] }};color:{{ $rColors[$ti] }}">{{ $ti+1 }}</span>
                            @else
                            <span style="color:#94a3b8;font-size:11px;font-weight:700;padding-left:4px">{{ $ti+1 }}</span>
                            @endif
                        </td>
                        <td class="fw-semibold" style="color:#1D1D1D;font-size:12px;">{{ $tn }}</td>
                        <td style="text-align:right;font-weight:800;color:#FF5C00;">{{ $telecallerCallCounts[$ti] }}</td>
                        <td style="text-align:right;">
                            <span style="background:{{ $telecallerConvCounts[$ti] > 0 ? '#dcfce7' : '#f1f5f9' }};color:{{ $telecallerConvCounts[$ti] > 0 ? '#16a34a' : '#94a3b8' }};font-size:11px;font-weight:700;padding:2px 7px;border-radius:6px;display:inline-block;">
                                {{ $telecallerConvCounts[$ti] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Service Performance --}}
@if($serviceStats->isNotEmpty())
<div class="dash-section-label">Service Intelligence</div>
<div class="chart-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="chart-header mb-0">
            <h3 class="mb-0">Service Performance</h3>
            <p class="mb-0">Lead volume and conversion rate by service</p>
        </div>
        <span style="background:#f0f4ff;color:#FF5C00;font-size:11px;padding:4px 10px;border-radius:8px;font-weight:700;border:1px solid #e0e7ff;">
            {{ $serviceStats->count() }} services
        </span>
    </div>
    <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <th style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;padding:0 12px 10px;width:32px;">#</th>
                    <th style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;padding:0 12px 10px;">Service</th>
                    <th style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;padding:0 12px 10px;text-align:center;">Leads</th>
                    <th style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;padding:0 12px 10px;text-align:center;">Conversions</th>
                    <th style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;padding:0 12px 10px;text-align:center;">Rate</th>
                    <th style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;padding:0 12px 10px;width:160px;">Volume</th>
                </tr>
            </thead>
            <tbody>
                @php $maxService = $serviceStats->max('total') ?: 1; @endphp
                @foreach($serviceStats as $si => $sr)
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:11px 12px;color:#94a3b8;font-size:11px;font-weight:700;">{{ $si+1 }}</td>
                    <td style="padding:11px 12px;font-weight:600;color:#1D1D1D;">{{ $sr['service'] }}</td>
                    <td style="padding:11px 12px;text-align:center;font-weight:700;color:#FF5C00;">{{ $sr['total'] }}</td>
                    <td style="padding:11px 12px;text-align:center;font-weight:700;color:#10b981;">{{ $sr['conversions'] }}</td>
                    <td style="padding:11px 12px;text-align:center;">
                        <span style="background:{{ $sr['rate'] >= 30 ? '#dcfce7' : ($sr['rate'] >= 10 ? '#fef9c3' : '#fee2e2') }};color:{{ $sr['rate'] >= 30 ? '#16a34a' : ($sr['rate'] >= 10 ? '#a16207' : '#dc2626') }};padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;">
                            {{ $sr['rate'] }}%
                        </span>
                    </td>
                    <td style="padding:11px 12px;">
                        <div style="background:#f1f5f9;border-radius:6px;height:8px;overflow:hidden;">
                            <div style="background:linear-gradient(90deg,#FF5C00,#FF8C4A);height:100%;width:{{ round($sr['total']/$maxService*100) }}%;border-radius:6px;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Charts Script ────────────────────────────────────────────────────────── --}}
<script>
(function () {
    const trendLabels = @json($leadTrendLabels);
    const trendLeads  = @json($leadTrendValues);
    const trendConvs  = @json($conversionTrendValues);
    const callLabels  = @json($callVolumeLabels);
    const callValues  = @json($callVolumeValues);
    const srcLabels   = @json($sourceLabels);
    const srcValues   = @json($sourceValues);
    const waLabels    = @json($waVolumeLabels);
    const waIn        = @json($waInboundValues);
    const waOut       = @json($waOutboundValues);
    const tcNames     = @json($telecallerNames);
    const tcCalls     = @json($telecallerCallCounts);
    const tcConvs     = @json($telecallerConvCounts);
    const convRate    = {{ $conversionRateThisMonth }};
    const palette     = ['#FF5C00','#f59e0b','#06b6d4','#10b981','#10b981','#ef4444','#f97316','#84cc16'];

    function _init() {
        /* 1 — Leads vs Conversions */
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    { label:'New Leads', data:trendLeads, borderColor:'#FF5C00', backgroundColor:'rgba(255,92,0,.10)',
                      fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#FF5C00', borderWidth:2.5 },
                    { label:'Conversions', data:trendConvs, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.08)',
                      fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#10b981', borderWidth:2.5 }
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                interaction:{ mode:'index', intersect:false },
                plugins:{ legend:{ display:true, position:'top', labels:{ font:{size:11}, color:'#475569', boxWidth:12, padding:14, usePointStyle:true } } },
                scales:{
                    x:{ grid:{display:false}, ticks:{font:{size:11},color:'#94a3b8'} },
                    y:{ grid:{color:'#f1f5f9'}, ticks:{font:{size:11},color:'#94a3b8',precision:0}, beginAtZero:true }
                }
            }
        });

        /* 2 — Gauge */
        new Chart(document.getElementById('gaugeChart'), {
            type:'doughnut',
            data:{ datasets:[{ data:[convRate, Math.max(100-convRate,0)], backgroundColor:['#FF5C00','#f1f5f9'], borderWidth:0, circumference:270, rotation:225 }] },
            options:{ responsive:true, maintainAspectRatio:true, cutout:'76%', plugins:{ legend:{display:false}, tooltip:{enabled:false} } }
        });

        /* 3 — Call Volume */
        const callMax = Math.max(...callValues, 1);
        new Chart(document.getElementById('callVolumeChart'), {
            type:'bar',
            data:{ labels:callLabels, datasets:[{ label:'Calls', data:callValues,
                backgroundColor: callValues.map(v => { const a = 0.35 + (v/callMax)*.65; return `rgba(99,102,241,${a.toFixed(2)})`; }),
                borderRadius:5, borderSkipped:false }] },
            options:{ responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false} },
                scales:{ x:{grid:{display:false},ticks:{font:{size:11},color:'#94a3b8'}}, y:{grid:{color:'#f1f5f9'},ticks:{font:{size:11},color:'#94a3b8',precision:0},beginAtZero:true} } }
        });

        /* 4 — Sources */
        new Chart(document.getElementById('sourceLeadChart'), {
            type:'doughnut',
            data:{ labels:srcLabels, datasets:[{ data:srcValues, backgroundColor:palette, borderWidth:2, borderColor:'#fff', hoverOffset:6 }] },
            options:{ responsive:true, maintainAspectRatio:false, cutout:'60%',
                plugins:{ legend:{ position:'bottom', labels:{ font:{size:11}, color:'#475569', padding:10, boxWidth:12, usePointStyle:true } } } }
        });

        /* 5 — WhatsApp */
        new Chart(document.getElementById('waVolumeChart'), {
            type:'bar',
            data:{ labels:waLabels, datasets:[
                { label:'Inbound',  data:waIn,  backgroundColor:'rgba(16,185,129,.75)', borderRadius:4, borderSkipped:false },
                { label:'Outbound', data:waOut, backgroundColor:'rgba(99,102,241,.75)', borderRadius:4, borderSkipped:false }
            ]},
            options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{ display:true, position:'top', labels:{font:{size:11},color:'#475569',boxWidth:12,padding:12,usePointStyle:true} } },
                scales:{ x:{grid:{display:false},ticks:{font:{size:11},color:'#94a3b8'}}, y:{grid:{color:'#f1f5f9'},ticks:{font:{size:11},color:'#94a3b8',precision:0},beginAtZero:true} } }
        });

        /* 6 — Telecaller horizontal bar */
        if (tcNames.length > 0) {
            const el = document.getElementById('telecallerPerfChart');
            if (el) new Chart(el, {
                type:'bar',
                data:{ labels:tcNames, datasets:[
                    { label:'Calls Made',  data:tcCalls, backgroundColor:'rgba(99,102,241,.80)',  borderRadius:4, borderSkipped:false },
                    { label:'Conversions', data:tcConvs, backgroundColor:'rgba(16,185,129,.80)', borderRadius:4, borderSkipped:false }
                ]},
                options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
                    plugins:{ legend:{ display:true, position:'top', labels:{font:{size:11},color:'#475569',boxWidth:12,padding:12,usePointStyle:true} } },
                    scales:{ x:{grid:{color:'#f1f5f9'},ticks:{font:{size:11},color:'#94a3b8',precision:0},beginAtZero:true}, y:{grid:{display:false},ticks:{font:{size:12,weight:'600'},color:'#475569'}} } }
            });
        }
    }

    if (typeof Chart !== 'undefined') { _init(); }
    else {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        s.onload = _init;
        document.head.appendChild(s);
    }
})();
</script>

@endsection
