import { Head, Link } from '@inertiajs/react';
import {
    LuPlus, LuUsers, LuPhone, LuCheck, LuCalendar, LuChartBar,
    LuTrendingUp, LuEye, LuChevronLeft, LuChevronRight,
} from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

const STATUS_CFG = {
    active:    { bg: '#dcfce7', color: '#16a34a', dot: '#22c55e', label: 'Active'    },
    paused:    { bg: '#fef9c3', color: '#b45309', dot: '#f59e0b', label: 'Paused'    },
    completed: { bg: '#FFF7ED', color: '#EA580C', dot: '#FF5C00', label: 'Completed' },
    draft:     { bg: '#f1f5f9', color: '#64748b', dot: '#94a3b8', label: 'Draft'     },
};

function StatusPill({ status }) {
    const s = STATUS_CFG[status] ?? STATUS_CFG.draft;
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, background: s.bg, color: s.color, fontSize: 11, fontWeight: 700, padding: '4px 10px', borderRadius: 20 }}>
            <span style={{ width: 6, height: 6, borderRadius: '50%', background: s.dot, display: 'inline-block' }} />
            {s.label}
        </span>
    );
}

function StatRow({ icon: Icon, label, value, sub, orange }) {
    return (
        <div style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 12px',
            background: orange ? OR : WH, borderRadius:10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.2)' : '0 1px 3px rgba(0,0,0,0.04)' }}>
            <div style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                background: orange ? 'rgba(255,255,255,0.18)' : '#FFF7ED',
                display:'flex', alignItems:'center', justifyContent:'center',
                color: orange ? '#fff' : OR }}><Icon size={15}/></div>
            <div style={{ flex:1, minWidth:0 }}>
                <div style={{ fontSize:9, fontWeight:600, textTransform:'uppercase', letterSpacing:'0.5px',
                    marginBottom:1, color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize:18, fontWeight:800, lineHeight:1,
                    color: orange ? '#fff' : DK }}>{value ?? 0}</div>
                {sub && <div style={{ fontSize:9.5, marginTop:2, color: orange ? 'rgba(255,255,255,0.65)' : MUT }}>{sub}</div>}
            </div>
        </div>
    );
}

function MiniBar({ value, max, color }) {
    const pct = max > 0 ? Math.min(100, Math.round((value / max) * 100)) : 0;
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
            <div style={{ flex: 1, height: 4, background: BOR, borderRadius: 2 }}>
                <div style={{ width: `${pct}%`, height: '100%', background: color, borderRadius: 2, transition: 'width .4s' }} />
            </div>
            <span style={{ fontSize: 10, color: MUT, minWidth: 26, textAlign: 'right' }}>{pct}%</span>
        </div>
    );
}

function Pagination({ links, lastPage }) {
    if (lastPage <= 1) return null;
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 4, flexWrap: 'wrap' }}>
            {links.map((link, i) => {
                const isActive   = link.active;
                const isDisabled = !link.url;
                const label      = link.label.replace('&laquo;', '«').replace('&raquo;', '»');
                const style = {
                    minWidth: 34, height: 34, borderRadius: 8, border: '1.5px solid',
                    borderColor: isActive ? OR : BOR,
                    background: isActive ? OR : WH,
                    color: isActive ? '#fff' : isDisabled ? MUT : BDY,
                    fontWeight: isActive ? 700 : 500, fontSize: 13,
                    cursor: isDisabled ? 'default' : 'pointer',
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                    textDecoration: 'none',
                    boxShadow: isActive ? `0 2px 8px ${OR}40` : 'none',
                };
                return link.url
                    ? <Link key={i} href={link.url} style={style}>{label}</Link>
                    : <span key={i} style={style}>{label}</span>;
            })}
        </div>
    );
}

export default function Index({ campaigns, totalStats }) {
    const s   = totalStats ?? {};
    const maxContacts = Math.max(...(campaigns.data ?? []).map(c => c.contacts_count ?? 0), 1);
    const convRate = s.total_contacts > 0 ? Math.round((s.converted_contacts / s.total_contacts) * 100) : 0;

    const kpis = [
        { icon: LuChartBar,   label: 'Total Campaigns',  value: s.total ?? 0,                               sub: `${s.active ?? 0} active · ${s.paused ?? 0} paused`, accentColor: OR },
        { icon: LuUsers,      label: 'Total Contacts',   value: (s.total_contacts ?? 0).toLocaleString(),   sub: 'Across all campaigns',                               accentColor: '#FF5C00' },
        { icon: LuCheck,      label: 'Converted',        value: (s.converted_contacts ?? 0).toLocaleString(), sub: s.total_contacts > 0 ? `${convRate}% conversion rate` : 'No contacts yet', accentColor: '#10b981' },
        { icon: LuPhone,      label: 'Total Calls Made', value: (s.total_calls ?? 0).toLocaleString(),      sub: 'Campaign call activity',                             accentColor: '#f59e0b' },
    ];

    return (
        <>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .camp-idx, .camp-idx div, .camp-idx span:not([class*="material"]),
                .camp-idx p, .camp-idx h1, .camp-idx h2, .camp-idx h3,
                .camp-idx button, .camp-idx input, .camp-idx select, .camp-idx a,
                .camp-idx th, .camp-idx td, .camp-idx label, .camp-idx small {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .camp-kpi { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
                @media(max-width:960px){ .camp-kpi{ grid-template-columns:repeat(2,1fr); } }
            `}</style>
            <Head title="Campaigns" />

            <div className="camp-idx">
                {/* Page header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24, flexWrap: 'wrap', gap: 12 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <div style={{ width: 3, height: 32, background: OR, borderRadius: 2, flexShrink: 0 }} />
                        <div>
                            <h2 style={{ fontSize: 20, fontWeight: 800, color: DK, margin: 0 }}>Campaigns</h2>
                            <p style={{ color: MUT, fontSize: 13, margin: '2px 0 0' }}>Manage and track all your outreach campaigns</p>
                        </div>
                    </div>
                    <Link href="/manager/campaigns/create"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '9px 20px', borderRadius: 10, background: OR, color: '#fff', textDecoration: 'none', fontWeight: 700, fontSize: 13, boxShadow: `0 4px 12px ${OR}40`, border: 'none' }}>
                        <LuPlus size={16} />
                        New Campaign
                    </Link>
                </div>

                {/* KPI Cards */}
                <div className="camp-kpi">
                    {kpis.map((k, i) => <StatRow key={k.label} icon={k.icon} label={k.label} value={k.value} sub={k.sub} orange={i === 0}/>)}
                </div>

                {/* Analytics bar */}
                <div style={{ background: WH, borderRadius: 14, border: `1.5px solid ${BOR}`, padding: '16px 22px', marginBottom: 20, boxShadow: '0 2px 10px rgba(0,0,0,0.04)' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 14 }}>
                        <div style={{ width: 3, height: 20, background: OR, borderRadius: 2 }} />
                        <span style={{ fontSize: 13, fontWeight: 700, color: DK }}>Campaign Status Overview</span>
                    </div>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(160px,1fr))', gap: 16 }}>
                        {[
                            { key: 'active',    label: 'Active',    color: '#22c55e' },
                            { key: 'paused',    label: 'Paused',    color: '#f59e0b' },
                            { key: 'completed', label: 'Completed', color: '#FF5C00' },
                        ].map(item => {
                            const count = s[item.key] ?? 0;
                            const pct   = (s.total ?? 0) > 0 ? Math.round((count / s.total) * 100) : 0;
                            return (
                                <div key={item.key} style={{ background: '#f8fafc', borderRadius: 10, padding: '12px 14px' }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 8 }}>
                                        <span style={{ width: 8, height: 8, borderRadius: '50%', background: item.color, flexShrink: 0 }} />
                                        <span style={{ fontSize: 12, fontWeight: 600, color: BDY }}>{item.label}</span>
                                        <span style={{ marginLeft: 'auto', fontSize: 16, fontWeight: 800, color: DK }}>{count}</span>
                                    </div>
                                    <div style={{ height: 5, background: BOR, borderRadius: 3 }}>
                                        <div style={{ width: `${pct}%`, height: '100%', background: item.color, borderRadius: 3, transition: 'width .5s' }} />
                                    </div>
                                    <div style={{ fontSize: 10, color: MUT, marginTop: 4, textAlign: 'right' }}>{pct}% of total</div>
                                </div>
                            );
                        })}
                        {/* Conversion analytics */}
                        <div style={{ background: `${OR}0f`, borderRadius: 10, padding: '12px 14px', border: `1px solid ${OR}30` }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 8 }}>
                                <LuTrendingUp size={14} color={OR} />
                                <span style={{ fontSize: 12, fontWeight: 600, color: OR }}>Conversion Rate</span>
                                <span style={{ marginLeft: 'auto', fontSize: 16, fontWeight: 800, color: OR }}>{convRate}%</span>
                            </div>
                            <div style={{ height: 5, background: `${OR}30`, borderRadius: 3 }}>
                                <div style={{ width: `${convRate}%`, height: '100%', background: OR, borderRadius: 3, transition: 'width .5s' }} />
                            </div>
                            <div style={{ fontSize: 10, color: OR, marginTop: 4, textAlign: 'right' }}>{s.converted_contacts ?? 0} / {s.total_contacts ?? 0} contacts</div>
                        </div>
                    </div>
                </div>

                {/* Table card */}
                <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: '0 2px 12px rgba(0,0,0,0.05)' }}>

                    {/* Table header */}
                    <div style={{ padding: '16px 22px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 10, borderBottom: `1px solid ${BOR}` }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                            <div>
                                <div style={{ color: DK, fontWeight: 700, fontSize: 15 }}>All Campaigns</div>
                                <div style={{ color: MUT, fontSize: 12 }}>{s.total ?? 0} campaigns total</div>
                            </div>
                        </div>
                        <Link href="/manager/campaigns/create"
                            style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 8, background: OR, color: '#fff', fontSize: 12, fontWeight: 600, textDecoration: 'none' }}>
                            <LuPlus size={14} />
                            New Campaign
                        </Link>
                    </div>

                    {campaigns.data.length === 0 ? (
                        <div style={{ padding: '60px 20px', textAlign: 'center' }}>
                            <LuChartBar size={52} color={BOR} style={{ display: 'block', margin: '0 auto 12px' }} />
                            <div style={{ color: BDY, fontWeight: 600, fontSize: 15, marginBottom: 6 }}>No campaigns yet</div>
                            <div style={{ color: MUT, fontSize: 13, marginBottom: 20 }}>Create your first campaign to start reaching out</div>
                            <Link href="/manager/campaigns/create"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '9px 20px', borderRadius: 10, background: OR, color: '#fff', textDecoration: 'none', fontWeight: 700, fontSize: 13 }}>
                                <LuPlus size={16} />
                                Create Campaign
                            </Link>
                        </div>
                    ) : (
                        <>
                            <div style={{ overflowX: 'auto' }}>
                                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                    <thead>
                                        <tr style={{ background: '#F4F6F8', borderBottom: `2px solid ${BOR}`, position: 'sticky', top: 0, zIndex: 1 }}>
                                            {['#', 'Campaign', 'Status', 'Contacts', 'Progress', 'Converted', 'Created', ''].map((h, i) => (
                                                <th key={i} style={{ padding: '11px 16px', textAlign: i === 7 ? 'right' : i >= 3 ? 'center' : 'left', fontSize: 11, fontWeight: 700, color: BDY, textTransform: 'uppercase', letterSpacing: '.06em', whiteSpace: 'nowrap' }}>{h}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {campaigns.data.map((c, i) => {
                                            const isEven    = i % 2 === 1;
                                            const cfg       = STATUS_CFG[c.status] ?? STATUS_CFG.draft;
                                            const initial   = (c.name ?? '?')[0].toUpperCase();
                                            const contacts  = c.contacts_count ?? 0;
                                            const converted = c.converted_count ?? 0;
                                            const convRate  = contacts > 0 ? Math.round((converted / contacts) * 100) : 0;
                                            const sno       = (campaigns.current_page - 1) * campaigns.per_page + i + 1;

                                            const avatarColors = [
                                                [`${OR}20`, OR], ['#dcfce7', '#15803d'],
                                                ['#fef3c7', '#b45309'], ['#fee2e2', '#b91c1c'],
                                                ['#e0f2fe', '#0369a1'], ['#f0fdf4', '#166534'],
                                            ];
                                            const [avBg, avColor] = avatarColors[i % avatarColors.length];

                                            return (
                                                <tr key={c.id}
                                                    style={{ background: isEven ? '#fafbfc' : WH, borderBottom: `1px solid ${BOR}`, transition: 'background .12s' }}
                                                    onMouseEnter={e => e.currentTarget.style.background = `${OR}0a`}
                                                    onMouseLeave={e => e.currentTarget.style.background = isEven ? '#fafbfc' : WH}>

                                                    <td style={{ padding: '14px 16px', color: MUT, fontSize: 12, fontWeight: 600, width: 48 }}>{sno}</td>

                                                    <td style={{ padding: '14px 16px', minWidth: 200 }}>
                                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                            <div style={{ width: 38, height: 38, borderRadius: 10, background: avBg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                                <span style={{ fontSize: 15, fontWeight: 800, color: avColor }}>{initial}</span>
                                                            </div>
                                                            <div>
                                                                <div style={{ fontWeight: 700, color: DK, fontSize: 13 }}>{c.name}</div>
                                                                {c.description && (
                                                                    <div style={{ fontSize: 11, color: MUT, marginTop: 1 }}>
                                                                        {c.description.length > 45 ? c.description.slice(0, 45) + '…' : c.description}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td style={{ padding: '14px 16px', textAlign: 'center' }}>
                                                        <StatusPill status={c.status} />
                                                    </td>

                                                    <td style={{ padding: '14px 16px', textAlign: 'center' }}>
                                                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: `${OR}18`, color: OR, fontWeight: 700, fontSize: 13, padding: '4px 12px', borderRadius: 20 }}>
                                                            <LuUsers size={13} />
                                                            {contacts.toLocaleString()}
                                                        </span>
                                                    </td>

                                                    <td style={{ padding: '14px 16px', minWidth: 120 }}>
                                                        <MiniBar value={contacts} max={maxContacts} color={cfg.dot} />
                                                    </td>

                                                    <td style={{ padding: '14px 16px', textAlign: 'center' }}>
                                                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2 }}>
                                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#dcfce7', color: '#16a34a', fontWeight: 700, fontSize: 12, padding: '3px 10px', borderRadius: 20 }}>
                                                                <LuCheck size={11} />
                                                                {converted}
                                                            </span>
                                                            {contacts > 0 && (
                                                                <span style={{ fontSize: 10, color: MUT }}>{convRate}% rate</span>
                                                            )}
                                                        </div>
                                                    </td>

                                                    <td style={{ padding: '14px 16px', textAlign: 'center' }}>
                                                        <div style={{ display: 'flex', alignItems: 'center', gap: 4, justifyContent: 'center' }}>
                                                            <LuCalendar size={13} color={MUT} />
                                                            <span style={{ fontSize: 12, color: BDY, fontWeight: 500 }}>{c.created_at}</span>
                                                        </div>
                                                    </td>

                                                    <td style={{ padding: '14px 16px', textAlign: 'right' }}>
                                                        <Link href={`/manager/campaigns/${c.encrypted_id}`}
                                                            style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 8, background: OR, color: '#fff', textDecoration: 'none', fontSize: 12, fontWeight: 600, boxShadow: `0 2px 6px ${OR}40`, whiteSpace: 'nowrap' }}>
                                                            <LuEye size={14} />
                                                            View
                                                        </Link>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>

                            {campaigns.last_page > 1 && (
                                <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderTop: `1px solid ${BOR}`, background: '#fafbfc', flexWrap: 'wrap', gap: 10 }}>
                                    <div style={{ fontSize: 12, color: BDY, fontWeight: 500 }}>
                                        Showing <strong>{(campaigns.current_page - 1) * campaigns.per_page + 1}–{Math.min(campaigns.current_page * campaigns.per_page, campaigns.total)}</strong> of <strong>{campaigns.total}</strong> campaigns
                                    </div>
                                    <Pagination links={campaigns.links} lastPage={campaigns.last_page} />
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </>
    );
}
