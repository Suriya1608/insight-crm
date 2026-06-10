<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:8.5px; color:#1e293b; background:#fff; }

.hdr { background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%); color:#fff; padding:12px 16px 11px; margin-bottom:14px; }
.hdr-title { font-size:15px; font-weight:700; letter-spacing:.2px; }
.hdr-meta  { font-size:7.5px; opacity:.82; margin-top:4px; display:flex; gap:12px; }
.hdr-badge { background:rgba(255,255,255,.18); border-radius:3px; padding:2px 8px; }

.kpi-strip { display:flex; gap:8px; margin:0 14px 14px; }
.kpi-box { flex:1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:7px 10px; }
.kpi-box.indigo { border-top:3px solid #6366f1; }
.kpi-box.green  { border-top:3px solid #10b981; }
.kpi-box.amber  { border-top:3px solid #f59e0b; }
.kpi-box.cyan   { border-top:3px solid #06b6d4; }
.kpi-box.violet { border-top:3px solid #8b5cf6; }
.kpi-box.red    { border-top:3px solid #ef4444; }
.kpi-val { font-size:14px; font-weight:800; display:block; line-height:1.2; }
.kpi-box.indigo .kpi-val { color:#6366f1; }
.kpi-box.green  .kpi-val { color:#10b981; }
.kpi-box.amber  .kpi-val { color:#f59e0b; }
.kpi-box.cyan   .kpi-val { color:#06b6d4; }
.kpi-box.violet .kpi-val { color:#8b5cf6; }
.kpi-box.red    .kpi-val { color:#ef4444; }
.kpi-lbl { font-size:6.5px; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-top:2px; display:block; }

.section-lbl { font-size:7.5px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
               color:#6366f1; margin:0 14px 6px; border-top:1.5px solid #e2e8f0; padding-top:9px; }
.tbl-wrap { margin:0 14px 12px; }

table { width:100%; border-collapse:collapse; }
thead th { background:#0f172a; color:#fff; font-weight:700;
    padding:5px 7px; font-size:7.5px; text-transform:uppercase; letter-spacing:.3px; text-align:left; }
tbody tr:nth-child(even) { background:#f8fafc; }
tbody tr:nth-child(odd)  { background:#fff; }
tbody td { padding:4.5px 7px; font-size:8px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
tbody tr:last-child td { border-bottom:none; }
.num { text-align:right; font-variant-numeric:tabular-nums; }

.bar-wrap { display:flex; align-items:center; gap:5px; }
.bar-bg { flex:1; height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden; }
.bar-fill { height:100%; border-radius:3px; }

.dow-pill { display:inline-block; padding:1px 6px; border-radius:10px; font-size:7px; font-weight:700; }
.dow-Mon,.dow-Tue,.dow-Wed,.dow-Thu,.dow-Fri { background:#dbeafe; color:#1e40af; }
.dow-Sat { background:#fce7f3; color:#9d174d; }
.dow-Sun { background:#fee2e2; color:#991b1b; }

.footer { margin-top:14px; text-align:center; font-size:7px; color:#94a3b8;
          border-top:1px solid #e2e8f0; padding-top:6px; margin-left:14px; margin-right:14px; }
</style>
</head>
<body>

<div class="hdr">
    <div class="hdr-title">Daily / Weekly / Monthly Report</div>
    <div class="hdr-meta">
        <span class="hdr-badge">Period: {{ $periodLabel }}</span>
        <span class="hdr-badge">From: {{ $summary['From'] }}</span>
        <span class="hdr-badge">To: {{ $summary['To'] }}</span>
        <span class="hdr-badge">Generated: {{ $generatedAt }}</span>
    </div>
</div>

@php
    $totalLeads     = $summary['Total Leads'];
    $totalConverted = $summary['Converted'];
    $activeDays     = $summary['Active Days'];
    $overallRate    = $summary['Conv. Rate'];
    $maxDay         = $dailyRows->max('total') ?: 1;
    $peakRow        = $dailyRows->sortByDesc('total')->first();
    $avgPerDay      = $activeDays > 0 ? round($totalLeads / $activeDays, 1) : 0;
@endphp

<div class="kpi-strip">
    <div class="kpi-box indigo"><span class="kpi-val">{{ number_format($totalLeads) }}</span><span class="kpi-lbl">Total Leads</span></div>
    <div class="kpi-box green"> <span class="kpi-val">{{ number_format($totalConverted) }}</span><span class="kpi-lbl">Converted</span></div>
    <div class="kpi-box violet"><span class="kpi-val">{{ $overallRate }}</span><span class="kpi-lbl">Conv. Rate</span></div>
    <div class="kpi-box cyan">  <span class="kpi-val">{{ $activeDays }}</span><span class="kpi-lbl">Active Days</span></div>
    <div class="kpi-box amber"> <span class="kpi-val">{{ $avgPerDay }}</span><span class="kpi-lbl">Avg / Day</span></div>
    <div class="kpi-box red">   <span class="kpi-val">{{ $peakRow ? $peakRow['total'] : '—' }}</span><span class="kpi-lbl">Peak Day</span></div>
</div>

<div class="section-lbl">Daily Breakdown — {{ $periodLabel }}</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Day</th>
                <th class="num">Leads</th>
                <th class="num">Converted</th>
                <th style="min-width:70px">Conv. Rate</th>
                <th style="min-width:65px">Volume</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyRows->sortByDesc('total') as $i => $row)
            @php
                $rCol = $row['rate'] >= 10 ? '#10b981' : ($row['rate'] >= 5 ? '#f59e0b' : ($row['rate'] > 0 ? '#6366f1' : '#cbd5e1'));
                $vPct = round(($row['total'] / $maxDay) * 100);
            @endphp
            <tr>
                <td class="num" style="color:#94a3b8;font-weight:600">#{{ $i+1 }}</td>
                <td style="font-weight:600">{{ $row['day'] }}</td>
                <td><span class="dow-pill dow-{{ $row['dow'] }}">{{ $row['dow'] }}</span></td>
                <td class="num" style="font-weight:700;color:#6366f1">{{ number_format($row['total']) }}</td>
                <td class="num" style="font-weight:700;color:#10b981">{{ $row['converted'] }}</td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg"><div class="bar-fill" style="width:{{ min($row['rate'] * 5, 100) }}%;background:{{ $rCol }}"></div></div>
                        <span style="font-size:7.5px;font-weight:700;color:{{ $rCol }};min-width:28px">{{ $row['rate'] }}%</span>
                    </div>
                </td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg"><div class="bar-fill" style="width:{{ $vPct }}%;background:#6366f1;opacity:.6"></div></div>
                        <span style="font-size:7.5px;color:#64748b;min-width:24px">{{ $vPct }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    Edu CRM &nbsp;·&nbsp; Period Report &nbsp;·&nbsp; {{ $periodLabel }} &nbsp;·&nbsp; {{ $generatedAt }}
</div>
</body>
</html>
