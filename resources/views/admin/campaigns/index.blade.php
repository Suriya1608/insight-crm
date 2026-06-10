@extends('layouts.app')

@section('page_title', 'WhatsApp Campaigns')

@section('content')

@php
$totalCampaigns  = $campaigns->total();
$activeCampaigns = $campaigns->getCollection()->where('status', 'active')->count();
$totalContacts   = $campaigns->getCollection()->sum('contacts_count');
$totalSent       = $campaigns->getCollection()->sum('wa_sent_count');
@endphp

{{-- ── Page Header ── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#25D366,#128C7E);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(37,211,102,.35);">
            <span class="material-icons" style="color:#fff;font-size:24px;">campaign</span>
        </div>
        <div>
            <h2 style="font-size:20px;font-weight:800;color:#0f172a;margin:0;">WhatsApp Campaigns</h2>
            <p style="font-size:12.5px;color:#64748b;margin:2px 0 0;">Send bulk WhatsApp blasts to campaign contacts</p>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.campaigns.performance') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:9px;background:#fff;color:#374151;font-size:12.5px;font-weight:600;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.borderColor='#6366f1';this.style.color='#6366f1';"
           onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#374151';">
            <span class="material-icons" style="font-size:16px;">insights</span> Performance
        </a>
        <a href="{{ route('admin.campaigns.contacts') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:9px;background:#fff;color:#374151;font-size:12.5px;font-weight:600;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.borderColor='#6366f1';this.style.color='#6366f1';"
           onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#374151';">
            <span class="material-icons" style="font-size:16px;">people</span> All Contacts
        </a>
    </div>
</div>

{{-- ── Stats Strip ── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
    @php
    $stats = [
        ['label'=>'Total Campaigns','value'=>$campaigns->total(),'icon'=>'campaign','color'=>'#6366f1','bg'=>'#eef2ff'],
        ['label'=>'Active','value'=>$campaigns->getCollection()->where('status','active')->count(),'icon'=>'play_circle','color'=>'#10b981','bg'=>'#ecfdf5'],
        ['label'=>'Total Contacts','value'=>number_format($campaigns->getCollection()->sum('contacts_count')),'icon'=>'people','color'=>'#f59e0b','bg'=>'#fffbeb'],
        ['label'=>'Messages Sent','value'=>number_format($campaigns->getCollection()->sum('wa_sent_count')),'icon'=>'send','color'=>'#25D366','bg'=>'#f0fdf4'],
    ];
    @endphp
    @foreach ($stats as $s)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:{{ $s['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span class="material-icons" style="font-size:20px;color:{{ $s['color'] }};">{{ $s['icon'] }}</span>
        </div>
        <div>
            <div style="font-size:20px;font-weight:800;color:#0f172a;line-height:1.1;">{{ $s['value'] }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Filters ── --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <div style="flex:1;min-width:200px;">
            <div style="position:relative;">
                <span class="material-icons" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:17px;color:#94a3b8;">search</span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search campaign name…"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px 8px 34px;font-size:13px;color:#0f172a;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
        </div>
        <div style="min-width:160px;">
            <select name="status"
                style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;color:#0f172a;outline:none;background:#fff;"
                onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">All Statuses</option>
                @foreach (['active','paused','completed','draft'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 20px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
            <span class="material-icons" style="font-size:16px;">filter_list</span> Filter
        </button>
        <a href="{{ route('admin.campaigns.index') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-size:13px;font-weight:500;text-decoration:none;">
            <span class="material-icons" style="font-size:15px;">close</span> Clear
        </a>
    </form>
</div>

{{-- ── Campaign Cards ── --}}
@if ($campaigns->isEmpty())
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:64px 24px;text-align:center;">
        <div style="width:72px;height:72px;border-radius:20px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <span class="material-icons" style="font-size:36px;color:#25D366;">campaign</span>
        </div>
        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:6px;">No campaigns found</div>
        <div style="font-size:13px;color:#64748b;">Try a different search or create a campaign from the Manager panel.</div>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
        @foreach ($campaigns as $campaign)
        @php
            $statusMeta = [
                'active'    => ['color'=>'#10b981','bg'=>'#ecfdf5','label'=>'Active'],
                'paused'    => ['color'=>'#f59e0b','bg'=>'#fffbeb','label'=>'Paused'],
                'completed' => ['color'=>'#6366f1','bg'=>'#eef2ff','label'=>'Completed'],
                'draft'     => ['color'=>'#94a3b8','bg'=>'#f8fafc','label'=>'Draft'],
            ];
            $waStatusMeta = [
                'idle'      => ['color'=>'#94a3b8','bg'=>'#f8fafc','label'=>'Idle'],
                'queued'    => ['color'=>'#f59e0b','bg'=>'#fffbeb','label'=>'Queued'],
                'sending'   => ['color'=>'#3b82f6','bg'=>'#eff6ff','label'=>'Sending'],
                'completed' => ['color'=>'#10b981','bg'=>'#ecfdf5','label'=>'Completed'],
                'failed'    => ['color'=>'#ef4444','bg'=>'#fef2f2','label'=>'Failed'],
            ];
            $sm      = $statusMeta[$campaign->status]      ?? $statusMeta['draft'];
            $waStatus= $campaign->wa_blast_status ?? 'idle';
            $wsm     = $waStatusMeta[$waStatus]            ?? $waStatusMeta['idle'];
            $waTotal = $campaign->contacts_count ?? 0;
            $waSent  = $campaign->wa_sent_count ?? 0;
            $waFailed= $campaign->wa_failed_count ?? 0;
            $pct     = $waTotal > 0 ? round(($waSent + $waFailed) / $waTotal * 100) : 0;
            $sentPct = $waTotal > 0 ? round($waSent / $waTotal * 100) : 0;
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .2s;"
             onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">

            {{-- Card Top Bar --}}
            <div style="height:4px;background:linear-gradient(90deg,#25D366,#128C7E);"></div>

            {{-- Card Body --}}
            <div style="padding:18px 20px;flex:1;display:flex;flex-direction:column;gap:14px;">

                {{-- Header row --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:15px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $campaign->name }}</div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:5px;flex-wrap:wrap;">
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $sm['bg'] }};color:{{ $sm['color'] }};">
                                {{ $sm['label'] }}
                            </span>
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#64748b;">
                                <span class="material-icons" style="font-size:13px;">people</span>
                                {{ number_format($waTotal) }} contacts
                            </span>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:11px;color:#64748b;">Converted</div>
                        <div style="font-size:18px;font-weight:800;color:#6366f1;">{{ $campaign->converted_count ?? 0 }}</div>
                    </div>
                </div>

                {{-- WhatsApp Blast Progress --}}
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span class="material-icons" style="font-size:15px;color:#25D366;">whatsapp</span>
                            <span style="font-size:12px;font-weight:600;color:#374151;">WhatsApp Blast</span>
                        </div>
                        <span id="wa-status-badge-{{ $campaign->id }}"
                            style="display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:10.5px;font-weight:700;background:{{ $wsm['bg'] }};color:{{ $wsm['color'] }};">
                            {{ $wsm['label'] }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div style="background:#e2e8f0;border-radius:99px;height:6px;overflow:hidden;margin-bottom:6px;">
                        <div id="wa-progress-bar-{{ $campaign->id }}"
                             style="height:100%;border-radius:99px;background:linear-gradient(90deg,#25D366,#128C7E);width:{{ $sentPct }}%;transition:width .4s;"></div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-size:11.5px;color:#64748b;" id="wa-progress-text-{{ $campaign->id }}">
                            <span id="wa-sent-{{ $campaign->id }}" style="color:#10b981;font-weight:700;">{{ $waSent }}</span> sent ·
                            <span id="wa-failed-{{ $campaign->id }}" style="color:#ef4444;font-weight:600;">{{ $waFailed }}</span> failed
                        </div>
                        <span style="font-size:11px;font-weight:700;color:#374151;">{{ $sentPct }}%</span>
                    </div>

                    @if ($campaign->wa_last_blast_at)
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px;display:flex;align-items:center;gap:4px;">
                        <span class="material-icons" style="font-size:12px;">schedule</span>
                        Last: {{ $campaign->wa_last_blast_at->format('d M Y H:i') }}
                    </div>
                    @endif
                </div>

                {{-- Blast Action --}}
                <div style="margin-top:auto;">
                    @if ($waTotal === 0)
                        <div style="text-align:center;padding:10px;background:#fafafa;border:1px dashed #e2e8f0;border-radius:8px;font-size:12px;color:#94a3b8;">
                            <span class="material-icons" style="font-size:14px;vertical-align:middle;margin-right:4px;">info</span>
                            No contacts — import contacts first
                        </div>
                    @else
                        <div style="display:flex;gap:8px;">
                            @if ($waTemplates->isEmpty())
                                <div style="flex:1;text-align:center;padding:9px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;">
                                    No active templates — add one in WhatsApp Templates
                                </div>
                            @else
                                <select class="wa-template-select" data-campaign="{{ $campaign->id }}"
                                    style="flex:1;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:12.5px;color:#0f172a;background:#fff;outline:none;"
                                    onfocus="this.style.borderColor='#25D366'" onblur="this.style.borderColor='#e2e8f0'">
                                    @foreach ($waTemplates as $tpl)
                                        <option value="{{ $tpl->name }}" data-lang="{{ $tpl->language }}">{{ $tpl->display_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="wa-blast-btn"
                                    id="wa-btn-{{ $campaign->id }}"
                                    data-campaign="{{ $campaign->id }}"
                                    data-blast-url="{{ route('admin.campaigns.whatsapp-blast', $campaign->id) }}"
                                    data-status-url="{{ route('admin.campaigns.whatsapp-blast.status', $campaign->id) }}"
                                    data-total="{{ $waTotal }}"
                                    style="display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:linear-gradient(135deg,#25D366,#128C7E);color:#fff;border:none;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;white-space:nowrap;flex-shrink:0;"
                                    {{ in_array($waStatus, ['sending','queued']) ? 'disabled' : '' }}>
                                    @if (in_array($waStatus, ['sending','queued']))
                                        <span class="spinner-border spinner-border-sm" role="status" style="width:13px;height:13px;border-width:2px;"></span>
                                        Sending…
                                    @else
                                        <span class="material-icons" style="font-size:15px;">send</span>
                                        Blast
                                    @endif
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>

    @if ($campaigns->hasPages())
    <div style="margin-top:20px;">{{ $campaigns->links() }}</div>
    @endif
@endif

{{-- Toast Area --}}
<div id="waBlastToastArea" style="position:fixed;bottom:24px;right:24px;z-index:9999;width:320px;display:flex;flex-direction:column;gap:8px;"></div>

@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const polls = {};

    const waStatusMeta = {
        idle:      { color:'#94a3b8', bg:'#f8fafc', label:'Idle' },
        queued:    { color:'#f59e0b', bg:'#fffbeb', label:'Queued' },
        sending:   { color:'#3b82f6', bg:'#eff6ff', label:'Sending' },
        completed: { color:'#10b981', bg:'#ecfdf5', label:'Completed' },
        failed:    { color:'#ef4444', bg:'#fef2f2', label:'Failed' },
    };

    function showToast(msg, type) {
        const area = document.getElementById('waBlastToastArea');
        if (!area) return;
        const colors = { success:'#10b981', error:'#ef4444', info:'#3b82f6' };
        const div = document.createElement('div');
        div.style.cssText = `background:#fff;border:1px solid #e2e8f0;border-left:4px solid ${colors[type]||'#6366f1'};border-radius:10px;padding:13px 16px;box-shadow:0 4px 20px rgba(0,0,0,.12);animation:fadeIn .2s ease;`;
        div.innerHTML = `<div style="font-size:13px;font-weight:600;color:#0f172a;">${msg}</div>`;
        area.appendChild(div);
        setTimeout(() => div.remove(), 5000);
    }

    function updateBadge(id, status) {
        const badge = document.getElementById(`wa-status-badge-${id}`);
        if (!badge) return;
        const m = waStatusMeta[status] || waStatusMeta.idle;
        badge.textContent = m.label;
        badge.style.background = m.bg;
        badge.style.color = m.color;
    }

    function startPolling(campaignId, statusUrl) {
        if (polls[campaignId]) return;
        polls[campaignId] = setInterval(async () => {
            try {
                const res  = await fetch(statusUrl, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
                const data = await res.json();
                if (!data.ok) return;

                const total   = data.total || 0;
                const sentPct = total > 0 ? Math.round((data.sent / total) * 100) : 0;
                const bar     = document.getElementById(`wa-progress-bar-${campaignId}`);
                const sentEl  = document.getElementById(`wa-sent-${campaignId}`);
                const failEl  = document.getElementById(`wa-failed-${campaignId}`);
                const btn     = document.getElementById(`wa-btn-${campaignId}`);

                if (bar)    bar.style.width = sentPct + '%';
                if (sentEl) sentEl.textContent = data.sent;
                if (failEl) failEl.textContent = data.failed;
                updateBadge(campaignId, data.status);

                if (['completed','failed','idle'].includes(data.status)) {
                    clearInterval(polls[campaignId]);
                    delete polls[campaignId];
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = `<span class="material-icons" style="font-size:15px;">send</span> Blast`;
                    }
                    if (data.status === 'completed') showToast(`Blast complete: ${data.sent} sent, ${data.failed} failed.`, 'success');
                    if (data.status === 'failed')    showToast('Blast failed — check server logs.', 'error');
                }
            } catch (_) {}
        }, 3000);
    }

    // Resume polls for in-progress blasts on page load
    document.querySelectorAll('.wa-blast-btn').forEach(btn => {
        const badge = document.getElementById(`wa-status-badge-${btn.dataset.campaign}`);
        if (badge && ['sending','queued'].includes(badge.textContent.trim().toLowerCase())) {
            startPolling(btn.dataset.campaign, btn.dataset.statusUrl);
        }
    });

    document.querySelectorAll('.wa-blast-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const campaignId   = this.dataset.campaign;
            const blastUrl     = this.dataset.blastUrl;
            const statusUrl    = this.dataset.statusUrl;
            const total        = this.dataset.total;
            const select       = document.querySelector(`.wa-template-select[data-campaign="${campaignId}"]`);
            const templateName = select ? select.value : '';
            const templateLang = select ? (select.options[select.selectedIndex]?.dataset.lang || 'en') : 'en';

            if (!confirm(`Send WhatsApp blast to ${Number(total).toLocaleString()} contacts using "${select?.options[select?.selectedIndex]?.text || templateName}"?`)) return;

            this.disabled = true;
            this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" style="width:13px;height:13px;border-width:2px;"></span> Queuing…`;

            try {
                const res  = await fetch(blastUrl, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':csrfToken, 'X-Requested-With':'XMLHttpRequest' },
                    body: JSON.stringify({ template_name: templateName, template_language: templateLang }),
                });
                const data = await res.json();
                if (data.ok) {
                    showToast(data.message, 'info');
                    updateBadge(campaignId, 'sending');
                    startPolling(campaignId, statusUrl);
                } else {
                    showToast(data.error ?? 'Failed to start blast.', 'error');
                    this.disabled = false;
                    this.innerHTML = `<span class="material-icons" style="font-size:15px;">send</span> Blast`;
                }
            } catch (_) {
                showToast('Network error. Please try again.', 'error');
                this.disabled = false;
                this.innerHTML = `<span class="material-icons" style="font-size:15px;">send</span> Blast`;
            }
        });
    });
})();
</script>
@endpush
