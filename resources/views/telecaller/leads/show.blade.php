@extends('layouts.app')

@section('content')
    @include('layouts.whatsappchat')

    <style>
        .admin-lead-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1d2c4e 45%, #253769 75%, #1a3460 100%);
            border-radius: 16px;
            padding: 22px 24px;
            color: #fff;
            box-shadow: 0 10px 28px rgba(15, 39, 73, 0.22);
        }
        .admin-lead-hero .meta {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            letter-spacing: 0.01em;
        }
        .admin-lead-hero h2 { font-size: calc(2rem - 2pt); }
        .admin-info-card {
            border-radius: 14px;
            border: 1px solid #dbe5f1;
            background: #fff;
            padding: 13px 14px;
            min-height: 96px;
            box-shadow: 0 4px 12px rgba(10, 31, 68, 0.05);
        }
        .admin-info-label {
            color: #5b6f8f;
            font-size: 10px;
            letter-spacing: 0.08em;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .admin-info-value {
            color: #0d1f3a;
            font-weight: 700;
            font-size: 27px;
            line-height: 1.1;
            word-break: break-word;
        }
        .admin-info-value.small { font-size: 18px; }
        .admin-status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: #eaf2ff;
            color: #1657b8;
        }
        .admin-timeline {
            border-radius: 16px;
            border: 1px solid #dbe5f1;
            background: #fff;
            overflow: hidden;
        }
        .admin-timeline-head {
            padding: 18px 20px;
            border-bottom: 1px solid #e8eef7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .admin-timeline-count {
            border-radius: 999px;
            background: #edf4ff;
            color: #275ca8;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px;
        }
        .admin-timeline-body {
            max-height: 640px;
            overflow-y: auto;
            padding: 12px 20px 20px;
        }
        .admin-timeline-item {
            display: grid;
            grid-template-columns: 36px 1fr;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px dashed #e8eef7;
        }
        .admin-timeline-item:last-child { border-bottom: 0; }
        .admin-timeline-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #edf3fb;
            color: #0f365c;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-timeline-type {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 11px;
            background: #f0f5ff;
            color: #234d8f;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .admin-filter-btn {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1px solid #dbe5f1;
            background: #fff;
            color: #5b6f8f;
            cursor: pointer;
            transition: all .15s;
        }
        .admin-filter-btn.active,
        .admin-filter-btn:hover { background: #1657b8; border-color: #1657b8; color: #fff; }
        .admin-section-card {
            border-radius: 14px;
            border: 1px solid #dbe5f1;
            background: #fff;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .admin-section-head {
            padding: 14px 18px;
            border-bottom: 1px solid #e8eef7;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-section-body { padding: 16px 18px; }
    </style>

    {{-- Hero Banner --}}
    <div class="admin-lead-hero mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="meta mb-2">Lead Profile</div>
                <h2 class="mb-1 fw-bold">{{ $lead->name }}</h2>
                <div class="meta">{{ $lead->lead_code }} | {{ $lead->phone }}</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <x-aging-badge :days="$lead->days_aged" />
                <span class="admin-status-pill">{{ str_replace('_', ' ', $lead->status) }}</span>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#statusModal">Follow Up</button>
                <a href="{{ route('telecaller.leads') }}" class="btn btn-sm btn-light">Back</a>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Mobile Number</div>
                <div class="admin-info-value small">{{ $lead->phone ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Email ID</div>
                <div class="admin-info-value small">{{ $lead->email ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Service</div>
                <div class="admin-info-value small">{{ $lead->service_name ?? $lead->service?->name ?? '-' }}</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Status</div>
                <div class="admin-info-value small">{{ str_replace('_', ' ', strtoupper($lead->status)) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Assigned By</div>
                <div class="admin-info-value small">{{ $lead->assignedBy->name ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Source</div>
                <div class="admin-info-value small">{{ $lead->source }}</div>
            </div>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
        <button type="button" class="btn btn-primary call-btn"
            data-phone="{{ $lead->phone }}"
            data-provider="{{ $provider }}"
            data-lead="{{ $lead->id }}">
            <span class="material-icons">call</span>
            <span class="call-text">Call Now</span>
        </button>
        <button class="btn btn-success" type="button" id="openWhatsappChat">
            <span class="material-icons">chat</span>
            WhatsApp
        </button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal">
            <span class="material-icons">sync_alt</span>
            Change Status
        </button>
    </div>

    {{-- WhatsApp Chat Window --}}
    <div class="admin-section-card" id="whatsappSection">
        <div class="admin-section-head">
            <div style="width:34px;height:34px;border-radius:10px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                <span class="material-icons" style="font-size:18px;color:#16a34a;">chat</span>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#0d1f3a;">WhatsApp Chat</div>
                <div style="font-size:11.5px;color:#5b6f8f;">Meta Official API</div>
            </div>
            <span class="wa-live-dot" style="margin-left:auto;"></span>
        </div>
        <div class="wa-chat-window" style="border:none;border-radius:0;box-shadow:none;">
            <div id="waChatBody" class="wa-chat-body">
                @forelse ($whatsappMessages as $msg)
                    @php
                        $outgoing = $msg->direction !== 'inbound';
                        $st       = data_get($msg->meta_data, 'meta_status', 'sent');
                    @endphp
                    <div class="wa-message {{ $outgoing ? 'wa-outgoing' : 'wa-incoming' }}"
                         data-msg-id="{{ $msg->id }}">
                        @if ($msg->media_type && $msg->media_url)
                            @php $mUrl = asset('storage/' . $msg->media_url); @endphp
                            @if ($msg->media_type === 'image')
                                <img src="{{ $mUrl }}" style="max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;cursor:pointer;"
                                     onclick="window.open(this.src,'_blank')" alt="Image">
                            @elseif ($msg->media_type === 'audio')
                                <audio controls style="width:100%;min-width:180px;margin-bottom:4px;"><source src="{{ $mUrl }}"></audio>
                            @elseif ($msg->media_type === 'video')
                                <video controls style="max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;"><source src="{{ $mUrl }}"></video>
                            @else
                                <a href="{{ $mUrl }}" target="_blank" download
                                   style="display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.07);border-radius:6px;padding:6px 10px;margin-bottom:4px;text-decoration:none;color:inherit;font-size:12px;font-weight:600;">
                                    <span class="material-icons" style="font-size:18px;color:#137fec;">description</span>
                                    {{ $msg->media_filename ?? basename($msg->media_url) }}
                                </a>
                            @endif
                        @endif
                        @if ($msg->message_body && !($msg->media_type && in_array($msg->media_type, ['image','audio','video'])))
                            <p class="mb-1">{{ $msg->message_body }}</p>
                        @endif
                        <div class="wa-message-meta">
                            <small>{{ $msg->created_at?->format('h:i A') }}</small>
                            @if ($outgoing)
                                <span class="wa-tick {{ $st === 'read' ? 'wa-tick-read' : ($st === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent') }}">{{ in_array($st, ['delivered','read']) ? '✓✓' : '✓' }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div id="waEmptyPlaceholder" class="wa-message wa-incoming">
                        <p class="mb-1">No WhatsApp messages yet for this lead.</p>
                        <small>Start the conversation below</small>
                    </div>
                @endforelse
            </div>

            <div class="wa-chat-footer">
                <div class="wa-template-row">
                    <button type="button" class="wa-template-btn wa-tpl-direct-btn"
                        data-template="{{ $waTemplateName }}"
                        data-params="{{ json_encode([$lead->name]) }}"
                        data-display="Hello {{ $lead->name }}, thank you for your interest in our programs!">
                        ✅ Welcome
                    </button>
                    <button type="button" class="wa-template-btn"
                        data-msg="Reminder: your follow-up is scheduled. Please confirm your preferred time.">
                        Follow-up
                    </button>
                    <button type="button" class="wa-template-btn"
                        data-msg="Please share your preferred course and we will guide you with next steps.">
                        Course Info
                    </button>
                </div>

                <div id="waLeadFilePreview" style="display:none;align-items:center;gap:8px;background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:8px;padding:6px 10px;margin-bottom:6px;font-size:12px;">
                    <span class="material-icons" id="waLeadFileIcon" style="color:#137fec;font-size:18px;">attach_file</span>
                    <span id="waLeadFileName" style="flex:1;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                    <span id="waLeadFileSize" style="color:#64748b;white-space:nowrap;"></span>
                    <button type="button" id="waLeadFileRemove" style="background:none;border:none;cursor:pointer;color:#ef4444;padding:0;display:flex;">
                        <span class="material-icons" style="font-size:16px;">close</span>
                    </button>
                </div>
                <form id="waComposerForm" class="wa-composer-form">
                    @csrf
                    <input type="file" id="waLeadFileInput" style="display:none;"
                           accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
                    <button type="button" id="waLeadAttachBtn"
                            style="background:#f1f5f9;border:1.5px solid #e2e8f0;border-radius:50%;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;"
                            title="Attach file">
                        <span class="material-icons" style="font-size:18px;color:#64748b;">attach_file</span>
                    </button>
                    <input type="text" id="waMessageInput" class="form-control"
                        placeholder="Type a WhatsApp message…" autocomplete="off">
                    <button type="submit" class="btn btn-success" id="waSendBtn">
                        <span class="material-icons" id="waSendIcon">send</span>
                        <span class="spinner-border spinner-border-sm d-none" id="waSpinner"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Note Section --}}
    <div class="admin-section-card">
        <div class="admin-section-head">
            <div style="width:34px;height:34px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                <span class="material-icons" style="font-size:18px;color:#d97706;">edit_note</span>
            </div>
            <div style="font-size:14px;font-weight:700;color:#0d1f3a;">Add Note</div>
        </div>
        <div class="admin-section-body">
            <form method="POST" action="{{ route('telecaller.leads.addNote', encrypt($lead->id)) }}">
                @csrf
                <textarea name="note" class="form-control" rows="2"
                    placeholder="Write a note about this lead…" required
                    style="border-radius:10px;font-size:13px;resize:none;"></textarea>
                <div class="d-flex justify-content-end mt-2">
                    <button class="btn btn-dark btn-sm px-4" style="border-radius:8px;font-size:13px;">
                        <span class="material-icons" style="font-size:14px;vertical-align:middle;">add</span>
                        Add Note
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Activity Timeline --}}
    @php $activities = $lead->activities()->latest()->get(); @endphp
    <div class="admin-timeline">
        <div class="admin-timeline-head">
            <h4 class="mb-0 fw-bold">Activity Timeline</h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="admin-filter-btn active" onclick="filterTimeline('all', event)">All</button>
                <button class="admin-filter-btn" onclick="filterTimeline('call', event)">
                    <span class="material-icons" style="font-size:12px;vertical-align:middle;">call</span> Calls
                </button>
                <button class="admin-filter-btn" onclick="filterTimeline('whatsapp', event)">
                    <span class="material-icons" style="font-size:12px;vertical-align:middle;">chat</span> WhatsApp
                </button>
                <button class="admin-filter-btn" onclick="filterTimeline('note', event)">
                    <span class="material-icons" style="font-size:12px;vertical-align:middle;">description</span> Notes
                </button>
                <span class="admin-timeline-count">{{ $activities->count() }} entries</span>
            </div>
        </div>
        <div class="admin-timeline-body">
            @forelse($activities as $activity)
                @php
                    $typeConfig = [
                        'call'          => ['icon' => 'call',        'bg' => '#eef2ff', 'color' => '#6366f1'],
                        'note'          => ['icon' => 'description', 'bg' => '#fef3c7', 'color' => '#d97706'],
                        'whatsapp'      => ['icon' => 'chat',        'bg' => '#dcfce7', 'color' => '#16a34a'],
                        'status_change' => ['icon' => 'sync_alt',    'bg' => '#f0fdf4', 'color' => '#10b981'],
                        'followup'      => ['icon' => 'event',       'bg' => '#fdf4ff', 'color' => '#9333ea'],
                        'assignment'    => ['icon' => 'person',      'bg' => '#edf3fb', 'color' => '#0f365c'],
                        'email'         => ['icon' => 'email',       'bg' => '#eff6ff', 'color' => '#3b82f6'],
                    ];
                    $tc = $typeConfig[$activity->type] ?? ['icon' => 'info', 'bg' => '#f1f5f9', 'color' => '#64748b'];
                @endphp
                <div class="admin-timeline-item" data-type="{{ $activity->type }}">
                    <div class="admin-timeline-icon" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                        <span class="material-icons">{{ $tc['icon'] }}</span>
                    </div>
                    <div>
                        <span class="admin-timeline-type" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                            {{ str_replace('_', ' ', $activity->type) }}
                        </span>
                        <p class="mb-1 fw-semibold">{{ $activity->description }}</p>
                        <small class="text-muted">{{ $activity->user->name ?? '-' }} | {{ $activity->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0 p-3">No activity found for this lead.</p>
            @endforelse
        </div>
    </div>

    {{-- Status Change Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('telecaller.leads.changeStatus', encrypt($lead->id)) }}">
                @csrf
                <div class="modal-content" style="border-radius:16px;border:none;">
                    <div class="modal-header" style="border-bottom:1px solid #e8eef7;padding:20px 24px 16px;">
                        <h5 class="modal-title fw-bold">Update Lead Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding:20px 24px;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Select New Status</label>
                            <select class="form-select" name="status" id="statusSelect" style="border-radius:10px;font-size:13px;">
                                <option value="contacted">Contacted</option>
                                <option value="interested">Interested</option>
                                <option value="follow_up">Follow-up Required</option>
                                <option value="not_interested">Not Interested</option>
                            </select>
                        </div>
                        <div id="followupFields" style="display:none;">
                            <div class="row g-2 mb-3">
                                <div class="col-7">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Follow-up Date</label>
                                    <input type="date" name="next_followup" class="form-control" style="border-radius:10px;" min="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-5">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Time</label>
                                    <input type="time" name="followup_time" class="form-control" style="border-radius:10px;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" style="border-radius:10px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #e8eef7;padding:16px 24px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                        <button type="button" class="btn btn-primary" id="statusConfirmBtn" style="border-radius:8px;">Update Status</button>
                        <button type="submit" class="btn btn-warning d-none" id="statusFinalSubmit" style="border-radius:8px;">Yes, Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Call Outcome Modal --}}
    <div class="modal fade" id="callOutcomeModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;border:none;">
                <div class="modal-header border-0 pb-0" style="padding:20px 24px 12px;">
                    <h5 class="modal-title fw-bold">How did the call go?</h5>
                </div>
                <div class="modal-body" style="padding:8px 24px 20px;">
                    <p class="text-muted small mb-3">Select the outcome to log it against this lead.</p>
                    <input type="hidden" id="outcomeCallLogId">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success outcome-btn" style="border-radius:10px;" data-outcome="interested">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle;">thumb_up</span> Interested
                        </button>
                        <button class="btn btn-danger outcome-btn" style="border-radius:10px;" data-outcome="not_interested">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle;">thumb_down</span> Not Interested
                        </button>
                        <button class="btn btn-warning text-dark outcome-btn" style="border-radius:10px;" data-outcome="call_back_later">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle;">schedule</span> Call Back Later
                        </button>
                        <button class="btn btn-secondary outcome-btn" style="border-radius:10px;" data-outcome="switched_off">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle;">phone_disabled</span> Switched Off / No Answer
                        </button>
                        <button class="btn btn-outline-secondary outcome-btn" style="border-radius:10px;" data-outcome="wrong_number">
                            <span class="material-icons" style="font-size:16px;vertical-align:middle;">cancel</span> Wrong Number
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="padding:0 24px 16px;">
                    <button type="button" class="btn btn-link text-muted btn-sm" data-bs-dismiss="modal">Skip</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterTimeline(type, event) {
            document.querySelectorAll('.admin-filter-btn').forEach(b => b.classList.remove('active'));
            event.target.closest('.admin-filter-btn').classList.add('active');
            document.querySelectorAll('.admin-timeline-item').forEach(item => {
                item.style.display = type === 'all' || item.dataset.type === type ? 'grid' : 'none';
            });
        }
    </script>

    <script>
        window.addEventListener('load', function () { GC.initDevice(); });

        document.addEventListener('click', async function (e) {
            var btn = e.target.closest('.call-btn');
            if (!btn) return;
            if (GC.isActive()) { GC.endCall(); return; }
            btn.disabled = true;
            btn.querySelector('.call-text').textContent = 'Connecting…';
            try {
                await GC.startCall(btn.dataset.phone, btn.dataset.lead);
            } catch (err) {
                btn.disabled = false;
                btn.querySelector('.call-text').textContent = 'Call Now';
            }
        });

        document.addEventListener('gc:callAccepted', function () {
            var btn = document.querySelector('.call-btn');
            if (!btn) return;
            btn.disabled = false;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-danger', 'active-call');
            btn.querySelector('.call-text').textContent = 'End Call';
        });

        document.addEventListener('gc:callEnded', function (e) {
            var btn = document.querySelector('.call-btn');
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('btn-danger', 'active-call');
                btn.classList.add('btn-primary');
                btn.querySelector('.call-text').textContent = 'Call Now';
            }
            var callLogId = e.detail && e.detail.callLogId;
            if (callLogId) {
                document.getElementById('outcomeCallLogId').value = callLogId;
                new bootstrap.Modal(document.getElementById('callOutcomeModal')).show();
            }
        });
    </script>

    <script>
        (function () {
            const chatBody    = document.getElementById('waChatBody');
            const msgInput    = document.getElementById('waMessageInput');
            const form        = document.getElementById('waComposerForm');
            const sendBtn     = document.getElementById('waSendBtn');
            const sendIcon    = document.getElementById('waSendIcon');
            const spinner     = document.getElementById('waSpinner');
            const openChatBtn = document.getElementById('openWhatsappChat');

            if (!chatBody || !msgInput || !form) return;

            const SAVE_URL  = @json(route('telecaller.leads.whatsapp.store', encrypt($lead->id)));
            const MEDIA_URL = @json(route('telecaller.leads.whatsapp.media', encrypt($lead->id)));
            const FETCH_URL = @json(route('telecaller.leads.whatsapp.fetch', encrypt($lead->id)));
            const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';

            let _waAudioCtx = null;
            function playWaChime() {
                try {
                    if (!_waAudioCtx) _waAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    [[1100, 0], [880, 0.18]].forEach(function(pair) {
                        const osc = _waAudioCtx.createOscillator(), gain = _waAudioCtx.createGain();
                        osc.connect(gain); gain.connect(_waAudioCtx.destination);
                        osc.type = 'sine'; osc.frequency.value = pair[0];
                        const t = _waAudioCtx.currentTime + pair[1];
                        gain.gain.setValueAtTime(0, t);
                        gain.gain.linearRampToValueAtTime(0.3, t + 0.01);
                        gain.gain.exponentialRampToValueAtTime(0.001, t + 0.22);
                        osc.start(t); osc.stop(t + 0.22);
                    });
                } catch(e) {}
            }

            function showWaToast(title, message, color) {
                const stack = document.getElementById('waToastStack');
                if (!stack) return;
                const div = document.createElement('div');
                div.style.cssText = 'background:#fff;border:1px solid #e2e8f0;border-left:4px solid ' + (color || '#25D366') + ';border-radius:10px;padding:10px 14px;box-shadow:0 4px 16px rgba(0,0,0,0.12);pointer-events:auto;animation:waSlideIn .25s ease;';
                div.innerHTML = '<div style="display:flex;align-items:flex-start;gap:8px;"><span class="material-icons" style="color:' + (color||'#25D366') + ';font-size:20px;flex-shrink:0;margin-top:1px;">chat</span><div style="flex:1;min-width:0;"><div style="font-weight:700;font-size:13px;color:#0f172a;">' + title + '</div>' + (message ? '<div style="font-size:12px;color:#64748b;margin-top:2px;">' + message + '</div>' : '') + '</div><button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:18px;line-height:1;padding:0;flex-shrink:0;">&times;</button></div>';
                stack.appendChild(div);
                setTimeout(function() { try { div.remove(); } catch(e){} }, 5000);
            }

            const fileInput     = document.getElementById('waLeadFileInput');
            const attachBtn     = document.getElementById('waLeadAttachBtn');
            const filePreviewEl = document.getElementById('waLeadFilePreview');
            const fileNameEl    = document.getElementById('waLeadFileName');
            const fileSizeEl    = document.getElementById('waLeadFileSize');
            const fileIconEl    = document.getElementById('waLeadFileIcon');
            const fileRemoveBtn = document.getElementById('waLeadFileRemove');
            let pendingFile = null;

            attachBtn?.addEventListener('click', () => fileInput.click());
            fileInput?.addEventListener('change', function () {
                const f = this.files[0]; if (!f) return;
                pendingFile = f;
                fileNameEl.textContent = f.name;
                fileSizeEl.textContent = f.size < 1048576 ? (f.size/1024).toFixed(1)+' KB' : (f.size/1048576).toFixed(1)+' MB';
                fileIconEl.textContent = f.type.startsWith('image/') ? 'image' : f.type.startsWith('video/') ? 'videocam' : f.type.startsWith('audio/') ? 'headphones' : 'description';
                filePreviewEl.style.display = 'flex';
                msgInput.placeholder = 'Add a caption (optional)…';
                msgInput.removeAttribute('required');
            });
            fileRemoveBtn?.addEventListener('click', clearFile);
            function clearFile() {
                pendingFile = null;
                if (fileInput) fileInput.value = '';
                if (filePreviewEl) filePreviewEl.style.display = 'none';
                msgInput.placeholder = 'Type a WhatsApp message…';
            }

            let lastMsgId = 0;
            chatBody.querySelectorAll('[data-msg-id]').forEach(el => {
                const id = parseInt(el.dataset.msgId) || 0;
                if (id > lastMsgId) lastMsgId = id;
            });
            chatBody.scrollTop = chatBody.scrollHeight;

            openChatBtn?.addEventListener('click', function () {
                document.getElementById('whatsappSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                msgInput.focus();
            });

            document.querySelectorAll('.wa-template-btn:not(.wa-tpl-direct-btn)').forEach(btn => {
                btn.addEventListener('click', () => { msgInput.value = btn.dataset.msg || ''; msgInput.focus(); });
            });

            const TEMPLATE_URL = @json(route('telecaller.leads.whatsapp.template', encrypt($lead->id)));
            document.querySelectorAll('.wa-tpl-direct-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const templateName = btn.dataset.template;
                    const params = JSON.parse(btn.dataset.params || '[]');
                    const displayBody = btn.dataset.display || '';
                    const origHtml = btn.innerHTML;
                    btn.disabled = true; btn.innerHTML = '⏳ Sending…';
                    try {
                        const res = await fetch(TEMPLATE_URL, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                            body: JSON.stringify({ template_name: templateName, params: params, display_body: displayBody }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) { showError(data.message || 'Template send failed'); return; }
                        document.getElementById('waEmptyPlaceholder')?.remove();
                        appendBubble({ id: data.message_id, body: data.message, direction: 'outbound', time: data.time, status: 'sent' });
                        if (data.message_id > lastMsgId) lastMsgId = data.message_id;
                    } catch (err) { showError(err.message || 'Network error.');
                    } finally { btn.disabled = false; btn.innerHTML = origHtml; }
                });
            });

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (pendingFile) { await sendMedia(); return; }
                const text = msgInput.value.trim(); if (!text) return;
                setSending(true);
                try {
                    const res = await fetch(SAVE_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ message: text }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) { showError(data.message || 'Send failed'); return; }
                    msgInput.value = '';
                    document.getElementById('waEmptyPlaceholder')?.remove();
                    appendBubble({ id: data.message_id, body: data.message || text, direction: 'outbound', time: data.time || now(), status: 'sent' });
                    lastMsgId = data.message_id || lastMsgId;
                    showWaToast('Message sent', 'WhatsApp message delivered to queue', '#137fec');
                } catch (err) { showError(err.message || 'Network error.');
                } finally { setSending(false); }
            });

            async function sendMedia() {
                if (!pendingFile) return;
                setSending(true);
                try {
                    const fd = new FormData();
                    fd.append('_token', CSRF); fd.append('file', pendingFile);
                    const caption = msgInput.value.trim(); if (caption) fd.append('caption', caption);
                    const res = await fetch(MEDIA_URL, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) { showError(data.message || 'Upload failed'); return; }
                    clearFile(); msgInput.value = '';
                    document.getElementById('waEmptyPlaceholder')?.remove();
                    appendBubble({ id: data.message_id, body: data.message, direction: 'outbound', time: data.time || now(), status: 'sent', media_type: data.media_type, media_url: data.media_url, media_filename: data.media_filename });
                    lastMsgId = data.message_id || lastMsgId;
                    showWaToast('Media sent', 'File delivered to queue', '#137fec');
                } catch (err) { showError(err.message || 'Upload failed.');
                } finally { setSending(false); }
            }

            const pollTimer = setInterval(poll, 7000);
            document.addEventListener('visibilitychange', () => { if (document.hidden) clearInterval(pollTimer); });

            window.addEventListener('wa:message.new', function(e) {
                if (!e.detail || e.detail.lead_id == {{ $lead->id }}) { poll(); }
            });

            async function poll() {
                try {
                    const res = await fetch(FETCH_URL + '?after=' + lastMsgId, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.messages?.length > 0) {
                        document.getElementById('waEmptyPlaceholder')?.remove();
                        let newInbound = 0;
                        data.messages.forEach(m => {
                            if (m.id > lastMsgId) {
                                appendBubble({ id: m.id, body: m.body, direction: m.direction, time: m.time, status: m.status || 'sent', media_type: m.media_type, media_url: m.media_url, media_filename: m.media_filename });
                                lastMsgId = m.id;
                                if (m.direction === 'inbound') newInbound++;
                            }
                        });
                        if (newInbound > 0) { playWaChime(); showWaToast('New WhatsApp message', newInbound > 1 ? newInbound + ' new messages' : 'New message received', '#25D366'); }
                    }
                    if (data.statuses) {
                        Object.entries(data.statuses).forEach(([id, status]) => {
                            const tick = chatBody.querySelector('[data-msg-id="' + id + '"] .wa-tick');
                            if (tick) tick.className = 'wa-tick ' + (status === 'read' ? 'wa-tick-read' : status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent');
                        });
                    }
                } catch (_) {}
            }

            function appendBubble({ id, body, direction, time, status, media_type, media_url, media_filename }) {
                const outgoing = direction !== 'inbound';
                const el = document.createElement('div');
                el.className = 'wa-message ' + (outgoing ? 'wa-outgoing' : 'wa-incoming');
                el.dataset.msgId = id || '';
                if (media_type && media_url) {
                    if (media_type === 'image') {
                        const img = document.createElement('img');
                        img.src = media_url; img.style.cssText = 'max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;cursor:pointer;';
                        img.onclick = () => window.open(img.src, '_blank'); el.appendChild(img);
                    } else if (media_type === 'audio') {
                        const audio = document.createElement('audio'); audio.controls = true;
                        audio.style.cssText = 'width:100%;min-width:180px;margin-bottom:4px;';
                        audio.innerHTML = `<source src="${media_url}">`; el.appendChild(audio);
                    } else if (media_type === 'video') {
                        const video = document.createElement('video'); video.controls = true;
                        video.style.cssText = 'max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;';
                        video.innerHTML = `<source src="${media_url}">`; el.appendChild(video);
                    } else {
                        const a = document.createElement('a'); a.href = media_url; a.target = '_blank'; a.download = true;
                        a.style.cssText = 'display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.07);border-radius:6px;padding:6px 10px;margin-bottom:4px;text-decoration:none;color:inherit;font-size:12px;font-weight:600;';
                        a.innerHTML = `<span class="material-icons" style="font-size:18px;color:#137fec;">description</span>${escHtml(media_filename || 'File')}`;
                        el.appendChild(a);
                    }
                }
                const showText = body && !['image','audio','video'].includes(media_type || '');
                if (showText) { const p = document.createElement('p'); p.className = 'mb-1'; p.textContent = body; el.appendChild(p); }
                const tickHtml = outgoing ? `<span class="wa-tick ${status === 'read' ? 'wa-tick-read' : status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent'}">${(status === 'delivered' || status === 'read') ? '✓✓' : '✓'}</span>` : '';
                const meta = document.createElement('div'); meta.className = 'wa-message-meta';
                meta.innerHTML = `<small>${time || now()}</small>${tickHtml}`; el.appendChild(meta);
                chatBody.appendChild(el); chatBody.scrollTop = chatBody.scrollHeight;
            }

            function setSending(v) { sendBtn.disabled = v; sendIcon.classList.toggle('d-none', v); spinner.classList.toggle('d-none', !v); }
            function showError(msg) {
                const div = document.createElement('div');
                div.className = 'alert alert-danger alert-dismissible mx-2 my-1 py-2 small';
                div.innerHTML = '<strong>Send failed:</strong> ' + escHtml(msg) + ' <button type="button" class="btn-close" style="font-size:11px;" onclick="this.parentElement.remove()"></button>';
                chatBody.appendChild(div); chatBody.scrollTop = chatBody.scrollHeight;
            }
            function now() { return new Date().toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' }); }
            function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        })();
    </script>

    <script>
        (function() {
            const statusSelect = document.getElementById('statusSelect');
            const confirmBtn   = document.getElementById('statusConfirmBtn');
            const finalBtn     = document.getElementById('statusFinalSubmit');

            statusSelect?.addEventListener('change', function() {
                document.getElementById('followupFields').style.display = this.value === 'follow_up' ? 'block' : 'none';
                confirmBtn?.classList.remove('d-none');
                finalBtn?.classList.add('d-none');
            });
            confirmBtn?.addEventListener('click', function() {
                const selected = statusSelect?.options[statusSelect.selectedIndex]?.text || '';
                const current  = @json(ucfirst(str_replace('_', ' ', $lead->status)));
                confirmBtn.textContent = 'Changing: ' + current + ' → ' + selected;
                confirmBtn.classList.add('d-none');
                finalBtn?.classList.remove('d-none');
            });
            document.getElementById('statusModal')?.addEventListener('hidden.bs.modal', function() {
                confirmBtn?.classList.remove('d-none');
                if (confirmBtn) confirmBtn.textContent = 'Update Status';
                finalBtn?.classList.add('d-none');
            });
        })();
    </script>

    <script>
        (function() {
            const outcomeUrl = @json(route('call.outcome'));
            const csrf = @json(csrf_token());
            document.querySelectorAll('.outcome-btn').forEach(function(btn) {
                btn.addEventListener('click', async function() {
                    const callLogId = document.getElementById('outcomeCallLogId').value;
                    const outcome = this.dataset.outcome;
                    if (!callLogId) return;
                    try {
                        await fetch(outcomeUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ call_log_id: callLogId, outcome: outcome })
                        });
                    } catch(e) {}
                    bootstrap.Modal.getInstance(document.getElementById('callOutcomeModal'))?.hide();
                });
            });
        })();
    </script>

@endsection
