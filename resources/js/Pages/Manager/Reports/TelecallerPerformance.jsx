import { Head } from '@inertiajs/react';
import { useMemo } from 'react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip as RcTooltip,
    ResponsiveContainer, Cell, PieChart, Pie,
} from 'recharts';
import ReportFilters from './_Filters';
import { ReportNavBar } from './Home';
import {
    LuUsers, LuPhone, LuClock, LuTrendingUp, LuCheck, LuX,
    LuFileSpreadsheet, LuFileText, LuExternalLink,
} from 'react-icons/lu';

/* ── Design tokens ─────────────────────────────────────────── */
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

const CARD = { background: WH, border: `1px solid ${BOR}`, borderRadius: 14, boxShadow: '0 2px 8px rgba(0,0,0,0.04)' };

const COLORS = [OR,'#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#EC4899','#0EA5E9','#84CC16','#F97316'];
const RANK_MEDAL = ['#F59E0B','#9CA3AF','#CD7F32'];

const KPI_GRADIENTS = {
    orange: `linear-gradient(90deg,${OR},#FF8C42)`,
    amber:  'linear-gradient(90deg,#F59E0B,#FBBF24)',
    green:  'linear-gradient(90deg,#10B981,#34D399)',
    purple: 'linear-gradient(90deg,#8B5CF6,#A78BFA)',
    cyan:   'linear-gradient(90deg,#06B6D4,#22D3EE)',
    red:    'linear-gradient(90deg,#EF4444,#F87171)',
};

function fmtSecs(secs) {
    const h = Math.floor(secs / 3600), m = Math.floor((secs % 3600) / 60);
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function Avatar({ name }) {
    const initials = (name || '?').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    const color = COLORS[(name?.charCodeAt(0) ?? 0) % COLORS.length];
    return (
        <div style={{ width: 36, height: 36, borderRadius: '50%', background: color, flexShrink: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontSize: 12, fontWeight: 700 }}>{initials}</div>
    );
}

function KpiCard({ Icon, cls, label, value, sub, subColor }) {
    const grad = KPI_GRADIENTS[cls] ?? KPI_GRADIENTS.orange;
    return (
        <div style={{ ...CARD, padding: '18px 20px 16px', position: 'relative', overflow: 'hidden', height: '100%', transition: 'transform 0.22s', cursor: 'default' }}
            onMouseEnter={e => { e.currentTarget.style.transform = 'translateY(-3px)'; e.currentTarget.style.boxShadow = `0 8px 28px rgba(255,92,0,0.10)`; }}
            onMouseLeave={e => { e.currentTarget.style.transform = ''; e.currentTarget.style.boxShadow = '0 2px 8px rgba(0,0,0,0.04)'; }}>
            <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 3, background: grad, borderRadius: '14px 14px 0 0' }} />
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 14, marginTop: 4 }}>
                <div style={{ width: 40, height: 40, borderRadius: 10, background: grad, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
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

function ScoreBar({ score }) {
    const s = parseFloat(score) || 0;
    const grade = s >= 70 ? 'A' : s >= 40 ? 'B' : s >= 20 ? 'C' : 'D';
    const color = s >= 70 ? '#10B981' : s >= 40 ? '#F59E0B' : s >= 20 ? OR : '#EF4444';
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 7, minWidth: 120 }}>
            <div style={{ width: 28, height: 28, borderRadius: 8, background: color + '18', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12, fontWeight: 800, color, flexShrink: 0 }}>{grade}</div>
            <div style={{ flex: 1 }}>
                <div style={{ height: 6, background: BOR, borderRadius: 3 }}>
                    <div style={{ width: `${Math.min(100, s)}%`, height: '100%', background: color, borderRadius: 3 }} />
                </div>
                <div style={{ fontSize: 11, fontWeight: 700, color, marginTop: 2 }}>{s}</div>
            </div>
        </div>
    );
}

const ttStyle = { background: WH, border: `1px solid ${BOR}`, borderRadius: 10, fontSize: 12, padding: '8px 12px', boxShadow: '0 4px 16px rgba(0,0,0,0.08)', fontFamily: 'Poppins, sans-serif' };

function BarTip({ active, payload, label }) {
    if (!active || !payload?.length) return null;
    return (
        <div style={ttStyle}>
            <div style={{ fontWeight: 700, marginBottom: 4 }}>{label}</div>
            {payload.map((p, i) => <div key={i} style={{ color: p.color, fontWeight: 600 }}>{p.name}: {p.value}</div>)}
        </div>
    );
}
function PieTip({ active, payload }) {
    if (!active || !payload?.length) return null;
    const d = payload[0];
    return (
        <div style={ttStyle}>
            <div style={{ fontWeight: 700 }}>{d.name}</div>
            <div style={{ color: MUT }}>{d.value} calls · {d.payload.share}%</div>
        </div>
    );
}

function ChartCard({ title, sub, children, style }) {
    return (
        <div style={{ ...CARD, padding: '20px 22px', marginBottom: 0, ...style, height: '100%' }}>
            <div className="mb-3">
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <div style={{ width: 3, height: 18, background: OR, borderRadius: 2 }} />
                    <h6 style={{ fontWeight: 700, fontSize: 15, margin: 0, color: DK }}>{title}</h6>
                </div>
                {sub && <span style={{ fontSize: 11, color: MUT, marginLeft: 11 }}>{sub}</span>}
            </div>
            {children}
        </div>
    );
}

export default function TelecallerPerformance({ filters, filterOptions, rows, aggStats }) {
    const safeRows = rows ?? [];

    const totalAssigned  = useMemo(() => safeRows.reduce((s, r) => s + (r.assigned || 0), 0), [safeRows]);
    const totalTalkSecs  = useMemo(() => safeRows.reduce((s, r) => s + (r.total_duration_secs || 0), 0), [safeRows]);
    const totalInbound   = useMemo(() => safeRows.reduce((s, r) => s + (r.calls_inbound  || 0), 0), [safeRows]);
    const totalOutbound  = useMemo(() => safeRows.reduce((s, r) => s + (r.calls_outbound || 0), 0), [safeRows]);
    const totalConnected = useMemo(() => safeRows.reduce((s, r) => s + (r.calls_connected|| 0), 0), [safeRows]);
    const totalAttended  = useMemo(() => safeRows.reduce((s, r) => s + (r.attended       || 0), 0), [safeRows]);
    const totalFups      = useMemo(() => safeRows.reduce((s, r) => s + (r.followups      || 0), 0), [safeRows]);
    const totalWA        = useMemo(() => safeRows.reduce((s, r) => s + (r.whatsapp_sent  || 0), 0), [safeRows]);

    const totalConverted  = aggStats?.total_converted ?? 0;
    const totalCalls      = aggStats?.total_calls     ?? 0;
    const totalMissed     = aggStats?.total_missed    ?? 0;
    const conversionRate  = totalAssigned > 0 ? ((totalConverted / totalAssigned) * 100).toFixed(1) : '0.0';
    const missRate        = totalCalls    > 0 ? Math.round((totalMissed / totalCalls) * 100) : 0;
    const maxCalls        = Math.max(1, ...safeRows.map(r => r.calls || 0));

    const barData = useMemo(() => safeRows.slice(0, 10).map(r => ({ name: r.name.split(' ')[0], Assigned: r.assigned || 0, Calls: r.calls || 0, Converted: r.converted || 0 })), [safeRows]);
    const scoreData = useMemo(() => [...safeRows].sort((a,b)=>(b.efficiency_score||0)-(a.efficiency_score||0)).slice(0,10).map(r=>({ name: r.name.split(' ')[0], Score: parseFloat(r.efficiency_score)||0 })), [safeRows]);
    const talkData  = useMemo(() => safeRows.slice(0,10).map(r=>({ name: r.name.split(' ')[0], Minutes: Math.round((r.total_duration_secs||0)/60) })), [safeRows]);
    const callMix   = useMemo(() => {
        const total = totalInbound + totalOutbound + totalMissed;
        return [
            { name: 'Inbound',  value: totalInbound,  fill: '#10B981', share: total>0?Math.round((totalInbound/total)*100):0 },
            { name: 'Outbound', value: totalOutbound, fill: OR,        share: total>0?Math.round((totalOutbound/total)*100):0 },
            { name: 'Missed',   value: totalMissed,   fill: '#EF4444', share: total>0?Math.round((totalMissed/total)*100):0 },
        ];
    }, [totalInbound, totalOutbound, totalMissed]);

    const exportParams = new URLSearchParams({ date_range: filters?.date_range??'30', source: filters?.source??'all', telecaller: filters?.telecaller??'all', campaign: filters?.campaign??'all', call_type: filters?.call_type??'all' }).toString();
    const detailUrl = id => `/manager/reports/telecaller-detail?telecaller=${id}&date_range=${filters?.date_range??'30'}&source=${filters?.source??'all'}&campaign=${filters?.campaign??'all'}&call_type=${filters?.call_type??'all'}`;

    const thBase = { padding: '12px 14px', fontSize: 11, color: BDY, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', borderBottom: `2px solid ${BOR}`, whiteSpace: 'nowrap', position: 'sticky', top: 0, zIndex: 2, background: '#F4F6F8' };

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.rpt-tc,.rpt-tc div,.rpt-tc span:not([class*="material"]),.rpt-tc p,.rpt-tc h1,.rpt-tc h2,.rpt-tc h3,.rpt-tc h4,.rpt-tc h5,.rpt-tc h6,.rpt-tc button,.rpt-tc input,.rpt-tc select,.rpt-tc a,.rpt-tc th,.rpt-tc td,.rpt-tc label,.rpt-tc small{font-family:'Poppins',sans-serif!important;box-sizing:border-box;}
.rpt-tc .rpt-tbl tbody tr:hover td{background:rgba(255,92,0,0.04)!important;}`}</style>
            <Head title="Telecaller Performance" />

            <div className="rpt-tc">
                <ReportNavBar active="/manager/reports/telecaller-performance" />
                <ReportFilters filters={filters} filterOptions={filterOptions} url="/manager/reports/telecaller-performance" showCampaign showCallType exportSlug="telecaller-performance" />

                {/* ── KPI Row 1 ── */}
                <div className="row g-3 mb-3">
                    <div className="col-6 col-md-3"><KpiCard Icon={LuUsers}      cls="purple" label="Total Telecallers" value={aggStats?.total_telecallers??0} sub={`${safeRows.length} with activity`} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuPhone}      cls="orange" label="Total Calls"       value={totalCalls}                     sub={`${totalInbound} in · ${totalOutbound} out`} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuClock}      cls="cyan"   label="Total Talk Time"   value={aggStats?.total_talk_time??'00:00:00'} sub={`avg ${fmtSecs(safeRows.length>0?Math.round(totalTalkSecs/safeRows.length):0)} / telecaller`} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuUsers}      cls="amber"  label="Total Assigned"    value={totalAssigned}                   sub={`${totalAttended} attended`} /></div>
                </div>

                {/* ── KPI Row 2 ── */}
                <div className="row g-3 mb-4">
                    <div className="col-6 col-md-3"><KpiCard Icon={LuCheck}      cls="green"  label="Converted"         value={totalConverted}                  sub={`${conversionRate}% conversion rate`} subColor={parseFloat(conversionRate)>=10?'#10B981':'#F59E0B'} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuTrendingUp} cls="green"  label="Conversion Rate"   value={`${conversionRate}%`}             sub={`${totalConverted} of ${totalAssigned} leads`} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuPhone}      cls="cyan"   label="WhatsApp Sent"     value={totalWA}                         sub="outbound messages" /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuX}          cls="red"    label="Missed Calls"      value={totalMissed}                     sub={`${missRate}% miss rate`} subColor="#EF4444" /></div>
                </div>

                {/* ── Charts Row 1 ── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-8">
                        <ChartCard title="Performance Overview" sub="Assigned · Calls · Converted per telecaller">
                            {barData.length === 0 ? <div className="text-center text-muted py-4">No data</div> : (
                                <>
                                    <ResponsiveContainer width="100%" height={220}>
                                        <BarChart data={barData} margin={{ top: 4, right: 16, left: 0, bottom: 4 }} barGap={3}>
                                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={BOR} />
                                            <XAxis dataKey="name" tick={{ fontSize: 12, fill: BDY, fontWeight: 500 }} axisLine={false} tickLine={false} />
                                            <YAxis tick={{ fontSize: 11, fill: MUT }} axisLine={false} tickLine={false} />
                                            <RcTooltip content={<BarTip />} />
                                            <Bar dataKey="Assigned"  fill="#FFE0CC" radius={[4,4,0,0]} barSize={18} name="Assigned"  />
                                            <Bar dataKey="Calls"     fill={OR}       radius={[4,4,0,0]} barSize={18} name="Calls"     />
                                            <Bar dataKey="Converted" fill="#10B981"  radius={[4,4,0,0]} barSize={18} name="Converted" />
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
                                </>
                            )}
                        </ChartCard>
                    </div>
                    <div className="col-md-4">
                        <ChartCard title="Call Mix" sub="Inbound · Outbound · Missed breakdown">
                            <ResponsiveContainer width="100%" height={160}>
                                <PieChart>
                                    <Pie data={callMix} dataKey="value" cx="50%" cy="50%" innerRadius={46} outerRadius={68} paddingAngle={3}>
                                        {callMix.map((d, i) => <Cell key={i} fill={d.fill} stroke="none" />)}
                                    </Pie>
                                    <RcTooltip content={<PieTip />} />
                                </PieChart>
                            </ResponsiveContainer>
                            <div className="mt-2">
                                {callMix.map(d => (
                                    <div key={d.name} className="d-flex align-items-center justify-content-between mb-2">
                                        <div className="d-flex align-items-center gap-2">
                                            <div style={{ width: 8, height: 8, borderRadius: '50%', background: d.fill }} />
                                            <span style={{ fontSize: 12, fontWeight: 500, color: BDY }}>{d.name}</span>
                                        </div>
                                        <div className="d-flex align-items-center gap-2">
                                            <span style={{ fontSize: 12, fontWeight: 800, color: d.fill }}>{d.value}</span>
                                            <span style={{ fontSize: 11, padding: '1px 7px', borderRadius: 20, background: d.fill + '18', color: d.fill, fontWeight: 700 }}>{d.share}%</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </ChartCard>
                    </div>
                </div>

                {/* ── Charts Row 2 ── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-6">
                        <ChartCard title="Efficiency Scores" sub="Ranked by performance score (A≥70 · B≥40 · C≥20 · D)">
                            {scoreData.length === 0 ? <div className="text-center text-muted py-4">No data</div> : (
                                <ResponsiveContainer width="100%" height={Math.max(160, scoreData.length * 38)}>
                                    <BarChart data={scoreData} layout="vertical" margin={{ top: 2, right: 50, left: 8, bottom: 2 }}>
                                        <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke={BOR} />
                                        <XAxis type="number" domain={[0,100]} tick={{ fontSize: 11, fill: MUT }} axisLine={false} tickLine={false} />
                                        <YAxis type="category" dataKey="name" tick={{ fontSize: 11, fill: BDY, fontWeight: 500 }} width={65} axisLine={false} tickLine={false} />
                                        <RcTooltip content={<BarTip />} />
                                        <Bar dataKey="Score" radius={[0,5,5,0]} barSize={16} name="Score">
                                            {scoreData.map((d, i) => <Cell key={i} fill={d.Score>=70?'#10B981':d.Score>=40?OR:d.Score>=20?'#F59E0B':'#EF4444'} />)}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            )}
                            <div className="d-flex gap-3 mt-2 flex-wrap" style={{ fontSize: 11 }}>
                                {[['#10B981','A · ≥70'],[OR,'B · ≥40'],['#F59E0B','C · ≥20'],['#EF4444','D · <20']].map(([c,l]) => (
                                    <span key={l} className="d-flex align-items-center gap-1">
                                        <span style={{ width: 10, height: 10, borderRadius: 2, background: c, display: 'inline-block' }} />
                                        <span style={{ fontWeight: 600, color: BDY }}>{l}</span>
                                    </span>
                                ))}
                            </div>
                        </ChartCard>
                    </div>
                    <div className="col-md-6">
                        <ChartCard title="Talk Time per Telecaller" sub="Total talk time in minutes">
                            {talkData.length === 0 ? <div className="text-center text-muted py-4">No data</div> : (
                                <ResponsiveContainer width="100%" height={Math.max(160, talkData.length * 38)}>
                                    <BarChart data={talkData} layout="vertical" margin={{ top: 2, right: 50, left: 8, bottom: 2 }}>
                                        <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke={BOR} />
                                        <XAxis type="number" tick={{ fontSize: 11, fill: MUT }} axisLine={false} tickLine={false} />
                                        <YAxis type="category" dataKey="name" tick={{ fontSize: 11, fill: BDY, fontWeight: 500 }} width={65} axisLine={false} tickLine={false} />
                                        <RcTooltip content={<BarTip />} />
                                        <Bar dataKey="Minutes" radius={[0,5,5,0]} barSize={16} name="Minutes">
                                            {talkData.map((d, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            )}
                        </ChartCard>
                    </div>
                </div>

                {/* ── Performance Table ── */}
                <div style={{ ...CARD, padding: '20px 22px', marginBottom: 32 }}>
                    <div className="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <div style={{ width: 3, height: 18, background: OR, borderRadius: 2 }} />
                                <h6 style={{ fontWeight: 700, fontSize: 16, margin: 0, color: DK }}>Telecaller Performance Ranking</h6>
                            </div>
                            <span style={{ fontSize: 12, color: MUT, marginLeft: 11 }}>{safeRows.length} telecaller{safeRows.length!==1?'s':''} · sorted by efficiency score · click name to view details</span>
                        </div>
                        <div className="d-flex gap-2">
                            <a href={`/manager/reports/export/telecaller-performance/excel?${exportParams}`} style={{ height: 36, padding: '0 14px', background: '#F0FDF4', color: '#16A34A', border: '1.5px solid #BBF7D0', borderRadius: 8, fontWeight: 700, fontSize: 12, display: 'inline-flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}><LuFileSpreadsheet size={15} /> Excel</a>
                            <a href={`/manager/reports/export/telecaller-performance/pdf?${exportParams}`} target="_blank" rel="noreferrer" style={{ height: 36, padding: '0 14px', background: '#FEF2F2', color: '#DC2626', border: '1.5px solid #FECACA', borderRadius: 8, fontWeight: 700, fontSize: 12, display: 'inline-flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}><LuFileText size={15} /> PDF</a>
                        </div>
                    </div>

                    <div style={{ border: `1.5px solid ${BOR}`, borderRadius: 12 }}>
                        <div style={{ overflowX: 'auto', borderRadius: 12 }}>
                            <table className="rpt-tbl" style={{ width: '100%', borderCollapse: 'collapse', minWidth: 1160 }}>
                                <thead>
                                    <tr>
                                        {['#','Telecaller','Assigned','Attended','Total Calls','Inbound','Outbound','Missed','Connected','Talk Time','Avg Talk','WhatsApp','Camp.','Fups','Converted','Conv.%','Score'].map((h,i) => (
                                            <th key={i} style={{ ...thBase, textAlign: i<=1||i>=9&&i<=10 ? 'left' : 'right' }}>{h}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {safeRows.length === 0 ? (
                                        <tr><td colSpan={17} style={{ textAlign: 'center', padding: '48px 0', color: MUT }}>
                                            <LuUsers size={44} color={BOR} style={{ display: 'block', margin: '0 auto 8px' }} />
                                            No telecaller data for the selected period
                                        </td></tr>
                                    ) : safeRows.map((r, i) => {
                                        const attPct = r.assigned>0?Math.round((r.attended/r.assigned)*100):0;
                                        const cBarW  = Math.round(((r.calls||0)/maxCalls)*100);
                                        const isTop  = i===0;
                                        const bg     = isTop?'#FFF8F5':i%2===0?WH:'#FAFAFA';
                                        return (
                                            <tr key={r.id??i} style={{ background: bg, borderBottom: `1px solid ${BOR}` }}>
                                                <td style={{ padding: '13px 14px', textAlign: 'center', width: 44 }}>
                                                    {i<3 ? <span style={{ fontSize: 18, color: RANK_MEDAL[i] }}>★</span> : <span style={{ fontSize: 12, color: MUT, fontWeight: 700 }}>{i+1}</span>}
                                                </td>
                                                <td style={{ padding: '13px 14px' }}>
                                                    <div className="d-flex align-items-center gap-2">
                                                        <Avatar name={r.name} />
                                                        <div>
                                                            <a href={detailUrl(r.id)} style={{ fontSize: 13, fontWeight: 700, color: OR, textDecoration: 'none', display: 'block' }}>{r.name}</a>
                                                            <div style={{ fontSize: 11, color: MUT, marginTop: 1, display: 'flex', alignItems: 'center', gap: 3 }}>
                                                                <LuExternalLink size={10} /> View details
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right',fontSize:13,fontWeight:700 }}>{r.assigned}</td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}>
                                                    <div style={{ fontSize:13,fontWeight:700,color:'#06B6D4' }}>{r.attended}</div>
                                                    <div style={{ fontSize:11,fontWeight:700,color:attPct>=75?'#10B981':attPct>=40?'#F59E0B':'#EF4444' }}>{attPct}%</div>
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}>
                                                    <div style={{ fontSize:13,fontWeight:800 }}>{r.calls}</div>
                                                    <div style={{ height:4,background:BOR,borderRadius:2,marginTop:4,minWidth:52 }}>
                                                        <div style={{ width:`${cBarW}%`,height:'100%',background:OR,borderRadius:2 }} />
                                                    </div>
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#ECFDF5',color:'#10B981',fontWeight:700,fontSize:12 }}>{r.calls_inbound}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#FFF5EF',color:OR,fontWeight:700,fontSize:12 }}>{r.calls_outbound}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#FEF2F2',color:'#EF4444',fontWeight:700,fontSize:12 }}>{r.calls_missed}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#F5F3FF',color:'#8B5CF6',fontWeight:700,fontSize:12 }}>{r.calls_connected}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right',fontSize:12,fontFamily:'monospace',fontWeight:700 }}>{r.total_talk_time}</td>
                                                <td style={{ padding:'13px 14px',fontSize:12,fontFamily:'monospace',color:MUT }}>{r.avg_talk_time}</td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#F0FDF4',color:'#16A34A',fontWeight:700,fontSize:12 }}>{r.whatsapp_sent??0}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right',fontSize:13,color:MUT,fontWeight:600 }}>{r.campaign_calls??0}</td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}>
                                                    <div style={{ fontSize:13 }}>{r.followups}</div>
                                                    {r.followups_pending>0&&<div style={{ fontSize:11,color:'#EF4444',fontWeight:700 }}>{r.followups_pending} pend.</div>}
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}>
                                                    <span style={{ display:'inline-block',padding:'3px 11px',borderRadius:20,fontWeight:800,fontSize:12,background:r.converted>0?'#ECFDF5':'#F8FAFC',color:r.converted>0?'#10B981':MUT,border:r.converted>0?'1.5px solid #6EE7B7':`1.5px solid ${BOR}` }}>{r.converted}</span>
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}>
                                                    <div style={{ display:'flex',alignItems:'center',justifyContent:'flex-end',gap:6 }}>
                                                        <div style={{ width:40,height:6,background:BOR,borderRadius:3 }}>
                                                            <div style={{ width:`${Math.min(100,r.conversion_rate)}%`,height:'100%',borderRadius:3,background:r.conversion_rate>=10?'#10B981':r.conversion_rate>=5?'#F59E0B':'#EF4444' }} />
                                                        </div>
                                                        <span style={{ fontSize:12,fontWeight:700,minWidth:38 }}>{r.conversion_rate}%</span>
                                                    </div>
                                                </td>
                                                <td style={{ padding:'13px 14px',minWidth:130 }}><ScoreBar score={r.efficiency_score} /></td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                                {safeRows.length > 0 && (
                                    <tfoot>
                                        <tr style={{ background: '#F4F6F8', borderTop: `2px solid ${BOR}` }}>
                                            <td colSpan={2} style={{ padding:'11px 14px',fontSize:11,fontWeight:800,color:MUT,textTransform:'uppercase',letterSpacing:'0.06em' }}>Totals</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800 }}>{totalAssigned}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800 }}>{totalAttended}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800 }}>{totalCalls}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800,color:'#10B981' }}>{totalInbound}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800,color:OR }}>{totalOutbound}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800,color:'#EF4444' }}>{totalMissed}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:13,fontWeight:800,color:'#8B5CF6' }}>{totalConnected}</td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:12,fontWeight:800,fontFamily:'monospace' }}>{fmtSecs(totalTalkSecs)}</td>
                                            <td colSpan={4} />
                                            <td style={{ padding:'11px 14px',textAlign:'right' }}><span style={{ fontWeight:800,fontSize:13,color:'#10B981' }}>{totalConverted}</span></td>
                                            <td style={{ padding:'11px 14px',textAlign:'right',fontSize:12,fontWeight:800 }}>{conversionRate}%</td>
                                            <td />
                                        </tr>
                                    </tfoot>
                                )}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
