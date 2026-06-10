@extends('layouts.app')

@section('page_title', 'Automation – Follow-up Reminder Rules')

@section('content')
<style>
/* ── Status cards ─────────────────────────────────────── */
.fr-status-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:24px; }
.fr-status-card {
    background:#fff; border-radius:16px; padding:20px 22px;
    border:1px solid #e2e8f0; box-shadow:0 1px 6px rgba(15,23,42,.06);
    display:flex; align-items:center; gap:16px; position:relative; overflow:hidden;
}
.fr-status-card::before { content:''; position:absolute; top:0;left:0;right:0;height:3px; background:var(--sc,#6366f1); border-radius:16px 16px 0 0; }
.fr-status-icon {
    width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
    background:var(--si,rgba(99,102,241,.1));
}
.fr-status-icon .material-icons { font-size:24px; color:var(--sk,#6366f1); }
.fr-status-label { font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:3px; }
.fr-status-val   { font-size:1.25rem;font-weight:800;color:var(--sk,#0f172a);line-height:1.1; }
.fr-status-pill  {
    position:absolute;top:16px;right:16px;
    font-size:.65rem;font-weight:700;padding:3px 10px;border-radius:20px;
    background:var(--sp-bg,#dcfce7);color:var(--sp-c,#16a34a);
}

/* ── Main card ────────────────────────────────────────── */
.fr-card {
    background:#fff; border-radius:16px; overflow:hidden;
    border:1px solid #e2e8f0; box-shadow:0 1px 8px rgba(15,23,42,.06);
}
.fr-card-head {
    background:linear-gradient(135deg,#1e3a6e,#0f172a);
    padding:18px 24px; display:flex; align-items:center; gap:12px;
}
.fr-card-head .material-icons { color:#fff; font-size:22px; }
.fr-card-head-title { color:#fff; font-weight:700; font-size:1rem; }
.fr-card-head-sub   { color:rgba(255,255,255,.5); font-size:.78rem; margin-top:1px; }
.fr-card-body { padding:28px 28px 24px; }

/* ── Section label ────────────────────────────────────── */
.fr-section-lbl {
    font-size:.68rem;font-weight:700;color:#6366f1;text-transform:uppercase;
    letter-spacing:.7px;margin-bottom:16px;display:flex;align-items:center;gap:6px;
    padding-bottom:8px;border-bottom:1.5px solid #eef2ff;
}
.fr-section-lbl .material-icons { font-size:14px; }

/* ── Toggle rows ──────────────────────────────────────── */
.fr-toggle-row {
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:16px; padding:16px 20px; border-radius:12px;
    border:1.5px solid #e2e8f0; background:#fafbfc;
    transition:border-color .18s, background .18s;
    margin-bottom:12px;
}
.fr-toggle-row:hover { border-color:#c7d2fe; background:#f8f9ff; }
.fr-toggle-info { flex:1; min-width:0; }
.fr-toggle-title { font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:3px; }
.fr-toggle-desc  { font-size:.78rem;color:#64748b;line-height:1.5; }
.fr-toggle-right { flex-shrink:0;display:flex;align-items:center;padding-top:2px; }

/* Custom switch */
.fr-switch { position:relative;width:48px;height:26px;cursor:pointer; }
.fr-switch input { opacity:0;width:0;height:0;position:absolute; }
.fr-switch-track {
    position:absolute;inset:0;border-radius:13px;
    background:#e2e8f0;transition:background .22s;
    border:1.5px solid #cbd5e1;
}
.fr-switch input:checked ~ .fr-switch-track { background:#6366f1;border-color:#6366f1; }
.fr-switch-thumb {
    position:absolute;top:3px;left:3px;width:18px;height:18px;
    border-radius:50%;background:#fff;
    box-shadow:0 1px 4px rgba(0,0,0,.2);
    transition:transform .22s;
}
.fr-switch input:checked ~ .fr-switch-thumb { transform:translateX(22px); }

/* ── Days input ───────────────────────────────────────── */
.fr-days-row {
    padding:16px 20px; border-radius:12px;
    border:1.5px solid #e2e8f0; background:#fafbfc;
    margin-bottom:12px;
}
.fr-days-row:focus-within { border-color:#a5b4fc; background:#f8f9ff; }
.fr-days-label { font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:3px; }
.fr-days-desc  { font-size:.78rem;color:#64748b;margin-bottom:12px; }
.fr-days-input-wrap { display:flex;align-items:center;gap:12px; }
.fr-days-input {
    width:90px;padding:9px 14px;border-radius:10px;
    border:1.5px solid #e2e8f0;font-size:.95rem;font-weight:700;
    color:#0f172a;background:#fff;outline:none;
    transition:border-color .18s,box-shadow .18s;text-align:center;
}
.fr-days-input:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.fr-days-hint { font-size:.75rem;color:#64748b; }

/* ── Divider ──────────────────────────────────────────── */
.fr-divider { height:1px;background:#f1f5f9;margin:20px 0; }

/* ── Save bar ─────────────────────────────────────────── */
.fr-save-bar {
    display:flex;align-items:center;gap:10px;padding:18px 28px;
    border-top:1px solid #f1f5f9;background:#f8fafc;
}
.fr-save-btn {
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 24px;border-radius:10px;font-size:.88rem;font-weight:700;
    background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;
    cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.35);
    transition:transform .15s,box-shadow .15s;
}
.fr-save-btn:hover { transform:translateY(-1px);box-shadow:0 6px 18px rgba(99,102,241,.45); }
.fr-save-btn .material-icons { font-size:17px; }
.fr-back-btn {
    display:inline-flex;align-items:center;gap:6px;
    padding:10px 20px;border-radius:10px;font-size:.88rem;font-weight:600;
    background:#fff;color:#64748b;border:1.5px solid #e2e8f0;
    text-decoration:none;transition:all .15s;
}
.fr-back-btn:hover { border-color:#94a3b8;color:#0f172a; }
.fr-back-btn .material-icons { font-size:17px; }
</style>

{{-- ── Status Cards ──────────────────────────────────────── --}}
<div class="fr-status-grid">
    {{-- Reminder Alerts --}}
    @php $remEnabled = $values['enabled']; @endphp
    <div class="fr-status-card" style="--sc:{{ $remEnabled ? '#f59e0b' : '#94a3b8' }};--si:{{ $remEnabled ? 'rgba(245,158,11,.12)' : 'rgba(148,163,184,.12)' }};--sk:{{ $remEnabled ? '#f59e0b' : '#94a3b8' }}">
        <div class="fr-status-icon"><span class="material-icons">notifications_active</span></div>
        <div>
            <div class="fr-status-label">Reminder Alerts</div>
            <div class="fr-status-val">{{ $remEnabled ? 'Enabled' : 'Disabled' }}</div>
        </div>
        <span class="fr-status-pill" style="--sp-bg:{{ $remEnabled ? '#fef3c7' : '#f1f5f9' }};--sp-c:{{ $remEnabled ? '#b45309' : '#64748b' }}">
            {{ $remEnabled ? 'Active' : 'Off' }}
        </span>
    </div>

    {{-- Overdue Highlight --}}
    @php $ovEnabled = $values['highlight_overdue']; @endphp
    <div class="fr-status-card" style="--sc:{{ $ovEnabled ? '#ef4444' : '#94a3b8' }};--si:{{ $ovEnabled ? 'rgba(239,68,68,.12)' : 'rgba(148,163,184,.12)' }};--sk:{{ $ovEnabled ? '#ef4444' : '#94a3b8' }}">
        <div class="fr-status-icon"><span class="material-icons">warning_amber</span></div>
        <div>
            <div class="fr-status-label">Overdue Highlight</div>
            <div class="fr-status-val">{{ $ovEnabled ? 'Enabled' : 'Disabled' }}</div>
        </div>
        <span class="fr-status-pill" style="--sp-bg:{{ $ovEnabled ? '#fee2e2' : '#f1f5f9' }};--sp-c:{{ $ovEnabled ? '#dc2626' : '#64748b' }}">
            {{ $ovEnabled ? 'Active' : 'Off' }}
        </span>
    </div>

    {{-- Daily Summary --}}
    @php $dsEnabled = $values['daily_summary_email_enabled'] ?? false; @endphp
    <div class="fr-status-card" style="--sc:{{ $dsEnabled ? '#6366f1' : '#94a3b8' }};--si:{{ $dsEnabled ? 'rgba(99,102,241,.12)' : 'rgba(148,163,184,.12)' }};--sk:{{ $dsEnabled ? '#6366f1' : '#94a3b8' }}">
        <div class="fr-status-icon"><span class="material-icons">mark_email_read</span></div>
        <div>
            <div class="fr-status-label">Daily Summary Email</div>
            <div class="fr-status-val">{{ $dsEnabled ? 'Enabled' : 'Disabled' }}</div>
        </div>
        <span class="fr-status-pill" style="--sp-bg:{{ $dsEnabled ? '#eef2ff' : '#f1f5f9' }};--sp-c:{{ $dsEnabled ? '#6366f1' : '#64748b' }}">
            {{ $dsEnabled ? 'Active' : 'Off' }}
        </span>
    </div>
</div>

{{-- ── Main Form Card ────────────────────────────────────── --}}
<div class="fr-card">
    {{-- Header --}}
    <div class="fr-card-head">
        <span class="material-icons">tune</span>
        <div>
            <div class="fr-card-head-title">Follow-up Reminder Rules</div>
            <div class="fr-card-head-sub">Configure telecaller reminders, overdue highlighting, and panel alerts</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.automation.followup-reminders.update') }}">
        @csrf
        <div class="fr-card-body">

            {{-- ── Notifications section ───────────────────────── --}}
            <div class="fr-section-lbl">
                <span class="material-icons">notifications</span>
                Notification Settings
            </div>

            {{-- Toggle: Enable reminders --}}
            <div class="fr-toggle-row">
                <div class="fr-toggle-info">
                    <div class="fr-toggle-title">Enable follow-up reminder notifications</div>
                    <div class="fr-toggle-desc">Sends reminder alerts to the telecaller panel notification feed when a follow-up is approaching.</div>
                </div>
                <div class="fr-toggle-right">
                    <label class="fr-switch">
                        <input type="checkbox" id="enabled" name="enabled" value="1" {{ $values['enabled'] ? 'checked' : '' }}>
                        <span class="fr-switch-track"></span>
                        <span class="fr-switch-thumb"></span>
                    </label>
                </div>
            </div>

            {{-- Days before due --}}
            <div class="fr-days-row">
                <div class="fr-days-label">Reminder days before due date</div>
                <div class="fr-days-desc">How many days in advance should telecallers be reminded of upcoming follow-ups?</div>
                <div class="fr-days-input-wrap">
                    <input type="number" name="days_before" id="days_before" min="0" max="30"
                           class="fr-days-input" value="{{ $values['days_before'] }}">
                    <div>
                        <div style="font-size:.82rem;font-weight:600;color:#0f172a;">{{ $values['days_before'] }} day(s) before due</div>
                        <div class="fr-days-hint">0 = remind on the same day only &nbsp;·&nbsp; max 30 days</div>
                    </div>
                </div>
            </div>

            {{-- Toggle: Highlight overdue --}}
            <div class="fr-toggle-row">
                <div class="fr-toggle-info">
                    <div class="fr-toggle-title">Highlight overdue follow-ups in telecaller alerts</div>
                    <div class="fr-toggle-desc">Overdue follow-ups will be visually highlighted in red within the telecaller's alert panel.</div>
                </div>
                <div class="fr-toggle-right">
                    <label class="fr-switch">
                        <input type="checkbox" id="highlight_overdue" name="highlight_overdue" value="1" {{ $values['highlight_overdue'] ? 'checked' : '' }}>
                        <span class="fr-switch-track"></span>
                        <span class="fr-switch-thumb"></span>
                    </label>
                </div>
            </div>

            <div class="fr-divider"></div>

            {{-- ── Email section ───────────────────────────────── --}}
            <div class="fr-section-lbl">
                <span class="material-icons">email</span>
                Email Notifications
            </div>

            {{-- Toggle: Daily summary email --}}
            <div class="fr-toggle-row">
                <div class="fr-toggle-info">
                    <div class="fr-toggle-title">
                        Send daily performance summary email to managers
                        <span style="margin-left:8px;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;background:#eef2ff;color:#6366f1;border:1px solid #c7d2fe;">7 PM daily</span>
                    </div>
                    <div class="fr-toggle-desc">Managers receive a summary of each telecaller's calls, conversions, and follow-ups every evening at 7 PM.</div>
                </div>
                <div class="fr-toggle-right">
                    <label class="fr-switch">
                        <input type="checkbox" id="daily_summary_email_enabled" name="daily_summary_email_enabled" value="1"
                               {{ ($values['daily_summary_email_enabled'] ?? false) ? 'checked' : '' }}>
                        <span class="fr-switch-track"></span>
                        <span class="fr-switch-thumb"></span>
                    </label>
                </div>
            </div>

        </div>

        {{-- ── Save bar ─────────────────────────────────────── --}}
        <div class="fr-save-bar">
            <button type="submit" class="fr-save-btn">
                <span class="material-icons">save</span>
                Save Rules
            </button>
            <a href="{{ route('admin.dashboard') }}" class="fr-back-btn">
                <span class="material-icons">arrow_back</span>
                Back
            </a>
        </div>
    </form>
</div>

<script>
// Live-update the days label as user types
document.getElementById('days_before')?.addEventListener('input', function () {
    const next = this.parentElement?.nextElementSibling;
    if (next) {
        const label = next.querySelector('div:first-child');
        if (label) label.textContent = this.value + ' day(s) before due';
    }
});
</script>
@endsection
