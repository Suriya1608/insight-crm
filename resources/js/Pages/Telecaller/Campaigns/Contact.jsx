import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';

// ─── Status config ────────────────────────────────────────────────────────────
const STATUS_MAP = {
    new:            { label: 'New',            bg: '#f1f5f9', color: '#64748b' },
    assigned:       { label: 'Assigned',       bg: '#eef2ff', color: '#e05200' },
    contacted:      { label: 'Contacted',      bg: '#e0f2fe', color: '#0284c7' },
    interested:     { label: 'Interested',     bg: '#dcfce7', color: '#16a34a' },
    not_interested: { label: 'Not Interested', bg: '#fee2e2', color: '#dc2626' },
    converted:      { label: 'Converted',      bg: '#dcfce7', color: '#15803d' },
    follow_up:      { label: 'Follow-up',      bg: '#fef9c3', color: '#ca8a04' },
    lost:           { label: 'Lost',           bg: '#fef2f2', color: '#991b1b' },
};

const STATUS_OPTIONS = [
    { value: 'contacted',      label: 'Contacted',      icon: 'phone_in_talk', bg: '#0ea5e9' },
    { value: 'interested',     label: 'Interested',     icon: 'thumb_up',      bg: '#10b981' },
    { value: 'follow_up',      label: 'Follow-up',      icon: 'event_repeat',  bg: '#f59e0b' },
    { value: 'not_interested', label: 'Not Interested', icon: 'thumb_down',    bg: '#ef4444' },
    { value: 'converted',      label: 'Converted',      icon: 'check_circle',  bg: '#8b5cf6' },
];

const MISSED_STATUSES = new Set(['missed', 'no-answer', 'busy', 'failed', 'canceled']);

const ACTIVITY_ICON = {
    call:          'call',
    note:          'description',
    whatsapp:      'chat',
    status_change: 'sync_alt',
    followup_set:  'event',
};

function now12h() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// ─── StatusBadge ──────────────────────────────────────────────────────────────
function StatusBadge({ status }) {
    const s = STATUS_MAP[status] ?? { label: status, bg: '#f1f5f9', color: '#64748b' };
    return (
        <span style={{
            background: s.bg, color: s.color, fontSize: 11, fontWeight: 700,
            padding: '4px 12px', borderRadius: 99, whiteSpace: 'nowrap',
            letterSpacing: '.3px', textTransform: 'uppercase',
        }}>
            {s.label}
        </span>
    );
}

// ─── CallButton ───────────────────────────────────────────────────────────────
function CallButton({ phone, campaignContactId }) {
    const [state, setState] = useState('idle'); // idle | connecting | active

    useEffect(() => {
        function onAccepted() { setState('active'); }
        function onEnded()    { setState('idle'); }
        document.addEventListener('gc:callAccepted', onAccepted);
        document.addEventListener('gc:callEnded',    onEnded);
        return () => {
            document.removeEventListener('gc:callAccepted', onAccepted);
            document.removeEventListener('gc:callEnded',    onEnded);
        };
    }, []);

    async function handleClick() {
        if (state === 'active') { window.GC?.endCall(); return; }
        setState('connecting');
        try {
            await window.GC?.startCall(phone, null, campaignContactId ?? null);
        } catch (_) {
            setState('idle');
        }
    }

    const label = state === 'active' ? 'End Call' : state === 'connecting' ? 'Connecting…' : 'Call Now';
    const cls   = state === 'active' ? 'btn btn-danger call-btn active-call' : 'btn btn-primary call-btn';

    return (
        <button type="button" className={cls}
            data-phone={phone}
            disabled={state === 'connecting'}
            onClick={handleClick}>
            <span className="material-icons">call</span>
            <span className="call-text">{label}</span>
        </button>
    );
}

// ─── StatusPanel (inline toggle) ──────────────────────────────────────────────
function StatusPanel({ contact, courses, url, onClose, onChanged }) {
    const [selected,        setSelected]        = useState(contact.status);
    const [quota,           setQuota]           = useState(contact.quota ?? '');
    const [convertedCourse, setConvertedCourse] = useState(String(contact.converted_course_id ?? ''));
    const [followupDate,    setFollowupDate]    = useState('');
    const [followupTime,    setFollowupTime]    = useState('');
    const [remarks,         setRemarks]         = useState('');
    const [saving,          setSaving]          = useState(false);

    const needsFollowup = selected === 'follow_up';
    const needsConvert  = selected === 'converted';
    const canSubmit     = !saving && selected !== contact.status
        && !(needsConvert && (!quota || !convertedCourse));

    async function handleSubmit(e) {
        e.preventDefault();
        setSaving(true);
        try {
            const csrf    = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const payload = { status: selected };
            if (needsConvert) {
                payload.quota               = quota;
                payload.converted_course_id = convertedCourse;
            }
            if (needsFollowup) {
                payload.next_followup = followupDate;
                payload.followup_time = followupTime;
                payload.remarks       = remarks;
            }
            const res = await fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                onChanged?.(selected, { next_followup: followupDate, followup_time: followupTime });
                onClose?.();
            }
        } catch (_) {}
        setSaving(false);
    }

    return (
        <form onSubmit={handleSubmit} style={{
            background: '#fff', border: '1px solid #e2e8f0', borderRadius: 10,
            boxShadow: '0 4px 16px rgba(0,0,0,.07)', padding: '14px 16px', marginBottom: 16,
        }}>
            {/* Header */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                    <span className="material-icons" style={{ fontSize: 16, color: '#FF5C00' }}>sync_alt</span>
                    <span style={{ fontWeight: 600, fontSize: 13, color: '#1D1D1D' }}>Update Status</span>
                    <span style={{ padding: '2px 8px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: '#f1f5f9', color: '#64748b' }}>
                        Current: {STATUS_MAP[contact.status]?.label ?? contact.status}
                    </span>
                </div>
                <button type="button" onClick={onClose}
                    style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#94a3b8', lineHeight: 1, padding: 0 }}>
                    <span className="material-icons" style={{ fontSize: 18 }}>close</span>
                </button>
            </div>

            {/* Status pills */}
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 7, marginBottom: 14 }}>
                {STATUS_OPTIONS.map(s => {
                    const active = selected === s.value;
                    return (
                        <button key={s.value} type="button"
                            onClick={() => setSelected(s.value)}
                            style={{
                                display: 'inline-flex', alignItems: 'center', gap: 5,
                                padding: '6px 14px', borderRadius: 20,
                                border: `1.5px solid ${s.bg}`,
                                background: active ? s.bg : `${s.bg}18`,
                                color: active ? '#fff' : s.bg,
                                fontSize: 12, fontWeight: 600, cursor: 'pointer', transition: 'all .15s',
                            }}>
                            {active && <span className="material-icons" style={{ fontSize: 12 }}>check</span>}
                            <span className="material-icons" style={{ fontSize: 14 }}>{s.icon}</span>
                            {s.label}
                        </button>
                    );
                })}
            </div>

            {/* Converted — quota + course */}
            {needsConvert && (
                <div style={{ borderTop: '1px solid #f1f5f9', paddingTop: 12, marginBottom: 12 }}>
                    {/* Quota */}
                    <label style={{ fontSize: 11, fontWeight: 600, color: '#64748b', display: 'block', marginBottom: 6 }}>
                        <span className="material-icons" style={{ fontSize: 13, verticalAlign: 'middle', marginRight: 3 }}>how_to_reg</span>
                        Quota <span style={{ color: '#ef4444' }}>*</span>
                    </label>
                    <div style={{ display: 'flex', gap: 8, marginBottom: 12 }}>
                        {[{ value: 'management', label: 'Management' }, { value: 'counselling', label: 'Counselling' }].map(q => {
                            const active = quota === q.value;
                            return (
                                <button key={q.value} type="button"
                                    onClick={() => setQuota(q.value)}
                                    style={{
                                        flex: 1, padding: '7px 0', borderRadius: 8,
                                        fontSize: 12, fontWeight: 600, cursor: 'pointer',
                                        border: `1.5px solid ${active ? '#8b5cf6' : '#e2e8f0'}`,
                                        background: active ? '#8b5cf6' : '#f8fafc',
                                        color: active ? '#fff' : '#64748b',
                                    }}>
                                    {active && <span className="material-icons" style={{ fontSize: 12, verticalAlign: 'middle', marginRight: 3 }}>check</span>}
                                    {q.label}
                                </button>
                            );
                        })}
                    </div>
                    {/* Course */}
                    <label style={{ fontSize: 11, fontWeight: 600, color: '#64748b', display: 'block', marginBottom: 4 }}>
                        <span className="material-icons" style={{ fontSize: 13, verticalAlign: 'middle', marginRight: 3 }}>school</span>
                        Course Selected <span style={{ color: '#ef4444' }}>*</span>
                    </label>
                    <select className="form-select form-select-sm"
                        value={convertedCourse}
                        onChange={e => setConvertedCourse(e.target.value)}>
                        <option value="">— Select course —</option>
                        {courses.map(c => (
                            <option key={c.id} value={String(c.id)}>{c.name}</option>
                        ))}
                    </select>
                </div>
            )}

            {/* Follow-up fields */}
            {needsFollowup && (
                <div style={{ borderTop: '1px solid #f1f5f9', paddingTop: 12, marginBottom: 12 }}>
                    <div style={{ display: 'flex', gap: 10, marginBottom: 8 }}>
                        <div style={{ flex: 1 }}>
                            <label style={{ fontSize: 11, fontWeight: 600, color: '#64748b', display: 'block', marginBottom: 3 }}>
                                Follow-up Date
                            </label>
                            <input type="date" className="form-control form-control-sm"
                                min={new Date().toISOString().slice(0, 10)}
                                value={followupDate}
                                onChange={e => setFollowupDate(e.target.value)} />
                        </div>
                        <div style={{ width: 110 }}>
                            <label style={{ fontSize: 11, fontWeight: 600, color: '#64748b', display: 'block', marginBottom: 3 }}>
                                Time
                            </label>
                            <input type="time" className="form-control form-control-sm"
                                value={followupTime}
                                onChange={e => setFollowupTime(e.target.value)} />
                        </div>
                    </div>
                    <textarea className="form-control form-control-sm" rows={2}
                        placeholder="Remarks (optional)…"
                        value={remarks}
                        onChange={e => setRemarks(e.target.value)} />
                </div>
            )}

            {/* Footer */}
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
                <button type="button" className="btn btn-sm btn-outline-secondary" onClick={onClose}>Cancel</button>
                <button type="submit" className="btn btn-sm btn-primary" disabled={!canSubmit}>
                    {saving ? 'Saving…' : 'Apply Status'}
                </button>
            </div>
        </form>
    );
}

// ─── WaBubble ─────────────────────────────────────────────────────────────────
function WaBubble({ msg }) {
    const out = msg.direction !== 'inbound';
    const tickClass = msg.status === 'read' ? 'wa-tick-read'
        : msg.status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent';
    const tickChar = ['delivered','read'].includes(msg.status) ? '✓✓' : '✓';

    return (
        <div className={`wa-message ${out ? 'wa-outgoing' : 'wa-incoming'}`} data-msg-id={msg.id}>
            {msg.media_type && msg.media_url && (() => {
                if (msg.media_type === 'image') return (
                    <img src={msg.media_url} alt="" onClick={() => window.open(msg.media_url,'_blank')}
                        style={{ maxWidth:200, maxHeight:160, borderRadius:6, display:'block', marginBottom:4, cursor:'pointer' }} />
                );
                if (msg.media_type === 'audio') return (
                    <audio controls style={{ width:'100%', minWidth:180, marginBottom:4 }}>
                        <source src={msg.media_url} />
                    </audio>
                );
                if (msg.media_type === 'video') return (
                    <video controls style={{ maxWidth:200, maxHeight:160, borderRadius:6, display:'block', marginBottom:4 }}>
                        <source src={msg.media_url} />
                    </video>
                );
                return (
                    <a href={msg.media_url} target="_blank" rel="noreferrer" download
                        style={{ display:'flex', alignItems:'center', gap:6, background:'rgba(0,0,0,.07)',
                            borderRadius:6, padding:'6px 10px', marginBottom:4, textDecoration:'none',
                            color:'inherit', fontSize:12, fontWeight:600 }}>
                        <span className="material-icons" style={{ fontSize:18, color:'#FF5C00' }}>description</span>
                        {msg.media_filename || 'File'}
                    </a>
                );
            })()}

            {msg.body && !['image','audio','video'].includes(msg.media_type || '') && (
                <p className="mb-1">{msg.body}</p>
            )}

            <div className="wa-message-meta">
                <small>{msg.time}</small>
                {out && <span className={`wa-tick ${tickClass}`}>{tickChar}</span>}
            </div>
        </div>
    );
}

// ─── WaChat ───────────────────────────────────────────────────────────────────
function WaChat({ contactName, initialMessages, urls }) {
    const chatBodyRef = useRef(null);
    const lastIdRef   = useRef(
        initialMessages.length ? Math.max(...initialMessages.map(m => m.id)) : 0
    );
    const fileInputRef = useRef(null);

    const [messages,    setMessages]    = useState(initialMessages);
    const [text,        setText]        = useState('');
    const [pendingFile, setPendingFile] = useState(null);
    const [sending,     setSending]     = useState(false);
    const [toasts,      setToasts]      = useState([]);

    useEffect(() => {
        if (chatBodyRef.current)
            chatBodyRef.current.scrollTop = chatBodyRef.current.scrollHeight;
    }, [messages]);

    const poll = useCallback(async () => {
        try {
            const res  = await fetch(`${urls.wa_fetch}?after=${lastIdRef.current}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.messages?.length) {
                const fresh = data.messages.filter(m => m.id > lastIdRef.current);
                if (fresh.length) {
                    setMessages(prev => [...prev, ...fresh]);
                    lastIdRef.current = Math.max(...fresh.map(m => m.id));
                }
            }
            if (data.statuses) {
                setMessages(prev => prev.map(m => {
                    const s = data.statuses[m.id];
                    return s ? { ...m, status: s } : m;
                }));
            }
        } catch (_) {}
    }, [urls.wa_fetch]);

    useEffect(() => {
        const t = setInterval(poll, 7_000);
        return () => clearInterval(t);
    }, [poll]);

    function addToast(msg, color) {
        const id = Date.now();
        setToasts(prev => [...prev, { id, msg, color }]);
        setTimeout(() => setToasts(prev => prev.filter(t => t.id !== id)), 4000);
    }

    function handleFileChange(e) {
        const f = e.target.files[0];
        if (f) setPendingFile(f);
    }
    function clearFile() {
        setPendingFile(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    }

    async function handleSubmit(e) {
        e.preventDefault();
        if (pendingFile) { await sendMedia(); return; }
        const body = text.trim();
        if (!body) return;
        setSending(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(urls.wa_store, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ message: body }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { addToast(data.message || 'Send failed', '#ef4444'); return; }
            setText('');
            const newMsg = { id: data.message_id, body: data.message || body,
                direction: 'outbound', time: data.time || now12h(), status: 'sent' };
            setMessages(prev => [...prev, newMsg]);
            if (data.message_id > lastIdRef.current) lastIdRef.current = data.message_id;
            addToast('Message sent', '#FF5C00');
        } catch (err) {
            addToast(err.message || 'Network error', '#ef4444');
        } finally {
            setSending(false);
        }
    }

    async function sendMedia() {
        if (!pendingFile) return;
        setSending(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const fd   = new FormData();
            fd.append('_token', csrf);
            fd.append('file', pendingFile);
            if (text.trim()) fd.append('caption', text.trim());
            const res  = await fetch(urls.wa_media, { method: 'POST', headers: { Accept: 'application/json' }, body: fd });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { addToast(data.message || 'Upload failed', '#ef4444'); return; }
            clearFile(); setText('');
            const newMsg = { id: data.message_id, body: data.message, direction: 'outbound',
                time: data.time || now12h(), status: 'sent',
                media_type: data.media_type, media_url: data.media_url, media_filename: data.media_filename };
            setMessages(prev => [...prev, newMsg]);
            if (data.message_id > lastIdRef.current) lastIdRef.current = data.message_id;
            addToast('Media sent', '#FF5C00');
        } catch (err) {
            addToast(err.message || 'Upload failed', '#ef4444');
        } finally {
            setSending(false);
        }
    }

    const fileLabel = pendingFile
        ? (pendingFile.size < 1_048_576
            ? `${pendingFile.name} (${(pendingFile.size / 1024).toFixed(1)} KB)`
            : `${pendingFile.name} (${(pendingFile.size / 1_048_576).toFixed(1)} MB)`)
        : null;

    return (
        <div className="card border-0 shadow-sm mb-4" style={{ position: 'relative' }}>
            <div className="card-body p-0">
                <div className="wa-chat-window">
                    <div className="wa-chat-header">
                        <div className="wa-user-block">
                            <div className="wa-avatar">{contactName.charAt(0).toUpperCase()}</div>
                            <div>
                                <h6 className="mb-0">{contactName}</h6>
                                <small>Meta WhatsApp</small>
                            </div>
                        </div>
                        <span className="wa-live-dot"></span>
                    </div>

                    <div className="wa-chat-body" ref={chatBodyRef}>
                        {messages.length === 0 && (
                            <div className="wa-message wa-incoming">
                                <p className="mb-1">No WhatsApp messages yet for this contact.</p>
                                <small>Start the conversation below.</small>
                            </div>
                        )}
                        {messages.map(m => <WaBubble key={m.id} msg={m} />)}
                    </div>

                    <div className="wa-chat-footer">
                        <div className="wa-template-row">
                            <button type="button" className="wa-template-btn"
                                onClick={() => setText(`Hello ${contactName}, thanks for your interest. Can we connect now?`)}>
                                Intro
                            </button>
                            <button type="button" className="wa-template-btn"
                                onClick={() => setText('Reminder: your follow-up is scheduled. Please confirm your preferred time.')}>
                                Follow-up
                            </button>
                            <button type="button" className="wa-template-btn"
                                onClick={() => setText('Please share your preferred course and we will guide you with next steps.')}>
                                Course Info
                            </button>
                        </div>

                        {pendingFile && (
                            <div style={{ display:'flex', alignItems:'center', gap:8, background:'#f0f9ff',
                                border:'1.5px solid #bae6fd', borderRadius:8, padding:'6px 10px',
                                marginBottom:6, fontSize:12 }}>
                                <span className="material-icons" style={{ color:'#FF5C00', fontSize:18 }}>attach_file</span>
                                <span style={{ flex:1, fontWeight:600, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>
                                    {fileLabel}
                                </span>
                                <button type="button" onClick={clearFile}
                                    style={{ background:'none', border:'none', cursor:'pointer', color:'#ef4444', padding:0, display:'flex' }}>
                                    <span className="material-icons" style={{ fontSize:16 }}>close</span>
                                </button>
                            </div>
                        )}

                        <form className="wa-composer-form" onSubmit={handleSubmit}>
                            <input type="file" ref={fileInputRef} style={{ display:'none' }}
                                accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
                                onChange={handleFileChange} />
                            <button type="button" onClick={() => fileInputRef.current?.click()}
                                style={{ background:'#f1f5f9', border:'1.5px solid #e2e8f0', borderRadius:'50%',
                                    width:38, height:38, display:'flex', alignItems:'center',
                                    justifyContent:'center', cursor:'pointer', flexShrink:0 }}
                                title="Attach file">
                                <span className="material-icons" style={{ fontSize:18, color:'#64748b' }}>attach_file</span>
                            </button>
                            <input className="form-control" type="text" autoComplete="off"
                                placeholder={pendingFile ? 'Add a caption (optional)…' : 'Type a WhatsApp message...'}
                                value={text} onChange={e => setText(e.target.value)} />
                            <button type="submit" className="btn btn-success" disabled={sending}>
                                {sending
                                    ? <span className="spinner-border spinner-border-sm"></span>
                                    : <span className="material-icons">send</span>
                                }
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {toasts.length > 0 && (
                <div style={{ position:'absolute', bottom:80, right:12, zIndex:10, pointerEvents:'none', display:'flex', flexDirection:'column', gap:6 }}>
                    {toasts.map(t => (
                        <div key={t.id} style={{ background:'#fff', border:`1px solid #e2e8f0`,
                            borderLeft:`4px solid ${t.color}`, borderRadius:10,
                            padding:'8px 14px', boxShadow:'0 4px 16px rgba(0,0,0,.12)',
                            fontSize:13, fontWeight:600, color:'#1D1D1D' }}>
                            {t.msg}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

// ─── ActivityTimeline ─────────────────────────────────────────────────────────
const DIRECTION_STYLE = {
    inbound:  { background: '#dcfce7', color: '#16a34a' },
    outbound: { background: '#eff6ff', color: '#2563eb' },
};

const TL_FILTERS = [
    { key: 'all',      label: 'All' },
    { key: 'call',     label: 'Call' },
    { key: 'whatsapp', label: 'WhatsApp' },
    { key: 'note',     label: 'Note' },
    { key: 'status_change', label: 'Status' },
];

function getActivityIcon(activity) {
    if (activity.type === 'call') {
        if (MISSED_STATUSES.has(activity.call_status)) return 'call_missed';
        if (activity.direction === 'inbound') return 'call_received';
        if (activity.direction === 'outbound') return 'call_made';
    }
    return ACTIVITY_ICON[activity.type] || 'info';
}

function ActivityTimeline({ activities }) {
    const [filter, setFilter] = useState('all');
    const visible = filter === 'all' ? activities : activities.filter(a => a.type === filter);

    return (
        <div className="timeline-card">
            <div className="timeline-header">
                <h2>Activity Timeline</h2>
                <div className="timeline-filters">
                    {TL_FILTERS.map(f => (
                        <button key={f.key}
                            className={`filter-btn${filter === f.key ? ' active' : ''}`}
                            onClick={() => setFilter(f.key)}>
                            {f.label}
                        </button>
                    ))}
                </div>
            </div>
            <div className="timeline-content">
                {visible.length === 0 ? (
                    <div className="text-center py-5">
                        <span className="material-icons" style={{ fontSize:40, color:'#cbd5e1' }}>timeline</span>
                        <p className="text-muted mt-2">No activity recorded yet. Make your first call.</p>
                    </div>
                ) : visible.map(activity => (
                    <div className="timeline-item" key={activity.id} data-type={activity.type}>
                        <div className="timeline-icon">
                            <span className="material-icons"
                                style={activity.type === 'call' && MISSED_STATUSES.has(activity.call_status)
                                    ? { color: '#ef4444' } : undefined}>
                                {getActivityIcon(activity)}
                            </span>
                        </div>
                        <div className="timeline-body">
                            <p style={{ marginBottom: activity.type === 'call' && activity.direction ? 4 : 0 }}>
                                {activity.description}
                            </p>
                            {activity.type === 'call' && activity.direction && (
                                <span style={{
                                    display: 'inline-block', fontSize: 11, fontWeight: 600,
                                    padding: '1px 8px', borderRadius: 20, marginBottom: 4,
                                    textTransform: 'uppercase', letterSpacing: '.03em',
                                    ...(DIRECTION_STYLE[activity.direction] ?? {}),
                                }}>
                                    {activity.direction}
                                </span>
                            )}
                            {activity.type === 'call' && activity.meta && (
                                <div className="d-flex flex-wrap gap-1 mb-1">
                                    {activity.meta.outcome && (
                                        <span className="badge bg-light text-dark border">
                                            Outcome: {activity.meta.outcome.charAt(0).toUpperCase() + activity.meta.outcome.slice(1)}
                                        </span>
                                    )}
                                    {activity.meta.duration && (
                                        <span className="badge bg-light text-dark border">
                                            Duration: {activity.meta.duration}s
                                        </span>
                                    )}
                                </div>
                            )}
                            <small>{activity.created_by} | {activity.created_at}</small>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

// ─── ScheduleFollowup ─────────────────────────────────────────────────────────
function ScheduleFollowupForm({ contact, url, onSaved }) {
    const [form, setForm] = useState({
        followup_date: contact.next_followup ?? '',
        followup_time: contact.followup_time ?? '',
        notes: '',
    });
    const [saving, setSaving] = useState(false);
    const [saved,  setSaved]  = useState(false);

    async function handleSubmit(e) {
        e.preventDefault();
        setSaving(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify(form),
            });
            if (res.ok) {
                setSaved(true);
                setTimeout(() => setSaved(false), 3000);
                onSaved?.({ next_followup: form.followup_date, followup_time: form.followup_time });
            }
        } catch (_) {}
        setSaving(false);
    }

    return (
        <div className="chart-card mb-4" style={{ borderRadius: 16 }}>
            <div className="chart-header mb-3">
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <div style={{ width: 32, height: 32, borderRadius: 9, background: 'rgba(99,102,241,.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <span className="material-icons" style={{ fontSize: 17, color: '#FF5C00' }}>event</span>
                    </div>
                    <h3 style={{ margin: 0 }}>Schedule Follow-Up</h3>
                </div>
            </div>
            <form onSubmit={handleSubmit}>
                <div className="row g-2 mb-2">
                    <div className="col-7">
                        <label className="form-label small fw-semibold mb-1">Date</label>
                        <input type="date" className="form-control form-control-sm" style={{ borderRadius: 9 }}
                            value={form.followup_date}
                            min={new Date().toISOString().split('T')[0]}
                            onChange={e => setForm({ ...form, followup_date: e.target.value })}
                            required />
                    </div>
                    <div className="col-5">
                        <label className="form-label small fw-semibold mb-1">Time</label>
                        <input type="time" className="form-control form-control-sm" style={{ borderRadius: 9 }}
                            value={form.followup_time}
                            onChange={e => setForm({ ...form, followup_time: e.target.value })} />
                    </div>
                </div>
                <div className="mb-3">
                    <label className="form-label small fw-semibold mb-1">Notes (optional)</label>
                    <textarea className="form-control form-control-sm" rows={2} style={{ borderRadius: 9 }}
                        placeholder="Add follow-up notes..."
                        value={form.notes}
                        onChange={e => setForm({ ...form, notes: e.target.value })} />
                </div>
                <button style={{
                    width: '100%', padding: '9px 16px', borderRadius: 10, border: 'none',
                    background: saved ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#FF5C00,#e05200)',
                    color: '#fff', fontWeight: 700, fontSize: 13,
                    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
                    cursor: saving ? 'not-allowed' : 'pointer', opacity: saving ? .75 : 1,
                }} disabled={saving}>
                    <span className="material-icons" style={{ fontSize: 15 }}>{saved ? 'check_circle' : 'event_available'}</span>
                    {saving ? 'Saving…' : saved ? 'Saved!' : 'Save Follow-up'}
                </button>
            </form>
        </div>
    );
}

// ─── AddNote ─────────────────────────────────────────────────────────────────
function AddNote({ url, onAdded }) {
    const [note,   setNote]   = useState('');
    const [saving, setSaving] = useState(false);
    const [saved,  setSaved]  = useState(false);

    async function handleSubmit(e) {
        e.preventDefault();
        if (!note.trim()) return;
        setSaving(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ note }),
            });
            if (res.ok) {
                setNote('');
                setSaved(true);
                setTimeout(() => setSaved(false), 2500);
                onAdded?.();
            }
        } catch (_) {}
        setSaving(false);
    }

    return (
        <div style={{ background: '#fff', borderRadius: 16, border: '1.5px solid #e2e8f0', padding: 20, marginBottom: 20 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}>
                <div style={{ width: 32, height: 32, borderRadius: 9, background: '#f1f5f9', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <span className="material-icons" style={{ fontSize: 17, color: '#64748b' }}>sticky_note_2</span>
                </div>
                <span style={{ fontSize: 13, fontWeight: 700, color: '#1D1D1D' }}>Add Note</span>
            </div>
            <form onSubmit={handleSubmit}>
                <textarea className="form-control" rows={2}
                    style={{ borderRadius: 10, resize: 'none', fontSize: 13 }}
                    placeholder="Write a note about this contact..."
                    value={note}
                    onChange={e => setNote(e.target.value)}
                    required />
                <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 12 }}>
                    <button style={{
                        display: 'inline-flex', alignItems: 'center', gap: 6,
                        padding: '8px 18px', borderRadius: 9, border: 'none',
                        background: saved ? '#10b981' : '#1D1D1D',
                        color: '#fff', fontWeight: 700, fontSize: 12.5, cursor: saving ? 'not-allowed' : 'pointer',
                    }} disabled={saving}>
                        <span className="material-icons" style={{ fontSize: 15 }}>{saved ? 'check' : 'save'}</span>
                        {saving ? 'Saving…' : saved ? 'Saved!' : 'Save Note'}
                    </button>
                </div>
            </form>
        </div>
    );
}

// ─── CallOutcomeModal ─────────────────────────────────────────────────────────
const OUTCOMES = [
    { value: 'interested',     label: 'Interested',               icon: 'thumb_up',     bg: '#dcfce7', color: '#16a34a' },
    { value: 'not_interested', label: 'Not Interested',           icon: 'thumb_down',   bg: '#fee2e2', color: '#dc2626' },
    { value: 'callback',       label: 'Call Back Later',          icon: 'callback',     bg: '#fef9c3', color: '#b45309' },
    { value: 'no_answer',      label: 'Switched Off / No Answer', icon: 'phone_missed', bg: '#f1f5f9', color: '#64748b' },
    { value: 'called',         label: 'Other / Just Called',      icon: 'phone_callback', bg: '#f8fafc', color: '#475569', border: true },
];

function CallOutcomeModal({ url, onLogged }) {
    const modalRef   = useRef(null);
    const callLogRef = useRef(null);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        function onCallEnded(e) {
            const id = e.detail?.callLogId;
            callLogRef.current = id ?? null;
            const el = modalRef.current;
            if (el && window.bootstrap?.Modal) {
                new window.bootstrap.Modal(el).show();
            }
        }
        document.addEventListener('gc:callEnded', onCallEnded);
        return () => document.removeEventListener('gc:callEnded', onCallEnded);
    }, []);

    async function log(outcome) {
        setSaving(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ outcome }),
            });
            onLogged?.(outcome);
        } catch (_) {}
        setSaving(false);
        window.bootstrap?.Modal.getInstance(modalRef.current)?.hide();
    }

    return (
        <div className="modal fade" id="callOutcomeModal" ref={modalRef}
            tabIndex={-1} data-bs-backdrop="static" data-bs-keyboard="false">
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content" style={{ borderRadius: 18, border: 'none', overflow: 'hidden' }}>
                    <div style={{ background: 'linear-gradient(135deg,#1D1D1D,#1e293b)', padding: '18px 22px 14px', display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 36, height: 36, borderRadius: 10, background: 'rgba(99,102,241,.25)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <span className="material-icons" style={{ fontSize: 20, color: '#a5b4fc' }}>call_end</span>
                        </div>
                        <div>
                            <div style={{ fontSize: 14, fontWeight: 700, color: '#fff' }}>How did the call go?</div>
                            <div style={{ fontSize: 11.5, color: '#94a3b8' }}>Log the outcome to update the contact status</div>
                        </div>
                    </div>
                    <div style={{ padding: '16px 22px' }}>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                            {OUTCOMES.map(({ value, label, icon, bg, color, border }) => (
                                <button key={value} disabled={saving} onClick={() => log(value)}
                                    style={{
                                        display: 'flex', alignItems: 'center', gap: 10,
                                        padding: '11px 16px', borderRadius: 10,
                                        background: bg, color,
                                        border: border ? `1.5px solid #e2e8f0` : 'none',
                                        fontWeight: 700, fontSize: 13, cursor: saving ? 'not-allowed' : 'pointer',
                                        textAlign: 'left', width: '100%', transition: 'filter .15s',
                                    }}
                                    onMouseEnter={e => e.currentTarget.style.filter = 'brightness(.95)'}
                                    onMouseLeave={e => e.currentTarget.style.filter = ''}
                                >
                                    <span className="material-icons" style={{ fontSize: 18 }}>{icon}</span>
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>
                    <div style={{ padding: '0 22px 18px', textAlign: 'center' }}>
                        <button type="button" style={{ background: 'none', border: 'none', color: '#94a3b8', fontSize: 12, cursor: 'pointer' }}
                            data-bs-dismiss="modal">
                            Skip for now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

// ─── Main Contact Page ────────────────────────────────────────────────────────
export default function Contact({ campaign, contact: initialContact, activities: initialActivities, whatsapp_messages, courses, urls }) {
    const [contact,    setContact]    = useState(initialContact);
    const [activities, setActivities] = useState(initialActivities ?? []);
    const [statusOpen, setStatusOpen] = useState(false);

    function fmtFollowup() {
        if (!contact.next_followup) return null;
        const date = new Date(contact.next_followup).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        if (!contact.followup_time) return date;
        const [h, m] = contact.followup_time.split(':');
        const d = new Date(); d.setHours(h, m);
        return `${date} ${d.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })}`;
    }

    function handleStatusChanged(newStatus, followupData) {
        setContact(prev => ({
            ...prev,
            status: newStatus,
            ...(followupData?.next_followup ? { next_followup: followupData.next_followup, followup_time: followupData.followup_time } : {}),
        }));
        setActivities(prev => [{
            id: Date.now(), type: 'status_change',
            description: `Status changed to ${newStatus}`,
            meta: null, created_by: 'You', created_at: 'just now',
        }, ...prev]);
    }

    function handleNoteAdded() {
        router.reload({ only: ['activities'] });
    }

    function handleOutcomeLogged(outcome) {
        const statusMap = { interested:'interested', not_interested:'not_interested', callback:'follow_up', no_answer:'contacted', called:'contacted' };
        if (statusMap[outcome]) setContact(prev => ({ ...prev, status: statusMap[outcome] }));
        setActivities(prev => [{
            id: Date.now(), type: 'call',
            description: 'Outbound call made',
            meta: { outcome }, created_by: 'You', created_at: 'just now',
        }, ...prev]);
    }

    function scrollToChat() {
        document.querySelector('.wa-chat-body')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    const followupLabel = fmtFollowup();
    const profileItems = [
        { icon: 'phone',      label: 'Mobile',           value: contact.phone },
        contact.email  && { icon: 'mail',       label: 'Email',          value: contact.email  },
        contact.course && { icon: 'school',     label: 'Course Interest',value: contact.course },
        contact.city   && { icon: 'location_on',label: 'City',           value: contact.city   },
        { icon: 'call',       label: 'Total Calls Made', value: String(contact.call_count ?? 0) },
        followupLabel && { icon: 'event',       label: 'Next Follow-up', value: followupLabel  },
    ].filter(Boolean);

    return (
        <>
            <Head title={contact.name}/>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');`}</style>

            {/* ── Sub-nav ──────────────────────────────────────────────────── */}
            <div style={{
                background: 'linear-gradient(135deg,#1D1D1D 0%,#1D1D1D 45%,#FF5C00 75%,#1a3460 100%)',
                padding: '14px 28px',
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                flexWrap: 'wrap', gap: 10,
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    {/* Back button */}
                    <Link href={campaign.back_url}
                        style={{
                            width: 36, height: 36, borderRadius: 10, flexShrink: 0,
                            background: 'rgba(255,255,255,.10)', border: '1.5px solid rgba(255,255,255,.18)',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            textDecoration: 'none', color: '#fff', transition: 'background .15s',
                        }}
                        onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,.20)'}
                        onMouseLeave={e => e.currentTarget.style.background = 'rgba(255,255,255,.10)'}>
                        <span className="material-icons" style={{ fontSize: 19 }}>arrow_back</span>
                    </Link>

                    {/* Breadcrumb trail */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <Link href={campaign.back_url}
                            style={{ fontSize: 12, color: '#94a3b8', textDecoration: 'none', fontWeight: 500,
                                display: 'flex', alignItems: 'center', gap: 4 }}
                            onMouseEnter={e => e.currentTarget.style.color = '#c7d2fe'}
                            onMouseLeave={e => e.currentTarget.style.color = '#94a3b8'}>
                            <span className="material-icons" style={{ fontSize: 13 }}>campaign</span>
                            {campaign.name}
                        </Link>
                        <span style={{ color: '#475569', fontSize: 13 }}>›</span>
                        <span style={{ fontSize: 13, fontWeight: 700, color: '#fff' }}>{contact.name}</span>
                    </div>
                </div>

                {/* Right — status + phone */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    {contact.phone && (
                        <span style={{ fontSize: 12, color: '#94a3b8', display: 'flex', alignItems: 'center', gap: 4 }}>
                            <span className="material-icons" style={{ fontSize: 13 }}>phone</span>
                            {contact.phone}
                        </span>
                    )}
                    <StatusBadge status={contact.status} />
                </div>
            </div>

            <div className="dashboard-content">
                <div className="row g-4">

                    {/* ── LEFT COLUMN ─────────────────────────────────────── */}
                    <div className="col-lg-4">
                        {/* Profile Card */}
                        <div className="profile-card mb-4">
                            <div className="profile-header">
                                <div className="profile-info">
                                    <h1 className="profile-name">{contact.name}</h1>
                                    <StatusBadge status={contact.status} />
                                    <p className="profile-id" style={{ marginTop: 6 }}>{campaign.name}</p>
                                </div>
                            </div>
                            <div className="profile-details">
                                {profileItems.map(item => (
                                    <div className="detail-item" key={item.label}>
                                        <span className="material-icons">{item.icon}</span>
                                        <div className="flex-grow-1">
                                            <p className="detail-label">{item.label}</p>
                                            <p className="detail-value">{item.value}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Schedule Follow-up */}
                        <ScheduleFollowupForm
                            contact={contact}
                            url={urls.set_followup}
                            onSaved={({ next_followup, followup_time }) =>
                                setContact(prev => ({ ...prev, next_followup, followup_time }))
                            }
                        />
                    </div>

                    {/* ── RIGHT COLUMN ────────────────────────────────────── */}
                    <div className="col-lg-8">

                        {/* ── Action Bar (ab-card style) ──────────────────── */}
                        <div className="ab-card mb-4">
                            <style>{`
                                .ab-card {
                                    background: #fff;
                                    border: 1px solid #e2e8f0;
                                    border-radius: 16px;
                                    overflow: hidden;
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
                                }
                                .ab-card-head {
                                    background: linear-gradient(135deg,#1D1D1D 0%,#1D1D1D 45%,#FF5C00 75%,#1a3460 100%);
                                    padding: 12px 20px;
                                    display: flex; align-items: center; gap: 10px;
                                }
                                .ab-card-head .material-icons { color: #FF5C00; font-size: 18px; }
                                .ab-card-head-title { font-size: 13px; font-weight: 700; color: #fff; }
                                .ab-lead-chip { margin-left: auto; display: flex; align-items: center; gap: 8px; }
                                .ab-lead-name { font-size: 12px; font-weight: 600; color: #ffffff; }
                                .ab-status-chip {
                                    font-size: 10px; font-weight: 700; padding: 2px 9px;
                                    border-radius: 20px; text-transform: uppercase; letter-spacing: .5px;
                                    background: rgba(99,102,241,0.25); color: #a5b4fc;
                                    border: 1px solid rgba(99,102,241,0.35);
                                }
                                .ab-body { display: flex; align-items: stretch; flex-wrap: wrap; }
                                .ab-group {
                                    padding: 16px 20px;
                                    display: flex; flex-direction: column; gap: 8px;
                                    flex: 1; min-width: 0;
                                }
                                .ab-group + .ab-group { border-left: 1px solid #f1f5f9; }
                                .ab-group-label {
                                    font-size: 10px; font-weight: 700; color: #94a3b8;
                                    text-transform: uppercase; letter-spacing: .7px; margin-bottom: 2px;
                                }
                                .ab-group-btns { display: flex; flex-wrap: wrap; gap: 8px; }
                                .ab-btn {
                                    display: inline-flex; align-items: center; gap: 6px;
                                    padding: 8px 14px; border-radius: 10px;
                                    font-size: 13px; font-weight: 600;
                                    border: 1.5px solid transparent;
                                    cursor: pointer; transition: all .15s; white-space: nowrap;
                                }
                                .ab-btn .material-icons { font-size: 17px; }
                                .call-btn.ab-call, .btn.call-btn {
                                    background: #1D1D1D !important; color: #fff !important;
                                    border-color: #1D1D1D !important; border-radius: 10px !important;
                                    padding: 8px 16px !important; font-size: 13px !important;
                                    font-weight: 700 !important;
                                    display: inline-flex !important; align-items: center !important; gap: 6px !important;
                                }
                                .btn.call-btn:hover:not(:disabled) { background: #1e293b !important; border-color: #1e293b !important; }
                                .btn.call-btn.active-call { background: #ef4444 !important; border-color: #ef4444 !important; }
                                .btn.call-btn .material-icons { color: #FF5C00 !important; font-size: 17px !important; }
                                .btn.call-btn.active-call .material-icons { color: #fff !important; }
                                .ab-wa { background: #25d366; color: #fff; border-color: #25d366; }
                                .ab-wa:hover { background: #1db954; border-color: #1db954; }
                                .ab-wa .material-icons { color: #fff; }
                                .ab-status { background: #f8fafc; color: #334155; border-color: #e2e8f0; }
                                .ab-status:hover, .ab-status.active { background: #1D1D1D; color: #fff; border-color: #1D1D1D; }
                                .ab-status .material-icons { font-size: 17px; }
                                @media (max-width: 767px) {
                                    .ab-group + .ab-group { border-left: none; border-top: 1px solid #f1f5f9; }
                                }
                            `}</style>

                            {/* Header */}
                            <div className="ab-card-head">
                                <span className="material-icons">bolt</span>
                                <span className="ab-card-head-title">Quick Actions</span>
                                <div className="ab-lead-chip">
                                    <span className="ab-lead-name">{contact.name}</span>
                                    <span className="ab-status-chip">{(contact.status || '').replace(/_/g, ' ')}</span>
                                </div>
                            </div>

                            {/* Groups */}
                            <div className="ab-body">
                                {/* Group 1 — Communication */}
                                <div className="ab-group">
                                    <div className="ab-group-label">Communication</div>
                                    <div className="ab-group-btns">
                                        <CallButton phone={contact.phone} campaignContactId={contact.id} />
                                        <button className="ab-btn ab-wa" type="button" onClick={scrollToChat}>
                                            <span className="material-icons">chat</span>
                                            WhatsApp
                                        </button>
                                    </div>
                                </div>

                                {/* Group 2 — Contact Actions */}
                                <div className="ab-group" style={{ flex: 'none' }}>
                                    <div className="ab-group-label">Contact Actions</div>
                                    <div className="ab-group-btns">
                                        <button
                                            className={`ab-btn ab-status${statusOpen ? ' active' : ''}`}
                                            type="button"
                                            onClick={() => setStatusOpen(o => !o)}>
                                            <span className="material-icons">sync_alt</span>
                                            Change Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Inline Status Panel */}
                        {statusOpen && (
                            <StatusPanel
                                contact={contact}
                                courses={courses ?? []}
                                url={urls.change_status}
                                onClose={() => setStatusOpen(false)}
                                onChanged={handleStatusChanged}
                            />
                        )}

                        {/* WhatsApp Chat */}
                        <WaChat
                            contactName={contact.name}
                            initialMessages={whatsapp_messages ?? []}
                            urls={urls}
                        />

                        {/* Add Note */}
                        <AddNote url={urls.add_note} onAdded={handleNoteAdded} />

                        {/* Activity Timeline */}
                        <ActivityTimeline activities={activities} />
                    </div>
                </div>
            </div>

            {/* Call Outcome Modal */}
            <CallOutcomeModal url={urls.log_call} onLogged={handleOutcomeLogged} />
        </>
    );
}
