@extends('layouts.manager.app')
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
                @if($lead->is_active)
                    <span class="badge rounded-pill" style="background:rgba(220,252,231,0.9);color:#16a34a;font-size:11px;font-weight:600;">ACTIVE</span>
                @else
                    <span class="badge rounded-pill" style="background:rgba(254,226,226,0.9);color:#dc2626;font-size:11px;font-weight:600;">INACTIVE</span>
                @endif
                <span class="admin-status-pill">{{ str_replace('_', ' ', $lead->status) }}</span>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#statusModal">Follow Up</button>
                <a href="{{ route('manager.leads') }}" class="btn btn-sm btn-light">Back</a>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Mobile Number</div>
                <div class="admin-info-value small d-flex justify-content-between align-items-center">
                    <span>{{ $lead->phone ?: '-' }}</span>
                    <button type="button" class="btn btn-link p-0 ms-2" style="color:#6366f1;"
                            data-bs-toggle="modal" data-bs-target="#editContactModal" title="Edit contact">
                        <span class="material-icons" style="font-size:17px;">edit</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Email ID</div>
                <div class="admin-info-value small d-flex justify-content-between align-items-center">
                    <span>{{ $lead->email ?: '-' }}</span>
                    <button type="button" class="btn btn-link p-0 ms-2" style="color:#6366f1;"
                            data-bs-toggle="modal" data-bs-target="#editContactModal" title="Edit contact">
                        <span class="material-icons" style="font-size:17px;">edit</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-info-card">
                <div class="admin-info-label">Service</div>
                <div class="admin-info-value small">{{ $lead->service_name ?? $lead->service?->name ?? '-' }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="admin-info-card">
                <div class="admin-info-label">Status</div>
                <div class="admin-info-value small">{{ str_replace('_', ' ', strtoupper($lead->status)) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-info-card">
                <div class="admin-info-label">Manager</div>
                <div class="admin-info-value small">{{ $lead->assignedBy->name ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-info-card">
                <div class="admin-info-label">Telecaller</div>
                <div class="admin-info-value small mb-2">{{ $lead->assignedUser->name ?? '-' }}</div>
                <form method="POST" action="{{ route('manager.assign', encrypt($lead->id)) }}">
                    @csrf
                    <select name="assigned_to" class="form-select form-select-sm mb-2" required>
                        <option value="">Select Telecaller</option>
                        @foreach ($telecallers as $tele)
                            <option value="{{ $tele->id }}"
                                {{ $lead->assigned_to == $tele->id ? 'selected disabled' : '' }}>
                                {{ $tele->name }}
                                {{ $lead->assigned_to == $tele->id ? '(Current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary w-100">
                        {{ $lead->assignedUser ? 'Reassign' : 'Assign' }}
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-info-card">
                <div class="admin-info-label">Source</div>
                <div class="admin-info-value small">{{ $lead->source }}</div>
            </div>
        </div>
    </div>

    {{-- Meta Ad Tracking (conditional) --}}
    @if($lead->fbclid || $lead->utm_campaign || $lead->utm_medium || $lead->utm_content || $lead->utm_term || $lead->meta_ad_id || $lead->meta_adset_id || $lead->meta_campaign_id || $lead->meta_form_id)
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div style="background:#fff3e0;border-left:4px solid #f57c00;border-radius:6px;padding:10px 16px;">
                <span class="material-icons" style="font-size:16px;color:#f57c00;vertical-align:-3px;">ads_click</span>
                <strong style="font-size:13px;color:#f57c00;margin-left:4px;">Meta Ad Tracking</strong>
            </div>
        </div>
        @if($lead->meta_campaign_id)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">Campaign ID</div><div class="admin-info-value small" style="font-family:monospace;">{{ $lead->meta_campaign_id }}</div></div></div>
        @endif
        @if($lead->meta_adset_id)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">Ad Set ID</div><div class="admin-info-value small" style="font-family:monospace;">{{ $lead->meta_adset_id }}</div></div></div>
        @endif
        @if($lead->meta_ad_id)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">Ad ID</div><div class="admin-info-value small" style="font-family:monospace;">{{ $lead->meta_ad_id }}</div></div></div>
        @endif
        @if($lead->meta_form_id)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">Lead Form ID</div><div class="admin-info-value small" style="font-family:monospace;">{{ $lead->meta_form_id }}</div></div></div>
        @endif
        @if($lead->utm_campaign)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">UTM Campaign</div><div class="admin-info-value small">{{ $lead->utm_campaign }}</div></div></div>
        @endif
        @if($lead->utm_medium)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">UTM Medium</div><div class="admin-info-value small">{{ $lead->utm_medium }}</div></div></div>
        @endif
        @if($lead->utm_content)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">UTM Content</div><div class="admin-info-value small">{{ $lead->utm_content }}</div></div></div>
        @endif
        @if($lead->utm_term)
        <div class="col-md-3"><div class="admin-info-card"><div class="admin-info-label">UTM Term</div><div class="admin-info-value small">{{ $lead->utm_term }}</div></div></div>
        @endif
        @if($lead->fbclid)
        <div class="col-md-6"><div class="admin-info-card"><div class="admin-info-label">Facebook Click ID (fbclid)</div><div class="admin-info-value small" style="font-family:monospace;word-break:break-all;">{{ $lead->fbclid }}</div></div></div>
        @endif
    </div>
    @endif

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
        <form method="POST" action="{{ route('manager.leads.toggleActive', encrypt($lead->id)) }}"
              onsubmit="return confirm('{{ $lead->is_active ? 'Mark this lead as Inactive?' : 'Mark this lead as Active?' }}')">
            @csrf
            <button type="submit" class="btn {{ $lead->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                <span class="material-icons">{{ $lead->is_active ? 'toggle_off' : 'toggle_on' }}</span>
                {{ $lead->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
    </div>

    {{-- WhatsApp Chat Window --}}
    <div class="admin-section-card">
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
                        $isOut   = $msg->direction !== 'inbound';
                        $status  = data_get($msg->meta_data, 'meta_status', 'sent');
                        $isDbl   = in_array($status, ['delivered', 'read']);
                        $tickCls = $status === 'read' ? 'wa-tick-read' : ($status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent');
                    @endphp
                    <div class="wa-message {{ $isOut ? 'wa-outgoing' : 'wa-incoming' }}"
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
                            @if ($isOut)
                                <span class="wa-tick {{ $tickCls }}">{{ $isDbl ? '✓✓' : '✓' }}</span>
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
                        placeholder="Type a WhatsApp message..." autocomplete="off">
                    <button type="submit" class="btn btn-success" id="waSendBtn">
                        <span class="material-icons">send</span>
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
            <form method="POST" action="{{ route('manager.leads.addNote', encrypt($lead->id)) }}">
                @csrf
                <textarea name="note" class="form-control" rows="2" placeholder="Write a note about this lead..." required
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
                <button class="admin-filter-btn active" onclick="filterTimeline('all')">All</button>
                <button class="admin-filter-btn" onclick="filterTimeline('call')">Calls</button>
                <button class="admin-filter-btn" onclick="filterTimeline('whatsapp')">WhatsApp</button>
                <button class="admin-filter-btn" onclick="filterTimeline('note')">Notes</button>
                <span class="admin-timeline-count">{{ $activities->count() }} entries</span>
            </div>
        </div>
        <div class="admin-timeline-body">
            @forelse($activities as $activity)
                <div class="admin-timeline-item" data-type="{{ $activity->type }}">
                    <div class="admin-timeline-icon">
                        <span class="material-icons">
                            @switch($activity->type)
                                @case('call') call @break
                                @case('note') description @break
                                @case('whatsapp') chat @break
                                @case('sms') sms @break
                                @case('status_change') sync_alt @break
                                @case('followup') event @break
                                @case('assignment') person @break
                                @default info
                            @endswitch
                        </span>
                    </div>
                    <div>
                        <span class="admin-timeline-type">{{ str_replace('_', ' ', $activity->type) }}</span>
                        <p class="mb-1 fw-semibold">{{ $activity->description }}</p>
                        <small class="text-muted">{{ $activity->user->name ?? '-' }} | {{ $activity->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0 p-3">No activity found for this lead.</p>
            @endforelse
        </div>
    </div>

    {{-- Edit Contact Modal --}}
    <div class="modal fade" id="editContactModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('manager.leads.updateContact', encrypt($lead->id)) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="material-icons me-2" style="vertical-align:-5px;color:#6366f1;">edit</span>
                            Edit Contact Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $lead->phone) }}"
                                   placeholder="e.g. 9876543210" required maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $lead->email) }}"
                                   placeholder="e.g. student@example.com" maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-icons me-1" style="font-size:16px;vertical-align:-3px;">save</span>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('manager.leads.changeStatus', encrypt($lead->id)) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Lead Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select New Status</label>
                            <select class="form-select" name="status" id="statusSelect">
                                <option value="new">New</option>
                                <option value="assigned">Assigned</option>
                                <option value="contacted">Contacted</option>
                                <option value="interested">Interested</option>
                                <option value="follow_up">Follow-up Required</option>
                                <option value="not_interested">Not Interested</option>
                                <option value="converted">Converted</option>
                            </select>
                        </div>
                        <div id="followupFields" style="display:none;">
                            <div class="row g-2 mb-3">
                                <div class="col-7">
                                    <label class="form-label fw-semibold">Follow-up Date</label>
                                    <input type="date" name="next_followup" class="form-control"
                                           min="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-5">
                                    <label class="form-label fw-semibold">Time</label>
                                    <input type="time" name="followup_time" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Call Outcome Modal --}}
    <div class="modal fade" id="callOutcomeModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">How did the call go?</h5>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">Select the outcome to log it against this lead.</p>
                    <input type="hidden" id="outcomeCallLogId">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success outcome-btn" data-outcome="interested">Interested</button>
                        <button class="btn btn-danger outcome-btn" data-outcome="not_interested">Not Interested</button>
                        <button class="btn btn-warning text-dark outcome-btn" data-outcome="call_back_later">Call Back Later</button>
                        <button class="btn btn-secondary outcome-btn" data-outcome="switched_off">Switched Off / No Answer</button>
                        <button class="btn btn-outline-secondary outcome-btn" data-outcome="wrong_number">Wrong Number</button>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted btn-sm" data-bs-dismiss="modal">Skip</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterTimeline(type) {
            document.querySelectorAll('.admin-filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.admin-timeline-item').forEach(item => {
                item.style.display = (type === 'all' || item.dataset.type === type) ? 'grid' : 'none';
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
            btn.querySelector('.call-text').textContent = 'Connecting...';
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
                var modal = new bootstrap.Modal(document.getElementById('callOutcomeModal'));
                modal.show();
            }
        });
    </script>

    <script>
        (function () {
            const chatBody = document.getElementById('waChatBody');
            const msgInput = document.getElementById('waMessageInput');
            const form     = document.getElementById('waComposerForm');
            const openBtn  = document.getElementById('openWhatsappChat');
            const sendBtn  = document.getElementById('waSendBtn');

            if (!chatBody || !msgInput || !form) return;

            const saveUrl   = @json(route('manager.leads.whatsapp.store', encrypt($lead->id)));
            const mediaUrl  = @json(route('manager.leads.whatsapp.media', encrypt($lead->id)));
            const fetchUrl  = @json(route('manager.leads.whatsapp.fetch', encrypt($lead->id)));
            const csrfToken = '{{ csrf_token() }}';

            let _waAudioCtx = null;
            function playWaChime() {
                try {
                    if (!_waAudioCtx) _waAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    [[1100, 0], [880, 0.18]].forEach(function(pair) {
                        const osc  = _waAudioCtx.createOscillator();
                        const gain = _waAudioCtx.createGain();
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
                div.innerHTML = '<div style="display:flex;align-items:flex-start;gap:8px;">' +
                    '<span class="material-icons" style="color:' + (color || '#25D366') + ';font-size:20px;flex-shrink:0;margin-top:1px;">chat</span>' +
                    '<div style="flex:1;min-width:0;">' +
                        '<div style="font-weight:700;font-size:13px;color:#0f172a;">' + title + '</div>' +
                        (message ? '<div style="font-size:12px;color:#64748b;margin-top:2px;">' + message + '</div>' : '') +
                    '</div>' +
                    '<button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:18px;line-height:1;padding:0;flex-shrink:0;">&times;</button>' +
                    '</div>';
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
                const f = this.files[0];
                if (!f) return;
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
                msgInput.placeholder = 'Type a WhatsApp message...';
            }

            let lastMsgId = 0;
            chatBody.querySelectorAll('[data-msg-id]').forEach(el => {
                const id = parseInt(el.dataset.msgId, 10);
                if (id > lastMsgId) lastMsgId = id;
            });
            chatBody.scrollTop = chatBody.scrollHeight;

            openBtn?.addEventListener('click', function () {
                chatBody.scrollIntoView({ behavior: 'smooth', block: 'start' });
                msgInput.focus();
            });

            document.querySelectorAll('.wa-template-btn:not(.wa-tpl-direct-btn)').forEach(btn => {
                btn.addEventListener('click', function () {
                    msgInput.value = btn.dataset.msg || '';
                    msgInput.focus();
                });
            });

            const templateUrl = @json(route('manager.leads.whatsapp.template', encrypt($lead->id)));
            document.querySelectorAll('.wa-tpl-direct-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const templateName = btn.dataset.template;
                    const params       = JSON.parse(btn.dataset.params || '[]');
                    const displayBody  = btn.dataset.display || '';
                    const origHtml = btn.innerHTML;
                    btn.disabled   = true;
                    btn.innerHTML  = '⏳ Sending…';
                    try {
                        const res = await fetch(templateUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify({ template_name: templateName, params: params, display_body: displayBody }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) { showWaError(data.message || 'Template send failed (' + res.status + ')'); return; }
                        appendBubble({ id: data.message_id, body: data.message, direction: 'outbound', time: data.time, status: 'sent' });
                        if (data.message_id > lastMsgId) lastMsgId = data.message_id;
                    } catch (err) {
                        showWaError(err.message || 'Network error.');
                    } finally {
                        btn.disabled  = false;
                        btn.innerHTML = origHtml;
                    }
                });
            });

            function tickCls(status) {
                return status === 'read' ? 'wa-tick-read' : status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent';
            }
            function tickChar(status) {
                return (status === 'delivered' || status === 'read') ? '✓✓' : '✓';
            }
            function updateTick(msgEl, status) {
                const tick = msgEl.querySelector('.wa-tick');
                if (!tick) return;
                tick.className = 'wa-tick ' + tickCls(status);
                tick.textContent = tickChar(status);
            }

            function buildBubble(msg) {
                const isOut = msg.direction !== 'inbound';
                const div   = document.createElement('div');
                div.className  = 'wa-message ' + (isOut ? 'wa-outgoing' : 'wa-incoming');
                div.dataset.msgId = msg.id;
                if (msg.media_type && msg.media_url) {
                    if (msg.media_type === 'image') {
                        const img = document.createElement('img');
                        img.src = msg.media_url;
                        img.style.cssText = 'max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;cursor:pointer;';
                        img.onclick = () => window.open(img.src, '_blank');
                        div.appendChild(img);
                    } else if (msg.media_type === 'audio') {
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'width:100%;min-width:180px;margin-bottom:4px;';
                        audio.innerHTML = `<source src="${msg.media_url}">`;
                        div.appendChild(audio);
                    } else if (msg.media_type === 'video') {
                        const video = document.createElement('video');
                        video.controls = true;
                        video.style.cssText = 'max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;';
                        video.innerHTML = `<source src="${msg.media_url}">`;
                        div.appendChild(video);
                    } else {
                        const a = document.createElement('a');
                        a.href = msg.media_url; a.target = '_blank'; a.download = true;
                        a.style.cssText = 'display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.07);border-radius:6px;padding:6px 10px;margin-bottom:4px;text-decoration:none;color:inherit;font-size:12px;font-weight:600;';
                        a.innerHTML = `<span class="material-icons" style="font-size:18px;color:#137fec;">description</span>${msg.media_filename || 'File'}`;
                        div.appendChild(a);
                    }
                }
                const bodyText = msg.body || '';
                const showText = bodyText && !['image','audio','video'].includes(msg.media_type || '');
                if (showText) {
                    const p = document.createElement('p');
                    p.className = 'mb-1';
                    p.textContent = bodyText;
                    div.appendChild(p);
                }
                const meta = document.createElement('div');
                meta.className = 'wa-message-meta';
                const time = document.createElement('small');
                time.textContent = msg.time;
                meta.appendChild(time);
                if (isOut) {
                    const tick = document.createElement('span');
                    const st   = msg.status || 'sent';
                    tick.className   = 'wa-tick ' + tickCls(st);
                    tick.textContent = tickChar(st);
                    meta.appendChild(tick);
                }
                div.appendChild(meta);
                return div;
            }

            function appendBubble(msg) {
                document.getElementById('waEmptyPlaceholder')?.remove();
                chatBody.appendChild(buildBubble(msg));
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            async function poll() {
                try {
                    const res = await fetch(`${fetchUrl}?after=${lastMsgId}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    let newInbound = 0;
                    (data.messages || []).forEach(msg => {
                        if (!chatBody.querySelector(`[data-msg-id="${msg.id}"]`)) {
                            appendBubble(msg);
                            if (msg.direction === 'inbound') newInbound++;
                        }
                        if (msg.id > lastMsgId) lastMsgId = msg.id;
                    });
                    if (newInbound > 0) {
                        playWaChime();
                        showWaToast('New WhatsApp message', newInbound > 1 ? newInbound + ' new messages received' : 'New message received', '#25D366');
                    }
                    Object.entries(data.statuses || {}).forEach(([id, status]) => {
                        const el = chatBody.querySelector(`[data-msg-id="${id}"]`);
                        if (el) updateTick(el, status);
                    });
                } catch (_) {}
            }

            setInterval(poll, 7000);
            window.addEventListener('wa:message.new', function(e) {
                if (!e.detail || e.detail.lead_id == {{ $lead->id }}) { poll(); }
            });

            function showWaError(msg) {
                const err = document.createElement('div');
                err.className = 'alert alert-danger alert-dismissible mx-2 my-1 py-2 small';
                err.innerHTML = '<strong>Send failed:</strong> ' + msg +
                    ' <button type="button" class="btn-close" style="font-size:11px;" onclick="this.parentElement.remove()"></button>';
                document.getElementById('waEmptyPlaceholder')?.remove();
                chatBody.appendChild(err);
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (pendingFile) { await sendMedia(); return; }
                const message = msgInput.value.trim();
                if (!message) return;
                sendBtn.disabled = true;
                try {
                    const res = await fetch(saveUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ message }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) { showWaError(data.message || 'Server error (' + res.status + ')'); return; }
                    appendBubble({ id: data.message_id, body: data.message, direction: 'outbound', time: data.time, status: 'sent' });
                    if (data.message_id > lastMsgId) lastMsgId = data.message_id;
                    msgInput.value = '';
                    showWaToast('Message sent', 'WhatsApp message delivered to queue', '#137fec');
                } catch (err) {
                    showWaError(err.message || 'Network error — check your connection.');
                } finally {
                    sendBtn.disabled = false;
                }
            });

            async function sendMedia() {
                if (!pendingFile) return;
                sendBtn.disabled = true;
                try {
                    const fd = new FormData();
                    fd.append('_token', csrfToken);
                    fd.append('file', pendingFile);
                    const caption = msgInput.value.trim();
                    if (caption) fd.append('caption', caption);
                    const res  = await fetch(mediaUrl, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) { showWaError(data.message || 'Upload failed'); return; }
                    clearFile();
                    msgInput.value = '';
                    appendBubble({ id: data.message_id, body: data.message, direction: 'outbound', time: data.time, status: 'sent', media_type: data.media_type, media_url: data.media_url, media_filename: data.media_filename });
                    if (data.message_id > lastMsgId) lastMsgId = data.message_id;
                    showWaToast('Media sent', 'File delivered to queue', '#137fec');
                } catch (err) {
                    showWaError(err.message || 'Upload failed.');
                } finally {
                    sendBtn.disabled = false;
                }
            }
        })();
    </script>

    <script>
        document.getElementById('statusSelect').addEventListener('change', function() {
            document.getElementById('followupFields').style.display = this.value === 'follow_up' ? 'block' : 'none';
        });
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
