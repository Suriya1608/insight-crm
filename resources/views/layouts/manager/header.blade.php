<header class="top-header">
    @php
        $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
        $isManager = auth()->check() && auth()->user()->role === 'manager';
        $mgrNavLogo     = \App\Models\Setting::get('site_logo');
        $mgrNavSiteName = \App\Models\Setting::get('site_name', 'Admission CRM');
    @endphp

    <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">

        {{-- ── Left: hamburger · brand · divider · page title ── --}}
        <div class="d-flex align-items-center gap-3">

            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <span class="material-icons">menu</span>
            </button>

            {{-- Brand logo + site name --}}
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

            {{-- Page title --}}
            <div>
                <h2 class="page-header-title mb-0" id="pageHeaderTitle">
                    @yield('page_title', 'Manager Dashboard')
                </h2>
                <p class="page-header-subtitle mb-0" style="margin-top:2px;">
                    Welcome back, <strong style="font-family:'Poppins',sans-serif;">{{ auth()->user()->name }}</strong>
                </p>
            </div>
        </div>

        {{-- ── Right: AY switcher · TCN · notifications · docs · avatar ── --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">

            {{-- ── Academic Year Context Switcher ──────────────────────────── --}}
            @if(isset($globalAcademicYears) && $globalAcademicYears->count() > 0)
            <div class="dropdown" id="ayDropdownWrap">
                <button id="ayDropdownBtn" type="button"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="true"
                    title="Switch Academic Year"
                    style="display:inline-flex;align-items:center;gap:6px;
                           background:{{ $globalSelectedAyId ? 'linear-gradient(135deg,#FF5C00,#FF8C4A)' : 'rgba(255,255,255,0.10)' }};
                           color:#fff;
                           border:1px solid {{ $globalSelectedAyId ? 'transparent' : 'rgba(255,255,255,0.18)' }};
                           border-radius:20px;padding:0 12px 0 8px;height:34px;
                           font-size:11.5px;font-weight:600;cursor:pointer;
                           box-shadow:{{ $globalSelectedAyId ? '0 2px 8px rgba(255,92,0,.35)' : 'none' }};
                           transition:all .2s;flex-shrink:0;font-family:Poppins,sans-serif;">
                    <span class="material-icons" style="font-size:15px;opacity:.85;">school</span>
                    <span id="aySelectedLabel" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $globalSelectedAy?->name ?? 'All Years' }}
                    </span>
                    <span class="material-icons" style="font-size:14px;opacity:.7;margin-left:1px;">expand_more</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
                    style="min-width:210px;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
                    <div class="px-3 py-2" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <small class="text-uppercase fw-bold d-flex align-items-center gap-1"
                            style="font-size:9.5px;letter-spacing:1px;color:#64748b;">
                            <span class="material-icons" style="font-size:12px;color:#FF5C00;">calendar_today</span>
                            Academic Year
                        </small>
                    </div>
                    <a href="#" onclick="aySwitch('all','All Years'); return false;"
                        class="ay-item dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                        data-ay-id="all"
                        style="font-size:13px;font-weight:{{ !$globalSelectedAyId ? '700' : '400' }};color:{{ !$globalSelectedAyId ? '#FF5C00' : '#0f172a' }};">
                        <span class="ay-radio material-icons" style="font-size:15px;color:{{ !$globalSelectedAyId ? '#FF5C00' : '#94a3b8' }};">
                            {{ !$globalSelectedAyId ? 'radio_button_checked' : 'radio_button_unchecked' }}
                        </span>
                        All Years
                    </a>
                    <div style="border-top:1px solid #e2e8f0;"></div>
                    @foreach($globalAcademicYears as $ay)
                    <a href="#" onclick="aySwitch('{{ $ay->id }}','{{ addslashes($ay->name) }}'); return false;"
                        class="ay-item dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                        data-ay-id="{{ $ay->id }}"
                        style="font-size:13px;font-weight:{{ $globalSelectedAyId == $ay->id ? '700' : '400' }};color:{{ $globalSelectedAyId == $ay->id ? '#FF5C00' : '#0f172a' }};">
                        <span class="ay-radio material-icons" style="font-size:15px;color:{{ $globalSelectedAyId == $ay->id ? '#FF5C00' : '#94a3b8' }};">
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

            <form id="ayForm" method="POST" action="{{ route('academic-year.select') }}" style="display:none;">
                @csrf
                <input type="hidden" name="academic_year_id" id="ayFormInput">
            </form>

            <script>
            function aySwitch(id, label) {
                if (window._inertiaRouter) {
                    window._inertiaRouter.post(
                        '{{ route('academic-year.select') }}',
                        { academic_year_id: id },
                        {
                            preserveScroll: false,
                            onSuccess: function () {
                                var isFiltered = (id !== 'all');
                                var lbl = document.getElementById('aySelectedLabel');
                                if (lbl) lbl.textContent = label;
                                var btn = document.getElementById('ayDropdownBtn');
                                if (btn) {
                                    btn.style.background   = isFiltered ? 'linear-gradient(135deg,#FF5C00,#FF8C4A)' : 'rgba(255,255,255,0.10)';
                                    btn.style.color        = '#fff';
                                    btn.style.border       = isFiltered ? '1px solid transparent' : '1px solid rgba(255,255,255,0.18)';
                                    btn.style.boxShadow    = isFiltered ? '0 2px 8px rgba(255,92,0,.35)' : 'none';
                                }
                                document.querySelectorAll('#ayDropdownWrap .ay-item').forEach(function (el) {
                                    var active = el.getAttribute('data-ay-id') === String(id);
                                    var radio  = el.querySelector('.ay-radio');
                                    if (radio) {
                                        radio.textContent = active ? 'radio_button_checked' : 'radio_button_unchecked';
                                        radio.style.color = active ? '#FF5C00' : '#94a3b8';
                                    }
                                    el.style.fontWeight = active ? '700' : '400';
                                    el.style.color      = active ? '#FF5C00' : '#0f172a';
                                });
                                window.dispatchEvent(new CustomEvent('ay-changed'));
                                window._inertiaRouter.reload({ preserveScroll: false });
                            }
                        }
                    );
                } else {
                    document.getElementById('ayFormInput').value = id;
                    document.getElementById('ayForm').submit();
                }
            }
            </script>
            @endif

            {{-- ── Manager Notification Bell ── --}}
            @if ($isManager)
                <div class="dropdown">
                    <button class="btn btn-sm position-relative"
                        style="width:36px;height:36px;padding:0;border-radius:9px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;transition:all .15s;"
                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        onclick="if(window.mgrFetchNotifs) window.mgrFetchNotifs();"
                        onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background='rgba(255,255,255,.08)'"
                        title="Notifications">
                        <span class="material-icons" style="font-size:18px;color:rgba(255,255,255,.80);">notifications</span>
                        <span id="managerNotifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="display:none;font-size:9px;min-width:16px;height:16px;padding:0 4px;line-height:16px;">0</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg" style="width:370px;max-width:95vw;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">
                        <div class="p-3 d-flex justify-content-between align-items-center" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size:14px;">Notifications</h6>
                                <small class="text-muted" style="font-size:11px;">Leads, follow-ups &amp; escalations</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold"
                                style="font-size:12px;color:#FF5C00;" id="managerNotifMarkRead">Mark all read</button>
                        </div>
                        <div style="max-height:400px;overflow-y:auto;">
                            <div class="px-3 pt-2 pb-2">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1" style="font-size:9.5px;letter-spacing:1px;color:#64748b;">
                                    <span class="material-icons" style="font-size:12px;color:#FF5C00;">person_add</span>Lead Assignments
                                </small>
                                <div id="mgrNotifLeads" class="mt-1 small text-muted">No lead assignments.</div>
                            </div>
                            <div class="px-3 py-2" style="border-top:1px solid #e2e8f0;">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1" style="font-size:9.5px;letter-spacing:1px;color:#64748b;">
                                    <span class="material-icons" style="font-size:12px;color:#f59e0b;">event</span>Follow-ups
                                </small>
                                <div id="mgrNotifFollowups" class="mt-1 small text-muted">No follow-up alerts.</div>
                            </div>
                            <div class="px-3 py-2" style="border-top:1px solid #e2e8f0;">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1" style="font-size:9.5px;letter-spacing:1px;color:#64748b;">
                                    <span class="material-icons" style="font-size:12px;color:#ef4444;">warning</span>SLA / System
                                </small>
                                <div id="managerNotifSystem" class="mt-1 small text-muted">No system alerts.</div>
                            </div>
                            <div class="px-3 py-2" style="border-top:1px solid #e2e8f0;">
                                <small class="text-uppercase fw-bold d-flex align-items-center gap-1" style="font-size:9.5px;letter-spacing:1px;color:#64748b;">
                                    <span class="material-icons" style="font-size:12px;color:#25D366;">chat</span>WhatsApp
                                </small>
                                <div id="managerNotifWhatsapp" class="mt-1 small text-muted">No WhatsApp messages.</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Documents Quick Access ── --}}
            <button class="btn btn-sm position-relative"
                style="width:36px;height:36px;padding:0;border-radius:9px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;transition:all .15s;"
                type="button" data-bs-toggle="modal" data-bs-target="#docsModal" title="Documents"
                onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
                <span class="material-icons" style="font-size:18px;color:rgba(255,255,255,.80);">folder_open</span>
            </button>

            @yield('header_actions')

            {{-- ── Divider ── --}}
            <div style="width:1px;height:22px;background:rgba(255,255,255,.18);flex-shrink:0;"></div>

            {{-- ── User Avatar Dropdown ── --}}
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

        </div>
    </div>
    @yield('header_actions1')
</header>
