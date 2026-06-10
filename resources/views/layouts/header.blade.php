<header class="top-header">
    <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">

        <div class="d-flex align-items-center gap-3">

            @if(auth()->check() && auth()->user()->role === 'telecaller')
                {{-- ── Telecaller: logo in navbar, no hamburger (arrow is on sidebar) ── --}}
                @php
                    $tcNavLogo     = \App\Models\Setting::get('site_logo');
                    $tcNavSiteName = \App\Models\Setting::get('site_name', 'Admission CRM');
                @endphp
                <div class="tc-navbar-brand">
                    <div class="tc-navbar-logo">
                        @if($tcNavLogo)
                            <img src="{{ asset('storage/' . $tcNavLogo) }}" alt="Logo">
                        @else
                            <span class="material-icons" style="font-size:18px;color:#fff;">school</span>
                        @endif
                    </div>
                    <div>
                        <div class="tc-navbar-site-name">{{ $tcNavSiteName }}</div>
                        <div class="tc-navbar-site-role">Telecaller Panel</div>
                    </div>
                </div>
                <div class="tc-navbar-divider"></div>
            @else
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                @if(auth()->check() && auth()->user()->role === 'manager')
                    {{-- ── Manager: brand logo + "Manager Panel" ── --}}
                    @php
                        $mgrNavLogo     = \App\Models\Setting::get('site_logo');
                        $mgrNavSiteName = \App\Models\Setting::get('site_name', 'Admission CRM');
                    @endphp
                    <div class="tc-navbar-brand">
                        <div class="tc-navbar-logo">
                            @if($mgrNavLogo)
                                <img src="{{ asset('storage/' . $mgrNavLogo) }}" alt="Logo">
                            @else
                                <span class="material-icons" style="font-size:18px;color:#fff;">school</span>
                            @endif
                        </div>
                        <div>
                            <div class="tc-navbar-site-name">{{ $mgrNavSiteName }}</div>
                            <div class="tc-navbar-site-role">Manager Panel</div>
                        </div>
                    </div>
                    <div class="tc-navbar-divider"></div>
                @elseif(auth()->check() && auth()->user()->role === 'admin')
                    {{-- ── Admin: brand logo + "Admin Panel" ── --}}
                    @php
                        $admNavLogo     = \App\Models\Setting::get('site_logo');
                        $admNavSiteName = \App\Models\Setting::get('site_name', 'Admission CRM');
                    @endphp
                    <div class="tc-navbar-brand">
                        <div class="tc-navbar-logo">
                            @if($admNavLogo)
                                <img src="{{ asset('storage/' . $admNavLogo) }}" alt="Logo">
                            @else
                                <span class="material-icons" style="font-size:18px;color:#fff;">admin_panel_settings</span>
                            @endif
                        </div>
                        <div>
                            <div class="tc-navbar-site-name">{{ $admNavSiteName }}</div>
                            <div class="tc-navbar-site-role">Admin Panel</div>
                        </div>
                    </div>
                    <div class="tc-navbar-divider"></div>
                @endif
            @endif

            <div>
                <h2 class="page-header-title mb-0" id="pageHeaderTitle">
                    @yield('page_title', 'Dashboard')
                </h2>
                @if(auth()->check() && in_array(auth()->user()->role, ['telecaller', 'manager']))
                <p class="page-header-subtitle mb-0" style="margin-top:2px;">
                    Welcome back, <strong style="font-family:'Poppins',sans-serif;">{{ auth()->user()->name }}</strong>
                </p>
                @else
                <p class="page-header-subtitle mb-0" style="margin-top:2px;">
                    Welcome back, <strong style="color:var(--text-dark);">{{ auth()->user()->name }}</strong>
                </p>
                @endif
            </div>
        </div>

        {{-- Telecaller: quick-stat pills + live clock --}}
        @if(auth()->check() && auth()->user()->role === 'telecaller')
        @php
            [$tcHdrCalls, $tcHdrFollowups, $tcHdrOverdue] = \Illuminate\Support\Facades\Cache::remember(
                'tc_hdr_stats_' . auth()->id() . '_' . today()->format('Ymd'),
                60,
                function () {
                    $uid = auth()->id();
                    $hasCompletedAt = \Illuminate\Support\Facades\Cache::remember(
                        'schema_followups_completed_at', 3600,
                        fn() => \Illuminate\Support\Facades\Schema::hasColumn('followups', 'completed_at')
                    );
                    $calls = \App\Models\CallLog::where('user_id', $uid)->whereDate('created_at', today())->count();
                    $fu = \App\Models\Followup::whereHas('lead', fn($q) => $q->where('assigned_to', $uid))
                        ->whereDate('next_followup', today())
                        ->when($hasCompletedAt, fn($q) => $q->whereNull('completed_at'))
                        ->count();
                    $ov = \App\Models\Followup::whereHas('lead', fn($q) => $q->where('assigned_to', $uid))
                        ->whereDate('next_followup', '<', today())
                        ->when($hasCompletedAt, fn($q) => $q->whereNull('completed_at'))
                        ->count();
                    return [$calls, $fu, $ov];
                }
            );
        @endphp
        <div class="tc-header-stats" style="margin-left:auto;margin-right:10px;">
            <a href="{{ route('telecaller.calls.outbound') }}" onclick="inertiaVisit(event,this.href)" class="tc-hstat tc-hstat-orange">
                <span class="material-icons" style="font-size:13px;">call</span>
                <span id="tc-hdr-calls">{{ $tcHdrCalls }}</span>&nbsp;Calls Today
            </a>
            <a href="{{ route('telecaller.followups.today') }}" onclick="inertiaVisit(event,this.href)" class="tc-hstat tc-hstat-neutral">
                <span class="material-icons" style="font-size:13px;">event_note</span>
                <span id="tc-hdr-followups">{{ $tcHdrFollowups }}</span>&nbsp;Follow-ups
            </a>
            @if($tcHdrOverdue > 0)
            <a href="{{ route('telecaller.followups.overdue') }}" onclick="inertiaVisit(event,this.href)" class="tc-hstat tc-hstat-red">
                <span class="material-icons" style="font-size:13px;">warning_amber</span>
                <span id="tc-hdr-overdue">{{ $tcHdrOverdue }}</span>&nbsp;Overdue
            </a>
            @endif
        </div>
        <span id="tc-live-clock" style="margin-right:4px;font-family:'Poppins',sans-serif;font-size:13px;font-weight:600;color:#fff;white-space:nowrap;"></span>
        <script>
        (function(){
            function tickClock(){
                var el=document.getElementById('tc-live-clock');
                if(!el) return;
                var now=new Date();
                var h=now.getHours(), m=now.getMinutes(), s=now.getSeconds();
                var ampm=h>=12?'PM':'AM';
                h=h%12||12;
                el.textContent=(h<10?'0'+h:h)+':'+(m<10?'0'+m:m)+':'+(s<10?'0'+s:s)+' '+ampm;
            }
            tickClock();
            setInterval(tickClock,1000);
        })();
        </script>
        @endif

        <div class="d-flex align-items-center gap-2 flex-wrap">

            {{-- ── Academic Year Context Switcher ──────────────────────────── --}}
            @if(auth()->check() && isset($globalAcademicYears) && $globalAcademicYears->count() > 0)
            <div class="dropdown" id="ayDropdownWrap">
                {{-- Trigger button --}}
                <button id="ayDropdownBtn" type="button"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="true"
                    title="Switch Academic Year"
                    style="display:inline-flex;align-items:center;gap:6px;
                           background:{{ $globalSelectedAyId ? 'linear-gradient(135deg,#6366f1,#4f46e5)' : 'var(--background-light)' }};
                           color:{{ $globalSelectedAyId ? '#fff' : 'var(--text-dark)' }};
                           border:1px solid {{ $globalSelectedAyId ? 'transparent' : 'var(--border-color)' }};
                           border-radius:20px;padding:0 12px 0 8px;height:34px;
                           font-size:11.5px;font-weight:600;cursor:pointer;
                           box-shadow:{{ $globalSelectedAyId ? '0 2px 8px rgba(99,102,241,.3)' : 'none' }};
                           transition:all .2s;flex-shrink:0;">
                    <span class="material-icons" style="font-size:15px;opacity:.85;">school</span>
                    <span id="aySelectedLabel" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $globalSelectedAy?->name ?? 'All Years' }}
                    </span>
                    <span class="material-icons" style="font-size:14px;opacity:.7;margin-left:1px;">expand_more</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                    style="min-width:210px;border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
                    <div class="px-3 py-2"
                        style="background:var(--background-light);border-bottom:1px solid var(--border-color);">
                        <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                            style="font-size:9.5px;letter-spacing:1px;color:var(--text-muted);">
                            <span class="material-icons" style="font-size:12px;color:#6366f1;">calendar_today</span>
                            Academic Year
                        </small>
                    </div>
                    {{-- "All Years" option --}}
                    <a href="#"
                        onclick="aySwitch('all','All Years'); return false;"
                        class="ay-item dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                        data-ay-id="all"
                        style="font-size:13px;font-weight:{{ !$globalSelectedAyId ? '700' : '400' }};color:{{ !$globalSelectedAyId ? '#6366f1' : '#0f172a' }};">
                        <span class="ay-radio material-icons" style="font-size:15px;color:{{ !$globalSelectedAyId ? '#6366f1' : '#94a3b8' }};">
                            {{ !$globalSelectedAyId ? 'radio_button_checked' : 'radio_button_unchecked' }}
                        </span>
                        All Years
                    </a>
                    <div style="border-top:1px solid var(--border-color);"></div>
                    {{-- Year options --}}
                    @foreach($globalAcademicYears as $ay)
                    <a href="#"
                        onclick="aySwitch('{{ $ay->id }}','{{ addslashes($ay->name) }}'); return false;"
                        class="ay-item dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                        data-ay-id="{{ $ay->id }}"
                        style="font-size:13px;font-weight:{{ $globalSelectedAyId == $ay->id ? '700' : '400' }};color:{{ $globalSelectedAyId == $ay->id ? '#6366f1' : '#0f172a' }};">
                        <span class="ay-radio material-icons" style="font-size:15px;color:{{ $globalSelectedAyId == $ay->id ? '#6366f1' : '#94a3b8' }};">
                            {{ $globalSelectedAyId == $ay->id ? 'radio_button_checked' : 'radio_button_unchecked' }}
                        </span>
                        {{ $ay->name }}
                        @if($ay->is_active)
                        <span style="margin-left:auto;font-size:9px;font-weight:600;color:#10b981;background:#d1fae5;padding:1px 6px;border-radius:9px;">ACTIVE</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Fallback form for Blade pages (admin) that don't have Inertia router --}}
            <form id="ayForm" method="POST" action="{{ route('academic-year.select') }}" style="display:none;">
                @csrf
                <input type="hidden" name="academic_year_id" id="ayFormInput">
            </form>

            <script>
            function aySwitch(id, label) {
                if (window._inertiaRouter) {
                    // ── Inertia SPA path: no full page reload ──────────────────
                    window._inertiaRouter.post(
                        '{{ route('academic-year.select') }}',
                        { academic_year_id: id },
                        {
                            preserveScroll: false,
                            onSuccess: function () {
                                var isFiltered = (id !== 'all');

                                // Update button label
                                var lbl = document.getElementById('aySelectedLabel');
                                if (lbl) lbl.textContent = label;

                                // Update button appearance
                                var btn = document.getElementById('ayDropdownBtn');
                                if (btn) {
                                    btn.style.background    = isFiltered ? 'linear-gradient(135deg,#6366f1,#4f46e5)' : 'var(--background-light,#f1f5f9)';
                                    btn.style.color         = isFiltered ? '#fff' : 'var(--text-dark,#0f172a)';
                                    btn.style.border        = isFiltered ? '1px solid transparent' : '1px solid var(--border-color,#e2e8f0)';
                                    btn.style.boxShadow     = isFiltered ? '0 2px 8px rgba(99,102,241,.3)' : 'none';
                                }

                                // Update radio icons on every item
                                document.querySelectorAll('#ayDropdownWrap .ay-item').forEach(function (el) {
                                    var active = el.getAttribute('data-ay-id') === String(id);
                                    var radio  = el.querySelector('.ay-radio');
                                    if (radio) {
                                        radio.textContent  = active ? 'radio_button_checked' : 'radio_button_unchecked';
                                        radio.style.color  = active ? '#6366f1' : '#94a3b8';
                                    }
                                    el.style.fontWeight = active ? '700' : '400';
                                    el.style.color      = active ? '#6366f1' : '#0f172a';
                                });

                                // Signal AY-aware components (e.g. Dashboard) to
                                // refresh their data immediately.
                                window.dispatchEvent(new CustomEvent('ay-changed'));

                                // Reload the current Inertia page so all data
                                // re-fetches scoped to the newly selected AY.
                                window._inertiaRouter.reload({ preserveScroll: false });
                            }
                        }
                    );
                } else {
                    // ── Blade fallback: standard form POST ─────────────────────
                    document.getElementById('ayFormInput').value = id;
                    document.getElementById('ayForm').submit();
                }
            }
            </script>
            @endif

            @if (auth()->check() && in_array(auth()->user()->role, ['telecaller', 'manager']) && \App\Models\Setting::get('primary_call_provider') === 'tcn')
                {{-- TCN Ready / Not Ready toggle --}}
                <button id="tcnReadyBtn" type="button"
                    title="Start / Stop calling session"
                    style="display:inline-flex;align-items:center;gap:6px;background:#475569;color:#fff;
                           border:none;border-radius:22px;padding:0 16px 0 10px;height:36px;
                           font-size:11.5px;font-weight:600;cursor:pointer;
                           transition:background .25s,box-shadow .25s;
                           box-shadow:0 1px 6px rgba(0,0,0,.18);letter-spacing:0.3px;flex-shrink:0;">
                    <span id="tcnStatusDot" style="width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.35);flex-shrink:0;transition:background .25s;"></span>
                    <span class="material-icons" style="font-size:14px;line-height:1;" id="tcnReadyIco">phone_disabled</span>
                    <span id="tcnReadyLabel">Not Ready</span>
                </button>
            @endif

            @if (auth()->check() && auth()->user()->role === 'manager')
                {{-- Manager Notification Bell --}}
                <div class="dropdown">
                    <button class="btn btn-sm position-relative"
                        style="width:36px;height:36px;padding:0;border-radius:9px;background:var(--background-light);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;transition:all .15s;"
                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        onclick="if(window.mgrFetchNotifs) window.mgrFetchNotifs();"
                        onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--background-light)'"
                        title="Notifications">
                        <span class="material-icons" style="font-size:18px;color:var(--text-muted);">notifications</span>
                        <span id="mgrNotifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="display:none;font-size:9px;min-width:16px;height:16px;padding:0 4px;line-height:16px;">0</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg" style="width:370px;max-width:95vw;border-radius:14px;border:1px solid var(--border-color);overflow:hidden;">
                        {{-- Header --}}
                        <div class="p-3 d-flex justify-content-between align-items-center"
                            style="background:var(--background-light);border-bottom:1px solid var(--border-color);">
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size:14px;">Notifications</h6>
                                <small class="text-muted" style="font-size:11px;">Leads, follow-ups & escalations</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold"
                                style="font-size:12px;color:var(--primary-color);"
                                id="mgrNotifMarkRead">Mark all read</button>
                        </div>

                        <div style="max-height:400px;overflow-y:auto;">
                            <div class="px-3 pt-2 pb-2">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                                    style="font-size:9.5px;letter-spacing:1px;color:var(--text-muted);">
                                    <span class="material-icons" style="font-size:12px;color:#6366f1;">person_add</span>Lead Assignments
                                </small>
                                <div id="mgrNotifLeads" class="mt-1 small text-muted">No lead assignments.</div>
                            </div>
                            <div class="px-3 py-2" style="border-top:1px solid var(--border-color);">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                                    style="font-size:9.5px;letter-spacing:1px;color:var(--text-muted);">
                                    <span class="material-icons" style="font-size:12px;color:#f59e0b;">event</span>Follow-ups
                                </small>
                                <div id="mgrNotifFollowups" class="mt-1 small text-muted">No follow-up alerts.</div>
                            </div>
                            <div class="px-3 py-2" style="border-top:1px solid var(--border-color);">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                                    style="font-size:9.5px;letter-spacing:1px;color:var(--text-muted);">
                                    <span class="material-icons" style="font-size:12px;color:#ef4444;">warning</span>SLA Escalations
                                </small>
                                <div id="mgrNotifSla" class="mt-1 small text-muted">No SLA alerts.</div>
                            </div>
                            <div class="px-3 py-2" style="border-top:1px solid var(--border-color);">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                                    style="font-size:9.5px;letter-spacing:1px;color:var(--text-muted);">
                                    <span class="material-icons" style="font-size:12px;color:#25D366;">chat</span>WhatsApp
                                </small>
                                <div id="mgrNotifWhatsapp" class="mt-1 small text-muted">No WhatsApp messages.</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (auth()->check() && auth()->user()->role === 'telecaller')
                {{-- Override Bootstrap blue switch → orange --}}
                <style>
                    #teleNotifSoundToggle:checked { background-color: #FF5C00 !important; border-color: #FF5C00 !important; }
                    #teleNotifSoundToggle:focus   { box-shadow: 0 0 0 .2rem rgba(255,92,0,.2) !important; }
                </style>

                {{-- Notification Bell --}}
                <div class="dropdown">
                    <button class="btn btn-sm position-relative"
                        style="width:36px;height:36px;padding:0;border-radius:9px;background:var(--background-light);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;transition:all .15s;"
                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--background-light)'">
                        <span class="material-icons" style="font-size:18px;color:var(--text-muted);">notifications</span>
                        <span id="teleNotifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="display:none;font-size:9px;min-width:16px;height:16px;padding:0 4px;line-height:16px;">0</span>
                    </button>

                    {{-- No flex wrapper — scroll area has fixed max-height so footer is always visible --}}
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                        style="width:400px;max-width:95vw;border-radius:16px;border:1px solid #ffd5c0;overflow:hidden;">

                        {{-- Orange Gradient Header --}}
                        <div class="px-3 py-3 d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#FF7A30 0%,#FF5C00 100%);">
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size:14px;color:#fff;letter-spacing:.2px;">Notifications</h6>
                                <small style="font-size:11px;color:rgba(255,255,255,.8);">Missed calls, follow-ups &amp; alerts</small>
                            </div>
                            <span class="material-icons" style="font-size:22px;color:rgba(255,255,255,.85);">notifications_active</span>
                        </div>

                        {{-- Sound toggle --}}
                        <div class="px-3 py-2 d-flex align-items-center justify-content-between"
                            style="background:#fff;border-bottom:1px solid #f0e6e0;">
                            <small class="fw-semibold d-flex align-items-center gap-1" style="font-size:12px;color:#0f172a;">
                                <span class="material-icons" style="font-size:15px;color:#FF5C00;">volume_up</span>
                                Sound alerts
                            </small>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="teleNotifSoundToggle" style="cursor:pointer;">
                            </div>
                        </div>

                        {{-- Scrollable notification list — fixed max-height ensures footer is always below --}}
                        <div style="max-height:320px;overflow-y:auto;background:#fafafa;">

                            {{-- Missed Calls --}}
                            <div class="p-3 pb-2">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="material-icons" style="font-size:14px;color:#ef4444;">phone_missed</span>
                                    <small class="text-uppercase fw-bold" style="font-size:9.5px;letter-spacing:1px;color:#ef4444;">Missed Calls</small>
                                    <span id="teleNotifMissedCount" class="badge rounded-pill ms-auto" style="display:none;background:#fee2e2;color:#ef4444;font-size:10px;font-weight:700;"></span>
                                </div>
                                <div id="teleNotifMissedCalls">
                                    <div class="text-center py-2" style="color:#94a3b8;font-size:12px;">No missed calls.</div>
                                </div>
                            </div>

                            {{-- Follow-up Reminders --}}
                            <div class="px-3 pb-2" style="border-top:1px solid #f0e6e0;">
                                <div class="d-flex align-items-center gap-2 mb-2 pt-2">
                                    <span class="material-icons" style="font-size:14px;color:#f59e0b;">calendar_today</span>
                                    <small class="text-uppercase fw-bold" style="font-size:9.5px;letter-spacing:1px;color:#f59e0b;">Follow-up Reminders</small>
                                    <span id="teleNotifFollowupCount" class="badge rounded-pill ms-auto" style="display:none;background:#fef3c7;color:#d97706;font-size:10px;font-weight:700;"></span>
                                </div>
                                <div id="teleNotifFollowups">
                                    <div class="text-center py-2" style="color:#94a3b8;font-size:12px;">No reminders.</div>
                                </div>
                            </div>

                            {{-- WhatsApp Messages --}}
                            <div class="px-3 pb-2" style="border-top:1px solid #f0e6e0;">
                                <div class="d-flex align-items-center gap-2 mb-2 pt-2">
                                    <span class="material-icons" style="font-size:14px;color:#10b981;">chat</span>
                                    <small class="text-uppercase fw-bold" style="font-size:9.5px;letter-spacing:1px;color:#10b981;">WhatsApp Messages</small>
                                    <span id="teleNotifWhatsappCount" class="badge rounded-pill ms-auto" style="display:none;background:#d1fae5;color:#059669;font-size:10px;font-weight:700;"></span>
                                </div>
                                <div id="teleNotifWhatsapp">
                                    <div class="text-center py-2" style="color:#94a3b8;font-size:12px;">No WhatsApp messages.</div>
                                </div>
                            </div>

                            {{-- System --}}
                            <div class="px-3 pb-3" style="border-top:1px solid #f0e6e0;">
                                <div class="d-flex align-items-center gap-2 mb-2 pt-2">
                                    <span class="material-icons" style="font-size:14px;color:#FF5C00;">info</span>
                                    <small class="text-uppercase fw-bold" style="font-size:9.5px;letter-spacing:1px;color:#FF5C00;">System</small>
                                    <span id="teleNotifSystemCount" class="badge rounded-pill ms-auto" style="display:none;background:#ffe8d6;color:#FF5C00;font-size:10px;font-weight:700;"></span>
                                </div>
                                <div id="teleNotifSystem">
                                    <div class="text-center py-2" style="color:#94a3b8;font-size:12px;">No system notifications.</div>
                                </div>
                            </div>

                        </div>{{-- end scrollable --}}

                        {{-- Footer — outside scroll area, always visible --}}
                        <div style="padding:8px;border-top:1px solid #f0e6e0;background:#fff;">
                            <button type="button" id="teleNotifMarkRead"
                                class="btn btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-1"
                                style="font-size:12px;background:linear-gradient(135deg,#FF7A30,#FF5C00);color:#fff;border:none;border-radius:10px;padding:9px 0;letter-spacing:.3px;transition:opacity .15s;"
                                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                                <span class="material-icons" style="font-size:16px;">done_all</span>
                                Mark all as read
                            </button>
                        </div>

                    </div>
                </div>
            @endif

            @if(auth()->check() && !in_array(auth()->user()->role, ['admin','telecaller']))
                {{-- Documents Quick Access --}}
                <button class="btn btn-sm position-relative"
                    style="width:36px;height:36px;padding:0;border-radius:9px;background:var(--background-light);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;transition:all .15s;"
                    type="button" data-bs-toggle="modal" data-bs-target="#docsModal" title="Documents"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--background-light)'">
                    <span class="material-icons" style="font-size:18px;color:var(--text-muted);">folder_open</span>
                </button>
            @endif

            @yield('header_actions')

            @if(auth()->check() && auth()->user()->role === 'manager')
                <div style="width:1px;height:22px;background:rgba(255,255,255,.18);flex-shrink:0;"></div>
                @php $hdrInitials = strtoupper(substr(auth()->user()->name, 0, 1)); @endphp
                <div class="dropdown">
                    <button type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        title="{{ auth()->user()->name }}"
                        style="width:36px;height:36px;padding:0;border:none;border-radius:10px;background:linear-gradient(135deg,#FF5C00,#FF8C4A);color:#fff;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(255,92,0,.40);flex-shrink:0;letter-spacing:0;font-family:'Poppins',sans-serif;">
                        {{ $hdrInitials }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                        style="width:230px;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">
                        <div class="p-3 d-flex align-items-center gap-3"
                            style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);">
                            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.20);color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Poppins',sans-serif;">
                                {{ $hdrInitials }}
                            </div>
                            <div style="overflow:hidden;min-width:0;">
                                <div style="color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:'Poppins',sans-serif;">
                                    {{ auth()->user()->name }}
                                </div>
                                <div style="color:rgba(255,255,255,.80);font-size:11px;display:flex;align-items:center;gap:3px;margin-top:2px;font-family:'Poppins',sans-serif;">
                                    <span class="material-icons" style="font-size:11px;">manage_accounts</span>
                                    Manager
                                </div>
                            </div>
                        </div>
                        <div class="py-1" style="background:#fff;">
                            <a href="{{ route('password.change') }}"
                                class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                style="font-size:13px;color:#0f172a;font-weight:500;">
                                <span class="material-icons" style="font-size:17px;color:#FF5C00;">lock_reset</span>
                                Change Password
                            </a>
                        </div>
                        <div style="border-top:1px solid var(--border-color);" class="py-1 bg-white">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                    style="font-size:13px;color:#ef4444;font-weight:500;background:transparent;border:none;width:100%;text-align:left;">
                                    <span class="material-icons" style="font-size:17px;color:#ef4444;">logout</span>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if(auth()->check() && auth()->user()->role === 'admin')
                <div style="width:1px;height:22px;background:rgba(255,255,255,.18);flex-shrink:0;"></div>
                @php $hdrInitials = strtoupper(substr(auth()->user()->name, 0, 1)); @endphp
                <div class="dropdown">
                    <button type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        title="{{ auth()->user()->name }}"
                        style="width:36px;height:36px;padding:0;border:none;border-radius:10px;background:linear-gradient(135deg,#FF5C00,#FF8C4A);color:#fff;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(255,92,0,.40);flex-shrink:0;letter-spacing:0;font-family:'Poppins',sans-serif;">
                        {{ $hdrInitials }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                        style="width:230px;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">
                        <div class="p-3 d-flex align-items-center gap-3"
                            style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);">
                            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.20);color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Poppins',sans-serif;">
                                {{ $hdrInitials }}
                            </div>
                            <div style="overflow:hidden;min-width:0;">
                                <div style="color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:'Poppins',sans-serif;">
                                    {{ auth()->user()->name }}
                                </div>
                                <div style="color:rgba(255,255,255,.80);font-size:11px;display:flex;align-items:center;gap:3px;margin-top:2px;font-family:'Poppins',sans-serif;">
                                    <span class="material-icons" style="font-size:11px;">admin_panel_settings</span>
                                    Administrator
                                </div>
                            </div>
                        </div>
                        <div class="py-1" style="background:#fff;">
                            <a href="{{ route('password.change') }}"
                                class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                style="font-size:13px;color:#0f172a;font-weight:500;">
                                <span class="material-icons" style="font-size:17px;color:#FF5C00;">lock_reset</span>
                                Change Password
                            </a>
                        </div>
                        <div style="border-top:1px solid #e2e8f0;" class="py-1 bg-white">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                    style="font-size:13px;color:#ef4444;font-weight:500;background:transparent;border:none;width:100%;text-align:left;">
                                    <span class="material-icons" style="font-size:17px;color:#ef4444;">logout</span>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if(auth()->check() && auth()->user()->role === 'telecaller')
                <div style="width:1px;height:22px;background:var(--border-color);flex-shrink:0;"></div>
                @php $hdrInitials = strtoupper(substr(auth()->user()->name, 0, 1)); @endphp
                <div class="dropdown">
                    <button type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        title="{{ auth()->user()->name }}"
                        style="width:36px;height:36px;padding:0;border:none;border-radius:10px;background:linear-gradient(135deg,#FF5C00,#FF8C4A);color:#fff;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(255,92,0,.40);flex-shrink:0;letter-spacing:0;font-family:'Poppins',sans-serif;">
                        {{ $hdrInitials }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                        style="width:230px;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">
                        {{-- User info --}}
                        <div class="p-3 d-flex align-items-center gap-3"
                            style="background:linear-gradient(135deg,#FF5C00,#FF8C4A);">
                            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.20);color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Poppins',sans-serif;">
                                {{ $hdrInitials }}
                            </div>
                            <div style="overflow:hidden;min-width:0;">
                                <div style="color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:'Poppins',sans-serif;">
                                    {{ auth()->user()->name }}
                                </div>
                                <div style="color:rgba(255,255,255,.80);font-size:11px;display:flex;align-items:center;gap:3px;margin-top:2px;font-family:'Poppins',sans-serif;">
                                    <span class="material-icons" style="font-size:11px;">headset_mic</span>
                                    Telecaller
                                </div>
                            </div>
                        </div>
                        {{-- Actions --}}
                        <div class="py-1" style="background:#fff;">
                            <a href="{{ route('password.change') }}"
                                class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                style="font-size:13px;color:#0f172a;font-weight:500;">
                                <span class="material-icons" style="font-size:17px;color:#FF5C00;">lock_reset</span>
                                Change Password
                            </a>
                        </div>
                        <div style="border-top:1px solid #e2e8f0;" class="py-1 bg-white">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                    style="font-size:13px;color:#ef4444;font-weight:500;background:transparent;border:none;width:100%;text-align:left;">
                                    <span class="material-icons" style="font-size:17px;color:#ef4444;">logout</span>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if(auth()->check() && auth()->user()->role === 'report_viewer')
                {{-- Notifications Bell --}}
                <div class="dropdown">
                    <button class="btn btn-sm position-relative"
                        style="width:36px;height:36px;padding:0;border-radius:9px;background:var(--background-light);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;transition:all .15s;"
                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--background-light)'"
                        title="Notifications">
                        <span class="material-icons" style="font-size:18px;color:var(--text-muted);">notifications</span>
                        <span id="rvNotifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="display:none;font-size:9px;min-width:16px;height:16px;padding:0 4px;line-height:16px;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg" style="width:320px;max-width:95vw;border-radius:14px;border:1px solid var(--border-color);overflow:hidden;">
                        <div class="p-3 d-flex justify-content-between align-items-center"
                            style="background:var(--background-light);border-bottom:1px solid var(--border-color);">
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size:14px;">Notifications</h6>
                                <small class="text-muted" style="font-size:11px;">System alerts</small>
                            </div>
                        </div>
                        <div style="max-height:320px;overflow-y:auto;">
                            <div class="px-3 py-2">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                                    style="font-size:9.5px;letter-spacing:1px;color:var(--text-muted);">
                                    <span class="material-icons" style="font-size:12px;color:#6366f1;">campaign</span>System
                                </small>
                                <div id="rvNotifSystem" class="mt-1 small text-muted">No notifications.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div style="width:1px;height:22px;background:var(--border-color);flex-shrink:0;"></div>

                {{-- Profile Avatar Dropdown --}}
                @php $hdrInitials = strtoupper(substr(auth()->user()->name, 0, 1)); @endphp
                <div class="dropdown">
                    <button type="button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        title="{{ auth()->user()->name }}"
                        style="width:36px;height:36px;padding:0;border:none;border-radius:10px;background:linear-gradient(135deg,#06b6d4,#0891b2);color:#fff;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(6,182,212,.35);flex-shrink:0;letter-spacing:0;">
                        {{ $hdrInitials }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                        style="width:230px;border-radius:14px;border:1px solid var(--border-color);overflow:hidden;">
                        <div class="p-3 d-flex align-items-center gap-3"
                            style="background:linear-gradient(135deg,#06b6d4,#0891b2);">
                            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.2);color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                {{ $hdrInitials }}
                            </div>
                            <div style="overflow:hidden;min-width:0;">
                                <div style="color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ auth()->user()->name }}
                                </div>
                                <div style="color:rgba(255,255,255,.75);font-size:11px;display:flex;align-items:center;gap:3px;margin-top:2px;">
                                    <span class="material-icons" style="font-size:11px;">bar_chart</span>
                                    Report Viewer
                                </div>
                            </div>
                        </div>
                        <div class="py-1" style="background:#fff;">
                            <a href="{{ route('password.change') }}"
                                class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                style="font-size:13px;color:#0f172a;font-weight:500;">
                                <span class="material-icons" style="font-size:17px;color:#6366f1;">lock_reset</span>
                                Change Password
                            </a>
                        </div>
                        <div style="border-top:1px solid var(--border-color);" class="py-1 bg-white">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                    style="font-size:13px;color:#ef4444;font-weight:500;background:transparent;border:none;width:100%;text-align:left;">
                                    <span class="material-icons" style="font-size:17px;color:#ef4444;">logout</span>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
    @yield('header_actions1')
</header>
