<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1e293b; background: #fff; }

/* Header */
.hdr { background: #6366f1; color: #fff; padding: 11px 16px 10px; margin-bottom: 12px; }
.hdr-title { font-size: 14px; font-weight: 700; letter-spacing: .2px; }
.hdr-meta  { font-size: 7.5px; opacity: .82; margin-top: 4px; display: flex; gap: 16px; }
.hdr-badge { background: rgba(255,255,255,.18); border-radius: 3px; padding: 1px 7px; }

/* Section label */
.section-lbl { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px;
               color: #6366f1; margin: 0 14px 5px; padding-top: 2px; }

/* Table */
.tbl-wrap { margin: 0 14px; }
table { width: 100%; border-collapse: collapse; }
thead th {
    background: #0f172a; color: #fff; font-weight: 700;
    padding: 5px 6px; font-size: 7.5px; text-transform: uppercase;
    letter-spacing: .3px; border: none; text-align: left;
}
thead th:first-child { border-radius: 2px 0 0 0; }
thead th:last-child  { border-radius: 0 2px 0 0; }
tbody tr:nth-child(even) { background: #f8fafc; }
tbody tr:nth-child(odd)  { background: #fff; }
tbody td { padding: 4.5px 6px; font-size: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }

/* Summary KPI strip */
.kpi-strip { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 14px 12px; }
.kpi-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 10px; min-width: 80px; }
.kpi-val  { font-size: 13px; font-weight: 700; color: #6366f1; display: block; line-height: 1.2; }
.kpi-lbl  { font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-top: 1px; display: block; }

/* Footer */
.footer { margin-top: 14px; text-align: center; font-size: 7px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px; }

/* Empty state */
.empty { text-align: center; padding: 14px; color: #94a3b8; font-size: 8px; }

/* Numeric columns right-align */
.num { text-align: right; font-variant-numeric: tabular-nums; }
</style>
</head>
<body>

{{-- ── Header ── --}}
<div class="hdr">
    <div class="hdr-title">{{ $title }}</div>
    <div class="hdr-meta">
        <span class="hdr-badge">Manager: {{ $manager }}</span>
        <span class="hdr-badge">Period: {{ $period }}</span>
        <span class="hdr-badge">Generated: {{ $generatedAt }}</span>
        <span class="hdr-badge">{{ count($rows) }} record{{ count($rows) !== 1 ? 's' : '' }}</span>
    </div>
</div>

{{-- ── KPI summary strip (first 4 numeric columns aggregated) ── --}}
@if(count($rows) > 0)
@php
    $numericCols = [];
    foreach ($headers as $hi => $hdr) {
        $colVals = array_filter(array_column($rows, $hi), fn($v) => is_numeric($v));
        if (count($colVals) > 0 && count($colVals) >= count($rows) * 0.5) {
            $numericCols[$hdr] = array_sum($colVals);
        }
        if (count($numericCols) >= 6) break;
    }
@endphp
@if(count($numericCols))
<div class="kpi-strip">
    @foreach($numericCols as $lbl => $val)
    <div class="kpi-box">
        <span class="kpi-val">{{ is_float($val) ? number_format($val, 1) : number_format($val) }}</span>
        <span class="kpi-lbl">{{ $lbl }}</span>
    </div>
    @endforeach
</div>
@endif
@endif

{{-- ── Data table ── --}}
<div class="section-lbl">Report Data</div>
<div class="tbl-wrap">
    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $ci => $cell)
                @php $isNum = is_numeric($cell) && !in_array($headers[$ci] ?? '', ['Lead Code', 'Phone', 'Date', 'Created At', 'Day', 'Called At']); @endphp
                <td class="{{ $isNum ? 'num' : '' }}">{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($headers) }}" class="empty">No records found for the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    Edu CRM &nbsp;·&nbsp; {{ $title }} &nbsp;·&nbsp; {{ $manager }} &nbsp;·&nbsp; {{ $generatedAt }}
</div>

</body>
</html>
