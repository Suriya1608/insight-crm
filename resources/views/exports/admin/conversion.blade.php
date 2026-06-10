<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1e293b; background: #fff; }

/* Header */
.hdr { background: #6366f1; color: #fff; padding: 11px 16px 10px; margin-bottom: 14px; }
.hdr-title { font-size: 15px; font-weight: 700; letter-spacing: .2px; }
.hdr-meta  { font-size: 7.5px; opacity: .82; margin-top: 4px; display: flex; gap: 14px; }
.hdr-badge { background: rgba(255,255,255,.18); border-radius: 3px; padding: 1px 7px; }

/* KPI strip */
.kpi-strip { display: flex; gap: 8px; margin: 0 14px 14px; }
.kpi-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 6px 10px; border-top: 3px solid #6366f1; }
.kpi-val  { font-size: 15px; font-weight: 800; color: #6366f1; display: block; line-height: 1.2; }
.kpi-lbl  { font-size: 6.5px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 1px; display: block; }

/* Section */
.section-lbl { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px;
               color: #6366f1; margin: 0 14px 5px; padding-top: 2px; border-top: 1.5px solid #e2e8f0; padding-top: 8px; }
.tbl-wrap { margin: 0 14px 12px; }

/* Table */
table { width: 100%; border-collapse: collapse; }
thead th {
    background: #0f172a; color: #fff; font-weight: 700;
    padding: 5px 7px; font-size: 7.5px; text-transform: uppercase;
    letter-spacing: .3px; text-align: left;
}
tbody tr:nth-child(even) { background: #f8fafc; }
tbody tr:nth-child(odd)  { background: #fff; }
tbody td { padding: 4.5px 7px; font-size: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
.num { text-align: right; font-variant-numeric: tabular-nums; }

/* Rate bar */
.bar-wrap { display: flex; align-items: center; gap: 5px; }
.bar-bg { flex: 1; height: 5px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 3px; }

/* Badges */
.badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 7px; font-weight: 700; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-red   { background: #fee2e2; color: #991b1b; }

/* Footer */
.footer { margin-top: 14px; text-align: center; font-size: 7px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px; margin-left: 14px; margin-right: 14px; }
</style>
</head>
<body>

{{-- Header --}}
<div class="hdr">
    <div class="hdr-title">Conversion Report</div>
    <div class="hdr-meta">
        <span class="hdr-badge">Period: {{ $periodLabel }}</span>
        <span class="hdr-badge">Generated: {{ $generatedAt }}</span>
        <span class="hdr-badge">Total Leads: {{ number_format($total) }}</span>
        <span class="hdr-badge">Conv. Rate: {{ $convRate }}%</span>
    </div>
</div>

{{-- KPI strip --}}
@php
    $kpis = [
        ['val' => number_format($summary['Total Leads']),     'lbl' => 'Total Leads'],
        ['val' => number_format($summary['Converted']),       'lbl' => 'Converted'],
        ['val' => number_format($summary['Contacted']),       'lbl' => 'Contacted'],
        ['val' => $summary['Conversion Rate'],                'lbl' => 'Conv. Rate'],
        ['val' => $summary['Contact Rate'],                   'lbl' => 'Contact Rate'],
    ];
@endphp
<div class="kpi-strip">
    @foreach($kpis as $kpi)
    <div class="kpi-box">
        <span class="kpi-val">{{ $kpi['val'] }}</span>
        <span class="kpi-lbl">{{ $kpi['lbl'] }}</span>
    </div>
    @endforeach
</div>

{{-- Status Breakdown --}}
<div class="section-lbl">Lead Status Breakdown</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="num">Count</th>
                <th style="min-width:80px">Share</th>
                <th style="min-width:60px">Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusData as $row)
            @php
                $arr  = (array) $row;
                $cnt  = (int) ($arr[1] ?? 0);
                $pct  = (float) rtrim($arr[2] ?? '0', '%');
                $col  = $pct >= 20 ? '#10b981' : ($pct >= 10 ? '#f59e0b' : '#6366f1');
            @endphp
            <tr>
                <td style="font-weight:600">{{ $arr[0] ?? '' }}</td>
                <td class="num" style="font-weight:700">{{ number_format($cnt) }}</td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ min($pct, 100) }}%;background:{{ $col }}"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:700;color:{{ $col }};min-width:28px">{{ $arr[2] ?? '' }}</span>
                    </div>
                </td>
                <td>
                    @php $g = $pct >= 20 ? 'A' : ($pct >= 10 ? 'B' : ($pct >= 5 ? 'C' : 'D')); @endphp
                    <span class="badge {{ $g === 'A' ? 'badge-green' : ($g === 'B' ? 'badge-amber' : 'badge-red') }}">{{ $g }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Source Breakdown --}}
@if($sourceData->count())
<div class="section-lbl">Conversion by Lead Source</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="num">Leads</th>
                <th class="num">Converted</th>
                <th style="min-width:90px">Conv. Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sourceData as $row)
            @php
                $arr  = (array) $row;
                $rate = (float) rtrim($arr[3] ?? '0', '%');
                $col  = $rate >= 10 ? '#10b981' : ($rate >= 5 ? '#f59e0b' : '#ef4444');
            @endphp
            <tr>
                <td style="font-weight:600">{{ $arr[0] ?? '' }}</td>
                <td class="num">{{ number_format((int)($arr[1] ?? 0)) }}</td>
                <td class="num" style="font-weight:700;color:#10b981">{{ number_format((int)($arr[2] ?? 0)) }}</td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ min($rate * 5, 100) }}%;background:{{ $col }}"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:700;color:{{ $col }};min-width:28px">{{ $arr[3] ?? '' }}</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Telecaller Breakdown --}}
@if($tcData->count())
<div class="section-lbl">Conversion by Telecaller</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:28px">#</th>
                <th>Telecaller</th>
                <th class="num">Leads</th>
                <th class="num">Converted</th>
                <th style="min-width:90px">Conv. Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tcData as $i => $row)
            @php
                $rate = (float) rtrim($row[3] ?? '0', '%');
                $col  = $rate >= 10 ? '#10b981' : ($rate >= 5 ? '#f59e0b' : '#ef4444');
            @endphp
            <tr>
                <td class="num" style="color:#94a3b8;font-weight:600">#{{ $i + 1 }}</td>
                <td style="font-weight:600">{{ $row[0] ?? '' }}</td>
                <td class="num">{{ number_format((int)($row[1] ?? 0)) }}</td>
                <td class="num" style="font-weight:700;color:#10b981">{{ number_format((int)($row[2] ?? 0)) }}</td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ min($rate * 5, 100) }}%;background:{{ $col }}"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:700;color:{{ $col }};min-width:28px">{{ $row[3] ?? '' }}</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    Edu CRM &nbsp;·&nbsp; Conversion Report &nbsp;·&nbsp; {{ $periodLabel }} &nbsp;·&nbsp; {{ $generatedAt }}
</div>

</body>
</html>
