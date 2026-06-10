import { Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import {
    PieChart, Pie, Cell, Tooltip as RcTooltip,
    BarChart, Bar, XAxis, YAxis, CartesianGrid, ResponsiveContainer,
} from 'recharts';
import ReportFilters from './_Filters';
import {
    LuChartBar, LuTrendingUp, LuUsers, LuPhone, LuCalendar,
    LuChartPie, LuFileSpreadsheet, LuFileText, LuChevronDown, LuChevronUp,
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
    borderRadius: 14, boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
};

/* ── Nav config ─────────────────────────────────────────────── */
const NAV = [
    { href: '/manager/reports/home',                   label: 'Overview'              },
    { href: '/manager/reports/telecaller-performance', label: 'Telecaller Performance'},
    { href: '/manager/reports/conversion',             label: 'Conversion'            },
    { href: '/manager/reports/source-performance',     label: 'Source Performance'    },
    { href: '/manager/reports/period',                 label: 'Period Analysis'       },
    { href: '/manager/reports/response-time',          label: 'Response Time'         },
    { href: '/manager/reports/call-efficiency',        label: 'Call Efficiency'       },
];

const COLORS = [OR,'#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#EC4899','#0EA5E9','#84CC16','#F97316'];

/* ── Report Nav Bar ─────────────────────────────────────────── */
function ReportNav({ active }) {
    return (
        <div style={{
            background: WH, border: `1.5px solid ${BOR}`, borderRadius: 14,
            padding: '6px 8px', marginBottom: 22, display: 'flex', gap: 4,
            flexWrap: 'wrap', boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
        }}>
            {NAV.map(n => {
                const isActive = active === n.href;
                return (
                    <Link key={n.href} href={n.href} style={{
                        display: 'inline-flex', alignItems: 'center', gap: 6,
                        padding: '8px 14px', borderRadius: 10,
                        fontSize: 12.5, fontWeight: isActive ? 700 : 500,
                        background: isActive ? OR : 'transparent',
                        color: isActive ? '#ffffff' : MUT,
                        textDecoration: 'none',
                        transition: 'all 0.18s',
                        boxShadow: isActive ? '0 4px 12px rgba(255,92,0,0.28)' : 'none',
                        whiteSpace: 'nowrap',
                    }}>
                        {n.label}
                    </Link>
                );
            })}
        </div>
    );
}
export function ReportNavBar({ active }) { return <ReportNav active={active} />; }

/* ── Helpers ─────────────────────────────────────────────────── */
const capFirst = s => s ? s.charAt(0).toUpperCase() + s.slice(1) : '—';

function fmtSecs(secs) {
    const h = Math.floor(secs / 3600), m = Math.floor((secs % 3600) / 60);
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function Avatar({ name }) {
    const initials = (name || '?').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    const color = COLORS[(name?.charCodeAt(0) ?? 0) % COLORS.length];
    return (
        <div style={{ width: 34, height: 34, borderRadius: '50%', background: color, flexShrink: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontSize: 12, fontWeight: 700 }}>{initials}</div>
    );
}

function ScoreBar({ score }) {
    const s = parseFloat(score) || 0;
    const color = s >= 70 ? '#10B981' : s >= 40 ? '#F59E0B' : '#EF4444';
    const bg    = s >= 70 ? 'rgba(16,185,129,0.1)' : s >= 40 ? 'rgba(245,158,11,0.1)' : 'rgba(239,68,68,0.1)';
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 7, minWidth: 110 }}>
            <div style={{ flex: 1, height: 7, background: BOR, borderRadius: 4 }}>
                <div style={{ width: `${Math.min(100, s)}%`, height: '100%', background: color, borderRadius: 4 }} />
            </div>
            <span style={{ fontSize: 11, fontWeight: 800, color, background: bg, padding: '2px 7px', borderRadius: 20, minWidth: 34, textAlign: 'center' }}>{s}</span>
        </div>
    );
}

/* ── StatRow — telecaller horizontal KPI ────────────────────── */
function StatRow({ Icon, label, value, sub, orange }) {
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
                color: orange ? '#fff' : OR }}><Icon size={15}/></div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 9, fontWeight: 600, textTransform: 'uppercase',
                    letterSpacing: '0.5px', marginBottom: 1,
                    color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize: 18, fontWeight: 800, lineHeight: 1,
                    color: orange ? '#fff' : DK }}>{value ?? 0}</div>
                {sub && <div style={{ fontSize: 9.5, marginTop: 2,
                    color: orange ? 'rgba(255,255,255,0.65)' : MUT }}>{sub}</div>}
            </div>
        </div>
    );
}

/* ── KPI Card ────────────────────────────────────────────────── */
const KPI_GRADIENTS = {
    orange: `linear-gradient(90deg,${OR},#FF8C42)`,
    amber:  'linear-gradient(90deg,#F59E0B,#FBBF24)',
    green:  'linear-gradient(90deg,#10B981,#34D399)',
    purple: 'linear-gradient(90deg,#8B5CF6,#A78BFA)',
    cyan:   'linear-gradient(90deg,#06B6D4,#22D3EE)',
    blue:   'linear-gradient(90deg,#3B82F6,#60A5FA)',
};

function KpiCard({ Icon, cls, label, value, sub, subColor }) {
    const grad = KPI_GRADIENTS[cls] ?? KPI_GRADIENTS.orange;
    return (
        <div style={{ ...CARD, padding: '18px 20px 16px', position: 'relative', overflow: 'hidden', height: '100%', transition: 'transform 0.22s, box-shadow 0.22s', cursor: 'default' }}
            onMouseEnter={e => { e.currentTarget.style.transform = 'translateY(-3px)'; e.currentTarget.style.boxShadow = `0 8px 28px rgba(255,92,0,0.10)`; }}
            onMouseLeave={e => { e.currentTarget.style.transform = ''; e.currentTarget.style.boxShadow = '0 2px 8px rgba(0,0,0,0.04)'; }}>
            <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 3, background: grad, borderRadius: '14px 14px 0 0' }} />
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 14, marginTop: 4 }}>
                <div style={{ width: 40, height: 40, borderRadius: 10, background: grad, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: `0 4px 12px rgba(255,92,0,0.20)` }}>
                    <Icon size={18} color="#fff" />
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontSize: 11, color: MUT, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: 4 }}>{label}</div>
                    <div style={{ fontSize: 26, fontWeight: 800, color: DK, lineHeight: 1 }}>{value}</div>
                    {sub && <div style={{ fontSize: 11, color: subColor ?? MUT, marginTop: 5, fontWeight: 600 }}>{sub}</div>}
                </div>
            </div>
        </div>
    );
}

/* ── Section card wrapper ────────────────────────────────────── */
function Card({ title, sub, badge, children }) {
    return (
        <div style={{ ...CARD, padding: '20px 22px', marginBottom: 0, height: '100%' }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 16 }}>
                <div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <div style={{ width: 3, height: 18, background: OR, borderRadius: 2 }} />
                        <h6 style={{ fontWeight: 700, fontSize: 15, margin: 0, color: DK }}>{title}</h6>
                    </div>
                    {sub && <span style={{ fontSize: 11, color: MUT, marginLeft: 11 }}>{sub}</span>}
                </div>
                {badge && <div>{badge}</div>}
            </div>
            {children}
        </div>
    );
}

/* ── Tooltips ────────────────────────────────────────────────── */
const ttStyle = { background: WH, border: `1px solid ${BOR}`, borderRadius: 10, fontSize: 12, padding: '8px 12px', boxShadow: '0 4px 16px rgba(0,0,0,0.08)', fontFamily: 'Poppins, sans-serif' };

function PieTip({ active, payload }) {
    if (!active || !payload?.length) return null;
    const d = payload[0];
    return (
        <div style={ttStyle}>
            <div style={{ fontWeight: 700, marginBottom: 2 }}>{capFirst(d.name)}</div>
            <div style={{ color: MUT }}>{d.value} leads · {d.payload.share}%</div>
        </div>
    );
}
function BarTip({ active, payload, label }) {
    if (!active || !payload?.length) return null;
    return (
        <div style={ttStyle}>
            <div style={{ fontWeight: 700, marginBottom: 4 }}>{label}</div>
            {payload.map((p, i) => <div key={i} style={{ color: p.color, fontWeight: 600 }}>{p.name}: {p.value}</div>)}
        </div>
    );
}

/* ═══════════════════════════════════════ MAIN PAGE ═══════════════════════════════════════ */
export default function Home({
    filters, filterOptions,
    totalLeads, contactedLeads, convertedLeads, conversionRate,
    activeTelecallers, funnel, sourceRows, enquiredCourseRows, finalCourseRows, telecallerRows,
}) {
    const [sortCol, setSortCol] = useState('efficiency_score');
    const [sortDir, setSortDir] = useState('desc');

    const rows = useMemo(() => {
        if (!telecallerRows?.length) return [];
        return [...telecallerRows].sort((a, b) => {
            const av = a[sortCol] ?? 0, bv = b[sortCol] ?? 0;
            if (typeof av === 'string') return sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
            return sortDir === 'asc' ? av - bv : bv - av;
        });
    }, [telecallerRows, sortCol, sortDir]);

    function toggleSort(col) {
        if (sortCol === col) setSortDir(d => d === 'desc' ? 'asc' : 'desc');
        else { setSortCol(col); setSortDir('desc'); }
    }

    const totalCalls    = useMemo(() => (telecallerRows ?? []).reduce((s, r) => s + (r.calls || 0), 0), [telecallerRows]);
    const totalTalkSecs = useMemo(() => (telecallerRows ?? []).reduce((s, r) => s + (r.total_duration_secs || 0), 0), [telecallerRows]);
    const totalFups     = useMemo(() => (telecallerRows ?? []).reduce((s, r) => s + (r.followups || 0), 0), [telecallerRows]);
    const maxCalls      = useMemo(() => Math.max(1, ...rows.map(r => r.calls || 0)), [rows]);
    const contactPct    = totalLeads > 0 ? Math.round((contactedLeads / totalLeads) * 100) : 0;

    const sourcePieData = useMemo(() =>
        (sourceRows ?? []).map((r, i) => ({
            name: r.source || 'Unknown', value: r.total,
            share: totalLeads > 0 ? Math.round((r.total / totalLeads) * 100) : 0,
            fill: COLORS[i % COLORS.length],
        })), [sourceRows, totalLeads]);

    const tcBarData = useMemo(() =>
        rows.slice(0, 10).map(r => ({
            name: r.name.split(' ')[0],
            Assigned: r.assigned || 0, Calls: r.calls || 0, Converted: r.converted || 0,
        })), [rows]);

    const courseBarData = useMemo(() =>
        (enquiredCourseRows ?? []).map(r => ({ name: r.course_name, Total: r.total, Converted: r.converted ?? 0 })),
        [enquiredCourseRows]);

    const finalCoursePieData = useMemo(() =>
        (finalCourseRows ?? []).map((r, i) => ({ name: r.course_name, value: r.total, fill: COLORS[i % COLORS.length] })),
        [finalCourseRows]);

    const exportQs = new URLSearchParams({ date_range: filters?.date_range ?? '30', source: filters?.source ?? 'all', telecaller: filters?.telecaller ?? 'all' }).toString();

    const thBase = { padding: '12px 14px', fontSize: 11, color: BDY, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', borderBottom: `2px solid ${BOR}`, whiteSpace: 'nowrap', position: 'sticky', top: 0, zIndex: 2 };

    const SortTh = ({ col, children, right }) => (
        <th onClick={() => toggleSort(col)} style={{
            ...thBase, textAlign: right ? 'right' : 'left',
            background: sortCol === col ? '#FFF5EF' : '#F4F6F8',
            color: sortCol === col ? OR : BDY, cursor: 'pointer',
        }}>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 3 }}>
                {children}
                {sortCol === col && sortDir === 'asc' ? <LuChevronUp size={12} /> : <LuChevronDown size={12} style={{ opacity: sortCol === col ? 1 : 0.35 }} />}
            </span>
        </th>
    );
    const Th = ({ children, right }) => (
        <th style={{ ...thBase, textAlign: right ? 'right' : 'left', background: '#F4F6F8' }}>{children}</th>
    );

    const funnelStages = [
        { label: 'New / Assigned', key: 'new',       color: OR,        bg: `${OR}08`        },
        { label: 'Contacted',      key: 'contacted',  color: '#F59E0B', bg: 'rgba(245,158,11,0.07)' },
        { label: 'Interested',     key: 'interested', color: '#8B5CF6', bg: 'rgba(139,92,246,0.07)' },
        { label: 'Converted',      key: 'converted',  color: '#10B981', bg: 'rgba(16,185,129,0.07)' },
    ];

    return (
        <>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .rpt-home, .rpt-home div, .rpt-home span:not([class*="material"]),
                .rpt-home p, .rpt-home h1, .rpt-home h2, .rpt-home h3, .rpt-home h4, .rpt-home h5, .rpt-home h6,
                .rpt-home button, .rpt-home input, .rpt-home select, .rpt-home a,
                .rpt-home th, .rpt-home td, .rpt-home label, .rpt-home small {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .rpt-home .rpt-table tbody tr:hover td { background: rgba(255,92,0,0.04) !important; }
                .rpt-kpi { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:14px; }
                @media(max-width:960px){ .rpt-kpi{ grid-template-columns:repeat(2,1fr); } }
            `}</style>
            <Head title="Reports Overview" />

            <div className="rpt-home">
                <ReportNav active="/manager/reports/home" />
                <ReportFilters filters={filters} filterOptions={filterOptions} url="/manager/reports/home" exportSlug="overview" />

                {/* ── KPI Row 1 ── */}
                <div className="rpt-kpi">
                    <StatRow Icon={LuUsers}      label="Total Leads"      value={totalLeads}     sub={`${contactedLeads} contacted (${contactPct}%)`} orange={true}  />
                    <StatRow Icon={LuPhone}       label="Contacted"        value={contactedLeads} sub={`${contactPct}% contact rate`}                  orange={false} />
                    <StatRow Icon={LuTrendingUp}  label="Converted"        value={convertedLeads} sub={`${conversionRate}% conversion rate`}            orange={false} />
                    <StatRow Icon={LuTrendingUp}  label="Conversion Rate"  value={`${conversionRate}%`} sub={`${convertedLeads} of ${totalLeads} leads`} orange={false} />
                </div>

                {/* ── KPI Row 2 ── */}
                <div className="rpt-kpi">
                    <StatRow Icon={LuUsers}    label="Active Telecallers" value={activeTelecallers}    sub={`${(telecallerRows ?? []).length} total staff`} orange={false} />
                    <StatRow Icon={LuPhone}    label="Total Calls"         value={totalCalls}           sub="all telecallers combined"                      orange={false} />
                    <StatRow Icon={LuCalendar} label="Total Talk Time"     value={fmtSecs(totalTalkSecs)} sub="cumulative talk time"                        orange={false} />
                    <StatRow Icon={LuCalendar} label="Follow-ups"          value={totalFups}            sub="in selected period"                            orange={false} />
                </div>

                {/* ── Funnel + Source ── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-5">
                        <Card title="Lead Funnel" sub="Stage-wise breakdown"
                            badge={<span style={{ background: OR, color: '#fff', fontSize: 12, fontWeight: 700, padding: '4px 12px', borderRadius: 20, boxShadow: `0 2px 8px ${OR}40` }}>{totalLeads} Total</span>}>
                            {funnelStages.map(stage => {
                                const val = funnel?.[stage.key] ?? 0;
                                const pct = totalLeads > 0 ? Math.round((val / totalLeads) * 100) : 0;
                                return (
                                    <div key={stage.key} className="mb-3 p-3 rounded-3" style={{ background: stage.bg }}>
                                        <div className="d-flex justify-content-between align-items-center mb-2">
                                            <div className="d-flex align-items-center gap-2">
                                                <div style={{ width: 10, height: 10, borderRadius: '50%', background: stage.color, flexShrink: 0 }} />
                                                <span style={{ fontSize: 13, fontWeight: 500, color: BDY }}>{stage.label}</span>
                                            </div>
                                            <div className="d-flex align-items-center gap-3">
                                                <span style={{ fontSize: 11, color: MUT, fontWeight: 600 }}>{pct}%</span>
                                                <span style={{ fontSize: 15, fontWeight: 800, color: stage.color, minWidth: 28, textAlign: 'right' }}>{val}</span>
                                            </div>
                                        </div>
                                        <div style={{ height: 7, background: 'rgba(255,255,255,0.7)', borderRadius: 4 }}>
                                            <div style={{ width: `${pct}%`, height: '100%', background: stage.color, borderRadius: 4, transition: 'width 0.5s' }} />
                                        </div>
                                    </div>
                                );
                            })}
                        </Card>
                    </div>

                    <div className="col-md-7">
                        <Card title="Leads by Source" sub={`${sourcePieData.length} active sources`}>
                            {sourcePieData.length === 0
                                ? <div className="text-center text-muted py-4">No source data</div>
                                : (
                                    <div className="row g-0 align-items-center">
                                        <div className="col-5">
                                            <ResponsiveContainer width="100%" height={200}>
                                                <PieChart>
                                                    <Pie data={sourcePieData} dataKey="value" cx="50%" cy="50%" innerRadius={54} outerRadius={80} paddingAngle={3}>
                                                        {sourcePieData.map((d, i) => <Cell key={i} fill={d.fill} stroke="none" />)}
                                                    </Pie>
                                                    <RcTooltip content={<PieTip />} />
                                                </PieChart>
                                            </ResponsiveContainer>
                                        </div>
                                        <div className="col-7">
                                            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
                                                <tbody>
                                                    {sourcePieData.map((d, i) => (
                                                        <tr key={i} style={{ borderBottom: `1px solid ${BOR}` }}>
                                                            <td style={{ padding: '8px 4px' }}>
                                                                <div className="d-flex align-items-center gap-2">
                                                                    <div style={{ width: 9, height: 9, borderRadius: '50%', background: d.fill, flexShrink: 0 }} />
                                                                    <span style={{ fontWeight: 500 }}>{capFirst(d.name)}</span>
                                                                </div>
                                                            </td>
                                                            <td style={{ padding: '8px 4px', minWidth: 60 }}>
                                                                <div style={{ height: 6, background: BOR, borderRadius: 3 }}>
                                                                    <div style={{ width: `${d.share}%`, height: '100%', background: d.fill, borderRadius: 3 }} />
                                                                </div>
                                                            </td>
                                                            <td style={{ padding: '8px 6px', textAlign: 'right', fontWeight: 800, color: d.fill }}>{d.value}</td>
                                                            <td style={{ padding: '8px 4px', textAlign: 'right' }}>
                                                                <span style={{ fontSize: 11, fontWeight: 700, padding: '2px 7px', borderRadius: 20, background: `${d.fill}18`, color: d.fill }}>{d.share}%</span>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                )}
                        </Card>
                    </div>
                </div>

                {/* ── Lead by Course + Final Selected ── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-7">
                        <Card title="Leads by Course" sub="Active pipeline by enquired course (not yet enrolled)">
                            {(enquiredCourseRows ?? []).length === 0
                                ? <div className="text-center text-muted py-4">No course data available</div>
                                : (
                                    <>
                                        <ResponsiveContainer width="100%" height={Math.max(180, (enquiredCourseRows ?? []).length * 40)}>
                                            <BarChart data={courseBarData} layout="vertical" margin={{ top: 2, right: 40, left: 8, bottom: 2 }}>
                                                <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke={BOR} />
                                                <XAxis type="number" tick={{ fontSize: 11, fill: MUT }} axisLine={false} tickLine={false} />
                                                <YAxis type="category" dataKey="name" tick={{ fontSize: 11, fill: BDY, fontWeight: 500 }} width={135} axisLine={false} tickLine={false} tickFormatter={v => v.length > 20 ? v.slice(0, 19) + '…' : v} />
                                                <RcTooltip content={<BarTip />} />
                                                <Bar dataKey="Total" fill={OR} radius={[0, 4, 4, 0]} barSize={18} name="Active Leads" />
                                            </BarChart>
                                        </ResponsiveContainer>
                                        <div className="d-flex gap-3 mt-2" style={{ fontSize: 11 }}>
                                            <span className="d-flex align-items-center gap-1">
                                                <span style={{ width: 10, height: 10, borderRadius: 2, background: OR, display: 'inline-block' }} /> Active Leads (pipeline)
                                            </span>
                                        </div>
                                    </>
                                )}
                        </Card>
                    </div>

                    <div className="col-md-5">
                        <Card title="Final Selected Course" sub="Enrolled / admitted students by course">
                            {(finalCourseRows ?? []).length === 0
                                ? <div className="text-center text-muted py-4">No enrolled students yet</div>
                                : (
                                    <>
                                        <ResponsiveContainer width="100%" height={190}>
                                            <PieChart>
                                                <Pie data={finalCoursePieData} dataKey="value" cx="50%" cy="50%" innerRadius={50} outerRadius={78} paddingAngle={3}>
                                                    {finalCoursePieData.map((d, i) => <Cell key={i} fill={d.fill} stroke="none" />)}
                                                </Pie>
                                                <RcTooltip contentStyle={ttStyle} formatter={(val, name) => [`${val} enrolled`, name]} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                        <div style={{ marginTop: 8 }}>
                                            {finalCoursePieData.map((d, i) => (
                                                <div key={i} className="d-flex align-items-center gap-2 mb-2">
                                                    <div style={{ width: 10, height: 10, borderRadius: 2, background: d.fill, flexShrink: 0 }} />
                                                    <span style={{ fontSize: 12, flex: 1, fontWeight: 500, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', color: BDY }}>{d.name}</span>
                                                    <span style={{ fontSize: 12, fontWeight: 800, color: d.fill }}>{d.value}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </>
                                )}
                        </Card>
                    </div>
                </div>

                {/* ── Telecaller Bar Chart ── */}
                {tcBarData.length > 0 && (
                    <div style={{ ...CARD, padding: '20px 22px', marginBottom: 20 }}>
                        <div className="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                    <div style={{ width: 3, height: 18, background: OR, borderRadius: 2 }} />
                                    <h6 style={{ fontWeight: 700, fontSize: 15, margin: 0, color: DK }}>Telecaller Performance</h6>
                                </div>
                                <span style={{ fontSize: 11, color: MUT, marginLeft: 11 }}>Assigned · Calls · Converted comparison</span>
                            </div>
                        </div>
                        <ResponsiveContainer width="100%" height={230}>
                            <BarChart data={tcBarData} margin={{ top: 5, right: 20, left: 0, bottom: 5 }} barGap={3}>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={BOR} />
                                <XAxis dataKey="name" tick={{ fontSize: 12, fill: BDY, fontWeight: 500 }} axisLine={false} tickLine={false} />
                                <YAxis tick={{ fontSize: 11, fill: MUT }} axisLine={false} tickLine={false} />
                                <RcTooltip content={<BarTip />} />
                                <Bar dataKey="Assigned"  fill="#FFE0CC" radius={[4,4,0,0]} barSize={20} name="Assigned"  />
                                <Bar dataKey="Calls"     fill={OR}      radius={[4,4,0,0]} barSize={20} name="Calls"     />
                                <Bar dataKey="Converted" fill="#10B981"  radius={[4,4,0,0]} barSize={20} name="Converted" />
                            </BarChart>
                        </ResponsiveContainer>
                        <div className="d-flex gap-4 justify-content-center mt-1" style={{ fontSize: 12 }}>
                            {[['#FFE0CC',OR,'Assigned'],[OR,OR,'Calls'],['#10B981','#10B981','Converted']].map(([bg,c,l]) => (
                                <span key={l} className="d-flex align-items-center gap-1">
                                    <span style={{ width: 12, height: 12, borderRadius: 3, background: bg, border: `1.5px solid ${c}`, display: 'inline-block' }} />
                                    <span style={{ fontWeight: 600, color: BDY }}>{l}</span>
                                </span>
                            ))}
                        </div>
                    </div>
                )}

                {/* ── Telecaller Summary Table ── */}
                {rows.length > 0 && (
                    <div style={{ ...CARD, padding: '20px 22px', marginBottom: 32 }}>
                        <div className="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                    <div style={{ width: 3, height: 18, background: OR, borderRadius: 2 }} />
                                    <h6 style={{ fontWeight: 700, fontSize: 16, margin: 0, color: DK }}>Telecaller Summary</h6>
                                </div>
                                <span style={{ fontSize: 12, color: MUT, marginLeft: 11 }}>{rows.length} telecaller{rows.length !== 1 ? 's' : ''} · click headers to sort</span>
                            </div>
                            <div className="d-flex gap-2">
                                <a href={`/manager/reports/export/overview/excel?${exportQs}`}
                                    style={{ height: 36, padding: '0 14px', background: '#F0FDF4', color: '#16A34A', border: '1.5px solid #BBF7D0', borderRadius: 8, fontWeight: 700, fontSize: 12, display: 'inline-flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}>
                                    <LuFileSpreadsheet size={15} /> Excel
                                </a>
                                <a href={`/manager/reports/export/overview/pdf?${exportQs}`} target="_blank" rel="noreferrer"
                                    style={{ height: 36, padding: '0 14px', background: '#FEF2F2', color: '#DC2626', border: '1.5px solid #FECACA', borderRadius: 8, fontWeight: 700, fontSize: 12, display: 'inline-flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}>
                                    <LuFileText size={15} /> PDF
                                </a>
                            </div>
                        </div>

                        <div style={{ border: `1.5px solid ${BOR}`, borderRadius: 12 }}>
                            <div style={{ overflowX: 'auto', borderRadius: 12 }}>
                                <table className="rpt-table" style={{ width: '100%', borderCollapse: 'collapse', minWidth: 980 }}>
                                    <thead>
                                        <tr>
                                            <Th>#</Th>
                                            <SortTh col="name">Telecaller</SortTh>
                                            <SortTh col="assigned" right>Assigned</SortTh>
                                            <SortTh col="attended" right>Attended</SortTh>
                                            <SortTh col="calls" right>Calls</SortTh>
                                            <SortTh col="total_duration_secs" right>Talk Time</SortTh>
                                            <SortTh col="avg_talk_time">Avg Talk</SortTh>
                                            <SortTh col="followups" right>Follow-ups</SortTh>
                                            <SortTh col="whatsapp_sent" right>WhatsApp</SortTh>
                                            <SortTh col="converted" right>Converted</SortTh>
                                            <SortTh col="conversion_rate" right>Conv.%</SortTh>
                                            <SortTh col="efficiency_score">Score</SortTh>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rows.map((r, i) => {
                                            const attPct = r.assigned > 0 ? Math.round((r.attended / r.assigned) * 100) : 0;
                                            const cBarW  = Math.round(((r.calls || 0) / maxCalls) * 100);
                                            const isTop  = i === 0;
                                            const bg     = isTop ? '#FFF8F5' : i % 2 === 0 ? WH : '#FAFAFA';
                                            return (
                                                <tr key={r.id ?? i} style={{ background: bg, borderBottom: `1px solid ${BOR}` }}>
                                                    <td style={{ padding: '13px 14px', textAlign: 'center', width: 42 }}>
                                                        {isTop
                                                            ? <span style={{ fontSize: 18, color: '#F59E0B' }}>★</span>
                                                            : <span style={{ fontSize: 12, color: MUT, fontWeight: 700 }}>{i + 1}</span>}
                                                    </td>
                                                    <td style={{ padding: '13px 14px' }}>
                                                        <div className="d-flex align-items-center gap-2">
                                                            <Avatar name={r.name} />
                                                            <div>
                                                                <div style={{ fontSize: 13, fontWeight: 700, color: DK }}>{r.name}</div>
                                                                <div style={{ fontSize: 11, color: MUT, marginTop: 1 }}>
                                                                    <span style={{ color: OR, fontWeight: 600 }}>{r.calls_inbound ?? 0}</span> in ·{' '}
                                                                    <span style={{ color: '#10B981', fontWeight: 600 }}>{r.calls_outbound ?? 0}</span> out ·{' '}
                                                                    <span style={{ color: '#EF4444', fontWeight: 600 }}>{r.calls_missed ?? 0}</span> miss
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right', fontSize: 13, fontWeight: 700 }}>{r.assigned}</td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right' }}>
                                                        <div style={{ fontSize: 13, fontWeight: 700 }}>{r.attended}</div>
                                                        <div style={{ fontSize: 11, fontWeight: 700, color: attPct >= 75 ? '#10B981' : attPct >= 40 ? '#F59E0B' : '#EF4444' }}>{attPct}%</div>
                                                    </td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right' }}>
                                                        <div style={{ fontSize: 13, fontWeight: 800 }}>{r.calls}</div>
                                                        <div style={{ height: 4, background: BOR, borderRadius: 2, marginTop: 4, minWidth: 48 }}>
                                                            <div style={{ width: `${cBarW}%`, height: '100%', background: OR, borderRadius: 2 }} />
                                                        </div>
                                                    </td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right', fontSize: 12, fontFamily: 'monospace', fontWeight: 700 }}>{r.total_talk_time}</td>
                                                    <td style={{ padding: '13px 14px', fontSize: 12, fontFamily: 'monospace', color: MUT }}>{r.avg_talk_time}</td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right' }}>
                                                        <div style={{ fontSize: 13 }}>{r.followups}</div>
                                                        {r.followups_pending > 0 && <div style={{ fontSize: 11, color: '#EF4444', fontWeight: 700 }}>{r.followups_pending} pending</div>}
                                                    </td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right', fontSize: 13 }}>{r.whatsapp_sent ?? 0}</td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right' }}>
                                                        <span style={{ display: 'inline-block', padding: '3px 11px', borderRadius: 20, fontWeight: 800, fontSize: 12, background: r.converted > 0 ? '#ECFDF5' : '#F8FAFC', color: r.converted > 0 ? '#10B981' : MUT, border: r.converted > 0 ? '1.5px solid #6EE7B7' : `1.5px solid ${BOR}` }}>{r.converted}</span>
                                                    </td>
                                                    <td style={{ padding: '13px 14px', textAlign: 'right' }}>
                                                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 6 }}>
                                                            <div style={{ width: 42, height: 6, background: BOR, borderRadius: 3 }}>
                                                                <div style={{ width: `${Math.min(100, r.conversion_rate)}%`, height: '100%', borderRadius: 3, background: r.conversion_rate >= 10 ? '#10B981' : r.conversion_rate >= 5 ? '#F59E0B' : '#EF4444' }} />
                                                            </div>
                                                            <span style={{ fontSize: 12, fontWeight: 700, minWidth: 36 }}>{r.conversion_rate}%</span>
                                                        </div>
                                                    </td>
                                                    <td style={{ padding: '13px 14px', minWidth: 130 }}><ScoreBar score={r.efficiency_score} /></td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                    <tfoot>
                                        <tr style={{ background: '#F4F6F8', borderTop: `2px solid ${BOR}` }}>
                                            <td colSpan={2} style={{ padding: '11px 14px', fontSize: 11, fontWeight: 800, color: MUT, textTransform: 'uppercase', letterSpacing: '0.06em' }}>Totals / Averages</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 13, fontWeight: 800 }}>{rows.reduce((s,r)=>s+(r.assigned||0),0)}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 13, fontWeight: 800 }}>{rows.reduce((s,r)=>s+(r.attended||0),0)}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 13, fontWeight: 800 }}>{totalCalls}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 12, fontWeight: 800, fontFamily: 'monospace' }}>{fmtSecs(totalTalkSecs)}</td>
                                            <td style={{ padding: '11px 14px' }} />
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 13, fontWeight: 800 }}>{totalFups}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 13, fontWeight: 800 }}>{rows.reduce((s,r)=>s+(r.whatsapp_sent||0),0)}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}><span style={{ fontWeight: 800, fontSize: 13, color: '#10B981' }}>{convertedLeads}</span></td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontSize: 12, fontWeight: 800 }}>{conversionRate}%</td>
                                            <td style={{ padding: '11px 14px' }} />
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {rows.length === 0 && (
                    <div style={{ ...CARD, padding: '48px 20px', textAlign: 'center' }}>
                        <LuChartBar size={44} color={BOR} style={{ display: 'block', margin: '0 auto 12px' }} />
                        <div style={{ color: MUT, fontWeight: 600 }}>No telecaller data for the selected period</div>
                    </div>
                )}
            </div>
        </>
    );
}
