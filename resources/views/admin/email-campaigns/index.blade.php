@extends('layouts.app')

@section('page_title', 'Email Campaigns')

@php
$IC = [
    'filter'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'search'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>',
    'refresh-cw' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H3v5"/></svg>',
    'plus'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="12" x2="12" y1="5" y2="19" stroke-linecap="round"/><line x1="5" x2="19" y1="12" y2="12" stroke-linecap="round"/></svg>',
    'mail'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="20" height="16" x="2" y="4" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
    'settings' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'list'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="8" x2="21" y1="6" y2="6" stroke-linecap="round"/><line x1="8" x2="21" y1="12" y2="12" stroke-linecap="round"/><line x1="8" x2="21" y1="18" y2="18" stroke-linecap="round"/><line x1="3" x2="3.01" y1="6" y2="6" stroke-linecap="round"/><line x1="3" x2="3.01" y1="12" y2="12" stroke-linecap="round"/><line x1="3" x2="3.01" y1="18" y2="18" stroke-linecap="round"/></svg>',
    'check'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="20 6 9 17 4 12"/></svg>',
    'edit'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>',
    'trash'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
    'eye'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>',
    'users'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'send'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="22" x2="11" y1="2" y2="13" stroke-linecap="round"/><polygon stroke-linecap="round" stroke-linejoin="round" points="22 2 15 22 11 13 2 9 22 2"/></svg>',
    'zap'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon stroke-linecap="round" stroke-linejoin="round" points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
    'trending-up' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline stroke-linecap="round" stroke-linejoin="round" points="16 7 22 7 22 13"/></svg>',
    'bar-chart' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="18" x2="18" y1="20" y2="10" stroke-linecap="round"/><line x1="12" x2="12" y1="20" y2="4" stroke-linecap="round"/><line x1="6" x2="6" y1="20" y2="14" stroke-linecap="round"/></svg>',
    'x-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15" stroke-linecap="round"/><line x1="9" x2="15" y1="9" y2="15" stroke-linecap="round"/></svg>',
];
function ico($IC, $name, $size=14) {
    if(!isset($IC[$name])) return '';
    return str_replace('<svg ','<svg width="'.$size.'" height="'.$size.'" ',$IC[$name]);
}

/* ── KPI totals across all campaigns ── */
$totalCampaigns  = $campaigns instanceof \Illuminate\Pagination\LengthAwarePaginator ? $campaigns->total() : $campaigns->count();
$totalSent       = $campaigns->sum('sent_count');
$totalOpened     = $campaigns->sum('opened_count');
$totalFailed     = $campaigns->sum('failed_count');
@endphp

@section('header_actions')
    <a href="{{ route('admin.email-campaigns.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff!important;border:none;border-radius:8px;font-weight:600;padding:7px 14px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
        {!! ico($IC,'plus',14) !!}
        New Campaign
    </a>
@endsection

@section('content')

{{-- ── Flash message ── --}}
@if (session('success'))
    <div style="background:#ECFDF5;border:1px solid #6EE7B7;border-radius:10px;padding:11px 16px;margin-bottom:14px;font-size:13px;color:#065F46;display:flex;align-items:center;gap:8px;font-family:'Poppins',sans-serif;">
        {!! ico($IC,'check',14) !!}
        {{ session('success') }}
    </div>
@endif

{{-- ── KPI StatRow — full width top ── --}}
<div class="ec-kpi-grid mb-3">
    <div class="ec-sr ec-sr-or">
        <div class="ec-sr-icon">{!! ico($IC,'mail',15) !!}</div>
        <div>
            <div class="ec-sr-lbl">Total Campaigns</div>
            <div class="ec-sr-val">{{ $totalCampaigns }}</div>
        </div>
    </div>
    <div class="ec-sr ec-sr-wh">
        <div class="ec-sr-icon" style="background:#ECFDF5;color:#10B981;">{!! ico($IC,'send',15) !!}</div>
        <div>
            <div class="ec-sr-lbl">Emails Sent</div>
            <div class="ec-sr-val">{{ number_format($totalSent) }}</div>
        </div>
    </div>
    <div class="ec-sr ec-sr-wh">
        <div class="ec-sr-icon" style="background:#EFF6FF;color:#3B82F6;">{!! ico($IC,'eye',15) !!}</div>
        <div>
            <div class="ec-sr-lbl">Opened</div>
            <div class="ec-sr-val">{{ number_format($totalOpened) }}</div>
        </div>
    </div>
    <div class="ec-sr ec-sr-wh">
        <div class="ec-sr-icon" style="background:#FEF2F2;color:#EF4444;">{!! ico($IC,'x-circle',15) !!}</div>
        <div>
            <div class="ec-sr-lbl">Failed</div>
            <div class="ec-sr-val">{{ number_format($totalFailed) }}</div>
        </div>
    </div>
</div>

{{-- ── 2-column: filter nav left | table right ── --}}
<div class="ec-body">

    {{-- LEFT panel ── --}}
    <div class="ec-left-panel">

        {{-- Section head ── --}}
        <div class="ec-panel-head">
            <div class="ec-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'filter',13) !!}</span>
            <span class="ec-panel-title">Filters</span>
        </div>

        {{-- Filter form ── --}}
        <form method="GET" action="{{ url()->current() }}" class="ec-filter-form">
            <div>
                <label class="ec-fi-lbl">Status</label>
                <select name="status" class="ec-fi">
                    <option value="">All Statuses</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="sending"   {{ request('status') === 'sending'   ? 'selected' : '' }}>Sending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="ec-fi-wrap">
                <span class="ec-fi-ico">{!! ico($IC,'search',13) !!}</span>
                <input type="text" name="search" class="ec-fi"
                       value="{{ request('search') }}"
                       placeholder="Campaign name…">
            </div>
            <button type="submit" class="ec-apply-btn">
                {!! ico($IC,'search',12) !!} Apply Filters
            </button>
            @if(request('search') || request('status'))
            <a href="{{ url()->current() }}" class="ec-reset-btn">
                {!! ico($IC,'refresh-cw',11) !!} Reset
            </a>
            @endif
        </form>

        {{-- Divider ── --}}
        <div style="height:1px;background:#F0F0F0;margin:4px 12px 10px;"></div>

        {{-- Quick stats ── --}}
        <div class="ec-panel-head" style="padding-top:2px;">
            <div class="ec-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'bar-chart',13) !!}</span>
            <span class="ec-panel-title">Overview</span>
        </div>
        <div class="ec-quick-stats">
            @php
                $statuses = ['draft' => ['#9CA3AF','#F3F4F6'], 'scheduled' => ['#3B82F6','#EFF6FF'], 'sending' => ['#D97706','#FFFBEB'], 'completed' => ['#10B981','#ECFDF5'], 'failed' => ['#EF4444','#FEF2F2']];
            @endphp
            @foreach($statuses as $st => [$color, $bg])
            @php $cnt = $campaigns->where('status', $st)->count(); @endphp
            <div class="ec-qs-row">
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                <span class="ec-qs-lbl">{{ ucfirst($st) }}</span>
                <span class="ec-qs-val" style="background:{{ $bg }};color:{{ $color }};">{{ $cnt }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- RIGHT — table card ── --}}
    <div class="ec-table-card">
        {{-- SHead ── --}}
        <div class="ec-table-head">
            <div style="display:flex;align-items:center;gap:9px;">
                <div class="ec-acc"></div>
                <span style="color:#FF5C00;display:flex;">{!! ico($IC,'mail',14) !!}</span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#1D1D1D;">Email Campaigns</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">
                        {{ $campaigns instanceof \Illuminate\Pagination\LengthAwarePaginator ? $campaigns->total() : $campaigns->count() }}
                        campaign{{ ($campaigns instanceof \Illuminate\Pagination\LengthAwarePaginator ? $campaigns->total() : $campaigns->count()) !== 1 ? 's' : '' }} found
                    </div>
                </div>
            </div>
            <span class="ec-badge">{{ $campaigns instanceof \Illuminate\Pagination\LengthAwarePaginator ? $campaigns->total() : $campaigns->count() }}</span>
        </div>

        @if ($campaigns->isEmpty())
            {{-- Empty state ── --}}
            <div class="ec-empty">
                <div style="width:56px;height:56px;border-radius:14px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#FF5C00;opacity:.6;">{!! ico($IC,'mail',28) !!}</div>
                <div style="font-size:14px;font-weight:700;color:#1D1D1D;margin-bottom:4px;">No email campaigns yet</div>
                <div style="font-size:12px;color:#9CA3AF;margin-bottom:14px;">Create your first campaign to start reaching your audience.</div>
                <a href="{{ route('admin.email-campaigns.create') }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff!important;border-radius:8px;font-weight:600;padding:7px 16px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
                    {!! ico($IC,'plus',13) !!} Create First Campaign
                </a>
            </div>
        @else
            {{-- Table ── --}}
            <div class="ec-tbl-wrap">
                <table class="ec-tbl">
                    <thead>
                        <tr>
                            <th style="width:32px;">#</th>
                            <th>Campaign</th>
                            <th>Template</th>
                            <th>Status</th>
                            <th>Recipients</th>
                            <th>Sent</th>
                            <th>Opened</th>
                            <th>Failed</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th style="text-align:right;padding-right:14px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $index => $ec)
                        @php
                            $statusMap = [
                                'draft'     => ['#9CA3AF', '#F3F4F6'],
                                'scheduled' => ['#3B82F6', '#EFF6FF'],
                                'sending'   => ['#D97706', '#FFFBEB'],
                                'completed' => ['#10B981', '#ECFDF5'],
                                'failed'    => ['#EF4444', '#FEF2F2'],
                            ];
                            [$stColor, $stBg] = $statusMap[$ec->status] ?? ['#9CA3AF', '#F3F4F6'];
                        @endphp
                        <tr>
                            <td style="color:#9CA3AF;font-size:11px;font-weight:600;">
                                {{ ($campaigns instanceof \Illuminate\Pagination\LengthAwarePaginator
                                    ? ($campaigns->currentPage() - 1) * $campaigns->perPage()
                                    : 0) + $index + 1 }}
                            </td>
                            <td>
                                <a href="{{ route('admin.email-campaigns.show', $ec) }}"
                                   style="font-size:12.5px;font-weight:700;color:#1D1D1D;text-decoration:none;">{{ $ec->name }}</a>
                                @if ($ec->description)
                                    <div style="font-size:11px;color:#9CA3AF;margin-top:2px;">{{ Str::limit($ec->description, 55) }}</div>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#9CA3AF;">{{ $ec->template_name }}</td>
                            <td>
                                <span style="background:{{ $stBg }};color:{{ $stColor }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;">
                                    {{ ucfirst($ec->status) }}
                                </span>
                            </td>
                            <td style="font-size:12.5px;font-weight:600;color:#1D1D1D;">{{ number_format($ec->recipients_count) }}</td>
                            <td>
                                <span style="font-size:12.5px;font-weight:700;color:#10B981;">{{ number_format($ec->sent_count) }}</span>
                                @if ($ec->recipients_count > 0)
                                    <span style="font-size:10.5px;color:#9CA3AF;display:block;">({{ $ec->delivery_rate }}%)</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:12.5px;font-weight:700;color:#3B82F6;">{{ number_format($ec->opened_count) }}</span>
                                @if ($ec->sent_count > 0)
                                    <span style="font-size:10.5px;color:#9CA3AF;display:block;">({{ $ec->open_rate }}%)</span>
                                @endif
                            </td>
                            <td>
                                @if ($ec->failed_count > 0)
                                    <span style="font-size:12.5px;font-weight:700;color:#EF4444;">{{ number_format($ec->failed_count) }}</span>
                                @else
                                    <span style="font-size:12px;color:#9CA3AF;">0</span>
                                @endif
                            </td>
                            <td style="font-size:11.5px;color:#9CA3AF;">{{ $ec->creator?->name ?? '—' }}</td>
                            <td style="font-size:11.5px;color:#9CA3AF;white-space:nowrap;">
                                {{ $ec->scheduled_at
                                    ? 'Sched: ' . $ec->scheduled_at->format('d M, h:i A')
                                    : $ec->created_at->format('d M Y') }}
                            </td>
                            <td style="padding-right:14px;">
                                <div style="display:flex;gap:5px;align-items:center;justify-content:flex-end;">
                                    <a href="{{ route('admin.email-campaigns.show', $ec) }}"
                                       class="ec-btn ec-btn-view" title="View Stats">{!! ico($IC,'bar-chart',13) !!}</a>
                                    <form action="{{ route('admin.email-campaigns.destroy', $ec) }}"
                                          method="POST" style="display:inline;"
                                          onsubmit="return confirm('Delete this campaign? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ec-btn ec-btn-del" title="Delete Campaign">{!! ico($IC,'trash',13) !!}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination ── --}}
            @if ($campaigns->hasPages())
            <div class="ec-pager">
                <small style="color:#9CA3AF;font-size:11.5px;">
                    Showing {{ $campaigns->firstItem() }}–{{ $campaigns->lastItem() }} of {{ $campaigns->total() }} results
                </small>
                {{ $campaigns->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
            @endif
        @endif
    </div>

</div>{{-- end ec-body --}}

<style>
.ec-kpi-grid,.ec-body,.ec-left-panel,.ec-table-card,.ec-tbl,.ec-pager,.ec-filter-form { font-family:'Poppins',sans-serif!important; }

/* ── KPI row ── */
.ec-kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px; }
@media(max-width:1200px){ .ec-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:768px) { .ec-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
.ec-sr { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px; }
.ec-sr-or { background:#FF5C00;box-shadow:0 4px 14px rgba(255,92,0,.22); }
.ec-sr-wh { background:#FEFEFE;border:1px solid #F0F0F0;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.ec-sr-icon { width:32px;height:32px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
.ec-sr-or .ec-sr-icon { background:rgba(255,255,255,.18);color:#fff; }
.ec-sr-wh .ec-sr-icon { background:#FFF7ED;color:#FF5C00; }
.ec-sr-lbl { font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1px; }
.ec-sr-or .ec-sr-lbl { color:rgba(255,255,255,.75); }
.ec-sr-wh .ec-sr-lbl { color:#9CA3AF; }
.ec-sr-val { font-size:20px;font-weight:800;line-height:1; }
.ec-sr-or .ec-sr-val { color:#fff; }
.ec-sr-wh .ec-sr-val { color:#1D1D1D; }

/* ── 2-col layout ── */
.ec-body { display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start; }
@media(max-width:900px){ .ec-body{ grid-template-columns:1fr; } }

/* ── Left panel ── */
.ec-left-panel { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.ec-panel-head { display:flex;align-items:center;gap:7px;padding:12px 14px 10px; }
.ec-acc { width:3px;height:20px;background:#FF5C00;border-radius:2px;flex-shrink:0; }
.ec-panel-title { font-size:12px;font-weight:700;color:#1D1D1D; }

/* ── Filter form ── */
.ec-filter-form { padding:0 12px 14px;display:flex;flex-direction:column;gap:9px; }
.ec-fi-lbl { font-size:9.5px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px; }
.ec-fi-wrap { position:relative; }
.ec-fi-ico { position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9CA3AF;pointer-events:none;display:flex; }
.ec-fi { width:100%;height:34px;border-radius:8px;border:1px solid #E5E7EB;font-size:12.5px;color:#1D1D1D;background:#FAFBFC;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s,box-shadow .15s;box-sizing:border-box; }
.ec-fi-wrap .ec-fi { padding-left:32px; }
.ec-fi:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09);background:#fff; }
.ec-apply-btn { width:100%;background:#FF5C00;color:#fff;border:none;border-radius:8px;padding:8px;font-size:12.5px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;font-family:'Poppins',sans-serif!important; }
.ec-apply-btn:hover { background:#e05200; }
.ec-reset-btn { width:100%;background:#FEFEFE;color:#374151;border:1px solid #E5E7EB;border-radius:8px;padding:7px;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;text-decoration:none;font-family:'Poppins',sans-serif!important; }
.ec-reset-btn:hover { background:#F3F4F6; }

/* ── Quick stats ── */
.ec-quick-stats { padding:0 12px 14px;display:flex;flex-direction:column;gap:7px; }
.ec-qs-row { display:flex;align-items:center;gap:8px; }
.ec-qs-lbl { flex:1;font-size:12px;color:#374151;font-weight:500; }
.ec-qs-val { font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px; }

/* ── Right table card ── */
.ec-table-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.ec-table-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 18px;border-bottom:1px solid #F0F0F0;background:linear-gradient(135deg,#FAFBFC,#FEFEFE); }
.ec-badge { background:#FFF7ED;color:#FF5C00;border:1px solid #FED7AA;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px; }
.ec-tbl-wrap { overflow-y:auto;overflow-x:auto;max-height:540px; }
.ec-tbl-wrap::-webkit-scrollbar { width:5px; }
.ec-tbl-wrap::-webkit-scrollbar-thumb { background:#D1D5DB;border-radius:4px; }
.ec-tbl-wrap::-webkit-scrollbar-thumb:hover { background:#FF5C00; }
.ec-tbl { width:100%;border-collapse:separate;border-spacing:0; }
.ec-tbl thead th { position:sticky;top:0;z-index:2;background:#F4F6F8;color:#9CA3AF;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:10px 13px;white-space:nowrap;border-bottom:2px solid #F0F0F0; }
.ec-tbl tbody td { padding:11px 13px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F4F6F8; }
.ec-tbl tbody tr:last-child td { border-bottom:none; }
.ec-tbl tbody tr:nth-child(even) td { background:#FAFBFC; }
.ec-tbl tbody tr:hover td { background:#FFF7ED!important; }
.ec-tbl tbody tr:hover td:first-child { border-left:3px solid #FF5C00;padding-left:15px; }

/* ── Action buttons ── */
.ec-btn { width:28px;height:28px;border-radius:7px;border:1px solid #E5E7EB;background:#F9FAFB;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;color:#6B7280; }
.ec-btn:hover { transform:translateY(-1px); }
.ec-btn-view { color:#3B82F6;border-color:#BFDBFE; }
.ec-btn-view:hover { background:#EFF6FF; }
.ec-btn-del { color:#EF4444;border-color:#FECACA; }
.ec-btn-del:hover { background:#FEF2F2; }

/* ── Empty state ── */
.ec-empty { text-align:center;padding:52px 16px; }

/* ── Pagination ── */
.ec-pager { padding:10px 16px;border-top:1px solid #F0F0F0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:9px;background:#FAFBFC; }
.ec-pager .page-link { background:#FEFEFE;border-color:#E5E7EB;color:#374151;font-size:11.5px;border-radius:7px;padding:4px 9px;font-family:'Poppins',sans-serif!important; }
.ec-pager .page-item.active .page-link { background:#FF5C00;border-color:#FF5C00;color:#fff; }
.ec-pager .page-item.disabled .page-link { opacity:.4; }
</style>

@endsection
