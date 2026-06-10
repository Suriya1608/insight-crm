@push('styles')
<style>
    /* ── Settings Nav ── */
    .settings-nav-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        overflow-x: auto;
    }
    .settings-nav-inner {
        display: flex;
        align-items: flex-start;
        gap: 0;
        min-width: max-content;
    }
    .settings-nav-group { display: flex; flex-direction: column; gap: 6px; }
    .settings-nav-group-label {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
        color: #94a3b8;
        padding-left: 2px;
        margin-bottom: 2px;
    }
    .settings-nav-pills { display: flex; flex-wrap: nowrap; gap: 4px; }
    .settings-nav-divider {
        width: 1px;
        background: #e2e8f0;
        margin: 4px 16px;
        align-self: stretch;
    }
    .s-nav-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 13px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 500;
        color: #475569;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: background .15s, color .15s, border-color .15s;
        line-height: 1;
    }
    .s-nav-pill .material-icons { font-size: 14px; }
    .s-nav-pill:hover {
        background: #f1f5f9;
        color: #1e293b;
        text-decoration: none;
    }
    .s-nav-pill.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(99,102,241,.35);
        font-weight: 600;
    }
    .s-nav-pill.active .material-icons { color: #fff; }
</style>
@endpush

<div class="settings-nav-wrap">
    <div class="settings-nav-inner">

        {{-- System --}}
        <div class="settings-nav-group">
            <div class="settings-nav-group-label">System</div>
            <div class="settings-nav-pills">
                <a href="{{ route('admin.settings.general') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                    <span class="material-icons">tune</span> General
                </a>
                <a href="{{ route('admin.settings.pages') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.pages') ? 'active' : '' }}">
                    <span class="material-icons">web</span> Pages
                </a>
                <a href="{{ route('admin.settings.security') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.security') ? 'active' : '' }}">
                    <span class="material-icons">shield</span> Security
                </a>
            </div>
        </div>

        <div class="settings-nav-divider"></div>

        {{-- Communication --}}
        <div class="settings-nav-group">
            <div class="settings-nav-group-label">Communication</div>
            <div class="settings-nav-pills">
                <a href="{{ route('admin.settings.smtp') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.smtp') ? 'active' : '' }}">
                    <span class="material-icons">mail</span> SMTP
                </a>
                <a href="{{ route('admin.settings.sms') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.sms') ? 'active' : '' }}">
                    <span class="material-icons">sms</span> SMS
                </a>
                <a href="{{ route('admin.settings.whatsapp') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}">
                    <span class="material-icons">chat</span> WhatsApp
                </a>
                <a href="{{ route('admin.settings.tcn') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.tcn', 'admin.settings.call') || request()->routeIs('admin.tcn-relay-clients.*') ? 'active' : '' }}">
                    <span class="material-icons">headset_mic</span> Softphone
                </a>
                <a href="{{ route('admin.settings.zoom') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.zoom') ? 'active' : '' }}">
                    <span class="material-icons">videocam</span> Zoom
                </a>
                <a href="{{ route('admin.settings.google-meet') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.google-meet') ? 'active' : '' }}">
                    <span class="material-icons">video_call</span> Google Meet
                </a>
                <a href="{{ route('admin.settings.realtime') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.realtime') ? 'active' : '' }}">
                    <span class="material-icons">bolt</span> Real-Time
                </a>
            </div>
        </div>

        <div class="settings-nav-divider"></div>

        {{-- Lead Management --}}
        <div class="settings-nav-group">
            <div class="settings-nav-group-label">Lead Management</div>
            <div class="settings-nav-pills">
                <a href="{{ route('admin.settings.default-lead-status') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.default-lead-status') ? 'active' : '' }}">
                    <span class="material-icons">flag</span> Lead Status
                </a>
                <a href="{{ route('admin.settings.lead-portals') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.lead-portals') ? 'active' : '' }}">
                    <span class="material-icons">input</span> Lead Portals
                </a>
                <a href="{{ route('admin.settings.notifications') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.notifications') ? 'active' : '' }}">
                    <span class="material-icons">notifications</span> Notifications
                </a>
            </div>
        </div>

        <div class="settings-nav-divider"></div>

        {{-- Operations --}}
        <div class="settings-nav-group">
            <div class="settings-nav-group-label">Operations</div>
            <div class="settings-nav-pills">
                <a href="{{ route('admin.settings.business-hours') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.business-hours') ? 'active' : '' }}">
                    <span class="material-icons">schedule</span> Business Hours
                </a>
                <a href="{{ route('admin.settings.working-days') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.working-days') ? 'active' : '' }}">
                    <span class="material-icons">date_range</span> Working Days
                </a>
                <a href="{{ route('admin.settings.timezone') }}"
                   class="s-nav-pill {{ request()->routeIs('admin.settings.timezone') ? 'active' : '' }}">
                    <span class="material-icons">public</span> Timezone
                </a>
            </div>
        </div>

    </div>
</div>
