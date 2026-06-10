<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:8.5px; color:#1e293b; background:#fff; }

.hdr { background:linear-gradient(135deg,#1e3a6e 0%,#0f172a 100%); color:#fff; padding:12px 16px 11px; margin-bottom:14px; }
.hdr-title { font-size:15px; font-weight:700; letter-spacing:.2px; }
.hdr-sub   { font-size:8px; opacity:.7; margin-top:2px; }
.hdr-meta  { font-size:7.5px; opacity:.75; margin-top:6px; display:flex; gap:10px; }
.hdr-badge { background:rgba(255,255,255,.15); border-radius:3px; padding:2px 8px; }

.tbl-wrap { margin:0 14px 12px; }
table { width:100%; border-collapse:collapse; }

thead tr.grp th { font-size:7px; font-weight:700; text-align:center; padding:5px 8px; }
thead tr.grp .th-main { background:#0f172a; color:#fff; text-align:left; vertical-align:middle; }
thead tr.grp .th-mgmt { background:#4f46e5; color:#fff; border-left:2px solid #3730a3; }
thead tr.grp .th-coun { background:#059669; color:#fff; border-left:2px solid #047857; }
thead tr.grp .th-over { background:#334155; color:#fff; }

thead tr.sub th {
    padding:5px 8px; font-size:7px; font-weight:700;
    text-transform:uppercase; letter-spacing:.3px; color:#fff; text-align:right;
}
thead tr.sub .th-main { background:#1e293b; color:#fff; text-align:left; }
thead tr.sub .th-mgmt { background:#4f46e5; border-left:2px solid #3730a3; }
thead tr.sub .th-coun { background:#059669; border-left:2px solid #047857; }
thead tr.sub .th-over { background:#334155; }
thead tr.sub .th-fill { background:#334155; text-align:left; }

tbody tr:nth-child(even) { background:#f8fafc; }
tbody tr:nth-child(odd)  { background:#fff; }
tbody td { padding:5px 8px; font-size:8px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
tbody tr:last-child td { border-bottom:none; }

.num { text-align:right; font-weight:700; font-variant-numeric:tabular-nums; }
.c-muted { color:#0f172a; }
.c-green  { color:#10b981; }
.c-blue   { color:#6366f1; }
.c-red    { color:#ef4444; }
.c-gray   { color:#94a3b8; font-size:7px; }

.fill-wrap { display:flex; align-items:center; gap:4px; }
.fill-bg   { flex:1; height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden; }
.fill-bar  { height:5px; border-radius:3px; }
.fill-pct  { font-size:7px; font-weight:700; min-width:22px; }

tfoot td { padding:6px 8px; font-size:8px; font-weight:700; background:#0f172a; color:#fff; border-top:2px solid #334155; }
tfoot .num { text-align:right; }

.footer { margin:14px 14px 0; text-align:center; font-size:7px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:6px; }
</style>
</head>
<body>

<div class="hdr">
    <div class="hdr-title">Course Intakes — {{ $yearLabel }}</div>
    <div class="hdr-sub">Seat allocation per quota per academic year</div>
    <div class="hdr-meta">
        <span class="hdr-badge">Total Courses: {{ $intakes->count() }}</span>
        <span class="hdr-badge">Total Seats: {{ number_format($summary['total_seats']) }}</span>
        <span class="hdr-badge">Enrolled: {{ number_format($summary['total_enrolled']) }}</span>
        <span class="hdr-badge">Fill Rate: {{ $summary['fill_pct'] }}%</span>
        <span class="hdr-badge">Generated: {{ $generatedAt }}</span>
    </div>
</div>

<div class="tbl-wrap">
    <table>
        <thead>
            <tr class="grp">
                <th class="th-main" rowspan="2" style="vertical-align:middle;min-width:160px;">#&nbsp; Course</th>
                <th class="th-mgmt" colspan="3">Management Quota</th>
                <th class="th-coun" colspan="3">Counselling Quota</th>
                <th class="th-over" colspan="2">Overall</th>
                <th class="th-over" style="min-width:70px;">Fill Rate</th>
            </tr>
            <tr class="sub">
                <th class="th-mgmt num">Seats</th>
                <th class="th-mgmt num">Enrolled</th>
                <th class="th-mgmt num">Balance</th>
                <th class="th-coun num">Seats</th>
                <th class="th-coun num">Enrolled</th>
                <th class="th-coun num">Balance</th>
                <th class="th-over num">Total Seats</th>
                <th class="th-over num">Enrolled</th>
                <th class="th-fill"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($intakes as $i => $intake)
            @php
                $pct      = $intake->total_seats > 0 ? round($intake->total_enrolled / $intake->total_seats * 100) : 0;
                $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#10b981');
            @endphp
            <tr>
                <td>
                    <span class="c-gray">{{ $i + 1 }}&nbsp;&nbsp;</span>
                    <strong>{{ $intake->course?->name ?? '—' }}</strong>
                </td>
                <td class="num c-muted">{{ $intake->management_seats }}</td>
                <td class="num c-green">{{ $intake->management_enrolled }}</td>
                <td class="num {{ $intake->management_balance <= 0 ? 'c-red' : 'c-blue' }}">{{ $intake->management_balance }}</td>
                <td class="num c-muted">{{ $intake->counselling_seats }}</td>
                <td class="num c-green">{{ $intake->counselling_enrolled }}</td>
                <td class="num {{ $intake->counselling_balance <= 0 ? 'c-red' : 'c-green' }}">{{ $intake->counselling_balance }}</td>
                <td class="num c-muted">{{ $intake->total_seats }}</td>
                <td class="num c-green">{{ $intake->total_enrolled }}</td>
                <td>
                    <div class="fill-wrap">
                        <div class="fill-bg"><div class="fill-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div></div>
                        <span class="fill-pct" style="color:{{ $barColor }}">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total ({{ $intakes->count() }} courses)</td>
                <td class="num">{{ number_format($summary['mgmt_seats']) }}</td>
                <td class="num" style="color:#86efac">{{ number_format($summary['mgmt_enrolled']) }}</td>
                <td class="num" style="color:{{ $summary['mgmt_balance'] <= 0 ? '#fca5a5' : '#a5b4fc' }}">{{ number_format($summary['mgmt_balance']) }}</td>
                <td class="num">{{ number_format($summary['coun_seats']) }}</td>
                <td class="num" style="color:#86efac">{{ number_format($summary['coun_enrolled']) }}</td>
                <td class="num" style="color:{{ $summary['coun_balance'] <= 0 ? '#fca5a5' : '#86efac' }}">{{ number_format($summary['coun_balance']) }}</td>
                <td class="num">{{ number_format($summary['total_seats']) }}</td>
                <td class="num" style="color:#86efac">{{ number_format($summary['total_enrolled']) }}</td>
                <td style="color:#fde68a;font-weight:700">{{ $summary['fill_pct'] }}% filled</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="footer">
    Edu CRM &nbsp;·&nbsp; Course Intakes &nbsp;·&nbsp; {{ $yearLabel }} &nbsp;·&nbsp; {{ $generatedAt }}
</div>
</body>
</html>
