@extends('layouts.app')

@section('page_title', 'Automation - Lead Assignment Rules')

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
    'route'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="6" cy="19" r="3"/><circle cx="18" cy="5" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 19h4.5a3.5 3.5 0 0 0 0-7h-8a3.5 3.5 0 0 1 0-7H12"/></svg>',
    'timer'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="13" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4l2 2"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2"/></svg>',
    'layers'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon stroke-linecap="round" stroke-linejoin="round" points="12 2 2 7 12 12 22 7 12 2"/><polyline stroke-linecap="round" stroke-linejoin="round" points="2 17 12 22 22 17"/><polyline stroke-linecap="round" stroke-linejoin="round" points="2 12 12 17 22 12"/></svg>',
    'refresh'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 16v-5h5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 8v5h-5"/></svg>',
    'school'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 17l10 5 10-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12l10 5 10-5"/></svg>',
    'inbox'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="22 12 16 12 14 15 10 15 8 12 2 12"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',
    'save'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="17 21 17 13 7 13 7 21"/><polyline stroke-linecap="round" stroke-linejoin="round" points="7 3 7 8 15 8"/></svg>',
    'arrow-left' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="19" x2="5" y1="12" y2="12" stroke-linecap="round"/><polyline stroke-linecap="round" stroke-linejoin="round" points="12 19 5 12 12 5"/></svg>',
    'hourglass' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 22h14M5 2h14M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>',
    'agent'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>',
    'shield'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'pause'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect x="6" y="4" width="4" height="16" rx="1" stroke-linecap="round"/><rect x="14" y="4" width="4" height="16" rx="1" stroke-linecap="round"/></svg>',
    'play'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon stroke-linecap="round" stroke-linejoin="round" points="5 3 19 12 5 21 5 3"/></svg>',
    'circle-dot' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="1" fill="currentColor"/></svg>',
];
function ico($IC, $name, $size=14) {
    if(!isset($IC[$name])) return '';
    return str_replace('<svg ','<svg width="'.$size.'" height="'.$size.'" ',$IC[$name]);
}
@endphp

@section('content')

{{-- ── KPI StatRow ── --}}
<div class="la-kpi-grid mb-3">
    <div class="la-sr la-sr-or">
        <div class="la-sr-icon">{!! ico($IC,'route',15) !!}</div>
        <div>
            <div class="la-sr-lbl">Active Mode</div>
            <div class="la-sr-val" style="font-size:14px;font-weight:800;">
                @if($values['mode'] === 'round_robin') Round Robin
                @else Open Pool
                @endif
            </div>
        </div>
    </div>
    <div class="la-sr la-sr-wh">
        <div class="la-sr-icon" style="background:{{ $values['auto_assign_tc'] ? '#ECFDF5' : '#FFFBEB' }};color:{{ $values['auto_assign_tc'] ? '#10B981' : '#D97706' }};">{!! ico($IC,'timer',15) !!}</div>
        <div>
            <div class="la-sr-lbl">Auto-Assign TC</div>
            <div class="la-sr-val">{{ $values['auto_assign_tc'] ? $values['auto_assign_tc_hours'].'h timeout' : 'Disabled' }}</div>
        </div>
    </div>
    <div class="la-sr la-sr-wh">
        <div class="la-sr-icon" style="background:{{ $values['enabled'] ? '#ECFDF5' : '#FEF2F2' }};color:{{ $values['enabled'] ? '#10B981' : '#EF4444' }};">{!! ico($IC,'zap',15) !!}</div>
        <div>
            <div class="la-sr-lbl">Assignment</div>
            <div class="la-sr-val">{{ $values['enabled'] ? 'Enabled' : 'Disabled' }}</div>
        </div>
    </div>
    <div class="la-sr la-sr-wh">
        <div class="la-sr-icon" style="background:#ECFDF5;color:#10B981;">{!! ico($IC,'users',15) !!}</div>
        <div>
            <div class="la-sr-lbl">Managers</div>
            <div class="la-sr-val">{{ $managers->count() }} active</div>
        </div>
    </div>
</div>

{{-- ── Settings form ── --}}
<div class="la-body">

    <div class="la-left-col">

        {{-- Settings card ── --}}
        <div class="la-card mb-3">

            {{-- SHead ── --}}
            <div class="la-card-head">
                <div class="la-acc"></div>
                <span style="color:#FF5C00;display:flex;">{!! ico($IC,'settings',14) !!}</span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#1D1D1D;">Lead Assignment Rules</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;">Choose how incoming leads are distributed.</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.automation.lead-assignment.update') }}" style="padding:0 18px 18px;">
                @csrf

                {{-- ── Assignment Mode ── --}}
                <div class="la-section-pill mb-2">
                    <span style="display:flex;">{!! ico($IC,'settings',13) !!}</span> Assignment Mode
                </div>
                <div class="la-mode-grid mb-4" id="modePicker">
                    @foreach([
                        ['value' => 'round_robin', 'icon' => 'refresh', 'label' => 'Round Robin', 'desc' => 'Leads distributed evenly to all active managers in rotation.',              'color' => '#FF5C00', 'bg' => '#FFF7ED'],
                        ['value' => 'open_pool',   'icon' => 'inbox',   'label' => 'Open Pool',   'desc' => 'Leads go into an open pool. Any manager can claim a lead first-come first-served.', 'color' => '#7C3AED', 'bg' => '#F5F3FF'],
                    ] as $opt)
                    <label class="la-mode-card {{ $values['mode'] === $opt['value'] ? 'selected' : '' }}"
                           for="mode_{{ $opt['value'] }}"
                           style="{{ $values['mode'] === $opt['value'] ? '--mc:#'.(ltrim($opt['color'],'#')).';' : '' }}">
                        <input type="radio" name="mode" id="mode_{{ $opt['value'] }}"
                               value="{{ $opt['value'] }}" {{ $values['mode'] === $opt['value'] ? 'checked' : '' }}>
                        <div class="la-mode-check" style="background:{{ $opt['color'] }};">
                            {!! ico($IC,'check',11) !!}
                        </div>
                        <div class="la-mode-icon" style="background:{{ $opt['bg'] }};color:{{ $opt['color'] }};">
                            {!! ico($IC,$opt['icon'],18) !!}
                        </div>
                        <div style="font-size:12px;font-weight:700;color:#1D1D1D;margin-bottom:3px;">{{ $opt['label'] }}</div>
                        <div style="font-size:11px;color:#9CA3AF;line-height:1.45;">{{ $opt['desc'] }}</div>
                    </label>
                    @endforeach
                </div>

                {{-- ── General Settings ── --}}
                <div class="la-section-pill mb-2">
                    <span style="display:flex;">{!! ico($IC,'settings',13) !!}</span> General Settings
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">

                    <label class="la-toggle-row" for="enabled" style="cursor:pointer;">
                        <div class="la-toggle-icon" style="background:#FFF7ED;color:#FF5C00;">
                            {!! ico($IC,'zap',16) !!}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;margin-bottom:2px;">Enable auto lead assignment</div>
                            <div style="font-size:11px;color:#9CA3AF;line-height:1.4;">When disabled, leads must be manually assigned by admin.</div>
                        </div>
                        <div class="form-check form-switch ms-2 mb-0" style="flex-shrink:0;">
                            <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1"
                                   role="switch" {{ $values['enabled'] ? 'checked' : '' }}>
                        </div>
                    </label>

                    <label class="la-toggle-row" for="active_only" style="cursor:pointer;">
                        <div class="la-toggle-icon" style="background:#ECFDF5;color:#10B981;">
                            {!! ico($IC,'shield',16) !!}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;margin-bottom:2px;">Assign only to active managers</div>
                            <div style="font-size:11px;color:#9CA3AF;line-height:1.4;">Applies to round-robin assignment.</div>
                        </div>
                        <div class="form-check form-switch ms-2 mb-0" style="flex-shrink:0;">
                            <input class="form-check-input" type="checkbox" id="active_only" name="active_only" value="1"
                                   role="switch" {{ $values['active_only'] ? 'checked' : '' }}>
                        </div>
                    </label>

                </div>

                {{-- ── Auto-Assign Telecaller ── --}}
                <div class="la-section-pill mb-2">
                    <span style="display:flex;">{!! ico($IC,'agent',13) !!}</span> Auto-Assign Telecaller
                </div>
                <div style="margin-bottom:14px;" onclick="document.getElementById('auto_assign_tc').click()">
                    <label class="la-toggle-row" style="cursor:pointer;">
                        <div class="la-toggle-icon" style="background:#FFFBEB;color:#D97706;">
                            {!! ico($IC,'timer',16) !!}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12.5px;font-weight:700;color:#1D1D1D;margin-bottom:2px;">Enable auto-assign to telecaller</div>
                            <div style="font-size:11px;color:#9CA3AF;line-height:1.4;">
                                If a manager hasn't assigned a lead to a telecaller within the configured time,
                                the system auto-assigns via round-robin to all active telecallers.
                            </div>
                        </div>
                        <div class="form-check form-switch ms-2 mb-0" style="flex-shrink:0;">
                            <input class="form-check-input" type="checkbox" id="auto_assign_tc" name="auto_assign_tc"
                                   value="1" role="switch" {{ $values['auto_assign_tc'] ? 'checked' : '' }}
                                   onclick="event.stopPropagation()">
                        </div>
                    </label>
                </div>

                <div class="la-timeout-box mb-4">
                    <span style="color:#9CA3AF;display:flex;">{!! ico($IC,'hourglass',18) !!}</span>
                    <label class="mb-0" for="auto_assign_tc_hours" style="font-size:12.5px;font-weight:700;color:#1D1D1D;white-space:nowrap;">Timeout (hours)</label>
                    <input type="number" class="la-num-input" id="auto_assign_tc_hours" name="auto_assign_tc_hours"
                           value="{{ $values['auto_assign_tc_hours'] }}" min="1" max="720">
                    <span style="font-size:11.5px;color:#9CA3AF;">1–720 hours &bull; Runs every minute via scheduler.</span>
                </div>

                <div style="display:flex;gap:8px;align-items:center;">
                    <button type="submit" class="la-save-btn">
                        {!! ico($IC,'save',14) !!} Save Rules
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="la-back-btn">
                        {!! ico($IC,'arrow-left',13) !!} Back
                    </a>
                </div>
            </form>
        </div>

    </div>{{-- end left col --}}

</div>{{-- end la-body --}}

<style>
.la-kpi-grid,.la-body,.la-left-col,.la-right-col,.la-card,.la-tbl,.la-mode-grid { font-family:'Poppins',sans-serif!important; }

/* ── KPI row ── */
.la-kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px; }
@media(max-width:900px) { .la-kpi-grid{ grid-template-columns:repeat(2,1fr); } }
.la-sr { display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px; }
.la-sr-or { background:#FF5C00;box-shadow:0 4px 14px rgba(255,92,0,.22); }
.la-sr-wh { background:#FEFEFE;border:1px solid #F0F0F0;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.la-sr-icon { width:32px;height:32px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
.la-sr-or .la-sr-icon { background:rgba(255,255,255,.18);color:#fff; }
.la-sr-wh .la-sr-icon { background:#FFF7ED;color:#FF5C00; }
.la-sr-lbl { font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1px; }
.la-sr-or .la-sr-lbl { color:rgba(255,255,255,.75); }
.la-sr-wh .la-sr-lbl { color:#9CA3AF; }
.la-sr-val { font-size:20px;font-weight:800;line-height:1; }
.la-sr-or .la-sr-val { color:#fff; }
.la-sr-wh .la-sr-val { color:#1D1D1D; }

/* ── Layout ── */
.la-body { display:grid;grid-template-columns:480px;gap:14px;align-items:start; }
@media(max-width:600px){ .la-body{ grid-template-columns:1fr; } }

/* ── White cards ── */
.la-card { background:#FEFEFE;border:1px solid #F0F0F0;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden; }
.la-card-head { display:flex;align-items:center;gap:9px;padding:14px 18px;border-bottom:1px solid #F0F0F0;background:linear-gradient(135deg,#FAFBFC,#FEFEFE); }
.la-acc { width:3px;height:20px;background:#FF5C00;border-radius:2px;flex-shrink:0; }

/* ── Section pill ── */
.la-section-pill { display:inline-flex;align-items:center;gap:5px;background:#FFF7ED;border:1px solid #FED7AA;border-radius:20px;padding:3px 10px;font-size:10.5px;font-weight:700;color:#FF5C00;text-transform:uppercase;letter-spacing:.4px; }

/* ── Mode cards ── */
.la-mode-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:10px; }
@media(max-width:500px){ .la-mode-grid{ grid-template-columns:1fr; } }
.la-mode-card { border:2px solid #F0F0F0;border-radius:12px;padding:14px 12px 12px;cursor:pointer;transition:border-color .2s,box-shadow .2s,background .2s;background:#FEFEFE;position:relative;overflow:hidden;display:block; }
.la-mode-card input[type=radio] { display:none; }
.la-mode-card:hover { border-color:#FED7AA;box-shadow:0 4px 14px rgba(255,92,0,.10); }
.la-mode-card.selected { border-color:#FF5C00;background:#FFF7ED;box-shadow:0 4px 18px rgba(255,92,0,.15); }
.la-mode-check { position:absolute;top:10px;right:10px;width:20px;height:20px;border-radius:50%;display:none;align-items:center;justify-content:center;color:#fff; }
.la-mode-card.selected .la-mode-check { display:flex; }
.la-mode-icon { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:9px; }

/* ── Toggle rows ── */
.la-toggle-row { display:flex;align-items:flex-start;gap:12px;padding:13px 14px;background:#F9FAFB;border:1px solid #F0F0F0;border-radius:10px; }
.la-toggle-icon { width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center; }

/* ── Timeout box ── */
.la-timeout-box { background:#F9FAFB;border:1px solid #F0F0F0;border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
.la-num-input { width:90px;height:34px;border-radius:8px;border:1px solid #E5E7EB;background:#fff;font-size:13px;font-weight:600;color:#1D1D1D;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s; }
.la-num-input:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09); }

/* ── Buttons ── */
.la-save-btn { display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif!important;transition:background .15s; }
.la-save-btn:hover { background:#e05200; }
.la-back-btn { display:inline-flex;align-items:center;gap:6px;background:#FEFEFE;color:#374151;border:1px solid #E5E7EB;border-radius:8px;padding:7px 16px;font-size:12.5px;font-weight:600;text-decoration:none;font-family:'Poppins',sans-serif!important;transition:background .15s; }
.la-back-btn:hover { background:#F3F4F6;color:#1D1D1D; }
.la-add-btn { display:inline-flex;align-items:center;gap:5px;background:#FFF7ED;color:#FF5C00;border:1px solid #FED7AA;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif!important;white-space:nowrap;transition:background .15s; }
.la-add-btn:hover { background:#FFEDD5; }

/* ── Add mapping form ── */
.la-map-form { display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end; }
@media(max-width:600px){ .la-map-form{ grid-template-columns:1fr; } }
.la-fi-lbl { font-size:9.5px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px; }
.la-fi { width:100%;height:34px;border-radius:8px;border:1px solid #E5E7EB;font-size:12.5px;color:#1D1D1D;background:#fff;padding:0 10px;outline:none;font-family:'Poppins',sans-serif!important;transition:border-color .15s;box-sizing:border-box; }
.la-fi:focus { border-color:#FF5C00;box-shadow:0 0 0 3px rgba(255,92,0,.09); }

/* ── Mappings table ── */
.la-tbl-wrap { overflow-x:auto; }
.la-tbl { width:100%;border-collapse:separate;border-spacing:0; }
.la-tbl thead th { background:#F4F6F8;color:#9CA3AF;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;padding:10px 13px;white-space:nowrap;border-bottom:2px solid #F0F0F0; }
.la-tbl tbody td { padding:11px 13px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F4F6F8; }
.la-tbl tbody tr:last-child td { border-bottom:none; }
.la-tbl tbody tr:hover td { background:#FFF7ED!important; }
.la-tbl tbody tr:hover td:first-child { border-left:3px solid #FF5C00;padding-left:10px; }

/* ── Table action buttons ── */
.la-tbl-btn { display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:7px;border:1px solid;font-size:11.5px;font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif!important;transition:all .15s; }
.la-tbl-btn-ok   { background:#ECFDF5;color:#059669;border-color:#6EE7B7; }
.la-tbl-btn-ok:hover { background:#D1FAE5; }
.la-tbl-btn-warn { background:#FFFBEB;color:#D97706;border-color:#FDE68A; }
.la-tbl-btn-warn:hover { background:#FEF3C7; }
.la-tbl-btn-del  { background:#FEF2F2;color:#DC2626;border-color:#FECACA; }
.la-tbl-btn-del:hover { background:#FEE2E2; }

/* ── Empty state ── */
.la-empty { text-align:center;padding:52px 16px; }

/* ── Bootstrap switch — orange accent ── */
.form-check-input:checked { background-color:#FF5C00;border-color:#FF5C00; }
.form-check-input:focus { box-shadow:0 0 0 3px rgba(255,92,0,.15); }
</style>

@endsection

@push('scripts')
<script>
// Mode card click — update selected state
document.querySelectorAll('#modePicker .la-mode-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('#modePicker .la-mode-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input[type=radio]').checked = true;
    });
});
</script>
@endpush
