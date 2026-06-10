import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuCalendar, LuCalendarClock, LuCalendarCheck, LuUser,
    LuDownload, LuCheck, LuX, LuExternalLink, LuPhone, LuClock,
} from 'react-icons/lu';
import { BiError } from 'react-icons/bi';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

// ─── Tab / scope config ────────────────────────────────────────────────────────
const TABS = [
    { key: 'today',    href: '/manager/followups/today',    label: 'Today',                icon: <LuCalendar size={15}/>,     accent: OR,        desc: 'Due today'         },
    { key: 'overdue',  href: '/manager/followups/overdue',  label: 'Overdue',              icon: <BiError size={15}/>,        accent: '#EF4444', desc: 'Past due'          },
    { key: 'upcoming', href: '/manager/followups/upcoming', label: 'Upcoming',             icon: <LuCalendarClock size={15}/>, accent: '#10B981', desc: 'Scheduled'         },
    { key: 'missed',   href: '/manager/followups/missed',   label: 'Missed by Telecaller', icon: <LuUser size={15}/>,         accent: '#8B5CF6', desc: 'Telecaller missed' },
];

const STATUS_STYLES = {
    today:     { bg: '#FFF7ED', color: OR,        dot: OR        },
    overdue:   { bg: '#FEF2F2', color: '#DC2626', dot: '#EF4444' },
    upcoming:  { bg: '#ECFDF5', color: '#16A34A', dot: '#10B981' },
    completed: { bg: '#F9FAFB', color: '#6B7280', dot: '#9CA3AF' },
};

function StatusBadge({ label }) {
    const st = STATUS_STYLES[label] ?? { bg: '#F3F4F6', color: '#6B7280', dot: '#9CA3AF' };
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
            padding: '3px 10px', borderRadius: 20, fontSize: 11, fontWeight: 700,
            background: st.bg, color: st.color, whiteSpace: 'nowrap' }}>
            <span style={{ width: 5, height: 5, borderRadius: '50%', background: st.dot, flexShrink: 0 }}/>
            {label ? label.charAt(0).toUpperCase() + label.slice(1) : '—'}
        </span>
    );
}

function DateChip({ dateFmt, timeFmt, statusLabel }) {
    const isOverdue = statusLabel === 'overdue';
    const isToday   = statusLabel === 'today';
    const color = isOverdue ? '#DC2626' : isToday ? OR : '#374151';
    const bg    = isOverdue ? '#FEF2F2' : isToday ? '#FFF7ED' : '#F3F4F6';
    return (
        <div>
            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
                fontSize: 11.5, fontWeight: 700, color, background: bg,
                padding: '3px 8px', borderRadius: 6 }}>
                {isOverdue ? <BiError size={12}/> : <LuCalendar size={11}/>}
                {dateFmt || '—'}
            </div>
            {timeFmt && (
                <div style={{ fontSize: 10, color: MUT, marginTop: 2, display: 'flex', alignItems: 'center', gap: 3 }}>
                    <LuClock size={9}/>{timeFmt}
                </div>
            )}
        </div>
    );
}

const TC_PALETTE = [
    { bg: '#FFF7ED', color: OR        }, { bg: '#DCFCE7', color: '#15803D' },
    { bg: '#EFF6FF', color: '#1D4ED8' }, { bg: '#FEF3C7', color: '#92400E' },
    { bg: '#F5F3FF', color: '#6D28D9' }, { bg: '#CFFAFE', color: '#155E75' },
];
function tcColor(name) {
    if (!name) return TC_PALETTE[0];
    let h = 0;
    for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
    return TC_PALETTE[h % TC_PALETTE.length];
}
function TcBadge({ name }) {
    if (!name) return <span style={{ color: MUT, fontSize: 12 }}>—</span>;
    const { bg, color } = tcColor(name);
    const initials = name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
            <span style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                width: 28, height: 28, borderRadius: '50%',
                background: bg, color, fontSize: 10, fontWeight: 700, flexShrink: 0 }}>{initials}</span>
            <span style={{ fontSize: 11.5, fontWeight: 600, color }}>{name}</span>
        </div>
    );
}

const AVATARS = [OR, '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899'];
const avatarBg = n => AVATARS[(n?.charCodeAt(0) ?? 0) % AVATARS.length];

export default function Index({ scope, title, followups, kpi = {} }) {
    const [emailSending, setEmailSending] = useState(false);
    const [emailMsg,     setEmailMsg]     = useState('');

    function handleEmail() {
        setEmailSending(true); setEmailMsg('');
        fetch(`/manager/followups/export/${scope}/email`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '', 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(d => setEmailMsg(d.message ?? 'Sent!'))
            .catch(() => setEmailMsg('Failed to send.'))
            .finally(() => setEmailSending(false));
    }

    const k = kpi;
    const activeTab  = TABS.find(t => t.key === scope) ?? TABS[0];
    const scopeColor = activeTab.accent;

    return (
        <>
            <Head title={title}/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .fu-pg, .fu-pg div, .fu-pg span:not([class*="material"]),
                .fu-pg p,.fu-pg label,.fu-pg button,.fu-pg input,.fu-pg select,
                .fu-pg a,.fu-pg td,.fu-pg th,.fu-pg small,.fu-pg textarea {
                    font-family:'Poppins',sans-serif !important;
                }
                .fu-pg { display:flex; flex-direction:column; gap:14px; }

                /* ── Two-column body ── */
                .fu-body {
                    display:grid;
                    grid-template-columns:220px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:860px){ .fu-body{ grid-template-columns:1fr; } }

                /* ── Stacked KPI cards in left panel ── */
                .fu-kpi-stack { display:flex; flex-direction:column; gap:9px; }

                /* ── Table ── */
                .fu-tbl { width:100%; border-collapse:collapse; min-width:560px; }
                .fu-tbl thead th {
                    background:#F4F6F8; color:${MUT}; font-size:9.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.7px; padding:10px 14px;
                    border-bottom:2px solid ${BOR}; white-space:nowrap;
                    position:sticky; top:0; z-index:1;
                }
                .fu-tbl tbody td {
                    padding:11px 14px; vertical-align:middle; font-size:12px; color:${BDY};
                    border-bottom:1px solid #F4F6F8; transition:background .08s;
                }
                .fu-tbl tbody tr:last-child td { border-bottom:none; }
                .fu-tbl tbody tr:nth-child(even) td { background:#FAFBFC; }
                .fu-tbl tbody tr:hover td { background:#FFF7ED !important; }
                .fu-tbl tbody tr:hover td:first-child { border-left:3px solid ${scopeColor}; padding-left:16px; }

                /* ── Scrollable wrapper ── */
                .fu-scroll { overflow-y:auto; overflow-x:auto; max-height:480px; }
                .fu-scroll::-webkit-scrollbar { width:5px; }
                .fu-scroll::-webkit-scrollbar-track { background:#F4F6F8; border-radius:4px; }
                .fu-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .fu-scroll::-webkit-scrollbar-thumb:hover { background:${OR}; }

                /* ── Pagination ── */
                .fu-pager {
                    padding:10px 18px; border-top:1px solid ${BOR};
                    display:flex; align-items:center; justify-content:space-between;
                    flex-wrap:wrap; gap:9px; background:#FAFBFC;
                }
                .fu-pager .page-link { background:${WH}; border-color:#E5E7EB; color:${BDY}; font-size:11.5px; border-radius:7px; padding:4px 9px; }
                .fu-pager .page-item.active .page-link { background:${scopeColor}; border-color:${scopeColor}; color:#fff; }
                .fu-pager .page-item.disabled .page-link { opacity:.4; }

                /* ── Export menu ── */
                .fu-exp .dropdown-item { font-size:12px; padding:8px 13px; display:flex; align-items:center; gap:7px; color:${BDY}; }
                .fu-exp .dropdown-item:hover { background:${DK}; color:#fff; }

                .fu-view-btn {
                    width:30px; height:30px; border-radius:7px; border:1px solid ${BOR};
                    background:#F3F4F6; color:${MUT}; display:inline-flex; align-items:center;
                    justify-content:center; text-decoration:none; transition:all .15s; flex-shrink:0;
                }
                .fu-view-btn:hover { background:${scopeColor}; border-color:${scopeColor}; color:#fff; }
            `}</style>

            <div className="fu-pg">

                {/* ── Scope header card ── */}
                <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                    boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden' }}>
                    <div style={{ height: 4, background: `linear-gradient(90deg,${scopeColor},${scopeColor}66)` }}/>
                    <div style={{ padding: '16px 20px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                            gap: 12, flexWrap: 'wrap' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                                <div style={{ width: 44, height: 44, borderRadius: 12, flexShrink: 0,
                                    background: `${scopeColor}15`, display: 'flex', alignItems: 'center',
                                    justifyContent: 'center', color: scopeColor }}>
                                    {scope === 'today'    && <LuCalendar size={22}/>}
                                    {scope === 'overdue'  && <BiError size={22}/>}
                                    {scope === 'upcoming' && <LuCalendarClock size={22}/>}
                                    {scope === 'missed'   && <LuUser size={22}/>}
                                </div>
                                <div>
                                    <div style={{ fontSize: 17, fontWeight: 800, color: DK }}>{title}</div>
                                    <div style={{ fontSize: 12, color: MUT, marginTop: 2 }}>
                                        {scope === 'today'    ? 'Follow-ups scheduled for today' :
                                         scope === 'overdue'  ? 'Missed follow-ups that need attention' :
                                         scope === 'upcoming' ? 'Upcoming scheduled follow-ups' :
                                                                'Follow-ups missed by telecallers'}
                                    </div>
                                </div>
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                                <span style={{ background: `${scopeColor}15`, color: scopeColor,
                                    fontSize: 12, fontWeight: 700, padding: '4px 12px', borderRadius: 20 }}>
                                    {followups.total} records
                                </span>
                                <div className="dropdown">
                                    <button type="button" data-bs-toggle="dropdown"
                                        style={{ background: '#FFF7ED', color: OR, border: '1px solid #FED7AA',
                                            borderRadius: 8, padding: '7px 13px', fontSize: 12, fontWeight: 600,
                                            display: 'inline-flex', alignItems: 'center', gap: 5, cursor: 'pointer' }}>
                                        <LuDownload size={13}/> Export
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow-sm fu-exp"
                                        style={{ borderRadius: 10, border: `1px solid ${BOR}`, padding: 5, minWidth: 170 }}>
                                        <li><a className="dropdown-item" href={`/manager/followups/export/${scope}/pdf`}
                                            target="_blank" rel="noreferrer"><span>📄</span> Download PDF</a></li>
                                        <li>
                                            <button type="button" className="dropdown-item" disabled={emailSending} onClick={handleEmail}
                                                style={{ cursor: emailSending ? 'default' : 'pointer', width: '100%', background: 'none', border: 'none' }}>
                                                {emailSending ? <LuClock size={14} style={{ color: MUT }}/> : <LuCheck size={14} style={{ color: '#10B981' }}/>}
                                                {emailSending ? 'Sending…' : 'Email Report'}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                {emailMsg && (
                                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4,
                                        fontSize: 11, fontWeight: 600,
                                        background: emailMsg.startsWith('Failed') ? '#FEF2F2' : '#F0FDF4',
                                        color: emailMsg.startsWith('Failed') ? '#DC2626' : '#16A34A',
                                        padding: '4px 10px', borderRadius: 8 }}>
                                        {emailMsg.startsWith('Failed') ? <LuX size={11}/> : <LuCheck size={11}/>} {emailMsg}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Two-column: KPI left + table right ── */}
                <div className="fu-body">

                    {/* LEFT — stacked KPI link cards (one per scope) */}
                    <div className="fu-kpi-stack">
                        {TABS.map(t => {
                            const cnt = k[t.key] ?? 0;
                            const on  = scope === t.key;
                            return (
                                <Link key={t.key} href={t.href} style={{ textDecoration: 'none' }}>
                                    <div style={{ background: on ? t.accent : WH, borderRadius: 12,
                                        border: on ? 'none' : `1px solid ${BOR}`,
                                        boxShadow: on ? `0 4px 16px ${t.accent}30` : '0 1px 4px rgba(0,0,0,0.04)',
                                        padding: '13px 16px', display: 'flex', alignItems: 'center', gap: 11,
                                        transition: 'all .15s', cursor: 'pointer' }}>
                                        <div style={{ width: 36, height: 36, borderRadius: 9, flexShrink: 0,
                                            background: on ? 'rgba(255,255,255,0.18)' : `${t.accent}15`,
                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                            color: on ? '#fff' : t.accent }}>{t.icon}</div>
                                        <div style={{ flex: 1, minWidth: 0 }}>
                                            <div style={{ fontSize: 22, fontWeight: 800, lineHeight: 1,
                                                color: on ? '#fff' : DK }}>{cnt}</div>
                                            <div style={{ fontSize: 10, fontWeight: 600, marginTop: 2,
                                                color: on ? 'rgba(255,255,255,0.75)' : MUT,
                                                textTransform: 'uppercase', letterSpacing: '.4px' }}>{t.label}</div>
                                            <div style={{ fontSize: 9.5, color: on ? 'rgba(255,255,255,0.5)' : MUT, marginTop: 1 }}>
                                                {t.desc}
                                            </div>
                                        </div>
                                        {on && <div style={{ width: 6, height: 6, borderRadius: '50%',
                                            background: 'rgba(255,255,255,0.6)', flexShrink: 0 }}/>}
                                    </div>
                                </Link>
                            );
                        })}
                    </div>

                    {/* RIGHT — table card */}
                    <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                        boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden' }}>
                        <div style={{ height: 2, background: `linear-gradient(90deg,${scopeColor},${scopeColor}55)` }}/>

                        <div className="fu-scroll">
                            <table className="fu-tbl">
                                <thead>
                                    <tr>
                                        <th style={{ width: 38 }}>#</th>
                                        <th>Lead</th>
                                        <th>Date &amp; Time</th>
                                        <th>Phone</th>
                                        <th>Telecaller</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th style={{ textAlign: 'right', paddingRight: 16 }}>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {followups.data.length === 0 ? (
                                        <tr><td colSpan={8}>
                                            <div style={{ textAlign: 'center', padding: '52px 0 48px' }}>
                                                <div style={{ width: 60, height: 60, borderRadius: 16,
                                                    background: `${scopeColor}12`,
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    margin: '0 auto 14px', color: scopeColor }}>
                                                    {scope === 'overdue' ? <BiError size={28}/> : <LuCalendarCheck size={28}/>}
                                                </div>
                                                <div style={{ fontSize: 14, fontWeight: 700, color: DK, marginBottom: 6 }}>
                                                    {scope === 'overdue' ? 'No overdue follow-ups' : 'All clear!'}
                                                </div>
                                                <div style={{ fontSize: 12, color: MUT, maxWidth: 260, margin: '0 auto', lineHeight: 1.7 }}>
                                                    {scope === 'overdue' ? 'Your team is up to date.' : 'No follow-ups in this view.'}
                                                </div>
                                            </div>
                                        </td></tr>
                                    ) : followups.data.map((item, idx) => {
                                        const sno = (followups.current_page - 1) * followups.per_page + idx + 1;
                                        const bg  = avatarBg(item.lead_name);
                                        return (
                                            <tr key={item.id}>
                                                <td style={{ color: MUT, fontSize: 10.5, fontWeight: 600 }}>{sno}</td>
                                                <td>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                                                        <div style={{ width: 32, height: 32, borderRadius: 9, flexShrink: 0,
                                                            background: `linear-gradient(135deg,${bg},${bg}bb)`,
                                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                            color: '#fff', fontSize: 12, fontWeight: 800 }}>
                                                            {(item.lead_name || '?')[0].toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <div style={{ fontWeight: 700, color: DK, fontSize: 12.5, lineHeight: 1.2 }}>
                                                                {item.lead_name || '—'}
                                                            </div>
                                                            <div style={{ fontSize: 10.5, color: MUT, marginTop: 1 }}>
                                                                {item.lead_code || '—'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <DateChip dateFmt={item.next_followup_fmt}
                                                        timeFmt={item.followup_time_fmt}
                                                        statusLabel={item.status_label}/>
                                                </td>
                                                <td>
                                                    {item.lead_phone
                                                        ? <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 11.5, color: BDY }}>
                                                            <LuPhone size={12} color={MUT}/>{item.lead_phone}
                                                          </span>
                                                        : <span style={{ color: MUT }}>—</span>}
                                                </td>
                                                <td><TcBadge name={item.telecaller_name}/></td>
                                                <td>
                                                    <div style={{ maxWidth: 160, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: 11.5, color: MUT }}>
                                                        {item.remarks || '—'}
                                                    </div>
                                                </td>
                                                <td><StatusBadge label={item.status_label}/></td>
                                                <td style={{ paddingRight: 14 }}>
                                                    {item.encrypted_lead_id && (
                                                        <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                                                            <Link href={`/manager/leads/${item.encrypted_lead_id}`}
                                                                className="fu-view-btn" title="View Lead">
                                                                <LuExternalLink size={13}/>
                                                            </Link>
                                                        </div>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        <div className="fu-pager">
                            <small style={{ color: MUT }}>{followups.from ?? 0}–{followups.to ?? 0} of {followups.total} results</small>
                            {followups.last_page > 1 && (
                                <nav>
                                    <ul className="pagination pagination-sm mb-0" style={{ gap: 2 }}>
                                        {followups.links.map((link, i) => (
                                            <li key={i} className={['page-item', link.active ? 'active' : '', !link.url ? 'disabled' : ''].join(' ')}>
                                                {link.url
                                                    ? <Link href={link.url} className="page-link" dangerouslySetInnerHTML={{ __html: link.label }}/>
                                                    : <span className="page-link" dangerouslySetInnerHTML={{ __html: link.label }}/>}
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            )}
                        </div>
                    </div>

                </div>
            </div>
        </>
    );
}
