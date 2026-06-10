import { Head } from '@inertiajs/react';
import { useMemo } from 'react';
import {
    RadarChart, Radar, PolarGrid, PolarAngleAxis, PolarRadiusAxis,
    ScatterChart, Scatter, XAxis, YAxis, ZAxis, CartesianGrid,
    Tooltip as RcTooltip, ResponsiveContainer, Cell,
    BarChart, Bar, LabelList,
} from 'recharts';
import ReportFilters from './_Filters';
import { ReportNavBar } from './Home';
import {
    LuUsers, LuCheck, LuTrendingUp, LuPhone, LuFileSpreadsheet, LuFileText,
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
const RADAR_COLORS = [OR,'#10B981','#F59E0B','#EF4444','#8B5CF6'];
const RANK_MEDAL = ['#F59E0B','#9CA3AF','#CD7F32'];

const KPI_GRADIENTS = {
    orange: `linear-gradient(90deg,${OR},#FF8C42)`,
    amber:  'linear-gradient(90deg,#F59E0B,#FBBF24)',
    green:  'linear-gradient(90deg,#10B981,#34D399)',
    purple: 'linear-gradient(90deg,#8B5CF6,#A78BFA)',
    cyan:   'linear-gradient(90deg,#06B6D4,#22D3EE)',
    red:    'linear-gradient(90deg,#EF4444,#F87171)',
};

const STATUS_COLORS = { new: OR, assigned: '#06B6D4', contacted: '#F59E0B', interested: '#8B5CF6', converted: '#10B981', not_interested: '#EF4444', follow_up: '#F97316' };

const capFirst = s => s ? s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g,' ') : '—';

function Avatar({ name }) {
    const initials = (name||'?').split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
    const color = COLORS[(name?.charCodeAt(0)??0)%COLORS.length];
    return <div style={{ width:34,height:34,borderRadius:'50%',background:color,flexShrink:0,display:'flex',alignItems:'center',justifyContent:'center',color:'#fff',fontSize:12,fontWeight:700 }}>{initials}</div>;
}

function KpiCard({ Icon, cls, label, value, sub, subColor }) {
    const grad = KPI_GRADIENTS[cls] ?? KPI_GRADIENTS.orange;
    return (
        <div style={{ ...CARD, padding:'18px 20px 16px', position:'relative', overflow:'hidden', height:'100%', transition:'transform 0.22s', cursor:'default' }}
            onMouseEnter={e=>{e.currentTarget.style.transform='translateY(-3px)';e.currentTarget.style.boxShadow='0 8px 28px rgba(255,92,0,0.10)';}}
            onMouseLeave={e=>{e.currentTarget.style.transform='';e.currentTarget.style.boxShadow='0 2px 8px rgba(0,0,0,0.04)';}}>
            <div style={{ position:'absolute',top:0,left:0,right:0,height:3,background:grad,borderRadius:'14px 14px 0 0' }} />
            <div style={{ display:'flex',alignItems:'flex-start',gap:14,marginTop:4 }}>
                <div style={{ width:40,height:40,borderRadius:10,background:grad,display:'flex',alignItems:'center',justifyContent:'center',flexShrink:0 }}><Icon size={18} color="#fff" /></div>
                <div style={{ flex:1,minWidth:0 }}>
                    <div style={{ fontSize:11,color:MUT,fontWeight:600,textTransform:'uppercase',letterSpacing:'0.05em',marginBottom:4 }}>{label}</div>
                    <div style={{ fontSize:26,fontWeight:800,color:DK,lineHeight:1 }}>{value}</div>
                    {sub&&<div style={{ fontSize:11,color:subColor??MUT,marginTop:5,fontWeight:600 }}>{sub}</div>}
                </div>
            </div>
        </div>
    );
}

function ChartCard({ title, sub, badge, children, style }) {
    return (
        <div style={{ ...CARD, padding:'20px 22px', marginBottom:0, ...style, height:'100%' }}>
            <div className="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <div style={{ display:'flex',alignItems:'center',gap:8 }}>
                        <div style={{ width:3,height:18,background:OR,borderRadius:2 }} />
                        <h6 style={{ fontWeight:700,fontSize:15,margin:0,color:DK }}>{title}</h6>
                    </div>
                    {sub&&<span style={{ fontSize:11,color:MUT,marginLeft:11 }}>{sub}</span>}
                </div>
                {badge}
            </div>
            {children}
        </div>
    );
}

const ttStyle = { background:WH, border:`1px solid ${BOR}`, borderRadius:10, fontSize:12, padding:'8px 12px', boxShadow:'0 4px 16px rgba(0,0,0,0.08)', fontFamily:'Poppins, sans-serif' };
const thBase = { padding:'12px 14px', fontSize:11, color:BDY, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.06em', borderBottom:`2px solid ${BOR}`, whiteSpace:'nowrap', position:'sticky', top:0, zIndex:2, background:'#F4F6F8' };

function BarTip({ active, payload, label }) {
    if (!active||!payload?.length) return null;
    return <div style={ttStyle}><div style={{ fontWeight:700,marginBottom:4 }}>{label}</div>{payload.map((p,i)=><div key={i} style={{ color:p.color,fontWeight:600 }}>{p.name}: {p.value}</div>)}</div>;
}
function ScatterTip({ active, payload }) {
    if (!active||!payload?.length) return null;
    const d = payload[0]?.payload;
    return <div style={ttStyle}><div style={{ fontWeight:700,marginBottom:4 }}>{capFirst(d?.name||'')}</div><div style={{ color:MUT }}>Total Leads: <b style={{ color:DK }}>{d?.x}</b></div><div style={{ color:MUT }}>Conv. Rate: <b style={{ color:'#10B981' }}>{d?.y}%</b></div><div style={{ color:MUT }}>Converted: <b style={{ color:OR }}>{d?.z}</b></div></div>;
}

export default function Conversion({ filters, filterOptions, statusRows, teleRows, totalLeads, convertedLeads, overallRate, funnel, sourceRows, enquiredCourseRows, finalCourseRows }) {
    const tele    = teleRows        ?? [];
    const sources = sourceRows      ?? [];
    const statuses= statusRows      ?? [];
    const eq      = enquiredCourseRows ?? [];
    const fc      = finalCourseRows    ?? [];

    const funnelNew  = funnel?.new        ?? 0;
    const funnelCont = funnel?.contacted  ?? 0;
    const funnelInt  = funnel?.interested ?? 0;
    const funnelConv = funnel?.converted  ?? 0;
    const base       = funnelNew > 0 ? funnelNew : 1;

    const engagementRate      = totalLeads>0?Math.round(((funnelCont+funnelInt+funnelConv)/totalLeads)*100):0;
    const newToContactRate    = funnelNew>0?Math.round((funnelCont/funnelNew)*100):0;
    const contactToIntRate    = funnelCont>0?Math.round((funnelInt/funnelCont)*100):0;
    const interestToCloseRate = funnelInt>0?Math.round((funnelConv/funnelInt)*100):0;
    const bestPerformer       = tele[0] ?? null;

    const radarTele = tele.slice(0,5);
    const maxTotal  = Math.max(...radarTele.map(r=>r.total||0),1);

    const radarData = useMemo(() =>
        ['Lead Volume','Attend Rate','Interest Rate','Conv. Rate','Lead Quality'].map((subject,idx) => {
            const entry = { subject };
            radarTele.forEach(r => {
                switch(idx) {
                    case 0: entry[r.name]=Math.round((r.total/maxTotal)*100); break;
                    case 1: entry[r.name]=r.total>0?Math.round(((r.attended??0)/r.total)*100):0; break;
                    case 2: entry[r.name]=r.total>0?Math.round(((r.interested??0)/r.total)*100):0; break;
                    case 3: entry[r.name]=Math.min(100,Math.round(r.rate||0)); break;
                    case 4: entry[r.name]=r.total>0?Math.min(100,Math.round((((r.attended??0)+(r.interested??0)+(r.converted??0))/(r.total*2))*100)):0; break;
                    default: break;
                }
            });
            return entry;
        }), [tele]);

    const scatterData = useMemo(() => sources.filter(r=>r.source).map(r=>({ x:r.total, y:parseFloat(r.rate)||0, z:r.converted||1, name:r.source })), [sources]);
    const dropData    = useMemo(() => [
        { stage:'New→Contact',    Retained:funnelCont, Dropped:Math.max(0,funnelNew-funnelCont),  dropPct:funnelNew>0?Math.round(((funnelNew-funnelCont)/funnelNew)*100):0 },
        { stage:'Contact→Interest',Retained:funnelInt,  Dropped:Math.max(0,funnelCont-funnelInt),  dropPct:funnelCont>0?Math.round(((funnelCont-funnelInt)/funnelCont)*100):0 },
        { stage:'Interest→Convert',Retained:funnelConv, Dropped:Math.max(0,funnelInt-funnelConv),  dropPct:funnelInt>0?Math.round(((funnelInt-funnelConv)/funnelInt)*100):0 },
    ], [funnelNew,funnelCont,funnelInt,funnelConv]);

    const exportQs  = new URLSearchParams({ date_range:filters?.date_range??'30', source:filters?.source??'all', telecaller:filters?.telecaller??'all' }).toString();
    const exportUrl = fmt => `/manager/reports/export/conversion/${fmt}?${exportQs}`;

    const FUNNEL_STAGES = [
        { key:'new',       label:'New / Assigned', Icon:LuUsers,       color:OR,        val:funnelNew  },
        { key:'contacted', label:'Contacted',       Icon:LuPhone,       color:'#06B6D4', val:funnelCont },
        { key:'interested',label:'Interested',      Icon:LuCheck,       color:'#F59E0B', val:funnelInt  },
        { key:'converted', label:'Converted',       Icon:LuTrendingUp,  color:'#10B981', val:funnelConv },
    ];

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.rpt-conv,.rpt-conv div,.rpt-conv span:not([class*="material"]),.rpt-conv p,.rpt-conv h1,.rpt-conv h2,.rpt-conv h3,.rpt-conv h4,.rpt-conv h5,.rpt-conv h6,.rpt-conv button,.rpt-conv input,.rpt-conv select,.rpt-conv a,.rpt-conv th,.rpt-conv td,.rpt-conv label,.rpt-conv small{font-family:'Poppins',sans-serif!important;box-sizing:border-box;}
.rpt-conv .rpt-tbl tbody tr:hover td{background:rgba(255,92,0,0.04)!important;}`}</style>
            <Head title="Conversion Report" />
            <div className="rpt-conv">
                <ReportNavBar active="/manager/reports/conversion" />
                <ReportFilters filters={filters} filterOptions={filterOptions} url="/manager/reports/conversion" exportSlug="conversion" />

                {/* ── KPI Row 1 ── */}
                <div className="row g-3 mb-3">
                    <div className="col-6 col-md-3"><KpiCard Icon={LuUsers}      cls="orange" label="Total Leads"       value={totalLeads??0}        sub="in selected period" /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuCheck}      cls="green"  label="Converted"         value={convertedLeads??0}    sub={`of ${totalLeads??0} total leads`} subColor={(overallRate??0)>=10?'#10B981':'#F59E0B'} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuTrendingUp} cls="green"  label="Conversion Rate"   value={`${overallRate??0}%`}  sub="overall rate" subColor={(overallRate??0)>=10?'#10B981':'#F59E0B'} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuTrendingUp} cls="purple" label="Engagement Rate"   value={`${engagementRate}%`} sub="contacted + interested + converted" subColor={engagementRate>=50?'#10B981':'#F59E0B'} /></div>
                </div>

                {/* ── KPI Row 2 ── */}
                <div className="row g-3 mb-4">
                    <div className="col-6 col-md-3"><KpiCard Icon={LuPhone}      cls="cyan"   label="New → Contact Rate"    value={`${newToContactRate}%`}    sub={`${funnelCont} of ${funnelNew} reached`}     subColor={newToContactRate>=50?'#10B981':'#EF4444'} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuCheck}      cls="amber"  label="Contact → Interest Rate" value={`${contactToIntRate}%`}  sub={`${funnelInt} of ${funnelCont} interested`}   subColor={contactToIntRate>=30?'#10B981':'#EF4444'} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuTrendingUp} cls="green"  label="Interest → Close Rate"  value={`${interestToCloseRate}%`} sub={`${funnelConv} of ${funnelInt} closed`}       subColor={interestToCloseRate>=40?'#10B981':'#EF4444'} /></div>
                    <div className="col-6 col-md-3"><KpiCard Icon={LuUsers}      cls="amber"  label="Best Converter"         value={bestPerformer?.name??'—'} sub={bestPerformer?`${bestPerformer.rate}% conv. rate`:'No data'} /></div>
                </div>

                {/* ── Funnel + Radar ── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-5">
                        <ChartCard title="Conversion Funnel" sub="Lead flow with drop-off analysis"
                            badge={<span style={{ background:'#ECFDF5',color:'#10B981',fontSize:12,fontWeight:700,padding:'4px 12px',borderRadius:20,border:'1.5px solid #6EE7B7' }}>{Math.round((funnelConv/base)*100)}% efficiency</span>}>
                            <div style={{ display:'flex',flexDirection:'column',gap:0 }}>
                                {FUNNEL_STAGES.map((stage,idx) => {
                                    const widthPct = base>0?Math.max(20,Math.round((stage.val/base)*100)):20;
                                    const prevVal  = idx>0?FUNNEL_STAGES[idx-1].val:null;
                                    const dropOff  = prevVal&&prevVal>0?Math.round(((prevVal-stage.val)/prevVal)*100):null;
                                    return (
                                        <div key={stage.key}>
                                            {dropOff!==null && (
                                                <div style={{ display:'flex',alignItems:'center',justifyContent:'center',padding:'4px 0',gap:6 }}>
                                                    <div style={{ flex:1,height:1,background:BOR }} />
                                                    <span style={{ fontSize:11,fontWeight:700,padding:'2px 8px',borderRadius:20,background:dropOff>50?'#FEF2F2':'#FFFBEB',color:dropOff>50?'#EF4444':'#D97706' }}>↓ {dropOff}% drop</span>
                                                    <div style={{ flex:1,height:1,background:BOR }} />
                                                </div>
                                            )}
                                            <div style={{ display:'flex',flexDirection:'column',alignItems:'center',marginBottom:2 }}>
                                                <div style={{ width:`${widthPct}%`,minWidth:'30%',background:`linear-gradient(135deg,${stage.color}22,${stage.color}11)`,border:`1.5px solid ${stage.color}40`,borderRadius:10,padding:'12px 16px',display:'flex',alignItems:'center',justifyContent:'space-between',transition:'width 0.5s' }}>
                                                    <div style={{ display:'flex',alignItems:'center',gap:8 }}>
                                                        <div style={{ width:30,height:30,borderRadius:8,background:stage.color+'20',display:'flex',alignItems:'center',justifyContent:'center' }}>
                                                            <stage.Icon size={16} color={stage.color} />
                                                        </div>
                                                        <span style={{ fontSize:13,fontWeight:600,color:BDY,whiteSpace:'nowrap' }}>{stage.label}</span>
                                                    </div>
                                                    <div style={{ display:'flex',alignItems:'center',gap:8,flexShrink:0 }}>
                                                        <span style={{ fontSize:20,fontWeight:800,color:stage.color }}>{stage.val}</span>
                                                        <span style={{ fontSize:11,fontWeight:700,padding:'2px 7px',borderRadius:20,background:stage.color+'18',color:stage.color }}>{Math.round((stage.val/base)*100)}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="mt-3 p-3 rounded-3" style={{ background:'#FAFAFA',border:`1.5px solid ${BOR}` }}>
                                <div className="d-flex justify-content-between align-items-center mb-2">
                                    <span style={{ fontSize:12,fontWeight:700,color:MUT }}>End-to-End Conversion</span>
                                    <span style={{ fontSize:15,fontWeight:800,color:'#10B981' }}>{Math.round((funnelConv/base)*100)}%</span>
                                </div>
                                <div style={{ height:8,background:BOR,borderRadius:4 }}>
                                    <div style={{ width:`${Math.round((funnelConv/base)*100)}%`,height:'100%',background:`linear-gradient(90deg,${OR},#10B981)`,borderRadius:4,transition:'width 0.6s' }} />
                                </div>
                                <div className="d-flex justify-content-between mt-2" style={{ fontSize:11,color:MUT }}>
                                    <span>{funnelNew} started</span><span>{funnelConv} converted</span>
                                </div>
                            </div>
                        </ChartCard>
                    </div>

                    <div className="col-md-7">
                        <ChartCard title="Telecaller Performance Radar" sub="Multi-dimension comparison">
                            {tele.length === 0 ? <div className="text-center text-muted py-4">No telecaller data</div> : (
                                <>
                                    <ResponsiveContainer width="100%" height={280}>
                                        <RadarChart data={radarData} margin={{ top:8,right:24,bottom:8,left:24 }}>
                                            <PolarGrid gridType="polygon" stroke={BOR} />
                                            <PolarAngleAxis dataKey="subject" tick={{ fontSize:11,fill:MUT,fontWeight:600 }} />
                                            <PolarRadiusAxis angle={90} domain={[0,100]} tick={{ fontSize:10,fill:MUT }} tickCount={4} />
                                            {radarTele.map((r,i) => (
                                                <Radar key={r.name} name={r.name} dataKey={r.name} stroke={RADAR_COLORS[i%RADAR_COLORS.length]} fill={RADAR_COLORS[i%RADAR_COLORS.length]} fillOpacity={0.12} strokeWidth={2} />
                                            ))}
                                            <RcTooltip contentStyle={ttStyle} />
                                        </RadarChart>
                                    </ResponsiveContainer>
                                    <div className="d-flex gap-3 flex-wrap justify-content-center mt-1" style={{ fontSize:12 }}>
                                        {radarTele.map((r,i) => (
                                            <span key={r.name} className="d-flex align-items-center gap-1">
                                                <span style={{ width:10,height:10,borderRadius:'50%',background:RADAR_COLORS[i%RADAR_COLORS.length],display:'inline-block' }} />
                                                <span style={{ fontWeight:600,color:BDY }}>{r.name.split(' ')[0]}</span>
                                            </span>
                                        ))}
                                    </div>
                                    <div className="mt-3 p-3 rounded-3" style={{ background:'#FAFAFA',border:`1.5px solid ${BOR}`,fontSize:11,color:MUT }}>
                                        <div className="d-flex gap-4 flex-wrap">
                                            <span><b style={{ color:DK }}>Lead Volume</b> — relative share of assigned leads</span>
                                            <span><b style={{ color:DK }}>Lead Quality</b> — composite of engagement + conversion</span>
                                        </div>
                                    </div>
                                </>
                            )}
                        </ChartCard>
                    </div>
                </div>

                {/* ── Source Scatter + Stage Drop Analysis ── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-6">
                        <ChartCard title="Source Effectiveness Map" sub="Bubble: converted count · X: total leads · Y: conversion rate">
                            {scatterData.length < 2 ? (
                                <div className="text-center text-muted py-4">Need 2+ sources for this chart</div>
                            ) : (
                                <>
                                    <ResponsiveContainer width="100%" height={240}>
                                        <ScatterChart margin={{ top:8,right:24,bottom:24,left:0 }}>
                                            <CartesianGrid strokeDasharray="3 3" stroke={BOR} />
                                            <XAxis type="number" dataKey="x" name="Total Leads" tick={{ fontSize:11,fill:MUT }} axisLine={false} tickLine={false} label={{ value:'Total Leads',position:'insideBottom',offset:-16,fontSize:11,fill:MUT }} />
                                            <YAxis type="number" dataKey="y" name="Conv. Rate" unit="%" tick={{ fontSize:11,fill:MUT }} axisLine={false} tickLine={false} />
                                            <ZAxis type="number" dataKey="z" range={[60,500]} name="Converted" />
                                            <RcTooltip content={<ScatterTip />} />
                                            <Scatter data={scatterData} name="Sources">
                                                {scatterData.map((d,i) => <Cell key={i} fill={COLORS[i%COLORS.length]} fillOpacity={0.75} />)}
                                            </Scatter>
                                        </ScatterChart>
                                    </ResponsiveContainer>
                                    <div className="d-flex gap-2 flex-wrap mt-1">
                                        {scatterData.map((d,i) => (
                                            <span key={i} style={{ display:'inline-flex',alignItems:'center',gap:4,padding:'3px 9px',borderRadius:20,background:COLORS[i%COLORS.length]+'18',fontSize:11,fontWeight:600,color:COLORS[i%COLORS.length] }}>
                                                <span style={{ width:7,height:7,borderRadius:'50%',background:COLORS[i%COLORS.length],display:'inline-block' }} />{capFirst(d.name)}
                                            </span>
                                        ))}
                                    </div>
                                </>
                            )}
                        </ChartCard>
                    </div>
                    <div className="col-md-6">
                        <ChartCard title="Funnel Stage Drop Analysis" sub="Leads retained vs lost at each funnel transition">
                            {funnelNew === 0 ? <div className="text-center text-muted py-4">No funnel data</div> : (
                                <>
                                    <ResponsiveContainer width="100%" height={200}>
                                        <BarChart data={dropData} margin={{ top:4,right:40,left:0,bottom:4 }} barGap={4}>
                                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={BOR} />
                                            <XAxis dataKey="stage" tick={{ fontSize:11,fill:BDY,fontWeight:500 }} axisLine={false} tickLine={false} />
                                            <YAxis tick={{ fontSize:11,fill:MUT }} axisLine={false} tickLine={false} />
                                            <RcTooltip content={<BarTip />} />
                                            <Bar dataKey="Retained" fill="#10B981" radius={[4,4,0,0]} barSize={28} name="Retained" />
                                            <Bar dataKey="Dropped"  fill="#EF4444" radius={[4,4,0,0]} barSize={28} name="Dropped" fillOpacity={0.7} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                    <div className="d-flex gap-4 mt-2">
                                        {[['#10B981','Retained'],['#EF4444','Dropped']].map(([c,l]) => (
                                            <span key={l} className="d-flex align-items-center gap-1" style={{ fontSize:11 }}>
                                                <span style={{ width:10,height:10,borderRadius:2,background:c,display:'inline-block' }} />
                                                <span style={{ fontWeight:600,color:BDY }}>{l}</span>
                                            </span>
                                        ))}
                                    </div>
                                    <div className="mt-3">
                                        {dropData.map((d,i) => (
                                            <div key={i} className="d-flex align-items-center justify-content-between py-2" style={{ borderTop:i>0?`1px solid ${BOR}`:'none',fontSize:12 }}>
                                                <span style={{ fontWeight:600,color:BDY }}>{d.stage}</span>
                                                <div className="d-flex align-items-center gap-3">
                                                    <span style={{ color:'#10B981',fontWeight:700 }}>{d.Retained} kept</span>
                                                    <span style={{ color:'#EF4444',fontWeight:700 }}>{d.dropPct}% drop</span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </>
                            )}
                        </ChartCard>
                    </div>
                </div>

                {/* ── Telecaller Conversion Table ── */}
                <div style={{ ...CARD,padding:'20px 22px',marginBottom:20 }}>
                    <div className="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <div style={{ display:'flex',alignItems:'center',gap:8 }}>
                                <div style={{ width:3,height:18,background:OR,borderRadius:2 }} />
                                <h6 style={{ fontWeight:700,fontSize:16,margin:0,color:DK }}>Conversion by Telecaller</h6>
                            </div>
                            <span style={{ fontSize:12,color:MUT,marginLeft:11 }}>Ranked by conversion rate · {tele.length} telecaller{tele.length!==1?'s':''}</span>
                        </div>
                        <div className="d-flex gap-2">
                            <a href={exportUrl('excel')} style={{ height:36,padding:'0 14px',background:'#F0FDF4',color:'#16A34A',border:'1.5px solid #BBF7D0',borderRadius:8,fontWeight:700,fontSize:12,display:'inline-flex',alignItems:'center',gap:5,textDecoration:'none' }}><LuFileSpreadsheet size={15} /> Excel</a>
                            <a href={exportUrl('pdf')} target="_blank" rel="noreferrer" style={{ height:36,padding:'0 14px',background:'#FEF2F2',color:'#DC2626',border:'1.5px solid #FECACA',borderRadius:8,fontWeight:700,fontSize:12,display:'inline-flex',alignItems:'center',gap:5,textDecoration:'none' }}><LuFileText size={15} /> PDF</a>
                        </div>
                    </div>

                    <div style={{ border:`1.5px solid ${BOR}`,borderRadius:12 }}>
                        <div style={{ overflowX:'auto',borderRadius:12 }}>
                            <table className="rpt-tbl" style={{ width:'100%',borderCollapse:'collapse',minWidth:780 }}>
                                <thead>
                                    <tr>{['#','Telecaller','Assigned','Attended','Interested','Converted','Attend%','Interest%','Conv. Rate'].map((h,i)=><th key={i} style={{ ...thBase,textAlign:i<=1?'left':'right' }}>{h}</th>)}</tr>
                                </thead>
                                <tbody>
                                    {tele.length===0 ? (
                                        <tr><td colSpan={9} style={{ textAlign:'center',padding:'40px 0',color:MUT }}><LuUsers size={40} color={BOR} style={{ display:'block',margin:'0 auto 8px' }} />No telecaller data</td></tr>
                                    ) : tele.map((r,i) => {
                                        const attendPct   = r.total>0?Math.round(((r.attended??0)/r.total)*100):0;
                                        const interestPct = r.total>0?Math.round(((r.interested??0)/r.total)*100):0;
                                        const isTop = i===0;
                                        const bg    = isTop?'#FFF8F5':i%2===0?WH:'#FAFAFA';
                                        return (
                                            <tr key={i} style={{ background:bg,borderBottom:`1px solid ${BOR}` }}>
                                                <td style={{ padding:'13px 14px',textAlign:'center' }}>
                                                    {i<3?<span style={{ fontSize:18,color:RANK_MEDAL[i] }}>★</span>:<span style={{ fontSize:12,color:MUT,fontWeight:700 }}>{i+1}</span>}
                                                </td>
                                                <td style={{ padding:'13px 14px' }}>
                                                    <div className="d-flex align-items-center gap-2">
                                                        <Avatar name={r.name} />
                                                        <div>
                                                            <div style={{ fontSize:13,fontWeight:700,color:DK }}>{r.name}</div>
                                                            {isTop&&<span style={{ fontSize:10,padding:'1px 7px',borderRadius:20,background:`${OR}18`,color:OR,fontWeight:700 }}>Top Converter</span>}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right',fontSize:13,fontWeight:700 }}>{r.total}</td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#FFF5EF',color:OR,fontWeight:700,fontSize:12 }}>{r.attended??0}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 10px',borderRadius:20,background:'#FFFBEB',color:'#B45309',fontWeight:700,fontSize:12 }}>{r.interested??0}</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}>
                                                    <span style={{ display:'inline-block',padding:'3px 11px',borderRadius:20,fontWeight:800,fontSize:12,background:r.converted>0?'#ECFDF5':'#F8FAFC',color:r.converted>0?'#10B981':MUT,border:r.converted>0?'1.5px solid #6EE7B7':`1.5px solid ${BOR}` }}>{r.converted}</span>
                                                </td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ fontSize:12,fontWeight:700,color:attendPct>=70?'#10B981':attendPct>=40?'#F59E0B':'#EF4444' }}>{attendPct}%</span></td>
                                                <td style={{ padding:'13px 14px',textAlign:'right' }}><span style={{ fontSize:12,fontWeight:700,color:interestPct>=30?'#8B5CF6':MUT }}>{interestPct}%</span></td>
                                                <td style={{ padding:'13px 14px',minWidth:160 }}>
                                                    <div style={{ display:'flex',alignItems:'center',gap:8 }}>
                                                        <div style={{ flex:1,height:7,background:BOR,borderRadius:4 }}>
                                                            <div style={{ width:`${Math.min(100,r.rate)}%`,height:'100%',background:r.rate>=10?'#10B981':r.rate>=5?'#F59E0B':'#EF4444',borderRadius:4 }} />
                                                        </div>
                                                        <span style={{ fontSize:12,fontWeight:800,color:r.rate>=10?'#10B981':r.rate>=5?'#F59E0B':'#EF4444',minWidth:38 }}>{r.rate}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {/* ── Source + Status tables ── */}
                <div className="row g-4 mb-4">
                    {sources.length > 0 && (
                        <div className="col-md-6">
                            <div style={{ ...CARD,padding:'20px 22px',height:'100%' }}>
                                <div style={{ display:'flex',alignItems:'center',gap:8,marginBottom:14 }}>
                                    <div style={{ width:3,height:18,background:OR,borderRadius:2 }} />
                                    <h6 style={{ fontWeight:700,fontSize:15,margin:0,color:DK }}>Conversion by Source</h6>
                                </div>
                                <div style={{ border:`1.5px solid ${BOR}`,borderRadius:12 }}>
                                    <div style={{ overflowX:'auto',borderRadius:12 }}>
                                        <table className="rpt-tbl" style={{ width:'100%',borderCollapse:'collapse' }}>
                                            <thead><tr>{['Source','Leads','Converted','Rate'].map((h,i)=><th key={i} style={{ ...thBase,textAlign:i===0?'left':'right' }}>{h}</th>)}</tr></thead>
                                            <tbody>
                                                {sources.map((r,i) => {
                                                    const c = COLORS[i%COLORS.length]; const rate=parseFloat(r.rate)||0;
                                                    const bg=i%2===0?WH:'#FAFAFA';
                                                    return (
                                                        <tr key={i} style={{ background:bg,borderBottom:`1px solid ${BOR}` }}>
                                                            <td style={{ padding:'12px 14px' }}>
                                                                <div className="d-flex align-items-center gap-2">
                                                                    <span style={{ width:10,height:10,borderRadius:'50%',background:c,flexShrink:0 }} />
                                                                    <span style={{ fontSize:13,fontWeight:600,color:DK }}>{capFirst(r.source||'Unknown')}</span>
                                                                </div>
                                                            </td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right',fontSize:13,fontWeight:700 }}>{r.total}</td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'2px 10px',borderRadius:20,background:r.converted>0?'#ECFDF5':'#F8FAFC',color:r.converted>0?'#10B981':MUT,fontWeight:800,fontSize:12,border:r.converted>0?'1.5px solid #6EE7B7':`1.5px solid ${BOR}` }}>{r.converted}</span></td>
                                                            <td style={{ padding:'12px 14px',minWidth:130 }}>
                                                                <div style={{ display:'flex',alignItems:'center',gap:7 }}>
                                                                    <div style={{ flex:1,height:6,background:BOR,borderRadius:3 }}><div style={{ width:`${Math.min(100,rate)}%`,height:'100%',background:rate>=10?'#10B981':rate>=5?'#F59E0B':'#EF4444',borderRadius:3 }} /></div>
                                                                    <span style={{ fontSize:12,fontWeight:700,minWidth:38,color:rate>=10?'#10B981':rate>=5?'#D97706':'#EF4444' }}>{rate}%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {statuses.length > 0 && (
                        <div className={sources.length>0?'col-md-6':'col-12'}>
                            <div style={{ ...CARD,padding:'20px 22px',height:'100%' }}>
                                <div style={{ display:'flex',alignItems:'center',gap:8,marginBottom:14 }}>
                                    <div style={{ width:3,height:18,background:OR,borderRadius:2 }} />
                                    <h6 style={{ fontWeight:700,fontSize:15,margin:0,color:DK }}>Lead Status Breakdown</h6>
                                </div>
                                <div style={{ border:`1.5px solid ${BOR}`,borderRadius:12 }}>
                                    <div style={{ overflowX:'auto',borderRadius:12 }}>
                                        <table className="rpt-tbl" style={{ width:'100%',borderCollapse:'collapse' }}>
                                            <thead><tr>{['Status','Count','Share of Total'].map((h,i)=><th key={i} style={{ ...thBase,textAlign:i===1?'right':'left' }}>{h}</th>)}</tr></thead>
                                            <tbody>
                                                {statuses.map((r,i) => {
                                                    const pct=totalLeads>0?Math.round((r.total/totalLeads)*100):0;
                                                    const color=STATUS_COLORS[r.status]??MUT;
                                                    const bg=i%2===0?WH:'#FAFAFA';
                                                    return (
                                                        <tr key={i} style={{ background:bg,borderBottom:`1px solid ${BOR}` }}>
                                                            <td style={{ padding:'12px 14px' }}>
                                                                <div className="d-flex align-items-center gap-2">
                                                                    <span style={{ width:10,height:10,borderRadius:'50%',background:color,flexShrink:0 }} />
                                                                    <span style={{ fontSize:13,fontWeight:600,color:DK }}>{r.status?.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}</span>
                                                                </div>
                                                            </td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right',fontSize:14,fontWeight:800,color }}>{r.total}</td>
                                                            <td style={{ padding:'12px 14px',minWidth:160 }}>
                                                                <div style={{ display:'flex',alignItems:'center',gap:8 }}>
                                                                    <div style={{ flex:1,height:7,background:BOR,borderRadius:4 }}><div style={{ width:`${pct}%`,height:'100%',background:color,borderRadius:4 }} /></div>
                                                                    <span style={{ fontSize:12,fontWeight:700,color,minWidth:34,textAlign:'right' }}>{pct}%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* ── Course Tables ── */}
                <div className="row g-4 mb-4">
                    {eq.length > 0 && (
                        <div className="col-lg-6">
                            <div style={{ ...CARD,padding:'20px 22px',height:'100%' }}>
                                <div style={{ display:'flex',alignItems:'center',gap:8,marginBottom:14 }}>
                                    <div style={{ width:3,height:18,background:OR,borderRadius:2 }} />
                                    <h6 style={{ fontWeight:700,fontSize:15,margin:0,color:DK }}>Leads by Enquired Course</h6>
                                </div>
                                <div style={{ border:`1.5px solid ${BOR}`,borderRadius:12 }}>
                                    <div style={{ overflowX:'auto',borderRadius:12 }}>
                                        <table className="rpt-tbl" style={{ width:'100%',borderCollapse:'collapse' }}>
                                            <thead><tr>{['Course','Total','Converted','Conv. Rate'].map((h,i)=><th key={i} style={{ ...thBase,textAlign:i===0?'left':'right' }}>{h}</th>)}</tr></thead>
                                            <tbody>
                                                {eq.map((r,i) => {
                                                    const rate=parseFloat(r.rate)||0; const c=COLORS[i%COLORS.length]; const bg=i%2===0?WH:'#FAFAFA';
                                                    return (
                                                        <tr key={i} style={{ background:bg,borderBottom:`1px solid ${BOR}` }}>
                                                            <td style={{ padding:'12px 14px' }}>
                                                                <div className="d-flex align-items-center gap-2">
                                                                    <span style={{ width:8,height:8,borderRadius:2,background:c,flexShrink:0 }} />
                                                                    <span style={{ fontSize:12,fontWeight:600,color:DK }} title={r.course_name}>{r.course_name}</span>
                                                                </div>
                                                            </td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right',fontSize:13,fontWeight:700 }}>{r.total}</td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'2px 9px',borderRadius:20,background:r.converted>0?'#ECFDF5':'#F8FAFC',color:r.converted>0?'#10B981':MUT,fontWeight:800,fontSize:12 }}>{r.converted}</span></td>
                                                            <td style={{ padding:'12px 14px',minWidth:120 }}>
                                                                <div style={{ display:'flex',alignItems:'center',gap:7 }}>
                                                                    <div style={{ flex:1,height:6,background:BOR,borderRadius:3 }}><div style={{ width:`${Math.min(100,rate)}%`,height:'100%',background:rate>=20?'#10B981':rate>=10?'#F59E0B':'#EF4444',borderRadius:3 }} /></div>
                                                                    <span style={{ fontSize:12,fontWeight:700,minWidth:38,color:rate>=20?'#10B981':rate>=10?'#D97706':'#EF4444' }}>{rate}%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {fc.length > 0 && (
                        <div className="col-lg-6">
                            <div style={{ ...CARD,padding:'20px 22px',height:'100%' }}>
                                <div style={{ display:'flex',alignItems:'center',gap:8,marginBottom:14 }}>
                                    <div style={{ width:3,height:18,background:OR,borderRadius:2 }} />
                                    <h6 style={{ fontWeight:700,fontSize:15,margin:0,color:DK }}>Enrollments by Final Course</h6>
                                </div>
                                <div style={{ border:`1.5px solid ${BOR}`,borderRadius:12 }}>
                                    <div style={{ overflowX:'auto',borderRadius:12 }}>
                                        <table className="rpt-tbl" style={{ width:'100%',borderCollapse:'collapse' }}>
                                            <thead><tr>{['Course','Enrolled','Management','Counselling','Split'].map((h,i)=><th key={i} style={{ ...thBase,textAlign:i===0?'left':'right' }}>{h}</th>)}</tr></thead>
                                            <tbody>
                                                {fc.map((r,i) => {
                                                    const mgmt=r.management_count??0; const couns=r.counselling_count??0;
                                                    const total=mgmt+couns||r.total||1; const mgmtPct=Math.round((mgmt/total)*100);
                                                    const bg=i%2===0?WH:'#FAFAFA';
                                                    return (
                                                        <tr key={i} style={{ background:bg,borderBottom:`1px solid ${BOR}` }}>
                                                            <td style={{ padding:'12px 14px' }}>
                                                                <div className="d-flex align-items-center gap-2">
                                                                    <span style={{ width:8,height:8,borderRadius:2,background:'#10B981',flexShrink:0 }} />
                                                                    <span style={{ fontSize:12,fontWeight:600,color:DK }} title={r.course_name}>{r.course_name}</span>
                                                                </div>
                                                            </td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right' }}><span style={{ display:'inline-block',padding:'3px 11px',borderRadius:20,background:'#ECFDF5',color:'#10B981',fontWeight:800,fontSize:12,border:'1.5px solid #6EE7B7' }}>{r.total}</span></td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right',fontSize:13,fontWeight:700,color:OR }}>{mgmt}</td>
                                                            <td style={{ padding:'12px 14px',textAlign:'right',fontSize:13,fontWeight:700,color:'#8B5CF6' }}>{couns}</td>
                                                            <td style={{ padding:'12px 14px',minWidth:100 }}>
                                                                <div style={{ height:8,background:`${OR}18`,borderRadius:4,position:'relative' }}>
                                                                    <div style={{ width:`${mgmtPct}%`,height:'100%',background:OR,borderRadius:4 }} />
                                                                </div>
                                                                <div style={{ fontSize:10,color:MUT,marginTop:2 }}>
                                                                    <span style={{ color:OR,fontWeight:700 }}>{mgmtPct}%</span> Mgmt · <span style={{ color:'#8B5CF6',fontWeight:700 }}>{100-mgmtPct}%</span> Coun.
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
