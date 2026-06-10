import { Head, Link, router } from '@inertiajs/react';
import {
    LuArrowLeft, LuTrash2, LuUsers, LuCheck, LuMailOpen,
    LuMousePointerClick, LuX, LuMail, LuChevronLeft, LuChevronRight,
} from 'react-icons/lu';

/* ── Design tokens ─────────────────────────────────────────── */
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

const CARD = {
    background: WH, border: `1px solid ${BOR}`,
    borderRadius: 14, boxShadow: '0 2px 8px rgba(0,0,0,0.04)', padding: '20px 22px',
};

const STATUS_CFG = {
    draft:     { bg: '#F9FAFB', color: MUT,       label: 'Draft'     },
    scheduled: { bg: '#EFF6FF', color: '#2563EB',  label: 'Scheduled' },
    sending:   { bg: '#FFFBEB', color: '#D97706',  label: 'Sending'   },
    completed: { bg: '#F0FDF4', color: '#16A34A',  label: 'Completed' },
    failed:    { bg: '#FEF2F2', color: '#DC2626',  label: 'Failed'    },
};

const R_STATUS_CFG = {
    pending: { bg: '#F9FAFB', color: MUT       },
    sent:    { bg: '#F0FDF4', color: '#16A34A' },
    failed:  { bg: '#FEF2F2', color: '#DC2626' },
    bounced: { bg: '#FFFBEB', color: '#D97706' },
    opened:  { bg: '#FFF5EF', color: OR        },
};

function StatusBadge({ status, map }) {
    const cfg = map[status] ?? { bg: '#F9FAFB', color: MUT };
    const label = status ? status.charAt(0).toUpperCase() + status.slice(1) : '—';
    return (
        <span style={{ display: 'inline-block', padding: '3px 10px', borderRadius: 20, background: cfg.bg, color: cfg.color, fontSize: 11, fontWeight: 700 }}>
            {label}
        </span>
    );
}

function ProgressBar({ label, value, max, rate, color }) {
    return (
        <div style={{ ...CARD }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6, fontSize: 13 }}>
                <span style={{ fontWeight: 700, color: DK }}>{label}</span>
                <span style={{ color: MUT, fontSize: 12 }}>{value}/{max}</span>
            </div>
            <div style={{ height: 7, background: BOR, borderRadius: 4, overflow: 'hidden' }}>
                <div style={{ width: `${rate}%`, height: '100%', background: color, borderRadius: 4, transition: 'width .5s' }} />
            </div>
            <div style={{ fontSize: 11, color: MUT, marginTop: 5 }}>{rate}% {label.toLowerCase()}</div>
        </div>
    );
}

function KpiCard({ Icon, label, value, color }) {
    return (
        <div style={{ ...CARD, textAlign: 'center', padding: '16px 12px' }}>
            <div style={{ width: 38, height: 38, borderRadius: 10, background: `${color}14`, display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 10px' }}>
                <Icon size={18} color={color} />
            </div>
            <div style={{ fontSize: 11, color: MUT, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: 4 }}>{label}</div>
            <div style={{ fontSize: 22, fontWeight: 800, color: DK }}>{(value ?? 0).toLocaleString()}</div>
        </div>
    );
}

export default function Show({ campaign, recipients }) {
    const c = campaign;

    function handleDelete() {
        if (!window.confirm('Delete this campaign?')) return;
        router.delete(c.delete_url);
    }

    const statusCfg = STATUS_CFG[c.status] ?? STATUS_CFG.draft;

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');.ec-show{font-family:'Poppins',sans-serif;}.ec-show .table-hover tbody tr:hover td{background:rgba(255,92,0,0.04)!important;}.ec-page-active .page-link{background:${OR}!important;border-color:${OR}!important;color:#fff!important;}`}</style>
            <Head title={`${c.name} — Analytics`} />

            <div className="ec-show">
                {/* ── Header ── */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 24, flexWrap: 'wrap' }}>
                    <Link href="/manager/email-campaigns"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 12px', borderRadius: 8, background: WH, border: `1px solid ${BOR}`, color: BDY, textDecoration: 'none', fontSize: 13, fontWeight: 600 }}>
                        <LuArrowLeft size={16} />Back
                    </Link>
                    <div style={{ flex: 1 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            <div style={{ width: 4, height: 26, background: OR, borderRadius: 2 }} />
                            <h2 style={{ fontSize: 18, fontWeight: 800, color: DK, margin: 0 }}>{c.name}</h2>
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 6, marginLeft: 12 }}>
                            <span style={{ display: 'inline-block', padding: '3px 10px', borderRadius: 20, background: statusCfg.bg, color: statusCfg.color, fontSize: 11, fontWeight: 700 }}>
                                {statusCfg.label}
                            </span>
                            <span style={{ fontSize: 12, color: MUT }}>
                                Template: <strong style={{ color: BDY }}>{c.template_name}</strong>
                                {c.course_filter && <> — Course: <strong style={{ color: BDY }}>{c.course_filter}</strong></>}
                            </span>
                        </div>
                    </div>
                    <button
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '8px 14px', borderRadius: 8, background: '#FEF2F2', color: '#DC2626', border: '1px solid #FECACA', fontWeight: 600, fontSize: 13, cursor: 'pointer' }}
                        onClick={handleDelete}>
                        <LuTrash2 size={15} />Delete
                    </button>
                </div>

                {/* ── Count KPIs ── */}
                <div className="row g-3 mb-3">
                    {[
                        { label: 'Recipients', value: c.recipients_count, Icon: LuUsers,           color: OR        },
                        { label: 'Sent',       value: c.sent_count,       Icon: LuMail,            color: '#2563EB' },
                        { label: 'Opened',     value: c.opened_count,     Icon: LuMailOpen,        color: '#16A34A' },
                        { label: 'Clicked',    value: c.click_count,      Icon: LuMousePointerClick,color: '#7C3AED'},
                        { label: 'Bounced',    value: c.bounced_count,    Icon: LuX,               color: '#D97706' },
                        { label: 'Failed',     value: c.failed_count,     Icon: LuX,               color: '#DC2626' },
                    ].map(stat => (
                        <div className="col-6 col-md-2" key={stat.label}>
                            <KpiCard {...stat} />
                        </div>
                    ))}
                </div>

                {/* ── Rate KPIs ── */}
                <div className="row g-3 mb-4">
                    {[
                        { label: 'Delivery Rate', value: `${c.delivery_rate}%`, Icon: LuMail,             color: '#16A34A' },
                        { label: 'Open Rate',     value: `${c.open_rate}%`,     Icon: LuMailOpen,         color: OR        },
                        { label: 'Click Rate',    value: `${c.click_rate}%`,    Icon: LuMousePointerClick,color: '#7C3AED' },
                        { label: 'Bounce Rate',   value: `${c.bounce_rate}%`,   Icon: LuX,                color: '#DC2626' },
                    ].map(stat => (
                        <div className="col-6 col-md-3" key={stat.label}>
                            <div style={{ ...CARD, display: 'flex', alignItems: 'center', gap: 12 }}>
                                <div style={{ width: 40, height: 40, borderRadius: 10, background: `${stat.color}14`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                    <stat.Icon size={18} color={stat.color} />
                                </div>
                                <div>
                                    <div style={{ fontSize: 11, color: MUT, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{stat.label}</div>
                                    <div style={{ fontSize: 22, fontWeight: 800, color: stat.color, lineHeight: 1.2 }}>{stat.value ?? '0%'}</div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* ── Progress Bars ── */}
                <div className="row g-3 mb-4">
                    <div className="col-md-3"><ProgressBar label="Delivery" value={c.sent_count} max={c.recipients_count} rate={c.delivery_rate} color={OR} /></div>
                    <div className="col-md-3"><ProgressBar label="Opens"    value={c.opened_count} max={c.sent_count}       rate={c.open_rate}     color="#16A34A" /></div>
                    <div className="col-md-3"><ProgressBar label="Clicks"   value={c.click_count}  max={c.sent_count}       rate={c.click_rate}    color="#7C3AED" /></div>
                    <div className="col-md-3"><ProgressBar label="Bounces"  value={c.bounced_count} max={c.sent_count}      rate={c.bounce_rate}   color="#EF4444" /></div>
                </div>

                {/* ── Recipients Table ── */}
                <div style={{ ...CARD, padding: 0, overflow: 'hidden' }}>
                    {/* Header bar */}
                    <div style={{ background: DK, padding: '14px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                            <LuUsers size={18} color="#fff" />
                            <div>
                                <div style={{ color: '#fff', fontWeight: 700, fontSize: 15 }}>Recipients</div>
                                <div style={{ color: 'rgba(255,255,255,0.5)', fontSize: 12 }}>{(c.recipients_count ?? 0).toLocaleString()} total</div>
                            </div>
                        </div>
                    </div>

                    {recipients.data.length === 0 ? (
                        <div style={{ textAlign: 'center', padding: '40px 20px', color: MUT, fontSize: 13 }}>No recipients found.</div>
                    ) : (
                        <>
                            <div className="table-responsive">
                                <table className="table table-hover align-middle table-sm mb-0" style={{ fontSize: 13 }}>
                                    <thead>
                                        <tr style={{ background: '#F4F6F8' }}>
                                            {['Email', 'Name', 'Status', 'Sent At', 'Opened At'].map(h => (
                                                <th key={h} style={{ padding: '10px 14px', fontSize: 11, fontWeight: 700, color: BDY, textTransform: 'uppercase', letterSpacing: '0.05em', borderBottom: `2px solid ${BOR}` }}>{h}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recipients.data.map(r => (
                                            <tr key={r.id} style={{ borderBottom: `1px solid ${BOR}` }}>
                                                <td style={{ padding: '10px 14px', fontSize: 13 }}>{r.email}</td>
                                                <td style={{ padding: '10px 14px', color: MUT, fontSize: 13 }}>{r.name || '—'}</td>
                                                <td style={{ padding: '10px 14px' }}>
                                                    <StatusBadge status={r.status} map={R_STATUS_CFG} />
                                                    {r.opened_at && (
                                                        <span style={{ display: 'inline-block', padding: '3px 9px', borderRadius: 20, background: '#FFF5EF', color: OR, fontSize: 11, fontWeight: 700, marginLeft: 6 }}>Opened</span>
                                                    )}
                                                </td>
                                                <td style={{ padding: '10px 14px', color: MUT, fontSize: 12 }}>{r.sent_at ?? '—'}</td>
                                                <td style={{ padding: '10px 14px', color: MUT, fontSize: 12 }}>{r.opened_at ?? '—'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {recipients.last_page > 1 && (
                                <div style={{ padding: '12px 20px', borderTop: `1px solid ${BOR}` }}>
                                    <nav>
                                        <ul className="pagination pagination-sm mb-0">
                                            {recipients.links.map((link, i) => (
                                                <li key={i} className={['page-item', link.active ? 'ec-page-active active' : '', !link.url ? 'disabled' : ''].join(' ')}>
                                                    {link.url
                                                        ? <Link href={link.url} className="page-link" dangerouslySetInnerHTML={{ __html: link.label }} />
                                                        : <span className="page-link" dangerouslySetInnerHTML={{ __html: link.label }} />
                                                    }
                                                </li>
                                            ))}
                                        </ul>
                                    </nav>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </>
    );
}
