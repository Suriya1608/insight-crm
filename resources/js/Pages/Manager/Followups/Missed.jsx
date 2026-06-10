import { Head, Link } from '@inertiajs/react';
import {
    LuCalendar, LuCalendarClock, LuUser, LuClock,
} from 'react-icons/lu';
import { BiError } from 'react-icons/bi';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';
const SCOPE_COLOR = '#8B5CF6'; // purple for "missed" scope

// ─── Tab / scope config — same as Index.jsx ───────────────────────────────────
const TABS = [
    { key: 'today',    href: '/manager/followups/today',    label: 'Today',                icon: <LuCalendar size={15}/>,      accent: OR,          desc: 'Due today'         },
    { key: 'overdue',  href: '/manager/followups/overdue',  label: 'Overdue',              icon: <BiError size={15}/>,         accent: '#EF4444',   desc: 'Past due'          },
    { key: 'upcoming', href: '/manager/followups/upcoming', label: 'Upcoming',             icon: <LuCalendarClock size={15}/>, accent: '#10B981',   desc: 'Scheduled'         },
    { key: 'missed',   href: '/manager/followups/missed',   label: 'Missed by Telecaller', icon: <LuUser size={15}/>,          accent: SCOPE_COLOR, desc: 'Telecaller missed' },
];

// ─── Telecaller avatar badge ───────────────────────────────────────────────────
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
    if (!name) return <span style={{ color: MUT, fontSize: 12 }}>Unassigned</span>;
    const { bg, color } = tcColor(name);
    const initials = name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <span style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                width: 30, height: 30, borderRadius: '50%',
                background: bg, color, fontSize: 11, fontWeight: 700, flexShrink: 0 }}>{initials}</span>
            <span style={{ fontSize: 12, fontWeight: 700, color }}>{name}</span>
        </div>
    );
}

// ─── Missed count badge ────────────────────────────────────────────────────────
function MissedBadge({ count }) {
    const cfg = count >= 10
        ? { bg: '#FEF2F2', color: '#DC2626', border: '#FECACA', dot: '#EF4444' }
        : count >= 5
        ? { bg: '#FFFBEB', color: '#D97706', border: '#FDE68A', dot: '#F59E0B' }
        : { bg: '#ECFDF5', color: '#065F46', border: '#6EE7B7', dot: '#10B981' };
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
            padding: '3px 10px', borderRadius: 20, fontSize: 11, fontWeight: 700,
            background: cfg.bg, color: cfg.color, border: `1px solid ${cfg.border}` }}>
            <span style={{ width: 5, height: 5, borderRadius: '50%', background: cfg.dot, flexShrink: 0 }}/>
            {count} missed
        </span>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Missed({ rows, kpi = {} }) {
    const k = kpi;

    return (
        <>
            <Head title="Missed Follow-ups by Telecaller"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .fu-pg, .fu-pg div, .fu-pg span:not([class*="material"]),
                .fu-pg p,.fu-pg label,.fu-pg button,.fu-pg input,.fu-pg select,
                .fu-pg a,.fu-pg td,.fu-pg th,.fu-pg small {
                    font-family:'Poppins',sans-serif !important;
                }
                .fu-pg { display:flex; flex-direction:column; gap:14px; }

                .fu-body {
                    display:grid;
                    grid-template-columns:220px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:860px){ .fu-body{ grid-template-columns:1fr; } }

                .fu-kpi-stack { display:flex; flex-direction:column; gap:9px; }

                .fu-tbl { width:100%; border-collapse:collapse; min-width:480px; }
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
                .fu-tbl tbody tr:hover td { background:#FAF5FF !important; }
                .fu-tbl tbody tr:hover td:first-child { border-left:3px solid ${SCOPE_COLOR}; padding-left:16px; }

                .fu-scroll { overflow-y:auto; overflow-x:auto; max-height:480px; }
                .fu-scroll::-webkit-scrollbar { width:5px; }
                .fu-scroll::-webkit-scrollbar-track { background:#F4F6F8; border-radius:4px; }
                .fu-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .fu-scroll::-webkit-scrollbar-thumb:hover { background:${SCOPE_COLOR}; }

                .fu-pager {
                    padding:10px 18px; border-top:1px solid ${BOR};
                    display:flex; align-items:center; justify-content:space-between;
                    flex-wrap:wrap; gap:9px; background:#FAFBFC;
                }
                .fu-pager .page-link { background:${WH}; border-color:#E5E7EB; color:${BDY}; font-size:11.5px; border-radius:7px; padding:4px 9px; }
                .fu-pager .page-item.active .page-link { background:${SCOPE_COLOR}; border-color:${SCOPE_COLOR}; color:#fff; }
                .fu-pager .page-item.disabled .page-link { opacity:.4; }
            `}</style>

            <div className="fu-pg">

                {/* ── Scope header card ── */}
                <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                    boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden' }}>
                    <div style={{ height: 4, background: `linear-gradient(90deg,${SCOPE_COLOR},${SCOPE_COLOR}66)` }}/>
                    <div style={{ padding: '16px 20px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                            <div style={{ width: 44, height: 44, borderRadius: 12, flexShrink: 0,
                                background: `${SCOPE_COLOR}15`, display: 'flex', alignItems: 'center',
                                justifyContent: 'center', color: SCOPE_COLOR }}>
                                <LuUser size={22}/>
                            </div>
                            <div>
                                <div style={{ fontSize: 17, fontWeight: 800, color: DK }}>Missed by Telecaller</div>
                                <div style={{ fontSize: 12, color: MUT, marginTop: 2 }}>
                                    Overdue follow-ups grouped by telecaller
                                </div>
                            </div>
                            <div style={{ marginLeft: 'auto' }}>
                                <span style={{ background: `${SCOPE_COLOR}15`, color: SCOPE_COLOR,
                                    fontSize: 12, fontWeight: 700, padding: '4px 12px', borderRadius: 20 }}>
                                    {rows.total} telecaller{rows.total !== 1 ? 's' : ''}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Two-column: KPI left + table right ── */}
                <div className="fu-body">

                    {/* LEFT — stacked KPI link cards */}
                    <div className="fu-kpi-stack">
                        {TABS.map(t => {
                            const cnt = k[t.key] ?? 0;
                            const on  = t.key === 'missed';
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
                        <div style={{ height: 2, background: `linear-gradient(90deg,${SCOPE_COLOR},${SCOPE_COLOR}55)` }}/>

                        <div className="fu-scroll">
                            <table className="fu-tbl">
                                <thead>
                                    <tr>
                                        <th style={{ width: 38 }}>#</th>
                                        <th>Telecaller</th>
                                        <th>Missed Count</th>
                                        <th>Oldest Pending</th>
                                        <th>Latest Pending</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.data.length === 0 ? (
                                        <tr><td colSpan={5}>
                                            <div style={{ textAlign: 'center', padding: '52px 0 48px' }}>
                                                <div style={{ width: 60, height: 60, borderRadius: 16,
                                                    background: `${SCOPE_COLOR}12`,
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    margin: '0 auto 14px', color: SCOPE_COLOR }}>
                                                    <LuCalendar size={28}/>
                                                </div>
                                                <div style={{ fontSize: 14, fontWeight: 700, color: DK, marginBottom: 6 }}>
                                                    All caught up!
                                                </div>
                                                <div style={{ fontSize: 12, color: MUT, maxWidth: 260, margin: '0 auto', lineHeight: 1.7 }}>
                                                    No missed follow-ups. Your team is on track.
                                                </div>
                                            </div>
                                        </td></tr>
                                    ) : rows.data.map((row, idx) => {
                                        const sno = (rows.current_page - 1) * rows.per_page + idx + 1;
                                        return (
                                            <tr key={row.telecaller_id ?? idx}>
                                                <td style={{ color: MUT, fontSize: 10.5, fontWeight: 600 }}>{sno}</td>
                                                <td><TcBadge name={row.telecaller_name}/></td>
                                                <td><MissedBadge count={row.missed_count}/></td>
                                                <td>
                                                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
                                                        fontSize: 11.5, color: '#DC2626', fontWeight: 600,
                                                        background: '#FEF2F2', padding: '3px 9px', borderRadius: 6 }}>
                                                        <BiError size={11}/>{row.oldest_pending}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span style={{ fontSize: 11.5, color: BDY, fontWeight: 500 }}>
                                                        {row.latest_pending}
                                                    </span>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        <div className="fu-pager">
                            <small style={{ color: MUT }}>{rows.from ?? 0}–{rows.to ?? 0} of {rows.total} results</small>
                            {rows.last_page > 1 && (
                                <nav>
                                    <ul className="pagination pagination-sm mb-0" style={{ gap: 2 }}>
                                        {rows.links.map((link, i) => (
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
