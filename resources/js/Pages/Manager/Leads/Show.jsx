import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import {
    LuPhone, LuMail, LuUser, LuCalendar, LuMapPin, LuFileText,
    LuMessageSquare, LuVideo, LuPencil, LuX, LuCheck, LuChevronLeft,
    LuZap, LuRefreshCw, LuEye, LuExternalLink, LuPlus, LuSend,
    LuChevronDown, LuPhoneCall, LuPhoneMissed, LuArrowUpRight, LuArrowDownLeft, LuPause, LuPlay,
} from 'react-icons/lu';

// ─── Design tokens ────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

// ─── helpers ──────────────────────────────────────────────────────────────────
const SOURCE_CATEGORY_LABELS = {
    social_media:  'Social Media',
    newspaper:     'Newspaper',
    tv:            'TV Advertisement',
    referral:      'Referral',
    walk_in:       'Walk-in / Self',
    other:         'Other',
    website:       'Landing Page',
    facebook_ads:  'Facebook Ads',
    instagram_ads: 'Instagram Ads',
    google_ads:    'Google Ads',
    other_digital: 'Digital (Other)',
};

function sourceLabel(lead) {
    const cat = SOURCE_CATEGORY_LABELS[lead.source_category] ?? lead.source_category ?? '—';
    if (lead.source_detail) return `${cat} · ${lead.source_detail}`;
    return cat;
}

const STATUS_LABELS = {
    new: 'New', assigned: 'Assigned', contacted: 'Contacted',
    interested: 'Interested', follow_up: 'Follow-up',
    not_interested: 'Not Interested', converted: 'Converted',
};

function now12h() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function playChime() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [[1100, 0], [880, 0.18]].forEach(([freq, delay]) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.type = 'sine'; osc.frequency.value = freq;
            const t = ctx.currentTime + delay;
            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(0.3, t + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.001, t + 0.22);
            osc.start(t); osc.stop(t + 0.22);
        });
    } catch (_) {}
}

// ─── ProfileCard ──────────────────────────────────────────────────────────────
function todayDateStr() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function ProfileCard({ lead, telecallers, assignUrl, onEditContact }) {
    const today = todayDateStr();
    const form = useForm({ assigned_to: lead.assigned_to ?? '', assignment_date: today });

    const availableTelecallers = telecallers.filter(t =>
        !(t.blocked_dates ?? []).includes(form.data.assignment_date)
    );

    function handleDateChange(e) {
        const newDate = e.target.value;
        form.setData('assignment_date', newDate);
        const currentTc = telecallers.find(t => t.id === +form.data.assigned_to);
        if (currentTc && (currentTc.blocked_dates ?? []).includes(newDate)) {
            form.setData('assigned_to', '');
        }
    }

    function submit(e) {
        e.preventDefault();
        form.post(assignUrl);
    }

    const hiddenCount = telecallers.length - availableTelecallers.length;

    const statusColors = {
        new: { bg:'#f1f5f9', color:'#475569' }, assigned: { bg:'#FFF4EE', color:OR },
        contacted: { bg:'#e0f2fe', color:'#0369a1' }, interested: { bg:'#dcfce7', color:'#15803d' },
        follow_up: { bg:'#fef3c7', color:'#92400e' }, not_interested: { bg:'#fee2e2', color:'#991b1b' },
        converted: { bg:'#f3e8ff', color:'#7e22ce' },
    };
    const sc = statusColors[lead.status] ?? { bg:'#f1f5f9', color:'#475569' };

    return (
        <div style={{ background: WH, border: `1px solid ${BOR}`, borderRadius: 16, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.04)', marginBottom: 20 }}>
            {/* Orange accent top */}
            <div style={{ height: 4, background: `linear-gradient(90deg,${OR},#ff8c00)` }} />

            {/* Header */}
            <div style={{ padding: '20px 20px 16px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 10 }}>
                    <div style={{ width: 48, height: 48, borderRadius: 14, background: OR + '18', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <span style={{ fontSize: 20, fontWeight: 800, color: OR }}>{lead.name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div>
                        <div style={{ fontSize: 16, fontWeight: 800, color: DK }}>{lead.name}</div>
                        <div style={{ fontSize: 12, color: MUT, marginTop: 1 }}>ID: {lead.lead_code}</div>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                    <span style={{ padding: '3px 12px', borderRadius: 20, fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.5px', background: sc.bg, color: sc.color }}>
                        {STATUS_LABELS[lead.status] ?? lead.status}
                    </span>
                    {lead.is_active
                        ? <span style={{ padding: '3px 12px', borderRadius: 20, fontSize: 11, fontWeight: 700, background: '#dcfce7', color: '#16a34a' }}>Active</span>
                        : <span style={{ padding: '3px 12px', borderRadius: 20, fontSize: 11, fontWeight: 700, background: '#fee2e2', color: '#dc2626' }}>Inactive</span>
                    }
                </div>
            </div>

            {/* Details */}
            <div style={{ padding: '0 20px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
                {/* Phone */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                    <LuPhone size={15} style={{ color: OR, flexShrink: 0 }} />
                    <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>Phone</div>
                        <div style={{ fontSize: 13, fontWeight: 600, color: DK }}>{lead.phone}</div>
                    </div>
                    <button type="button" style={{ background: 'none', border: 'none', cursor: 'pointer', color: OR, padding: 0 }}
                        onClick={() => onEditContact(lead.phone, lead.email)} title="Edit contact">
                        <LuPencil size={15} />
                    </button>
                </div>

                {/* Email */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                    <LuMail size={15} style={{ color: OR, flexShrink: 0 }} />
                    <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>Email</div>
                        <div style={{ fontSize: 13, fontWeight: 600, color: DK }}>{lead.email || '—'}</div>
                    </div>
                    <button type="button" style={{ background: 'none', border: 'none', cursor: 'pointer', color: OR, padding: 0 }}
                        onClick={() => onEditContact(lead.phone, lead.email)} title="Edit contact">
                        <LuPencil size={15} />
                    </button>
                </div>

                {[
                    { Icon: LuFileText,  label: 'Service',        value: lead.service || '—' },
                    { Icon: LuExternalLink, label: 'Source',      value: sourceLabel(lead) },
                ].map(d => (
                    <div key={d.label} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                        <d.Icon size={15} style={{ color: OR, flexShrink: 0 }} />
                        <div>
                            <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>{d.label}</div>
                            <div style={{ fontSize: 13, fontWeight: 600, color: DK }}>{d.value}</div>
                        </div>
                    </div>
                ))}

                {/* Demographics */}
                {(lead.gender || lead.dob || lead.city || lead.district || lead.state || lead.pincode || lead.address) && (
                    <>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, margin: '2px 0' }}>
                            <div style={{ width: 3, height: 16, background: OR, borderRadius: 2 }} />
                            <span style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.6px' }}>Demographics</span>
                        </div>
                        {lead.gender && (
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                                <LuUser size={15} style={{ color: MUT, flexShrink: 0 }} />
                                <div>
                                    <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>Gender</div>
                                    <div style={{ fontSize: 13, fontWeight: 600, color: DK }}>{lead.gender.charAt(0).toUpperCase() + lead.gender.slice(1)}</div>
                                </div>
                            </div>
                        )}
                        {lead.dob && (
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                                <LuCalendar size={15} style={{ color: MUT, flexShrink: 0 }} />
                                <div>
                                    <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>Date of Birth</div>
                                    <div style={{ fontSize: 13, fontWeight: 600, color: DK }}>{lead.dob}</div>
                                </div>
                            </div>
                        )}
                        {(lead.city || lead.district || lead.state || lead.pincode) && (
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                                <LuMapPin size={15} style={{ color: MUT, flexShrink: 0 }} />
                                <div>
                                    <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>Location</div>
                                    <div style={{ fontSize: 13, fontWeight: 600, color: DK }}>
                                        {[lead.city, lead.district, lead.state].filter(Boolean).join(', ')}
                                        {lead.pincode && <span style={{ color: MUT, marginLeft: 4 }}>– {lead.pincode}</span>}
                                    </div>
                                </div>
                            </div>
                        )}
                        {lead.address && (
                            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 10, padding: '8px 12px', background: '#f8fafc', borderRadius: 10 }}>
                                <LuMapPin size={15} style={{ color: MUT, flexShrink: 0, marginTop: 2 }} />
                                <div>
                                    <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px' }}>Address</div>
                                    <div style={{ fontSize: 13, fontWeight: 600, color: DK, whiteSpace: 'pre-wrap' }}>{lead.address}</div>
                                </div>
                            </div>
                        )}
                    </>
                )}

                {/* Assign telecaller */}
                <div style={{ background: '#f8fafc', borderRadius: 10, padding: '12px' }}>
                    <div style={{ fontSize: 10, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.4px', marginBottom: 8, display: 'flex', alignItems: 'center', gap: 5 }}>
                        <LuUser size={12} style={{ color: OR }} /> Assigned Telecaller
                    </div>
                    <form onSubmit={submit}>
                        <label style={{ fontSize: 11, color: MUT, fontWeight: 600, display: 'block', marginBottom: 3 }}>Assignment Date</label>
                        <input type="date" className="form-control form-control-sm mb-2"
                            value={form.data.assignment_date} min={today} onChange={handleDateChange} />
                        <select className="form-select form-select-sm mb-1" required
                            value={form.data.assigned_to}
                            onChange={e => form.setData('assigned_to', e.target.value)}>
                            <option value="">Select Telecaller</option>
                            {availableTelecallers.map(t => (
                                <option key={t.id} value={t.id} disabled={lead.assigned_to === t.id}>
                                    {t.name}{lead.assigned_to === t.id ? ' (Current)' : ''}
                                </option>
                            ))}
                        </select>
                        {availableTelecallers.length === 0 && (
                            <p style={{ fontSize: 11, color: '#ef4444', margin: '2px 0 6px' }}>No telecallers available on this date.</p>
                        )}
                        {hiddenCount > 0 && (
                            <p style={{ fontSize: 11, color: '#f59e0b', margin: '2px 0 6px' }}>{hiddenCount} telecaller{hiddenCount > 1 ? 's' : ''} unavailable on this date.</p>
                        )}
                        <button style={{ width: '100%', marginTop: 6, padding: '8px', borderRadius: 9, border: 'none', background: OR, color: '#fff', fontSize: 13, fontWeight: 700, cursor: form.processing ? 'not-allowed' : 'pointer', fontFamily: 'inherit' }}
                            disabled={form.processing}>
                            {lead.assigned_user ? 'Reassign' : 'Assign'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}

// ─── WaDateSeparator ──────────────────────────────────────────────────────────
function WaDateSeparator({ dateStr }) {
    const today     = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    const d = new Date(dateStr);
    const label = d.toDateString() === today.toDateString()     ? 'Today'
                : d.toDateString() === yesterday.toDateString() ? 'Yesterday'
                : dateStr;
    return (
        <div style={{ display:'flex', alignItems:'center', gap:8, margin:'10px 4px', color:MUT, fontSize:12 }}>
            <div style={{ flex:1, height:1, background:BOR }} />
            <span style={{ background:'#f0f2f5', padding:'2px 12px', borderRadius:10, whiteSpace:'nowrap' }}>{label}</span>
            <div style={{ flex:1, height:1, background:BOR }} />
        </div>
    );
}

// ─── WaBubble ─────────────────────────────────────────────────────────────────
function WaBubble({ msg }) {
    const out = msg.direction !== 'inbound';
    const tickClass = msg.status === 'read' ? 'wa-tick-read'
        : msg.status === 'delivered' ? 'wa-tick-delivered' : 'wa-tick-sent';
    const tickChar = ['delivered', 'read'].includes(msg.status) ? '✓✓' : '✓';

    return (
        <div className={`wa-message ${out ? 'wa-outgoing' : 'wa-incoming'}`} data-msg-id={msg.id}>
            {msg.media_type && msg.media_url && (() => {
                if (msg.media_type === 'image') return (
                    <img src={msg.media_url} alt="" onClick={() => window.open(msg.media_url, '_blank')}
                        style={{ maxWidth: 200, maxHeight: 160, borderRadius: 6, display: 'block', marginBottom: 4, cursor: 'pointer' }} />
                );
                if (msg.media_type === 'audio') return (
                    <audio controls style={{ width: '100%', minWidth: 180, marginBottom: 4 }}>
                        <source src={msg.media_url} />
                    </audio>
                );
                if (msg.media_type === 'video') return (
                    <video controls style={{ maxWidth: 200, maxHeight: 160, borderRadius: 6, display: 'block', marginBottom: 4 }}>
                        <source src={msg.media_url} />
                    </video>
                );
                return (
                    <a href={msg.media_url} target="_blank" rel="noreferrer" download
                        style={{ display: 'flex', alignItems: 'center', gap: 6, background: 'rgba(0,0,0,.07)', borderRadius: 6, padding: '6px 10px', marginBottom: 4, textDecoration: 'none', color: 'inherit', fontSize: 12, fontWeight: 600 }}>
                        <LuFileText size={18} style={{ color: OR }} />
                        {msg.media_filename || 'File'}
                    </a>
                );
            })()}
            {msg.body && !['image', 'audio', 'video'].includes(msg.media_type || '') && (
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
function WaChat({ lead, initialMessages, templateName, urls, initialSessionActive }) {
    const chatBodyRef  = useRef(null);
    const lastIdRef    = useRef(initialMessages.length ? Math.max(...initialMessages.map(m => m.id)) : 0);
    const fileInputRef = useRef(null);

    const [messages,      setMessages]      = useState(initialMessages);
    const [text,          setText]          = useState('');
    const [pendingFile,   setPendingFile]   = useState(null);
    const [sending,       setSending]       = useState(false);
    const [waToasts,      setWaToasts]      = useState([]);
    const [sessionActive, setSessionActive] = useState(initialSessionActive ?? false);
    const [templateSent,  setTemplateSent]  = useState(false);

    useEffect(() => {
        if (chatBodyRef.current) chatBodyRef.current.scrollTop = chatBodyRef.current.scrollHeight;
    }, [messages]);

    const poll = useCallback(async () => {
        try {
            const res  = await fetch(`${urls.wa_fetch}?after=${lastIdRef.current}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.session_active !== undefined) {
                setSessionActive(data.session_active);
                if (data.session_active) setTemplateSent(false);
            }
            if (data.messages?.length) {
                const fresh = data.messages.filter(m => m.id > lastIdRef.current);
                if (fresh.length) {
                    setMessages(prev => [...prev, ...fresh]);
                    lastIdRef.current = Math.max(...fresh.map(m => m.id));
                    if (fresh.some(m => m.direction === 'inbound')) {
                        playChime();
                        addToast('New WhatsApp message', '#25D366');
                        setTemplateSent(false);
                    }
                }
            }
            if (data.statuses) {
                setMessages(prev => prev.map(m => { const s = data.statuses[m.id]; return s ? { ...m, status: s } : m; }));
            }
        } catch (_) {}
    }, [urls.wa_fetch]);

    useEffect(() => {
        const t = setInterval(poll, 7_000);
        const onWaMsg = (e) => { if (!e.detail || e.detail.lead_id == lead.id) poll(); };
        window.addEventListener('wa:message.new', onWaMsg);
        return () => { clearInterval(t); window.removeEventListener('wa:message.new', onWaMsg); };
    }, [poll]);

    function addToast(msg, color) {
        const id = Date.now();
        setWaToasts(prev => [...prev, { id, msg, color }]);
        setTimeout(() => setWaToasts(prev => prev.filter(t => t.id !== id)), 5000);
    }

    function clearFile() { setPendingFile(null); if (fileInputRef.current) fileInputRef.current.value = ''; }

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
            if (data.session_active !== undefined) setSessionActive(data.session_active);
            setText('');
            const newMsg = { id: data.message_id, body: data.message || body, direction: 'outbound', time: data.time || now12h(), status: 'sent' };
            setMessages(prev => [...prev, newMsg]);
            if (data.message_id > lastIdRef.current) lastIdRef.current = data.message_id;
            addToast('Message sent', OR);
        } catch (err) { addToast(err.message || 'Network error', '#ef4444'); }
        finally { setSending(false); }
    }

    async function sendMedia() {
        if (!pendingFile) return;
        setSending(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const fd   = new FormData();
            fd.append('_token', csrf); fd.append('file', pendingFile);
            if (text.trim()) fd.append('caption', text.trim());
            const res  = await fetch(urls.wa_media, { method: 'POST', headers: { Accept: 'application/json' }, body: fd });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { addToast(data.message || 'Upload failed', '#ef4444'); return; }
            clearFile(); setText('');
            setMessages(prev => [...prev, { id: data.message_id, body: data.message, direction: 'outbound', time: data.time || now12h(), status: 'sent', media_type: data.media_type, media_url: data.media_url, media_filename: data.media_filename }]);
            if (data.message_id > lastIdRef.current) lastIdRef.current = data.message_id;
            addToast('Media sent', OR);
        } catch (err) { addToast(err.message || 'Upload failed', '#ef4444'); }
        finally { setSending(false); }
    }

    async function sendTemplate() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const displayBody = `Hello ${lead.name}, thank you for your interest in our programs!`;
        try {
            const res  = await fetch(urls.wa_template, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ template_name: templateName, params: [lead.name], display_body: displayBody }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { addToast(data.message || 'Template failed', '#ef4444'); return; }
            setMessages(prev => [...prev, { id: data.message_id, body: data.message || displayBody, direction: 'outbound', time: data.time || now12h(), status: 'sent' }]);
            if (data.message_id > lastIdRef.current) lastIdRef.current = data.message_id;
            setTemplateSent(true);
            addToast('Welcome template sent — waiting for lead to reply', OR);
        } catch (err) { addToast(err.message || 'Network error', '#ef4444'); }
    }

    const fileLabel = pendingFile
        ? (pendingFile.size < 1_048_576 ? `${pendingFile.name} (${(pendingFile.size / 1024).toFixed(1)} KB)` : `${pendingFile.name} (${(pendingFile.size / 1_048_576).toFixed(1)} MB)`)
        : null;

    return (
        <div className="card border-0 shadow-sm mb-4" style={{ position: 'relative', borderRadius: 16, overflow: 'hidden' }}>
            <div className="card-body p-0">
                <div className="wa-chat-window">
                    <div className="wa-chat-header">
                        <div className="wa-user-block">
                            <div className="wa-avatar">{lead.name.charAt(0).toUpperCase()}</div>
                            <div><h6 className="mb-0">{lead.name}</h6><small>WhatsApp CRM Chat</small></div>
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            {sessionActive ? (
                                <span style={{ fontSize: 11, fontWeight: 700, padding: '3px 10px', borderRadius: 20, background: '#dcfce7', color: '#15803d', display: 'flex', alignItems: 'center', gap: 4 }}>
                                    <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#16a34a', display: 'inline-block' }}></span>
                                    24h session active
                                </span>
                            ) : templateSent ? (
                                <span style={{ fontSize: 11, fontWeight: 700, padding: '3px 10px', borderRadius: 20, background: '#FFF4EE', color: OR, display: 'flex', alignItems: 'center', gap: 4 }}>
                                    <span style={{ width: 7, height: 7, borderRadius: '50%', background: OR, display: 'inline-block' }}></span>
                                    Awaiting reply
                                </span>
                            ) : (
                                <span style={{ fontSize: 11, fontWeight: 700, padding: '3px 10px', borderRadius: 20, background: '#fef9c3', color: '#854d0e', display: 'flex', alignItems: 'center', gap: 4 }}>
                                    <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#f59e0b', display: 'inline-block' }}></span>
                                    No active session
                                </span>
                            )}
                            <span className="wa-live-dot"></span>
                        </div>
                    </div>

                    {!sessionActive && !templateSent && (
                        <div style={{ background: '#fffbeb', borderBottom: '1px solid #fde68a', padding: '7px 14px', fontSize: 12, color: '#92400e', display: 'flex', alignItems: 'center', gap: 6 }}>
                            <span style={{ fontSize: 14, color: '#f59e0b' }}>ℹ</span>
                            <span>
                                <strong>No active session</strong> — the lead hasn't messaged you yet.
                                Click <strong>Welcome</strong> below to send an opening template.
                            </span>
                        </div>
                    )}
                    {!sessionActive && templateSent && (
                        <div style={{ background: '#FFF4EE', borderBottom: `1px solid ${OR}30`, padding: '7px 14px', fontSize: 12, color: OR, display: 'flex', alignItems: 'center', gap: 6 }}>
                            <span>
                                <strong>Welcome template sent.</strong> Once the lead replies, a 24h session opens and you can send any message freely.
                            </span>
                        </div>
                    )}

                    <div id="waChatBody" className="wa-chat-body" ref={chatBodyRef}>
                        {messages.length === 0 && (
                            <div className="wa-message wa-incoming">
                                <p className="mb-1">No WhatsApp messages yet for this lead.</p>
                                <small>Start the conversation below</small>
                            </div>
                        )}
                        {(() => {
                            let lastDate = null;
                            return messages.flatMap(m => {
                                const items = [];
                                if (m.date && m.date !== lastDate) {
                                    lastDate = m.date;
                                    items.push(<WaDateSeparator key={`d-${m.date}`} dateStr={m.date} />);
                                }
                                items.push(<WaBubble key={m.id} msg={m} />);
                                return items;
                            });
                        })()}
                    </div>

                    <div className="wa-chat-footer">
                        <div className="wa-template-row">
                            <button type="button" className="wa-template-btn wa-tpl-direct-btn" onClick={sendTemplate}>Welcome</button>
                            <button type="button" className="wa-template-btn" onClick={() => setText('Reminder: your follow-up is scheduled. Please confirm your preferred time.')}>Follow-up</button>
                            <button type="button" className="wa-template-btn" onClick={() => setText('Please share your preferred course and we will guide you with next steps.')}>Course Info</button>
                        </div>

                        {pendingFile && (
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, background: '#FFF4EE', border: `1.5px solid ${OR}30`, borderRadius: 8, padding: '6px 10px', marginBottom: 6, fontSize: 12 }}>
                                <LuFileText size={18} style={{ color: OR }} />
                                <span style={{ flex: 1, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{fileLabel}</span>
                                <button type="button" onClick={clearFile} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#ef4444', padding: 0, display: 'flex' }}>
                                    <LuX size={16} />
                                </button>
                            </div>
                        )}

                        <form className="wa-composer-form" onSubmit={handleSubmit}>
                            <input type="file" ref={fileInputRef} style={{ display: 'none' }}
                                accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
                                onChange={e => setPendingFile(e.target.files[0] || null)} />
                            <button type="button" onClick={() => fileInputRef.current?.click()}
                                style={{ background: '#f1f5f9', border: `1.5px solid ${BOR}`, borderRadius: '50%', width: 38, height: 38, display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', flexShrink: 0 }} title="Attach file">
                                <LuFileText size={18} style={{ color: MUT }} />
                            </button>
                            <input className="form-control" type="text" autoComplete="off"
                                placeholder={pendingFile ? 'Add a caption (optional)…' : 'Type a WhatsApp message...'}
                                value={text} onChange={e => setText(e.target.value)} />
                            <button type="submit" className="btn btn-success" disabled={sending}>
                                {sending ? <span className="spinner-border spinner-border-sm"></span> : <LuSend size={18} />}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {waToasts.length > 0 && (
                <div style={{ position: 'absolute', bottom: 80, right: 12, zIndex: 10, pointerEvents: 'none', display: 'flex', flexDirection: 'column', gap: 6 }}>
                    {waToasts.map(t => (
                        <div key={t.id} style={{ background: WH, border: `1px solid ${BOR}`, borderLeft: `4px solid ${t.color}`, borderRadius: 10, padding: '8px 14px', boxShadow: '0 4px 16px rgba(0,0,0,.12)', fontSize: 13, fontWeight: 600, color: DK }}>
                            {t.msg}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

// ─── NoteForm ─────────────────────────────────────────────────────────────────
function NoteForm({ url }) {
    const form = useForm({ note: '' });
    function submit(e) {
        e.preventDefault();
        form.post(url, { onSuccess: () => form.reset('note') });
    }
    return (
        <div style={{ background: WH, border: `1px solid ${BOR}`, borderRadius: 14, padding: '18px 20px', marginBottom: 20, boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12 }}>
                <div style={{ width: 3, height: 22, background: OR, borderRadius: 2 }} />
                <LuFileText size={16} style={{ color: OR }} />
                <span style={{ fontWeight: 700, fontSize: 14, color: DK }}>Add Note</span>
            </div>
            <form onSubmit={submit}>
                <textarea className="form-control" rows={2} placeholder="Write a note about this lead..."
                    value={form.data.note} onChange={e => form.setData('note', e.target.value)} required
                    style={{ borderRadius: 10, border: `1.5px solid ${BOR}`, fontSize: 13 }} />
                <div className="d-flex justify-content-end mt-3">
                    <button style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '8px 18px', borderRadius: 9, border: 'none', background: DK, color: '#fff', fontSize: 13, fontWeight: 700, cursor: form.processing ? 'not-allowed' : 'pointer', fontFamily: 'inherit' }}
                        disabled={form.processing}>
                        <LuPlus size={15} />
                        {form.processing ? 'Saving…' : 'Add Note'}
                    </button>
                </div>
            </form>
        </div>
    );
}

// ─── Timeline ─────────────────────────────────────────────────────────────────
const TYPE_ICON_MAP = {
    call: LuPhone, note: LuFileText, whatsapp: LuMessageSquare,
    status_change: LuRefreshCw, followup: LuCalendar, assignment: LuUser,
};

const MISSED_STATUSES = new Set(['missed', 'no-answer', 'busy', 'failed', 'canceled']);

function getIconComp(item) {
    if (item.type === 'call') {
        if (MISSED_STATUSES.has(item.call_status)) return LuPhoneMissed ?? LuPhone;
        return item.direction === 'inbound' ? LuArrowDownLeft ?? LuPhone : LuArrowUpRight ?? LuPhone;
    }
    return TYPE_ICON_MAP[item.type] ?? LuFileText;
}

const DIRECTION_BADGE = {
    inbound:  { background: '#dcfce7', color: '#16a34a' },
    outbound: { background: '#FFF4EE', color: OR },
};
const WA_BADGE = {
    inbound:  { background: '#dcfce7', color: '#15803d' },
    outbound: { background: '#f0fdf4', color: '#16a34a' },
};

const FILTERS = [
    { key: 'all',      label: 'All' },
    { key: 'call',     label: 'Calls' },
    { key: 'whatsapp', label: 'WhatsApp' },
];

function Timeline({ activities }) {
    const [filter, setFilter] = useState('all');
    const visible = filter === 'all' ? activities : activities.filter(a => a.type === filter);

    return (
        <div style={{ background: WH, border: `1px solid ${BOR}`, borderRadius: 14, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
            <div style={{ padding: '16px 20px', borderBottom: `1px solid ${BOR}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 10 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <div style={{ width: 3, height: 22, background: OR, borderRadius: 2 }} />
                    <LuRefreshCw size={16} style={{ color: OR }} />
                    <span style={{ fontWeight: 700, fontSize: 14, color: DK }}>Activity Timeline</span>
                </div>
                <div style={{ display: 'flex', gap: 6 }}>
                    {FILTERS.map(f => (
                        <button key={f.key}
                            style={{
                                padding: '5px 14px', borderRadius: 20, fontSize: 12, fontWeight: 600, cursor: 'pointer', fontFamily: 'inherit',
                                background: filter === f.key ? OR : WH,
                                color: filter === f.key ? '#fff' : BDY,
                                border: `1.5px solid ${filter === f.key ? OR : BOR}`,
                            }}
                            onClick={() => setFilter(f.key)}>
                            {f.label}
                        </button>
                    ))}
                </div>
            </div>
            <div style={{ padding: '12px 20px' }}>
                {visible.length === 0 && (
                    <p style={{ color: MUT, textAlign: 'center', padding: '16px 0', fontSize: 13 }}>No activity yet.</p>
                )}
                {visible.map(a => {
                    const IconComp = getIconComp(a);
                    const isMissed = a.type === 'call' && MISSED_STATUSES.has(a.call_status);
                    return (
                        <div key={a.id} style={{ display: 'flex', gap: 12, marginBottom: 14 }}>
                            <div style={{ width: 32, height: 32, borderRadius: 10, background: isMissed ? '#fee2e2' : '#FFF4EE', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                <IconComp size={15} style={{ color: isMissed ? '#ef4444' : OR }} />
                            </div>
                            <div style={{ flex: 1, paddingTop: 4 }}>
                                <p style={{ margin: 0, fontSize: 13, color: BDY, marginBottom: a.direction ? 4 : 0 }}>{a.description}</p>
                                {a.type === 'call' && a.direction && (
                                    <span style={{ display: 'inline-block', fontSize: 11, fontWeight: 600, padding: '1px 8px', borderRadius: 20, marginBottom: 4, textTransform: 'uppercase', letterSpacing: '0.03em', ...(DIRECTION_BADGE[a.direction] ?? {}) }}>
                                        {a.direction}
                                    </span>
                                )}
                                {a.type === 'whatsapp' && a.direction && (
                                    <span style={{ display: 'inline-block', fontSize: 11, fontWeight: 600, padding: '1px 8px', borderRadius: 20, marginBottom: 4, textTransform: 'uppercase', letterSpacing: '0.03em', ...(WA_BADGE[a.direction] ?? {}) }}>
                                        {a.direction}
                                    </span>
                                )}
                                <small style={{ fontSize: 11, color: MUT }}>{a.user || '—'} | {a.time}</small>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

// ─── StatusPanel ──────────────────────────────────────────────────────────────
const STATUS_OPTIONS_MGR = [
    { value: 'new',            label: 'New',           bg: '#64748b' },
    { value: 'assigned',       label: 'Assigned',      bg: OR },
    { value: 'contacted',      label: 'Contacted',     bg: '#0ea5e9' },
    { value: 'interested',     label: 'Interested',    bg: '#10b981' },
    { value: 'follow_up',      label: 'Follow-up',     bg: '#f59e0b' },
    { value: 'not_interested', label: 'Not Interested',bg: '#ef4444' },
    { value: 'converted',      label: 'Converted',     bg: '#8b5cf6' },
];

function StatusPanel({ lead, url, courses, onClose }) {
    const form = useForm({ status: lead.status, quota: lead.quota ?? '', final_course_id: lead.final_course_id ?? lead.course_id ?? '', next_followup: '', followup_time: '', remarks: '' });
    const needsFollowup = form.data.status === 'follow_up';
    const needsQuota    = form.data.status === 'converted';
    const isSameCourse  = Number(form.data.final_course_id) === Number(lead.course_id);

    function submit(e) {
        e.preventDefault();
        form.post(url, { onSuccess: onClose });
    }

    return (
        <form onSubmit={submit} style={{ background: WH, border: `1px solid ${BOR}`, borderRadius: 12, boxShadow: '0 4px 16px rgba(0,0,0,.07)', padding: '16px', marginBottom: 16 }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                    <div style={{ width: 3, height: 20, background: OR, borderRadius: 2 }} />
                    <LuRefreshCw size={15} style={{ color: OR }} />
                    <span style={{ fontWeight: 600, fontSize: 13, color: DK }}>Update Status</span>
                    <span style={{ padding: '2px 8px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: '#f1f5f9', color: MUT }}>
                        Current: {STATUS_LABELS[lead.status] ?? lead.status}
                    </span>
                </div>
                <button type="button" onClick={onClose} style={{ background: 'none', border: 'none', cursor: 'pointer', color: MUT, lineHeight: 1, padding: 0 }}>
                    <LuX size={18} />
                </button>
            </div>

            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 7, marginBottom: 14 }}>
                {STATUS_OPTIONS_MGR.map(s => {
                    const active = form.data.status === s.value;
                    return (
                        <button key={s.value} type="button" onClick={() => form.setData('status', s.value)}
                            style={{
                                display: 'inline-flex', alignItems: 'center', gap: 5,
                                padding: '6px 14px', borderRadius: 20,
                                border: `1.5px solid ${s.bg}`,
                                background: active ? s.bg : `${s.bg}18`,
                                color: active ? '#fff' : s.bg,
                                fontSize: 12, fontWeight: 600, cursor: 'pointer', transition: 'all .15s',
                                fontFamily: 'inherit',
                            }}>
                            {active && <LuCheck size={12} />}
                            {s.label}
                        </button>
                    );
                })}
            </div>

            {needsQuota && (
                <div style={{ borderTop: `1px solid ${BOR}`, paddingTop: 12, marginBottom: 12 }}>
                    <label style={{ fontSize: 11, fontWeight: 600, color: MUT, display: 'block', marginBottom: 4 }}>
                        Quota <span style={{ color: '#ef4444' }}>*</span>
                    </label>
                    <div style={{ display: 'flex', gap: 8 }}>
                        {[{ value: 'management', label: 'Management' }, { value: 'counselling', label: 'Counselling' }].map(q => {
                            const active = form.data.quota === q.value;
                            return (
                                <button key={q.value} type="button" onClick={() => form.setData('quota', q.value)}
                                    style={{
                                        flex: 1, padding: '7px 0', borderRadius: 8, fontFamily: 'inherit',
                                        border: `1.5px solid ${active ? OR : BOR}`,
                                        background: active ? OR : WH,
                                        color: active ? '#fff' : MUT,
                                        fontSize: 12, fontWeight: 600, cursor: 'pointer',
                                    }}>
                                    {active && <LuCheck size={12} style={{ verticalAlign: 'middle', marginRight: 3 }} />}
                                    {q.label}
                                </button>
                            );
                        })}
                    </div>
                    {form.errors.quota && <div style={{ fontSize: 11, color: '#ef4444', marginTop: 4 }}>{form.errors.quota}</div>}
                    <div style={{ marginTop: 10 }}>
                        <label style={{ fontSize: 11, fontWeight: 600, color: MUT, display: 'block', marginBottom: 4 }}>
                            Final Selected Course <span style={{ color: '#ef4444' }}>*</span>
                        </label>
                        <div style={{ display: 'flex', gap: 8, marginBottom: 6 }}>
                            <button type="button" onClick={() => form.setData('final_course_id', lead.course_id ?? '')}
                                style={{ flex: 1, padding: '6px 0', borderRadius: 8, fontSize: 12, fontWeight: 600, cursor: 'pointer', fontFamily: 'inherit', border: `1.5px solid ${isSameCourse ? OR : BOR}`, background: isSameCourse ? OR : WH, color: isSameCourse ? '#fff' : MUT }}>
                                {isSameCourse && <LuCheck size={12} style={{ verticalAlign: 'middle', marginRight: 3 }} />}
                                Same as Enquired
                            </button>
                            <button type="button" onClick={() => { if (isSameCourse) form.setData('final_course_id', ''); }}
                                style={{ flex: 1, padding: '6px 0', borderRadius: 8, fontSize: 12, fontWeight: 600, cursor: 'pointer', fontFamily: 'inherit', border: `1.5px solid ${!isSameCourse ? OR : BOR}`, background: !isSameCourse ? OR + '18' : WH, color: !isSameCourse ? OR : MUT }}>
                                Different Course
                            </button>
                        </div>
                        {!isSameCourse && (
                            <select className="form-select form-select-sm" value={form.data.final_course_id}
                                onChange={e => form.setData('final_course_id', e.target.value)}>
                                <option value="">— Select course —</option>
                                {courses.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        )}
                        {form.errors.final_course_id && <div style={{ fontSize: 11, color: '#ef4444', marginTop: 4 }}>{form.errors.final_course_id}</div>}
                    </div>
                </div>
            )}

            {needsFollowup && (
                <div style={{ borderTop: `1px solid ${BOR}`, paddingTop: 12, marginBottom: 12 }}>
                    <div style={{ display: 'flex', gap: 10, marginBottom: 8 }}>
                        <div style={{ flex: 1 }}>
                            <label style={{ fontSize: 11, fontWeight: 600, color: MUT, display: 'block', marginBottom: 3 }}>Follow-up Date</label>
                            <input type="date" className="form-control form-control-sm" min={new Date().toISOString().slice(0, 10)} value={form.data.next_followup} onChange={e => form.setData('next_followup', e.target.value)} />
                        </div>
                        <div style={{ width: 110 }}>
                            <label style={{ fontSize: 11, fontWeight: 600, color: MUT, display: 'block', marginBottom: 3 }}>Time</label>
                            <input type="time" className="form-control form-control-sm" value={form.data.followup_time} onChange={e => form.setData('followup_time', e.target.value)} />
                        </div>
                    </div>
                    <textarea className="form-control form-control-sm" rows={2} placeholder="Remarks (optional)…" value={form.data.remarks} onChange={e => form.setData('remarks', e.target.value)} />
                </div>
            )}

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
                <button type="button" onClick={onClose}
                    style={{ padding: '7px 16px', borderRadius: 8, background: WH, border: `1.5px solid ${BOR}`, color: BDY, fontSize: 13, fontWeight: 600, cursor: 'pointer', fontFamily: 'inherit' }}>Cancel</button>
                <button type="submit"
                    style={{ padding: '7px 16px', borderRadius: 8, border: 'none', background: OR, color: '#fff', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit', opacity: (form.processing || form.data.status === lead.status || (needsQuota && (!form.data.quota || !form.data.final_course_id))) ? .6 : 1 }}
                    disabled={form.processing || form.data.status === lead.status || (needsQuota && (!form.data.quota || !form.data.final_course_id))}>
                    {form.processing ? 'Saving…' : 'Apply Status'}
                </button>
            </div>
        </form>
    );
}

// ─── CallOutcomeModal ─────────────────────────────────────────────────────────
const OUTCOMES = [
    { value: 'interested',      label: 'Interested',               cls: 'btn-success' },
    { value: 'not_interested',  label: 'Not Interested',           cls: 'btn-danger' },
    { value: 'call_back_later', label: 'Call Back Later',          cls: 'btn-warning text-dark' },
    { value: 'switched_off',    label: 'Switched Off / No Answer', cls: 'btn-secondary' },
    { value: 'wrong_number',    label: 'Wrong Number',             cls: 'btn-outline-secondary' },
];

function CallOutcomeModal({ url }) {
    const modalRef   = useRef(null);
    const callLogRef = useRef(null);

    useEffect(() => {
        function onEnded(e) {
            const id = e.detail?.callLogId;
            if (!id) return;
            callLogRef.current = id;
            if (modalRef.current && window.bootstrap?.Modal) new window.bootstrap.Modal(modalRef.current).show();
        }
        document.addEventListener('gc:callEnded', onEnded);
        return () => document.removeEventListener('gc:callEnded', onEnded);
    }, []);

    async function recordOutcome(outcome) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        try {
            await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ call_log_id: callLogRef.current, outcome }),
            });
        } catch (_) {}
        window.bootstrap?.Modal.getInstance(modalRef.current)?.hide();
    }

    return (
        <div className="modal fade" id="callOutcomeModal" ref={modalRef} tabIndex={-1} data-bs-backdrop="static" data-bs-keyboard="false">
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content">
                    <div className="modal-header border-0 pb-0">
                        <h5 className="modal-title fw-bold">How did the call go?</h5>
                    </div>
                    <div className="modal-body pt-2">
                        <p className="text-muted small mb-3">Select the outcome to log it against this lead.</p>
                        <div className="d-grid gap-2">
                            {OUTCOMES.map(o => (
                                <button key={o.value} className={`btn ${o.cls}`} onClick={() => recordOutcome(o.value)}>{o.label}</button>
                            ))}
                        </div>
                    </div>
                    <div className="modal-footer border-0 pt-0">
                        <button type="button" className="btn btn-link text-muted btn-sm" data-bs-dismiss="modal">Skip</button>
                    </div>
                </div>
            </div>
        </div>
    );
}

// ─── MeetDropdown ─────────────────────────────────────────────────────────────
function MeetDropdown({ startUrl, lead, onCreated }) {
    const [loading, setLoading] = useState(false);
    const [toast,   setToast]   = useState(null);
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function startNow() {
        setLoading(true); setToast(null);
        try {
            const res  = await fetch(startUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { setToast({ ok: false, msg: data.error || 'Failed to create meeting.' }); return; }
            onCreated(data.meeting);
            const ch = [data.wa_sent ? 'WhatsApp' : null, data.email_sent ? 'Email' : null].filter(Boolean).join(' & ') || 'none';
            setToast({ ok: true, msg: `Meet created! Notified via ${ch}.` });
            setTimeout(() => setToast(null), 5000);
            if (data.meeting?.meeting_link) window.open(data.meeting.meeting_link, '_blank');
        } catch (err) {
            setToast({ ok: false, msg: err.message || 'Network error.' });
        } finally { setLoading(false); }
    }

    return (
        <div style={{ position: 'relative' }}>
            <div className="dropdown">
                <button className="ab-btn ab-meet" data-bs-toggle="dropdown" disabled={loading}>
                    {loading ? <span className="spinner-border spinner-border-sm" style={{ width:14, height:14 }} /> : <LuVideo size={16} />}
                    Google Meet
                    <LuChevronDown size={14} style={{ opacity: 0.7 }} />
                </button>
                <ul className="dropdown-menu ab-dropdown shadow-sm">
                    <li>
                        <button className="dropdown-item d-flex align-items-center gap-2" onClick={startNow} disabled={loading}>
                            <LuVideo size={16} style={{ color: OR }} />
                            <div><div style={{ fontWeight: 600, fontSize: 13 }}>Start Now</div><div style={{ fontSize: 11, color: MUT }}>Create & open instantly</div></div>
                        </button>
                    </li>
                    <li><hr className="dropdown-divider my-1" /></li>
                    <li>
                        <button className="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#scheduleMeetModal">
                            <LuCalendar size={16} style={{ color: '#10b981' }} />
                            <div><div style={{ fontWeight: 600, fontSize: 13 }}>Schedule</div><div style={{ fontSize: 11, color: MUT }}>Pick a date & time</div></div>
                        </button>
                    </li>
                </ul>
            </div>
            {toast && (
                <div style={{ position: 'absolute', top: 44, left: 0, zIndex: 99, minWidth: 270, background: WH, border: `1.5px solid ${toast.ok ? '#bbf7d0' : '#fecaca'}`, borderRadius: 10, padding: '8px 12px', boxShadow: '0 4px 16px rgba(0,0,0,.12)', fontSize: 12, color: toast.ok ? '#15803d' : '#dc2626', fontWeight: 600 }}>
                    {toast.ok ? <LuCheck size={13} style={{ verticalAlign: 'middle', marginRight: 4 }} /> : <LuX size={13} style={{ verticalAlign: 'middle', marginRight: 4 }} />}
                    {toast.msg}
                </div>
            )}
        </div>
    );
}

// ─── ZoomDropdown ─────────────────────────────────────────────────────────────
function ZoomDropdown({ startUrl, lead, onCreated }) {
    const [loading, setLoading] = useState(false);
    const [toast,   setToast]   = useState(null);
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function startNow() {
        setLoading(true); setToast(null);
        try {
            const res  = await fetch(startUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { setToast({ ok: false, msg: data.error || 'Failed to create Zoom meeting.' }); return; }
            onCreated(data.meeting);
            const ch = data.wa_sent ? 'WhatsApp' : 'none';
            setToast({ ok: true, msg: `Zoom created! Notified via ${ch}.` });
            setTimeout(() => setToast(null), 5000);
            if (data.meeting?.meeting_link) window.open(data.meeting.meeting_link, '_blank');
        } catch (err) {
            setToast({ ok: false, msg: err.message || 'Network error.' });
        } finally { setLoading(false); }
    }

    return (
        <div style={{ position: 'relative' }}>
            <div className="dropdown">
                <button className="ab-btn ab-zoom" data-bs-toggle="dropdown" disabled={loading}>
                    {loading ? <span className="spinner-border spinner-border-sm" style={{ width:14, height:14 }} /> : <LuVideo size={16} />}
                    Zoom
                    <LuChevronDown size={14} style={{ opacity: 0.7 }} />
                </button>
                <ul className="dropdown-menu ab-dropdown shadow-sm">
                    <li>
                        <button className="dropdown-item d-flex align-items-center gap-2" onClick={startNow} disabled={loading}>
                            <LuVideo size={16} style={{ color: '#2D8CFF' }} />
                            <div><div style={{ fontWeight: 600, fontSize: 13 }}>Start Now</div><div style={{ fontSize: 11, color: MUT }}>Create & open instantly</div></div>
                        </button>
                    </li>
                    <li><hr className="dropdown-divider my-1" /></li>
                    <li>
                        <button className="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#scheduleZoomModal">
                            <LuCalendar size={16} style={{ color: '#10b981' }} />
                            <div><div style={{ fontWeight: 600, fontSize: 13 }}>Schedule</div><div style={{ fontSize: 11, color: MUT }}>Pick a date & time</div></div>
                        </button>
                    </li>
                </ul>
            </div>
            {toast && (
                <div style={{ position: 'absolute', top: 44, left: 0, zIndex: 99, minWidth: 270, background: WH, border: `1.5px solid ${toast.ok ? '#bbf7d0' : '#fecaca'}`, borderRadius: 10, padding: '8px 12px', boxShadow: '0 4px 16px rgba(0,0,0,.12)', fontSize: 12, color: toast.ok ? '#15803d' : '#dc2626', fontWeight: 600 }}>
                    {toast.ok ? <LuCheck size={13} style={{ verticalAlign: 'middle', marginRight: 4 }} /> : <LuX size={13} style={{ verticalAlign: 'middle', marginRight: 4 }} />}
                    {toast.msg}
                </div>
            )}
        </div>
    );
}

// ─── EmailModal ───────────────────────────────────────────────────────────────
function EmailModal({ url, lead, templates }) {
    const [mode,    setMode]    = useState('compose');
    const [tplId,   setTplId]   = useState('');
    const [subject, setSubject] = useState('');
    const [body,    setBody]    = useState('');
    const [files,   setFiles]   = useState([]);
    const [loading, setLoading] = useState(false);
    const [error,   setError]   = useState('');
    const [success, setSuccess] = useState(false);
    const fileRef = useRef(null);
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function reset() {
        setMode('compose'); setTplId(''); setSubject(''); setBody('');
        setFiles([]); setLoading(false); setError(''); setSuccess(false);
        if (fileRef.current) fileRef.current.value = '';
    }

    function selectTemplate(id) {
        setTplId(id);
        const tpl = templates.find(t => String(t.id) === String(id));
        if (tpl) { setSubject(tpl.subject || ''); setBody(tpl.body || ''); }
    }

    async function handleSend(e) {
        e.preventDefault();
        if (!subject.trim()) { setError('Subject is required.'); return; }
        if (!body.trim())    { setError('Message body is required.'); return; }
        setLoading(true); setError('');
        const fd = new FormData();
        fd.append('subject', subject);
        fd.append('body', body);
        files.forEach(f => fd.append('attachments[]', f));
        try {
            const res  = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, body: fd });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { setError(data.error || data.message || 'Failed to send email.'); return; }
            setSuccess(true);
            setTimeout(() => { reset(); window.bootstrap?.Modal.getInstance(document.getElementById('emailModal'))?.hide(); }, 2000);
        } catch (err) { setError(err.message || 'Network error.'); }
        finally { setLoading(false); }
    }

    const hasEmail = !!lead.email;

    return (
        <div className="modal fade" id="emailModal" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered modal-lg">
                <form onSubmit={handleSend}>
                    <div className="modal-content" style={{ borderRadius: 16, border: 'none' }}>
                        <div className="modal-header" style={{ borderBottom: `1px solid ${BOR}`, padding: '20px 24px 16px' }}>
                            <div className="d-flex align-items-center gap-2">
                                <div style={{ width: 34, height: 34, borderRadius: 10, background: '#FFF4EE', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    <LuMail size={18} style={{ color: OR }} />
                                </div>
                                <div>
                                    <h5 className="modal-title fw-bold mb-0">Send Email</h5>
                                    <div style={{ fontSize: 11.5, color: MUT }}>
                                        To: {hasEmail ? lead.email : <span style={{ color: '#ef4444' }}>No email on file</span>}
                                    </div>
                                </div>
                            </div>
                            <button type="button" className="btn-close" data-bs-dismiss="modal" onClick={reset}></button>
                        </div>

                        <div className="modal-body" style={{ padding: '20px 24px' }}>
                            {!hasEmail && (
                                <div className="alert alert-warning py-2 small mb-3">
                                    This lead has no email address — email cannot be sent.
                                </div>
                            )}
                            {success && (
                                <div style={{ background: '#f0fdf4', border: '1px solid #bbf7d0', borderRadius: 10, padding: '12px 14px', marginBottom: 16 }}>
                                    <div style={{ fontWeight: 700, fontSize: 13, color: '#15803d' }}>
                                        <LuCheck size={15} style={{ verticalAlign: 'middle', marginRight: 4 }} />
                                        Email sent successfully!
                                    </div>
                                </div>
                            )}
                            {error && <div className="alert alert-danger py-2 small mb-3">{error}</div>}

                            {templates.length > 0 && (
                                <div className="mb-3 d-flex gap-2">
                                    {[{ key: 'compose', label: 'Compose' }, { key: 'template', label: 'Use Template' }].map(({ key, label }) => (
                                        <button key={key} type="button"
                                            onClick={() => { setMode(key); if (key === 'compose') { setTplId(''); setSubject(''); setBody(''); } }}
                                            style={{ fontSize: 12.5, padding: '5px 16px', borderRadius: 20, cursor: 'pointer', fontFamily: 'inherit', border: `1.5px solid ${mode === key ? OR : BOR}`, background: mode === key ? '#FFF4EE' : WH, color: mode === key ? OR : MUT, fontWeight: mode === key ? 700 : 500 }}>
                                            {label}
                                        </button>
                                    ))}
                                </div>
                            )}

                            {mode === 'template' && (
                                <div className="mb-3">
                                    <label className="form-label fw-semibold" style={{ fontSize: 13 }}>Select Template</label>
                                    <select className="form-select" style={{ borderRadius: 10, fontSize: 13 }} value={tplId} onChange={e => selectTemplate(e.target.value)}>
                                        <option value="">— Choose a template —</option>
                                        {templates.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                    </select>
                                </div>
                            )}

                            <div className="mb-3">
                                <label className="form-label fw-semibold" style={{ fontSize: 13 }}>Subject</label>
                                <input type="text" className="form-control" style={{ borderRadius: 10, fontSize: 13 }} placeholder="Email subject…" value={subject} onChange={e => setSubject(e.target.value)} maxLength={255} />
                            </div>

                            <div className="mb-3">
                                <label className="form-label fw-semibold" style={{ fontSize: 13 }}>Message</label>
                                <textarea className="form-control" rows={8} style={{ borderRadius: 10, fontSize: 13, resize: 'vertical', fontFamily: 'inherit' }} placeholder="Write your message here…" value={body} onChange={e => setBody(e.target.value)} />
                                {mode === 'template' && tplId && <div style={{ fontSize: 11, color: MUT, marginTop: 4 }}>Template loaded — you can edit the content before sending.</div>}
                            </div>

                            <div className="mb-1">
                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 4 }}>
                                    <label className="form-label fw-semibold mb-0" style={{ fontSize: 13 }}>Attachments <span style={{ fontWeight: 400, color: MUT }}>(optional)</span></label>
                                    {files.length > 0 && (
                                        <button type="button" onClick={() => { setFiles([]); if (fileRef.current) fileRef.current.value = ''; }}
                                            style={{ fontSize: 12, color: '#ef4444', background: 'none', border: 'none', cursor: 'pointer', padding: 0, fontWeight: 600 }}>Clear all</button>
                                    )}
                                </div>
                                <div style={{ border: `1.5px dashed ${BOR}`, borderRadius: 10, padding: '10px 14px', background: '#f8fafc', cursor: 'pointer', minHeight: 72, display: 'flex', flexDirection: 'column', justifyContent: 'center' }}
                                    onClick={() => fileRef.current?.click()}>
                                    <input type="file" multiple ref={fileRef} className="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.txt" onChange={e => setFiles(Array.from(e.target.files))} />
                                    {files.length === 0 ? (
                                        <div style={{ textAlign: 'center', color: MUT, fontSize: 12.5 }}>
                                            <LuFileText size={20} style={{ display: 'block', margin: '0 auto 4px' }} />
                                            Click to attach files — PDF, DOC, images, ZIP (max 10 MB each)
                                        </div>
                                    ) : (
                                        <div>
                                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, alignItems: 'center', marginBottom: 6 }}>
                                                {files.map((f, i) => (
                                                    <span key={i} style={{ fontSize: 11.5, padding: '3px 10px', borderRadius: 20, background: '#FFF4EE', color: OR, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 4 }}>
                                                        <LuFileText size={12} /> {f.name}
                                                    </span>
                                                ))}
                                            </div>
                                            <div style={{ fontSize: 11, color: MUT }}>Click to add more files</div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="modal-footer" style={{ borderTop: `1px solid ${BOR}`, padding: '16px 24px' }}>
                            <button type="button" className="btn btn-light" style={{ borderRadius: 8 }} data-bs-dismiss="modal" onClick={reset}>Cancel</button>
                            <button type="submit" disabled={loading || !hasEmail || success}
                                style={{ borderRadius: 8, background: OR, color: '#fff', border: 'none', padding: '8px 18px', fontWeight: 600, fontSize: 13.5, fontFamily: 'inherit' }}>
                                {loading ? <><span className="spinner-border spinner-border-sm me-1"></span>Sending…</> : <><LuMail size={16} style={{ verticalAlign: 'middle', marginRight: 6 }} />Send Email</>}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}

// ─── ScheduleMeetModal ────────────────────────────────────────────────────────
function ScheduleMeetModal({ url, lead, onCreated }) {
    const [loading, setLoading] = useState(false); const [meetingTime, setMeetingTime] = useState('');
    const [duration, setDuration] = useState(60); const [title, setTitle] = useState('');
    const [notes, setNotes] = useState(''); const [error, setError] = useState(''); const [result, setResult] = useState(null);
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function reset() { setMeetingTime(''); setDuration(60); setTitle(''); setNotes(''); setError(''); setResult(null); }

    async function handleSubmit(e) {
        e.preventDefault();
        if (!meetingTime) { setError('Please select a date and time.'); return; }
        setLoading(true); setError(''); setResult(null);
        try {
            const res  = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, body: JSON.stringify({ meeting_time: meetingTime, duration, title, notes }) });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { setError(data.error || data.message || 'Failed to schedule meeting.'); return; }
            onCreated(data.meeting); setResult({ email_sent: data.email_sent, wa_sent: data.wa_sent });
            setTimeout(() => { reset(); window.bootstrap?.Modal.getInstance(document.getElementById('scheduleMeetModal'))?.hide(); }, 2200);
        } catch (err) { setError(err.message || 'Network error.'); } finally { setLoading(false); }
    }

    const minDateTime = new Date(Date.now() + 5 * 60000).toISOString().slice(0, 16);
    const hasEmail    = !!lead.email;

    return (
        <div className="modal fade" id="scheduleMeetModal" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
                <form onSubmit={handleSubmit}>
                    <div className="modal-content" style={{ borderRadius: 16, border: 'none' }}>
                        <div className="modal-header" style={{ borderBottom: `1px solid ${BOR}`, padding: '20px 24px 16px' }}>
                            <div className="d-flex align-items-center gap-2">
                                <div style={{ width: 34, height: 34, borderRadius: 10, background: '#FFF4EE', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    <LuVideo size={18} style={{ color: OR }} />
                                </div>
                                <h5 className="modal-title fw-bold mb-0">Schedule Google Meet</h5>
                            </div>
                            <button type="button" className="btn-close" data-bs-dismiss="modal" onClick={reset}></button>
                        </div>
                        <div className="modal-body" style={{ padding: '20px 24px' }}>
                            {result && (
                                <div style={{ background: '#f0fdf4', border: '1px solid #bbf7d0', borderRadius: 10, padding: '12px 14px', marginBottom: 16 }}>
                                    <div style={{ fontWeight: 700, fontSize: 13, color: '#15803d', marginBottom: 6 }}><LuCheck size={15} style={{ verticalAlign: 'middle', marginRight: 4 }} />Meeting scheduled successfully!</div>
                                    <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                                        <span style={{ fontSize: 11.5, padding: '2px 10px', borderRadius: 20, background: result.wa_sent ? '#dcfce7' : '#fef9c3', color: result.wa_sent ? '#15803d' : '#854d0e', fontWeight: 600 }}>WhatsApp {result.wa_sent ? 'sent' : 'not sent'}</span>
                                        <span style={{ fontSize: 11.5, padding: '2px 10px', borderRadius: 20, background: result.email_sent ? '#dcfce7' : '#fef9c3', color: result.email_sent ? '#15803d' : '#854d0e', fontWeight: 600 }}>Calendar invite {result.email_sent ? 'sent' : 'skipped (no email)'}</span>
                                    </div>
                                </div>
                            )}
                            {error && <div className="alert alert-danger py-2 small mb-3">{error}</div>}
                            <div className="mb-3"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Meeting Title</label><input type="text" className="form-control" style={{ borderRadius: 10, fontSize: 13 }} placeholder={`Meeting with ${lead.name}`} value={title} onChange={e => setTitle(e.target.value)} maxLength={200} /></div>
                            <div className="row g-3 mb-3">
                                <div className="col-7"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Date &amp; Time</label><input type="datetime-local" className="form-control" style={{ borderRadius: 10, fontSize: 13 }} min={minDateTime} value={meetingTime} onChange={e => setMeetingTime(e.target.value)} required /></div>
                                <div className="col-5"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Duration</label><select className="form-select" style={{ borderRadius: 10, fontSize: 13 }} value={duration} onChange={e => setDuration(Number(e.target.value))}><option value={15}>15 min</option><option value={30}>30 min</option><option value={45}>45 min</option><option value={60}>1 hour</option><option value={90}>1.5 hours</option><option value={120}>2 hours</option></select></div>
                            </div>
                            <div className="mb-3"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Notes / Agenda (optional)</label><textarea className="form-control" rows={2} style={{ borderRadius: 10, fontSize: 13, resize: 'none' }} placeholder="Agenda, topics to discuss, etc." value={notes} onChange={e => setNotes(e.target.value)} maxLength={1000} /></div>
                            <div style={{ background: '#f8fafc', border: `1px solid ${BOR}`, borderRadius: 10, padding: '10px 14px', fontSize: 12, color: BDY }}>
                                <div style={{ fontWeight: 700, marginBottom: 4 }}>Notifications sent automatically:</div>
                                <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                                    <span>WhatsApp message with Meet link to {lead.phone}</span>
                                    <span>Google Calendar invite to {hasEmail ? lead.email : <span style={{ color: '#ef4444' }}>no email on file</span>}</span>
                                </div>
                            </div>
                        </div>
                        <div className="modal-footer" style={{ borderTop: `1px solid ${BOR}`, padding: '16px 24px' }}>
                            <button type="button" className="btn btn-light" data-bs-dismiss="modal" style={{ borderRadius: 8 }} onClick={reset}>Cancel</button>
                            <button type="submit" style={{ borderRadius: 8, background: OR, color: '#fff', border: 'none', padding: '8px 18px', fontWeight: 600, fontFamily: 'inherit' }} disabled={loading || !!result}>
                                {loading ? <><span className="spinner-border spinner-border-sm me-1"></span>Scheduling…</> : <><LuVideo size={16} style={{ verticalAlign: 'middle', marginRight: 6 }} />Schedule Meet</>}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}

// ─── ScheduleZoomModal ────────────────────────────────────────────────────────
function ScheduleZoomModal({ url, lead, onCreated }) {
    const [loading, setLoading] = useState(false); const [meetingTime, setMeetingTime] = useState('');
    const [duration, setDuration] = useState(60); const [title, setTitle] = useState('');
    const [notes, setNotes] = useState(''); const [error, setError] = useState(''); const [result, setResult] = useState(null);
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function reset() { setMeetingTime(''); setDuration(60); setTitle(''); setNotes(''); setError(''); setResult(null); }

    async function handleSubmit(e) {
        e.preventDefault();
        if (!meetingTime) { setError('Please select a date and time.'); return; }
        setLoading(true); setError(''); setResult(null);
        try {
            const res  = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, body: JSON.stringify({ meeting_time: meetingTime, duration, title, notes }) });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { setError(data.error || data.message || 'Failed to schedule Zoom meeting.'); return; }
            onCreated(data.meeting); setResult({ wa_sent: data.wa_sent });
            setTimeout(() => { reset(); window.bootstrap?.Modal.getInstance(document.getElementById('scheduleZoomModal'))?.hide(); }, 2200);
        } catch (err) { setError(err.message || 'Network error.'); } finally { setLoading(false); }
    }

    const minDateTime = new Date(Date.now() + 5 * 60000).toISOString().slice(0, 16);

    return (
        <div className="modal fade" id="scheduleZoomModal" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
                <form onSubmit={handleSubmit}>
                    <div className="modal-content" style={{ borderRadius: 16, border: 'none' }}>
                        <div className="modal-header" style={{ borderBottom: `1px solid ${BOR}`, padding: '20px 24px 16px' }}>
                            <div className="d-flex align-items-center gap-2">
                                <div style={{ width: 34, height: 34, borderRadius: 10, background: '#dbeafe', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    <LuVideo size={18} style={{ color: '#2D8CFF' }} />
                                </div>
                                <h5 className="modal-title fw-bold mb-0">Schedule Zoom Meeting</h5>
                            </div>
                            <button type="button" className="btn-close" data-bs-dismiss="modal" onClick={reset}></button>
                        </div>
                        <div className="modal-body" style={{ padding: '20px 24px' }}>
                            {result && (
                                <div style={{ background: '#f0fdf4', border: '1px solid #bbf7d0', borderRadius: 10, padding: '12px 14px', marginBottom: 16 }}>
                                    <div style={{ fontWeight: 700, fontSize: 13, color: '#15803d', marginBottom: 6 }}><LuCheck size={15} style={{ verticalAlign: 'middle', marginRight: 4 }} />Zoom meeting scheduled successfully!</div>
                                    <span style={{ fontSize: 11.5, padding: '2px 10px', borderRadius: 20, background: result.wa_sent ? '#dcfce7' : '#fef9c3', color: result.wa_sent ? '#15803d' : '#854d0e', fontWeight: 600 }}>WhatsApp {result.wa_sent ? 'sent' : 'not sent'}</span>
                                </div>
                            )}
                            {error && <div className="alert alert-danger py-2 small mb-3">{error}</div>}
                            <div className="mb-3"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Meeting Title</label><input type="text" className="form-control" style={{ borderRadius: 10, fontSize: 13 }} placeholder={`Zoom with ${lead.name}`} value={title} onChange={e => setTitle(e.target.value)} maxLength={200} /></div>
                            <div className="row g-3 mb-3">
                                <div className="col-7"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Date &amp; Time</label><input type="datetime-local" className="form-control" style={{ borderRadius: 10, fontSize: 13 }} min={minDateTime} value={meetingTime} onChange={e => setMeetingTime(e.target.value)} required /></div>
                                <div className="col-5"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Duration</label><select className="form-select" style={{ borderRadius: 10, fontSize: 13 }} value={duration} onChange={e => setDuration(Number(e.target.value))}><option value={15}>15 min</option><option value={30}>30 min</option><option value={45}>45 min</option><option value={60}>1 hour</option><option value={90}>1.5 hours</option><option value={120}>2 hours</option></select></div>
                            </div>
                            <div className="mb-3"><label className="form-label fw-semibold" style={{ fontSize: 13 }}>Notes / Agenda (optional)</label><textarea className="form-control" rows={2} style={{ borderRadius: 10, fontSize: 13, resize: 'none' }} placeholder="Agenda, topics to discuss, etc." value={notes} onChange={e => setNotes(e.target.value)} maxLength={1000} /></div>
                            <div style={{ background: '#eff6ff', border: '1px solid #bfdbfe', borderRadius: 10, padding: '10px 14px', fontSize: 12, color: '#1e40af' }}>
                                <div style={{ fontWeight: 700, marginBottom: 4 }}>Notifications sent automatically:</div>
                                <span>WhatsApp message with Zoom link to {lead.phone}</span>
                            </div>
                        </div>
                        <div className="modal-footer" style={{ borderTop: `1px solid ${BOR}`, padding: '16px 24px' }}>
                            <button type="button" className="btn btn-light" data-bs-dismiss="modal" style={{ borderRadius: 8 }} onClick={reset}>Cancel</button>
                            <button type="submit" style={{ borderRadius: 8, background: '#2D8CFF', color: '#fff', border: 'none', padding: '8px 18px', fontWeight: 600, fontFamily: 'inherit' }} disabled={loading || !!result}>
                                {loading ? <><span className="spinner-border spinner-border-sm me-1"></span>Scheduling…</> : <><LuVideo size={16} style={{ verticalAlign: 'middle', marginRight: 6 }} />Schedule Zoom</>}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}

// ─── MeetHistory ──────────────────────────────────────────────────────────────
const STATUS_COLOR = {
    scheduled: { bg: '#FFF4EE', color: OR },
    completed:  { bg: '#f0fdf4', color: '#16a34a' },
    missed:     { bg: '#fef2f2', color: '#ef4444' },
};

function MeetHistory({ meetings, statusUrl, onStatusChanged }) {
    if (!meetings || meetings.length === 0) {
        return (
            <div style={{ textAlign: 'center', padding: '28px 0', color: MUT, fontSize: 13 }}>
                <LuVideo size={36} style={{ opacity: .25, display: 'block', margin: '0 auto 6px' }} />
                No meetings scheduled yet.
            </div>
        );
    }

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function changeStatus(id, status) {
        const url = statusUrl.replace('__ID__', id);
        try {
            const res = await fetch(url, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, body: JSON.stringify({ status }) });
            if (res.ok) onStatusChanged(id, status);
        } catch (_) {}
    }

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, padding: '14px 18px' }}>
            {meetings.map(m => {
                const sc = STATUS_COLOR[m.status] ?? STATUS_COLOR.scheduled;
                return (
                    <div key={m.id} style={{ display: 'flex', alignItems: 'flex-start', gap: 12, padding: '12px 14px', borderRadius: 12, background: '#f8fafc', border: `1px solid ${BOR}` }}>
                        <div style={{ width: 36, height: 36, borderRadius: 10, background: sc.bg, color: sc.color, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                            <LuVideo size={18} />
                        </div>
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 2 }}>
                                <span style={{ fontSize: 13, fontWeight: 700, color: DK }}>{m.title}</span>
                                {m.meeting_type === 'zoom'
                                    ? <span style={{ fontSize: 10, fontWeight: 700, padding: '1px 7px', borderRadius: 20, background: '#dbeafe', color: '#2D8CFF' }}>Zoom</span>
                                    : <span style={{ fontSize: 10, fontWeight: 700, padding: '1px 7px', borderRadius: 20, background: '#FFF4EE', color: OR }}>Google</span>
                                }
                            </div>
                            <div style={{ fontSize: 12, color: MUT }}>{m.meeting_time} &bull; {m.duration} min</div>
                            {m.notes && <div style={{ fontSize: 12, color: MUT, marginTop: 2 }}>{m.notes}</div>}
                            {m.meeting_link && (
                                <a href={m.meeting_link} target="_blank" rel="noreferrer"
                                    style={{ fontSize: 12, color: OR, fontWeight: 600, display: 'inline-flex', alignItems: 'center', gap: 3, marginTop: 4, textDecoration: 'none' }}>
                                    <LuExternalLink size={13} /> Join Meeting
                                </a>
                            )}
                        </div>
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 6, flexShrink: 0 }}>
                            <span style={{ fontSize: 10.5, fontWeight: 700, padding: '3px 10px', borderRadius: 20, background: sc.bg, color: sc.color, textTransform: 'capitalize' }}>{m.status}</span>
                            {m.status === 'scheduled' && (
                                <div style={{ display: 'flex', gap: 4 }}>
                                    <button onClick={() => changeStatus(m.id, 'completed')} style={{ fontSize: 11, padding: '2px 8px', borderRadius: 6, border: '1px solid #10b981', background: '#f0fdf4', color: '#16a34a', cursor: 'pointer', fontFamily: 'inherit' }}>Done</button>
                                    <button onClick={() => changeStatus(m.id, 'missed')} style={{ fontSize: 11, padding: '2px 8px', borderRadius: 6, border: '1px solid #ef4444', background: '#fef2f2', color: '#ef4444', cursor: 'pointer', fontFamily: 'inherit' }}>Missed</button>
                                </div>
                            )}
                            {m.whatsapp_sent && <span style={{ fontSize: 10, color: '#16a34a', display: 'flex', alignItems: 'center', gap: 2 }}><LuCheck size={11} /> WA sent</span>}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// ─── CallButton ───────────────────────────────────────────────────────────────
function CallButton({ phone, leadId }) {
    // idle → connecting → active (CALL sent) → answered (PSTN bridged)
    const [state, setState] = useState('idle');
    const [onHold, setOnHold] = useState(false);

    useEffect(() => {
        function onAccepted()  { setState(s => (s === 'connecting' || s === 'idle') ? 'active' : s); }
        function onAnswered()  { setState('answered'); }
        function onHoldChg(e) { setOnHold(!!(e.detail?.onHold)); }
        function onEnded()     { setState('idle'); setOnHold(false); }

        document.addEventListener('gc:callAccepted', onAccepted);
        document.addEventListener('gc:callAnswered', onAnswered);
        document.addEventListener('gc:holdChanged',  onHoldChg);
        document.addEventListener('gc:callEnded',    onEnded);
        return () => {
            document.removeEventListener('gc:callAccepted', onAccepted);
            document.removeEventListener('gc:callAnswered', onAnswered);
            document.removeEventListener('gc:holdChanged',  onHoldChg);
            document.removeEventListener('gc:callEnded',    onEnded);
        };
    }, []);

    async function handleClick() {
        if (state === 'active' || state === 'answered') { window.GC?.endCall(); return; }
        setState('connecting');
        try {
            await window.GC?.startCall(phone, leadId);
            setState('active');
        }
        catch (_) { setState('idle'); }
    }

    const isInCall = state === 'active' || state === 'answered';

    return (
        <div style={{ display: 'inline-flex', gap: '6px', alignItems: 'center' }}>
            {state === 'answered' && (
                <button type="button"
                    className={`btn call-btn ${onHold ? 'btn-secondary' : 'btn-warning'}`}
                    title={onHold ? 'Resume call' : 'Hold call'}
                    onClick={() => window.GC?.toggleHold()}>
                    {onHold ? <LuPlay size={16} /> : <LuPause size={16} />}
                    <span className="call-text">{onHold ? 'Resume' : 'Hold'}</span>
                </button>
            )}
            <button type="button"
                className={isInCall ? 'btn btn-danger call-btn active-call' : 'btn btn-primary call-btn'}
                data-phone={phone} data-lead={leadId}
                disabled={state === 'connecting'} onClick={handleClick}>
                <LuPhoneCall size={16} />
                <span className="call-text">
                    {isInCall ? 'End Call' : state === 'connecting' ? 'Connecting…' : 'Call Now'}
                </span>
            </button>
        </div>
    );
}

// ─── Main Show ────────────────────────────────────────────────────────────────
export default function Show({ lead, telecallers, courses, whatsapp_messages, wa_template_name, wa_session_active, urls, meetings: initialMeetings, email_templates }) {
    const [meetings, setMeetings]         = useState(initialMeetings ?? []);
    const [statusOpen, setStatusOpen]     = useState(false);
    const [editContact, setEditContact]   = useState(false);
    const [editPhone, setEditPhone]       = useState('');
    const [editEmail, setEditEmail]       = useState('');
    const [contactErr, setContactErr]     = useState('');
    const [toggling, setToggling]         = useState(false);
    const [currentLead, setCurrentLead]   = useState(lead);

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function openEditContact(phone, email) {
        setEditPhone(phone || '');
        setEditEmail(email || '');
        setContactErr('');
        setEditContact(true);
    }

    async function submitEditContact(e) {
        e.preventDefault();
        setContactErr('');
        const res = await fetch(urls.update_contact, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
            body: JSON.stringify({ phone: editPhone, email: editEmail }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { setContactErr(data.message || 'Failed to update.'); return; }
        setCurrentLead(prev => ({ ...prev, phone: editPhone, email: editEmail }));
        setEditContact(false);
    }

    async function handleToggleActive() {
        const label = currentLead.is_active ? 'Deactivate' : 'Activate';
        if (!confirm(`${label} this lead?`)) return;
        setToggling(true);
        const res = await fetch(urls.toggle_active, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        });
        if (res.ok) setCurrentLead(prev => ({ ...prev, is_active: !prev.is_active }));
        setToggling(false);
    }

    function scrollToChat() {
        document.getElementById('waChatBody')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function handleMeetingCreated(meeting) { setMeetings(prev => [meeting, ...prev]); }
    function handleStatusChanged(id, status) { setMeetings(prev => prev.map(m => m.id === id ? { ...m, status } : m)); }

    return (
        <>
            <Head title="Lead Profile" />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .mgr-show-wrap { font-family: 'Poppins', sans-serif; }
                .mgr-show-wrap .form-control, .mgr-show-wrap .form-select { font-family: 'Poppins', sans-serif; border-radius: 8px; border: 1.5px solid ${BOR}; font-size: 13px; }
                .mgr-show-wrap .form-control:focus, .mgr-show-wrap .form-select:focus { border-color: ${OR}; box-shadow: 0 0 0 3px ${OR}18; }

                /* Quick actions card */
                .ab-card { background:${WH}; border:1px solid ${BOR}; border-radius:16px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
                .ab-card-head { background:${DK}; padding:12px 20px; display:flex; align-items:center; gap:10px; }
                .ab-card-head-title { font-size:13px; font-weight:700; color:#fff; }
                .ab-lead-chip { margin-left:auto; display:flex; align-items:center; gap:8px; }
                .ab-lead-name { font-size:12px; font-weight:600; color:#fff; }
                .ab-status-chip { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:3px 10px; border-radius:20px; background:${OR}; color:#fff; }
                .ab-body { display:flex; align-items:stretch; flex-wrap:wrap; }
                .ab-group { padding:16px 20px; display:flex; flex-direction:column; gap:8px; flex:1; }
                .ab-group + .ab-group { border-left:1px solid ${BOR}; }
                .ab-group-label { font-size:10px; font-weight:700; color:${MUT}; text-transform:uppercase; letter-spacing:.7px; }
                .ab-group-btns { display:flex; flex-wrap:wrap; gap:8px; }
                .ab-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:10px; font-size:13px; font-weight:600; border:1.5px solid transparent; cursor:pointer; transition:all .15s; font-family:'Poppins',sans-serif; }
                .btn.call-btn { background:${DK} !important; color:${OR} !important; border-color:${DK} !important; border-radius:10px !important; }
                .btn.call-btn.active-call { background:#ef4444 !important; border-color:#ef4444 !important; color:#fff !important; }
                .ab-wa { background:#25d366; color:#fff; border-color:#25d366; }
                .ab-email { background:${WH}; color:${BDY}; border-color:${BOR}; }
                .ab-email:hover { background:${OR}; color:#fff; border-color:${OR}; }
                .ab-meet { background:#FFF4EE; color:${OR}; border-color:${OR}30; }
                .ab-meet:hover { background:${OR}; color:#fff; }
                .ab-zoom { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
                .ab-zoom:hover { background:#2D8CFF; color:#fff; }
                .ab-status { background:${WH}; color:${BDY}; border-color:${BOR}; }
                .ab-status:hover, .ab-status.active { background:${OR}; color:#fff; border-color:${OR}; }
                .ab-edit { background:${WH}; color:${BDY}; border-color:${BOR}; }
                .ab-edit:hover { background:${OR}; color:#fff; border-color:${OR}; }
                .ab-deact { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
                .ab-deact:hover { background:#ef4444; color:#fff; }
                .ab-act { background:#f0fdf4; color:#16a34a; border-color:#bbf7d0; }
                .ab-act:hover { background:#10b981; color:#fff; }
                .ab-dropdown { border-radius:12px !important; border:1px solid ${BOR} !important; padding:6px !important; min-width:200px !important; }
            `}</style>

            <div className="mgr-show-wrap">
                <div className="lead-profile-nav">
                    <div className="d-flex justify-content-between align-items-center w-100">
                        <div className="d-flex align-items-center gap-3">
                            <Link href="/manager/leads"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 9, fontSize: 13, fontWeight: 600, background: WH, border: `1.5px solid ${BOR}`, color: BDY, textDecoration: 'none' }}>
                                <LuChevronLeft size={16} /> Back to Leads
                            </Link>
                            <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                            <div>
                                <h2 style={{ fontSize: 18, fontWeight: 800, color: DK, margin: 0 }}>Lead Profile</h2>
                                <p style={{ color: MUT, fontSize: 12, margin: 0 }}>Complete details and activity timeline</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="dashboard-content">
                    <div className="row g-4">
                        <div className="col-lg-4">
                            <ProfileCard lead={currentLead} telecallers={telecallers} assignUrl={urls.assign} onEditContact={openEditContact} />
                        </div>

                        <div className="col-lg-8">
                            {/* Quick Actions card */}
                            <div className="ab-card mb-4">
                                <div className="ab-card-head">
                                    <LuZap size={18} style={{ color: OR }} />
                                    <span className="ab-card-head-title">Quick Actions</span>
                                    <div className="ab-lead-chip">
                                        <span className="ab-lead-name">{currentLead.name}</span>
                                        <span className="ab-status-chip">{(currentLead.status || '').replace(/_/g, ' ')}</span>
                                    </div>
                                </div>

                                <div className="ab-body">
                                    <div className="ab-group">
                                        <div className="ab-group-label">Communication</div>
                                        <div className="ab-group-btns">
                                            <CallButton phone={currentLead.phone} leadId={currentLead.id} />
                                            <button className="ab-btn ab-wa" type="button" onClick={scrollToChat}>
                                                <LuMessageSquare size={16} />WhatsApp
                                            </button>
                                            <button className="ab-btn ab-email" type="button" data-bs-toggle="modal" data-bs-target="#emailModal">
                                                <LuMail size={16} />Email
                                            </button>
                                        </div>
                                    </div>

                                    <div className="ab-group">
                                        <div className="ab-group-label">Meetings</div>
                                        <div className="ab-group-btns">
                                            <MeetDropdown startUrl={urls.meet_start} lead={lead} onCreated={handleMeetingCreated} />
                                            <ZoomDropdown startUrl={urls.zoom_start} lead={lead} onCreated={handleMeetingCreated} />
                                        </div>
                                    </div>

                                    <div className="ab-group" style={{ flex: 'none' }}>
                                        <div className="ab-group-label">Lead Actions</div>
                                        <div className="ab-group-btns">
                                            <button className={`ab-btn ab-status${statusOpen ? ' active' : ''}`} type="button" onClick={() => setStatusOpen(o => !o)}>
                                                <LuRefreshCw size={16} />Change Status
                                            </button>
                                            <button className="ab-btn ab-edit" type="button" onClick={openEditContact.bind(null, currentLead.phone, currentLead.email)}>
                                                <LuPencil size={16} />Edit Contact
                                            </button>
                                            <button className={`ab-btn ${currentLead.is_active ? 'ab-deact' : 'ab-act'}`} type="button" onClick={handleToggleActive} disabled={toggling}>
                                                <LuRefreshCw size={16} />
                                                {toggling ? '…' : (currentLead.is_active ? 'Deactivate' : 'Activate')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {statusOpen && (
                                <StatusPanel lead={lead} url={urls.change_status} courses={courses ?? []} onClose={() => setStatusOpen(false)} />
                            )}

                            <div style={{ position: 'relative' }}>
                                <WaChat lead={lead} initialMessages={whatsapp_messages} templateName={wa_template_name} initialSessionActive={wa_session_active} urls={urls} />
                            </div>

                            {/* Meeting History */}
                            <div style={{ background: WH, border: `1px solid ${BOR}`, borderRadius: 14, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.04)', marginBottom: 20 }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '14px 18px', borderBottom: `1px solid ${BOR}` }}>
                                    <div style={{ width: 3, height: 22, background: OR, borderRadius: 2 }} />
                                    <LuVideo size={18} style={{ color: OR }} />
                                    <div>
                                        <div style={{ fontSize: 14, fontWeight: 700, color: DK }}>Meeting History</div>
                                        <div style={{ fontSize: 11.5, color: MUT }}>Google Meet &amp; Zoom sessions</div>
                                    </div>
                                    <span style={{ marginLeft: 'auto', fontSize: 11.5, fontWeight: 700, background: '#FFF4EE', color: OR, padding: '3px 10px', borderRadius: 20, border: `1px solid ${OR}30` }}>
                                        {meetings.length}
                                    </span>
                                </div>
                                <MeetHistory meetings={meetings} statusUrl={urls.meet_status} onStatusChanged={handleStatusChanged} />
                            </div>

                            <NoteForm url={urls.add_note} />
                            <Timeline activities={lead.activities} />
                        </div>
                    </div>
                </div>
            </div>

            <CallOutcomeModal url={urls.call_outcome} />
            <ScheduleMeetModal url={urls.meet_schedule} lead={currentLead} onCreated={handleMeetingCreated} />
            <ScheduleZoomModal url={urls.zoom_schedule} lead={currentLead} onCreated={handleMeetingCreated} />
            <EmailModal url={urls.email} lead={currentLead} templates={email_templates ?? []} />

            {/* Edit Contact Modal */}
            {editContact && (
                <div className="modal fade show" style={{ display: 'block', background: 'rgba(0,0,0,.4)' }} tabIndex={-1}>
                    <div className="modal-dialog">
                        <form onSubmit={submitEditContact}>
                            <div className="modal-content" style={{ borderRadius: 16, border: 'none' }}>
                                <div className="modal-header" style={{ background: DK, borderRadius: '16px 16px 0 0', border: 'none' }}>
                                    <h5 className="modal-title" style={{ color: '#fff', fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8 }}>
                                        <LuPencil size={16} style={{ color: OR }} /> Edit Contact Details
                                    </h5>
                                    <button type="button" className="btn-close btn-close-white" onClick={() => setEditContact(false)} />
                                </div>
                                <div className="modal-body" style={{ padding: '20px 24px' }}>
                                    {contactErr && <div className="alert alert-danger py-2" style={{ fontSize: 13 }}>{contactErr}</div>}
                                    <div className="mb-3">
                                        <label className="form-label fw-semibold">Mobile Number <span className="text-danger">*</span></label>
                                        <input type="text" className="form-control" required maxLength={20} value={editPhone} onChange={e => setEditPhone(e.target.value)} placeholder="e.g. 9876543210" />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label fw-semibold">Email Address</label>
                                        <input type="email" className="form-control" maxLength={255} value={editEmail} onChange={e => setEditEmail(e.target.value)} placeholder="e.g. student@example.com" />
                                    </div>
                                </div>
                                <div className="modal-footer" style={{ borderTop: `1px solid ${BOR}` }}>
                                    <button type="button" style={{ padding: '8px 16px', borderRadius: 8, background: WH, border: `1.5px solid ${BOR}`, color: BDY, fontSize: 13, fontWeight: 600, cursor: 'pointer', fontFamily: 'inherit' }} onClick={() => setEditContact(false)}>Cancel</button>
                                    <button type="submit" style={{ padding: '8px 18px', borderRadius: 8, background: OR, color: '#fff', border: 'none', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'inherit' }}>
                                        <LuCheck size={16} style={{ verticalAlign: 'middle', marginRight: 4 }} /> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </>
    );
}
