<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Force Turbo Drive to do a full page reload when navigating to this page.
         Without this, Turbo body-swaps the login HTML into the active Inertia app
         shell, showing the login form as a floating overlay over the dashboard. --}}
    <meta name="turbo-visit-control" content="reload">

    @php
        $siteName = \App\Models\Setting::get('site_name', 'Admission CRM');
        $favicon  = \App\Models\Setting::get('site_favicon');
        $logo     = \App\Models\Setting::get('site_logo');
    @endphp

    <title>{{ $siteName }}</title>

    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('images/default-favicon.png') }}">
    @endif

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary:      #FF5C00;
            --primary-dk:   #e05200;
            --primary-900:  #7c2d00;
            --primary-800:  #9a3800;
            --primary-700:  #c44400;
            --bg:           #f1f5f9;
            --border:       #e2e8f0;
            --text:         #0f172a;
            --muted:        #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ── Split layout ──────────────────────────────────────── */
        .auth-wrapper { display: flex; width: 100%; min-height: 100vh; }

        /* ── Left Brand Panel — dark with orange accent ─────────── */
        .auth-left {
            flex: 0 0 42%;
            background: linear-gradient(160deg, #1a1a1a 0%, #0f172a 40%, #0a0f1e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 56px;
            position: relative;
            overflow: hidden;
        }

        /* Grid overlay texture */
        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Orange glow blob bottom-right */
        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -120px;
            right: -80px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,92,0,.22) 0%, transparent 65%);
            pointer-events: none;
        }

        .blob-tr {
            position: absolute;
            top: -60px;
            right: -60px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,140,74,.12) 0%, transparent 65%);
            pointer-events: none;
        }

        .blob-bl {
            position: absolute;
            bottom: 20%;
            left: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,92,0,.10) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Brand logo box */
        .brand-logo-wrap {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #FF5C00, #e05200);
            border: none;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(255,92,0,.40);
            position: relative;
            z-index: 1;
        }
        .brand-logo-wrap img   { height: 32px; object-fit: contain; }
        .brand-logo-wrap .material-icons { font-size: 28px; color: #fff; }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: -0.4px;
            position: relative;
            z-index: 1;
        }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255,255,255,.70);
            margin-bottom: 44px;
            line-height: 1.7;
            max-width: 300px;
            position: relative;
            z-index: 1;
        }

        /* Feature list */
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255,255,255,.88);
            font-size: 13.5px;
            font-weight: 500;
        }

        .feature-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: rgba(255,92,0,.18);
            border: 1px solid rgba(255,92,0,.30);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon .material-icons { font-size: 18px; color: #FF5C00; }

        .auth-left-footer {
            margin-top: auto;
            padding-top: 48px;
            font-size: 12px;
            color: rgba(255,255,255,.30);
            position: relative;
            z-index: 1;
        }

        /* ── Right Form Panel ──────────────────────────────────── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            background: var(--bg);
        }

        .auth-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 48px 44px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 32px rgba(255,92,0,.09);
            overflow: hidden;
            position: relative;
        }

        /* Orange top accent bar */
        .auth-card::before {
            content: '';
            display: block;
            height: 4px;
            background: linear-gradient(90deg, #FF5C00 0%, #FF8C4A 50%, #ffb347 100%);
            margin: -48px -44px 36px;
            border-radius: 0;
        }

        .auth-card-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 4px;
        }

        .auth-card-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 32px;
        }

        /* ── Form elements ─────────────────────────────────────── */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .form-control {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255,92,0,.12);
            outline: none;
        }

        .form-control.is-invalid { border-color: #ef4444; }

        .input-group .form-control {
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group-text {
            background: #fff;
            border: 1.5px solid var(--border);
            border-left: none;
            border-radius: 0 10px 10px 0;
            cursor: pointer;
            color: var(--muted);
            transition: color .2s;
        }

        .input-group-text:hover { color: var(--primary); }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text { border-color: var(--primary); }

        .input-group:focus-within .input-group-text {
            box-shadow: 3px 0 0 3px rgba(255,92,0,.12) inset;
        }

        .invalid-feedback { font-size: 12px; color: #ef4444; }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(255,92,0,.15); }

        .form-check-label { font-size: 13px; color: var(--muted); }

        /* ── Primary Button ────────────────────────────────────── */
        .btn-primary-crm {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dk) 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            padding: 11px 24px;
            width: 100%;
            cursor: pointer;
            transition: box-shadow .2s, transform .1s;
            letter-spacing: .2px;
            box-shadow: 0 3px 12px rgba(255,92,0,.30);
        }

        .btn-primary-crm:hover {
            box-shadow: 0 5px 20px rgba(255,92,0,.45);
            transform: translateY(-1px);
        }

        .btn-primary-crm:active { transform: scale(.98); box-shadow: none; }

        .forgot-link {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover { text-decoration: underline; color: var(--primary-dk); }

        /* ── Alert ─────────────────────────────────────────────── */
        .auth-alert {
            border-radius: 10px;
            font-size: 13px;
            padding: 12px 16px;
            margin-bottom: 20px;
            border: none;
        }

        .auth-alert-success { background: #d1fae5; color: #065f46; }

        /* ── Responsive ────────────────────────────────────────── */
        @media (max-width: 768px) {
            .auth-left { display: none; }
            .auth-right { padding: 32px 16px; }
            .auth-card { padding: 36px 24px; border-radius: 16px; }
            .auth-card::before { margin: -36px -24px 28px; }
        }
    </style>

    @vite(['resources/js/app.js'])
</head>

<body>
    <div class="auth-wrapper">

        {{-- Left: Brand Panel --}}
        <div class="auth-left">
            <div class="blob-tr"></div>
            <div class="blob-bl"></div>
            <div style="position: relative; z-index: 1; width: 100%;">
                <div class="brand-logo-wrap">
                    @if ($logo)
                        <img src="{{ asset('storage/' . $logo) }}" alt="{{ $siteName }}">
                    @else
                        <span class="material-icons">school</span>
                    @endif
                </div>

                <div class="brand-name">{{ $siteName }}</div>
                <p class="brand-tagline">Streamline your admissions process with powerful lead management and smart analytics.</p>

                <ul class="feature-list">
                    <li class="feature-item">
                        <div class="feature-icon"><span class="material-icons">person_add</span></div>
                        <span>Smart Lead Management</span>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><span class="material-icons">support_agent</span></div>
                        <span>Telecaller Performance Tracking</span>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><span class="material-icons">bar_chart</span></div>
                        <span>Reports & Analytics</span>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><span class="material-icons">event_note</span></div>
                        <span>Automated Follow-up Reminders</span>
                    </li>
                </ul>
            </div>

            <div class="auth-left-footer">
                &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
            </div>
        </div>

        {{-- Right: Form Panel --}}
        <div class="auth-right">
            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
