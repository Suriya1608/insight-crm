<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1e293b; background: #fff; }

.hdr { background: linear-gradient(135deg,#6366f1 0%,#4f46e5 100%); color: #fff; padding: 12px 16px 11px; margin-bottom: 14px; }
.hdr-title { font-size: 15px; font-weight: 700; letter-spacing: .2px; }
.hdr-meta  { font-size: 7.5px; opacity: .82; margin-top: 4px; display: flex; gap: 12px; }
.hdr-badge { background: rgba(255,255,255,.18); border-radius: 3px; padding: 2px 8px; }

.kpi-strip { display: flex; gap: 8px; margin: 0 14px 14px; }
.kpi-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 7px 10px; border-top: 3px solid #6366f1; }
.kpi-box.green  { border-top-color: #10b981; }
.kpi-box.amber  { border-top-color: #f59e0b; }
.kpi-box.violet { border-top-color: #8b5cf6; }
.kpi-box.cyan   { border-top-color: #06b6d4; }
.kpi-val { font-size: 14px; font-weight: 800; color: #6366f1; display: block; line-height: 1.2; }
.kpi-box.green  .kpi-val  { color: #10b981; }
.kpi-box.amber  .kpi-val  { color: #f59e0b; }
.kpi-box.violet .kpi-val  { color: #8b5cf6; }
.kpi-box.cyan   .kpi-val  { color: #06b6d4; }
.kpi-lbl { font-size: 6.5px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; display: block; }

.section-lbl { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px;
               color: #6366f1; margin: 0 14px 6px; border-top: 1.5px solid #e2e8f0; padding-top: 9px; }
.tbl-wrap { margin: 0 14px 12px; }

table { width: 100%; border-collapse: collapse; }
thead th {
    background: #0f172a; color: #fff; font-weight: 700;
    padding: 5px 7px; font-size: 7.5px; text-transform: uppercase;
    letter-spacing: .3px; text-align: left;
}
tbody tr:nth-child(even) { background: #f8fafc; }
tbody tr:nth-child(odd)  { background: #fff; }
tbody td { padding: 5px 7px; font-size: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
.num { text-align: right; font-variant-numeric: tabular-nums; }

.bar-wrap { display: flex; align-items: center; gap: 5px; }
.bar-bg { flex: 1; height: 5px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 3px; }

.badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 7px; font-weight: 800; }
.g-A { background: #d1fae5; color: #065f46; }
.g-B { background: #dbeafe; color: #1e40af; }
.g-C { background: #fef3c7; color: #92400e; }
.g-D { background: #fee2e2; color: #991b1b; }

.summary-grid { display: flex; gap: 6px; flex-wrap: wrap; margin: 0 14px 12px; }
.sum-item { flex: 1; min-width: 90px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 5px 8px; }
.sum-key { font-size: 6.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; display: block; }
.sum-val { font-size: 9px; font-weight: 700; color: #0f172a; display: block; margin-top: 1px; }

.footer { margin-top: 14px; text-align: center; font-size: 7px; color: #94a3b8;
          border-top: 1px solid #e2e8f0; padding-top: 6px; margin-left: 14px; margin-right: 14px; }
</style>
</head>
<body>

<div class="hdr">
    <div class="hdr-title">Lead Source Report</div>
    <div class="hdr-meta">
        <span class="hdr-badge">Period: {{ $periodLabel }}</span>
        <span class="hdr-badge">Generated: {{ $generatedAt }}</span>
        <span class="hdr-badge">Total Leads: {{ number_format($totalAll) }}</span>
        <span class="hdr-badge">Overall Conv. Rate: {{ $overallRate }}%</span>
    </div>
</div>

@php
    $kpis = [
        ['val' => number_format($summary['Total Sources']),   'lbl' => 'Active Sources',    'cls' => ''],
        ['val' => number_format($summary['Total Leads']),     'lbl' => 'Total Leads',       'cls' => 'cyan'],
        ['val' => number_format($summary['Total Converted']), 'lbl' => 'Converted',         'cls' => 'green'],
        ['val' => $summary['Overall Conv %'],                 'lbl' => 'Conv. Rate',        'cls' => 'violet'],
        ['val' => $summary['Grade A Sources'],                'lbl' => 'Grade A Sources',   'cls' => 'green'],
        ['val' => $summary['Grade D Sources'],                'lbl' => 'Grade D Sources',   'cls' => 'amber'],
    ];
@endphp
<div class="kpi-strip">
    @foreach($kpis as $k)
    <div class="kpi-box {{ $k['cls'] }}">
        <span class="kpi-val">{{ $k['val'] }}</span>
        <span class="kpi-lbl">{{ $k['lbl'] }}</span>
    </div>
    @endforeach
</div>

<div class="section-lbl">Full Source Performance Matrix — {{ $periodLabel }}</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:24px">#</th>
                <th>Source</th>
                <th class="num">Total</th>
                <th class="num">Converted</th>
                <th class="num">Active</th>
                <th class="num">Lost</th>
                <th style="min-width:75px">Conv. Rate</th>
                <th style="min-width:65px">Share</th>
                <th class="num">Avg Days</th>
                <th style="width:32px">Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
            @php
                $rCol = $row['rate'] >= 10 ? '#10b981' : ($row['rate'] >= 5 ? '#f59e0b' : '#ef4444');
            @endphp
            <tr>
                <td class="num" style="color:#94a3b8;font-weight:600">#{{ $i + 1 }}</td>
                <td style="font-weight:600">{{ ucfirst($row['source']) }}</td>
                <td class="num" style="font-weight:700">{{ number_format($row['total']) }}</td>
                <td class="num" style="font-weight:700;color:#10b981">{{ number_format($row['converted']) }}</td>
                <td class="num" style="color:#06b6d4">{{ number_format($row['active']) }}</td>
                <td class="num" style="color:#ef4444">{{ number_format($row['lost']) }}</td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ min($row['rate'] * 5, 100) }}%;background:{{ $rCol }}"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:700;color:{{ $rCol }};min-width:30px">{{ $row['rate'] }}%</span>
                    </div>
                </td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ $row['share'] }}%;background:#6366f1"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:600;color:#6366f1;min-width:26px">{{ $row['share'] }}%</span>
                    </div>
                </td>
                <td class="num">
                    @if($row['avg_days'] !== null)
                        <span style="color:#f59e0b;font-weight:600">{{ $row['avg_days'] }}d</span>
                    @else —
                    @endif
                </td>
                <td style="text-align:center">
                    <span class="badge g-{{ $row['grade'] }}">{{ $row['grade'] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(!empty($courseRows) && count($courseRows) > 0)
<div class="section-lbl" style="color:#8b5cf6">Course Enquiry Analytics — {{ $periodLabel }}</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:24px">#</th>
                <th>Course</th>
                <th class="num">Enquiries</th>
                <th class="num">Converted</th>
                <th class="num">Active</th>
                <th class="num">Lost</th>
                <th style="min-width:75px">Conv. Rate</th>
                <th style="min-width:65px">Share</th>
                <th class="num">Avg Days</th>
                <th style="width:32px">Grade</th>
            </tr>
        </thead>
        <tbody>
            @php
                $courseTotal = collect($courseRows instanceof \Illuminate\Support\Collection ? $courseRows->all() : (array)$courseRows);
                $ctAll = $courseTotal->sum(fn($r) => is_array($r) ? ($r['total'] ?? 0) : (int)($r->total_leads ?? 0));
            @endphp
            @foreach($courseRows as $i => $cr)
            @php
                $cTotal = is_array($cr) ? ($cr['total'] ?? 0) : (int)($cr->total_leads ?? 0);
                $cConv  = is_array($cr) ? ($cr['converted'] ?? 0) : (int)($cr->converted_leads ?? 0);
                $cLost  = is_array($cr) ? ($cr['lost'] ?? 0) : (int)($cr->lost_leads ?? 0);
                $cActive = is_array($cr) ? ($cr['active'] ?? 0) : (int)($cr->active_leads ?? 0);
                $cRate  = is_array($cr) ? ($cr['rate'] ?? 0) : ($cTotal > 0 ? round(($cConv / $cTotal) * 100, 2) : 0);
                $cShare = $ctAll > 0 ? round(($cTotal / $ctAll) * 100, 1) : 0;
                $cDays  = is_array($cr) ? ($cr['avg_days'] ?? null) : ($cr->avg_days !== null ? round($cr->avg_days, 1) : null);
                $cGrade = is_array($cr) ? ($cr['grade'] ?? 'D') : ($cRate >= 10 ? 'A' : ($cRate >= 5 ? 'B' : ($cRate >= 1 ? 'C' : 'D')));
                $cName  = is_array($cr) ? ($cr['course'] ?? 'Unknown') : ($cr->course_name ?? 'Unknown');
                $rCol   = $cRate >= 10 ? '#10b981' : ($cRate >= 5 ? '#f59e0b' : '#ef4444');
            @endphp
            <tr>
                <td class="num" style="color:#94a3b8;font-weight:600">#{{ $i + 1 }}</td>
                <td style="font-weight:600">{{ $cName }}</td>
                <td class="num" style="font-weight:700">{{ number_format($cTotal) }}</td>
                <td class="num" style="font-weight:700;color:#10b981">{{ number_format($cConv) }}</td>
                <td class="num" style="color:#06b6d4">{{ number_format($cActive) }}</td>
                <td class="num" style="color:#ef4444">{{ number_format($cLost) }}</td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ min($cRate * 5, 100) }}%;background:{{ $rCol }}"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:700;color:{{ $rCol }};min-width:30px">{{ $cRate }}%</span>
                    </div>
                </td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:{{ $cShare }}%;background:#8b5cf6"></div>
                        </div>
                        <span style="font-size:7.5px;font-weight:600;color:#8b5cf6;min-width:26px">{{ $cShare }}%</span>
                    </div>
                </td>
                <td class="num">
                    @if($cDays !== null)
                        <span style="color:#f59e0b;font-weight:600">{{ $cDays }}d</span>
                    @else —
                    @endif
                </td>
                <td style="text-align:center">
                    <span class="badge g-{{ $cGrade }}">{{ $cGrade }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    Edu CRM &nbsp;·&nbsp; Lead Source Report &nbsp;·&nbsp; {{ $periodLabel }} &nbsp;·&nbsp; {{ $generatedAt }}
</div>

</body>
</html>
