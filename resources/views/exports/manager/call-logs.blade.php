<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1e293b; background: #fff; }

.hdr { background: linear-gradient(135deg,#1e3a6e,#0f172a); color: #fff; padding: 11px 16px 10px; margin-bottom: 10px; }
.hdr-title { font-size: 14px; font-weight: 700; letter-spacing: .2px; }
.hdr-meta  { font-size: 7.5px; opacity: .82; margin-top: 4px; display: flex; gap: 16px; }
.hdr-badge { background: rgba(255,255,255,.18); border-radius: 3px; padding: 1px 7px; }

.kpi-strip { display: flex; gap: 8px; margin: 0 14px 10px; }
.kpi-box   { flex: 1; border-radius: 4px; padding: 6px 10px; text-align: center; }
.kpi-box.total     { background: #e0f2fe; border: 1px solid #7dd3fc; }
.kpi-box.inbound   { background: #d1fae5; border: 1px solid #6ee7b7; }
.kpi-box.outbound  { background: #ede9fe; border: 1px solid #c4b5fd; }
.kpi-box.missed    { background: #fee2e2; border: 1px solid #fca5a5; }
.kpi-val { font-size: 16px; font-weight: 700; display: block; line-height: 1.2; }
.kpi-box.total .kpi-val    { color: #0369a1; }
.kpi-box.inbound .kpi-val  { color: #065f46; }
.kpi-box.outbound .kpi-val { color: #6d28d9; }
.kpi-box.missed .kpi-val   { color: #dc2626; }
.kpi-lbl { font-size: 7px; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; display: block; color: #64748b; }

.section-lbl { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px;
               color: #1e3a6e; margin: 0 14px 5px; }

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

.badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7px; font-weight: 700; text-transform: uppercase; }
.badge-outbound  { background: #ede9fe; color: #6d28d9; }
.badge-inbound   { background: #d1fae5; color: #065f46; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-answered  { background: #dbeafe; color: #1e40af; }
.badge-missed, .badge-failed, .badge-busy, .badge-no-answer { background: #fee2e2; color: #dc2626; }
.badge-canceled  { background: #fef9c3; color: #713f12; }

.dur { font-family: monospace; }
.footer { margin-top: 12px; text-align: center; font-size: 7px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px; }
.empty  { text-align: center; padding: 14px; color: #94a3b8; font-size: 8px; }
</style>
</head>
<body>

<div class="hdr">
    <div class="hdr-title">{{ $title }}</div>
    <div class="hdr-meta">
        <span class="hdr-badge">Manager: {{ $manager }}</span>
        <span class="hdr-badge">Generated: {{ $generatedAt }}</span>
        <span class="hdr-badge">{{ count($rows) }} record{{ count($rows) !== 1 ? 's' : '' }}</span>
    </div>
</div>

<div class="kpi-strip">
    <div class="kpi-box total">
        <span class="kpi-val">{{ $kpi['total'] }}</span>
        <span class="kpi-lbl">Total Calls</span>
    </div>
    <div class="kpi-box inbound">
        <span class="kpi-val">{{ $kpi['inbound'] }}</span>
        <span class="kpi-lbl">Inbound</span>
    </div>
    <div class="kpi-box outbound">
        <span class="kpi-val">{{ $kpi['outbound'] }}</span>
        <span class="kpi-lbl">Outbound</span>
    </div>
    <div class="kpi-box missed">
        <span class="kpi-val">{{ $kpi['missed'] }}</span>
        <span class="kpi-lbl">Missed / Failed</span>
    </div>
</div>

<div class="section-lbl">Call Log Details</div>
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
                @php
                    $hdr = $headers[$ci] ?? '';
                    $isType   = $hdr === 'Type';
                    $isStatus = $hdr === 'Status';
                    $isDur    = $hdr === 'Duration';
                    $cls = '';
                    if ($isType)   $cls = 'badge badge-' . strtolower($cell);
                    if ($isStatus) $cls = 'badge badge-' . strtolower(str_replace([' ','-'], ['-','-'], $cell));
                @endphp
                <td>
                    @if($isType || $isStatus)
                        <span class="{{ $cls }}">{{ $cell }}</span>
                    @elseif($isDur)
                        <span class="dur">{{ $cell }}</span>
                    @else
                        {{ $cell }}
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($headers) }}" class="empty">No call logs found for the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    Edu CRM &nbsp;·&nbsp; {{ $title }} &nbsp;·&nbsp; {{ $manager }} &nbsp;·&nbsp; {{ $generatedAt }}
</div>

</body>
</html>
