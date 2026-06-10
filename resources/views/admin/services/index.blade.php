@extends('layouts.app')

@section('page_title', 'Service Management')

@php
$IC = [
    'filter'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'search'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>',
    'refresh-cw' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H3v5"/></svg>',
    'plus'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="12" x2="12" y1="5" y2="19" stroke-linecap="round"/><line x1="5" x2="19" y1="12" y2="12" stroke-linecap="round"/></svg>',
    'edit'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>',
    'trash'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
    'trending-up' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline stroke-linecap="round" stroke-linejoin="round" points="16 7 22 7 22 13"/></svg>',
    'box'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
    'list'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="8" x2="21" y1="6" y2="6" stroke-linecap="round"/><line x1="8" x2="21" y1="12" y2="12" stroke-linecap="round"/><line x1="8" x2="21" y1="18" y2="18" stroke-linecap="round"/><line x1="3" x2="3.01" y1="6" y2="6" stroke-linecap="round"/><line x1="3" x2="3.01" y1="12" y2="12" stroke-linecap="round"/><line x1="3" x2="3.01" y1="18" y2="18" stroke-linecap="round"/></svg>',
    'tag'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7" stroke-linecap="round"/></svg>',
    'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline stroke-linecap="round" stroke-linejoin="round" points="22 4 12 14.01 9 11.01"/></svg>',
    'x-circle'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15" stroke-linecap="round"/><line x1="9" x2="15" y1="9" y2="15" stroke-linecap="round"/></svg>',
    'bar-chart'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="18" x2="18" y1="20" y2="10" stroke-linecap="round"/><line x1="12" x2="12" y1="20" y2="4" stroke-linecap="round"/><line x1="6" x2="6" y1="20" y2="14" stroke-linecap="round"/></svg>',
];
function ico($IC, $name, $size=14) {
    if(!isset($IC[$name])) return '';
    return str_replace('<svg ','<svg width="'.$size.'" height="'.$size.'" ',$IC[$name]);
}
@endphp

@section('header_actions')
    <a href="{{ route('admin.services.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff!important;border:none;border-radius:8px;font-weight:600;padding:7px 14px;font-size:12px;text-decoration:none;font-family:'Poppins',sans-serif;user-select:none;-webkit-user-select:none;cursor:pointer;white-space:nowrap;">
        {!! ico($IC,'plus',14) !!}
        New Service
    </a>
@endsection

@section('content')

@if (session('success'))
<div style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:8px;font-size:12.5px;color:#065F46;font-family:'Poppins',sans-serif;">
    {!! ico($IC,'check-circle',14) !!}
    {{ session('success') }}
</div>
@endif

{{-- ── KPI StatRow ── --}}
<div class="cm-kpi-grid mb-3">
    <div class="cm-sr cm-sr-or">
        <div class="cm-sr-icon">{!! ico($IC,'box',15) !!}</div>
        <div>
            <div class="cm-sr-lbl">Total Services</div>
            <div class="cm-sr-val">{{ $totalServices }}</div>
        </div>
    </div>
    <div class="cm-sr cm-sr-wh">
        <div class="cm-sr-icon" style="background:#ECFDF5;color:#10B981;">{!! ico($IC,'check-circle',15) !!}</div>
        <div>
            <div class="cm-sr-lbl">Active</div>
            <div class="cm-sr-val">{{ $activeServices }}</div>
        </div>
    </div>
    <div class="cm-sr cm-sr-wh">
        <div class="cm-sr-icon" style="background:#FEF2F2;color:#EF4444;">{!! ico($IC,'x-circle',15) !!}</div>
        <div>
            <div class="cm-sr-lbl">Inactive</div>
            <div class="cm-sr-val">{{ $inactiveServices }}</div>
        </div>
    </div>
    <div class="cm-sr cm-sr-wh">
        <div class="cm-sr-icon" style="background:#F5F3FF;color:#7C3AED;">{!! ico($IC,'tag',15) !!}</div>
        <div>
            <div class="cm-sr-lbl">With Code</div>
            <div class="cm-sr-val">{{ $withCode }}</div>
        </div>
    </div>
</div>

{{-- ── 2-column: filter left | table right ── --}}
<div class="cm-body">

    {{-- LEFT: filter panel ── --}}
    <div class="cm-left-panel">

        <div class="cm-panel-head">
            <div class="cm-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'filter',13) !!}</span>
            <span class="cm-panel-title">Filters</span>
        </div>

        <form method="GET" action="{{ route('admin.services.index') }}" class="cm-filter-form" id="filterForm">
            <div class="cm-fi-wrap">
                <span class="cm-fi-ico">{!! ico($IC,'search',13) !!}</span>
                <input type="text" name="search" class="cm-fi"
                       value="{{ request('search') }}"
                       placeholder="Name or code…">
            </div>
            <div>
                <label class="cm-fi-lbl">Status</label>
                <select name="status" class="cm-fi" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="cm-apply-btn">
                {!! ico($IC,'search',12) !!} Apply Filters
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('admin.services.index') }}" class="cm-reset-btn">
                {!! ico($IC,'refresh-cw',11) !!} Reset
            </a>
            @endif
        </form>

        <div style="height:1px;background:#F0F0F0;margin:0 12px;"></div>
        <div class="cm-panel-head" style="padding-top:10px;">
            <div class="cm-acc"></div>
            <span style="color:#FF5C00;display:flex;">{!! ico($IC,'bar-chart',13) !!}</span>
            <span class="cm-panel-title">Summary</span>
        </div>
        <div style="padding:0 12px 14px;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11.5px;color:#9CA3AF;font-family:'Poppins',sans-serif;">Active rate</span>
                <span style="font-size:12px;font-weight:700;color:#10B981;font-family:'Poppins',sans-serif;">
                    {{ $totalServices > 0 ? round($activeServices / $totalServices * 100) : 0 }}%
                </span>
            </div>
            <div style="height:5px;border-radius:99px;background:#F0F0F0;overflow:hidden;">
                <div style="height:100%;border-radius:99px;background:#10B981;width:{{ $totalServices > 0 ? round($activeServices / $totalServices * 100) : 0 }}%;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                <span style="font-size:11.5px;color:#9CA3AF;font-family:'Poppins',sans-serif;">Coded</span>
                <span style="font-size:12px;font-weight:700;color:#7C3AED;font-family:'Poppins',sans-serif;">
                    {{ $totalServices > 0 ? round($withCode / $totalServices * 100) : 0 }}%
                </span>
            </div>
            <div style="height:5px;border-radius:99px;background:#F0F0F0;overflow:hidden;">
                <div style="height:100%;border-radius:99px;background:#7C3AED;width:{{ $totalServices > 0 ? round($withCode / $totalServices * 100) : 0 }}%;"></div>
            </div>
        </div>
    </div>

    {{-- RIGHT: table card ── --}}
    <div class="cm-table-card">

        <div class="cm-table-head">
            <div style="display:flex;align-items:center;gap:9px;">
                <div class="cm-acc"></div>
                <span style="color:#FF5C00;display:flex;">{!! ico($IC,'box',14) !!}</span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#1D1D1D;">Service Management</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">{{ $services->total() }} {{ Str::plural('service', $services->total()) }} found</div>
                </div>
            </div>
            <span class="cm-badge">{{ $services->total() }}</span>
        </div>

        @if ($services->isEmpty())
        <div class="cm-empty">
            <div style="width:56px;height:56px;border-radius:14px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#FF5C00;opacity:.6;">{!! ico($IC,'box',28) !!}</div>
            <div style="font-size:14px;font-weight:700;color:#1D1D1D;margin-bottom:4px;">No services found</div>
            <div style="font-size:12px;color:#9CA3AF;margin-bottom:14px;">
                @if(request('search') || request('status'))
                    No services match your filters.
                @else
                    No services added yet.
                @endif
            </div>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.services.index') }}" class="cm-reset-btn" style="width:auto;padding:7px 16px;">Clear Filters</a>
            @else
                <a href="{{ route('admin.services.create') }}" class="cm-apply-btn" style="width:auto;padding:7px 16px;text-decoration:none;color:#fff;">Add First Service</a>
            @endif
        </div>
        @else
        <div class="cm-tbl-wrap">
            <table class="cm-tbl">
                <thead>
                    <tr>
                        <th style="width:38px;">#</th>
                        <th>Service Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th style="text-align:center;width:70px;">Order</th>
                        <th style="text-align:center;width:110px;">Status</th>
                        <th style="text-align:right;padding-right:14px;width:90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $i => $service)
                    @php
                        $initial   = strtoupper(substr($service->name, 0, 1));
                        $avColors  = ['#FF5C00','#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4'];
                        $avBg      = $avColors[abs(($service->id ?? $i) % count($avColors))];
                    @endphp
                    <tr>
                        <td style="color:#9CA3AF;font-size:11px;font-weight:600;">{{ ($services->currentPage() - 1) * $services->perPage() + $loop->iteration }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:9px;">
                                <div style="width:32px;height:32px;border-radius:9px;background:{{ $avBg }};color:#fff;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $initial }}</div>
                                <div>
                                    <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;line-height:1.2;">{{ $service->name }}</div>
                                    @if($service->code)
                                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">{{ $service->code }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($service->code)
                                <span style="background:#F5F3FF;color:#7C3AED;font-size:10.5px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;display:inline-block;">{{ $service->code }}</span>
                            @else
                                <span style="color:#9CA3AF;">—</span>
                            @endif
                        </td>
                        <td style="max-width:220px;">
                            <span style="font-size:12px;color:#9CA3AF;">{{ $service->description ? Str::limit($service->description, 70) : '—' }}</span>
                        </td>
                        <td style="text-align:center;">
                            <span style="background:#F4F6F8;border:1px solid #F0F0F0;border-radius:6px;padding:2px 9px;font-size:11.5px;font-weight:700;color:#4B5563;display:inline-block;">{{ $service->sort_order }}</span>
                        </td>
                        <td style="text-align:center;">
                            <form action="{{ route('admin.services.toggle-status', $service->id) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" title="Click to toggle"
                                    class="cm-status-btn {{ $service->is_active ? 'cm-status-active' : 'cm-status-inactive' }}">
                                    <span class="cm-status-dot"></span>
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td style="padding-right:14px;">
                            <div style="display:flex;gap:5px;align-items:center;justify-content:flex-end;">
                                <a href="{{ route('admin.services.edit', $service->id) }}"
                                   class="cm-btn cm-btn-edit" title="Edit Service">{!! ico($IC,'edit',13) !!}</a>
                                <form action="{{ route('admin.services.destroy', $service->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete {{ addslashes($service->name) }}?')"
                                      style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="cm-btn cm-btn-del" title="Delete Service">{!! ico($IC,'trash',13) !!}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="cm-pager">
            <small style="color:#9CA3AF;font-size:11.5px;">
                @if($services->hasPages())
                    Showing {{ $services->firstItem() }}–{{ $services->lastItem() }} of {{ $services->total() }} services
                @else
                    Showing all {{ $services->total() }} {{ Str::plural('service', $services->total()) }}
                @endif
            </small>
            @if($services->hasPages())
                {{ $services->onEachSide(1)->links('pagination::bootstrap-5') }}
            @endif
        </div>
        @endif

    </div>

</div>

<style>
.cm-kpi-grid,.cm-body,.cm-left-panel,.cm-table-card,.cm-tbl,.cm-pager,.cm-filter-form { font-family:'Poppins',sans-serif!important; }
.cm-kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px; }
@media(max-width:1200px){ .cm-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px){ .cm-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
.cm-sr { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px; }
.cm-sr-or { background:#FF5C00;box-shadow:0 4px 14px rgba(255,92,0,.22); }
.cm-sr-wh { background:#FEFEFE;border:1px solid #F0F0F0;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.cm-sr-icon { width:32px;height:32px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
.cm-sr-or .cm-sr-icon { background:rgba(255,255,255,.18);color:#fff; }
.cm-sr-wh .cm-sr-icon { background:#FFF7ED;color:#FF5C00; }
.cm-sr-lbl { font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1px; }
.cm-sr-or .cm-sr-lbl { color:rgba(255,255,255,.75); }
.cm-sr-wh .cm-sr-lbl { color:#9CA3AF; }
.cm-sr-val { font-size:20px;font-weight:800;line-height:1; }
.cm-sr-or .cm-sr-val { color:#fff; }
.cm-sr-wh .cm-sr-val { color:#1D1D1D; }
.cm-body { display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start; }
@media(max-width:900px){ .cm-body{ grid-template-columns:1fr; } }
.cm-left-panel { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.cm-panel-head { display:flex;align-items:center;gap:7px;padding:12px 14px 10px; }
.cm-acc { width:3px;height:20px;background:#FF5C00;border-radius:2px;flex-shrink:0; }
.cm-panel-title { font-size:12px;font-weight:700;color:#1D1D1D; }
.cm-filter-form { padding:4px 12px 14px;display:flex;flex-direction:column;gap:9px; }
.cm-fi-lbl { font-size:9.5px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px; }
.cm-fi-wrap { position:relative; }
.cm-fi-ico { position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9CA3AF;pointer-events:none;display:flex; }
.cm-fi { width:100%;height:34px;border-radius:8px;border:1px solid #E5E7EB;font-size:12.5px;color:#1D1D1D;background:#FAFBFC;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s,box-shadow .15s;box-sizing:border-box; }
.cm-fi-wrap .cm-fi { padding-left:32px; }
.cm-fi:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09);background:#fff; }
.cm-apply-btn { width:100%;background:#FF5C00;color:#fff!important;border:none;border-radius:8px;padding:8px;font-size:12.5px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;font-family:'Poppins',sans-serif!important; }
.cm-apply-btn:hover { background:#e05200;color:#fff!important; }
.cm-reset-btn { width:100%;background:#FEFEFE;color:#374151;border:1px solid #E5E7EB;border-radius:8px;padding:7px;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;text-decoration:none;font-family:'Poppins',sans-serif!important; }
.cm-reset-btn:hover { background:#F3F4F6; }
.cm-table-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.cm-table-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 18px;border-bottom:1px solid #F0F0F0;background:linear-gradient(135deg,#FAFBFC,#FEFEFE); }
.cm-badge { background:#FFF7ED;color:#FF5C00;border:1px solid #FED7AA;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px; }
.cm-tbl-wrap { overflow-y:auto;overflow-x:auto;max-height:520px; }
.cm-tbl-wrap::-webkit-scrollbar { width:5px; }
.cm-tbl-wrap::-webkit-scrollbar-thumb { background:#D1D5DB;border-radius:4px; }
.cm-tbl-wrap::-webkit-scrollbar-thumb:hover { background:#FF5C00; }
.cm-tbl { width:100%;border-collapse:separate;border-spacing:0; }
.cm-tbl thead th { position:sticky;top:0;z-index:2;background:#F4F6F8;color:#9CA3AF;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:10px 13px;white-space:nowrap;border-bottom:2px solid #F0F0F0; }
.cm-tbl tbody td { padding:11px 13px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F4F6F8; }
.cm-tbl tbody tr:last-child td { border-bottom:none; }
.cm-tbl tbody tr:nth-child(even) td { background:#FAFBFC; }
.cm-tbl tbody tr:hover td { background:#FFF7ED!important; }
.cm-tbl tbody tr:hover td:first-child { border-left:3px solid #FF5C00;padding-left:15px; }
.cm-btn { width:28px;height:28px;border-radius:7px;border:1px solid #E5E7EB;background:#F9FAFB;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;color:#6B7280; }
.cm-btn:hover { transform:translateY(-1px); }
.cm-btn-edit { color:#FF5C00;border-color:#FED7AA; }
.cm-btn-edit:hover { background:#FFF7ED; }
.cm-btn-del { color:#EF4444;border-color:#FECACA; }
.cm-btn-del:hover { background:#FEF2F2; }
.cm-status-btn { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;border:none;cursor:pointer;font-size:11.5px;font-weight:600;transition:all .2s;font-family:'Poppins',sans-serif!important; }
.cm-status-dot { width:6px;height:6px;border-radius:50%;display:inline-block; }
.cm-status-btn.cm-status-active  { background:rgba(16,185,129,.12);color:#059669; }
.cm-status-btn.cm-status-active .cm-status-dot  { background:#10b981;box-shadow:0 0 0 2px rgba(16,185,129,.3); }
.cm-status-btn.cm-status-inactive { background:rgba(239,68,68,.09);color:#dc2626; }
.cm-status-btn.cm-status-inactive .cm-status-dot { background:#ef4444; }
.cm-empty { text-align:center;padding:52px 16px; }
.cm-pager { padding:10px 16px;border-top:1px solid #F0F0F0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:9px;background:#FAFBFC; }
.cm-pager .page-link { background:#FEFEFE;border-color:#E5E7EB;color:#374151;font-size:11.5px;border-radius:7px;padding:4px 9px;font-family:'Poppins',sans-serif!important; }
.cm-pager .page-item.active .page-link { background:#FF5C00;border-color:#FF5C00;color:#fff; }
.cm-pager .page-item.disabled .page-link { opacity:.4; }
</style>

@endsection
