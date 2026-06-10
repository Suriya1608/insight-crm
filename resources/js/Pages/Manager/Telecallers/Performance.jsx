import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuPhone, LuClock, LuTrendingUp, LuCalendar, LuZap, LuPhoneMissed,
    LuStar, LuUsers, LuChartBar, LuSearch, LuChevronLeft, LuArrowUpRight,
    LuArrowDownLeft, LuMessageSquare,
} from 'react-icons/lu';

// ─── Design tokens ────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

// ─── Helpers ──────────────────────────────────────────────────────────────────
const OUTCOME_META = {
    interested:      { label: 'Interested',      color: '#10b981' },
    not_interested:  { label: 'Not Interested',  color: '#ef4444' },
    call_back_later: { label: 'Call Back Later', color: '#f59e0b' },
    switched_off:    { label: 'Switched Off',    color: '#64748b' },
    wrong_number:    { label: 'Wrong Number',    color: '#8b5cf6' },
};

const STATUS_META = {
    new:           { label: 'New',           color: '#64748b' },
    assigned:      { label: 'Assigned',      color: OR },
    contacted:     { label: 'Contacted',     color: '#0ea5e9' },
    interested:    { label: 'Interested',    color: '#10b981' },
    follow_up:     { label: 'Follow-up',     color: '#f59e0b' },
    converted:     { label: 'Converted',     color: '#8b5cf6' },
    not_interested:{ label: 'Not Interested',color: '#ef4444' },
};

function scoreGrade(score) {
    if (score >= 80) return { grade: 'A', label: 'Excellent', color: '#10b981' };
    if (score >= 60) return { grade: 'B', label: 'Good',      color: OR };
    if (score >= 40) return { grade: 'C', label: 'Average',   color: '#f59e0b' };
    return                  { grade: 'D', label: 'Needs Work', color: '#ef4444' };
}

function fmtSecs(s) {
    s = Math.max(0, parseInt(s) || 0);
    return [Math.floor(s/3600), Math.floor((s%3600)/60), s%60]
        .map(v => String(v).padStart(2,'0')).join(':');
}
function gmdate(secs) {
    secs = Math.max(0, secs);
    return `${String(Math.floor(secs/60)).padStart(2,'0')}:${String(secs%60).padStart(2,'0')}`;
}

// ─── Section title ────────────────────────────────────────────────────────────
function SectionTitle({ Icon, title, right }) {
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', marginBottom:14 }}>
            <div style={{ display:'flex', alignItems:'center', gap:8 }}>
                <div style={{ width:3, height:24, background:OR, borderRadius:2, flexShrink:0 }} />
                {Icon && <Icon size={18} style={{ color: OR }} />}
                <span style={{ fontWeight:700, fontSize:14, color:DK }}>{title}</span>
            </div>
            {right && <span style={{ fontSize:12, color:MUT }}>{right}</span>}
        </div>
    );
}

// ─── KPI card ─────────────────────────────────────────────────────────────────
function KpiCard({ Icon, iconColor, label, value, sub, badge, badgeColor }) {
    return (
        <div className="col-6 col-lg-3">
            <div style={{
                background:WH, borderRadius:14, padding:'20px 18px',
                border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)',
                height:'100%', position:'relative', overflow:'hidden',
            }}>
                <div style={{
                    width:44, height:44, borderRadius:12, background:iconColor+'18',
                    display:'flex', alignItems:'center', justifyContent:'center', marginBottom:12,
                }}>
                    <Icon size={22} style={{ color: iconColor }} />
                </div>
                <div style={{ fontSize:11, fontWeight:600, color:MUT, textTransform:'uppercase', letterSpacing:.6, marginBottom:4 }}>
                    {label}
                </div>
                <div style={{ fontSize:26, fontWeight:800, color:DK, lineHeight:1.1 }}>{value}</div>
                {sub && <div style={{ fontSize:12, color:MUT, marginTop:4 }}>{sub}</div>}
                {badge && (
                    <span style={{
                        position:'absolute', top:14, right:14,
                        background:(badgeColor||OR)+'18', color:badgeColor||OR,
                        fontSize:11, fontWeight:700, padding:'2px 8px', borderRadius:20,
                    }}>{badge}</span>
                )}
            </div>
        </div>
    );
}

// ─── WhatsApp card ────────────────────────────────────────────────────────────
function WhatsAppActivity({ sent, received, total }) {
    const sentPct     = total > 0 ? Math.round((sent     / total) * 100) : 0;
    const receivedPct = total > 0 ? Math.round((received / total) * 100) : 0;
    return (
        <div style={{ background:WH, borderRadius:14, padding:'20px 22px', border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)', marginBottom:24 }}>
            <SectionTitle Icon={LuMessageSquare} title="WhatsApp Activity" right={`${total} messages`} />
            {total === 0 ? (
                <div style={{ textAlign:'center', padding:'20px 0', color:MUT, fontSize:13 }}>
                    <LuMessageSquare size={36} style={{ display:'block', margin:'0 auto 8px', opacity:0.25 }} />
                    No WhatsApp activity for this period
                </div>
            ) : (
                <>
                    <div style={{ display:'flex', borderRadius:99, overflow:'hidden', height:10, marginBottom:18 }}>
                        <div style={{ width:sentPct+'%', background:'#25d366', transition:'width .6s ease' }} />
                        <div style={{ width:receivedPct+'%', background:'#128c7e', transition:'width .6s ease' }} />
                    </div>
                    <div style={{ display:'flex', gap:16 }}>
                        <div style={{ flex:1, background:'#25d36608', borderRadius:12, padding:'14px 16px', borderLeft:'3px solid #25d366' }}>
                            <div style={{ display:'flex', alignItems:'center', gap:6, marginBottom:8 }}>
                                <span style={{ fontSize:12, fontWeight:700, color:'#25d366', textTransform:'uppercase', letterSpacing:.5 }}>Sent</span>
                                <span style={{ marginLeft:'auto', fontSize:11, fontWeight:700, background:'#25d36618', color:'#25d366', padding:'1px 8px', borderRadius:20 }}>{sentPct}%</span>
                            </div>
                            <div style={{ fontSize:28, fontWeight:800, color:DK, lineHeight:1 }}>{sent}</div>
                        </div>
                        <div style={{ flex:1, background:'#128c7e08', borderRadius:12, padding:'14px 16px', borderLeft:'3px solid #128c7e' }}>
                            <div style={{ display:'flex', alignItems:'center', gap:6, marginBottom:8 }}>
                                <span style={{ fontSize:12, fontWeight:700, color:'#128c7e', textTransform:'uppercase', letterSpacing:.5 }}>Received</span>
                                <span style={{ marginLeft:'auto', fontSize:11, fontWeight:700, background:'#128c7e18', color:'#128c7e', padding:'1px 8px', borderRadius:20 }}>{receivedPct}%</span>
                            </div>
                            <div style={{ fontSize:28, fontWeight:800, color:DK, lineHeight:1 }}>{received}</div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}

// ─── Direction split ──────────────────────────────────────────────────────────
function DirectionSplit({ inbound, outbound, inboundSecs, outboundSecs }) {
    const total  = inbound + outbound;
    const inPct  = total > 0 ? Math.round((inbound  / total) * 100) : 0;
    const outPct = total > 0 ? Math.round((outbound / total) * 100) : 0;
    return (
        <div style={{ background:WH, borderRadius:14, padding:'20px 22px', border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)', marginBottom:24 }}>
            <SectionTitle Icon={LuPhone} title="Inbound vs Outbound" right={`${total} total calls`} />
            {total === 0 ? (
                <div style={{ textAlign:'center', padding:'20px 0', color:MUT, fontSize:13 }}>No call data for this period</div>
            ) : (
                <>
                    <div style={{ display:'flex', borderRadius:99, overflow:'hidden', height:10, marginBottom:18 }}>
                        <div style={{ width:outPct+'%', background:OR, transition:'width .6s ease' }} />
                        <div style={{ width:inPct +'%', background:'#10b981', transition:'width .6s ease' }} />
                    </div>
                    <div style={{ display:'flex', gap:16 }}>
                        <div style={{ flex:1, background:OR+'08', borderRadius:12, padding:'14px 16px', borderLeft:`3px solid ${OR}` }}>
                            <div style={{ display:'flex', alignItems:'center', gap:6, marginBottom:8 }}>
                                <LuArrowUpRight size={16} style={{ color: OR }} />
                                <span style={{ fontSize:12, fontWeight:700, color:OR, textTransform:'uppercase', letterSpacing:.5 }}>Outbound</span>
                                <span style={{ marginLeft:'auto', fontSize:11, fontWeight:700, background:OR+'18', color:OR, padding:'1px 8px', borderRadius:20 }}>{outPct}%</span>
                            </div>
                            <div style={{ fontSize:28, fontWeight:800, color:DK, lineHeight:1 }}>{outbound}</div>
                            <div style={{ fontSize:11, color:MUT, marginTop:4 }}>calls · {fmtSecs(outboundSecs)} talk time</div>
                        </div>
                        <div style={{ flex:1, background:'#10b98108', borderRadius:12, padding:'14px 16px', borderLeft:'3px solid #10b981' }}>
                            <div style={{ display:'flex', alignItems:'center', gap:6, marginBottom:8 }}>
                                <LuArrowDownLeft size={16} style={{ color: '#10b981' }} />
                                <span style={{ fontSize:12, fontWeight:700, color:'#10b981', textTransform:'uppercase', letterSpacing:.5 }}>Inbound</span>
                                <span style={{ marginLeft:'auto', fontSize:11, fontWeight:700, background:'#10b98118', color:'#10b981', padding:'1px 8px', borderRadius:20 }}>{inPct}%</span>
                            </div>
                            <div style={{ fontSize:28, fontWeight:800, color:DK, lineHeight:1 }}>{inbound}</div>
                            <div style={{ fontSize:11, color:MUT, marginTop:4 }}>calls · {fmtSecs(inboundSecs)} talk time</div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}

// ─── Calls-per-day SVG line chart ─────────────────────────────────────────────
function DailyLineChart({ dailyBreakdown }) {
    if (!dailyBreakdown || dailyBreakdown.length === 0) {
        return <div style={{ textAlign:'center', padding:'20px 0', color:MUT, fontSize:13 }}>No call activity for this period</div>;
    }
    const W=560, H=120, pad={top:18,right:16,bottom:28,left:34};
    const iW=W-pad.left-pad.right, iH=H-pad.top-pad.bottom;
    const n=dailyBreakdown.length, maxCalls=Math.max(...dailyBreakdown.map(d=>d.calls),1);
    const xPos=i=>pad.left+(n<=1?iW/2:(i/(n-1))*iW);
    const yPos=v=>pad.top+iH-(v/maxCalls)*iH;
    const pts=dailyBreakdown.map((d,i)=>({x:xPos(i),y:yPos(d.calls),calls:d.calls,day:d.day}));
    const linePath=pts.map((p,i)=>`${i===0?'M':'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
    const areaPath=`${linePath} L${pts[pts.length-1].x.toFixed(1)},${(pad.top+iH).toFixed(1)} L${pts[0].x.toFixed(1)},${(pad.top+iH).toFixed(1)} Z`;
    const stride=n>20?5:n>10?3:1;
    const yTicks=[0,Math.round(maxCalls/2),maxCalls];
    const shortLabel=day=>{const p=day.split(' ');return `${p[0]} ${p[1]}`;};
    return (
        <div style={{ overflowX:'auto' }}>
            <svg viewBox={`0 0 ${W} ${H}`} style={{ width:'100%', minWidth:300, display:'block' }}>
                <defs>
                    <linearGradient id="mgrPerfGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%"   stopColor={OR} stopOpacity="0.18" />
                        <stop offset="100%" stopColor={OR} stopOpacity="0"    />
                    </linearGradient>
                </defs>
                {yTicks.map(v=>(
                    <g key={v}>
                        <line x1={pad.left} y1={yPos(v).toFixed(1)} x2={pad.left+iW} y2={yPos(v).toFixed(1)} stroke={BOR} strokeWidth="1" />
                        <text x={pad.left-5} y={yPos(v)+4} textAnchor="end" fontSize="9" fill={MUT}>{v}</text>
                    </g>
                ))}
                <path d={areaPath} fill="url(#mgrPerfGrad)" />
                <path d={linePath} fill="none" stroke={OR} strokeWidth="2" strokeLinejoin="round" strokeLinecap="round" />
                {pts.map((p,i)=>(
                    <g key={i}>
                        <title>{p.calls} calls · {p.day}</title>
                        <circle cx={p.x.toFixed(1)} cy={p.y.toFixed(1)} r={p.calls>0?3.5:2}
                            fill={p.calls>0?OR:BOR} stroke="#fff" strokeWidth="1.5" />
                        {(i%stride===0||i===n-1) && (
                            <text x={p.x.toFixed(1)} y={H-5} textAnchor="middle" fontSize="9" fill={MUT}>{shortLabel(p.day)}</text>
                        )}
                    </g>
                ))}
            </svg>
        </div>
    );
}

// ─── Hourly heatmap ───────────────────────────────────────────────────────────
function HourlyChart({ hourlyBreakdown }) {
    const maxCalls  = Math.max(...hourlyBreakdown.map(h=>h.calls),1);
    const workHours = hourlyBreakdown.filter(h=>h.hour>=8&&h.hour<=20);
    return (
        <div style={{ overflowX:'auto' }}>
            <div style={{ display:'flex', alignItems:'flex-end', gap:4, minWidth:360, height:80, paddingBottom:22 }}>
                {workHours.map(h=>{
                    const heightPct=(h.calls/maxCalls)*100;
                    const active=h.calls>0;
                    return (
                        <div key={h.hour} style={{ flex:1, display:'flex', flexDirection:'column', alignItems:'center', gap:3 }}>
                            <div title={`${h.calls} calls at ${h.hour}:00`} style={{
                                width:'100%', borderRadius:'4px 4px 0 0',
                                height:active?Math.max(heightPct*0.55,4)+'px':'4px',
                                background:active?OR:BOR,
                            }} />
                            <span style={{ fontSize:9, color:MUT, whiteSpace:'nowrap' }}>{h.hour}h</span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

// ─── Date range filter ────────────────────────────────────────────────────────
function DateRangeFilter({ telecallerHash, dateFrom, dateTo }) {
    const [from, setFrom] = useState(dateFrom);
    const [to,   setTo]   = useState(dateTo);

    function apply() {
        router.get(`/manager/telecallers/${telecallerHash}/performance`, { from_date: from, to_date: to }, { preserveScroll: false });
    }

    function preset(days) {
        const end   = new Date();
        const start = new Date();
        start.setDate(end.getDate() - (days - 1));
        const fmt = d => d.toISOString().slice(0,10);
        setFrom(fmt(start));
        setTo(fmt(end));
    }

    return (
        <div style={{
            background:WH, border:`1px solid ${BOR}`, borderRadius:12, padding:'14px 16px', marginTop:20,
        }}>
            <div style={{ display:'flex', flexWrap:'wrap', alignItems:'flex-end', gap:10 }}>
                <div>
                    <label style={{ display:'block', fontSize:10, fontWeight:700, color:MUT, textTransform:'uppercase', letterSpacing:.5, marginBottom:4 }}>
                        From Date
                    </label>
                    <input type="date" value={from} onChange={e=>setFrom(e.target.value)}
                        style={{
                            padding:'7px 10px', borderRadius:8, border:`1px solid ${BOR}`,
                            background:WH, color:DK, fontSize:13, outline:'none',
                            fontFamily: 'inherit',
                        }} />
                </div>
                <div>
                    <label style={{ display:'block', fontSize:10, fontWeight:700, color:MUT, textTransform:'uppercase', letterSpacing:.5, marginBottom:4 }}>
                        To Date
                    </label>
                    <input type="date" value={to} onChange={e=>setTo(e.target.value)}
                        style={{
                            padding:'7px 10px', borderRadius:8, border:`1px solid ${BOR}`,
                            background:WH, color:DK, fontSize:13, outline:'none',
                            fontFamily: 'inherit',
                        }} />
                </div>
                <button type="button" onClick={apply}
                    style={{
                        padding:'8px 18px', borderRadius:8, border:'none', cursor:'pointer',
                        background:OR, color:'#fff', fontSize:13, fontWeight:700,
                        display:'flex', alignItems:'center', gap:5, fontFamily: 'inherit',
                    }}>
                    <LuSearch size={15} />
                    Search
                </button>

                {/* Quick presets */}
                <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
                    {[
                        { label:'Today',    days:1  },
                        { label:'7 Days',   days:7  },
                        { label:'30 Days',  days:30 },
                    ].map(p=>(
                        <button key={p.label} type="button" onClick={()=>preset(p.days)}
                            style={{
                                padding:'7px 12px', borderRadius:8, border:`1px solid ${BOR}`,
                                background:WH, color:BDY,
                                fontSize:12, fontWeight:600, cursor:'pointer', fontFamily: 'inherit',
                            }}>
                            {p.label}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Performance({
    telecaller, dateFrom, dateTo, period,
    callsHandled, talkTimeLabel, talkMinutes, avgCallDuration,
    conversionPercent, totalAssigned,
    followupsCompleted, followupsScheduled, followupCompletionRate, pendingFollowups,
    responseTimeLabel,
    missedCalls, missedRate,
    waSent, waReceived, waTotal,
    inboundCount, outboundCount, inboundTalkSecs, outboundTalkSecs,
    outcomeBreakdown, leadStatusRows,
    dailyBreakdown, hourlyBreakdown, bestDay,
    productivityScore,
    callTarget, callTargetPct, uniqueLeadsCalled, totalLeadsEver,
}) {
    const { grade, label: gradeLabel, color: gradeColor } = scoreGrade(productivityScore);
    const totalOutcomeCalls = Object.values(outcomeBreakdown).reduce((a,b)=>a+b,0);
    const totalLeads        = Object.values(leadStatusRows).reduce((a,b)=>a+b,0);
    const isMultiDay        = dailyBreakdown.length > 1;

    return (
        <>
            <Head title={`${telecaller.name} — Performance`} />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .mgr-perf-wrap { font-family: 'Poppins', sans-serif; }
            `}</style>

            <div className="mgr-perf-wrap">
                {/* ── Hero header ──────────────────────────────────────────────── */}
                <div style={{
                    background: WH, border:`1px solid ${BOR}`,
                    borderRadius:16, padding:'24px 28px', marginBottom:24,
                    boxShadow:'0 2px 8px rgba(0,0,0,0.04)', position:'relative', overflow:'hidden',
                }}>
                    {/* Orange accent top bar */}
                    <div style={{ position:'absolute', top:0, left:0, right:0, height:4, background:`linear-gradient(90deg,${OR},#ff8c00)`, borderRadius:'16px 16px 0 0' }} />

                    {/* Back link */}
                    <Link href="/manager/telecallers" style={{
                        display:'inline-flex', alignItems:'center', gap:4, marginBottom:14,
                        color:MUT, textDecoration:'none', fontSize:12, fontWeight:600,
                    }}>
                        <LuChevronLeft size={15} />
                        Back to Telecallers
                    </Link>

                    <div style={{ display:'flex', alignItems:'flex-start', justifyContent:'space-between', flexWrap:'wrap', gap:16 }}>
                        <div>
                            <div style={{ fontSize:11, fontWeight:700, color:MUT, textTransform:'uppercase', letterSpacing:1, marginBottom:6 }}>
                                Telecaller Performance
                            </div>
                            <h1 style={{ fontSize:24, fontWeight:800, margin:0, marginBottom:4, color:DK }}>{telecaller.name}</h1>
                            <p style={{ margin:0, color:MUT, fontSize:13 }}>{period}</p>
                        </div>

                        {/* Score badge */}
                        <div style={{
                            background:'#FFF4EE', border:`1px solid ${OR}30`,
                            borderRadius:16, padding:'16px 22px', textAlign:'center', minWidth:110,
                        }}>
                            <div style={{ fontSize:38, fontWeight:900, lineHeight:1, color:OR }}>{productivityScore}</div>
                            <div style={{ fontSize:11, color:MUT, marginTop:2 }}>/ 100 score</div>
                            <div style={{
                                marginTop:6, background:gradeColor, borderRadius:20,
                                padding:'2px 12px', fontSize:12, fontWeight:700, display:'inline-block', color:'#fff',
                            }}>{grade} · {gradeLabel}</div>
                        </div>
                    </div>

                    {/* Date range filter */}
                    <DateRangeFilter telecallerHash={telecaller.encoded_id} dateFrom={dateFrom} dateTo={dateTo} />
                </div>

                {/* ── KPI cards ────────────────────────────────────────────────── */}
                <div className="row g-3 mb-4">
                    <KpiCard Icon={LuPhone} iconColor={OR} label="Calls Handled" value={callsHandled} sub={`Avg duration: ${avgCallDuration}`} />
                    <KpiCard Icon={LuClock} iconColor="#06b6d4" label="Total Talk Time" value={talkTimeLabel} sub={`${talkMinutes} minutes on calls`} />
                    <KpiCard Icon={LuTrendingUp} iconColor="#10b981" label="Conversion Rate" value={`${conversionPercent}%`}
                        sub={`${totalAssigned} leads assigned`}
                        badge={parseFloat(conversionPercent) > 0 ? `${conversionPercent}%` : null} badgeColor="#10b981" />
                    <KpiCard Icon={LuCalendar} iconColor="#f59e0b" label="Followups Done" value={followupsCompleted}
                        sub={followupsScheduled > 0
                            ? `of ${followupsScheduled} scheduled · ${followupCompletionRate}%${pendingFollowups > 0 ? ` · ${pendingFollowups} overdue` : ''}`
                            : pendingFollowups > 0 ? `${pendingFollowups} overdue` : 'None scheduled'}
                        badge={pendingFollowups > 0 ? `${pendingFollowups} overdue` : null} badgeColor="#ef4444" />
                    <KpiCard Icon={LuZap} iconColor="#8b5cf6" label="Avg Response Time" value={responseTimeLabel} sub="From assignment to first contact" />
                    <KpiCard Icon={LuPhoneMissed} iconColor="#ef4444" label="Missed Calls" value={missedCalls}
                        sub={inboundCount > 0 ? `${missedRate}% of inbound calls` : 'No inbound calls'}
                        badge={missedCalls > 0 ? `${missedRate}%` : null} badgeColor="#ef4444" />
                    {bestDay && isMultiDay && (
                        <KpiCard Icon={LuStar} iconColor="#f59e0b" label="Best Day" value={bestDay.calls + ' calls'} sub={bestDay.day} badge="Peak" badgeColor="#f59e0b" />
                    )}
                    <KpiCard Icon={LuUsers} iconColor="#06b6d4" label="Leads Contacted" value={`${uniqueLeadsCalled} / ${totalLeadsEver}`}
                        sub={`${callTargetPct}% of all assigned leads called`}
                        badge={callTargetPct >= 100 ? 'All' : null} badgeColor="#10b981" />
                </div>

                {/* ── Direction split ───────────────────────────────────────────── */}
                <DirectionSplit inbound={inboundCount} outbound={outboundCount} inboundSecs={inboundTalkSecs} outboundSecs={outboundTalkSecs} />

                {/* ── WhatsApp ──────────────────────────────────────────────────── */}
                <WhatsAppActivity sent={waSent} received={waReceived} total={waTotal} />

                {/* ── Outcomes + Lead pipeline ──────────────────────────────────── */}
                <div className="row g-3 mb-4">
                    <div className="col-md-6">
                        <div style={{ background:WH, borderRadius:14, padding:'20px 22px', border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)', height:'100%' }}>
                            <SectionTitle Icon={LuChartBar} title="Call Outcomes" right={`${totalOutcomeCalls} classified`} />
                            {totalOutcomeCalls === 0 ? (
                                <div style={{ textAlign:'center', padding:'30px 0', color:MUT, fontSize:13 }}>
                                    <LuPhone size={36} style={{ display:'block', margin:'0 auto 8px', opacity:.25 }} />
                                    No outcome data yet
                                </div>
                            ) : (
                                Object.entries(outcomeBreakdown).map(([key, count]) => {
                                    const meta = OUTCOME_META[key] || { label:key, color:MUT };
                                    const pct  = totalOutcomeCalls > 0 ? Math.round((count/totalOutcomeCalls)*100) : 0;
                                    return (
                                        <div key={key} style={{ marginBottom:14 }}>
                                            <div style={{ display:'flex', justifyContent:'space-between', marginBottom:5 }}>
                                                <span style={{ fontSize:13, fontWeight:600, color:BDY }}>{meta.label}</span>
                                                <div style={{ display:'flex', gap:10, alignItems:'center' }}>
                                                    <span style={{ fontSize:13, color:MUT }}>{count} calls</span>
                                                    <span style={{ minWidth:38, textAlign:'center', background:meta.color+'18', color:meta.color, fontSize:11, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>{pct}%</span>
                                                </div>
                                            </div>
                                            <div style={{ background:BOR, borderRadius:99, height:6 }}>
                                                <div style={{ width:pct+'%', height:'100%', borderRadius:99, background:meta.color, transition:'width .6s ease', minWidth:pct>0?6:0 }} />
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>

                    <div className="col-md-6">
                        <div style={{ background:WH, borderRadius:14, padding:'20px 22px', border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)', height:'100%' }}>
                            <SectionTitle Icon={LuUsers} title="Lead Pipeline" right={`${totalLeads} total`} />
                            {totalLeads === 0 ? (
                                <div style={{ textAlign:'center', padding:'30px 0', color:MUT, fontSize:13 }}>
                                    <LuUsers size={36} style={{ display:'block', margin:'0 auto 8px', opacity:.25 }} />
                                    No leads assigned in this period
                                </div>
                            ) : (
                                Object.entries(leadStatusRows).map(([status, count]) => {
                                    const meta = STATUS_META[status] || { label:status, color:MUT };
                                    const pct  = Math.round((count/totalLeads)*100);
                                    return (
                                        <div key={status} style={{ display:'flex', alignItems:'center', gap:10, marginBottom:12 }}>
                                            <div style={{ width:10, height:10, borderRadius:'50%', background:meta.color, flexShrink:0 }} />
                                            <span style={{ flex:1, fontSize:13, color:BDY, fontWeight:500 }}>{meta.label}</span>
                                            <div style={{ width:90, background:BOR, borderRadius:99, height:6 }}>
                                                <div style={{ width:pct+'%', height:'100%', background:meta.color, borderRadius:99, minWidth:pct>0?4:0 }} />
                                            </div>
                                            <span style={{ fontSize:12, color:MUT, minWidth:28, textAlign:'right' }}>{count}</span>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>
                </div>

                {/* ── Call activity chart ───────────────────────────────────────── */}
                <div style={{ background:WH, borderRadius:14, padding:'20px 22px', border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)', marginBottom:24 }}>
                    {isMultiDay ? (
                        <>
                            <SectionTitle Icon={LuTrendingUp} title="Calls per Day" right={`${dailyBreakdown.length} days`} />
                            <DailyLineChart dailyBreakdown={dailyBreakdown} />
                        </>
                    ) : (
                        <>
                            <SectionTitle Icon={LuChartBar} title="Call Activity by Hour" right="8AM – 8PM" />
                            {callsHandled === 0
                                ? <div style={{ textAlign:'center', padding:'20px 0', color:MUT, fontSize:13 }}>No call activity for this period</div>
                                : <HourlyChart hourlyBreakdown={hourlyBreakdown} />
                            }
                        </>
                    )}
                </div>

                {/* ── Daily breakdown table ─────────────────────────────────────── */}
                <div style={{ background:WH, borderRadius:14, overflow:'hidden', border:`1px solid ${BOR}`, boxShadow:'0 2px 8px rgba(0,0,0,0.04)' }}>
                    <div style={{ padding:'16px 22px', borderBottom:`1px solid ${BOR}`, display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                        <div style={{ display:'flex', alignItems:'center', gap:8 }}>
                            <div style={{ width:3, height:24, background:OR, borderRadius:2 }} />
                            <LuCalendar size={18} style={{ color:OR }} />
                            <span style={{ fontWeight:700, fontSize:14, color:DK }}>Call Activity Breakdown</span>
                        </div>
                        <span style={{ fontSize:12, color:MUT }}>{dailyBreakdown.length} day(s)</span>
                    </div>
                    <div style={{ overflowX:'auto' }}>
                        <table style={{ width:'100%', borderCollapse:'collapse' }}>
                            <thead>
                                <tr style={{ background:'#F4F6F8' }}>
                                    {['Date','Calls','Talk Time','Avg/Call'].map(h=>(
                                        <th key={h} style={{ padding:'10px 22px', textAlign:'left', fontSize:11, fontWeight:700, color:MUT, textTransform:'uppercase', letterSpacing:.6, whiteSpace:'nowrap' }}>
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {dailyBreakdown.length === 0 ? (
                                    <tr>
                                        <td colSpan={4} style={{ padding:'36px 22px', textAlign:'center', color:MUT, fontSize:13 }}>
                                            <LuCalendar size={32} style={{ display:'block', margin:'0 auto 8px', opacity:.25 }} />
                                            No activity for this period
                                        </td>
                                    </tr>
                                ) : dailyBreakdown.map((row,i)=>{
                                    const avgSec = row.answered_calls > 0 ? Math.round(row.talk_secs/row.answered_calls) : 0;
                                    return (
                                        <tr key={i} style={{ borderTop:`1px solid ${BOR}`, transition:'background .1s' }}
                                            onMouseEnter={e=>e.currentTarget.style.background='#FFF7ED'}
                                            onMouseLeave={e=>e.currentTarget.style.background='transparent'}>
                                            <td style={{ padding:'13px 22px', fontWeight:600, color:DK, fontSize:13 }}>{row.day}</td>
                                            <td style={{ padding:'13px 22px' }}>
                                                <span style={{ background:OR+'18', color:OR, fontWeight:700, fontSize:13, padding:'3px 10px', borderRadius:20 }}>
                                                    {row.calls}
                                                </span>
                                            </td>
                                            <td style={{ padding:'13px 22px', fontSize:13, color:BDY, fontFamily:'monospace' }}>{row.talk_time}</td>
                                            <td style={{ padding:'13px 22px', fontSize:13, color:MUT, fontFamily:'monospace' }}>{gmdate(avgSec)}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
}
