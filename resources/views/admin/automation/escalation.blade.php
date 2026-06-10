@extends('layouts.app')

@section('page_title', 'Automation – Escalation Rules')

@section('content')

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card {{ $values['enabled'] ? 'highlight-success' : 'highlight-danger' }}">
            <div class="stat-icon {{ $values['enabled'] ? 'green' : 'red' }}">
                <span class="material-icons">{{ $values['enabled'] ? 'bolt' : 'block' }}</span>
            </div>
            <div class="stat-label">Escalation Engine</div>
            <div class="stat-value">{{ $values['enabled'] ? 'Enabled' : 'Disabled' }}</div>
            <div class="stat-trend {{ $values['enabled'] ? 'up' : 'down' }} mt-2">
                <span class="material-icons" style="font-size:13px;">{{ $values['enabled'] ? 'check_circle' : 'cancel' }}</span>
                {{ $values['enabled'] ? 'Auto-escalation active' : 'Automation paused' }}
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon blue"><span class="material-icons">support_agent</span></div>
            <div class="stat-label">Telecaller SLA</div>
            <div class="stat-value">{{ $values['response_sla_minutes'] }} <span style="font-size:14px;font-weight:600;color:var(--text-muted)">min</span></div>
            <div class="stat-trend stable mt-2">
                <span class="material-icons" style="font-size:13px;">schedule</span>
                Response window per lead
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon amber"><span class="material-icons">manage_accounts</span></div>
            <div class="stat-label">Manager SLA</div>
            <div class="stat-value">{{ $values['manager_sla_minutes'] }} <span style="font-size:14px;font-weight:600;color:var(--text-muted)">min</span></div>
            <div class="stat-trend stable mt-2">
                <span class="material-icons" style="font-size:13px;">schedule</span>
                Escalation window after TC
            </div>
        </div>
    </div>
</div>

{{-- ── Hierarchy Pipeline ── --}}
<div class="chart-card mb-4">
    <div class="chart-header mb-4">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="material-icons" style="color:var(--primary-color);font-size:18px;">account_tree</span>
            <h3 class="mb-0">Escalation Hierarchy</h3>
        </div>
        <p class="text-muted mb-0" style="font-size:13px;">How unresponded leads flow up the chain automatically.</p>
    </div>

    <div class="escalation-pipeline">
        {{-- Step 1: Telecaller --}}
        <div class="pipeline-node node-blue">
            <div class="node-badge">1</div>
            <div class="node-icon-wrap blue">
                <span class="material-icons">support_agent</span>
            </div>
            <div class="node-title">Telecaller</div>
            <div class="node-sub">Must respond in</div>
            <div class="node-sla" style="color:#3b82f6;">{{ $values['response_sla_minutes'] }} min</div>
        </div>

        {{-- Arrow --}}
        <div class="pipeline-arrow">
            <div class="arrow-line"></div>
            <span class="material-icons arrow-icon">arrow_forward_ios</span>
            <div class="arrow-label">no response</div>
        </div>

        {{-- Step 2: Manager --}}
        <div class="pipeline-node node-amber">
            <div class="node-badge" style="background:#f97316;">2</div>
            <div class="node-icon-wrap amber">
                <span class="material-icons">manage_accounts</span>
            </div>
            <div class="node-title">Manager</div>
            <div class="node-sub">Must respond in</div>
            <div class="node-sla" style="color:#f97316;">{{ $values['manager_sla_minutes'] }} min</div>
        </div>

        {{-- Arrow --}}
        <div class="pipeline-arrow">
            <div class="arrow-line" style="background:linear-gradient(90deg,#fed7aa,#fecaca);"></div>
            <span class="material-icons arrow-icon" style="color:#ef4444;">arrow_forward_ios</span>
            <div class="arrow-label">no response</div>
        </div>

        {{-- Step 3: Admin --}}
        <div class="pipeline-node node-red">
            <div class="node-badge" style="background:#ef4444;">3</div>
            <div class="node-icon-wrap red">
                <span class="material-icons">admin_panel_settings</span>
            </div>
            <div class="node-title">Admin / Report Viewer</div>
            <div class="node-sub">Critical alert sent</div>
            <div class="node-sla" style="color:#ef4444;">Final escalation</div>
        </div>
    </div>

    {{-- SLA Reset Info --}}
    <div class="sla-info-banner mt-4">
        <div class="d-flex align-items-start gap-2">
            <span class="material-icons flex-shrink-0 mt-1" style="font-size:16px;color:#6366f1;">info</span>
            <div style="font-size:13px;color:var(--text-dark);">
                <strong>SLA reset:</strong> Any activity on the lead (call, note, WhatsApp, status change, follow-up) by any user resets the escalation clock.
                Lead <em>assignment</em> to a telecaller does <strong>not</strong> count as a response.
            </div>
        </div>
    </div>
</div>

{{-- ── Rules Form ── --}}
<div class="chart-card">
    <div class="chart-header mb-4">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="material-icons" style="color:var(--primary-color);font-size:18px;">tune</span>
            <h3 class="mb-0">Escalation Rules</h3>
        </div>
        <p class="text-muted mb-0" style="font-size:13px;">Configure SLA timeframes and toggle escalation types.</p>
    </div>

    <form method="POST" action="{{ route('admin.automation.escalation.update') }}">
        @csrf

        {{-- Toggle Rule Cards --}}
        <div class="row g-3 mb-4">
            {{-- Enable Escalation --}}
            <div class="col-12">
                <label class="rule-toggle-card" for="enabled">
                    <div class="rule-toggle-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <span class="material-icons">bolt</span>
                    </div>
                    <div class="rule-toggle-body">
                        <div class="rule-toggle-title">Enable escalation automation</div>
                        <div class="rule-toggle-desc">Master switch — turns on/off the entire escalation engine for all rules below.</div>
                    </div>
                    <div class="ms-auto flex-shrink-0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input rule-switch" type="checkbox" id="enabled"
                                name="enabled" value="1" {{ $values['enabled'] ? 'checked' : '' }}>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Missed Follow-ups --}}
            <div class="col-md-6">
                <label class="rule-toggle-card h-100" for="missed_followups">
                    <div class="rule-toggle-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                        <span class="material-icons">event_busy</span>
                    </div>
                    <div class="rule-toggle-body">
                        <div class="rule-toggle-title">Escalate missed follow-ups</div>
                        <div class="rule-toggle-desc">Creates in-app + email manager notification when a follow-up is missed.</div>
                    </div>
                    <div class="ms-auto flex-shrink-0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input rule-switch" type="checkbox" id="missed_followups"
                                name="missed_followups" value="1" {{ $values['missed_followups'] ? 'checked' : '' }}>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Response SLA --}}
            <div class="col-md-6">
                <label class="rule-toggle-card h-100" for="response_sla">
                    <div class="rule-toggle-icon" style="background:linear-gradient(135deg,#10b981,#06b6d4);">
                        <span class="material-icons">timer</span>
                    </div>
                    <div class="rule-toggle-body">
                        <div class="rule-toggle-title">Escalate leads not contacted in time</div>
                        <div class="rule-toggle-desc">Runs the full hierarchy when leads go unanswered beyond the SLA window.</div>
                    </div>
                    <div class="ms-auto flex-shrink-0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input rule-switch" type="checkbox" id="response_sla"
                                name="response_sla" value="1" {{ $values['response_sla'] ? 'checked' : '' }}>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- SLA Timeframes --}}
        <div class="sla-section">
            <div class="sla-section-header">
                <span class="material-icons" style="font-size:14px;color:var(--primary-color);">access_time</span>
                SLA Timeframes
            </div>
            <div class="row g-3 mt-0">
                <div class="col-md-6">
                    <div class="sla-input-card">
                        <div class="sla-input-header">
                            <div class="sla-input-icon" style="background:#eff6ff;">
                                <span class="material-icons" style="color:#3b82f6;font-size:18px;">support_agent</span>
                            </div>
                            <div>
                                <div class="sla-input-title">Telecaller Response SLA</div>
                                <div class="sla-input-sub">First-level response window</div>
                            </div>
                        </div>
                        <div class="sla-input-row">
                            <input type="number" name="response_sla_minutes" min="5" max="10080"
                                class="form-control sla-number-input" value="{{ $values['response_sla_minutes'] }}">
                            <span class="sla-unit">min</span>
                        </div>
                        <div class="sla-input-hint">
                            <span class="material-icons" style="font-size:12px;">arrow_right</span>
                            No activity within this time → notify manager
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="sla-input-card">
                        <div class="sla-input-header">
                            <div class="sla-input-icon" style="background:#fff7ed;">
                                <span class="material-icons" style="color:#f97316;font-size:18px;">manage_accounts</span>
                            </div>
                            <div>
                                <div class="sla-input-title">Manager Response SLA</div>
                                <div class="sla-input-sub">Second-level escalation window</div>
                            </div>
                        </div>
                        <div class="sla-input-row">
                            <input type="number" name="manager_sla_minutes" min="5" max="10080"
                                class="form-control sla-number-input" value="{{ $values['manager_sla_minutes'] }}">
                            <span class="sla-unit">min</span>
                        </div>
                        <div class="sla-input-hint">
                            <span class="material-icons" style="font-size:12px;">arrow_right</span>
                            Still no activity → alert admin &amp; report viewers
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2 align-items-center">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4">
                <span class="material-icons" style="font-size:18px;">save</span>
                Save Rules
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <span class="material-icons" style="font-size:17px;">arrow_back</span>
                Back
            </a>
        </div>
    </form>
</div>

<style>
/* ── Escalation Pipeline ── */
.escalation-pipeline {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
    row-gap: 16px;
}

.pipeline-node {
    position: relative;
    text-align: center;
    padding: 20px 24px;
    border-radius: 14px;
    min-width: 148px;
    flex: 1;
    min-height: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: transform .2s;
}
.pipeline-node:hover { transform: translateY(-3px); }

.node-blue  { background: #eff6ff; border: 1.5px solid #bfdbfe; }
.node-amber { background: #fff7ed; border: 1.5px solid #fed7aa; }
.node-red   { background: #fef2f2; border: 1.5px solid #fecaca; }

.node-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #3b82f6;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}

.node-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}
.node-icon-wrap.blue  { background: var(--grad-primary); box-shadow: 0 4px 14px rgba(99,102,241,.3); }
.node-icon-wrap.amber { background: var(--grad-warning); box-shadow: 0 4px 14px rgba(245,158,11,.3); }
.node-icon-wrap.red   { background: var(--grad-danger);  box-shadow: 0 4px 14px rgba(239,68,68,.3);  }
.node-icon-wrap .material-icons { color: #fff; font-size: 22px; }

.node-title { font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
.node-sub   { font-size: 10.5px; color: var(--text-muted); margin-bottom: 4px; }
.node-sla   { font-size: 15px; font-weight: 800; letter-spacing: -.3px; }

.pipeline-arrow {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 0 6px;
    flex-shrink: 0;
}
.arrow-line {
    width: 36px;
    height: 2px;
    background: linear-gradient(90deg,#bfdbfe,#fed7aa);
    border-radius: 2px;
}
.arrow-icon { font-size: 14px; color: #f97316; }
.arrow-label { font-size: 9px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .4px; }

/* ── SLA Info Banner ── */
.sla-info-banner {
    background: linear-gradient(135deg,#eef2ff 0%,#f0fdf4 100%);
    border: 1px solid #c7d2fe;
    border-radius: 10px;
    padding: 14px 16px;
}

/* ── Rule Toggle Card ── */
.rule-toggle-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #f8fafc;
    border: 1.5px solid var(--border-color);
    border-radius: 12px;
    padding: 16px 18px;
    cursor: pointer;
    transition: border-color .2s, background .2s, box-shadow .2s;
    width: 100%;
    text-align: left;
}
.rule-toggle-card:hover {
    border-color: rgba(99,102,241,.35);
    background: #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,.08);
}

.rule-toggle-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rule-toggle-icon .material-icons { color: #fff; font-size: 20px; }

.rule-toggle-body { flex: 1; }
.rule-toggle-title { font-size: 13.5px; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
.rule-toggle-desc  { font-size: 12px; color: var(--text-muted); line-height: 1.45; }

.rule-switch {
    width: 44px !important;
    height: 24px !important;
    cursor: pointer;
}
.rule-switch:checked { background-color: var(--primary-color); border-color: var(--primary-color); }

/* ── SLA Section ── */
.sla-section {
    background: #f8fafc;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 18px 20px 20px;
}
.sla-section-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--text-muted);
    margin-bottom: 14px;
}

.sla-input-card {
    background: #fff;
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    padding: 16px;
    transition: border-color .2s, box-shadow .2s;
}
.sla-input-card:focus-within {
    border-color: rgba(99,102,241,.4);
    box-shadow: 0 0 0 3px rgba(99,102,241,.08);
}

.sla-input-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.sla-input-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sla-input-title { font-size: 13px; font-weight: 700; color: var(--text-dark); }
.sla-input-sub   { font-size: 11px; color: var(--text-muted); }

.sla-input-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.sla-number-input {
    font-size: 22px !important;
    font-weight: 800 !important;
    color: var(--text-dark) !important;
    border: none !important;
    background: #f1f5f9 !important;
    border-radius: 8px !important;
    padding: 8px 14px !important;
    width: 120px !important;
    letter-spacing: -.5px;
}
.sla-number-input:focus {
    outline: none !important;
    box-shadow: none !important;
    background: #e0e7ff !important;
}
.sla-unit {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
}
.sla-input-hint {
    display: flex;
    align-items: center;
    font-size: 11.5px;
    color: var(--text-muted);
    gap: 2px;
}

/* Responsive pipeline */
@media (max-width: 640px) {
    .escalation-pipeline { flex-direction: column; align-items: stretch; }
    .pipeline-arrow { flex-direction: row; padding: 4px 0; }
    .arrow-line { width: 24px; height: 2px; }
}
</style>
@endsection
