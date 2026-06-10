import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuFilter, LuUsers, LuPhone, LuCheck, LuTrendingUp, LuChartBar,
    LuCalendar, LuChevronLeft, LuChevronRight, LuX, LuMail,
} from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

const STATUS_CFG = {
    active:    { bg: '#dcfce7', color: '#16a34a' },
    paused:    { bg: '#fef9c3', color: '#ca8a04' },
    completed: { bg: '#f1f5f9', color: '#64748b' },
    draft:     { bg: '#ede9fe', color: '#7c3aed' },
};

function KpiCard({ icon: Icon, label, value, sub, accentColor, highlight }) {
    const bg = highlight ? OR : WH;
    return (
        <div style={{
            background: bg,
            borderRadius: 14, padding: '18px 16px',
            border: highlight ? 'none' : `1px solid ${BOR}`,
            height: '100%', position: 'relative', overflow: 'hidden',
            boxShadow: highlight ? `0 4px 14px ${OR}40` : '0 2px 8px rgba(0,0,0,0.04)',
        }}>
            {highlight && (
                <div style={{ position: 'absolute', top: -20, right: -20, width: 80, height: 80, borderRadius: '50%', background: 'rgba(255,255,255,0.1)' }} />
            )}
            <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 12 }}>
                <div style={{
                    width: 42, height: 42, borderRadius: 11,
                    background: highlight ? 'rgba(255,255,255,0.2)' : (accentColor ?? OR) + '18',
                    display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
                }}>
                    <Icon size={20} color={highlight ? '#fff' : (accentColor ?? OR)} />
                </div>
            </div>
            <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 4, color: highlight ? 'rgba(255,255,255,0.8)' : MUT }}>
                {label}
            </div>
            <div style={{ fontSize: 26, fontWeight: 800, lineHeight: 1, color: highlight ? '#fff' : DK }}>
                {value}
            </div>
            {sub && (
                <div style={{ fontSize: 11, marginTop: 5, color: highlight ? 'rgba(255,255,255,0.7)' : MUT }}>
                    {sub}
                </div>
            )}
        </div>
    );
}

function RateMetric({ label, value, color, icon: Icon }) {
    const pct = Math.min(100, Number(value ?? 0));
    return (
        <div style={{ marginBottom: 18 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                    <Icon size={16} color={color} />
                    <span style={{ fontSize: 13, fontWeight: 600, color: BDY }}>{label}</span>
                </div>
                <span style={{ fontSize: 18, fontWeight: 800, color }}>{pct}%</span>
            </div>
            <div style={{ height: 8, background: BOR, borderRadius: 99 }}>
                <div style={{ height: 8, width: `${pct}%`, background: color, borderRadius: 99, transition: 'width 0.7s ease' }} />
            </div>
        </div>
    );
}

function FunnelStep({ label, count, pct, color, icon: Icon, isLast }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: isLast ? 0 : 14 }}>
            <div style={{ width: 36, height: 36, borderRadius: 9, background: color + '1a', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <Icon size={18} color={color} />
            </div>
            <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                    <span style={{ fontSize: 12, fontWeight: 600, color: BDY }}>{label}</span>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <span style={{ fontSize: 13, fontWeight: 800, color: DK }}>{Number(count ?? 0).toLocaleString()}</span>
                        <span style={{ fontSize: 11, color: MUT, minWidth: 36, textAlign: 'right' }}>{pct}%</span>
                    </div>
                </div>
                <div style={{ height: 6, background: BOR, borderRadius: 99 }}>
                    <div style={{ height: 6, width: `${Math.min(100, Number(pct))}%`, background: color, borderRadius: 99, transition: 'width 0.7s ease' }} />
                </div>
            </div>
        </div>
    );
}

function Pagination({ links, lastPage }) {
    if (!lastPage || lastPage <= 1) return null;
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 4, flexWrap: 'wrap' }}>
            {links.map((link, i) => {
                const isActive   = link.active;
                const isDisabled = !link.url;
                const style = {
                    minWidth: 32, height: 32, borderRadius: 7, border: '1.5px solid',
                    borderColor: isActive ? OR : BOR,
                    background: isActive ? OR : WH,
                    color: isActive ? '#fff' : isDisabled ? MUT : BDY,
                    fontWeight: isActive ? 700 : 500, fontSize: 12,
                    cursor: isDisabled ? 'default' : 'pointer',
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                    textDecoration: 'none',
                    boxShadow: isActive ? `0 2px 8px ${OR}40` : 'none',
                };
                return link.url
                    ? <Link key={i} href={link.url} style={style} dangerouslySetInnerHTML={{ __html: link.label }} />
                    : <span key={i} style={style} dangerouslySetInnerHTML={{ __html: link.label }} />;
            })}
        </div>
    );
}

export default function Performance({ campaigns, telecallers, stats, perCampaign, filters }) {
    const s  = stats ?? {};
    const pc = perCampaign ?? { data: [], total: 0, current_page: 1, last_page: 1, links: [] };

    const [campaign,   setCampaign]   = useState(filters?.campaign   ?? '');
    const [telecaller, setTelecaller] = useState(filters?.telecaller ?? '');
    const [dateFrom,   setDateFrom]   = useState(filters?.date_from  ?? '');
    const [dateTo,     setDateTo]     = useState(filters?.date_to    ?? '');
    const [showFilter, setShowFilter] = useState(false);

    const total    = Number(s.total_contacts  ?? 0);
    const called   = Number(s.calls_completed ?? 0);
    const interest = Number(s.interested      ?? 0);
    const conv     = Number(s.converted       ?? 0);

    const funnelSteps = [
        { label: 'Total Contacts',  count: total,    pct: 100,                                                 color: OR,        icon: LuUsers },
        { label: 'Calls Completed', count: called,   pct: total > 0 ? Math.round(called / total * 100) : 0,   color: '#06b6d4', icon: LuPhone },
        { label: 'Interested',      count: interest, pct: total > 0 ? Math.round(interest / total * 100) : 0, color: '#10b981', icon: LuTrendingUp },
        { label: 'Converted',       count: conv,     pct: total > 0 ? Math.round(conv / total * 100) : 0,     color: '#8b5cf6', icon: LuCheck },
    ];

    const hasFilter = !!(filters?.campaign || filters?.telecaller || filters?.date_from || filters?.date_to);

    function applyFilters(e) {
        e.preventDefault();
        const params = {};
        if (campaign)   params.campaign   = campaign;
        if (telecaller) params.telecaller = telecaller;
        if (dateFrom)   params.date_from  = dateFrom;
        if (dateTo)     params.date_to    = dateTo;
        router.get('/manager/campaigns/performance', params, { preserveState: false });
    }

    function resetFilters() {
        setCampaign(''); setTelecaller(''); setDateFrom(''); setDateTo('');
        router.get('/manager/campaigns/performance', {}, { preserveState: false });
    }

    const inputStyle = { borderRadius: 8, borderColor: BOR, fontSize: 13, fontFamily: 'Poppins, sans-serif' };
    const labelStyle = { fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 6, display: 'block' };

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap'); .camp-perf * { font-family: 'Poppins', sans-serif !important; }`}</style>
            <Head title="Campaign Performance" />

            <div className="camp-perf">
                {/* Page Header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 12, marginBottom: 24 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <div style={{ width: 3, height: 32, background: OR, borderRadius: 2 }} />
                        <div>
                            <h2 style={{ fontSize: 22, fontWeight: 800, color: DK, margin: 0 }}>Campaign Performance</h2>
                            <p style={{ fontSize: 13, color: MUT, margin: '2px 0 0' }}>Aggregate analytics across all your campaigns</p>
                        </div>
                    </div>
                    <div style={{ display: 'flex', gap: 10 }}>
                        <button onClick={() => setShowFilter(v => !v)}
                            style={{
                                background: hasFilter ? `${OR}12` : WH,
                                color: hasFilter ? OR : BDY,
                                border: `1.5px solid ${hasFilter ? OR : BOR}`,
                                borderRadius: 9, padding: '8px 14px', fontSize: 13, fontWeight: 600,
                                display: 'flex', alignItems: 'center', gap: 6, cursor: 'pointer',
                                fontFamily: 'Poppins, sans-serif',
                            }}>
                            <LuFilter size={15} />
                            Filters{hasFilter ? ' •' : ''}
                        </button>
                        <Link href="/manager/campaigns"
                            style={{ background: DK, color: '#fff', border: 'none', borderRadius: 9, padding: '8px 14px', fontSize: 13, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 6, textDecoration: 'none' }}>
                            <LuChartBar size={15} />
                            All Campaigns
                        </Link>
                    </div>
                </div>

                {/* Filter Panel */}
                {showFilter && (
                    <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, padding: 20, marginBottom: 24, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
                        <form onSubmit={applyFilters} className="row g-3 align-items-end">
                            <div className="col-md-3">
                                <label style={labelStyle}>Campaign</label>
                                <select className="form-select form-select-sm" value={campaign} onChange={e => setCampaign(e.target.value)}
                                    style={inputStyle}>
                                    <option value="">All Campaigns</option>
                                    {campaigns.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>
                            <div className="col-md-3">
                                <label style={labelStyle}>Telecaller</label>
                                <select className="form-select form-select-sm" value={telecaller} onChange={e => setTelecaller(e.target.value)}
                                    style={inputStyle}>
                                    <option value="">All Telecallers</option>
                                    {telecallers.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                </select>
                            </div>
                            <div className="col-md-2">
                                <label style={labelStyle}>From</label>
                                <input type="date" className="form-control form-control-sm" value={dateFrom} onChange={e => setDateFrom(e.target.value)}
                                    style={inputStyle} />
                            </div>
                            <div className="col-md-2">
                                <label style={labelStyle}>To</label>
                                <input type="date" className="form-control form-control-sm" value={dateTo} onChange={e => setDateTo(e.target.value)}
                                    style={inputStyle} />
                            </div>
                            <div className="col-md-2 d-flex gap-2">
                                <button type="submit" className="btn btn-sm flex-grow-1"
                                    style={{ background: OR, color: '#fff', borderRadius: 8, fontWeight: 600, border: 'none', fontSize: 13, fontFamily: 'Poppins, sans-serif' }}>
                                    Apply
                                </button>
                                <button type="button" className="btn btn-sm btn-light" onClick={resetFilters}
                                    style={{ borderRadius: 8 }}>
                                    <LuX size={13} />
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {/* KPI Cards */}
                <div className="row g-3 mb-4">
                    {[
                        { icon: LuUsers,    label: 'Total Contacts',    value: total.toLocaleString(),                                highlight: true },
                        { icon: LuUsers,    label: 'Assigned',          value: Number(s.assigned ?? 0).toLocaleString(),             accentColor: '#f59e0b', sub: total > 0 ? `${Math.round(Number(s.assigned ?? 0) / total * 100)}% of total` : null },
                        { icon: LuPhone,    label: 'Calls Completed',   value: called.toLocaleString(),                              accentColor: '#06b6d4', sub: total > 0 ? `${Math.round(called / total * 100)}% contact rate` : null },
                        { icon: LuMail,     label: 'WhatsApp Sent',     value: Number(s.whatsapp_sent ?? 0).toLocaleString(),        accentColor: '#25D366', sub: 'outbound messages' },
                        { icon: LuTrendingUp, label: 'Interested',      value: interest.toLocaleString(),                           accentColor: '#10b981', sub: called > 0 ? `${Math.round(interest / called * 100)}% of calls` : null },
                        { icon: LuX,        label: 'Not Interested',    value: Number(s.not_interested ?? 0).toLocaleString(),       accentColor: '#ef4444', sub: called > 0 ? `${Math.round(Number(s.not_interested ?? 0) / called * 100)}% of calls` : null },
                        { icon: LuCalendar, label: 'Pending Follow-up', value: Number(s.followups_pending ?? 0).toLocaleString(),    accentColor: '#f97316', sub: 'awaiting callback' },
                        { icon: LuCheck,    label: 'Converted',         value: conv.toLocaleString(),                                accentColor: '#8b5cf6', sub: total > 0 ? `${Math.round(conv / total * 100)}% conv. rate` : null },
                    ].map((k, i) => (
                        <div key={i} className="col-6 col-md-3 col-xl-3">
                            <KpiCard {...k} />
                        </div>
                    ))}
                </div>

                <div className="row g-4 mb-4">
                    {/* Conversion Funnel */}
                    <div className="col-lg-5">
                        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', height: '100%', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '14px 20px', borderBottom: `1px solid ${BOR}` }}>
                                <div style={{ width: 3, height: 26, background: OR, borderRadius: 2 }} />
                                <span style={{ color: DK, fontWeight: 700, fontSize: 14 }}>Conversion Funnel</span>
                            </div>
                            <div style={{ padding: 20 }}>
                                {funnelSteps.map((step, i) => (
                                    <FunnelStep key={i} {...step} isLast={i === funnelSteps.length - 1} />
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Performance Rates */}
                    <div className="col-lg-7">
                        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', height: '100%', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '14px 20px', borderBottom: `1px solid ${BOR}` }}>
                                <div style={{ width: 3, height: 26, background: OR, borderRadius: 2 }} />
                                <span style={{ color: DK, fontWeight: 700, fontSize: 14 }}>Performance Rates</span>
                            </div>
                            <div style={{ padding: 20 }}>
                                <RateMetric label="Contact Rate"    value={s.contact_rate    ?? 0} color="#06b6d4" icon={LuPhone} />
                                <RateMetric label="Interest Rate"   value={s.interest_rate   ?? 0} color="#10b981" icon={LuTrendingUp} />
                                <RateMetric label="Conversion Rate" value={s.conversion_rate ?? 0} color={OR}       icon={LuCheck} />

                                {/* Score cards */}
                                <div className="row g-3 mt-1">
                                    {[
                                        { label: 'Contact Rate',    val: `${s.contact_rate    ?? 0}%`, color: '#06b6d4', bg: '#e0f2fe', desc: 'Calls / Total' },
                                        { label: 'Interest Rate',   val: `${s.interest_rate   ?? 0}%`, color: '#10b981', bg: '#dcfce7', desc: 'Interested / Calls' },
                                        { label: 'Conversion Rate', val: `${s.conversion_rate ?? 0}%`, color: OR,         bg: `${OR}12`, desc: 'Converted / Total' },
                                    ].map((item, i) => (
                                        <div className="col-4" key={i}>
                                            <div style={{ background: item.bg, borderRadius: 12, padding: '14px 12px', textAlign: 'center' }}>
                                                <div style={{ fontSize: 22, fontWeight: 800, color: item.color }}>{item.val}</div>
                                                <div style={{ fontSize: 11, fontWeight: 700, color: item.color, marginTop: 2 }}>{item.label}</div>
                                                <div style={{ fontSize: 10, color: MUT, marginTop: 2 }}>{item.desc}</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Campaign Breakdown Table */}
                <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
                    {/* Header */}
                    <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 8, borderBottom: `1px solid ${BOR}` }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                            <span style={{ color: DK, fontWeight: 700, fontSize: 15 }}>Campaign Breakdown</span>
                            <span style={{ background: `${OR}18`, color: OR, fontSize: 11, fontWeight: 600, padding: '2px 8px', borderRadius: 99 }}>
                                {pc.total ?? 0}
                            </span>
                        </div>
                        <span style={{ color: MUT, fontSize: 12 }}>
                            Page {pc.current_page} of {pc.last_page}
                        </span>
                    </div>

                    {/* Table */}
                    {(pc.data ?? []).length === 0 ? (
                        <div style={{ textAlign: 'center', padding: '48px 20px' }}>
                            <LuChartBar size={48} color={BOR} style={{ display: 'block', margin: '0 auto 10px' }} />
                            <p style={{ color: MUT, marginBottom: 0 }}>No campaign data found.</p>
                        </div>
                    ) : (
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0" style={{ fontSize: 13 }}>
                                <thead>
                                    <tr style={{ background: '#F4F6F8', position: 'sticky', top: 0, zIndex: 1 }}>
                                        {['Campaign', 'Status', 'Contacts', 'Assigned', 'Calls', 'WA Sent', 'Interested', 'Not Interested', 'Follow-ups', 'Converted', 'Conv. Rate'].map((h, i) => (
                                            <th key={i} style={{ padding: '10px 14px', color: BDY, fontWeight: 700, fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.05em', borderBottom: `2px solid ${BOR}`, whiteSpace: 'nowrap' }}>{h}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {(pc.data ?? []).map((row, i) => {
                                        const sCfg = STATUS_CFG[row.status] ?? { bg: '#f1f5f9', color: '#64748b' };
                                        const callPct = row.total_contacts > 0 ? Math.round(row.calls_completed / row.total_contacts * 100) : 0;
                                        return (
                                            <tr key={i} style={{ borderBottom: `1px solid ${BOR}` }}
                                                onMouseEnter={e => e.currentTarget.style.background = `${OR}08`}
                                                onMouseLeave={e => e.currentTarget.style.background = ''}>
                                                <td style={{ padding: '12px 14px', fontWeight: 700, color: DK, maxWidth: 160 }}>
                                                    <div style={{ whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{row.name}</div>
                                                </td>
                                                <td style={{ padding: '12px 14px' }}>
                                                    <span style={{ background: sCfg.bg, color: sCfg.color, fontSize: 11, fontWeight: 600, padding: '3px 9px', borderRadius: 99 }}>
                                                        {row.status}
                                                    </span>
                                                </td>
                                                <td style={{ padding: '12px 14px', fontWeight: 600, color: DK }}>
                                                    {(row.total_contacts ?? 0).toLocaleString()}
                                                </td>
                                                <td style={{ padding: '12px 14px', color: BDY }}>
                                                    {(row.assigned ?? 0).toLocaleString()}
                                                </td>
                                                <td style={{ padding: '12px 14px' }}>
                                                    <div>
                                                        <span style={{ fontWeight: 600, color: '#0284c7' }}>{(row.calls_completed ?? 0).toLocaleString()}</span>
                                                        <div style={{ height: 3, background: BOR, borderRadius: 99, marginTop: 4, width: 50 }}>
                                                            <div style={{ height: 3, width: `${callPct}%`, background: '#06b6d4', borderRadius: 99 }} />
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style={{ padding: '12px 14px', color: BDY }}>
                                                    {(row.whatsapp_sent ?? 0).toLocaleString()}
                                                </td>
                                                <td style={{ padding: '12px 14px' }}>
                                                    <span style={{ background: '#dcfce7', color: '#16a34a', fontSize: 12, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>
                                                        {row.interested ?? 0}
                                                    </span>
                                                </td>
                                                <td style={{ padding: '12px 14px' }}>
                                                    <span style={{ background: '#fee2e2', color: '#dc2626', fontSize: 12, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>
                                                        {row.not_interested ?? 0}
                                                    </span>
                                                </td>
                                                <td style={{ padding: '12px 14px', color: '#f59e0b', fontWeight: 600 }}>
                                                    {row.followups_pending ?? 0}
                                                </td>
                                                <td style={{ padding: '12px 14px' }}>
                                                    <span style={{ background: `${OR}18`, color: OR, fontSize: 12, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>
                                                        {row.converted ?? 0}
                                                    </span>
                                                </td>
                                                <td style={{ padding: '12px 14px' }}>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                                        <div style={{ height: 6, width: 50, background: BOR, borderRadius: 99, flexShrink: 0 }}>
                                                            <div style={{ height: 6, width: `${Math.min(100, row.conversion_rate ?? 0)}%`, background: OR, borderRadius: 99 }} />
                                                        </div>
                                                        <span style={{ fontSize: 12, fontWeight: 700, color: OR, whiteSpace: 'nowrap' }}>{row.conversion_rate ?? 0}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Pagination */}
                    {pc.last_page > 1 && (
                        <div style={{ padding: '12px 20px', borderTop: `1px solid ${BOR}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 8 }}>
                            <span style={{ fontSize: 12, color: BDY }}>
                                Showing {((pc.current_page - 1) * pc.per_page) + 1}–{Math.min(pc.current_page * pc.per_page, pc.total)} of {pc.total} campaigns
                            </span>
                            <Pagination links={pc.links} lastPage={pc.last_page} />
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
