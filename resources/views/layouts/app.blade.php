<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="call-provider" content="tcn">
    <meta name="user-role" content="{{ auth()->user()->role ?? '' }}">

    {{-- Dynamic Title --}}
    <title>{{ \App\Models\Setting::get('site_name', 'Admission CRM') }}</title>
    {{-- Dynamic Favicon --}}
    @php
        $favicon = \App\Models\Setting::get('site_favicon');
    @endphp

    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('images/default-favicon.png') }}">
    @endif

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}" rel="stylesheet">

    @if(auth()->check() && auth()->user()->role === 'telecaller')
    <style>
        *, *::before, *::after { font-family: 'Lato', sans-serif !important; }
        .material-icons { font-family: 'Material Icons' !important; }

        /* ── Orange theme: CSS variable overrides ── */
        body.role-telecaller {
            --primary-color: #FF5C00;
            --primary-dark: #e05200;
            --primary-light: rgba(255,92,0,0.10);
            --sidebar-hover: rgba(255,92,0,0.10);
            --sidebar-active: rgba(255,92,0,0.14);
            --grad-primary: linear-gradient(135deg, #FF5C00, #FF8C4A);
        }
        body.role-telecaller .btn-primary { background:#FF5C00!important; border-color:#FF5C00!important; }
        body.role-telecaller .btn-primary:hover { background:#e05200!important; border-color:#e05200!important; }
        body.role-telecaller .btn-outline-primary { color:#FF5C00!important; border-color:#FF5C00!important; }
        body.role-telecaller .btn-outline-primary:hover { background:#FF5C00!important; color:#fff!important; }
        body.role-telecaller .text-primary { color:#FF5C00!important; }
        body.role-telecaller a.text-primary:hover { color:#e05200!important; }
        body.role-telecaller .badge.bg-primary { background:#FF5C00!important; }
        body.role-telecaller .form-control:focus,
        body.role-telecaller .form-select:focus { border-color:#FF5C00!important; box-shadow:0 0 0 3px rgba(255,92,0,.12)!important; }
        body.role-telecaller .page-item.active .page-link { background:#FF5C00!important; border-color:#FF5C00!important; color:#fff!important; }
        /* Active nav item text/icon color in sidebar */
        body.role-telecaller .nav-item.active,
        body.role-telecaller .nav-item.active .material-icons { color:#ffb380!important; }
        body.role-telecaller .nav-item:hover .material-icons { color:rgba(255,180,128,0.90)!important; }
        body.role-telecaller .sidebar-nav .collapse .nav-item.active { color:#ffb380!important; }
    </style>
    @endif

    {{-- Lucide Icons for admin pages (same icon set as react-icons/lu) --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/umd/lucide.min.js" defer></script>
    @endif

    {{-- Admin: Telecaller-style dark theme ── scoped to body.role-admin --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
    <style>
        body.role-admin *:not(.material-icons):not([class*="material"]):not(i) { font-family:'Poppins',sans-serif!important; }

        /* ── CSS variable overrides ── */
        body.role-admin {
            --primary-color:#FF5C00;
            --primary-dark:#e05200;
            --primary-light:rgba(255,92,0,0.10);
            --sidebar-bg:#1D1D1D;
            --sidebar-hover:rgba(255,92,0,0.10);
            --sidebar-active:rgba(255,92,0,0.14);
            --grad-primary:linear-gradient(135deg,#FF5C00,#FF8C4A);
        }

        /* Sidebar/nav styles are handled in style.css (icon-only rules) */

        /* ── Header ── */
        body.role-admin .top-header { background:#1D1D1D!important; border-bottom:2px solid #FF5C00!important; border-top:none!important; box-shadow:0 2px 16px rgba(0,0,0,.28)!important; color:#fff!important; }
        /* All text inside header should be white by default */
        body.role-admin .top-header span:not([class*="badge"]),
        body.role-admin .top-header p,
        body.role-admin .top-header div,
        body.role-admin .top-header label,
        body.role-admin .top-header small { color:#fff!important; }
        /* Exceptions: dropdown menus keep dark text */
        body.role-admin .top-header .dropdown-menu span,
        body.role-admin .top-header .dropdown-menu div,
        body.role-admin .top-header .dropdown-menu a { color:#1D1D1D!important; }
        /* Popper.js gives fixed-in-fixed dropdowns z-index:1000 which is behind the header (z:1050) — fix it */
        body.role-admin .top-header .dropdown-menu { z-index:1060!important; }
        body.role-admin .page-header-title { color:#1D1D1D!important; font-weight:700!important; }
        body.role-admin .page-header-subtitle { color:#9CA3AF!important; }
        body.role-admin .top-header strong { color:#FEFEFE!important; }
        body.role-admin .mobile-menu-btn { color:#FEFEFE!important; background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.12)!important; }
        body.role-admin .top-header .btn { background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.12)!important; color:#fff!important; }
        body.role-admin .top-header .btn:hover { background:rgba(255,255,255,.15)!important; }
        body.role-admin .tc-navbar-site-name { color:#fff!important; }
        body.role-admin .tc-navbar-site-role { color:rgba(255,200,160,.85)!important; }
        body.role-admin .top-header .btn .material-icons { color:rgba(255,255,255,.75)!important; }
        body.role-admin .top-header #ayDropdownBtn { background:rgba(255,255,255,.08)!important; border-color:rgba(255,255,255,.15)!important; color:rgba(255,255,255,.8)!important; }

        /* ── Buttons & links ── */
        body.role-admin .btn-primary { background:#FF5C00!important; border-color:#FF5C00!important; }
        body.role-admin .btn-primary:hover { background:#e05200!important; border-color:#e05200!important; }
        body.role-admin .btn-outline-primary { color:#FF5C00!important; border-color:#FF5C00!important; }
        body.role-admin .btn-outline-primary:hover { background:#FF5C00!important; color:#fff!important; }
        body.role-admin .text-primary { color:#FF5C00!important; }
        body.role-admin a.text-primary:hover { color:#e05200!important; }
        body.role-admin .btn,
        body.role-admin button { user-select:none!important; -webkit-user-select:none!important; }

        /* ── Chart & section cards ── */
        body.role-admin .chart-card { background:#FEFEFE!important; border:1px solid #F0F0F0!important; border-radius:14px!important; box-shadow:0 2px 8px rgba(0,0,0,.04)!important; padding:0!important; overflow:hidden!important; }
        body.role-admin .chart-card .chart-header { padding:14px 20px 12px 23px!important; border-bottom:1px solid #F0F0F0!important; background:linear-gradient(135deg,#FAFBFC,#FFFFFF)!important; margin-bottom:0!important; position:relative!important; }
        body.role-admin .chart-card .chart-header::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:#FF5C00; border-radius:2px 0 0 2px; }
        body.role-admin .chart-card .chart-header h3 { font-size:13.5px!important; font-weight:700!important; color:#1D1D1D!important; margin:0!important; }
        body.role-admin .chart-card .chart-header p  { font-size:11px!important; color:#9CA3AF!important; margin:2px 0 0!important; }
        body.role-admin .chart-card > *:not(.chart-header) { padding:16px 20px; }

        /* ── Stat cards ── */
        body.role-admin .stat-card:hover { border-color:rgba(255,92,0,.20)!important; box-shadow:0 8px 28px rgba(255,92,0,.12)!important; }

        /* ── Bootstrap tables — telecaller style ── */
        body.role-admin .table thead th { background:#F4F6F8!important; color:#9CA3AF!important; font-size:9.5px!important; font-weight:700!important; text-transform:uppercase!important; letter-spacing:.7px!important; border-bottom:2px solid #F0F0F0!important; padding:10px 14px!important; }
        body.role-admin .table tbody td { padding:11px 14px!important; vertical-align:middle!important; font-size:12.5px!important; color:#374151!important; border-bottom:1px solid #F4F6F8!important; }
        body.role-admin .table tbody tr:last-child td { border-bottom:none!important; }
        body.role-admin .table tbody tr:hover td { background:#FFF7ED!important; }
        body.role-admin .table tbody tr:hover td:first-child { border-left:3px solid #FF5C00!important; }
        body.role-admin .table-responsive { border-radius:0 0 14px 14px; overflow:hidden; }

        /* ── Bootstrap cards ── */
        body.role-admin .card { background:#FEFEFE!important; border:1px solid #F0F0F0!important; border-radius:14px!important; box-shadow:0 2px 8px rgba(0,0,0,.04)!important; overflow:hidden; }
        body.role-admin .card-header { background:linear-gradient(135deg,#FAFBFC,#FFFFFF)!important; border-bottom:1px solid #F0F0F0!important; padding:14px 20px 12px 23px!important; position:relative; font-size:13.5px!important; font-weight:700!important; color:#1D1D1D!important; }
        body.role-admin .card-header::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:#FF5C00; border-radius:2px 0 0 2px; }
        body.role-admin .card-body { padding:16px 20px!important; }

        /* ── Filter forms / inputs ── */
        body.role-admin .form-control:focus,
        body.role-admin .form-select:focus { border-color:#FF5C00!important; box-shadow:0 0 0 3px rgba(255,92,0,.12)!important; outline:none!important; }
        body.role-admin .form-label { font-size:11px!important; font-weight:700!important; color:#9CA3AF!important; text-transform:uppercase!important; letter-spacing:.5px!important; }
        body.role-admin .form-control,
        body.role-admin .form-select { border-radius:8px!important; border-color:#E5E7EB!important; background:#FAFBFC!important; font-size:13px!important; }

        /* ── Section heading pattern ── */
        body.role-admin .section-title { font-size:14px!important; font-weight:700!important; color:#1D1D1D!important; display:flex; align-items:center; gap:8px; }
        body.role-admin h2.page-title, body.role-admin h3.section-title { color:#1D1D1D!important; font-family:'Poppins',sans-serif!important; }

        /* ── Pagination ── */
        body.role-admin .page-link { border-color:#E5E7EB!important; color:#374151!important; font-size:12px!important; border-radius:7px!important; }
        body.role-admin .page-item.active .page-link { background:#FF5C00!important; border-color:#FF5C00!important; color:#fff!important; }
        body.role-admin .page-item.disabled .page-link { opacity:.4!important; }

        /* ── Badges ── */
        body.role-admin .badge.bg-primary { background:#FF5C00!important; }
        body.role-admin .badge.bg-info    { background:#06B6D4!important; }

        /* ── Links ── */
        body.role-admin a:not(.btn):not(.nav-item):not(.nav-link):not(.s-nav-pill):not(.lm-scope-link) { color:#FF5C00!important; }
        body.role-admin a:not(.btn):not(.nav-item):not(.nav-link):not(.s-nav-pill):not(.lm-scope-link):hover { color:#e05200!important; }
        /* Header anchors must stay white — this rule comes after the orange link rule to win the cascade */
        body.role-admin .top-header a:not(.btn):not(.dropdown-item) { color:#fff!important; }
        /* Settings nav pills — preserve their own colour scheme */
        body.role-admin a.s-nav-pill { color:#475569!important; }
        body.role-admin a.s-nav-pill:hover { color:#1e293b!important; }
        body.role-admin a.s-nav-pill.active { background:#FF5C00!important; box-shadow:0 2px 8px rgba(255,92,0,.35)!important; color:#fff!important; }
        /* Flyout submenu — restore proper light-on-dark colour scheme */
        body.role-admin .tc-flyout-menu a { color:rgba(255,255,255,0.72)!important; }
        body.role-admin .tc-flyout-menu a:hover { color:#fff!important; background:rgba(255,92,0,0.14)!important; }
        body.role-admin .tc-flyout-menu a.active { color:#fff!important; background:rgba(255,92,0,0.30)!important; border-left:3px solid #FF5C00!important; font-weight:700!important; }
        /* Scope tabs — use their own colour scheme from the embedded blade CSS */
        body.role-admin .lm-scope-link { color:#374151!important; }
        body.role-admin .lm-scope-link:hover:not(.active) { color:#FF5C00!important; }
        body.role-admin .lm-scope-link.active { color:#fff!important; }

        /* ── Form focus ── */
        body.role-admin .form-control:focus,
        body.role-admin .form-select:focus { border-color:#FF5C00!important; box-shadow:0 0 0 3px rgba(255,92,0,.12)!important; }
    </style>
    @endif

    {{-- Global 419 handler: intercept all fetch() calls and redirect to login on session expiry --}}
    <script>
    (function () {
        var _origFetch = window.fetch;
        window.fetch = function (input, init) {
            init = Object.assign({}, init);
            // Ensure the server can detect AJAX requests (enables JSON 419 response)
            init.headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, init.headers);
            return _origFetch.call(window, input, init).then(function (response) {
                if (response.status === 419) {
                    window.location.href = @json(route('login'));
                }
                return response;
            });
        };
    })();
    </script>

    {{-- Broadcast / Echo config (injected before Vite bundle so echo.js can read it synchronously) --}}
    @php
        $bDriver = \App\Models\Setting::get('broadcast_driver', 'null');
        $bKey    = $bDriver === 'pusher'
                    ? \App\Models\Setting::getSecure('pusher_app_key', '')
                    : \App\Models\Setting::getSecure('reverb_app_key', '');
        $bConfig = [
            'driver'  => $bDriver,
            'key'     => $bKey,
            'cluster' => \App\Models\Setting::get('pusher_app_cluster', 'mt1'),
            'reverb'  => [
                'host'   => \App\Models\Setting::get('reverb_host', '127.0.0.1'),
                'port'   => (int) \App\Models\Setting::get('reverb_port', '8080'),
                'scheme' => \App\Models\Setting::get('reverb_scheme', 'http'),
            ],
        ];
    @endphp
    @if($bDriver !== 'null')
    <script>window.__BROADCAST__ = @json($bConfig);</script>
    @endif

    @vite(['resources/js/app.js'])

    <!-- Driver.js (guided tour) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <style>
    /* ── Driver.js Tour — Brand Theme ──────────────────────────────────── */
    .driver-popover {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        border-radius: 16px !important;
        box-shadow: 0 24px 64px rgba(99,102,241,.22), 0 6px 20px rgba(0,0,0,.12) !important;
        border: 1px solid rgba(99,102,241,.18) !important;
        max-width: 320px !important;
        padding: 0 !important;
        overflow: hidden !important;
        background: #fff !important;
    }
    .driver-popover::before {
        content: '';
        display: block;
        height: 4px;
        background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 55%, #06b6d4 100%);
        border-radius: 16px 16px 0 0;
    }
    .driver-popover-title {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-weight: 700 !important;
        font-size: 14.5px !important;
        color: #0f172a !important;
        padding: 14px 44px 0 18px !important;
        line-height: 1.35 !important;
        letter-spacing: -.1px !important;
    }
    .driver-popover-description {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-size: 12.5px !important;
        color: #475569 !important;
        line-height: 1.65 !important;
        padding: 7px 18px 2px !important;
    }
    .driver-popover-footer {
        display: flex !important;
        align-items: center !important;
        padding: 10px 18px 14px !important;
        gap: 7px !important;
        border-top: 1px solid #f1f5f9 !important;
        margin-top: 12px !important;
        background: #fafbff !important;
    }
    .driver-popover-progress-text {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        letter-spacing: .3px !important;
        flex: 1 !important;
        order: -1 !important;
    }
    .driver-popover-next-btn {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
        border: none !important;
        border-radius: 8px !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-weight: 600 !important;
        font-size: 12.5px !important;
        padding: 7px 15px !important;
        color: #fff !important;
        text-shadow: none !important;
        box-shadow: 0 2px 8px rgba(99,102,241,.38) !important;
        cursor: pointer !important;
        transition: opacity .15s !important;
    }
    .driver-popover-next-btn:hover,
    .driver-popover-next-btn:focus {
        opacity: .87 !important;
        box-shadow: 0 4px 12px rgba(99,102,241,.45) !important;
    }
    .driver-popover-prev-btn {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        color: #64748b !important;
        font-weight: 600 !important;
        font-size: 12.5px !important;
        padding: 7px 13px !important;
        text-shadow: none !important;
        cursor: pointer !important;
        transition: background .15s, color .15s !important;
    }
    .driver-popover-prev-btn:hover,
    .driver-popover-prev-btn:focus {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }
    .driver-popover-close-btn {
        position: absolute !important;
        top: 10px !important;
        right: 12px !important;
        background: none !important;
        border: none !important;
        color: #94a3b8 !important;
        font-size: 22px !important;
        line-height: 1 !important;
        padding: 0 !important;
        cursor: pointer !important;
        transition: color .15s !important;
    }
    .driver-popover-close-btn:hover { color: #475569 !important; }
    /* Welcome step — centered card */
    .tour-welcome-popover { max-width: 360px !important; }
    .tour-welcome-popover .driver-popover-title {
        text-align: center !important;
        font-size: 16px !important;
        padding: 18px 44px 0 !important;
    }
    .tour-welcome-popover .driver-popover-description { text-align: center !important; }
    .tour-welcome-popover .driver-popover-footer { justify-content: center !important; }
    .tour-welcome-popover .driver-popover-progress-text { display: none !important; }
    /* Step icon row */
    .t-row { display:flex; align-items:flex-start; gap:10px; margin-top:2px; }
    .t-ico  { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .t-ico .material-icons { font-size:18px !important; color:#fff !important; font-family:'Material Icons' !important; }
    .t-txt  { flex:1; }
    /* ──────────────────────────────────────────────────────────────────── */
    </style>

    @stack('styles')
</head>

<body class="{{ auth()->user()?->role === 'telecaller' ? 'role-telecaller' : '' }} {{ auth()->user()?->role === 'admin' ? 'role-admin' : '' }}">

    {{-- Sidebar backdrop (mobile/tablet) --}}
    <div id="sidebarBackdrop" onclick="closeSidebar()"></div>

    @include('layouts.sidebar')
    <div class="main-content" id="mainContent">
        @include('layouts.header')

        <div class="dashboard-content">

            {{-- Inline error banner (kept for validation errors) --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

        {{-- Site Footer --}}
        {{-- <div class="site-footer-bar">
            <div class="site-footer-top">
                <span class="site-footer-brand">
                    <span class="material-icons">school</span>
                    {{ \App\Models\Setting::get('site_name', 'Admission CRM') }}
                </span>
                <div class="site-footer-divider"></div>
                <span>&copy; {{ date('Y') }} All rights reserved.</span>
            </div>
            <div class="site-footer-bottom">
                <a href="{{ url('/privacy-policy') }}" target="_blank">Privacy Policy</a>
                <span class="site-footer-dot">&bull;</span>
                <a href="{{ url('/terms-of-service') }}" target="_blank">Terms of Service</a>
            </div>
        </div> --}}


    {{-- Documents Quick Access Modal --}}
    @if(auth()->check() && auth()->user()->role !== 'admin')
    <div class="modal fade" id="docsModal" tabindex="-1" aria-labelledby="docsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0;">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="docsModalLabel">
                        <span class="material-icons" style="color:#137fec;">folder_open</span>
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
    <script>
    (function () {
        const modal = document.getElementById('docsModal');
        if (!modal) return;
        const listUrl = @json(route('documents.list'));
        let loaded = false;

        modal.addEventListener('show.bs.modal', async function () {
            if (loaded) return;
            try {
                const res = await fetch(listUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const container = document.getElementById('docsListContainer');
                const loading   = document.getElementById('docsLoadingState');
                if (!data.ok || !data.documents.length) {
                    loading.innerHTML = '<span class="material-icons d-block mb-1" style="font-size:32px;color:#cbd5e1;">folder_open</span>No documents available.';
                    return;
                }
                const rows = data.documents.map(function(d) {
                    return '<div class="d-flex align-items-center justify-content-between py-2 border-bottom gap-3">' +
                        '<div class="d-flex align-items-center gap-2">' +
                        '<span class="material-icons" style="color:#64748b;font-size:20px;flex-shrink:0;">' + d.icon + '</span>' +
                        '<div>' +
                        '<div class="fw-semibold" style="font-size:14px;">' + d.title + '</div>' +
                        '<div class="text-muted" style="font-size:12px;">' + d.file_name + ' &middot; ' + d.file_size_formatted + ' &middot; ' + d.created_at + '</div>' +
                        '</div></div>' +
                        '<div class="d-flex gap-2" style="flex-shrink:0;">' +
                        '<a href="' + d.view_url + '" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">' +
                        '<span class="material-icons" style="font-size:15px;">visibility</span>View</a>' +
                        '<a href="' + d.download_url + '" class="btn btn-sm btn-primary d-flex align-items-center gap-1">' +
                        '<span class="material-icons" style="font-size:15px;">download</span>Download</a>' +
                        '</div>' +
                        '</div>';
                });
                container.innerHTML = rows.join('');
                loading.style.display = 'none';
                container.style.display = 'block';
                loaded = true;
            } catch (e) {
                document.getElementById('docsLoadingState').textContent = 'Failed to load documents.';
            }
        });
    })();
    </script>
    @endif

    </div>

    @if (auth()->check() && auth()->user()->role === 'telecaller')
        {{-- data-turbo-eval="false": run once on hard load; Turbo navigation does NOT restart this interval --}}
        <script data-turbo-eval="false">
            (function() {
                const csrfToken = @json(csrf_token());
                const heartbeatUrl = @json(route('telecaller.status.heartbeat'));

                async function post(url) {
                    try {
                        return await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({})
                        });
                    } catch (e) {
                        return null;
                    }
                }

                post(heartbeatUrl);
                setInterval(function() { post(heartbeatUrl); }, 30000);

            })();
        </script>
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
                const soundToggle = document.getElementById('teleNotifSoundToggle');
                const markReadBtn = document.getElementById('teleNotifMarkRead');

                if (!badge || !missedWrap || !followupWrap || !systemWrap) {
                    return;
                }

                let previousCount = 0;

                function getSoundEnabled() {
                    const v = localStorage.getItem(soundKey);
                    return v !== '0';
                }

                function setSoundEnabled(v) {
                    localStorage.setItem(soundKey, v ? '1' : '0');
                    if (soundToggle) soundToggle.checked = !!v;
                }

                function playBeep() {
                    if (!getSoundEnabled()) return;
                    try {
                        const audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioCtx.createOscillator();
                        const gainNode = audioCtx.createGain();

                        oscillator.type = 'sine';
                        oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                        gainNode.gain.setValueAtTime(0.001, audioCtx.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.2, audioCtx.currentTime + 0.01);
                        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);

                        oscillator.connect(gainNode);
                        gainNode.connect(audioCtx.destination);
                        oscillator.start();
                        oscillator.stop(audioCtx.currentTime + 0.22);
                    } catch (e) {}
                }

                function renderList(items, renderer, emptyText) {
                    if (!items || !items.length) {
                        return `<div class="small text-muted">${emptyText}</div>`;
                    }
                    return items.map(renderer).join('');
                }

                function getSeenIds(key) {
                    try {
                        const raw = localStorage.getItem(key);
                        const parsed = raw ? JSON.parse(raw) : [];
                        return Array.isArray(parsed) ? parsed.map(Number) : [];
                    } catch (e) {
                        return [];
                    }
                }

                function setSeenIds(key, ids) {
                    localStorage.setItem(key, JSON.stringify(Array.from(new Set(ids.map(Number)))));
                }

                function updateBadge(count) {
                    if (count > 0) {
                        badge.style.display = 'inline-block';
                        badge.textContent = count > 99 ? '99+' : String(count);
                    } else {
                        badge.style.display = 'none';
                    }
                }

                async function fetchNotifications() {
                    try {
                        const res = await fetch(snapshotUrl, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
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
                        const whatsappNotifications = rawWhatsapp;
                        const systemNotifications = rawSystem;

                        const count = missedCalls.length + followupReminders.length + whatsappNotifications.length + systemNotifications.length;
                        if (count > previousCount) {
                            playBeep();
                        }
                        previousCount = count;
                        updateBadge(count);

                        missedWrap.innerHTML = renderList(
                            missedCalls,
                            (item) => {
                                const link = item.lead_url ?
                                    `<a href="${item.lead_url}" class="small fw-semibold text-decoration-none js-notif-open">Open</a>` :
                                    '';
                                return `<div class="py-1 border-bottom">
                                    <div class="fw-semibold">${item.lead_name}</div>
                                    <div class="text-muted">${item.lead_code} | ${item.phone || '-'} ${item.time ? '| ' + item.time : ''}</div>
                                    ${link}
                                </div>`;
                            },
                            'No missed calls.'
                        );

                        followupWrap.innerHTML = renderList(
                            followupReminders,
                            (item) => `<div class="py-1 border-bottom">
                                <div class="fw-semibold">${item.lead_name}</div>
                                <div class="text-muted">${item.lead_code} | ${item.next_followup || '-'}</div>
                                <span class="badge ${item.type === 'overdue' ? 'bg-danger' : 'bg-warning text-dark'} mt-1">${item.type}</span>
                            </div>`,
                            'No reminders.'
                        );

                        if (waWrap) {
                            waWrap.innerHTML = renderList(
                                whatsappNotifications,
                                (item) => `<div class="py-1 border-bottom">
                                    <a href="${item.link || '#'}" class="fw-semibold text-decoration-none d-block">${item.title || 'WhatsApp'}</a>
                                    <div class="text-muted">${item.message || ''}</div>
                                    <div class="text-muted" style="font-size:11px;">${item.time || ''}</div>
                                </div>`,
                                'No WhatsApp messages.'
                            );
                        }

                        systemWrap.innerHTML = renderList(
                            systemNotifications,
                            (item) => `<div class="py-1 border-bottom">
                                <div>${item.message}</div>
                                <div class="text-muted">${item.time || ''}</div>
                            </div>`,
                            'No system notifications.'
                        );
                    } catch (e) {}
                }

                soundToggle?.addEventListener('change', function() {
                    setSoundEnabled(!!this.checked);
                });

                markReadBtn?.addEventListener('click', async function() {
                    try {
                        const res = await fetch(snapshotUrl, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const snap = await res.json();
                        const missedIds = (snap.missed_calls || []).map(item => Number(item.id)).filter(Boolean);
                        const followupIds = (snap.followup_reminders || []).map(item => Number(item.id)).filter(Boolean);
                        setSeenIds(seenMissedKey, [...getSeenIds(seenMissedKey), ...missedIds]);
                        setSeenIds(seenFollowupKey, [...getSeenIds(seenFollowupKey), ...followupIds]);

                        await fetch(markReadUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({})
                        });
                        await fetchNotifications();
                    } catch (e) {}
                });

                setSoundEnabled(getSoundEnabled());
                fetchNotifications();
                setInterval(fetchNotifications, 60000);

                // Immediately refresh when a missed call occurs (fired by global-call.js)
                window.addEventListener('gc:missedCall', function () {
                    fetchNotifications();
                });

                // "Open" link in missed call notifications — use Turbo.visit() so navigation
                // happens without a full page reload (Bootstrap dropdown swallows the click
                // before Turbo's own handler sees it).
                document.addEventListener('click', function (e) {
                    const a = e.target.closest('a.js-notif-open');
                    if (!a) return;
                    e.preventDefault();
                    const url = a.href;
                    const dropdown = a.closest('.dropdown');
                    if (dropdown && window.bootstrap) {
                        const btn = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                        if (btn) bootstrap.Dropdown.getInstance(btn)?.hide();
                    }
                    if (window.Turbo) {
                        Turbo.visit(url);
                    } else {
                        window.location.href = url;
                    }
                });
            })();
        </script>
    @endif


    {{--
        TCN Softphone — persistent iframe widget (bottom-right corner).
        NO role check here — the widget is always rendered when provider=tcn.
        Visibility is controlled entirely by JavaScript (postMessage events from
        the iframe). This guarantees data-turbo-permanent can always match the
        element by id across every Turbo navigation and NEVER recreates the iframe.

        Persistence strategy:
          • data-turbo-permanent: Turbo Drive matches #tcnWidget by id and keeps
            the existing DOM node (including the live iframe) on every navigation.
          • The iframe loads once; SIP connects once; calls never drop on nav.
          • data-turbo-eval="false" on the script prevents duplicate event listeners.
    --}}
    @if(\App\Models\Setting::get('primary_call_provider') === 'tcn' && in_array(auth()->user()->role, ['telecaller', 'manager']))
    <div id="tcnWidget" data-turbo-permanent>
        <iframe id="tcnSoftphoneFrame"
            src="/softphone?v={{ filemtime(resource_path('views/softphone.blade.php')) }}"
            allow="microphone"
            style="position:fixed;bottom:80px;right:20px;width:300px;height:480px;
                   border:none;z-index:1065;border-radius:14px;
                   box-shadow:0 8px 32px rgba(0,0,0,.20);display:none;
                   transition:height .2s,width .2s;">
        </iframe>
        {{--
            Toggle button — always in DOM with a neutral gray color.
            JS turns it green on TCN_READY, shows/hides the iframe on click.
            Using inline display:flex (not Bootstrap d-flex) so JS display:none works.
        --}}
        <button id="tcnToggleBtn" title="Toggle Softphone"
            style="position:fixed;bottom:24px;right:26px;z-index:1066;
                   width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;
                   background:#64748b;color:#fff;display:flex;
                   align-items:center;justify-content:center;
                   box-shadow:0 4px 20px rgba(0,0,0,.22);transition:background .25s;">
            <span class="material-icons" style="font-size:24px;pointer-events:none;" id="tcnToggleIco">phone</span>
        </button>
    </div>
    {{--
        data-turbo-eval="false" — runs ONCE on the first hard page load.
        Turbo navigations do NOT re-execute it, so event listeners are never
        duplicated. DOM refs are captured immediately (elements are above this script).
    --}}
    <script data-turbo-eval="false">
    (function () {
        var _frame    = document.getElementById('tcnSoftphoneFrame');
        var _btn      = document.getElementById('tcnToggleBtn');
        var _ico      = document.getElementById('tcnToggleIco');
        var _visible  = false;
        var _sipReady = false;   // true once TCN_READY received this tab lifetime

        // ── Header button helpers — always look up fresh DOM refs so they
        //    work after Turbo navigation replaces the header HTML. ────────
        function _rdyUpdate(active) {
            var btn = document.getElementById('tcnReadyBtn');
            var ico = document.getElementById('tcnReadyIco');
            var lbl = document.getElementById('tcnReadyLabel');
            var dot = document.getElementById('tcnStatusDot');
            if (!btn) return;
            if (active) {
                btn.style.background = '#10b981';
                btn.style.boxShadow = '0 2px 10px rgba(16,185,129,.4)';
                if (ico) ico.textContent = 'phone';
                if (lbl) lbl.textContent = 'Ready';
                if (dot) dot.style.background = 'rgba(255,255,255,.95)';
            } else {
                btn.style.background = '#475569';
                btn.style.boxShadow = '0 1px 6px rgba(0,0,0,.18)';
                if (ico) ico.textContent = 'phone_disabled';
                if (lbl) lbl.textContent = 'Not Ready';
                if (dot) dot.style.background = 'rgba(255,255,255,.35)';
            }
        }

        function _rdyConnecting() {
            var btn = document.getElementById('tcnReadyBtn');
            var ico = document.getElementById('tcnReadyIco');
            var lbl = document.getElementById('tcnReadyLabel');
            var dot = document.getElementById('tcnStatusDot');
            if (!btn) return;
            btn.style.background = '#f59e0b';
            btn.style.boxShadow = '0 2px 10px rgba(245,158,11,.4)';
            if (ico) ico.textContent = 'hourglass_empty';
            if (lbl) lbl.textContent = 'Connecting\u2026';
            if (dot) dot.style.background = 'rgba(255,255,255,.7)';
        }

        // ── Set initial header button state on hard page load ─────────────
        try {
            if (localStorage.getItem('tcn_sip_active') === '1') { _rdyConnecting(); }
        } catch (_) {}

        // ── Restore header button state after every Turbo navigation ──────
        // (also fires on the first hard load, after DOMContentLoaded)
        document.addEventListener('turbo:load', function () {
            if (_sipReady) { _rdyUpdate(true); return; }
            try {
                if (localStorage.getItem('tcn_sip_active') === '1') { _rdyConnecting(); }
            } catch (_) {}
        });

        // ── show / hide iframe ────────────────────────────────────────────
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

        // ── Header Start / Stop button (event delegation — survives Turbo nav) ──
        // Using capture phase so it fires before any other click handlers.
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#tcnReadyBtn')) return;
            var lbl = document.getElementById('tcnReadyLabel');
            var isReady = lbl && lbl.textContent.trim() === 'Ready';
            if (isReady) {
                // STOP — send silent logout (no browser confirm needed; user just clicked Stop)
                if (window.GC && typeof window.GC.disableCallingMode === 'function') {
                    window.GC.disableCallingMode();
                } else {
                    // Manager path: no GC — directly clear localStorage and tell iframe to logout
                    try { localStorage.removeItem('tcn_sip_active'); } catch (_) {}
                    if (_frame && _frame.contentWindow) {
                        _frame.contentWindow.postMessage({ type: 'LOGOUT_SILENT' }, '*');
                    }
                }
                _sipReady = false;
                _rdyUpdate(false);
                hide();
            } else {
                // START — persist flag + boot SIP in iframe
                _rdyConnecting();
                if (window.GC && typeof window.GC.enableCallingMode === 'function') {
                    window.GC.enableCallingMode();
                } else {
                    // Manager path: no GC — directly persist and send START_SIP to iframe
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

        // ── Floating toggle button — show/hide iframe ONLY (no SIP side-effects) ──
        if (_btn) {
            _btn.addEventListener('click', function () {
                if (_visible) { hide(); } else { show(); }
            });
        }

        // ── Forward [data-phone] attribute clicks to the iframe ───────────
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-phone]');
            if (!el || !_frame) return;
            var phone = el.getAttribute('data-phone');
            if (!phone) return;
            _frame.contentWindow.postMessage({ type: 'SET_PHONE', phone: phone }, '*');
            if (!_visible) show();
        }, true);

        // ── Messages from softphone iframe ────────────────────────────────
        // Registered once; persists across Turbo navigations for the tab lifetime.
        window.addEventListener('message', function (ev) {
            var d = ev.data;
            if (!d || typeof d !== 'object') return;

            switch (d.type) {
                case 'TCN_READY':
                    _sipReady = true;
                    if (_btn) { _btn.style.background = '#10b981'; _btn.style.animation = ''; }
                    _rdyUpdate(true);
                    break;

                case 'TCN_INCOMING_CALL':
                    // Show iframe so the incoming banner is visible
                    if (!_visible) show();
                    // Pulse the toggle button green to draw attention
                    if (_btn) { _btn.style.background = '#10b981'; _btn.style.animation = 'tcnBtnPulse .6s ease-in-out infinite'; }
                    break;

                case 'TCN_INCOMING_REJECTED':
                    if (_btn) { _btn.style.animation = ''; }
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
                    // Reconnect is in-flight — show amber on both buttons
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

    @if (auth()->check() && auth()->user()->role === 'telecaller')
        {{--
            data-turbo-eval="false" prevents Turbo Drive from re-evaluating this
            script on every navigation — avoids duplicate GC instances and double
            /call/end requests for a single call.
        --}}
        <script src="{{ asset('js/global-call.js?v=4') }}" data-turbo-eval="false"></script>

        {{-- GC lifecycle helpers — runs once on first hard load, persists via data-turbo-eval --}}
        <script data-turbo-eval="false">
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                var metaProvider = document.querySelector('meta[name="call-provider"]');
                if (metaProvider && metaProvider.getAttribute('content') === 'tcn') {
                    if (window.GC && typeof window.GC.initDevice === 'function') {
                        window.GC.initDevice();
                    }
                }
            });

            document.addEventListener('turbo:load', function () {
                var m = document.querySelector('meta[name="csrf-token"]');
                if (m && window.GC) {
                    window.GC._csrf = m.getAttribute('content');
                }
            });
        })();
        </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function openSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar)  sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
        }

        function closeSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar)  sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }

        function toggleSidebar() {
            const sidebar     = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            if (!sidebar) return;

            if (window.innerWidth > 991) {
                const collapsed = sidebar.classList.toggle('desktop-collapsed');
                mainContent && mainContent.classList.toggle('desktop-expanded', collapsed);
                try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); } catch(e) {}
            } else {
                if (sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }
        }

        function _restoreSidebarState() {
            if (window.innerWidth <= 991) return;
            const sidebar     = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            if (!sidebar) return;
            try {
                const collapsed = localStorage.getItem('sidebarCollapsed') === '1';
                sidebar.classList.toggle('desktop-collapsed', collapsed);
                mainContent && mainContent.classList.toggle('desktop-expanded', collapsed);
            } catch(e) {}
        }

        document.addEventListener('turbo:load', _restoreSidebarState);
        document.addEventListener('DOMContentLoaded', _restoreSidebarState);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (window.innerWidth > 991) {
                    const sidebar     = document.getElementById('sidebar');
                    const mainContent = document.getElementById('mainContent');
                    if (sidebar && sidebar.classList.contains('desktop-collapsed')) {
                        sidebar.classList.remove('desktop-collapsed');
                        mainContent && mainContent.classList.remove('desktop-expanded');
                        try { localStorage.setItem('sidebarCollapsed', '0'); } catch(e) {}
                    }
                } else {
                    closeSidebar();
                }
            }
        });
    </script>

    @stack('scripts')

    {{-- Guided Tour (Driver.js) — shown only on first login, sidebar-only, no page navigation --}}
    @auth
    @if(!auth()->user()->has_seen_tour)
    <script data-turbo-eval="false">
    (function () {
        const COMPLETE_URL = @json(route('tour.complete'));
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const ROLE = @json(auth()->user()->role);
        const USER_FIRST = @json(explode(' ', trim(auth()->user()->name))[0]);

        function markDone() {
            fetch(COMPLETE_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function () {});
        }

        // Build a rich description row: colored icon box + text
        function sd(icon, bg, text) {
            return '<div class="t-row"><div class="t-ico" style="background:' + bg + '"><span class="material-icons">' + icon + '</span></div><span class="t-txt">' + text + '</span></div>';
        }

        // Centered welcome step (no element target)
        function welcomeStep(panelLabel, stepCount) {
            return {
                popover: {
                    popoverClass: 'tour-welcome-popover',
                    title: '<span style="font-size:28px;display:block;margin-bottom:6px;">👋</span>Welcome, ' + USER_FIRST + '!',
                    description:
                        '<p style="margin:6px 0 10px;color:#475569;font-size:13px;line-height:1.65;">' +
                        'Here\'s a quick <strong style="color:#6366f1;">' + stepCount + '-step tour</strong> of your <strong>' + panelLabel + ' panel</strong>.' +
                        '<br>Use the arrows to navigate or skip anytime.</p>' +
                        '<div style="display:flex;justify-content:center;gap:18px;padding:6px 0;">' +
                            '<span style="display:flex;flex-direction:column;align-items:center;gap:3px;font-size:11px;color:#94a3b8;font-weight:600;">' +
                                '<span style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">' +
                                    '<span class="material-icons" style="font-size:14px;color:#6366f1;">explore</span>' +
                                '</span>Explore</span>' +
                            '<span style="display:flex;flex-direction:column;align-items:center;gap:3px;font-size:11px;color:#94a3b8;font-weight:600;">' +
                                '<span style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">' +
                                    '<span class="material-icons" style="font-size:14px;color:#10b981;">check_circle</span>' +
                                '</span>Learn</span>' +
                            '<span style="display:flex;flex-direction:column;align-items:center;gap:3px;font-size:11px;color:#94a3b8;font-weight:600;">' +
                                '<span style="width:28px;height:28px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">' +
                                    '<span class="material-icons" style="font-size:14px;color:#f59e0b;">rocket_launch</span>' +
                                '</span>Launch</span>' +
                        '</div>',
                }
            };
        }

        const tourSteps = {
            admin: [
                welcomeStep('Admin', 13),
                { element: '#nav-admin-dashboard',      popover: { title: 'Dashboard',          description: sd('home',           'linear-gradient(135deg,#6366f1,#4f46e5)', 'Bird\'s eye view of admissions, leads, and team activity.') } },
                { element: '#nav-admin-users',          popover: { title: 'User Management',     description: sd('group',           'linear-gradient(135deg,#8b5cf6,#6366f1)', 'Create and manage admins, managers, and telecallers.') } },
                { element: '#nav-admin-leads',          popover: { title: 'Lead Management',     description: sd('person_search',   'linear-gradient(135deg,#06b6d4,#0891b2)', 'View all leads — assigned, unassigned, converted, and lost.') } },
                { element: '#nav-admin-campaigns',      popover: { title: 'Campaigns',           description: sd('campaign',        'linear-gradient(135deg,#f59e0b,#d97706)', 'Monitor campaign performance and all campaign contacts.') } },
                { element: '#nav-admin-social',         popover: { title: 'Social Media',        description: sd('share',           'linear-gradient(135deg,#10b981,#059669)', 'Manage social marketing and track incoming leads.') } },
                { element: '#nav-admin-email',          popover: { title: 'Email Marketing',     description: sd('mail',            'linear-gradient(135deg,#6366f1,#4f46e5)', 'Create email campaigns and manage reusable templates.') } },
                { element: '#nav-admin-reports',        popover: { title: 'Reports',             description: sd('bar_chart',       'linear-gradient(135deg,#ef4444,#dc2626)', 'Telecaller performance, conversions, lead sources, call efficiency.') } },
                { element: '#nav-admin-automation',     popover: { title: 'Automation',          description: sd('auto_awesome',    'linear-gradient(135deg,#8b5cf6,#7c3aed)', 'Lead assignment rules, follow-up reminders, and escalations.') } },
                { element: '#nav-admin-courses',        popover: { title: 'Courses',             description: sd('school',          'linear-gradient(135deg,#06b6d4,#0891b2)', 'Manage the course catalog your team works with.') } },
                { element: '#nav-admin-academic-years', popover: { title: 'Academic Years',      description: sd('calendar_today',  'linear-gradient(135deg,#f59e0b,#d97706)', 'Define academic years to segment lead and intake data.') } },
                { element: '#nav-admin-intakes',        popover: { title: 'Course Intakes',      description: sd('event_seat',      'linear-gradient(135deg,#10b981,#059669)', 'Configure intake batches for each course and academic year.') } },
                { element: '#nav-admin-documents',      popover: { title: 'Documents',           description: sd('folder_open',     'linear-gradient(135deg,#64748b,#475569)', 'Upload and share documents with your team.') } },
                { element: '#nav-admin-settings',       popover: { title: 'Settings',            description: sd('settings',        'linear-gradient(135deg,#0f172a,#1e293b)', 'Configure site name, branding, integrations, and more.') } },
            ],
            manager: [
                welcomeStep('Manager', 9),
                { element: '#nav-mgr-dashboard',    popover: { title: 'Dashboard',            description: sd('home',          'linear-gradient(135deg,#6366f1,#4f46e5)', 'Command center — team performance, lead stats, and today\'s activity.') } },
                { element: '#nav-mgr-leads',        popover: { title: 'Leads',                description: sd('person_search', 'linear-gradient(135deg,#06b6d4,#0891b2)', 'View all leads, duplicates, and the unassigned lead pool.') } },
                { element: '#nav-mgr-telecallers',  popover: { title: 'Telecallers',          description: sd('group',         'linear-gradient(135deg,#8b5cf6,#6366f1)', 'Monitor telecaller presence, performance, and assignments.') } },
                { element: '#nav-mgr-campaigns',    popover: { title: 'Campaigns',            description: sd('campaign',      'linear-gradient(135deg,#f59e0b,#d97706)', 'Run and track outreach campaigns and view performance.') } },
                { element: '#nav-mgr-email',        popover: { title: 'Email Campaigns',      description: sd('mail',          'linear-gradient(135deg,#6366f1,#4f46e5)', 'Send bulk email campaigns to your lead lists.') } },
                { element: '#nav-mgr-whatsapp',     popover: { title: 'WhatsApp Chat',        description: sd('chat',          'linear-gradient(135deg,#10b981,#059669)', 'View and respond to all WhatsApp conversations with leads.') } },
                { element: '#nav-mgr-followups',    popover: { title: 'Follow-up Management', description: sd('event_repeat',  'linear-gradient(135deg,#f59e0b,#d97706)', 'Track today\'s, overdue, upcoming, and missed follow-ups.') } },
                { element: '#nav-mgr-calllogs',     popover: { title: 'Call Logs',            description: sd('call_log',      'linear-gradient(135deg,#ef4444,#dc2626)', 'Review inbound, outbound, and missed calls across your team.') } },
                { element: '#nav-mgr-reports',      popover: { title: 'Reports & Analytics',  description: sd('bar_chart',     'linear-gradient(135deg,#ef4444,#dc2626)', 'Conversions, telecaller performance, and lead response time.') } },
            ],
            telecaller: [
                welcomeStep('Telecaller', 9),
                { element: '#nav-tc-dashboard',     popover: { title: 'Dashboard',        description: sd('home',         'linear-gradient(135deg,#6366f1,#4f46e5)', 'Your personal dashboard — leads, today\'s tasks, and stats.') } },
                { element: '#nav-tc-leads',         popover: { title: 'My Leads',         description: sd('person_search','linear-gradient(135deg,#06b6d4,#0891b2)', 'View and manage all leads assigned to you.') } },
                { element: '#nav-tc-campaigns',     popover: { title: 'My Campaigns',     description: sd('campaign',     'linear-gradient(135deg,#f59e0b,#d97706)', 'Access campaigns assigned to you and reach out to contacts.') } },
                { element: '#nav-tc-availability',  popover: { title: 'My Availability',  description: sd('schedule',     'linear-gradient(135deg,#8b5cf6,#6366f1)', 'Set your schedule so managers know when you\'re on duty.') } },
                { element: '#nav-tc-whatsapp',      popover: { title: 'WhatsApp Chat',    description: sd('chat',         'linear-gradient(135deg,#10b981,#059669)', 'Send and receive WhatsApp messages with your leads.') } },
                { element: '#nav-tc-calls',         popover: { title: 'Call Management',  description: sd('call',         'linear-gradient(135deg,#ef4444,#dc2626)', 'Log outbound and inbound calls, and review missed calls.') } },
                { element: '#nav-tc-followups',     popover: { title: 'Follow-ups',       description: sd('event_repeat', 'linear-gradient(135deg,#f59e0b,#d97706)', 'Track scheduled follow-ups — today\'s, overdue, upcoming, done.') } },
                { element: '#nav-tc-performance',   popover: { title: 'My Performance',   description: sd('trending_up',  'linear-gradient(135deg,#10b981,#059669)', 'Check your daily, weekly, and monthly performance stats.') } },
                { element: '#nav-tc-reports',       popover: { title: 'My Reports',       description: sd('bar_chart',    'linear-gradient(135deg,#ef4444,#dc2626)', 'Download your personal performance and activity reports.') } },
            ],
            report_viewer: [
                welcomeStep('Report Viewer', 2),
                { element: '#nav-rv-dashboard', popover: { title: 'Dashboard',  description: sd('home',      'linear-gradient(135deg,#6366f1,#4f46e5)', 'Overview of key metrics — conversions, performance, and trends.') } },
                { element: '#nav-rv-reports',   popover: { title: 'Reports',    description: sd('bar_chart', 'linear-gradient(135deg,#ef4444,#dc2626)', 'Detailed reports: performance, conversions, lead sources, and more.') } },
            ],
        };

        const steps = tourSteps[ROLE];
        if (!steps) return;

        // Welcome step has no element — keep it. All others require the element to exist.
        const validSteps = steps.filter(function (s) {
            return !s.element || document.querySelector(s.element);
        });
        if (!validSteps.length) return;

        // Sidebar-right positioning for all element-targeted steps
        validSteps.forEach(function (s) {
            if (s.element) {
                s.popover.side  = 'right';
                s.popover.align = 'start';
            }
        });

        function startTour() {
            if (typeof window.driver === 'undefined') return;
            const driverObj = window.driver.js.driver({
                showProgress: true,
                animate: true,
                overlayOpacity: 0.55,
                smoothScroll: true,
                allowClose: true,
                progressText: 'Step @{{current}} of @{{total}}',
                nextBtnText: 'Next →',
                prevBtnText: '← Back',
                doneBtnText: '✓ Finish Tour',
                onDestroyStarted: function () {
                    markDone();
                    driverObj.destroy();
                },
                steps: validSteps,
            });
            driverObj.drive();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { setTimeout(startTour, 400); });
        } else {
            setTimeout(startTour, 400);
        }
    })();
    </script>
    @endif
    @endauth

    {{-- Chart.js global defaults — applied after page scripts load Chart.js --}}
    <script>
        function _applyChartDefaults() {
            if (typeof Chart !== 'undefined') {
                const isTelecaller = document.body.classList.contains('role-telecaller');
                Chart.defaults.font.family    = isTelecaller ? "'Lato', sans-serif" : "'Plus Jakarta Sans', sans-serif";
                Chart.defaults.font.size      = 12;
                Chart.defaults.color          = '#64748b';
                Chart.defaults.plugins.legend.labels.usePointStyle = true;
                Chart.defaults.plugins.legend.labels.padding        = 20;
                Chart.defaults.plugins.tooltip.backgroundColor      = '#0f172a';
                Chart.defaults.plugins.tooltip.titleColor           = '#f8fafc';
                Chart.defaults.plugins.tooltip.bodyColor            = '#cbd5e1';
                Chart.defaults.plugins.tooltip.padding              = 10;
                Chart.defaults.plugins.tooltip.cornerRadius         = 8;
                Chart.defaults.plugins.tooltip.displayColors        = true;
                Chart.defaults.scale.grid.color                     = 'rgba(0,0,0,.04)';
                Chart.defaults.scale.grid.drawBorder                = false;
            }
        }
        document.addEventListener('DOMContentLoaded', _applyChartDefaults);
        document.addEventListener('turbo:load',       _applyChartDefaults);
    </script>

    {{-- Global Toast Container --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1090;">
        @if(session('success'))
            <div id="flashToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div id="flashToastError" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">{{ session('error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Show Bootstrap flash toasts on both hard page loads AND Turbo navigations.
        function _showFlashToasts() {
            ['flashToast', 'flashToastError'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el && !el.dataset.bsToastShown) {
                    el.dataset.bsToastShown = '1';
                    new bootstrap.Toast(el, { delay: 4000 }).show();
                }
            });
        }
        document.addEventListener('DOMContentLoaded', _showFlashToasts);
        document.addEventListener('turbo:load',       _showFlashToasts);
    </script>

    {{-- WhatsApp Real-Time Inbound Notifications (5 s polling) --}}
    @auth
    <div id="waToastStack" style="position:fixed;top:76px;right:20px;z-index:9999;width:320px;display:flex;flex-direction:column;gap:8px;pointer-events:none;"></div>
    <script data-turbo-eval="false">
    (function () {
        @if(auth()->user()->role === 'telecaller')
        const pollUrl = @json(route('telecaller.whatsapp.inbox-poll'));
        @elseif(auth()->user()->role === 'manager')
        const pollUrl = @json(route('manager.whatsapp.inbox-poll'));
        @else
        return; // admin or other roles — no WA toasts
        @endif

        const LS_KEY = 'wa_notif_ts_{{ auth()->id() }}';
        let   lastTs   = localStorage.getItem(LS_KEY) || null;
        let   audioCtx = null;
        const shownIds = new Set();

        function playWaSound() {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                [[1100, 0], [880, 0.18]].forEach(function(pair) {
                    const freq = pair[0], delay = pair[1];
                    const osc  = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain); gain.connect(audioCtx.destination);
                    osc.type = 'sine'; osc.frequency.value = freq;
                    const t = audioCtx.currentTime + delay;
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
                    (link ? '<a href="' + esc(link) + '" style="display:inline-block;margin-top:6px;font-size:12px;font-weight:600;color:#137fec;text-decoration:none;">Open Chat &rarr;</a>' : '') +
                  '</div>' +
                  '<button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;padding:0;flex-shrink:0;">&times;</button>' +
                '</div>';
            stack.appendChild(div);
            setTimeout(function() { try { div.remove(); } catch(e){} }, 9000);
        }

        function showLoginSummary(count) {
            showToast(
                count + ' unread WhatsApp message' + (count > 1 ? 's' : ''),
                'You have messages that arrived while you were away.',
                null
            );
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
                    // Suppress if user is already viewing this lead's chat page
                    if (item.link) {
                        try { if (new URL(item.link).pathname === currentPath) return false; } catch(e) {}
                    }
                    return true;
                });
                items.forEach(function(item) { if (item.id) shownIds.add(item.id); });

                if (items.length > 0) {
                    playWaSound();
                    if (data.is_first) {
                        showLoginSummary(items.length);
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

        // Real-time: subscribe after ES modules load (DOMContentLoaded fires after module scripts)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[WA-RT] Echo:', window.Echo ? 'initialized' : 'NULL', '| __BROADCAST__:', JSON.stringify(window.__BROADCAST__));
            if (window.Echo) {
                try {
                    window.Echo.private('whatsapp.inbox.{{ auth()->id() }}')
                        .listen('.message.new', function(data) {
                            console.log('[WA-RT] Pusher event received:', data);
                            poll();
                            window.dispatchEvent(new CustomEvent('wa:message.new', { detail: data }));
                        });
                    console.log('[WA-RT] Subscribed to whatsapp.inbox.{{ auth()->id() }}');
                } catch (e) { console.error('[WA-RT] Subscribe error:', e); }
            }
        });
    })();
    </script>
    @endauth

    @auth
    {{-- Idle Session Timeout (15 minutes) --}}
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
    {{-- data-turbo-eval="false": idle timer runs once; Turbo navigation resets it (counts as activity) --}}
    <script data-turbo-eval="false">
    (function () {
        const IDLE_TIMEOUT    = 15 * 60 * 1000; // 15 minutes
        const WARN_BEFORE     = 60 * 1000;       // show warning 60s before timeout
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

        // Any user interaction resets the idle timer
        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
            document.addEventListener(evt, resetTimers, { passive: true });
        });

        // Turbo navigation counts as activity — always reset idle timer on page change
        document.addEventListener('turbo:load', resetTimers);

        // Stay logged in button — bind once; button is inside idleWarningModal which
        // is not a permanent element, so re-bind after each Turbo page swap.
        function _bindStayBtn() {
            var btn = document.getElementById('idleStayBtn');
            if (btn && !btn.dataset.idleBound) {
                btn.dataset.idleBound = '1';
                btn.addEventListener('click', resetTimers);
            }
        }
        document.addEventListener('DOMContentLoaded', _bindStayBtn);
        document.addEventListener('turbo:load',       _bindStayBtn);

        // Start timers immediately
        resetTimers();
    })();
    </script>
    @endauth

    {{-- Manager Notification Bell Polling --}}
    @auth
    @if(auth()->user()->role === 'manager')
    <script data-turbo-eval="false">
    (function () {
        const SNAPSHOT_URL = @json(route('manager.notifications.snapshot'));
        const MARK_READ_URL = @json(route('manager.notifications.read-all'));
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function esc(s) {
            return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function renderItems(containerId, items, emptyMsg) {
            const el = document.getElementById(containerId);
            if (!el) return;
            if (!items || items.length === 0) {
                el.innerHTML = '<span class="text-muted" style="font-size:12px;">' + esc(emptyMsg) + '</span>';
                return;
            }
            el.innerHTML = items.map(function (n) {
                const link = n.link && n.link !== '#'
                    ? '<a href="' + esc(n.link) + '" style="font-size:12px;color:#6366f1;text-decoration:none;font-weight:600;display:block;margin-top:2px;">View &rarr;</a>'
                    : '';
                return '<div style="padding:6px 0;border-bottom:1px solid #f1f5f9;">' +
                    '<div style="font-size:12px;font-weight:600;color:#0f172a;line-height:1.3;">' + esc(n.title) + '</div>' +
                    '<div style="font-size:11px;color:#64748b;margin-top:1px;">' + esc(n.message) + '</div>' +
                    '<div style="font-size:10px;color:#94a3b8;margin-top:2px;">' + esc(n.time) + '</div>' +
                    link +
                    '</div>';
            }).join('');
        }

        window.mgrFetchNotifs = async function () {
            try {
                const res = await fetch(SNAPSHOT_URL, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.ok) return;

                // Badge
                const badge = document.getElementById('mgrNotifBadge');
                if (badge) {
                    const count = data.badge_count || 0;
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = count > 0 ? '' : 'none';
                }

                renderItems('mgrNotifLeads',     data.lead_notifications,     'No lead assignments.');
                renderItems('mgrNotifFollowups',  data.followup_notifications, 'No follow-up alerts.');
                renderItems('mgrNotifSla',        data.sla_notifications,      'No SLA escalations.');
                renderItems('mgrNotifWhatsapp',   data.whatsapp_notifications, 'No WhatsApp messages.');
            } catch (e) {}
        };

        // Mark all read
        function bindMarkRead() {
            var btn = document.getElementById('mgrNotifMarkRead');
            if (btn && !btn.dataset.mgrBound) {
                btn.dataset.mgrBound = '1';
                btn.addEventListener('click', async function () {
                    try {
                        await fetch(MARK_READ_URL, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        window.mgrFetchNotifs();
                    } catch (e) {}
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            window.mgrFetchNotifs();
            bindMarkRead();
        });
        document.addEventListener('turbo:load', function () {
            window.mgrFetchNotifs();
            bindMarkRead();
        });

        // Poll every 45 seconds
        setInterval(window.mgrFetchNotifs, 45000);
    })();
    </script>
    @endif
    @endauth

    {{-- Lucide init for admin (fires after DOM + deferred scripts are ready) --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
    <script>
    function initLucideIcons() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }
    document.addEventListener('DOMContentLoaded', initLucideIcons);
    document.addEventListener('turbo:load',        initLucideIcons);
    </script>
    @endif
</body>

</html>
