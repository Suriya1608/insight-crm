import { Head, Link, router } from '@inertiajs/react';
import {
    LuMail, LuUsers, LuSend, LuMailOpen, LuPlus, LuChartBar,
    LuTrash2, LuFileText, LuTrendingUp, LuCheck, LuX, LuClock,
} from 'react-icons/lu';

// ─── Design tokens ─────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

// ─── Status config ──────────────────────────────────────────────────────────────
const STATUS_CFG = {
    draft:     { bg: '#F9FAFB', color: MUT,       dot: '#D1D5DB', label: 'Draft'     },
    scheduled: { bg: '#EFF6FF', color: '#2563EB',  dot: '#60A5FA', label: 'Scheduled' },
    sending:   { bg: '#FFFBEB', color: '#D97706',  dot: '#FCD34D', label: 'Sending'   },
    completed: { bg: '#F0FDF4', color: '#16A34A',  dot: '#4ADE80', label: 'Completed' },
    failed:    { bg: '#FEF2F2', color: '#DC2626',  dot: '#F87171', label: 'Failed'    },
};

const AVATAR_COLORS = [
    ['#FFF7ED', OR], ['#DCFCE7', '#15803D'],
    ['#FEF3C7', '#B45309'], ['#FEE2E2', '#B91C1C'],
    ['#E0F2FE', '#0369A1'], ['#F0FDF4', '#166534'],
];

// ─── StatRow — telecaller pattern ─────────────────────────────────────────────
function StatRow({ icon, label, value, orange }) {
    return (
        <div style={{
            display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
            background: orange ? OR : WH, borderRadius: 10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.2)' : '0 1px 3px rgba(0,0,0,0.04)',
        }}>
            <div style={{ width: 32, height: 32, borderRadius: 9, flexShrink: 0,
                background: orange ? 'rgba(255,255,255,0.18)' : '#FFF7ED',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: orange ? '#fff' : OR }}>{icon}</div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 9, fontWeight: 600, textTransform: 'uppercase',
                    letterSpacing: '0.5px', marginBottom: 1,
                    color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize: 20, fontWeight: 800, lineHeight: 1,
                    color: orange ? '#fff' : DK }}>{value ?? 0}</div>
            </div>
        </div>
    );
}

// ─── Section heading — orange left bar ────────────────────────────────────────
function SHead({ icon, title, sub, right }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            gap: 10, padding: '14px 20px', borderBottom: `1px solid ${BOR}`,
            background: 'linear-gradient(135deg,#FAFBFC 0%,#FFFFFF 100%)' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{ width: 3, height: 32, borderRadius: 2, background: OR, flexShrink: 0 }} />
                <div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                        {icon && <span style={{ color: OR }}>{icon}</span>}
                        <span style={{ fontSize: 13.5, fontWeight: 700, color: DK }}>{title}</span>
                    </div>
                    {sub && <div style={{ fontSize: 11, color: MUT, marginTop: 1 }}>{sub}</div>}
                </div>
            </div>
            {right && <div style={{ flexShrink: 0 }}>{right}</div>}
        </div>
    );
}

function Card({ children, style = {} }) {
    return (
        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
            boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden', ...style }}>
            {children}
        </div>
    );
}

// ─── Status pill ──────────────────────────────────────────────────────────────
function StatusPill({ status }) {
    const s = STATUS_CFG[status] ?? STATUS_CFG.draft;
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
            background: s.bg, color: s.color, fontSize: 11, fontWeight: 700,
            padding: '4px 10px', borderRadius: 20, whiteSpace: 'nowrap' }}>
            <span style={{ width: 6, height: 6, borderRadius: '50%', background: s.dot, flexShrink: 0 }} />
            {s.label}
        </span>
    );
}

// ─── Mini rate bar ────────────────────────────────────────────────────────────
function RateBar({ value, color }) {
    const pct = Math.min(100, Number(value ?? 0));
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
            <div style={{ flex: 1, height: 5, background: BOR, borderRadius: 99 }}>
                <div style={{ width: `${pct}%`, height: '100%', background: color, borderRadius: 99, transition: 'width .4s' }} />
            </div>
            <span style={{ fontSize: 11, fontWeight: 700, color, minWidth: 34, textAlign: 'right' }}>{pct}%</span>
        </div>
    );
}

// ─── Status breakdown item ────────────────────────────────────────────────────
function StatusBreakItem({ label, count, total, color, Icon }) {
    const pct = total > 0 ? Math.round(count / total * 100) : 0;
    return (
        <div style={{ background: '#F9FAFB', borderRadius: 10, padding: '12px 14px', border: `1px solid ${BOR}` }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 8 }}>
                <Icon size={14} color={color} />
                <span style={{ fontSize: 12, fontWeight: 600, color: BDY, flex: 1 }}>{label}</span>
                <span style={{ fontSize: 16, fontWeight: 800, color: DK }}>{count}</span>
            </div>
            <div style={{ height: 5, background: BOR, borderRadius: 3 }}>
                <div style={{ width: `${pct}%`, height: '100%', background: color, borderRadius: 3, transition: 'width .5s' }} />
            </div>
            <div style={{ fontSize: 10, color: MUT, marginTop: 3, textAlign: 'right' }}>{pct}% of total</div>
        </div>
    );
}

// ─── Pagination ───────────────────────────────────────────────────────────────
function Pagination({ links, lastPage }) {
    if (!lastPage || lastPage <= 1) return null;
    return (
        <nav>
            <ul className="pagination pagination-sm mb-0" style={{ flexWrap: 'wrap', gap: 3 }}>
                {links.map((link, i) => (
                    <li key={i} className={['page-item', link.active ? 'active' : '', !link.url ? 'disabled' : ''].join(' ')}>
                        {link.url
                            ? <Link href={link.url} className="page-link"
                                style={link.active ? { background: OR, borderColor: OR, color: '#fff' } : { borderColor: BOR, color: BDY }}
                                dangerouslySetInnerHTML={{ __html: link.label }} />
                            : <span className="page-link" style={{ borderColor: BOR, color: '#D1D5DB' }} dangerouslySetInnerHTML={{ __html: link.label }} />
                        }
                    </li>
                ))}
            </ul>
        </nav>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ campaigns, stats }) {
    const s = stats ?? {};

    function deleteCampaign(url) {
        if (!window.confirm('Delete this campaign?')) return;
        router.delete(url, { preserveScroll: false });
    }

    return (
        <>
            <Head title="Email Campaigns" />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .ec-pg, .ec-pg div, .ec-pg span:not([class*="material"]),
                .ec-pg p, .ec-pg h1, .ec-pg h2, .ec-pg h3,
                .ec-pg button, .ec-pg input, .ec-pg select, .ec-pg a,
                .ec-pg th, .ec-pg td, .ec-pg label, .ec-pg small {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .ec-pg { display: flex; flex-direction: column; gap: 14px; }

                .ec-kpi { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; }
                @media(max-width:960px){ .ec-kpi{ grid-template-columns:repeat(2,1fr); } }

                /* table */
                .ec-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
                .ec-tbl thead th {
                    position: sticky; top: 0; z-index: 2;
                    background: #F4F6F8; color: ${MUT}; font-size: 9.5px; font-weight: 700;
                    text-transform: uppercase; letter-spacing: .8px;
                    padding: 10px 14px; white-space: nowrap;
                    border-bottom: 2px solid ${BOR};
                }
                .ec-tbl tbody td {
                    padding: 11px 14px; vertical-align: middle;
                    font-size: 12px; color: ${BDY};
                    border-bottom: 1px solid #F4F6F8;
                    transition: background .08s;
                }
                .ec-tbl tbody tr:last-child td { border-bottom: none; }
                .ec-tbl tbody tr:nth-child(even) td { background: #FAFBFC; }
                .ec-tbl tbody tr:hover td { background: #FFF7ED !important; cursor: pointer; }
                .ec-tbl tbody tr:hover td:first-child { border-left: 3px solid ${OR}; padding-left: 16px; }

                .ec-scroll { overflow-y: auto; max-height: 520px; }
                .ec-scroll::-webkit-scrollbar { width: 5px; }
                .ec-scroll::-webkit-scrollbar-track { background: #F4F6F8; }
                .ec-scroll::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }
                .ec-scroll::-webkit-scrollbar-thumb:hover { background: ${OR}; }

                .ec-badge { background: #FFF7ED; color: ${OR}; border: 1px solid #FED7AA;
                    font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px; }

                .ec-pager { padding: 10px 18px; border-top: 1px solid ${BOR};
                    display: flex; align-items: center; justify-content: space-between;
                    flex-wrap: wrap; gap: 9px; background: #FAFBFC; }
            `}</style>

            <div className="ec-pg">

                {/* ── KPI row ── */}
                <div className="ec-kpi">
                    <StatRow icon={<LuMail size={15}/>}    label="Total Campaigns" value={s.total ?? 0}                           orange={true}  />
                    <StatRow icon={<LuUsers size={15}/>}   label="Recipients"      value={(s.total_recipients ?? 0).toLocaleString()} orange={false} />
                    <StatRow icon={<LuSend size={15}/>}    label="Emails Sent"     value={(s.total_sent ?? 0).toLocaleString()}       orange={false} />
                    <StatRow icon={<LuMailOpen size={15}/>} label="Emails Opened"  value={(s.total_opened ?? 0).toLocaleString()}     orange={false} />
                </div>

                {/* ── Analytics overview card ── */}
                <Card>
                    <SHead icon={<LuChartBar size={13}/>} title="Campaign Status Overview"
                        sub="Breakdown by status and engagement rates"/>
                    <div style={{ padding: '16px 20px' }}>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(150px,1fr))', gap: 12 }}>
                            {[
                                { key: 'completed', label: 'Completed', color: '#22C55E', Icon: LuCheck    },
                                { key: 'scheduled', label: 'Scheduled', color: '#60A5FA', Icon: LuClock    },
                                { key: 'sending',   label: 'Sending',   color: '#FCD34D', Icon: LuSend     },
                                { key: 'draft',     label: 'Draft',     color: MUT,       Icon: LuFileText },
                                { key: 'failed',    label: 'Failed',    color: '#F87171', Icon: LuX        },
                            ].map(item => (
                                <StatusBreakItem key={item.key} label={item.label} count={s[item.key] ?? 0}
                                    total={s.total ?? 0} color={item.color} Icon={item.Icon} />
                            ))}
                            {/* Engagement */}
                            <div style={{ background: '#FFF7ED', borderRadius: 10, padding: '12px 14px', border: `1px solid #FED7AA` }}>
                                <div style={{ fontSize: 11, fontWeight: 700, color: OR, marginBottom: 10, display: 'flex', alignItems: 'center', gap: 5 }}>
                                    <LuTrendingUp size={13}/> Engagement
                                </div>
                                <div style={{ marginBottom: 8 }}>
                                    <div style={{ fontSize: 10, color: OR, fontWeight: 600, marginBottom: 3 }}>Delivery Rate</div>
                                    <RateBar value={s.avg_delivery_rate ?? 0} color={OR}/>
                                </div>
                                <div>
                                    <div style={{ fontSize: 10, color: '#10B981', fontWeight: 600, marginBottom: 3 }}>Open Rate</div>
                                    <RateBar value={s.avg_open_rate ?? 0} color="#10B981"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                {/* ── Campaigns table ── */}
                <Card>
                    <SHead
                        icon={<LuMail size={13}/>}
                        title="All Campaigns"
                        sub={`${s.total ?? 0} campaigns total`}
                        right={
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <span className="ec-badge">{s.total ?? 0} records</span>
                                <a href="/manager/email-campaigns/create"
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6,
                                        background: OR, color: '#fff', textDecoration: 'none',
                                        borderRadius: 8, padding: '7px 14px', fontSize: 12,
                                        fontWeight: 600, boxShadow: '0 2px 8px rgba(255,92,0,0.25)' }}>
                                    <LuPlus size={13}/> New Campaign
                                </a>
                            </div>
                        }
                    />

                    {campaigns.data.length === 0 ? (
                        <div style={{ padding: '52px 20px', textAlign: 'center' }}>
                            <div style={{ width: 60, height: 60, borderRadius: 16, background: '#FFF7ED',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                margin: '0 auto 14px', boxShadow: '0 4px 14px rgba(255,92,0,0.1)' }}>
                                <LuMail size={28} color={OR}/>
                            </div>
                            <div style={{ fontSize: 14, fontWeight: 700, color: DK, marginBottom: 6 }}>No email campaigns yet</div>
                            <div style={{ fontSize: 12, color: MUT, marginBottom: 20 }}>Create your first campaign to start reaching out</div>
                            <a href="/manager/email-campaigns/create"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '9px 20px',
                                    borderRadius: 8, background: OR, color: '#fff', textDecoration: 'none', fontWeight: 700, fontSize: 13 }}>
                                <LuPlus size={16}/> Create Campaign
                            </a>
                        </div>
                    ) : (
                        <>
                            <div className="ec-scroll">
                                <table className="ec-tbl">
                                    <thead>
                                        <tr>
                                            <th style={{ width: 40 }}>#</th>
                                            <th>Campaign</th>
                                            <th>Status</th>
                                            <th>Recipients</th>
                                            <th style={{ minWidth: 110 }}>Delivery</th>
                                            <th style={{ minWidth: 110 }}>Opens</th>
                                            <th>Clicks</th>
                                            <th>Failed</th>
                                            <th>Date</th>
                                            <th style={{ textAlign: 'right', paddingRight: 16 }}>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {campaigns.data.map((ec, i) => {
                                            const [avBg, avColor] = AVATAR_COLORS[i % AVATAR_COLORS.length];
                                            const sno = (campaigns.current_page - 1) * campaigns.per_page + i + 1;
                                            const sentPct = ec.recipients_count > 0 ? Math.round(ec.sent_count / ec.recipients_count * 100) : 0;
                                            const openPct = ec.sent_count > 0 ? Math.round(ec.opened_count / ec.sent_count * 100) : 0;
                                            return (
                                                <tr key={ec.id} onClick={() => window.location.href = ec.show_url}>
                                                    <td style={{ color: MUT, fontSize: 10.5, fontWeight: 600 }}>{sno}</td>
                                                    <td style={{ minWidth: 200 }}>
                                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                            <div style={{ width: 34, height: 34, borderRadius: 9, background: avBg,
                                                                display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                                <span style={{ fontSize: 13, fontWeight: 800, color: avColor }}>{(ec.name ?? '?')[0].toUpperCase()}</span>
                                                            </div>
                                                            <div>
                                                                <div style={{ fontWeight: 700, color: DK, fontSize: 12.5 }}>{ec.name}</div>
                                                                {ec.description && <div style={{ fontSize: 10.5, color: MUT, marginTop: 1 }}>{ec.description.length > 50 ? ec.description.slice(0,50)+'…' : ec.description}</div>}
                                                                {ec.template_name && (
                                                                    <div style={{ fontSize: 10, color: BDY, marginTop: 2, display: 'flex', alignItems: 'center', gap: 3 }}>
                                                                        <LuFileText size={10}/>{ec.template_name}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><StatusPill status={ec.status}/></td>
                                                    <td>
                                                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4,
                                                            background: '#FFF7ED', color: OR, fontWeight: 700, fontSize: 11.5,
                                                            padding: '3px 9px', borderRadius: 20 }}>
                                                            <LuUsers size={11}/>{(ec.recipients_count ?? 0).toLocaleString()}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 3 }}>
                                                                <span style={{ fontSize: 11.5, fontWeight: 700, color: OR }}>{(ec.sent_count ?? 0).toLocaleString()}</span>
                                                                <span style={{ fontSize: 10, color: MUT }}>{sentPct}%</span>
                                                            </div>
                                                            <div style={{ height: 4, background: BOR, borderRadius: 99 }}>
                                                                <div style={{ height: 4, width: `${sentPct}%`, background: OR, borderRadius: 99 }}/>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 3 }}>
                                                                <span style={{ fontSize: 11.5, fontWeight: 700, color: '#16A34A' }}>{(ec.opened_count ?? 0).toLocaleString()}</span>
                                                                <span style={{ fontSize: 10, color: MUT }}>{openPct}%</span>
                                                            </div>
                                                            <div style={{ height: 4, background: BOR, borderRadius: 99 }}>
                                                                <div style={{ height: 4, width: `${openPct}%`, background: '#10B981', borderRadius: 99 }}/>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {(ec.click_count ?? 0) > 0
                                                            ? <span style={{ background: '#FFF7ED', color: OR, fontSize: 11.5, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>
                                                                {ec.click_count}{ec.sent_count > 0 && <span style={{ fontWeight: 400, marginLeft: 3 }}>({ec.click_rate}%)</span>}
                                                              </span>
                                                            : <span style={{ color: '#D1D5DB', fontSize: 12 }}>—</span>}
                                                    </td>
                                                    <td>
                                                        {(ec.failed_count ?? 0) > 0
                                                            ? <span style={{ background: '#FEE2E2', color: '#DC2626', fontSize: 11.5, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>{ec.failed_count}</span>
                                                            : <span style={{ color: '#D1D5DB', fontSize: 12 }}>0</span>}
                                                    </td>
                                                    <td style={{ whiteSpace: 'nowrap' }}>
                                                        {ec.scheduled_at
                                                            ? <div>
                                                                <div style={{ fontSize: 9.5, color: OR, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em' }}>Scheduled</div>
                                                                <div style={{ fontSize: 11.5, color: BDY, fontWeight: 500 }}>{ec.scheduled_at}</div>
                                                              </div>
                                                            : <div style={{ fontSize: 11.5, color: BDY }}>{ec.created_at}</div>}
                                                    </td>
                                                    <td style={{ paddingRight: 14 }} onClick={e => e.stopPropagation()}>
                                                        <div style={{ display: 'flex', gap: 6, justifyContent: 'flex-end' }}>
                                                            <Link href={ec.show_url}
                                                                style={{ width: 30, height: 30, borderRadius: 8, background: '#FFF7ED', color: OR,
                                                                    border: `1px solid #FED7AA`, display: 'inline-flex', alignItems: 'center',
                                                                    justifyContent: 'center', textDecoration: 'none' }} title="Analytics">
                                                                <LuChartBar size={14}/>
                                                            </Link>
                                                            <button
                                                                style={{ width: 30, height: 30, borderRadius: 8, background: '#FEF2F2', color: '#DC2626',
                                                                    border: '1px solid #FECACA', display: 'inline-flex', alignItems: 'center',
                                                                    justifyContent: 'center', cursor: 'pointer' }}
                                                                onClick={() => deleteCampaign(ec.delete_url)} title="Delete">
                                                                <LuTrash2 size={14}/>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>

                            {campaigns.last_page > 1 && (
                                <div className="ec-pager">
                                    <small style={{ color: MUT }}>
                                        Showing {(campaigns.current_page - 1) * campaigns.per_page + 1}–{Math.min(campaigns.current_page * campaigns.per_page, campaigns.total)} of {campaigns.total}
                                    </small>
                                    <Pagination links={campaigns.links} lastPage={campaigns.last_page}/>
                                </div>
                            )}
                        </>
                    )}
                </Card>

            </div>
        </>
    );
}
