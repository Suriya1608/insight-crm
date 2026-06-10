<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="call-provider" content="tcn">
    <meta name="user-role" content="{{ auth()->user()->role ?? '' }}">

    @php
        $siteName    = \App\Models\Setting::get('site_name', 'Admission CRM');
        $siteFavicon = \App\Models\Setting::get('site_favicon');
    @endphp

    @inertiaHead

    {{-- Tell Turbo Drive to do a full page reload when navigating here from
         a Turbo-enabled page (e.g. the login page). Without this, Turbo's
         body-swap runs before the @inertia div exists, so createInertiaApp
         finds null and React never mounts — causing a blank dashboard. --}}
    <meta name="turbo-visit-control" content="reload">

    @if ($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('images/default-favicon.png') }}">
    @endif

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    {{-- App styles --}}
    <link href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}" rel="stylesheet">

    {{-- Telecaller: force Lato font across entire panel --}}
    @if(auth()->check() && auth()->user()->role === 'telecaller')
    <style>
        *, *::before, *::after { font-family: 'Lato', sans-serif !important; }
        .material-icons { font-family: 'Material Icons' !important; }
    </style>
    @endif

    {{-- Manager: Telecaller-style dark theme ── scoped to body.role-manager --}}
    @if(auth()->check() && auth()->user()->role === 'manager')
    <style>
        body.role-manager *:not(.material-icons):not([class*="material"]):not(i) { font-family:'Poppins',sans-serif!important; }

        /* ── Header ── */
        body.role-manager .top-header { background:#1D1D1D!important; border-bottom:2px solid #FF5C00!important; border-top:none!important; box-shadow:0 2px 16px rgba(0,0,0,.28)!important; }
        body.role-manager .page-header-title { color:#FEFEFE!important; font-weight:700!important; }
        body.role-manager .page-header-subtitle { color:rgba(255,255,255,.60)!important; }
        body.role-manager .mobile-menu-btn { color:#FEFEFE!important; background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.12)!important; }
        body.role-manager .mobile-menu-btn:hover { background:rgba(255,92,0,.15)!important; }
        /* header icon buttons */
        body.role-manager .top-header .btn.position-relative { background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.14)!important; }
        body.role-manager .top-header .btn.position-relative:hover { background:rgba(255,255,255,.15)!important; }
        body.role-manager .top-header .btn.position-relative .material-icons { color:rgba(255,255,255,.80)!important; }
        body.role-manager .top-header button[data-bs-target="#docsModal"] { background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.14)!important; }
        body.role-manager .top-header button[data-bs-target="#docsModal"] .material-icons { color:rgba(255,255,255,.80)!important; }

        /* ── Sidebar ── */
        body.role-manager .sidebar { background:#1D1D1D!important; box-shadow:2px 0 20px rgba(0,0,0,.30)!important; }
        body.role-manager .sidebar-header { border-bottom-color:rgba(255,255,255,.08)!important; background:rgba(255,255,255,.02)!important; }
        body.role-manager .sidebar-logo { background:linear-gradient(135deg,#FF5C00,#FF8C4A)!important; box-shadow:0 4px 14px rgba(255,92,0,.40)!important; }
        body.role-manager .sidebar-title h1 { color:#FEFEFE!important; }
        body.role-manager .sidebar-title p { color:rgba(255,160,100,.60)!important; }
        body.role-manager .sidebar-close-btn { color:rgba(255,200,160,.55)!important; }

        /* nav items */
        body.role-manager .nav-item { color:rgba(255,255,255,.72)!important; }
        body.role-manager .nav-item .material-icons { color:rgba(255,255,255,.50)!important; }
        body.role-manager .nav-item:hover { background:rgba(255,92,0,.10)!important; color:#fff!important; }
        body.role-manager .nav-item:hover .material-icons { color:#FF5C00!important; }
        body.role-manager .nav-item.active { background:rgba(255,92,0,.14)!important; color:#FF8C4A!important; font-weight:600!important; }
        body.role-manager .nav-item.active .material-icons { color:#FF5C00!important; }
        body.role-manager .nav-item.active::before { background:linear-gradient(180deg,#FF5C00,#FF8C4A)!important; }
        body.role-manager .nav-section-label { color:rgba(255,255,255,.32)!important; }

        /* collapse toggle button */
        body.role-manager button.nav-item.active { background:rgba(255,92,0,.08)!important; color:rgba(255,160,100,.85)!important; }
        body.role-manager button.nav-item.active::before { display:none!important; }

        /* sub-items */
        body.role-manager .sidebar-nav .collapse .nav-item.active { background:rgba(255,92,0,.14)!important; color:#FF8C4A!important; }
        body.role-manager .sidebar-nav .collapse .nav-item.active::before { background:linear-gradient(180deg,#FF5C00,#FF8C4A)!important; display:block!important; }

        /* footer */
        body.role-manager .sidebar-footer { border-top-color:rgba(255,255,255,.08)!important; background:rgba(0,0,0,.18)!important; }
        body.role-manager .user-profile { background:rgba(255,255,255,.05)!important; border-color:rgba(255,255,255,.09)!important; }
        body.role-manager .user-profile:hover { background:rgba(255,255,255,.09)!important; }
        body.role-manager .user-avatar { background:linear-gradient(135deg,#FF5C00,#FF8C4A)!important; box-shadow:0 2px 10px rgba(255,92,0,.40)!important; }
        body.role-manager .user-info p { color:rgba(255,255,255,.90)!important; }
        body.role-manager .user-info span { color:rgba(255,255,255,.42)!important; }
        body.role-manager .sidebar-footer .btn-link { color:rgba(255,255,255,.50)!important; }
        body.role-manager .sidebar-footer .btn-link:hover { color:#ef4444!important; }
        body.role-manager .sidebar-nav::-webkit-scrollbar-thumb { background:rgba(255,92,0,.20)!important; }
    </style>
    @endif

    {{-- Admin: Telecaller-style dark theme ── scoped to body.role-admin --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
    <style>
        body.role-admin *:not(.material-icons):not([class*="material"]):not(i) { font-family:'Poppins',sans-serif!important; }

        /* ── CSS variable overrides ── */
        body.role-admin {
            --primary-color:#FF5C00!important;
            --primary-dark:#e05200!important;
            --primary-light:rgba(255,92,0,0.10)!important;
            --sidebar-bg:#1D1D1D!important;
            --sidebar-hover:rgba(255,92,0,0.10)!important;
            --sidebar-active:rgba(255,92,0,0.14)!important;
            --grad-primary:linear-gradient(135deg,#FF5C00,#FF8C4A)!important;
        }

        /* ── Header ── */
        body.role-admin .top-header { background:#1D1D1D!important; border-bottom:2px solid #FF5C00!important; border-top:none!important; box-shadow:0 2px 16px rgba(0,0,0,.28)!important; }
        body.role-admin .page-header-title { color:#FEFEFE!important; font-weight:700!important; }
        body.role-admin .page-header-subtitle { color:rgba(255,255,255,.60)!important; }
        body.role-admin .mobile-menu-btn { color:#FEFEFE!important; background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.12)!important; }
        body.role-admin .mobile-menu-btn:hover { background:rgba(255,92,0,.15)!important; }
        body.role-admin .top-header .btn { background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.12)!important; }
        body.role-admin .top-header .btn:hover { background:rgba(255,255,255,.15)!important; }
        body.role-admin .top-header .btn .material-icons { color:rgba(255,255,255,.75)!important; }
        body.role-admin .top-header strong { color:#FEFEFE!important; }

        /* ── Sidebar ── */
        body.role-admin .sidebar { background:#1D1D1D!important; box-shadow:2px 0 20px rgba(0,0,0,.30)!important; }
        body.role-admin .sidebar-header { border-bottom-color:rgba(255,255,255,.08)!important; background:rgba(255,255,255,.02)!important; }
        body.role-admin .sidebar-logo { background:linear-gradient(135deg,#FF5C00,#FF8C4A)!important; box-shadow:0 4px 14px rgba(255,92,0,.40)!important; }
        body.role-admin .sidebar-title h1 { color:#FEFEFE!important; }
        body.role-admin .sidebar-title p { color:rgba(255,160,100,.60)!important; }
        body.role-admin .sidebar-close-btn { color:rgba(255,200,160,.55)!important; }

        /* nav items */
        body.role-admin .nav-item { color:rgba(255,255,255,.72)!important; }
        body.role-admin .nav-item .material-icons { color:rgba(255,255,255,.50)!important; }
        body.role-admin .nav-item:hover { background:rgba(255,92,0,.10)!important; color:#fff!important; }
        body.role-admin .nav-item:hover .material-icons { color:#FF5C00!important; }
        body.role-admin .nav-item.active { background:rgba(255,92,0,.14)!important; color:#FF8C4A!important; font-weight:600!important; }
        body.role-admin .nav-item.active .material-icons { color:#FF5C00!important; }
        body.role-admin .nav-item.active::before { background:linear-gradient(180deg,#FF5C00,#FF8C4A)!important; }
        body.role-admin .nav-section-label { color:rgba(255,255,255,.32)!important; }
        body.role-admin button.nav-item.active { background:rgba(255,92,0,.08)!important; color:rgba(255,160,100,.85)!important; }
        body.role-admin button.nav-item.active::before { display:none!important; }
        body.role-admin .sidebar-nav .collapse .nav-item.active { background:rgba(255,92,0,.14)!important; color:#FF8C4A!important; }
        body.role-admin .sidebar-nav .collapse .nav-item.active::before { background:linear-gradient(180deg,#FF5C00,#FF8C4A)!important; display:block!important; }

        /* footer */
        body.role-admin .sidebar-footer { border-top-color:rgba(255,255,255,.08)!important; background:rgba(0,0,0,.18)!important; }
        body.role-admin .user-profile { background:rgba(255,255,255,.05)!important; border-color:rgba(255,255,255,.09)!important; }
        body.role-admin .user-profile:hover { background:rgba(255,255,255,.09)!important; }
        body.role-admin .user-avatar { background:linear-gradient(135deg,#FF5C00,#FF8C4A)!important; box-shadow:0 2px 10px rgba(255,92,0,.40)!important; }
        body.role-admin .user-info p { color:rgba(255,255,255,.90)!important; }
        body.role-admin .user-info span { color:rgba(255,255,255,.42)!important; }
        body.role-admin .sidebar-footer .btn-link { color:rgba(255,255,255,.50)!important; }
        body.role-admin .sidebar-footer .btn-link:hover { color:#ef4444!important; }
        body.role-admin .sidebar-nav::-webkit-scrollbar-thumb { background:rgba(255,92,0,.20)!important; }

        /* ── Primary buttons & links ── */
        body.role-admin .btn-primary { background:#FF5C00!important; border-color:#FF5C00!important; }
        body.role-admin .btn-primary:hover { background:#e05200!important; border-color:#e05200!important; }
        body.role-admin .btn-outline-primary { color:#FF5C00!important; border-color:#FF5C00!important; }
        body.role-admin .btn-outline-primary:hover { background:#FF5C00!important; color:#fff!important; }

        /* ── Stat cards ── */
        body.role-admin .stat-card:hover { border-color:rgba(255,92,0,.20)!important; box-shadow:0 8px 28px rgba(255,92,0,.12)!important; }

        /* ── AY switcher on dark header ── */
        body.role-admin .top-header #ayDropdownBtn { background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.15)!important; color:rgba(255,255,255,.8)!important; }
        body.role-admin .top-header #ayDropdownBtn:hover { background:rgba(255,255,255,.14)!important; }
        body.role-admin .top-header [style*="background:var(--border-color)"],
        body.role-admin .top-header [style*="background: var(--border-color)"] { background:rgba(255,255,255,.15)!important; }
    </style>
    @endif

    {{-- WhatsApp chat widget styles (used by Telecaller/Leads/Show React page) --}}
    @include('layouts.whatsappchat')

    {{--
        Inertia SPA entry point.
        This script tag renders ONCE for the entire tab lifetime.
        React handles all navigation — no page reloads after this point.
    --}}
    @php
        $bDriver = \App\Models\Setting::get('broadcast_driver', 'null');
        $bKey    = $bDriver === 'pusher'
                    ? \App\Models\Setting::getSecure('pusher_app_key', '')
                    : \App\Models\Setting::getSecure('reverb_app_key', '');
        $bConfig = [
            'driver'  => $bDriver,
            'key'     => $bKey,
            'cluster' => \App\Models\Setting::get('pusher_app_cluster', 'mt1'),
        ];
    @endphp
    @if($bDriver !== 'null')
    <script>window.__BROADCAST__ = @json($bConfig);</script>
    @endif

    @vite(['resources/js/inertia-app.jsx'])
</head>

<body class="{{ auth()->user()?->role === 'telecaller' ? 'role-telecaller' : '' }} {{ auth()->user()?->role === 'manager' ? 'role-manager' : '' }} {{ auth()->user()?->role === 'admin' ? 'role-admin' : '' }}">

    @include('layouts.sidebar')

    <div class="main-content" id="mainContent">

        @include('layouts.header')

        <div class="dashboard-content">
            {{-- React page components render here via Inertia --}}
            @inertia
        </div>

    </div>

    {{-- ─────────────────────────────────────────────────────────────────────
         TCN Softphone — renders ONCE, never reloads.
         In a true SPA there is no page navigation that could destroy this element.
         No data-turbo-permanent or any Turbo workaround is needed.
         ───────────────────────────────────────────────────────────────────── --}}
    @if (\App\Models\Setting::get('primary_call_provider') === 'tcn' && auth()->user()->role !== 'admin')
    <div id="tcnWidget">
        <iframe id="tcnSoftphoneFrame"
            src="/softphone?v={{ filemtime(resource_path('views/softphone.blade.php')) }}"
            allow="microphone"
            style="position:fixed;bottom:80px;right:20px;width:300px;height:480px;
                   border:none;z-index:1065;border-radius:14px;
                   box-shadow:0 8px 32px rgba(0,0,0,.20);display:none;
                   transition:height .2s,width .2s;">
        </iframe>

        <button id="tcnToggleBtn" title="Toggle Softphone"
            style="position:fixed;bottom:20px;right:20px;z-index:1066;
                   width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;
                   background:#64748b;color:#fff;display:flex;
                   align-items:center;justify-content:center;
                   box-shadow:0 4px 20px rgba(0,0,0,.22);transition:background .25s;">
            <span class="material-icons" style="font-size:24px;pointer-events:none;" id="tcnToggleIco">phone</span>
        </button>
    </div>

    <script>
    (function () {
        var _frame    = document.getElementById('tcnSoftphoneFrame');
        var _btn      = document.getElementById('tcnToggleBtn');
        var _ico      = document.getElementById('tcnToggleIco');
        var _visible  = false;
        var _sipReady = false;

        // ── Header button helpers ──────────────────────────────────────────
        function _rdyUpdate(active) {
            var btn = document.getElementById('tcnReadyBtn');
            var ico = document.getElementById('tcnReadyIco');
            var lbl = document.getElementById('tcnReadyLabel');
            if (!btn) return;
            if (active) {
                btn.style.background = '#10b981';
                if (ico) ico.textContent = 'phone';
                if (lbl) lbl.textContent = 'Ready';
            } else {
                btn.style.background = '#64748b';
                if (ico) ico.textContent = 'phone_disabled';
                if (lbl) lbl.textContent = 'Not Ready';
            }
        }

        function _rdyConnecting() {
            var btn = document.getElementById('tcnReadyBtn');
            var ico = document.getElementById('tcnReadyIco');
            var lbl = document.getElementById('tcnReadyLabel');
            if (!btn) return;
            btn.style.background = '#f59e0b';
            if (ico) ico.textContent = 'hourglass_empty';
            if (lbl) lbl.textContent = 'Connecting…';
        }

        // Restore header button state on first load
        try {
            if (localStorage.getItem('tcn_sip_active') === '1') { _rdyConnecting(); }
        } catch (_) {}

        function show() {
            if (!_frame) return;
            _frame.style.display      = 'block';
            _frame.style.bottom       = '80px';
            _frame.style.height       = '480px';
            _frame.style.width        = '300px';
            _frame.style.borderRadius = '14px';
            _visible = true;
            if (_ico) _ico.textContent = 'close';
        }

        function hide() {
            if (!_frame) return;
            _frame.style.display = 'none';
            _visible = false;
            if (_ico) _ico.textContent = 'phone';
        }

        // ── Header Start / Stop button ─────────────────────────────────────
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#tcnReadyBtn')) return;
            var lbl = document.getElementById('tcnReadyLabel');
            var isReady = lbl && lbl.textContent.trim() === 'Ready';
            if (isReady) {
                if (window.GC && typeof window.GC.disableCallingMode === 'function') {
                    window.GC.disableCallingMode();
                } else {
                    try { localStorage.removeItem('tcn_sip_active'); } catch (_) {}
                    if (_frame && _frame.contentWindow) {
                        _frame.contentWindow.postMessage({ type: 'LOGOUT_SILENT' }, '*');
                    }
                }
                _sipReady = false;
                _rdyUpdate(false);
                hide();
            } else {
                _rdyConnecting();
                if (window.GC && typeof window.GC.enableCallingMode === 'function') {
                    window.GC.enableCallingMode();
                } else {
                    try { localStorage.setItem('tcn_sip_active', '1'); } catch (_) {}
                    if (_frame) {
                        var _sendStart = function () {
                            try { if (_frame.contentWindow && _frame.contentWindow._sipBooted) return; } catch (_) {}
                            if (_frame && _frame.contentWindow) {
                                _frame.contentWindow.postMessage({ type: 'START_SIP' }, '*');
                            }
                        };
                        if (_frame.contentDocument && _frame.contentDocument.readyState === 'complete') {
                            _sendStart();
                        } else {
                            _frame.addEventListener('load', _sendStart, { once: true });
                        }
                    }
                }
                show();
            }
        }, true);

        // ── Floating toggle button ─────────────────────────────────────────
        if (_btn) {
            _btn.addEventListener('click', function () {
                if (_visible) { hide(); } else { show(); }
            });
        }

        // ── Forward [data-phone] clicks to the iframe ──────────────────────
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-phone]');
            if (!el || !_frame) return;
            var phone = el.getAttribute('data-phone');
            if (!phone) return;
            _frame.contentWindow.postMessage({ type: 'SET_PHONE', phone: phone }, '*');
            if (!_visible) show();
        }, true);

        // ── Messages from softphone iframe ─────────────────────────────────
        window.addEventListener('message', function (ev) {
            var d = ev.data;
            if (!d || typeof d !== 'object') return;

            switch (d.type) {
                case 'TCN_READY':
                    _sipReady = true;
                    if (_btn) { _btn.style.background = '#10b981'; _btn.style.animation = ''; }
                    _rdyUpdate(true);
                    break;
                case 'TCN_CALL_STARTED':
                    if (_btn) { _btn.style.background = '#6366f1'; _btn.style.animation = 'tcnBtnPulse 1s ease-in-out infinite'; }
                    if (!_visible) show();
                    break;
                case 'TCN_CALL_ANSWERED':
                    if (_btn) { _btn.style.background = '#ef4444'; _btn.style.animation = ''; }
                    break;
                case 'TCN_CALL_ENDED':
                    if (_btn) { _btn.style.background = '#10b981'; _btn.style.animation = ''; }
                    break;
                case 'TCN_LOGGED_OUT':
                    _sipReady = false;
                    if (_btn) { _btn.style.background = '#64748b'; _btn.style.animation = ''; }
                    _rdyUpdate(false);
                    break;
                case 'TCN_SIP_DROPPED':
                    _sipReady = false;
                    if (_btn) { _btn.style.background = '#f59e0b'; _btn.style.animation = ''; }
                    _rdyConnecting();
                    break;
                case 'SP_MINIMIZE':
                    if (_frame) { _frame.style.height = '44px'; _frame.style.width = '170px'; _frame.style.borderRadius = '22px'; }
                    break;
                case 'SP_EXPAND':
                    if (_frame) { _frame.style.height = '480px'; _frame.style.width = '300px'; _frame.style.borderRadius = '14px'; }
                    break;
            }
        });
    })();
    </script>
    <style>@keyframes tcnBtnPulse{0%,100%{opacity:1}50%{opacity:.55}}</style>
    @endif

    {{-- AI Assistant for managers is rendered by the React ChatWidget in inertia-app.jsx --}}

    @auth
    {{-- Idle Session Timeout (15 minutes) — mirrors the same timer in layouts/app.blade.php.
         The Inertia SPA never does a full page reload, so without this the user would
         stay "logged in" in the browser indefinitely after the server session expires. --}}
    <div class="modal fade" id="idleWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 8px 32px rgba(0,0,0,.15);">
                <div class="modal-body text-center p-4">
                    <span class="material-icons mb-2" style="font-size:40px; color:#f59e0b;">timer</span>
                    <h6 class="fw-bold mb-1">Session Expiring Soon</h6>
                    <p class="text-muted small mb-3">Your session will expire in <strong id="idleCountdown">60</strong> seconds due to inactivity.</p>
                    <button id="idleStayBtn" class="btn btn-primary btn-sm px-4">Stay Logged In</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const IDLE_TIMEOUT    = 15 * 60 * 1000;
        const WARN_BEFORE     = 60 * 1000;
        const IDLE_LOGOUT_URL = @json(route('idle.logout'));

        let idleTimer, warnTimer, countdownInterval;
        let warningShown = false;
        let modal, modalInstance;

        function resetTimers() {
            clearTimeout(idleTimer);
            clearTimeout(warnTimer);
            if (warningShown) hideWarning();
            warnTimer = setTimeout(showWarning, IDLE_TIMEOUT - WARN_BEFORE);
            idleTimer = setTimeout(doLogout,    IDLE_TIMEOUT);
        }

        function showWarning() {
            warningShown = true;
            let secs = 60;
            const cdEl = document.getElementById('idleCountdown');
            if (cdEl) cdEl.textContent = secs;
            clearInterval(countdownInterval);
            countdownInterval = setInterval(function () {
                secs--;
                const el = document.getElementById('idleCountdown');
                if (el) el.textContent = secs;
            }, 1000);
            if (!modal) {
                modal = document.getElementById('idleWarningModal');
                if (modal) modalInstance = new bootstrap.Modal(modal);
            }
            if (modalInstance) modalInstance.show();
        }

        function hideWarning() {
            warningShown = false;
            clearInterval(countdownInterval);
            if (modalInstance) modalInstance.hide();
        }

        function doLogout() {
            clearInterval(countdownInterval);
            window.location.href = IDLE_LOGOUT_URL;
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
            document.addEventListener(evt, resetTimers, { passive: true });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('idleStayBtn');
            if (btn) btn.addEventListener('click', resetTimers);
        });

        resetTimers();
    })();
    </script>
    @endauth

    {{-- global-call.js — managers only (call UI is in the lead profile) --}}
    @if (auth()->check() && auth()->user()->role === 'manager')
        <script src="{{ asset('js/global-call.js?v=5') }}"></script>
        <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                if (window.GC && typeof window.GC.initDevice === 'function') {
                    window.GC.initDevice();
                }
            });
        })();
        </script>
    @endif

    @if (auth()->check() && auth()->user()->role === 'telecaller')
        {{-- Notification bell polling --}}
        <script data-turbo-eval="false">
        (function() {
            const snapshotUrl = @json(route('telecaller.notifications.snapshot'));
            const markReadUrl = @json(route('telecaller.notifications.read-all'));
            const csrfToken = @json(csrf_token());
            const soundKey = 'telecaller_notify_sound_enabled';
            const seenMissedKey = 'telecaller_seen_missed_call_ids';
            const seenFollowupKey = 'telecaller_seen_followup_ids';

            const badge = document.getElementById('teleNotifBadge');
            const missedWrap = document.getElementById('teleNotifMissedCalls');
            const followupWrap = document.getElementById('teleNotifFollowups');
            const waWrap = document.getElementById('teleNotifWhatsapp');
            const systemWrap = document.getElementById('teleNotifSystem');
            const missedCountEl = document.getElementById('teleNotifMissedCount');
            const followupCountEl = document.getElementById('teleNotifFollowupCount');
            const waCountEl = document.getElementById('teleNotifWhatsappCount');
            const systemCountEl = document.getElementById('teleNotifSystemCount');
            const soundToggle = document.getElementById('teleNotifSoundToggle');
            const markReadBtn = document.getElementById('teleNotifMarkRead');

            if (!badge || !missedWrap || !followupWrap || !systemWrap) return;

            let previousCount = 0;

            function getSoundEnabled() { return localStorage.getItem(soundKey) !== '0'; }
            function setSoundEnabled(v) {
                localStorage.setItem(soundKey, v ? '1' : '0');
                if (soundToggle) soundToggle.checked = !!v;
            }

            function playBeep() {
                if (!getSoundEnabled()) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.001, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.start(); osc.stop(ctx.currentTime + 0.22);
                } catch (e) {}
            }

            function renderList(items, renderer, emptyText) {
                if (!items || !items.length) return `<div class="text-center py-2" style="color:#94a3b8;font-size:12px;">${emptyText}</div>`;
                return items.map(renderer).join('');
            }

            function setSectionCount(el, count) {
                if (!el) return;
                if (count > 0) { el.style.display = 'inline-block'; el.textContent = count; }
                else { el.style.display = 'none'; }
            }

            function getSeenIds(key) {
                try { const r = localStorage.getItem(key); const p = r ? JSON.parse(r) : []; return Array.isArray(p) ? p.map(Number) : []; } catch (e) { return []; }
            }
            function setSeenIds(key, ids) {
                localStorage.setItem(key, JSON.stringify(Array.from(new Set(ids.map(Number)))));
            }

            function updateBadge(count) {
                if (count > 0) { badge.style.display = 'inline-block'; badge.textContent = count > 99 ? '99+' : String(count); }
                else { badge.style.display = 'none'; }
            }

            async function fetchNotifications() {
                try {
                    const res = await fetch(snapshotUrl, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (!data || !data.ok) return;

                    const seenMissed = getSeenIds(seenMissedKey);
                    const seenFollowups = getSeenIds(seenFollowupKey);
                    const rawMissed = Array.isArray(data.missed_calls) ? data.missed_calls : [];
                    const rawFollowups = Array.isArray(data.followup_reminders) ? data.followup_reminders : [];
                    const rawWhatsapp = Array.isArray(data.whatsapp_notifications) ? data.whatsapp_notifications : [];
                    const rawSystem = Array.isArray(data.system_notifications) ? data.system_notifications : [];

                    const missedCalls = rawMissed.filter(item => !seenMissed.includes(Number(item.id)));
                    const followupReminders = rawFollowups.filter(item => !seenFollowups.includes(Number(item.id)));

                    const count = missedCalls.length + followupReminders.length + rawWhatsapp.length + rawSystem.length;
                    if (count > previousCount) playBeep();
                    previousCount = count;
                    updateBadge(count);
                    setSectionCount(missedCountEl, missedCalls.length);
                    setSectionCount(followupCountEl, followupReminders.length);
                    setSectionCount(waCountEl, rawWhatsapp.length);
                    setSectionCount(systemCountEl, rawSystem.length);

                    missedWrap.innerHTML = renderList(missedCalls, (item) => {
                        const link = item.lead_url
                            ? `<a href="${item.lead_url}" style="font-size:11px;color:#6366f1;font-weight:600;text-decoration:none;">Open lead →</a>`
                            : '';
                        return `<div class="d-flex gap-2 p-2 mb-1 rounded-3" style="background:#fff;border:1px solid #fee2e2;border-left:3px solid #ef4444;">
                            <span class="material-icons flex-shrink-0 mt-1" style="font-size:15px;color:#ef4444;">phone_missed</span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:600;color:#0f172a;">${item.lead_name || 'Unknown lead'}</div>
                                <div style="font-size:11px;color:#64748b;">${item.lead_code || ''} | ${item.phone || '-'}${item.time ? ' · ' + item.time : ''}</div>
                                ${link ? `<div class="mt-1">${link}</div>` : ''}
                            </div>
                            <span class="badge rounded-pill flex-shrink-0 align-self-start" style="background:#fee2e2;color:#ef4444;font-size:9px;">Missed</span>
                        </div>`;
                    }, 'No missed calls.');

                    followupWrap.innerHTML = renderList(followupReminders, (item) => {
                        const isOverdue = item.type === 'overdue';
                        const badgeStyle = isOverdue
                            ? 'background:#fee2e2;color:#ef4444;'
                            : 'background:#fef3c7;color:#d97706;';
                        return `<div class="d-flex gap-2 p-2 mb-1 rounded-3" style="background:#fff;border:1px solid #fef3c7;border-left:3px solid #f59e0b;">
                            <span class="material-icons flex-shrink-0 mt-1" style="font-size:15px;color:#f59e0b;">calendar_today</span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:600;color:#0f172a;">${item.lead_name}</div>
                                <div style="font-size:11px;color:#64748b;">${item.lead_code || ''} | ${item.next_followup || '-'}</div>
                                <span class="badge rounded-pill mt-1" style="${badgeStyle}font-size:9px;">${item.type}</span>
                            </div>
                        </div>`;
                    }, 'No reminders.');

                    if (waWrap) waWrap.innerHTML = renderList(rawWhatsapp, (item) =>
                        `<div class="d-flex gap-2 p-2 mb-1 rounded-3" style="background:#fff;border:1px solid #d1fae5;border-left:3px solid #10b981;">
                            <span class="material-icons flex-shrink-0 mt-1" style="font-size:15px;color:#10b981;">chat</span>
                            <div style="flex:1;min-width:0;">
                                <a href="${item.link || '#'}" style="font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;display:block;">${item.title || 'WhatsApp'}</a>
                                <div style="font-size:11px;color:#64748b;">${item.message || ''}</div>
                                <div style="font-size:10px;color:#94a3b8;">${item.time || ''}</div>
                            </div>
                        </div>`, 'No WhatsApp messages.');

                    systemWrap.innerHTML = renderList(rawSystem, (item) =>
                        `<div class="d-flex gap-2 p-2 mb-1 rounded-3" style="background:#fff;border:1px solid #ffe8d6;border-left:3px solid #FF5C00;">
                            <span class="material-icons flex-shrink-0 mt-1" style="font-size:15px;color:#FF5C00;">info</span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;color:#0f172a;">${item.message}</div>
                                <div style="font-size:10px;color:#94a3b8;">${item.time || ''}</div>
                            </div>
                        </div>`, 'No system notifications.');
                } catch (e) {}
            }

            soundToggle?.addEventListener('change', function() { setSoundEnabled(!!this.checked); });

            markReadBtn?.addEventListener('click', async function() {
                const btn = this;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="material-icons" style="font-size:15px;animation:spin .6s linear infinite;">refresh</span> Clearing…';
                try {
                    const res = await fetch(snapshotUrl, { headers: { 'Accept': 'application/json' } });
                    const snap = await res.json();
                    setSeenIds(seenMissedKey, [...getSeenIds(seenMissedKey), ...(snap.missed_calls || []).map(i => Number(i.id)).filter(Boolean)]);
                    setSeenIds(seenFollowupKey, [...getSeenIds(seenFollowupKey), ...(snap.followup_reminders || []).map(i => Number(i.id)).filter(Boolean)]);
                    await fetch(markReadUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({}) });
                    await fetchNotifications();
                    btn.innerHTML = '<span class="material-icons" style="font-size:15px;">check_circle</span> All cleared';
                    setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 1500);
                } catch (e) { btn.innerHTML = original; btn.disabled = false; }
            });

            setSoundEnabled(getSoundEnabled());
            fetchNotifications();
            setInterval(fetchNotifications, 60000);

            // Refresh immediately when a missed call occurs (fired by global-call.js after TCN_INCOMING_REJECTED)
            window.addEventListener('gc:missedCall', function () {
                fetchNotifications();
            });
        })();
        </script>
    @endif

    {{-- Documents Quick Access Modal --}}
    @if(auth()->check() && auth()->user()->role !== 'admin')
    <div class="modal fade" id="docsModal" tabindex="-1" aria-labelledby="docsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0;">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="docsModalLabel">
                        <span class="material-icons" style="color:#6366f1;">folder_open</span>
                        Documents
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="docsModalBody" style="min-height:160px;">
                    <div class="text-center text-muted py-4" id="docsLoadingState">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Loading documents...
                    </div>
                    <div id="docsListContainer" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bootstrap JS (CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Documents modal loader --}}
    @if(auth()->check() && auth()->user()->role !== 'admin')
    <script>
    (function () {
        var listUrl = @json(route('documents.list'));
        var loaded  = false;

        document.addEventListener('show.bs.modal', function (e) {
            if (e.target.id !== 'docsModal') return;
            if (loaded) return;
            fetch(listUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var container = document.getElementById('docsListContainer');
                    var loading   = document.getElementById('docsLoadingState');
                    if (!data.ok || !data.documents || !data.documents.length) {
                        loading.innerHTML = '<span class="material-icons d-block mb-1" style="font-size:32px;color:#cbd5e1;">folder_open</span>No documents available.';
                        return;
                    }
                    container.innerHTML = data.documents.map(function (d) {
                        return '<div class="d-flex align-items-center justify-content-between py-2 border-bottom gap-3">' +
                            '<div class="d-flex align-items-center gap-2">' +
                            '<span class="material-icons" style="color:#64748b;font-size:20px;flex-shrink:0;">' + d.icon + '</span>' +
                            '<div><div class="fw-semibold" style="font-size:14px;">' + d.title + '</div>' +
                            '<div class="text-muted" style="font-size:12px;">' + d.file_name + ' &middot; ' + d.file_size_formatted + ' &middot; ' + d.created_at + '</div></div></div>' +
                            '<div class="d-flex gap-2" style="flex-shrink:0;">' +
                            '<a href="' + d.view_url + '" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"><span class="material-icons" style="font-size:15px;">visibility</span>View</a>' +
                            '<a href="' + d.download_url + '" class="btn btn-sm btn-primary d-flex align-items-center gap-1"><span class="material-icons" style="font-size:15px;">download</span>Download</a>' +
                            '</div></div>';
                    }).join('');
                    loading.style.display = 'none';
                    container.style.display = 'block';
                    loaded = true;
                })
                .catch(function () {
                    document.getElementById('docsLoadingState').textContent = 'Failed to load documents.';
                });
        });
    })();
    </script>
    @endif

    {{-- Sidebar toggle --}}
    <script>
        function openSidebar() {
            var sidebar  = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar)  sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
        }

        function closeSidebar() {
            var sidebar  = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar)  sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }

        function toggleSidebar() {
            var sidebar     = document.getElementById('sidebar');
            var mainContent = document.getElementById('mainContent');
            if (!sidebar) return;

            if (window.innerWidth > 991) {
                var collapsed = sidebar.classList.toggle('desktop-collapsed');
                if (mainContent) mainContent.classList.toggle('desktop-expanded', collapsed);
                try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); } catch(e) {}
            } else {
                if (sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }
        }

        // Restore collapse state on load (persists across Inertia navigations)
        (function () {
            if (window.innerWidth <= 991) return;
            var sidebar     = document.getElementById('sidebar');
            var mainContent = document.getElementById('mainContent');
            if (!sidebar) return;
            try {
                var collapsed = localStorage.getItem('sidebarCollapsed') === '1';
                sidebar.classList.toggle('desktop-collapsed', collapsed);
                if (mainContent) mainContent.classList.toggle('desktop-expanded', collapsed);
            } catch(e) {}
        })();

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (window.innerWidth > 991) {
                    var sidebar     = document.getElementById('sidebar');
                    var mainContent = document.getElementById('mainContent');
                    if (sidebar && sidebar.classList.contains('desktop-collapsed')) {
                        sidebar.classList.remove('desktop-collapsed');
                        if (mainContent) mainContent.classList.remove('desktop-expanded');
                        try { localStorage.setItem('sidebarCollapsed', '0'); } catch(e) {}
                    }
                } else {
                    closeSidebar();
                }
            }
        });
    </script>

    {{-- The Blade-sidebar link guard lives in inertia-app.jsx (router.on('before')).
         No custom click interceptor needed here — Inertia's global handler plus
         the before-hook covers all navigation cases correctly. --}}

    {{-- Sync document.title → header <h2> so each React page can drive the header title
         via <Head title="..."/>.  A MutationObserver on <title> fires on every Inertia
         navigation; no polling, no race conditions. --}}
    <script>
    (function () {
        function syncTitle() {
            var raw = document.title || '';
            var page = raw.replace(/\s*[—–-]\s*Admission CRM\s*$/i, '').trim();
            var h2 = document.getElementById('pageHeaderTitle');
            if (h2 && page) h2.textContent = page;
        }

        // Observe <head> directly — on non-SSR Inertia there is no <title> in the
        // initial HTML, so watching a specific titleEl would bail out immediately.
        // Watching <head> catches: new <title> inserted (childList) and any text-node
        // change inside it (characterData + subtree).
        new MutationObserver(syncTitle).observe(document.head, {
            childList: true,
            subtree: true,
            characterData: true,
        });
    })();
    </script>

    {{-- Flash toasts — reads from Inertia shared props via window.__inertiaFlash injected by React --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1090;" id="toastContainer"></div>

    {{-- WhatsApp Real-Time Inbound Notifications (Pusher + fallback polling) --}}
    @auth
    @php $waRole = auth()->user()->role; @endphp
    @if($waRole === 'telecaller' || $waRole === 'manager')
    <div id="waToastStack" style="position:fixed;top:76px;right:20px;z-index:9999;width:320px;display:flex;flex-direction:column;gap:8px;pointer-events:none;"></div>
    <script>
    (function () {
        @if($waRole === 'telecaller')
        const pollUrl = @json(route('telecaller.whatsapp.inbox-poll'));
        @else
        const pollUrl = @json(route('manager.whatsapp.inbox-poll'));
        @endif

        const LS_KEY = 'wa_notif_ts_{{ auth()->id() }}';
        let   lastTs   = localStorage.getItem(LS_KEY) || null;
        let   audioCtx = null;
        const shownIds = new Set();

        function playWaSound() {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                [[1100, 0], [880, 0.18]].forEach(function(pair) {
                    const osc  = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain); gain.connect(audioCtx.destination);
                    osc.type = 'sine'; osc.frequency.value = pair[0];
                    const t = audioCtx.currentTime + pair[1];
                    gain.gain.setValueAtTime(0, t);
                    gain.gain.linearRampToValueAtTime(0.35, t + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.22);
                    osc.start(t); osc.stop(t + 0.22);
                });
            } catch (e) {}
        }

        function esc(s) {
            return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function showToast(title, message, link) {
            const stack = document.getElementById('waToastStack');
            if (!stack) return;
            const div = document.createElement('div');
            div.style.cssText = 'background:#fff;border:1px solid #e2e8f0;border-left:4px solid #25D366;border-radius:10px;padding:12px 14px;box-shadow:0 4px 16px rgba(0,0,0,0.13);pointer-events:auto;animation:waSlideIn .25s ease;';
            div.innerHTML =
                '<div style="display:flex;align-items:flex-start;gap:10px;">' +
                  '<span class="material-icons" style="color:#25D366;font-size:22px;flex-shrink:0;margin-top:1px;">chat</span>' +
                  '<div style="flex:1;min-width:0;">' +
                    '<div style="font-weight:700;font-size:13px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(title) + '</div>' +
                    '<div style="font-size:12px;color:#64748b;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(message) + '</div>' +
                    (link ? '<a href="' + esc(link) + '" style="display:inline-block;margin-top:6px;font-size:12px;font-weight:600;color:#6366f1;text-decoration:none;">Open Chat &rarr;</a>' : '') +
                  '</div>' +
                  '<button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;padding:0;flex-shrink:0;">&times;</button>' +
                '</div>';
            stack.appendChild(div);
            setTimeout(function() { try { div.remove(); } catch(e){} }, 9000);
        }

        async function poll() {
            try {
                const url = pollUrl + (lastTs ? '?after=' + encodeURIComponent(lastTs) : '');
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.ok) return;

                const currentPath = window.location.pathname;
                const items = (data.items || []).filter(function(item) {
                    if (!item.id || shownIds.has(item.id)) return false;
                    if (item.link) {
                        try { if (new URL(item.link).pathname === currentPath) return false; } catch(e) {}
                    }
                    return true;
                });
                items.forEach(function(item) { if (item.id) shownIds.add(item.id); });

                if (items.length > 0) {
                    playWaSound();
                    if (data.is_first) {
                        showToast(
                            items.length + ' unread WhatsApp message' + (items.length > 1 ? 's' : ''),
                            'Messages received while you were away.',
                            null
                        );
                    } else {
                        items.forEach(function(item) { showToast(item.title, item.message, item.link); });
                    }
                }

                if (data.ts) {
                    lastTs = data.ts;
                    localStorage.setItem(LS_KEY, data.ts);
                }
            } catch (e) {}
        }

        if (!document.getElementById('waToastStyle')) {
            const s = document.createElement('style');
            s.id = 'waToastStyle';
            s.textContent = '@keyframes waSlideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}';
            document.head.appendChild(s);
        }

        poll();
        setInterval(poll, 30000);

        // ── Pusher real-time: subscribe after ES module scripts execute ──────────
        // <script type="module"> (Vite bundle) executes before DOMContentLoaded,
        // so window.Echo is guaranteed set by the time this listener fires.
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Echo) return;
            try {
                window.Echo.private('whatsapp.inbox.{{ auth()->id() }}')
                    .listen('.message.new', function (data) {
                        poll();
                        // Relay to any open React chat window on this page
                        window.dispatchEvent(new CustomEvent('wa:message.new', { detail: data }));
                    });
            } catch (e) {}
        });
    })();
    </script>
    @endif
    @endauth

</body>
</html>
