import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import {
    LuChevronLeft, LuPhone, LuMail, LuCalendar, LuUsers,
    LuCheck, LuX, LuRefreshCw, LuPencil,
} from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

// Status config
const STATUS_MAP = {
    pending:        { label: 'Pending',        bg: '#f1f5f9', color: '#64748b' },
    called:         { label: 'Called',          bg: '#e0f2fe', color: '#0284c7' },
    interested:     { label: 'Interested',      bg: '#dcfce7', color: '#16a34a' },
    not_interested: { label: 'Not Interested',  bg: '#fee2e2', color: '#dc2626' },
    no_answer:      { label: 'No Answer',       bg: '#fef9c3', color: '#ca8a04' },
    callback:       { label: 'Callback',        bg: '#ede9fe', color: '#7c3aed' },
    converted:      { label: 'Converted',       bg: `${OR}18`, color: OR },
};
const STATUSES = ['pending','called','interested','not_interested','no_answer','callback','converted'];

const ACTIVITY_ICON_MAP = {
    call: LuPhone, note: LuPencil, whatsapp: LuMail, status_change: LuRefreshCw, followup_set: LuCalendar,
};

function now12h() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function StatusPill({ status }) {
    const s = STATUS_MAP[status] ?? { label: status, bg: '#f1f5f9', color: '#64748b' };
    return (
        <span style={{ background: s.bg, color: s.color, fontSize: 11, fontWeight: 700,
            padding: '3px 10px', borderRadius: 99, whiteSpace: 'nowrap' }}>
            {s.label}
        </span>
    );
}

function SectionCard({ title, children, style }) {
    return (
        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.04)', marginBottom: 20, ...style }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '14px 20px', borderBottom: `1px solid ${BOR}` }}>
                <div style={{ width: 3, height: 26, background: OR, borderRadius: 2 }} />
                <span style={{ fontSize: 14, fontWeight: 700, color: DK }}>{title}</span>
            </div>
            <div style={{ padding: 20 }}>{children}</div>
        </div>
    );
}

// WaBubble
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
                        <LuPencil size={18} color={OR} />
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

// WaChat
function WaChat({ contactName, initialMessages, urls }) {
    const chatBodyRef = useRef(null);
    const lastIdRef   = useRef(initialMessages.length ? Math.max(...initialMessages.map(m => m.id)) : 0);
    const fileInputRef = useRef(null);

    const [messages,    setMessages]    = useState(initialMessages);
    const [text,        setText]        = useState('');
    const [pendingFile, setPendingFile] = useState(null);
    const [sending,     setSending]     = useState(false);
    const [toasts,      setToasts]      = useState([]);

    useEffect(() => {
        if (chatBodyRef.current) chatBodyRef.current.scrollTop = chatBodyRef.current.scrollHeight;
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
        <div className="card border-0 shadow-sm mb-4" style={{ position: 'relative', borderRadius: 14, overflow: 'hidden', border: `1px solid ${BOR}` }}>
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
                                <LuPencil size={18} color={OR} />
                                <span style={{ flex:1, fontWeight:600, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>
                                    {fileLabel}
                                </span>
                                <button type="button" onClick={clearFile}
                                    style={{ background:'none', border:'none', cursor:'pointer', color:'#ef4444', padding:0, display:'flex' }}>
                                    <LuX size={16} />
                                </button>
                            </div>
                        )}

                        <form className="wa-composer-form" onSubmit={handleSubmit}>
                            <input type="file" ref={fileInputRef} style={{ display:'none' }}
                                accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
                                onChange={e => setPendingFile(e.target.files[0] || null)} />
                            <button type="button" onClick={() => fileInputRef.current?.click()}
                                style={{ background: WH, border:`1.5px solid ${BOR}`, borderRadius:'50%',
                                    width:38, height:38, display:'flex', alignItems:'center',
                                    justifyContent:'center', cursor:'pointer', flexShrink:0 }}>
                                <LuPencil size={18} color={MUT} />
                            </button>
                            <input className="form-control" type="text" autoComplete="off"
                                placeholder={pendingFile ? 'Add a caption (optional)…' : 'Type a WhatsApp message...'}
                                value={text} onChange={e => setText(e.target.value)} />
                            <button type="submit" className="btn btn-success" disabled={sending}>
                                {sending
                                    ? <span className="spinner-border spinner-border-sm" />
                                    : <span style={{ fontSize: 18 }}>➤</span>
                                }
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {toasts.length > 0 && (
                <div style={{ position:'absolute', bottom:80, right:12, zIndex:10, pointerEvents:'none',
                    display:'flex', flexDirection:'column', gap:6 }}>
                    {toasts.map(t => (
                        <div key={t.id} style={{ background:WH, border:`1px solid ${BOR}`,
                            borderLeft:`4px solid ${t.color}`, borderRadius:10,
                            padding:'8px 14px', boxShadow:'0 4px 16px rgba(0,0,0,.12)',
                            fontSize:13, fontWeight:600, color:DK }}>
                            {t.msg}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

// ActivityTimeline
function ActivityTimeline({ activities }) {
    const [filter, setFilter] = useState('all');
    const FILTERS = ['all','call','whatsapp','note','status_change'];
    const visible = filter === 'all' ? activities : activities.filter(a => a.type === filter);

    const ACTIVITY_COLORS = {
        call: OR, note: '#8B5CF6', whatsapp: '#25D366', status_change: '#06b6d4', followup_set: '#f59e0b',
    };

    return (
        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
            {/* Header */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 20px', borderBottom: `1px solid ${BOR}`, flexWrap: 'wrap', gap: 10 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <div style={{ width: 3, height: 26, background: OR, borderRadius: 2 }} />
                    <span style={{ fontSize: 15, fontWeight: 700, color: DK }}>Activity Timeline</span>
                </div>
                <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                    {FILTERS.map(f => (
                        <button key={f}
                            onClick={() => setFilter(f)}
                            style={{
                                padding: '5px 12px', borderRadius: 20, fontSize: 12, fontWeight: 600,
                                border: `1.5px solid ${filter === f ? OR : BOR}`,
                                background: filter === f ? OR : WH,
                                color: filter === f ? '#fff' : BDY,
                                cursor: 'pointer',
                            }}>
                            {f === 'all' ? 'All' : f === 'status_change' ? 'Status' : f.charAt(0).toUpperCase() + f.slice(1)}
                        </button>
                    ))}
                </div>
            </div>
            <div style={{ padding: 20 }}>
                {visible.length === 0 ? (
                    <div style={{ textAlign: 'center', padding: '30px 0' }}>
                        <LuCalendar size={40} color={BOR} style={{ display: 'block', margin: '0 auto 10px' }} />
                        <p style={{ color: MUT, marginBottom: 0 }}>No activity recorded yet.</p>
                    </div>
                ) : visible.map(activity => {
                    const IconComp = ACTIVITY_ICON_MAP[activity.type];
                    const color = ACTIVITY_COLORS[activity.type] ?? MUT;
                    return (
                        <div key={activity.id} style={{ display: 'flex', gap: 14, marginBottom: 16, alignItems: 'flex-start' }}>
                            <div style={{ width: 34, height: 34, borderRadius: 9, background: color + '18', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, marginTop: 2 }}>
                                {IconComp ? <IconComp size={16} color={color} /> : <span style={{ fontSize: 12, color }}>{activity.type[0].toUpperCase()}</span>}
                            </div>
                            <div style={{ flex: 1 }}>
                                <p style={{ fontSize: 13, color: DK, fontWeight: 500, margin: '0 0 4px' }}>{activity.description}</p>
                                {activity.type === 'call' && activity.meta && (
                                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 5, marginBottom: 4 }}>
                                        {activity.meta.outcome && (
                                            <span style={{ background: BOR, color: BDY, fontSize: 11, fontWeight: 600, padding: '2px 8px', borderRadius: 6 }}>
                                                Outcome: {activity.meta.outcome.charAt(0).toUpperCase() + activity.meta.outcome.slice(1)}
                                            </span>
                                        )}
                                        {activity.meta.duration && (
                                            <span style={{ background: BOR, color: BDY, fontSize: 11, fontWeight: 600, padding: '2px 8px', borderRadius: 6 }}>Duration: {activity.meta.duration}s</span>
                                        )}
                                    </div>
                                )}
                                <small style={{ color: MUT, fontSize: 11 }}>{activity.created_by} | {activity.created_at}</small>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

// ScheduleFollowupForm
function ScheduleFollowupForm({ contact, url, onSaved }) {
    const [form, setForm] = useState({
        followup_date: contact.next_followup ?? '',
        followup_time: contact.followup_time ?? '',
        status: '',
        notes: '',
    });
    const [saving, setSaving] = useState(false);
    const [saved,  setSaved]  = useState(false);

    const inputStyle = { borderRadius: 8, borderColor: BOR, fontSize: 13, fontFamily: 'Poppins, sans-serif', padding: '8px 12px', width: '100%', border: `1.5px solid ${BOR}`, outline: 'none' };

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
        <SectionCard title="Schedule Follow-Up">
            <form onSubmit={handleSubmit}>
                <div className="row g-2 mb-2">
                    <div className="col-7">
                        <label style={{ fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 5, display: 'block' }}>Date</label>
                        <input type="date" style={inputStyle}
                            value={form.followup_date}
                            min={new Date().toISOString().split('T')[0]}
                            onChange={e => setForm({ ...form, followup_date: e.target.value })} required />
                    </div>
                    <div className="col-5">
                        <label style={{ fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 5, display: 'block' }}>Time</label>
                        <input type="time" style={inputStyle}
                            value={form.followup_time}
                            onChange={e => setForm({ ...form, followup_time: e.target.value })} />
                    </div>
                </div>
                <div style={{ marginBottom: 10 }}>
                    <label style={{ fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 5, display: 'block' }}>Update Status</label>
                    <select style={{ ...inputStyle, appearance: 'auto' }} value={form.status}
                        onChange={e => setForm({ ...form, status: e.target.value })}>
                        <option value="">— Keep current —</option>
                        {['callback','interested','not_interested','no_answer','called'].map(s => (
                            <option key={s} value={s}>{s.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase())}</option>
                        ))}
                    </select>
                </div>
                <div style={{ marginBottom: 14 }}>
                    <label style={{ fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 5, display: 'block' }}>Notes (optional)</label>
                    <textarea style={{ ...inputStyle, resize: 'vertical' }} rows={2}
                        placeholder="Add follow-up notes..."
                        value={form.notes}
                        onChange={e => setForm({ ...form, notes: e.target.value })} />
                </div>
                <button style={{
                    width: '100%', padding: '10px', borderRadius: 9, border: 'none',
                    background: saved ? '#16a34a' : OR, color: '#fff', fontWeight: 700, fontSize: 13,
                    cursor: saving ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 7,
                }} disabled={saving}>
                    {saved ? <LuCheck size={15} /> : <LuCalendar size={15} />}
                    {saving ? 'Saving…' : saved ? 'Saved!' : 'Save Follow-up'}
                </button>
            </form>
        </SectionCard>
    );
}

// ReassignForm
function ReassignForm({ contact, telecallers, url }) {
    const [assignedTo, setAssignedTo] = useState(contact.assigned_to ?? '');
    const [saving,     setSaving]     = useState(false);
    const [saved,      setSaved]      = useState(false);

    async function handleSubmit(e) {
        e.preventDefault();
        setSaving(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ assigned_to: assignedTo }),
            });
            if (res.ok) { setSaved(true); setTimeout(() => setSaved(false), 3000); }
        } catch (_) {}
        setSaving(false);
    }

    return (
        <SectionCard title="Assign / Reassign">
            <form onSubmit={handleSubmit}>
                <select style={{ width: '100%', borderRadius: 8, border: `1.5px solid ${BOR}`, padding: '8px 12px', fontSize: 13, fontFamily: 'Poppins, sans-serif', marginBottom: 12, appearance: 'auto' }}
                    value={assignedTo}
                    onChange={e => setAssignedTo(e.target.value)} required>
                    <option value="">Select Telecaller</option>
                    {telecallers.map(tc => (
                        <option key={tc.id} value={tc.id}>{tc.name}</option>
                    ))}
                </select>
                <button style={{
                    width: '100%', padding: '10px', borderRadius: 9, border: 'none',
                    background: saved ? '#16a34a' : OR, color: '#fff', fontWeight: 700, fontSize: 13,
                    cursor: saving ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 7,
                }} disabled={saving}>
                    <LuUsers size={15} />
                    {saving ? 'Saving…' : saved ? 'Assigned!' : 'Assign'}
                </button>
            </form>
        </SectionCard>
    );
}

// AddNote
function AddNote({ url, onAdded }) {
    const [note,   setNote]   = useState('');
    const [saving, setSaving] = useState(false);

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
            if (res.ok) { setNote(''); onAdded?.(); }
        } catch (_) {}
        setSaving(false);
    }

    return (
        <SectionCard title="Add Note" style={{ marginBottom: 20 }}>
            <form onSubmit={handleSubmit}>
                <textarea
                    style={{ width: '100%', borderRadius: 8, border: `1.5px solid ${BOR}`, padding: '10px 12px', fontSize: 13, fontFamily: 'Poppins, sans-serif', resize: 'vertical', marginBottom: 12 }}
                    rows={2}
                    placeholder="Write a note about this contact..."
                    value={note} onChange={e => setNote(e.target.value)} required />
                <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                    <button style={{
                        padding: '9px 20px', borderRadius: 9, border: 'none',
                        background: DK, color: '#fff', fontWeight: 700, fontSize: 13,
                        cursor: saving ? 'not-allowed' : 'pointer', display: 'inline-flex', alignItems: 'center', gap: 6,
                    }} disabled={saving}>
                        <LuPencil size={14} />
                        {saving ? 'Saving…' : 'Add Note'}
                    </button>
                </div>
            </form>
        </SectionCard>
    );
}

// StatusModal
function StatusModal({ show, currentStatus, url, onClose, onChanged }) {
    const [selected, setSelected] = useState(currentStatus);
    const [step,     setStep]     = useState('pick');
    const [saving,   setSaving]   = useState(false);

    useEffect(() => {
        if (show) { setSelected(currentStatus); setStep('pick'); }
    }, [show, currentStatus]);

    async function confirm() {
        if (step === 'pick') { setStep('confirm'); return; }
        setSaving(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ status: selected }),
            });
            if (res.ok) { onChanged?.(selected); onClose?.(); }
        } catch (_) {}
        setSaving(false);
    }

    if (!show) return null;
    return (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.5)', zIndex: 9999, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, width: '90%', maxWidth: 420, boxShadow: '0 20px 60px rgba(0,0,0,0.2)', overflow: 'hidden' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 20px', borderBottom: `1px solid ${BOR}` }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 3, height: 24, background: OR, borderRadius: 2 }} />
                        <h5 style={{ fontSize: 15, fontWeight: 700, color: DK, margin: 0 }}>Update Contact Status</h5>
                    </div>
                    <button onClick={onClose} style={{ background: 'none', border: 'none', cursor: 'pointer', color: MUT, padding: 4, display: 'flex' }}>
                        <LuX size={18} />
                    </button>
                </div>
                <div style={{ padding: 20 }}>
                    {step === 'pick' ? (
                        <div style={{ marginBottom: 16 }}>
                            <label style={{ fontSize: 13, fontWeight: 600, color: BDY, marginBottom: 8, display: 'block' }}>Select Status</label>
                            <select style={{ width: '100%', borderRadius: 8, border: `1.5px solid ${BOR}`, padding: '9px 12px', fontSize: 13, fontFamily: 'Poppins, sans-serif', appearance: 'auto' }}
                                value={selected}
                                onChange={e => setSelected(e.target.value)}>
                                {STATUSES.map(s => (
                                    <option key={s} value={s}>
                                        {s.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase())}
                                    </option>
                                ))}
                            </select>
                        </div>
                    ) : (
                        <p style={{ color: BDY, fontSize: 14, marginBottom: 16 }}>
                            Change status from <strong style={{ color: DK }}>{currentStatus.replace(/_/g,' ')}</strong> to{' '}
                            <strong style={{ color: OR }}>{selected.replace(/_/g,' ')}</strong>?
                        </p>
                    )}
                </div>
                <div style={{ display: 'flex', gap: 10, padding: '14px 20px', borderTop: `1px solid ${BOR}`, justifyContent: 'flex-end' }}>
                    <button onClick={step === 'confirm' ? () => setStep('pick') : onClose}
                        style={{ padding: '9px 18px', borderRadius: 9, border: `1.5px solid ${BOR}`, background: WH, color: BDY, fontWeight: 600, fontSize: 13, cursor: 'pointer' }}>
                        {step === 'confirm' ? 'Back' : 'Cancel'}
                    </button>
                    <button onClick={confirm} disabled={saving}
                        style={{ padding: '9px 18px', borderRadius: 9, border: 'none', background: step === 'confirm' ? '#f59e0b' : OR, color: '#fff', fontWeight: 700, fontSize: 13, cursor: saving ? 'not-allowed' : 'pointer' }}>
                        {saving ? 'Saving…' : step === 'confirm' ? 'Yes, Confirm' : 'Update Status'}
                    </button>
                </div>
            </div>
        </div>
    );
}

// CallOutcomeModal
function CallOutcomeModal({ show, url, onClose, onLogged }) {
    const [saving, setSaving] = useState(false);

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
        onClose?.();
    }

    if (!show) return null;

    const outcomes = [
        { outcome:'interested',     label:'Interested',               bg: '#dcfce7', color: '#16a34a' },
        { outcome:'not_interested', label:'Not Interested',            bg: '#fee2e2', color: '#dc2626' },
        { outcome:'callback',       label:'Call Back Later',           bg: '#fef9c3', color: '#ca8a04' },
        { outcome:'no_answer',      label:'Switched Off / No Answer',  bg: '#f1f5f9', color: '#64748b' },
        { outcome:'called',         label:'Other / Just Called',       bg: BOR,       color: BDY },
    ];

    return (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.5)', zIndex: 9999, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, width: '90%', maxWidth: 380, boxShadow: '0 20px 60px rgba(0,0,0,0.2)', overflow: 'hidden' }}>
                <div style={{ padding: '18px 20px', borderBottom: `1px solid ${BOR}` }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                        <div style={{ width: 3, height: 24, background: OR, borderRadius: 2 }} />
                        <h5 style={{ fontSize: 15, fontWeight: 700, color: DK, margin: 0 }}>How did the call go?</h5>
                    </div>
                    <p style={{ color: MUT, fontSize: 12, margin: 0, marginLeft: 13 }}>Select the outcome to log it in the activity timeline.</p>
                </div>
                <div style={{ padding: '16px 20px', display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {outcomes.map(({ outcome, label, bg, color }) => (
                        <button key={outcome} disabled={saving} onClick={() => log(outcome)}
                            style={{ width: '100%', padding: '11px 16px', borderRadius: 9, border: `1.5px solid ${color}40`, background: bg, color, fontWeight: 600, fontSize: 13, cursor: saving ? 'not-allowed' : 'pointer', textAlign: 'left' }}>
                            {label}
                        </button>
                    ))}
                </div>
                <div style={{ padding: '10px 20px 16px', textAlign: 'center' }}>
                    <button onClick={onClose} style={{ background: 'none', border: 'none', color: MUT, fontSize: 13, cursor: 'pointer', fontFamily: 'Poppins, sans-serif' }}>Skip</button>
                </div>
            </div>
        </div>
    );
}

// Main
export default function Contact({ campaign, contact: initialContact, activities: initialActivities,
    whatsapp_messages, telecallers, urls }) {

    const [contact,     setContact]     = useState(initialContact);
    const [activities,  setActivities]  = useState(initialActivities ?? []);
    const [showStatus,  setShowStatus]  = useState(false);
    const [showOutcome, setShowOutcome] = useState(false);

    function fmtFollowup() {
        if (!contact.next_followup) return null;
        const date = new Date(contact.next_followup).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        if (!contact.followup_time) return date;
        const [h, m] = contact.followup_time.split(':');
        const d = new Date(); d.setHours(+h, +m);
        return `${date} ${d.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })}`;
    }

    function handleStatusChanged(newStatus) {
        setContact(prev => ({ ...prev, status: newStatus }));
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
        const statusMap = { interested:'interested', not_interested:'not_interested',
            callback:'callback', no_answer:'no_answer', called:'called' };
        if (statusMap[outcome]) setContact(prev => ({ ...prev, status: statusMap[outcome] }));
        setActivities(prev => [{
            id: Date.now(), type: 'call',
            description: 'Outbound call made',
            meta: { outcome }, created_by: 'You', created_at: 'just now',
        }, ...prev]);
    }

    useEffect(() => {
        const handler = () => setShowOutcome(true);
        document.addEventListener('gc:callEnded', handler);
        return () => document.removeEventListener('gc:callEnded', handler);
    }, []);

    const followupLabel = fmtFollowup();
    const profileItems = [
        { Icon: LuPhone,    label: 'Mobile',           value: contact.phone },
        contact.email  && { Icon: LuMail,     label: 'Email',            value: contact.email },
        contact.course && { Icon: LuUsers,    label: 'Course Interest',  value: contact.course },
        contact.city   && { Icon: LuUsers,    label: 'City',             value: contact.city },
        { Icon: LuUsers,    label: 'Assigned To',      value: contact.assigned_user ?? '—' },
        { Icon: LuPhone,    label: 'Total Calls Made', value: String(contact.call_count) },
        followupLabel && { Icon: LuCalendar,  label: 'Next Follow-up',   value: followupLabel },
    ].filter(Boolean);

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap'); .camp-contact * { font-family: 'Poppins', sans-serif !important; }`}</style>
            <Head title={contact.name} />

            <div className="camp-contact">
                {/* Nav bar */}
                <div style={{ marginBottom: 24, display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 12 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                        <Link href={urls.back}
                            style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 8, padding: '7px 14px', fontSize: 13, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 6, textDecoration: 'none', boxShadow: '0 1px 4px rgba(0,0,0,0.06)' }}>
                            <LuChevronLeft size={16} />
                            Back to {campaign.name}
                        </Link>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 3, height: 32, background: OR, borderRadius: 2 }} />
                            <div>
                                <h2 style={{ fontSize: 18, fontWeight: 800, color: DK, margin: 0 }}>{contact.name}</h2>
                                <p style={{ color: MUT, fontSize: 12, margin: '2px 0 0' }}>Campaign Contact</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row g-4">
                    {/* Left column */}
                    <div className="col-lg-4">
                        {/* Profile card */}
                        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.04)', marginBottom: 20 }}>
                            {/* Profile header */}
                            <div style={{ background: `linear-gradient(135deg,${OR},#ff8c00)`, padding: '24px 20px', textAlign: 'center' }}>
                                <div style={{ width: 64, height: 64, borderRadius: '50%', background: 'rgba(255,255,255,0.25)', color: '#fff', fontWeight: 800, fontSize: 26, display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 12px' }}>
                                    {(contact.name ?? '?')[0].toUpperCase()}
                                </div>
                                <h1 style={{ fontSize: 18, fontWeight: 800, color: '#fff', margin: '0 0 8px' }}>{contact.name}</h1>
                                <div style={{ marginBottom: 6 }}><StatusPill status={contact.status} /></div>
                                <p style={{ color: 'rgba(255,255,255,0.75)', fontSize: 12, margin: 0 }}>{campaign.name}</p>
                            </div>
                            {/* Details */}
                            <div style={{ padding: 20 }}>
                                {profileItems.map(item => (
                                    <div key={item.label} style={{ display: 'flex', alignItems: 'flex-start', gap: 12, marginBottom: 12 }}>
                                        <div style={{ width: 32, height: 32, borderRadius: 8, background: `${OR}18`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, marginTop: 2 }}>
                                            <item.Icon size={15} color={OR} />
                                        </div>
                                        <div>
                                            <p style={{ fontSize: 11, color: MUT, fontWeight: 600, margin: '0 0 2px', textTransform: 'uppercase', letterSpacing: '0.04em' }}>{item.label}</p>
                                            <p style={{ fontSize: 13, color: DK, fontWeight: 600, margin: 0 }}>{item.value}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <ReassignForm contact={contact} telecallers={telecallers} url={urls.reassign} />
                        <ScheduleFollowupForm
                            contact={contact}
                            url={urls.set_followup}
                            onSaved={({ next_followup, followup_time }) =>
                                setContact(prev => ({ ...prev, next_followup, followup_time }))
                            }
                        />
                    </div>

                    {/* Right column */}
                    <div className="col-lg-8">
                        {/* Action bar */}
                        <div style={{ display: 'flex', gap: 10, marginBottom: 20, flexWrap: 'wrap' }}>
                            <button type="button"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 7, padding: '10px 20px', borderRadius: 9, background: OR, color: '#fff', border: 'none', fontWeight: 700, fontSize: 14, cursor: 'pointer', boxShadow: `0 4px 12px ${OR}40` }}
                                onClick={async (e) => {
                                    const btn = e.currentTarget;
                                    if (window.GC?.isActive?.()) { window.GC.endCall(); return; }
                                    btn.disabled = true;
                                    btn.textContent = 'Connecting…';
                                    try { await window.GC?.startCall?.(contact.phone, null); }
                                    catch (_) {
                                        btn.disabled = false;
                                        btn.innerHTML = '<svg style="margin-right:7px" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 10 19.79 19.79 0 0 1 1.61 1.4 2 2 0 0 1 3.6 0h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 7.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 14.92z"/></svg> Call Now';
                                    }
                                }}>
                                <LuPhone size={16} />
                                Call Now
                            </button>

                            <button className="btn btn-success" type="button"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 7, fontFamily: 'Poppins, sans-serif', fontWeight: 700, fontSize: 14 }}
                                onClick={() => document.querySelector('.wa-chat-body')
                                    ?.scrollIntoView({ behavior:'smooth', block:'start' })}>
                                <LuMail size={16} />
                                WhatsApp
                            </button>

                            <button type="button"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 7, padding: '10px 18px', borderRadius: 9, background: WH, color: BDY, border: `1.5px solid ${BOR}`, fontWeight: 600, fontSize: 14, cursor: 'pointer', fontFamily: 'Poppins, sans-serif' }}
                                onClick={() => setShowStatus(true)}>
                                <LuRefreshCw size={15} />
                                Change Status
                            </button>
                        </div>

                        <WaChat
                            contactName={contact.name}
                            initialMessages={whatsapp_messages ?? []}
                            urls={urls}
                        />

                        <AddNote url={urls.add_note} onAdded={handleNoteAdded} />

                        <ActivityTimeline activities={activities} />
                    </div>
                </div>
            </div>

            <StatusModal
                show={showStatus}
                currentStatus={contact.status}
                url={urls.change_status}
                onClose={() => setShowStatus(false)}
                onChanged={handleStatusChanged}
            />
            <CallOutcomeModal
                show={showOutcome}
                url={urls.log_call}
                onClose={() => setShowOutcome(false)}
                onLogged={handleOutcomeLogged}
            />
        </>
    );
}
