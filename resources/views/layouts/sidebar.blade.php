<aside class="sidebar {{ auth()->user()->role === 'telecaller' ? 'sidebar-telecaller' : '' }}" id="sidebar">

    {{-- Arrow toggle button — telecaller, manager and admin --}}
    @if(in_array(auth()->user()->role, ['telecaller', 'manager', 'admin']))
    <button class="tc-sidebar-arrow" id="tcSidebarArrow" onclick="toggleSidebar()" title="Toggle Sidebar">
        <span class="material-icons tc-sidebar-arrow-icon">chevron_left</span>
    </button>
    @endif
    <div class="sidebar-header">
        <div class="sidebar-logo">
            @php $siteLogo = \App\Models\Setting::get('site_logo'); @endphp
            @if($siteLogo)
                <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;border-radius:8px;">
            @else
                <span class="material-icons">business</span>
            @endif
        </div>
        <div class="sidebar-title">
            <h1>{{ \App\Models\Setting::get('site_name', 'Insight CRM') }}</h1>
            <p>{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }} Panel</p>
        </div>
        <button class="sidebar-close-btn" onclick="closeSidebar()" title="Close menu">
            <span class="material-icons">close</span>
        </button>
    </div>

    <nav class="sidebar-nav">

        {{-- ADMIN MENU — icon-only with flyout submenus (mirrors manager/telecaller) --}}
        @if (auth()->user()->role == 'admin')
            @php
                $adminUsersActive      = request()->routeIs('admin.users*');
                $adminLeadsActive      = request()->routeIs('admin.leads.*');
                $adminCampaignsActive  = request()->routeIs('admin.campaigns.*') || request()->routeIs('admin.whatsapp-templates*');
                $adminEmailCampActive  = request()->routeIs('admin.email-campaigns*') || request()->routeIs('admin.email-templates*');
                $adminReportsActive    = request()->routeIs('admin.reports.*');
                $adminAutomationActive = request()->routeIs('admin.automation.*');
                $adminSettingsActive   = request()->routeIs('admin.settings.*') || request()->routeIs('admin.settings');
            @endphp

            {{-- Dashboard --}}
            <a id="nav-admin-dashboard" href="{{ route('admin.dashboard') }}"
                data-tooltip="Home"
                class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="material-icons">home</span>
                <span>Home</span>
            </a>

            {{-- User Management --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-users" href="{{ route('admin.users.admins') }}"
                    data-tooltip="Users"
                    class="nav-item {{ $adminUsersActive ? 'active' : '' }}">
                    <span class="material-icons">group</span>
                    <span class="flex-grow-1 text-start">User Management</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">User Management</div>
                    <a href="{{ route('admin.users.admins') }}" class="{{ request()->routeIs('admin.users.admins') ? 'active' : '' }}">
                        <span class="material-icons">admin_panel_settings</span> Admin Users
                    </a>
                    <a href="{{ route('admin.users.managers') }}" class="{{ request()->routeIs('admin.users.managers') ? 'active' : '' }}">
                        <span class="material-icons">manage_accounts</span> Managers
                    </a>
                    <a href="{{ route('admin.users.telecallers') }}" class="{{ request()->routeIs('admin.users.telecallers') ? 'active' : '' }}">
                        <span class="material-icons">headset_mic</span> Telecallers
                    </a>
                </div>
            </div>

            {{-- Lead Management --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-leads" href="{{ route('admin.leads.all') }}"
                    data-tooltip="Leads"
                    class="nav-item {{ $adminLeadsActive ? 'active' : '' }}">
                    <span class="material-icons">person_add</span>
                    <span class="flex-grow-1 text-start">Lead Management</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Lead Management</div>
                    <a href="{{ route('admin.leads.all') }}" class="{{ request()->routeIs('admin.leads.all') ? 'active' : '' }}">
                        <span class="material-icons">people</span> All Leads
                    </a>
                    <a href="{{ route('admin.leads.unassigned') }}" class="{{ request()->routeIs('admin.leads.unassigned') ? 'active' : '' }}">
                        <span class="material-icons">person_off</span> Unassigned
                    </a>
                    <a href="{{ route('admin.leads.assigned') }}" class="{{ request()->routeIs('admin.leads.assigned') ? 'active' : '' }}">
                        <span class="material-icons">assignment_ind</span> Assigned
                    </a>
                    <a href="{{ route('admin.leads.converted') }}" class="{{ request()->routeIs('admin.leads.converted') ? 'active' : '' }}">
                        <span class="material-icons">task_alt</span> Converted
                    </a>
                    <a href="{{ route('admin.leads.lost') }}" class="{{ request()->routeIs('admin.leads.lost') ? 'active' : '' }}">
                        <span class="material-icons">person_remove</span> Lost
                    </a>
                    <a href="{{ route('admin.leads.duplicates') }}" class="{{ request()->routeIs('admin.leads.duplicates') ? 'active' : '' }}">
                        <span class="material-icons">content_copy</span> Duplicates
                    </a>
                </div>
            </div>

            {{-- Campaigns --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-campaigns" href="{{ route('admin.campaigns.performance') }}"
                    data-tooltip="Campaigns"
                    class="nav-item {{ $adminCampaignsActive ? 'active' : '' }}">
                    <span class="material-icons">insights</span>
                    <span class="flex-grow-1 text-start">Campaigns</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Campaigns</div>
                    <a href="{{ route('admin.campaigns.performance') }}" class="{{ request()->routeIs('admin.campaigns.performance') ? 'active' : '' }}">
                        <span class="material-icons">bar_chart</span> Performance
                    </a>
                    <a href="{{ route('admin.campaigns.contacts') }}" class="{{ request()->routeIs('admin.campaigns.contacts') ? 'active' : '' }}">
                        <span class="material-icons">contacts</span> All Contacts
                    </a>
                    <a href="{{ route('admin.campaigns.index') }}" class="{{ request()->routeIs('admin.campaigns.index') ? 'active' : '' }}">
                        <span class="material-icons">send</span> WhatsApp Blast
                    </a>
                    <a href="{{ route('admin.whatsapp-templates.index') }}" class="{{ request()->routeIs('admin.whatsapp-templates*') ? 'active' : '' }}">
                        <span class="material-icons">chat_bubble_outline</span> WA Templates
                    </a>
                </div>
            </div>

            {{-- Social Media --}}
            <a id="nav-admin-social" href="{{ route('admin.marketing.social.media') }}"
                data-tooltip="Social Media"
                class="nav-item {{ request()->routeIs('admin.marketing.*') ? 'active' : '' }}">
                <span class="material-icons">share</span>
                <span>Social Media</span>
            </a>

            {{-- Email Marketing --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-email" href="{{ route('admin.email-campaigns.index') }}"
                    data-tooltip="Email Marketing"
                    class="nav-item {{ $adminEmailCampActive ? 'active' : '' }}">
                    <span class="material-icons">mark_email_read</span>
                    <span class="flex-grow-1 text-start">Email Marketing</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Email Marketing</div>
                    <a href="{{ route('admin.email-campaigns.index') }}" class="{{ request()->routeIs('admin.email-campaigns*') ? 'active' : '' }}">
                        <span class="material-icons">campaign</span> Campaigns
                    </a>
                    <a href="{{ route('admin.email-templates.index') }}" class="{{ request()->routeIs('admin.email-templates*') ? 'active' : '' }}">
                        <span class="material-icons">description</span> Templates
                    </a>
                </div>
            </div>

            {{-- Reports --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-reports" href="{{ route('admin.reports.telecaller-performance') }}"
                    data-tooltip="Reports"
                    class="nav-item {{ $adminReportsActive ? 'active' : '' }}">
                    <span class="material-icons">bar_chart</span>
                    <span class="flex-grow-1 text-start">Reports</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Reports &amp; Analytics</div>
                    <a href="{{ route('admin.reports.telecaller-performance') }}" class="{{ request()->routeIs('admin.reports.telecaller-performance') ? 'active' : '' }}">
                        <span class="material-icons">leaderboard</span> Telecaller Performance
                    </a>
                    <a href="{{ route('admin.reports.manager-performance') }}" class="{{ request()->routeIs('admin.reports.manager-performance') ? 'active' : '' }}">
                        <span class="material-icons">trending_up</span> Manager Performance
                    </a>
                    <a href="{{ route('admin.reports.conversion') }}" class="{{ request()->routeIs('admin.reports.conversion') ? 'active' : '' }}">
                        <span class="material-icons">swap_horiz</span> Conversion Report
                    </a>
                    <a href="{{ route('admin.reports.lead-source') }}" class="{{ request()->routeIs('admin.reports.lead-source') ? 'active' : '' }}">
                        <span class="material-icons">donut_large</span> Lead Source
                    </a>
                    <a href="{{ route('admin.reports.period') }}" class="{{ request()->routeIs('admin.reports.period') ? 'active' : '' }}">
                        <span class="material-icons">date_range</span> Period Analysis
                    </a>
                    <a href="{{ route('admin.reports.call-efficiency') }}" class="{{ request()->routeIs('admin.reports.call-efficiency') ? 'active' : '' }}">
                        <span class="material-icons">speed</span> Call Efficiency
                    </a>
                    <a href="{{ route('admin.reports.response-time') }}" class="{{ request()->routeIs('admin.reports.response-time') ? 'active' : '' }}">
                        <span class="material-icons">timer</span> Response Time
                    </a>
                </div>
            </div>

            {{-- Automation --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-automation" href="{{ route('admin.automation.lead-assignment') }}"
                    data-tooltip="Automation"
                    class="nav-item {{ $adminAutomationActive ? 'active' : '' }}">
                    <span class="material-icons">auto_fix_high</span>
                    <span class="flex-grow-1 text-start">Automation</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Automation</div>
                    <a href="{{ route('admin.automation.lead-assignment') }}" class="{{ request()->routeIs('admin.automation.lead-assignment') ? 'active' : '' }}">
                        <span class="material-icons">assignment</span> Lead Assignment
                    </a>
                    <a href="{{ route('admin.automation.followup-reminders') }}" class="{{ request()->routeIs('admin.automation.followup-reminders') ? 'active' : '' }}">
                        <span class="material-icons">notifications_active</span> Follow-up Reminders
                    </a>
                    <a href="{{ route('admin.automation.escalation') }}" class="{{ request()->routeIs('admin.automation.escalation') ? 'active' : '' }}">
                        <span class="material-icons">warning_amber</span> Escalation Rules
                    </a>
                </div>
            </div>

            {{-- Master Data --}}
            <div class="tc-flyout-wrap">
                <a id="nav-admin-services" href="{{ route('admin.services.index') }}"
                    data-tooltip="Master Data"
                    class="nav-item {{ request()->routeIs('admin.services*') || request()->routeIs('admin.documents*') ? 'active' : '' }}">
                    <span class="material-icons">category</span>
                    <span>Master Data</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Master Data</div>
                    <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services*') ? 'active' : '' }}">
                        <span class="material-icons">inventory_2</span> Services
                    </a>
                    <a href="{{ route('admin.documents') }}" class="{{ request()->routeIs('admin.documents*') ? 'active' : '' }}">
                        <span class="material-icons">folder_open</span> Documents
                    </a>
                </div>
            </div>

            {{-- Settings --}}
            <a id="nav-admin-settings" href="{{ route('admin.settings.general') }}"
                data-tooltip="Settings"
                class="nav-item {{ $adminSettingsActive ? 'active' : '' }}">
                <span class="material-icons">settings</span>
                <span>Settings</span>
            </a>
        @endif


        {{-- MANAGER MENU — icon-only with flyout submenus (mirrors telecaller) --}}
        @if (auth()->user()->role == 'manager')
            @php
                $mgrLeadsActive     = request()->routeIs('manager.leads*') || request()->routeIs('manager.leads.pool');
                $mgrCampaignsActive = request()->routeIs('manager.campaigns.*');
                $mgrEmailCampActive = request()->routeIs('manager.email-campaigns*');
                $mgrReportsActive   = request()->routeIs('manager.reports.*');
                $mgrFollowupsActive = request()->routeIs('manager.followups.*');
                $mgrCallLogsActive  = request()->routeIs('manager.call-logs.*');
                $mgrCallScope       = request('scope', 'all');
            @endphp

            {{-- Dashboard --}}
            <a id="nav-mgr-dashboard" href="{{ route('manager.dashboard') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="Home"
                class="nav-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                <span class="material-icons">home</span>
                <span>Home</span>
            </a>

            {{-- Leads --}}
            <div class="tc-flyout-wrap">
                <a id="nav-mgr-leads" href="{{ route('manager.leads') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="Leads"
                    class="nav-item {{ $mgrLeadsActive ? 'active' : '' }}">
                    <span class="material-icons">person_add</span>
                    <span class="flex-grow-1 text-start">Leads</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Leads</div>
                    <a href="{{ route('manager.leads') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.leads') && !request()->routeIs('manager.leads.duplicates') && !request()->routeIs('manager.leads.pool') ? 'active' : '' }}">
                        <span class="material-icons">people</span> All Leads
                    </a>
                    <a href="{{ route('manager.leads.duplicates') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.leads.duplicates') ? 'active' : '' }}">
                        <span class="material-icons">content_copy</span> Duplicate Leads
                    </a>
                    <a href="{{ route('manager.leads.pool') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.leads.pool') ? 'active' : '' }}">
                        <span class="material-icons">inventory_2</span> Open Pool
                    </a>
                </div>
            </div>

            {{-- Telecallers --}}
            <a id="nav-mgr-telecallers" href="{{ route('manager.telecallers') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="Telecallers"
                class="nav-item {{ request()->routeIs('manager.telecallers*') ? 'active' : '' }}">
                <span class="material-icons">headset_mic</span>
                <span>Telecallers</span>
            </a>

            {{-- Campaigns --}}
            <div class="tc-flyout-wrap">
                <a id="nav-mgr-campaigns" href="{{ route('manager.campaigns.index') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="Campaigns"
                    class="nav-item {{ $mgrCampaignsActive ? 'active' : '' }}">
                    <span class="material-icons">campaign</span>
                    <span class="flex-grow-1 text-start">Campaigns</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Campaigns</div>
                    <a href="{{ route('manager.campaigns.index') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.campaigns.index') || request()->routeIs('manager.campaigns.show') || request()->routeIs('manager.campaigns.create') || request()->routeIs('manager.campaigns.contact') ? 'active' : '' }}">
                        <span class="material-icons">list</span> All Campaigns
                    </a>
                    <a href="{{ route('manager.campaigns.performance') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.campaigns.performance') ? 'active' : '' }}">
                        <span class="material-icons">bar_chart</span> Performance
                    </a>
                </div>
            </div>

            {{-- Email Campaigns --}}
            <a id="nav-mgr-email" href="{{ route('manager.email-campaigns.index') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="Email Campaigns"
                class="nav-item {{ $mgrEmailCampActive ? 'active' : '' }}">
                <span class="material-icons">mark_email_read</span>
                <span>Email Campaigns</span>
            </a>

            {{-- WhatsApp Chat --}}
            <a id="nav-mgr-whatsapp" href="{{ route('manager.whatsapp.hub') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="WhatsApp Chat"
                class="nav-item {{ request()->routeIs('manager.whatsapp.*') ? 'active' : '' }}">
                <span class="material-icons">chat</span>
                <span>WhatsApp Chat</span>
            </a>

            {{-- Follow-ups --}}
            <div class="tc-flyout-wrap">
                <a id="nav-mgr-followups" href="{{ route('manager.followups.today') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="Follow-ups"
                    class="nav-item {{ $mgrFollowupsActive ? 'active' : '' }}">
                    <span class="material-icons">event_note</span>
                    <span class="flex-grow-1 text-start">Follow-up Management</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Follow-ups</div>
                    <a href="{{ route('manager.followups.today') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.followups.today') ? 'active' : '' }}">
                        <span class="material-icons">today</span> Today
                    </a>
                    <a href="{{ route('manager.followups.overdue') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.followups.overdue') ? 'active' : '' }}">
                        <span class="material-icons">warning_amber</span> Overdue
                    </a>
                    <a href="{{ route('manager.followups.upcoming') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.followups.upcoming') ? 'active' : '' }}">
                        <span class="material-icons">upcoming</span> Upcoming
                    </a>
                    <a href="{{ route('manager.followups.missed') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.followups.missed') ? 'active' : '' }}">
                        <span class="material-icons">event_busy</span> Missed by Telecaller
                    </a>
                </div>
            </div>

            {{-- Call Logs --}}
            <div class="tc-flyout-wrap">
                <a id="nav-mgr-calllogs" href="{{ route('manager.call-logs.index', ['scope' => 'all']) }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="Call Logs"
                    class="nav-item {{ $mgrCallLogsActive ? 'active' : '' }}">
                    <span class="material-icons">call</span>
                    <span class="flex-grow-1 text-start">Call Logs</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Call Logs</div>
                    <a href="{{ route('manager.call-logs.index', ['scope' => 'all']) }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ $mgrCallLogsActive && $mgrCallScope === 'all' ? 'active' : '' }}">
                        <span class="material-icons">call_made</span> All Calls
                    </a>
                    <a href="{{ route('manager.call-logs.index', ['scope' => 'inbound']) }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ $mgrCallLogsActive && $mgrCallScope === 'inbound' ? 'active' : '' }}">
                        <span class="material-icons">call_received</span> Inbound
                    </a>
                    <a href="{{ route('manager.call-logs.index', ['scope' => 'outbound']) }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ $mgrCallLogsActive && $mgrCallScope === 'outbound' ? 'active' : '' }}">
                        <span class="material-icons">call_made</span> Outbound
                    </a>
                    <a href="{{ route('manager.call-logs.index', ['scope' => 'missed']) }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ $mgrCallLogsActive && $mgrCallScope === 'missed' ? 'active' : '' }}">
                        <span class="material-icons">phone_missed</span> Missed
                    </a>
                </div>
            </div>

            {{-- Reports & Analytics --}}
            <div class="tc-flyout-wrap">
                <a id="nav-mgr-reports" href="{{ route('manager.reports.home') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="Reports"
                    class="nav-item {{ $mgrReportsActive ? 'active' : '' }}">
                    <span class="material-icons">bar_chart</span>
                    <span class="flex-grow-1 text-start">Reports &amp; Analytics</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Reports &amp; Analytics</div>
                    <a href="{{ route('manager.reports.home') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.home') ? 'active' : '' }}">
                        <span class="material-icons">home</span> Overview
                    </a>
                    <a href="{{ route('manager.reports.telecaller-performance') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.telecaller-performance') ? 'active' : '' }}">
                        <span class="material-icons">leaderboard</span> Telecaller Performance
                    </a>
                    <a href="{{ route('manager.reports.conversion') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.conversion') ? 'active' : '' }}">
                        <span class="material-icons">trending_up</span> Conversion Report
                    </a>
                    <a href="{{ route('manager.reports.source-performance') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.source-performance') ? 'active' : '' }}">
                        <span class="material-icons">donut_large</span> Source Performance
                    </a>
                    <a href="{{ route('manager.reports.period') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.period') ? 'active' : '' }}">
                        <span class="material-icons">date_range</span> Daily / Weekly / Monthly
                    </a>
                    <a href="{{ route('manager.reports.response-time') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.response-time') ? 'active' : '' }}">
                        <span class="material-icons">timer</span> Lead Response Time
                    </a>
                    <a href="{{ route('manager.reports.call-efficiency') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('manager.reports.call-efficiency') ? 'active' : '' }}">
                        <span class="material-icons">speed</span> Call Efficiency
                    </a>
                </div>
            </div>

        @endif


        {{-- REPORT VIEWER MENU (Principal / Director) --}}
        @if (auth()->user()->role == 'report_viewer')
            @php
                $rvReportsActive = request()->routeIs('report_viewer.reports.*');
            @endphp

            {{-- Dashboard (Inertia) --}}
            <a id="nav-rv-dashboard" href="{{ route('report_viewer.dashboard') }}"
                onclick="inertiaVisit(event, this.href)"
                class="nav-item {{ request()->routeIs('report_viewer.dashboard') ? 'active' : '' }}">
                <span class="material-icons">home</span>
                <span>Home</span>
            </a>

            {{-- ── Analytics ── --}}
            <div class="nav-section-label">Analytics</div>

            <button id="nav-rv-reports" class="nav-item w-100 border-0 {{ $rvReportsActive ? 'active' : 'bg-transparent' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#rvReportsMenu"
                aria-expanded="{{ $rvReportsActive ? 'true' : 'false' }}" aria-controls="rvReportsMenu">
                <span class="material-icons">bar_chart</span>
                <span class="flex-grow-1 text-start">Reports</span>
                <span class="material-icons" style="font-size:18px;">expand_more</span>
            </button>
            <div id="rvReportsMenu" class="collapse {{ $rvReportsActive ? 'show' : '' }}"
                style="padding-left:12px;margin-top:-2px;margin-bottom:8px;">
                <a href="{{ route('report_viewer.reports.telecaller-performance') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.telecaller-performance') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Telecaller Performance</a>
                <a href="{{ route('report_viewer.reports.manager-performance') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.manager-performance') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Manager Performance</a>
                <a href="{{ route('report_viewer.reports.conversion') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.conversion') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Conversion Report</a>
                <a href="{{ route('report_viewer.reports.lead-source') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.lead-source') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Lead Source</a>
                <a href="{{ route('report_viewer.reports.period') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.period') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Period Report</a>
                <a href="{{ route('report_viewer.reports.call-efficiency') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.call-efficiency') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Call Efficiency</a>
                <a href="{{ route('report_viewer.reports.response-time') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.response-time') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Response Time</a>
                <a href="{{ route('report_viewer.reports.escalation-matrix') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.escalation-matrix') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Escalation Matrix</a>
                <a href="{{ route('report_viewer.reports.index') }}"
                    class="nav-item {{ request()->routeIs('report_viewer.reports.index') ? 'active' : '' }}"
                    style="padding:8px 12px 8px 36px;font-size:13px;">Download Reports</a>
            </div>
        @endif


        {{-- TELECALLER MENU — icon-only, Figma style --}}
        @if (auth()->user()->role == 'telecaller')
            @php
                // Reuse the same cache entry populated by the header to avoid a redundant DB hit.
            $_tcStats = \Illuminate\Support\Facades\Cache::remember(
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
            $teleFollowupReminderCount = $_tcStats[1] + $_tcStats[2];
                $teleFollowupMenuActive    = request()->routeIs('telecaller.followups.*');
                $telePerformanceMenuActive = request()->routeIs('telecaller.performance.*');
                $teleCallsMenuActive       = request()->routeIs('telecaller.calls.*');
                $followupTooltip = 'Follow-ups' . ($teleFollowupReminderCount > 0 ? ' ('.$teleFollowupReminderCount.')' : '');
            @endphp

            {{-- Dashboard --}}
            <a id="nav-tc-dashboard" href="{{ route('telecaller.dashboard') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="Home"
                class="nav-item {{ request()->routeIs('telecaller.dashboard') ? 'active' : '' }}">
                <span class="material-icons">home</span>
                <span>Home</span>
            </a>

            {{-- My Leads --}}
            <a id="nav-tc-leads" href="{{ route('telecaller.leads') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="My Leads"
                class="nav-item {{ request()->routeIs('telecaller.leads*') ? 'active' : '' }}">
                <span class="material-icons">person_add</span>
                <span>My Leads</span>
            </a>

            {{-- My Campaigns --}}
            <a id="nav-tc-campaigns" href="{{ route('telecaller.campaigns.index') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="My Campaigns"
                class="nav-item {{ request()->routeIs('telecaller.campaigns*') ? 'active' : '' }}">
                <span class="material-icons">campaign</span>
                <span>My Campaigns</span>
            </a>

            {{-- My Availability --}}
            <a id="nav-tc-availability" href="{{ route('telecaller.availability') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="My Availability"
                class="nav-item {{ request()->routeIs('telecaller.availability*') ? 'active' : '' }}">
                <span class="material-icons">event_available</span>
                <span>My Availability</span>
            </a>

            {{-- WhatsApp --}}
            <a id="nav-tc-whatsapp" href="{{ route('telecaller.whatsapp.hub') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="WhatsApp Chat"
                class="nav-item {{ request()->routeIs('telecaller.whatsapp.*') ? 'active' : '' }}">
                <span class="material-icons">chat</span>
                <span>WhatsApp Chat</span>
            </a>

            {{-- Call Management — flyout submenu --}}
            <div class="tc-flyout-wrap">
                <a id="nav-tc-calls" href="{{ route('telecaller.calls.outbound') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="Call Management"
                    class="nav-item {{ $teleCallsMenuActive ? 'active' : '' }}">
                    <span class="material-icons">call</span>
                    <span>Call Management</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">Call Management</div>
                    <a href="{{ route('telecaller.calls.outbound') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.calls.outbound') ? 'active' : '' }}">
                        <span class="material-icons">call_made</span> Outbound Calls
                    </a>
                    <a href="{{ route('telecaller.calls.inbound') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.calls.inbound') ? 'active' : '' }}">
                        <span class="material-icons">call_received</span> Inbound Calls
                    </a>
                    <a href="{{ route('telecaller.calls.missed') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.calls.missed') ? 'active' : '' }}">
                        <span class="material-icons">phone_missed</span> Missed Calls
                    </a>
                    <a href="{{ route('telecaller.calls.history') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.calls.history') ? 'active' : '' }}">
                        <span class="material-icons">history</span> Call History
                    </a>
                </div>
            </div>

            {{-- Follow-ups — flyout submenu --}}
            <div class="tc-flyout-wrap">
                <a id="nav-tc-followups" href="{{ route('telecaller.followups.today') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="{{ $followupTooltip }}"
                    class="nav-item {{ $teleFollowupMenuActive ? 'active' : '' }}">
                    <span class="material-icons">event_note</span>
                    <span>Follow-ups</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">
                        Follow-ups @if($teleFollowupReminderCount > 0) ({{ $teleFollowupReminderCount }}) @endif
                    </div>
                    <a href="{{ route('telecaller.followups.today') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.followups.today') ? 'active' : '' }}">
                        <span class="material-icons">today</span> Today
                        @if($teleFollowupReminderCount > 0)
                            <span style="margin-left:auto;background:#FF5C00;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;">{{ $teleFollowupReminderCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('telecaller.followups.upcoming') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.followups.upcoming') ? 'active' : '' }}">
                        <span class="material-icons">upcoming</span> Upcoming
                    </a>
                    <a href="{{ route('telecaller.followups.overdue') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.followups.overdue') ? 'active' : '' }}">
                        <span class="material-icons">warning_amber</span> Overdue
                    </a>
                    <a href="{{ route('telecaller.followups.completed') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.followups.completed') ? 'active' : '' }}">
                        <span class="material-icons">task_alt</span> Completed
                    </a>
                </div>
            </div>

            {{-- My Performance — flyout submenu --}}
            <div class="tc-flyout-wrap">
                <a id="nav-tc-performance" href="{{ route('telecaller.performance.daily') }}"
                    onclick="inertiaVisit(event, this.href)"
                    data-tooltip="My Performance"
                    class="nav-item {{ $telePerformanceMenuActive ? 'active' : '' }}">
                    <span class="material-icons">trending_up</span>
                    <span>My Performance</span>
                </a>
                <div class="tc-flyout-menu">
                    <div class="tc-flyout-menu-title">My Performance</div>
                    <a href="{{ route('telecaller.performance.daily') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.performance.daily') ? 'active' : '' }}">
                        <span class="material-icons">today</span> Daily
                    </a>
                    <a href="{{ route('telecaller.performance.weekly') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.performance.weekly') ? 'active' : '' }}">
                        <span class="material-icons">date_range</span> Weekly
                    </a>
                    <a href="{{ route('telecaller.performance.monthly') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.performance.monthly') ? 'active' : '' }}">
                        <span class="material-icons">calendar_month</span> Monthly
                    </a>
                    <a href="{{ route('telecaller.performance.custom') }}" onclick="inertiaVisit(event, this.href)"
                        class="{{ request()->routeIs('telecaller.performance.custom') ? 'active' : '' }}">
                        <span class="material-icons">tune</span> Custom Range
                    </a>
                </div>
            </div>

            {{-- My Reports --}}
            <a id="nav-tc-reports" href="{{ route('telecaller.reports.index') }}"
                onclick="inertiaVisit(event, this.href)"
                data-tooltip="My Reports"
                class="nav-item {{ request()->routeIs('telecaller.reports.*') ? 'active' : '' }}">
                <span class="material-icons">download</span>
                <span>My Reports</span>
            </a>
        @endif

    </nav>

    @if(auth()->user()->role === 'admin')
    <div class="sidebar-footer">
        <div class="user-profile" style="position:relative;">
            <div class="user-avatar" role="button" onclick="toggleUserMenu()" title="Account options" style="cursor:pointer;">
                @php $initials = strtoupper(substr(auth()->user()->name, 0, 1)); @endphp
                <span class="user-avatar-initials">{{ $initials }}</span>
            </div>
            <div class="user-info" style="cursor:pointer;" onclick="toggleUserMenu()">
                <p>{{ auth()->user()->name }}</p>
                <span>
                    <span class="material-icons" style="font-size:10px;vertical-align:middle;">
                        @if(auth()->user()->role === 'admin') admin_panel_settings
                        @elseif(auth()->user()->role === 'manager') manage_accounts
                        @else headset_mic
                        @endif
                    </span>
                    {{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}
                </span>
            </div>

            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                @csrf
                <button type="submit" class="btn btn-link p-0" title="Logout">
                    <span class="material-icons" style="font-size: 20px;">logout</span>
                </button>
            </form>

            {{-- User popup menu --}}
            <div id="sidebarUserMenu" style="display:none;position:absolute;bottom:60px;left:0;right:0;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.12);z-index:9999;overflow:hidden;">
                <a href="{{ route('password.change') }}" class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none" style="color:#0f172a;font-size:13px;font-weight:500;transition:background .15s;" onmouseover="this.style.background='#f6f7f8'" onmouseout="this.style.background='transparent'">
                    <span class="material-icons" style="font-size:18px;color:#137fec;">lock_reset</span>
                    Change Password
                </a>
            </div>
        </div>
    </div>
    @endif
</aside>

<script>
function toggleUserMenu() {
    var menu = document.getElementById('sidebarUserMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('sidebarUserMenu');
    if (!menu) return;
    if (!e.target.closest('.user-profile')) {
        menu.style.display = 'none';
    }
});

/**
 * inertiaVisit(event, url)
 *
 * Intercepts a link click and delegates to Inertia's router so only the
 * React page component swaps — the outer shell (sidebar, header, TCN iframe)
 * is never destroyed, keeping the SIP/WebRTC connection alive across navigation.
 *
 * Falls back to normal browser navigation if the Inertia router isn't ready yet
 * (e.g. first hard load before inertia-app.jsx has bootstrapped).
 */
function inertiaVisit(event, url) {
    if (window._inertiaRouter) {
        event.preventDefault();
        window._inertiaRouter.visit(url);
    }
    // else: let the default href navigate normally
}

function syncSidebarActive(url) {
    try {
        const path = new URL(url, window.location.origin).pathname;
        const nav = document.querySelector('.sidebar-nav');
        if (!nav) return;

        // Remove active from all plain nav-item anchors and buttons
        nav.querySelectorAll('a.nav-item, button.nav-item').forEach(el => {
            el.classList.remove('active');
            if (el.tagName === 'BUTTON') {
                el.classList.add('bg-transparent');
            }
        });

        // Close all collapse sub-menus and reset their toggle buttons
        nav.querySelectorAll('.collapse').forEach(el => {
            el.classList.remove('show');
            const btn = nav.querySelector(`[data-bs-target="#${el.id}"]`);
            if (btn) btn.setAttribute('aria-expanded', 'false');
        });

        // Find the best matching link
        let bestLink = null;
        let bestLen = 0;
        nav.querySelectorAll('a.nav-item[href]').forEach(a => {
            try {
                const aPath = new URL(a.href, window.location.origin).pathname;
                if (path.startsWith(aPath) && aPath.length > bestLen) {
                    bestLen = aPath.length;
                    bestLink = a;
                }
            } catch (e) {}
        });

        if (bestLink) {
            bestLink.classList.add('active');
            // If it's inside a collapse sub-menu, open the parent collapse and activate its toggle button
            const collapse = bestLink.closest('.collapse');
            if (collapse) {
                collapse.classList.add('show');
                const toggleId = collapse.id;
                const btn = nav.querySelector(`[data-bs-target="#${toggleId}"]`);
                if (btn) {
                    btn.classList.add('active');
                    btn.classList.remove('bg-transparent');
                    btn.setAttribute('aria-expanded', 'true');
                }
            }
        }
    } catch (e) {}
}

// Take full ownership of sidebar collapse toggles:
// 1. Remove data-bs-toggle so Bootstrap's event delegation never sees these buttons.
// 2. Attach our own click handler that directly toggles the .show class.
// Must run on both DOMContentLoaded (first hard load) AND turbo:load (every
// subsequent Turbo Drive navigation that replaces the body with fresh HTML).
function initNavCollapseHandlers() {
    document.querySelectorAll('.sidebar-nav button.nav-item[data-bs-toggle="collapse"]').forEach(function(btn) {
        const targetSelector = btn.getAttribute('data-bs-target');

        // Strip Bootstrap's hook — its document-level delegation selector
        // [data-bs-toggle="collapse"] will no longer match these buttons.
        btn.removeAttribute('data-bs-toggle');
        btn.removeAttribute('data-bs-target');

        btn.addEventListener('click', function() {
            const target = document.querySelector(targetSelector);
            if (!target) return;
            const isOpen = target.classList.contains('show');
            target.classList.toggle('show', !isOpen);
            btn.setAttribute('aria-expanded', String(!isOpen));
        });
    });
}

function initSidebar() {
    initNavCollapseHandlers();
    syncSidebarActive(window.location.href);
}

// Hard page load
document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    // Keep active state in sync across Inertia SPA navigations
    if (window._inertiaRouter && typeof window._inertiaRouter.on === 'function') {
        window._inertiaRouter.on('navigate', function () {
            syncSidebarActive(window.location.href);
        });
    }
});

// Turbo Drive navigation (replaces body — new buttons need fresh handlers)
document.addEventListener('turbo:load', function () {
    initSidebar();
});

// Flyout submenu viewport-overflow correction.
// When a flyout panel (position:absolute, top:0) would extend below the
// visible area, shift it upward by the exact overflow amount.
(function () {
    function positionFlyout(wrap) {
        const menu = wrap.querySelector('.tc-flyout-menu');
        if (!menu) return;
        menu.style.top = '0';
        const overflow = menu.getBoundingClientRect().bottom - (window.innerHeight - 8);
        if (overflow > 0) menu.style.top = -overflow + 'px';
    }
    function resetFlyout(wrap) {
        const menu = wrap.querySelector('.tc-flyout-menu');
        if (menu) menu.style.top = '0';
    }
    function attachFlyoutHandlers() {
        document.querySelectorAll('.tc-flyout-wrap').forEach(function (wrap) {
            wrap.removeEventListener('mouseenter', wrap._flyoutEnter);
            wrap.removeEventListener('mouseleave', wrap._flyoutLeave);
            wrap._flyoutEnter = function () { positionFlyout(wrap); };
            wrap._flyoutLeave = function () { resetFlyout(wrap); };
            wrap.addEventListener('mouseenter', wrap._flyoutEnter);
            wrap.addEventListener('mouseleave', wrap._flyoutLeave);
        });
    }
    document.addEventListener('DOMContentLoaded', attachFlyoutHandlers);
    document.addEventListener('turbo:load', attachFlyoutHandlers);
}());
</script>
