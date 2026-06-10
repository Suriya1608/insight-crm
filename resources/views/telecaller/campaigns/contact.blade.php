@extends('layouts.app')

@section('content')
    @include('layouts.whatsappchat')

    {{-- Sub-nav --}}
    <div class="lead-profile-nav">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('telecaller.campaigns.show', encrypt($campaign->id)) }}"
                   style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;text-decoration:none;color:inherit;transition:background .15s;"
                   onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.12)'">
                    <span class="material-icons" style="font-size:20px;">arrow_back</span>
                </a>
                <div>
                    <h2 class="page-header-title mb-0">{{ $contact->name }}</h2>
                    <p class="page-header-subtitle mb-0">{{ $campaign->name }}</p>
                </div>
            </div>
            @php
                $statusBadge = ['pending'=>['bg'=>'rgba(255,255,255,.15)','text'=>'#fff'],'called'=>['bg'=>'#dbeafe','text'=>'#1d4ed8'],'interested'=>['bg'=>'#dcfce7','text'=>'#16a34a'],'not_interested'=>['bg'=>'#fee2e2','text'=>'#dc2626'],'no_answer'=>['bg'=>'#fef9c3','text'=>'#b45309'],'callback'=>['bg'=>'#ede9fe','text'=>'#7c3aed'],'converted'=>['bg'=>'#d1fae5','text'=>'#065f46']];
                $sb = $statusBadge[$contact->status] ?? ['bg'=>'rgba(255,255,255,.15)','text'=>'#fff'];
            @endphp
            <span style="background:{{ $sb['bg'] }};color:{{ $sb['text'] }};font-size:11px;font-weight:700;padding:5px 14px;border-radius:20px;">
                {{ App\Models\CampaignContact::statusLabel($contact->status) }}
            </span>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="row g-4">

            {{-- LEFT column --}}
            <div class="col-lg-4">

                {{-- Profile Card --}}
                <div class="profile-card mb-4">
                    <div class="profile-header">
                        <div class="profile-info">
                            <h1 class="profile-name">{{ $contact->name }}</h1>
                            <span class="badge bg-{{ App\Models\CampaignContact::statusColor($contact->status) }} mb-2">
                                {{ App\Models\CampaignContact::statusLabel($contact->status) }}
                            </span>
                            <p class="profile-id">{{ $campaign->name }}</p>
                        </div>
                    </div>

                    <div class="profile-details">
                        <div class="detail-item">
                            <span class="material-icons">phone</span>
                            <div class="flex-grow-1">
                                <p class="detail-label">Mobile</p>
                                <p class="detail-value">{{ $contact->phone }}</p>
                            </div>
                            <a href="tel:{{ $contact->phone }}"
                               style="width:30px;height:30px;border-radius:8px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                <span class="material-icons" style="font-size:15px;color:var(--primary-color);">call</span>
                            </a>
                        </div>

                        @if ($contact->email)
                            <div class="detail-item">
                                <span class="material-icons">mail</span>
                                <div class="flex-grow-1">
                                    <p class="detail-label">Email</p>
                                    <p class="detail-value">{{ $contact->email }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($contact->course)
                            <div class="detail-item">
                                <span class="material-icons">school</span>
                                <div class="flex-grow-1">
                                    <p class="detail-label">Course Interest</p>
                                    <p class="detail-value">{{ $contact->course }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($contact->city)
                            <div class="detail-item">
                                <span class="material-icons">location_on</span>
                                <div class="flex-grow-1">
                                    <p class="detail-label">City</p>
                                    <p class="detail-value">{{ $contact->city }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="detail-item">
                            <span class="material-icons">call</span>
                            <div class="flex-grow-1">
                                <p class="detail-label">Total Calls Made</p>
                                <p class="detail-value">{{ $contact->call_count }}</p>
                            </div>
                        </div>

                        @if ($contact->next_followup)
                            <div class="detail-item" style="background:var(--primary-light);border-radius:10px;margin:-4px;">
                                <span class="material-icons" style="color:var(--primary-color);">event</span>
                                <div class="flex-grow-1">
                                    <p class="detail-label">Next Follow-up</p>
                                    <p class="detail-value" style="color:var(--primary-color);font-weight:700;">
                                        {{ $contact->next_followup->format('d M Y') }}
                                        @if ($contact->followup_time)
                                            &nbsp;{{ date('h:i A', strtotime($contact->followup_time)) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="sl-actions-panel mb-4">
                    <button type="button" class="sl-action-btn sl-action-call call-btn"
                        data-phone="{{ $contact->phone }}"
                        data-provider="{{ $provider }}"
                        data-contact-id="{{ encrypt($contact->id) }}"
                        data-campaign-id="{{ encrypt($campaign->id) }}">
                        <span class="material-icons">call</span>
                        <span class="call-text">Call Now</span>
                    </button>
                    <button class="sl-action-btn sl-action-wa" type="button" id="openWhatsappChat">
                        <span class="material-icons">chat</span>
                        WhatsApp
                    </button>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <button class="sl-action-btn sl-action-status" data-bs-toggle="modal" data-bs-target="#statusModal">
                            <span class="material-icons">sync_alt</span>
                            Change Status
                        </button>
                        <button class="sl-action-btn sl-action-meeting" data-bs-toggle="modal" data-bs-target="#meetingModal">
                            <span class="material-icons">event</span>
                            Meeting
                        </button>
                    </div>
                </div>

                {{-- Schedule Follow-up --}}
                <div class="chart-card mb-4" style="border-radius:16px;">
                    <div class="chart-header mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:9px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;">
                                <span class="material-icons" style="font-size:17px;color:var(--primary-color);">event</span>
                            </div>
                            <h3 class="mb-0">Schedule Follow-Up</h3>
                        </div>
                    </div>
                    <form action="{{ route('telecaller.campaigns.contact.followup', [encrypt($campaign->id), encrypt($contact->id)]) }}"
                          method="POST">
                        @csrf
                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <label class="form-label small fw-semibold mb-1">Date</label>
                                <input type="date" name="followup_date" class="form-control form-control-sm" style="border-radius:9px;"
                                       value="{{ $contact->next_followup?->format('Y-m-d') }}"
                                       min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-semibold mb-1">Time</label>
                                <input type="time" name="followup_time" class="form-control form-control-sm" style="border-radius:9px;"
                                       value="{{ $contact->followup_time }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Update Status</label>
                            <select name="status" class="form-select form-select-sm" style="border-radius:9px;">
                                <option value="">— Keep current —</option>
                                @foreach (['contacted','interested','not_interested','converted','follow_up','lost'] as $s)
                                    <option value="{{ $s }}" {{ $contact->status === $s ? 'selected' : '' }}>
                                        {{ \App\Models\CampaignContact::statusLabel($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">Notes (optional)</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" style="border-radius:9px;"
                                      placeholder="Add follow-up notes..."></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm w-100" style="border-radius:10px;font-weight:700;padding:9px;">
                            <span class="material-icons me-1" style="font-size:15px;">event_available</span>
                            Save Follow-up
                        </button>
                    </form>
                </div>
            </div>

            {{-- RIGHT column --}}
            <div class="col-lg-8">

                {{-- WhatsApp Chat Window --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
                    <div class="card-body p-0">
                        <div class="wa-chat-window">
                            <div class="wa-chat-header">
                                <div class="wa-user-block">
                                    <div class="wa-avatar">{{ strtoupper(substr($contact->name, 0, 1)) }}</div>
                                    <div>
                                        <h6 class="mb-0">{{ $contact->name }}</h6>
                                        <small>Meta WhatsApp</small>
                                    </div>
                                </div>
                                <span class="wa-live-dot"></span>
                            </div>

                            <div id="waChatBody" class="wa-chat-body">
                                @forelse ($contactMessages as $msg)
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
                                                    <span class="material-icons" style="font-size:18px;color:var(--primary-color);">description</span>
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
                                        <p class="mb-1">No WhatsApp messages yet for this contact.</p>
                                        <small>Start the conversation below.</small>
                                    </div>
                                @endforelse
                            </div>

                            <div class="wa-chat-footer">
                                <div class="wa-template-row">
                                    <button type="button" class="wa-template-btn"
                                        data-msg="Hello {{ $contact->name }}, thanks for your interest. Can we connect now?">
                                        Intro
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
                                    <span class="material-icons" id="waLeadFileIcon" style="color:var(--primary-color);font-size:18px;">attach_file</span>
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
                                           style="border-radius:10px;"
                                           placeholder="Type a WhatsApp message..." autocomplete="off">
                                    <button type="submit" class="btn btn-success" id="waSendBtn"
                                            style="border-radius:10px;padding:0 16px;min-width:44px;">
                                        <span class="material-icons" id="waSendIcon">send</span>
                                        <span class="spinner-border spinner-border-sm d-none" id="waSpinner"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Add Note --}}
                <div style="background:#fff;border-radius:16px;border:1.5px solid var(--border-color);padding:20px;margin-bottom:20px;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width:32px;height:32px;border-radius:9px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                            <span class="material-icons" style="font-size:17px;color:var(--text-muted);">sticky_note_2</span>
                        </div>
                        <h6 class="mb-0 fw-bold" style="color:var(--text-dark);">Add Note</h6>
                    </div>
                    <form action="{{ route('telecaller.campaigns.contact.note', [encrypt($campaign->id), encrypt($contact->id)]) }}"
                          method="POST">
                        @csrf
                        <textarea name="note" class="form-control" rows="2" style="border-radius:10px;resize:none;"
                                  placeholder="Write a note about this contact..." required></textarea>
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-dark btn-sm d-flex align-items-center gap-1" style="border-radius:9px;font-weight:700;padding:8px 18px;">
                                <span class="material-icons" style="font-size:15px;">save</span>
                                Save Note
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Activity Timeline --}}
                <div class="timeline-card" style="border-radius:16px;">
                    <div class="timeline-header">
                        <h2>Activity Timeline</h2>
                        <div class="timeline-filters">
                            <button class="filter-btn active" onclick="filterTimeline('all', event)">All</button>
                            <button class="filter-btn" onclick="filterTimeline('call', event)">Calls</button>
                            <button class="filter-btn" onclick="filterTimeline('whatsapp', event)">WhatsApp</button>
                            <button class="filter-btn" onclick="filterTimeline('note', event)">Notes</button>
                            <button class="filter-btn" onclick="filterTimeline('meeting', event)">Meetings</button>
                        </div>
                    </div>

                    <div class="timeline-content">
                        @forelse ($activities as $activity)
                            <div class="timeline-item" data-type="{{ $activity->type }}">
                                <div class="timeline-icon">
                                    <span class="material-icons">
                                        @switch($activity->type)
                                            @case('call') call @break
                                            @case('whatsapp') chat @break
                                            @case('note') description @break
                                            @case('status_change') sync_alt @break
                                            @case('followup_set') event @break
                                            @case('meeting') event_available @break
                                            @default info @break
                                        @endswitch
                                    </span>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $activity->description }}</p>
                                    @if ($activity->type === 'call' && $activity->meta)
                                        @php $meta = $activity->meta; @endphp
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @if (!empty($meta['outcome']))
                                                <span class="badge bg-light text-dark border">Outcome: {{ ucfirst($meta['outcome']) }}</span>
                                            @endif
                                            @if (!empty($meta['duration']))
                                                <span class="badge bg-light text-dark border">Duration: {{ $meta['duration'] }}s</span>
                                            @endif
                                        </div>
                                    @endif
                                    <small>
                                        {{ $activity->createdBy->name ?? '-' }}
                                        |
                                        {{ $activity->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div style="width:56px;height:56px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                    <span class="material-icons" style="font-size:28px;color:#cbd5e1;">timeline</span>
                                </div>
                                <p class="fw-semibold mb-1" style="color:var(--text-dark);">No activity yet</p>
                                <p class="text-muted small">Make your first call to start logging activity.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Change Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('telecaller.campaigns.contact.status', [encrypt($campaign->id), encrypt($contact->id)]) }}">
                @csrf @method('PATCH')
                <div class="modal-content" style="border-radius:16px;border:none;">
                    <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:20px 24px 16px;">
                        <h5 class="modal-title fw-bold" style="font-size:15px;">Update Contact Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding:20px 24px;">
                        <label class="form-label fw-semibold small mb-2">Select new status</label>
                        <select class="form-select" name="status" id="statusSelect" style="border-radius:10px;">
                            @foreach (['new','assigned','contacted','interested','not_interested','converted','follow_up','lost'] as $s)
                                <option value="{{ $s }}" {{ $contact->status === $s ? 'selected' : '' }}>
                                    {{ \App\Models\CampaignContact::statusLabel($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:16px 24px;">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" style="border-radius:9px;">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" id="statusConfirmBtn" style="border-radius:9px;font-weight:700;">Update Status</button>
                        <button type="submit" class="btn btn-warning btn-sm d-none" id="statusFinalSubmit" style="border-radius:9px;font-weight:700;">Yes, Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Meeting Modal --}}
    <div class="modal fade" id="meetingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('telecaller.campaigns.contact.meeting', [encrypt($campaign->id), encrypt($contact->id)]) }}">
                @csrf
                <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
                    <div class="modal-header" style="background:linear-gradient(135deg,#1e3a6e,#0f172a);border-radius:16px 16px 0 0;border:none;padding:18px 24px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-icons" style="color:rgba(255,255,255,.8);font-size:20px;">event</span>
                            <h5 class="modal-title mb-0" style="color:#fff;font-weight:700;">Schedule Meeting</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding:24px;">
                        <div class="row g-3 mb-3">
                            <div class="col-7">
                                <label class="form-label fw-semibold small">Date <span class="text-danger">*</span></label>
                                <input type="date" name="meeting_date" class="form-control" style="border-radius:8px;"
                                    min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label fw-semibold small">Time</label>
                                <input type="time" name="meeting_time" class="form-control" style="border-radius:8px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Meeting Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 flex-wrap">
                                <label class="meeting-type-opt">
                                    <input type="radio" name="meeting_type" value="online" required>
                                    <span><span class="material-icons">videocam</span> Online</span>
                                </label>
                                <label class="meeting-type-opt">
                                    <input type="radio" name="meeting_type" value="in_person">
                                    <span><span class="material-icons">people</span> In-Person</span>
                                </label>
                                <label class="meeting-type-opt">
                                    <input type="radio" name="meeting_type" value="phone_call">
                                    <span><span class="material-icons">phone_in_talk</span> Phone Call</span>
                                </label>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold small">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="2" style="border-radius:8px;"
                                placeholder="Meeting agenda or additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:16px 24px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                        <button type="submit" class="btn" style="background:linear-gradient(135deg,#1e3a6e,#0f172a);color:#fff;border-radius:8px;font-weight:600;border:none;">
                            <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;">event_available</span>
                            Schedule Meeting
                        </button>
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
                    <div>
                        <h5 class="modal-title fw-bold" style="font-size:15px;">How did the call go?</h5>
                        <p class="text-muted small mb-0">Select an outcome to log it in the activity timeline.</p>
                    </div>
                </div>
                <div class="modal-body" style="padding:16px 24px;">
                    <div class="d-grid gap-2">
                        <button class="btn outcome-btn d-flex align-items-center gap-2" data-outcome="interested"
                                style="border-radius:10px;background:#dcfce7;color:#16a34a;font-weight:700;border:none;padding:11px 16px;text-align:left;">
                            <span class="material-icons" style="font-size:18px;">thumb_up</span> Interested
                        </button>
                        <button class="btn outcome-btn d-flex align-items-center gap-2" data-outcome="not_interested"
                                style="border-radius:10px;background:#fee2e2;color:#dc2626;font-weight:700;border:none;padding:11px 16px;text-align:left;">
                            <span class="material-icons" style="font-size:18px;">thumb_down</span> Not Interested
                        </button>
                        <button class="btn outcome-btn d-flex align-items-center gap-2" data-outcome="callback"
                                style="border-radius:10px;background:#fef9c3;color:#b45309;font-weight:700;border:none;padding:11px 16px;text-align:left;">
                            <span class="material-icons" style="font-size:18px;">callback</span> Call Back Later
                        </button>
                        <button class="btn outcome-btn d-flex align-items-center gap-2" data-outcome="no_answer"
                                style="border-radius:10px;background:#f1f5f9;color:#64748b;font-weight:700;border:none;padding:11px 16px;text-align:left;">
                            <span class="material-icons" style="font-size:18px;">phone_missed</span> Switched Off / No Answer
                        </button>
                        <button class="btn outcome-btn d-flex align-items-center gap-2" data-outcome="called"
                                style="border-radius:10px;background:#f8fafc;color:#475569;font-weight:700;border:1.5px solid #e2e8f0;padding:11px 16px;text-align:left;">
                            <span class="material-icons" style="font-size:18px;">phone_callback</span> Other / Just Called
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="padding:0 24px 20px;">
                    <button type="button" class="btn btn-link text-muted btn-sm w-100" data-bs-dismiss="modal">Skip for now</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .sl-actions-panel { display:flex; gap:8px; flex-direction:column; }
        .sl-action-btn {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:12px 16px; border-radius:12px; font-size:14px; font-weight:700;
            border:none; cursor:pointer; transition:all .18s; width:100%;
        }
        .sl-action-btn:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(0,0,0,.14); }
        .sl-action-call    { background:var(--grad-primary); color:#fff; }
        .sl-action-end     { background:var(--grad-danger);  color:#fff; }
        .sl-action-wa      { background:var(--grad-success); color:#fff; }
        .sl-action-status  { background:#f1f5f9; color:var(--text-dark); border:1px solid var(--border-color) !important; }
        .sl-action-status:hover { background:var(--text-dark); color:#fff; }
        .sl-action-meeting { background:linear-gradient(135deg,#1e3a6e,#0f172a); color:#fff; }
        .sl-action-meeting:hover { opacity:.92; }
        .meeting-type-opt input { display:none; }
        .meeting-type-opt span {
            display:inline-flex; align-items:center; gap:5px;
            padding:7px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;
            border:1.5px solid #e2e8f0; color:#475569; transition:all .15s;
        }
        .meeting-type-opt span .material-icons { font-size:15px; }
        .meeting-type-opt input:checked + span { background:linear-gradient(135deg,#1e3a6e,#0f172a); color:#fff; border-color:transparent; }
    </style>

    <script>
        function filterTimeline(type, event) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.timeline-item').forEach(item => {
                item.style.display = type === 'all' || item.dataset.type === type ? 'flex' : 'none';
            });
        }

        window.addEventListener('load', function () {
            GC.initDevice();
        });

        document.addEventListener('click', async function (e) {
            var btn = e.target.closest('.call-btn');
            if (!btn) return;

            if (GC.isActive()) {
                GC.endCall();
                return;
            }

            btn.disabled = true;
            btn.querySelector('.call-text').textContent = 'Connecting...';

            try {
                await GC.startCall(btn.dataset.phone, null);
            } catch (err) {
                btn.disabled = false;
                btn.querySelector('.call-text').textContent = 'Call Now';
            }
        });

        document.addEventListener('gc:callAccepted', function () {
            var btn = document.querySelector('.call-btn');
            if (!btn) return;
            btn.disabled = false;
            btn.classList.remove('sl-action-call');
            btn.classList.add('sl-action-end');
            btn.querySelector('.call-text').textContent = 'End Call';
        });

        document.addEventListener('gc:callEnded', function () {
            var btn = document.querySelector('.call-btn');
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('sl-action-end');
                btn.classList.add('sl-action-call');
                btn.querySelector('.call-text').textContent = 'Call Now';
            }
            var modal = new bootstrap.Modal(document.getElementById('callOutcomeModal'));
            modal.show();
        });

        (function () {
            const logUrl = '{{ route('telecaller.campaigns.contact.call', [encrypt($campaign->id), encrypt($contact->id)]) }}';
            const csrf   = document.querySelector('meta[name="csrf-token"]')?.content || '';

            document.querySelectorAll('.outcome-btn').forEach(function (btn) {
                btn.addEventListener('click', async function () {
                    const outcome = this.dataset.outcome;
                    try {
                        await fetch(logUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ outcome: outcome }),
                        });
                    } catch (e) {}
                    bootstrap.Modal.getInstance(document.getElementById('callOutcomeModal'))?.hide();
                    location.reload();
                });
            });
        })();

        (function () {
            var statusSelect = document.getElementById('statusSelect');
            var confirmBtn   = document.getElementById('statusConfirmBtn');
            var finalBtn     = document.getElementById('statusFinalSubmit');
            var currentStatus = @json(ucfirst(str_replace('_', ' ', $contact->status)));

            confirmBtn?.addEventListener('click', function () {
                var selected = statusSelect?.options[statusSelect.selectedIndex]?.text || '';
                confirmBtn.textContent = currentStatus + ' → ' + selected;
                confirmBtn.classList.add('d-none');
                finalBtn?.classList.remove('d-none');
            });

            document.getElementById('statusModal')?.addEventListener('hidden.bs.modal', function () {
                confirmBtn?.classList.remove('d-none');
                confirmBtn && (confirmBtn.textContent = 'Update Status');
                finalBtn?.classList.add('d-none');
            });
        })();
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

            const SAVE_URL  = @json(route('telecaller.campaigns.contact.whatsapp.store', [encrypt($campaign->id), encrypt($contact->id)]));
            const MEDIA_URL = @json(route('telecaller.campaigns.contact.whatsapp.media', [encrypt($campaign->id), encrypt($contact->id)]));
            const FETCH_URL = @json(route('telecaller.campaigns.contact.whatsapp.fetch', [encrypt($campaign->id), encrypt($contact->id)]));
            const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';

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
                const id = parseInt(el.dataset.msgId) || 0;
                if (id > lastMsgId) lastMsgId = id;
            });

            chatBody.scrollTop = chatBody.scrollHeight;

            openChatBtn?.addEventListener('click', function () {
                chatBody.scrollIntoView({ behavior: 'smooth', block: 'start' });
                msgInput.focus();
            });

            document.querySelectorAll('.wa-template-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    msgInput.value = btn.dataset.msg || '';
                    msgInput.focus();
                });
            });

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (pendingFile) { await sendMedia(); return; }

                const text = msgInput.value.trim();
                if (!text) return;

                setSending(true);
                try {
                    const res  = await fetch(SAVE_URL, {
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
                } catch (err) {
                    showError(err.message || 'Network error.');
                } finally {
                    setSending(false);
                }
            });

            async function sendMedia() {
                if (!pendingFile) return;
                setSending(true);
                try {
                    const fd = new FormData();
                    fd.append('_token', CSRF);
                    fd.append('file', pendingFile);
                    const caption = msgInput.value.trim();
                    if (caption) fd.append('caption', caption);

                    const res  = await fetch(MEDIA_URL, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) { showError(data.message || 'Upload failed'); return; }

                    clearFile();
                    msgInput.value = '';
                    document.getElementById('waEmptyPlaceholder')?.remove();
                    appendBubble({
                        id: data.message_id, body: data.message, direction: 'outbound',
                        time: data.time || now(), status: 'sent',
                        media_type: data.media_type, media_url: data.media_url, media_filename: data.media_filename,
                    });
                    lastMsgId = data.message_id || lastMsgId;
                } catch (err) {
                    showError(err.message || 'Upload failed.');
                } finally {
                    setSending(false);
                }
            }

            const pollTimer = setInterval(poll, 7000);
            document.addEventListener('visibilitychange', () => { if (document.hidden) clearInterval(pollTimer); });

            async function poll() {
                try {
                    const res  = await fetch(FETCH_URL + '?after=' + lastMsgId, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.messages?.length > 0) {
                        document.getElementById('waEmptyPlaceholder')?.remove();
                        data.messages.forEach(m => {
                            if (m.id > lastMsgId) {
                                appendBubble({ id: m.id, body: m.body, direction: m.direction, time: m.time, status: m.status || 'sent',
                                               media_type: m.media_type, media_url: m.media_url, media_filename: m.media_filename });
                                lastMsgId = m.id;
                            }
                        });
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
                        img.src = media_url;
                        img.style.cssText = 'max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;cursor:pointer;';
                        img.onclick = () => window.open(img.src, '_blank');
                        el.appendChild(img);
                    } else if (media_type === 'audio') {
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.style.cssText = 'width:100%;min-width:180px;margin-bottom:4px;';
                        audio.innerHTML = `<source src="${media_url}">`;
                        el.appendChild(audio);
                    } else if (media_type === 'video') {
                        const video = document.createElement('video');
                        video.controls = true;
                        video.style.cssText = 'max-width:200px;max-height:160px;border-radius:6px;display:block;margin-bottom:4px;';
                        video.innerHTML = `<source src="${media_url}">`;
                        el.appendChild(video);
                    } else {
                        const a = document.createElement('a');
                        a.href = media_url; a.target = '_blank'; a.download = true;
                        a.style.cssText = 'display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.07);border-radius:6px;padding:6px 10px;margin-bottom:4px;text-decoration:none;color:inherit;font-size:12px;font-weight:600;';
                        a.innerHTML = `<span class="material-icons" style="font-size:18px;color:var(--primary-color);">description</span>${escHtml(media_filename || 'File')}`;
                        el.appendChild(a);
                    }
                }

                const showText = body && !['image','audio','video'].includes(media_type || '');
                if (showText) {
                    const p = document.createElement('p');
                    p.className = 'mb-1';
                    p.textContent = body;
                    el.appendChild(p);
                }

                const tickHtml = outgoing
                    ? `<span class="wa-tick ${status === 'read' ? 'wa-tick-read' : status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent'}">${(status === 'delivered' || status === 'read') ? '✓✓' : '✓'}</span>`
                    : '';
                const meta = document.createElement('div');
                meta.className = 'wa-message-meta';
                meta.innerHTML = `<small>${time || now()}</small>${tickHtml}`;
                el.appendChild(meta);

                chatBody.appendChild(el);
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            function setSending(v) {
                sendBtn.disabled = v;
                sendIcon.classList.toggle('d-none', v);
                spinner.classList.toggle('d-none', !v);
            }

            function showError(msg) {
                const div = document.createElement('div');
                div.className = 'alert alert-danger alert-dismissible mx-2 my-1 py-2 small';
                div.innerHTML = '<strong>Send failed:</strong> ' + escHtml(msg) +
                    ' <button type="button" class="btn-close" style="font-size:11px;" onclick="this.parentElement.remove()"></button>';
                chatBody.appendChild(div);
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            function now() { return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }
            function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        })();
    </script>
@endsection
