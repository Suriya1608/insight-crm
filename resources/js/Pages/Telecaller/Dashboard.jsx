import { Head, Link } from '@inertiajs/react';
import { useState, useEffect, useCallback } from 'react';
import { AreaChart, Area, XAxis, YAxis, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, BarChart, Bar } from 'recharts';

import { LuClipboardList, LuUser, LuClock, LuExternalLink, LuChevronLeft, LuChevronRight,
         LuTrendingUp, LuTarget, LuActivity, LuZap, LuBell, LuPhone } from 'react-icons/lu';
import { MdOutlinePhoneInTalk, MdOutlineFileDownload } from 'react-icons/md';
import { IoCallOutline } from 'react-icons/io5';

// ─── Brand tokens ──────────────────────────────────────────────────────────────
const PR  = '#FF5C00';   // orange primary
const PRD = '#e05200';   // orange dark
const DK  = '#0f172a';   // dark slate
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BG  = '#f1f5f9';

// ─── Helpers ──────────────────────────────────────────────────────────────────
function pad(n) { return String(n).padStart(2, '0'); }
function toHM(secs) {
    const s = Number(secs || 0);
    return { h: Math.floor(s / 3600), m: Math.floor((s % 3600) / 60) };
}
const MONTH_NAMES = ['January','February','March','April','May','June',
    'July','August','September','October','November','December'];

// ─── Heatmap constants ────────────────────────────────────────────────────────
const TIME_ROWS = ['18:00','16:00','14:00','12:00','10:00','8:00'];
const DAYS_COL  = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
const CELL_BG = {
    assigned: '#fed7aa',   // orange-200
    waiting:  '#ffedd5',   // orange-100
    holiday:  '#D1D5DB',
    empty:    '#F3F4F6',
};

// ─── Color palettes ───────────────────────────────────────────────────────────
const OUT_COLS  = [PR, '#10b981', '#f59e0b', '#06b6d4', MUT];
const PIPE_COLS = ['#D1D5DB', '#FF8C4A', '#10b981', PR, PRD, '#ffb380'];

const TARGET_METRICS = [
    { key:'total_calls',  label:'Total Calls' },
    { key:'success_rate', label:'Overall Success Rate' },
    { key:'new_leads',    label:'New Leads Generated' },
    { key:'missed_red',   label:'Missed Call Reduction' },
];

// ─── Skeleton shimmer component ───────────────────────────────────────────────
function Sk({ w='100%', h=14, r=6, style={} }) {
    return <div className="tc-sk" style={{ width:w, height:h, borderRadius:r, ...style }}/>;
}

// ─── Sub-components ───────────────────────────────────────────────────────────
function StatusBadge({ status }) {
    const lc = (status || '').toLowerCase();
    const map = {
        success:    { bg:'#DCFCE7', c:'#16A34A', t:'Success' },
        connected:  { bg:'#DCFCE7', c:'#16A34A', t:'Connected' },
        completed:  { bg:'#DCFCE7', c:'#16A34A', t:'Completed' },
        waiting:    { bg:'#EEF2FF', c:PRD,        t:'Waiting' },
        pending:    { bg:'#EEF2FF', c:PRD,        t:'Pending' },
        failed:     { bg:'#FEE2E2', c:'#DC2626', t:'Failed' },
        missed:     { bg:'#FEE2E2', c:'#DC2626', t:'Missed' },
        'no-answer':{ bg:'#FEE2E2', c:'#DC2626', t:'No Answer' },
        busy:       { bg:'#FEF9C3', c:'#CA8A04', t:'Busy' },
    };
    const s = map[lc] ?? { bg:'#F3F4F6', c:'#6B7280', t: status || '—' };
    return <span style={{ padding:'3px 12px', borderRadius:20, fontSize:11, fontWeight:600,
        background:s.bg, color:s.c, whiteSpace:'nowrap' }}>{s.t}</span>;
}

function Card({ children, style = {} }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', padding:'18px 20px', ...style }}>
            {children}
        </div>
    );
}

function SectionTitle({ title, sub }) {
    return (
        <div style={{ marginBottom:12 }}>
            <div style={{ fontSize:14, fontWeight:700, color:DK }}>{title}</div>
            {sub && <div style={{ fontSize:11, color:MUT, marginTop:2 }}>{sub}</div>}
        </div>
    );
}

// KPI Card for top strip
function KpiCard({ icon, label, value, sub, color, pct }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', padding:'14px 16px',
            display:'flex', flexDirection:'column', gap:4 }}>
            <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between' }}>
                <div style={{ width:36, height:36, borderRadius:10, flexShrink:0,
                    background:`${color}18`, display:'flex', alignItems:'center',
                    justifyContent:'center', color, fontSize:18 }}>{icon}</div>
                {pct != null && (
                    <span style={{ fontSize:11, fontWeight:700, color,
                        background:`${color}12`, border:`1px solid ${color}30`,
                        borderRadius:20, padding:'2px 8px' }}>{pct}%</span>
                )}
            </div>
            <div style={{ fontSize:22, fontWeight:800, color:DK, lineHeight:1, marginTop:2 }}>{value}</div>
            <div style={{ fontSize:10, fontWeight:600, color:MUT, textTransform:'uppercase', letterSpacing:'0.5px' }}>{label}</div>
            {sub && <div style={{ fontSize:10, color:MUT, marginTop:1 }}>{sub}</div>}
        </div>
    );
}

// Progress bar card for goals section
function GoalCard({ icon, label, current, target, color, unit='' }) {
    const pct = Math.min(100, Math.round((current / Math.max(1, target)) * 100));
    return (
        <div style={{ background:WH, borderRadius:12, border:`1px solid ${BOR}`,
            boxShadow:'0 1px 4px rgba(0,0,0,0.04)', padding:'14px 16px' }}>
            <div style={{ display:'flex', alignItems:'center', gap:8, marginBottom:10 }}>
                <div style={{ width:28, height:28, borderRadius:8, background:`${color}15`,
                    display:'flex', alignItems:'center', justifyContent:'center', color, fontSize:14 }}>{icon}</div>
                <div style={{ flex:1 }}>
                    <div style={{ fontSize:11.5, fontWeight:600, color:DK }}>{label}</div>
                    <div style={{ fontSize:10, color:MUT }}>{current}{unit} / {target}{unit}</div>
                </div>
                <div style={{ fontSize:13, fontWeight:800, color }}>{pct}%</div>
            </div>
            <div style={{ background:'#F3F4F6', borderRadius:20, height:5, overflow:'hidden' }}>
                <div style={{ height:'100%', borderRadius:20, width:`${pct}%`,
                    background:`linear-gradient(90deg,${color},${color}aa)`,
                    transition:'width .8s ease' }}/>
            </div>
        </div>
    );
}

function StatCard({ icon, label, value, primary }) {
    return (
        <div style={{
            background: primary ? `linear-gradient(135deg,${PR},${PRD})` : WH,
            border:     primary ? 'none' : `1px solid ${BOR}`,
            borderRadius:12, padding:'12px 14px',
            boxShadow:  primary ? `0 4px 16px rgba(99,102,241,0.30)` : '0 2px 8px rgba(0,0,0,0.04)',
            flex:1, minWidth:0, display:'flex', flexDirection:'column', justifyContent:'space-between',
        }}>
            <div style={{ fontSize:16, color: primary ? 'rgba(255,255,255,0.85)' : MUT, marginBottom:4 }}>{icon}</div>
            <div>
                <div style={{ fontSize:9, fontWeight:600, color: primary ? 'rgba(255,255,255,0.75)' : MUT,
                    marginBottom:2, textTransform:'uppercase', letterSpacing:'0.6px' }}>{label}</div>
                <div style={{ fontSize:22, fontWeight:800, color: primary ? '#fff' : DK, lineHeight:1 }}>{value}</div>
            </div>
        </div>
    );
}

// ─── Calendar ─────────────────────────────────────────────────────────────────
function UpcomingCalendar({ initialData }) {
    const today = new Date(), ty = today.getFullYear(), tm = today.getMonth() + 1, td = today.getDate();
    const [state, setState]     = useState({ year:ty, month:tm, days:initialData ?? {} });
    const [loading, setLoading] = useState(false);

    async function navTo(year, month) {
        setLoading(true);
        try {
            const r = await fetch(`/telecaller/followups/calendar-data?year=${year}&month=${month}`,
                { headers:{ Accept:'application/json' } });
            const d = await r.json();
            setState({ year:d.year, month:d.month, days:d.days || {} });
        } catch (_) {}
        setLoading(false);
    }
    function prev() { let { year, month } = state; if (--month < 1) { month = 12; year--; } navTo(year, month); }
    function next() { let { year, month } = state; if (++month > 12) { month = 1; year++; } navTo(year, month); }

    const { year, month, days } = state;
    const dim  = new Date(year, month, 0).getDate();
    const fdow = (new Date(year, month - 1, 1).getDay() + 6) % 7;
    const cells = [];
    for (let i = 0; i < fdow; i++) cells.push(null);
    for (let d = 1; d <= dim; d++) cells.push(d);

    function densityStyle(c) {
        if (!c) return null;
        if (c <= 3) return { bg:'#DCFCE7', color:'#16A34A' };
        if (c <= 7) return { bg:'#FEF9C3', color:'#CA8A04' };
        return { bg:'#EEF2FF', color:PR };
    }

    return (
        <div className="tc-cal-outer">
            {/* Left — info panel */}
            <div className="tc-cal-info">
                <div className="tc-cal-info-tag">Schedule</div>
                <h3 className="tc-cal-heading">Upcoming Calendar</h3>
                <p className="tc-cal-body">Track your scheduled follow-ups and upcoming calls. Highlighted dates show your follow-up load.</p>
                <div className="tc-cal-legend-list">
                    {[
                        { color:PR,        label:'High',     desc:'8+ follow-ups scheduled' },
                        { color:'#F59E0B', label:'Moderate', desc:'4–7 follow-ups scheduled' },
                        { color:'#16A34A', label:'Low',      desc:'1–3 follow-ups scheduled' },
                    ].map(x => (
                        <div key={x.label} className="tc-cal-legend-item">
                            <div className="tc-cal-legend-dot" style={{ background:x.color }}/>
                            <div>
                                <div className="tc-cal-legend-label">{x.label} Call Count</div>
                                <div className="tc-cal-legend-desc">{x.desc}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Right — calendar grid */}
            <div className="tc-cal-grid-wrap" style={{ opacity:loading ? 0.5 : 1 }}>
                <div className="tc-cal-nav-row">
                    <button className="tc-cal-nav-btn" onClick={prev}><LuChevronLeft size={15}/></button>
                    <span className="tc-cal-month-label">{MONTH_NAMES[month - 1]} {year}</span>
                    <button className="tc-cal-nav-btn" onClick={next}><LuChevronRight size={15}/></button>
                </div>
                <div className="tc-cal-grid">
                    {['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d => (
                        <div key={d} className="tc-cal-dow">{d}</div>
                    ))}
                    {cells.map((d, i) => {
                        if (d === null) return <div key={`e${i}`}/>;
                        const key     = `${year}-${pad(month)}-${pad(d)}`;
                        const cnt     = days[key] || 0;
                        const ds      = densityStyle(cnt);
                        const isToday = year === ty && month === tm && d === td;
                        const isPast  = new Date(year, month - 1, d) < new Date(ty, tm - 1, td);
                        let href = '';
                        if (cnt > 0) { href = isToday ? '/telecaller/followups/today' : isPast ? '/telecaller/followups/overdue' : '/telecaller/followups/upcoming'; }
                        const cls   = `tc-cal-day${isToday ? ' tc-cal-today' : ''}${ds ? ' tc-cal-has' : ''}${isPast && !ds ? ' tc-cal-past' : ''}`;
                        const inner = <div className={cls} style={ds ? { background:ds.bg, color:ds.color, borderColor:ds.bg } : {}}>{d}{cnt > 0 && <span className="tc-cal-dot"/>}</div>;
                        return href
                            ? <Link key={key} href={href} style={{ textDecoration:'none' }}>{inner}</Link>
                            : <div key={key}>{inner}</div>;
                    })}
                </div>
            </div>
        </div>
    );
}

// ─── Main Dashboard ───────────────────────────────────────────────────────────
export default function Dashboard({ stats:iStats, missed_callbacks:iCB, followup_calendar, call_outcomes:iOut, call_history:iHistory, heatmap_data:iHM, lead_pipeline:iPipeline, weekly_metrics:iWeekly }) {
    const [stats,         setStats]        = useState(iStats ?? {});
    const [callbacks,     setCB]           = useState(iCB ?? []);
    const [outcomes,      setOut]          = useState(iOut ?? {});
    const [history,       setHistory]      = useState(iHistory ?? []);
    const [heatmap,       setHM]           = useState(iHM ?? {});
    const [pipeline,      setPipeline]     = useState(iPipeline ?? []);
    const [weeklyMetrics, setWeeklyMetrics]= useState(iWeekly ?? []);
    const [activeMet,     setAM]           = useState('total_calls');
    const [histFilter,    setHF]           = useState('Today');
    const [loading,       setLoading]      = useState(true);

    const fetchSnapshot = useCallback(async () => {
        try {
            const r = await fetch('/telecaller/panel/snapshot', { headers:{ Accept:'application/json' } });
            const d = await r.json();
            if (!d?.ok) return;
            setStats({
                assigned:       Number(d.total_assigned_leads    || 0),
                new_leads:      Number(d.new_leads               || 0),
                followups:      Number(d.today_followup_count    || 0),
                overdue:        Number(d.overdue_followup_count  || 0),
                calls:          Number(d.total_calls_today       || 0),
                talk_time_secs: Number(d.talk_time_today_seconds || 0),
                active_calls:   Number(d.active_call_count       || 0),
            });
            if (Array.isArray(d.missed_callbacks))                      setCB(d.missed_callbacks);
            if (d.call_outcomes && typeof d.call_outcomes === 'object') setOut(d.call_outcomes);
            if (Array.isArray(d.call_history))                          setHistory(d.call_history);
            if (d.heatmap_data && typeof d.heatmap_data === 'object')   setHM(d.heatmap_data);
            if (Array.isArray(d.lead_pipeline))                         setPipeline(d.lead_pipeline);
            if (Array.isArray(d.weekly_metrics))                        setWeeklyMetrics(d.weekly_metrics);
        } catch (_) {}
        setLoading(false);
    }, []);

    useEffect(() => {
        fetchSnapshot();
        const t = setInterval(() => { if (!document.hidden) fetchSnapshot(); }, 30000);
        const onVis = () => { if (!document.hidden) fetchSnapshot(); };
        document.addEventListener('visibilitychange', onVis);
        window.addEventListener('ay-changed', fetchSnapshot);
        return () => { clearInterval(t); document.removeEventListener('visibilitychange', onVis); window.removeEventListener('ay-changed', fetchSnapshot); };
    }, [fetchSnapshot]);

    const { h:tkH, m:tkM } = toHM(stats.talk_time_secs ?? 0);
    const tkPct  = Math.min(100, Math.round(((stats.talk_time_secs ?? 0) / (8 * 3600)) * 100));
    const tkNeed = Math.max(0, Math.ceil((8 * 3600 - (stats.talk_time_secs ?? 0)) / 3600));

    // Lead Pipeline — real lead status distribution
    const pipeData  = pipeline.map((p, i) => ({ ...p, color: PIPE_COLS[i % PIPE_COLS.length] }));
    const pipeTotal = pipeData.reduce((s, x) => s + x.value, 0);

    // Advanced KPI calculations
    const outTotal       = Object.values(outcomes ?? {}).reduce((s, v) => s + Number(v), 0);
    const outConnected   = Number((outcomes ?? {}).connected || 0);
    const callSuccessRate= outTotal > 0 ? Math.round((outConnected / outTotal) * 100) : 0;

    const hotLeads = pipeData.filter(p =>
        ['Interested','Follow Up','Contacted'].some(n => p.name.includes(n))
    ).reduce((s, p) => s + p.value, 0);
    const pipelineHealth = pipeTotal > 0 ? Math.round((hotLeads / pipeTotal) * 100) : 0;

    const weeklyCallsTotal = (weeklyMetrics ?? []).reduce((s, d) => s + (d.total_calls ?? 0), 0);
    const weeklySuccessAvg = weeklyMetrics?.length > 0
        ? Math.round((weeklyMetrics ?? []).reduce((s, d) => s + (d.success_rate ?? 0), 0) / weeklyMetrics.length)
        : 0;

    // Call Outcomes — real data
    const outEntries = Object.entries(outcomes ?? {}).filter(([, v]) => v > 0);
    const outData    = outEntries.map(([k, v]) => ({ name:k, value:Number(v) }));
    const outLegend  = outEntries.map(([k], i) => ({ k, color:OUT_COLS[i % OUT_COLS.length] }));

    // Weekly Target chart data for active tab
    const chartData = (weeklyMetrics ?? []).map(d => ({ t: d.t, v: d[activeMet] ?? 0 }));

    // Call history bar chart data (last 7 days)
    const histBarData = (weeklyMetrics ?? []).map(d => ({
        t: d.t, calls: d.total_calls ?? 0, success: d.success_rate ?? 0
    }));

    return (
        <>
            <Head title="Dashboard"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .tc-db *{ font-family:'Poppins',sans-serif!important; box-sizing:border-box; }
                .tc-db{ display:flex; flex-direction:column; gap:16px; }

                @keyframes tc-shimmer{
                    0%  { background-position:-400px 0 }
                    100%{ background-position: 400px 0 }
                }
                .tc-sk{
                    background:linear-gradient(90deg,#F0F2F5 25%,#E4E7EB 50%,#F0F2F5 75%);
                    background-size:800px 100%;
                    animation:tc-shimmer 1.5s infinite linear;
                    display:block;
                }

                /* ── Event Banner ── */
                .tc-event{
                    background:linear-gradient(135deg,${DK} 0%,#1e1b4b 60%,${PRD} 100%);
                    border-radius:14px; padding:14px 22px;
                    display:flex; align-items:center; justify-content:space-between; gap:12px;
                    box-shadow:0 4px 20px rgba(255,92,0,0.2);
                }
                .tc-event-tag{ font-size:10px; font-weight:600; color:#ffb380; text-transform:uppercase;
                    letter-spacing:1px; margin-bottom:4px; }
                .tc-event-title{ font-size:16px; font-weight:700; color:#FEFEFE; line-height:1.3; }
                .tc-event-btn{
                    background:${PR}; color:#fff; border:none; border-radius:8px;
                    padding:8px 18px; font-size:12px; font-weight:600; cursor:pointer;
                    transition:background 0.2s; white-space:nowrap; font-family:'Poppins',sans-serif!important;
                    box-shadow:0 2px 8px rgba(99,102,241,0.35);
                }
                .tc-event-btn:hover{ background:${PRD}; }

                /* ── KPI Strip ── */
                .tc-kpi-strip{
                    display:grid;
                    grid-template-columns:repeat(5,1fr);
                    gap:12px;
                }
                @media(max-width:1100px){ .tc-kpi-strip{ grid-template-columns:repeat(3,1fr); } }
                @media(max-width:640px) { .tc-kpi-strip{ grid-template-columns:repeat(2,1fr); } }

                /* ── Row 1 — compact ── */
                .tc-row1{ display:grid; grid-template-columns:152px 1fr; gap:14px; align-items:stretch; }
                .tc-stats-col{ display:flex; flex-direction:column; gap:8px; }

                /* ── Heatmap ── */
                .tc-hm-header{ display:flex; align-items:center; justify-content:space-between;
                    margin-bottom:12px; flex-wrap:wrap; gap:8px; flex-shrink:0; }
                .tc-hm-right{ display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
                .tc-legend-row{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
                .tc-legend-it{ display:flex; align-items:center; gap:5px; font-size:11px; color:#6B7280; }
                .tc-legend-dot{ width:8px; height:8px; border-radius:50%; flex-shrink:0; }
                .tc-hm-wrap{ display:flex; gap:8px; flex:1; min-height:0; }
                .tc-hm-ylabels{ display:flex; flex-direction:column; justify-content:space-between;
                    padding-right:6px; min-width:44px; padding-bottom:24px; flex-shrink:0; }
                .tc-hm-ylabel{ font-size:10px; color:#9CA3AF; text-align:right; }
                .tc-hm-main{ flex:1; min-width:0; display:flex; flex-direction:column; }
                .tc-hm-rows{ flex:1; min-height:0; display:flex; flex-direction:column; gap:5px; }
                .tc-hm-row{ flex:1; display:grid; grid-template-columns:repeat(7,1fr); gap:5px; }
                .tc-hm-cell{ border-radius:6px; transition:opacity 0.15s,transform 0.15s; }
                .tc-hm-cell:hover{ opacity:0.78; transform:scale(1.07); cursor:default; }
                .tc-hm-xlabels{ display:grid; grid-template-columns:repeat(7,1fr); gap:5px;
                    margin-top:6px; flex-shrink:0; }
                .tc-hm-xlabel{ text-align:center; font-size:11px; color:#9CA3AF; font-weight:500; }
                .tc-dropdown{ border:1px solid #E5E7EB; border-radius:8px; padding:5px 10px;
                    font-family:'Poppins',sans-serif!important; font-size:12px; color:${DK};
                    background:#fff; cursor:pointer; outline:none; }
                .tc-dropdown:focus{ border-color:${PR}; box-shadow:0 0 0 3px rgba(99,102,241,0.1); }

                /* ── Row 2 ── */
                .tc-row2{ display:grid; grid-template-columns:1fr 1.5fr; gap:14px; }

                /* Pipeline */
                .tc-pl-body{ display:flex; gap:16px; align-items:center; }
                .tc-pl-chart{ position:relative; flex-shrink:0; }
                .tc-pl-center{ position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; }
                .tc-pl-center-n{ font-size:20px; font-weight:800; color:${DK}; line-height:1; }
                .tc-pl-center-l{ font-size:10px; color:${MUT}; }
                .tc-pl-legend{ flex:1; display:flex; flex-direction:column; gap:7px; }
                .tc-pl-row{ display:flex; align-items:center; justify-content:space-between; }
                .tc-pl-left{ display:flex; align-items:center; gap:7px; font-size:12px; color:#374151; }
                .tc-pl-dot{ width:9px; height:9px; border-radius:2px; flex-shrink:0; }
                .tc-pl-bar-wrap{ flex:1; height:4px; background:#F3F4F6; border-radius:4px; overflow:hidden; margin:0 10px; }
                .tc-pl-bar{ height:100%; border-radius:4px; transition:width .6s ease; }
                .tc-pl-footer{ margin-top:10px; padding-top:8px; border-top:1px solid #F3F4F6;
                    display:flex; justify-content:flex-end; font-size:12px; color:#6B7280; }

                /* Weekly Target */
                .tc-target{
                    background:linear-gradient(135deg,${DK} 0%,#1e1b4b 100%);
                    border-radius:14px; padding:18px 20px; display:flex; gap:16px;
                }
                .tc-target-left{ display:flex; flex-direction:column; gap:6px; min-width:185px; }
                .tc-target-title{ font-size:14px; font-weight:700; color:#FEFEFE; margin-bottom:2px; }
                .tc-target-sub{ font-size:11px; color:#9CA3AF; margin-bottom:4px; }
                .tc-target-btn{ display:flex; align-items:center; gap:8px; padding:8px 10px;
                    border-radius:8px; border:none; font-family:'Poppins',sans-serif!important;
                    font-size:12px; font-weight:500; cursor:pointer; text-align:left; width:100%;
                    transition:background 0.18s; }
                .tc-target-btn-icon{ width:24px; height:24px; border-radius:6px;
                    background:rgba(255,255,255,0.08); display:flex; align-items:center;
                    justify-content:center; flex-shrink:0; color:#9CA3AF; }
                .tc-target-active{ background:${PR}; color:#fff; }
                .tc-target-active .tc-target-btn-icon{ background:rgba(255,255,255,0.2); color:#fff; }
                .tc-target-inactive{ background:rgba(255,255,255,0.05); color:#9CA3AF; }
                .tc-target-right{ flex:1; min-width:0; }

                /* ── Goals Grid ── */
                .tc-goals-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }

                /* ── Call History ── */
                .tc-history-head{ display:flex; align-items:flex-start; justify-content:space-between;
                    margin-bottom:14px; flex-wrap:wrap; gap:10px; }
                .tc-history-scroll{ max-height:260px; overflow-y:auto; }
                .tc-history-scroll::-webkit-scrollbar{ width:4px; }
                .tc-history-scroll::-webkit-scrollbar-track{ background:transparent; }
                .tc-history-scroll::-webkit-scrollbar-thumb{ background:#E5E7EB; border-radius:4px; }
                .tc-tbl{ width:100%; border-collapse:collapse; }
                .tc-tbl th{ font-size:11px; font-weight:600; color:#9CA3AF; text-align:left;
                    padding:7px 10px; border-bottom:1px solid #F3F4F6;
                    position:sticky; top:0; background:#FEFEFE; z-index:1; }
                .tc-tbl td{ font-size:12px; color:#374151; padding:9px 10px;
                    border-bottom:1px solid #F9FAFB; vertical-align:middle; }
                .tc-tbl tr:last-child td{ border-bottom:none; }
                .tc-tbl tbody tr:hover{ background:#fafbff; }
                .tc-view-link{ display:inline-flex; align-items:center; gap:3px; font-size:11px;
                    color:${PR}; font-weight:600; text-decoration:none; }
                .tc-view-link:hover{ text-decoration:underline; }
                .tc-export-btn{
                    background:${PR}; color:#fff; border:none; border-radius:8px;
                    padding:7px 14px; font-family:'Poppins',sans-serif!important; font-size:12px;
                    font-weight:600; cursor:pointer; display:flex; align-items:center; gap:5px;
                }
                .tc-export-btn:hover{ background:${PRD}; }
                .tc-select{ border:1px solid #E5E7EB; border-radius:8px; padding:6px 10px;
                    font-family:'Poppins',sans-serif!important; font-size:12px; color:${DK};
                    background:#fff; cursor:pointer; outline:none; }
                .tc-select:focus{ border-color:${PR}; box-shadow:0 0 0 2px rgba(99,102,241,0.12); }
                .tc-empty-row td{ color:#9CA3AF; text-align:center; font-size:12px; padding:20px; }

                /* ── Row 3 ── */
                .tc-row3{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }

                /* Talk time */
                .tc-talk-time{ display:flex; align-items:baseline; gap:4px; margin-bottom:6px; }
                .tc-talk-n{ font-size:34px; font-weight:800; color:${DK}; line-height:1; }
                .tc-talk-sep{ font-size:22px; font-weight:700; color:#9CA3AF; }
                .tc-talk-unit{ font-size:11px; color:#9CA3AF; align-self:flex-end; padding-bottom:3px; }
                .tc-talk-bar-wrap{ background:#F3F4F6; border-radius:20px; height:5px; margin:10px 0 3px; overflow:hidden; }
                .tc-talk-bar{ height:100%; border-radius:20px; background:linear-gradient(90deg,${PR},#FF8C4A); transition:width 0.8s; }
                .tc-talk-bar-lbl{ font-size:10px; color:#9CA3AF; text-align:right; }
                .tc-talk-need{ display:inline-block; border:1.5px solid ${PR}; color:${PR};
                    border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600; margin:8px 0 4px; }
                .tc-talk-desc{ font-size:11px; color:#6B7280; line-height:1.6; margin-top:4px; }

                /* Missed callbacks */
                .tc-missed-head{ display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px; }
                .tc-cb-btn{
                    background:${PR}; color:#fff; border:none; border-radius:6px;
                    padding:4px 11px; font-family:'Poppins',sans-serif!important; font-size:11px;
                    font-weight:600; cursor:pointer; white-space:nowrap;
                }
                .tc-cb-btn:hover{ background:${PRD}; }
                .tc-cb-scroll{ height:148px; overflow-y:scroll; }
                .tc-cb-scroll::-webkit-scrollbar{ width:5px; }
                .tc-cb-scroll::-webkit-scrollbar-track{ background:#F3F4F6; border-radius:4px; }
                .tc-cb-scroll::-webkit-scrollbar-thumb{ background:#D1D5DB; border-radius:4px; }
                .tc-cb-scroll::-webkit-scrollbar-thumb:hover{ background:${PR}; }
                .tc-cb-tbl{ width:100%; border-collapse:collapse; }
                .tc-cb-tbl th{ font-size:10px; font-weight:600; color:#9CA3AF; text-align:left;
                    padding:4px 6px; border-bottom:1px solid #F3F4F6;
                    position:sticky; top:0; background:#FEFEFE; z-index:1; }
                .tc-cb-tbl td{ font-size:11px; color:#374151; padding:8px 6px;
                    border-bottom:1px solid #F9FAFB; vertical-align:middle; }
                .tc-cb-tbl tr:last-child td{ border-bottom:none; }

                /* ── Calendar ── */
                .tc-cal-outer{ display:grid; grid-template-columns:280px 1fr; border-radius:14px;
                    overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
                .tc-cal-info{
                    background:linear-gradient(180deg,${DK} 0%,#3a1500 100%);
                    padding:28px 24px; display:flex; flex-direction:column;
                }
                .tc-cal-info-tag{ font-size:10px; font-weight:700; color:#ffb380; text-transform:uppercase;
                    letter-spacing:1px; margin-bottom:10px; }
                .tc-cal-heading{ font-size:20px; font-weight:700; color:#FEFEFE; margin-bottom:12px; line-height:1.3; }
                .tc-cal-body{ font-size:12px; color:#9CA3AF; line-height:1.75; margin-bottom:24px; flex:1; }
                .tc-cal-legend-list{ display:flex; flex-direction:column; gap:14px; }
                .tc-cal-legend-item{ display:flex; gap:10px; align-items:flex-start; }
                .tc-cal-legend-dot{ width:10px; height:10px; border-radius:3px; flex-shrink:0; margin-top:2px; }
                .tc-cal-legend-label{ font-size:12px; font-weight:600; color:#FEFEFE; margin-bottom:2px; }
                .tc-cal-legend-desc{ font-size:11px; color:#6B7280; line-height:1.5; }
                .tc-cal-grid-wrap{ background:#FEFEFE; padding:24px; border:1px solid #F0F0F0; border-left:none; }
                .tc-cal-nav-row{ display:flex; align-items:center; justify-content:space-between;
                    gap:12px; margin-bottom:14px; }
                .tc-cal-nav-btn{ width:30px; height:30px; border:1px solid #E5E7EB; border-radius:7px;
                    background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
                    color:#6B7280; transition:background 0.15s; }
                .tc-cal-nav-btn:hover{ background:#EEF2FF; color:${PR}; }
                .tc-cal-month-label{ font-size:14px; font-weight:700; color:${DK}; }
                .tc-cal-grid{ display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
                .tc-cal-dow{ text-align:center; font-size:10px; font-weight:700; color:#9CA3AF;
                    padding:5px 0; text-transform:uppercase; letter-spacing:0.4px; }
                .tc-cal-day{ text-align:center; font-size:12px; padding:7px 3px; border-radius:8px;
                    border:1px solid transparent; color:#374151; position:relative; cursor:default;
                    transition:background 0.15s; }
                .tc-cal-past{ color:#D1D5DB; }
                .tc-cal-today{ border:2px solid ${PR}!important; font-weight:700; color:${PR}; }
                .tc-cal-has{ font-weight:600; cursor:pointer; }
                .tc-cal-has:hover{ filter:brightness(0.95); }
                .tc-cal-dot{ position:absolute; bottom:2px; left:50%; transform:translateX(-50%);
                    width:4px; height:4px; border-radius:50%; background:currentColor; display:block; }

                /* ── Insight Banner ── */
                .tc-insight{
                    background:linear-gradient(135deg,#fff8f5,#fff3eb);
                    border:1px solid #fed7aa; border-radius:12px; padding:12px 16px;
                    display:flex; align-items:center; gap:12px;
                }

                /* ── Responsive ── */
                @media(max-width:1100px){ .tc-row2{ grid-template-columns:1fr; } .tc-row3{ grid-template-columns:1fr 1fr; } }
                @media(max-width:860px){
                    .tc-row1{ grid-template-columns:1fr; }
                    .tc-stats-col{ flex-direction:row; flex-wrap:wrap; }
                    .tc-stats-col>*{ flex:1; min-width:120px; }
                    .tc-row3{ grid-template-columns:1fr; }
                    .tc-target{ flex-direction:column; }
                    .tc-cal-outer{ grid-template-columns:1fr; }
                    .tc-goals-grid{ grid-template-columns:1fr; }
                }
                @media(max-width:600px){ .tc-row2{ grid-template-columns:1fr; } }
            `}</style>

            <div className="tc-db">

                {/* ── Event Banner ── */}
                {loading
                    ? <div className="tc-sk" style={{ height:56, borderRadius:14 }}/>
                    : <div className="tc-event">
                        <div>
                            <div className="tc-event-tag">
                                {stats.overdue > 0 ? '⚠ Action Required' : stats.followups > 0 ? '📅 Today\'s Schedule' : '✓ All Clear'}
                            </div>
                            <div className="tc-event-title">
                                {stats.overdue > 0
                                    ? `${stats.overdue} overdue follow-up${stats.overdue > 1 ? 's' : ''} need attention`
                                    : stats.followups > 0
                                        ? `${stats.followups} follow-up${stats.followups > 1 ? 's' : ''} scheduled for today — stay on track`
                                        : 'No pending events — great work today!'}
                            </div>
                        </div>
                        <div style={{ display:'flex', gap:8, alignItems:'center', flexShrink:0 }}>
                            {stats.active_calls > 0 && (
                                <span style={{ background:'rgba(16,185,129,0.15)', color:'#10b981',
                                    border:'1px solid rgba(16,185,129,0.30)', borderRadius:20,
                                    padding:'5px 12px', fontSize:11, fontWeight:600,
                                    display:'flex', alignItems:'center', gap:4 }}>
                                    <span style={{ width:6, height:6, borderRadius:'50%', background:'#10b981',
                                        animation:'tc-pulse 1.5s infinite', display:'inline-block' }}/>
                                    Live Call
                                </span>
                            )}
                            <Link href="/telecaller/followups/today">
                                <button className="tc-event-btn">View Now</button>
                            </Link>
                        </div>
                    </div>
                }

                {/* ── Advanced KPI Strip ── */}
                {loading
                    ? <div className="tc-kpi-strip">{[0,1,2,3,4].map(i=><Sk key={i} h={96} r={14}/>)}</div>
                    : <div className="tc-kpi-strip">
                        <KpiCard
                            icon={<MdOutlinePhoneInTalk/>}
                            label="Calls Today"
                            value={stats.calls ?? 0}
                            sub={`Target: 30 calls`}
                            color={PR}
                            pct={Math.min(100, Math.round(((stats.calls ?? 0)/30)*100))}
                        />
                        <KpiCard
                            icon={<LuTrendingUp/>}
                            label="Call Success Rate"
                            value={`${callSuccessRate}%`}
                            sub={`${outConnected} connected of ${outTotal}`}
                            color="#10b981"
                        />
                        <KpiCard
                            icon={<LuTarget/>}
                            label="Pipeline Health"
                            value={`${pipelineHealth}%`}
                            sub={`${hotLeads} engaged leads`}
                            color="#f59e0b"
                        />
                        <KpiCard
                            icon={<LuClipboardList/>}
                            label="Assigned Leads"
                            value={stats.assigned ?? 0}
                            sub={`${stats.new_leads ?? 0} new uncontacted`}
                            color="#06b6d4"
                        />
                        <KpiCard
                            icon={<LuBell/>}
                            label="Overdue Tasks"
                            value={stats.overdue ?? 0}
                            sub={stats.overdue > 0 ? 'Immediate action needed' : 'No overdue items'}
                            color={stats.overdue > 0 ? '#ef4444' : '#10b981'}
                        />
                    </div>
                }

                {/* ── Row 1: Mini Stats + Calls Heatmap ── */}
                {loading
                    ? <div className="tc-row1">
                        <div className="tc-stats-col">{[0,1,2,3].map(i=><Sk key={i} h={80} r={12}/>)}</div>
                        <Sk h={320} r={14}/>
                    </div>
                    : <div className="tc-row1">
                        <div className="tc-stats-col">
                            <StatCard icon={<MdOutlinePhoneInTalk/>} label="Calls Today"    value={stats.calls ?? 0} primary />
                            <StatCard icon={<LuClipboardList/>}      label="Assigned Tasks" value={stats.assigned ?? 0} />
                            <StatCard icon={<LuUser/>}               label="Follow Ups"     value={stats.followups ?? 0} />
                            <StatCard icon={<LuClock/>}              label="Overdue"        value={stats.overdue ?? 0} />
                        </div>
                        <Card style={{ display:'flex', flexDirection:'column' }}>
                            <div className="tc-hm-header">
                                <div>
                                    <div style={{ fontSize:15, fontWeight:700, color:DK }}>Calls Activity Heatmap</div>
                                    <div style={{ fontSize:11, color:MUT, marginTop:1 }}>This week's call distribution by time slot</div>
                                </div>
                                <div className="tc-hm-right">
                                    <div className="tc-legend-row">
                                        <div className="tc-legend-it"><div className="tc-legend-dot" style={{ background:'#fed7aa' }}/> High Activity</div>
                                        <div className="tc-legend-it"><div className="tc-legend-dot" style={{ background:'#ffedd5' }}/> Moderate</div>
                                        <div className="tc-legend-it"><div className="tc-legend-dot" style={{ background:'#D1D5DB' }}/> Holiday</div>
                                    </div>
                                </div>
                            </div>
                            <div className="tc-hm-wrap">
                                <div className="tc-hm-ylabels">
                                    {TIME_ROWS.map(t => <div key={t} className="tc-hm-ylabel">{t}</div>)}
                                </div>
                                <div className="tc-hm-main">
                                    <div className="tc-hm-rows">
                                        {TIME_ROWS.map(row => (
                                            <div key={row} className="tc-hm-row">
                                                {DAYS_COL.map(day => {
                                                    const type = heatmap[`${day}-${row}`] ?? 'empty';
                                                    return <div key={day} className="tc-hm-cell" style={{ background:CELL_BG[type] }}/>;
                                                })}
                                            </div>
                                        ))}
                                    </div>
                                    <div className="tc-hm-xlabels">
                                        {DAYS_COL.map(d => <div key={d} className="tc-hm-xlabel">{d}</div>)}
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>
                }

                {/* ── Row 2: Lead Pipeline + Weekly Target ── */}
                {loading
                    ? <div className="tc-row2"><Sk h={240} r={14}/><Sk h={240} r={14}/></div>
                    : <div className="tc-row2">
                        <Card>
                            <SectionTitle title="Lead Pipeline" sub="Real-time status distribution"/>
                            {pipeData.length > 0 ? (
                                <>
                                    <div className="tc-pl-body">
                                        <div className="tc-pl-chart" style={{ width:120, height:120 }}>
                                            <ResponsiveContainer width={120} height={120}>
                                                <PieChart>
                                                    <Pie data={pipeData} cx={55} cy={55} innerRadius={36} outerRadius={58}
                                                        dataKey="value" startAngle={90} endAngle={-270} strokeWidth={0}>
                                                        {pipeData.map((e, i) => <Cell key={i} fill={e.color}/>)}
                                                    </Pie>
                                                </PieChart>
                                            </ResponsiveContainer>
                                            <div className="tc-pl-center">
                                                <div className="tc-pl-center-n">{pipeTotal}</div>
                                                <div className="tc-pl-center-l">Leads</div>
                                            </div>
                                        </div>
                                        <div className="tc-pl-legend">
                                            {pipeData.map((p, i) => (
                                                <div key={i} className="tc-pl-row">
                                                    <div className="tc-pl-left">
                                                        <div className="tc-pl-dot" style={{ background:p.color }}/>{p.name}
                                                    </div>
                                                    <div className="tc-pl-bar-wrap">
                                                        <div className="tc-pl-bar" style={{
                                                            background:p.color,
                                                            width:`${pipeTotal > 0 ? Math.round((p.value/pipeTotal)*100) : 0}%`
                                                        }}/>
                                                    </div>
                                                    <div style={{ fontSize:12, fontWeight:700, color:DK, minWidth:24, textAlign:'right' }}>{p.value}</div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                    <div className="tc-pl-footer">
                                        Pipeline Health &nbsp;
                                        <strong style={{ color:pipelineHealth >= 40 ? '#10b981' : pipelineHealth >= 20 ? '#f59e0b' : '#ef4444' }}>
                                            {pipelineHealth}%
                                        </strong>
                                    </div>
                                </>
                            ) : (
                                <div style={{ display:'flex', flexDirection:'column', alignItems:'center',
                                    justifyContent:'center', color:MUT, fontSize:12, gap:6, padding:'32px 0' }}>
                                    <LuUser size={28} style={{ opacity:0.35 }}/>
                                    No leads assigned yet
                                </div>
                            )}
                        </Card>

                        <div className="tc-target">
                            <div className="tc-target-left">
                                <div className="tc-target-title">Weekly Performance</div>
                                <div className="tc-target-sub">Day-by-day metrics this week</div>
                                <div style={{ background:'rgba(255,255,255,0.06)', borderRadius:10, padding:'10px 12px', marginBottom:8 }}>
                                    <div style={{ fontSize:9, color:'#9CA3AF', textTransform:'uppercase', letterSpacing:'0.5px', marginBottom:4 }}>Week Summary</div>
                                    <div style={{ display:'flex', gap:14 }}>
                                        <div>
                                            <div style={{ fontSize:18, fontWeight:800, color:'#fff' }}>{weeklyCallsTotal}</div>
                                            <div style={{ fontSize:9, color:'#9CA3AF' }}>Total Calls</div>
                                        </div>
                                        <div style={{ width:1, background:'rgba(255,255,255,0.1)' }}/>
                                        <div>
                                            <div style={{ fontSize:18, fontWeight:800, color:'#FF8C4A' }}>{weeklySuccessAvg}%</div>
                                            <div style={{ fontSize:9, color:'#9CA3AF' }}>Avg Success</div>
                                        </div>
                                    </div>
                                </div>
                                {TARGET_METRICS.map(m => (
                                    <button key={m.key}
                                        className={`tc-target-btn ${activeMet === m.key ? 'tc-target-active' : 'tc-target-inactive'}`}
                                        onClick={() => setAM(m.key)}>
                                        <div className="tc-target-btn-icon"><IoCallOutline size={13}/></div>
                                        {m.label}
                                    </button>
                                ))}
                            </div>
                            <div className="tc-target-right">
                                <ResponsiveContainer width="100%" height={220}>
                                    <AreaChart key={activeMet} data={chartData} margin={{ top:10, right:8, left:-24, bottom:0 }}>
                                        <defs>
                                            <linearGradient id="tg" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%"  stopColor={PR} stopOpacity={0.35}/>
                                                <stop offset="95%" stopColor={PR} stopOpacity={0}/>
                                            </linearGradient>
                                        </defs>
                                        <XAxis dataKey="t" stroke="#444" tick={{ fontSize:10, fill:'#9CA3AF' }}/>
                                        <YAxis stroke="#444" tick={{ fontSize:10, fill:'#9CA3AF' }}/>
                                        <Tooltip contentStyle={{ background:'#1e1b4b', border:'none', borderRadius:8, fontSize:11, color:'#fff' }}/>
                                        <Area type="monotone" dataKey="v" stroke={PR} strokeWidth={2.5}
                                            fill="url(#tg)"
                                            dot={{ r:3, fill:PR, strokeWidth:0 }}
                                            activeDot={{ r:5, fill:'#fff', stroke:PR, strokeWidth:2 }}
                                            isAnimationActive={true}/>
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    </div>
                }

                {/* ── Today's Goals Progress ── */}
                {loading
                    ? <Sk h={120} r={14}/>
                    : <Card style={{ padding:'16px 20px' }}>
                        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', marginBottom:14 }}>
                            <div>
                                <div style={{ fontSize:14, fontWeight:700, color:DK }}>Today's Goals Progress</div>
                                <div style={{ fontSize:11, color:MUT, marginTop:1 }}>Track daily targets in real time</div>
                            </div>
                            <span style={{ fontSize:11, fontWeight:600, color:PR,
                                background:'#fff3eb', border:'1px solid #fed7aa',
                                borderRadius:20, padding:'3px 10px' }}>Live</span>
                        </div>
                        <div className="tc-goals-grid">
                            <GoalCard
                                icon={<LuPhone/>}
                                label="Daily Calls"
                                current={stats.calls ?? 0}
                                target={30}
                                color={PR}
                            />
                            <GoalCard
                                icon={<LuClock/>}
                                label="Talk Time"
                                current={Math.round((stats.talk_time_secs ?? 0) / 60)}
                                target={480}
                                color="#10b981"
                                unit=" min"
                            />
                            <GoalCard
                                icon={<LuActivity/>}
                                label="Follow-ups Today"
                                current={Math.max(0, (stats.followups ?? 0) - (stats.overdue ?? 0))}
                                target={Math.max(1, stats.followups ?? 1)}
                                color="#f59e0b"
                            />
                            <GoalCard
                                icon={<LuZap/>}
                                label="Success Rate"
                                current={callSuccessRate}
                                target={60}
                                color="#06b6d4"
                                unit="%"
                            />
                        </div>
                    </Card>
                }

                {/* ── Call History Table ── */}
                {loading
                    ? <Sk h={180} r={14}/>
                    : <Card>
                        <div className="tc-history-head">
                            <div>
                                <div style={{ fontSize:14, fontWeight:700, color:DK }}>Call History</div>
                                <div style={{ fontSize:11, color:MUT, marginTop:2 }}>Recent call records with outcomes</div>
                            </div>
                            <div style={{ display:'flex', gap:10, alignItems:'center' }}>
                                <select className="tc-select" value={histFilter} onChange={e => setHF(e.target.value)}>
                                    <option>Today</option><option>This Week</option><option>This Month</option>
                                </select>
                                <button className="tc-export-btn">
                                    <MdOutlineFileDownload size={15}/>Export
                                </button>
                            </div>
                        </div>
                        <div className="tc-history-scroll">
                            <table className="tc-tbl">
                                <thead>
                                    <tr>
                                        <th>LEAD NAME</th><th>DATE</th><th>TIME</th>
                                        <th>CODE</th><th>STATUS</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {history.length > 0
                                        ? history.map((row, i) => (
                                            <tr key={row.id}>
                                                <td style={{ fontWeight:600, color:DK }}>{row.lead_name}</td>
                                                <td style={{ color:MUT }}>{row.date}</td>
                                                <td style={{ color:MUT }}>{row.time}</td>
                                                <td><span style={{ fontSize:10.5, fontWeight:700, background:'#F3F4F6',
                                                    color:'#4B5563', border:'1px solid #E5E7EB', padding:'2px 7px',
                                                    borderRadius:5, fontFamily:'monospace' }}>{row.lead_code}</span></td>
                                                <td><StatusBadge status={row.status}/></td>
                                                <td>
                                                    {row.encrypted_lead_id
                                                        ? <Link href={`/telecaller/leads/${row.encrypted_lead_id}`} className="tc-view-link">
                                                            View <LuExternalLink size={11}/>
                                                          </Link>
                                                        : <span className="tc-view-link" style={{ cursor:'default', opacity:0.4 }}>
                                                            View <LuExternalLink size={11}/>
                                                          </span>
                                                    }
                                                </td>
                                            </tr>
                                        ))
                                        : <tr className="tc-empty-row"><td colSpan={6}>No call records found</td></tr>
                                    }
                                </tbody>
                            </table>
                        </div>
                    </Card>
                }

                {/* ── Row 3: Talk Time + Outcomes + Missed Callbacks ── */}
                {loading
                    ? <div className="tc-row3"><Sk h={200} r={14}/><Sk h={200} r={14}/><Sk h={200} r={14}/></div>
                    : <div className="tc-row3">

                        {/* Overall Talk Time */}
                        <Card>
                            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:8 }}>
                                <SectionTitle title="Overall Talk Time" sub="Today's call duration"/>
                                <select className="tc-select" style={{ fontSize:11, padding:'3px 8px' }}>
                                    <option>Today</option><option>This Week</option>
                                </select>
                            </div>
                            <div className="tc-talk-time">
                                <span className="tc-talk-n">{pad(tkH)}</span>
                                <span className="tc-talk-unit">hrs</span>
                                <span className="tc-talk-sep">:</span>
                                <span className="tc-talk-n">{pad(tkM)}</span>
                                <span className="tc-talk-unit">mins</span>
                            </div>
                            <div className="tc-talk-bar-wrap">
                                <div className="tc-talk-bar" style={{ width:`${tkPct}%` }}/>
                            </div>
                            <div className="tc-talk-bar-lbl">{tkPct}% of 8hr goal</div>
                            <div className="tc-talk-need">Need {tkNeed}hrs more</div>
                            <div className="tc-talk-desc">
                                to complete today's target.<br/>
                                Consistent talk time drives better conversion results.
                            </div>
                        </Card>

                        {/* Call Outcomes */}
                        <Card style={{ display:'flex', flexDirection:'column' }}>
                            <SectionTitle title="Call Outcomes" sub="Result breakdown for today"/>
                            {outData.length > 0 ? (
                                <>
                                    <ResponsiveContainer width="100%" height={140}>
                                        <PieChart>
                                            <Pie data={outData} cx="50%" cy="50%" innerRadius={36} outerRadius={65}
                                                dataKey="value" strokeWidth={0} startAngle={90} endAngle={-270}>
                                                {outData.map((_, i) => <Cell key={i} fill={OUT_COLS[i % OUT_COLS.length]}/>)}
                                            </Pie>
                                            <Tooltip contentStyle={{ fontSize:11, borderRadius:8, border:`1px solid ${BOR}` }}/>
                                        </PieChart>
                                    </ResponsiveContainer>
                                    <div style={{ display:'flex', flexWrap:'wrap', gap:'6px 14px', marginTop:8 }}>
                                        {outLegend.map(({ k, color }) => (
                                            <div key={k} style={{ display:'flex', alignItems:'center', gap:5, fontSize:11, color:'#6B7280' }}>
                                                <div style={{ width:8, height:8, borderRadius:'50%', background:color, flexShrink:0 }}/>
                                                {k} ({(outcomes ?? {})[k] || 0})
                                            </div>
                                        ))}
                                    </div>
                                    <div style={{ marginTop:10, padding:'8px 10px', background:'#f8fafc',
                                        borderRadius:8, border:`1px solid ${BOR}` }}>
                                        <div style={{ fontSize:10, color:MUT, textTransform:'uppercase', letterSpacing:'0.5px', marginBottom:3 }}>
                                            Success Rate
                                        </div>
                                        <div style={{ fontSize:18, fontWeight:800,
                                            color: callSuccessRate >= 50 ? '#10b981' : callSuccessRate >= 25 ? '#f59e0b' : '#ef4444' }}>
                                            {callSuccessRate}%
                                        </div>
                                    </div>
                                </>
                            ) : (
                                <div style={{ flex:1, display:'flex', flexDirection:'column', alignItems:'center',
                                    justifyContent:'center', color:MUT, fontSize:12, gap:6, padding:'20px 0' }}>
                                    <IoCallOutline size={28} style={{ opacity:0.35 }}/>
                                    No call outcomes yet today
                                </div>
                            )}
                        </Card>

                        {/* Missed Callbacks */}
                        <Card>
                            <div className="tc-missed-head">
                                <div>
                                    <div style={{ fontSize:13, fontWeight:700, color:DK }}>Missed Callbacks</div>
                                    <div style={{ fontSize:11, color:MUT, marginTop:2 }}>
                                        {callbacks.length} pending callback{callbacks.length !== 1 ? 's' : ''}
                                    </div>
                                </div>
                                <Link href="/telecaller/followups/today">
                                    <button className="tc-cb-btn" style={{ textDecoration:'none' }}>View All</button>
                                </Link>
                            </div>
                            {callbacks.length > 0 ? (
                                <div className="tc-cb-scroll">
                                <table className="tc-cb-tbl">
                                    <thead>
                                        <tr><th>NAME</th><th>DATE</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        {callbacks.map((cb) => (
                                            <tr key={cb.id}>
                                                <td style={{ fontWeight:600, color:DK, maxWidth:90,
                                                    overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>
                                                    {cb.lead_name || 'Unknown'}
                                                </td>
                                                <td style={{ color:MUT, whiteSpace:'nowrap', fontSize:10 }}>
                                                    {cb.created_at ? cb.created_at.split(',')[0] : '—'}
                                                </td>
                                                <td>
                                                    {cb.encrypted_lead_id
                                                        ? <Link href={`/telecaller/leads/${cb.encrypted_lead_id}`}>
                                                            <button className="tc-cb-btn">Call Back</button>
                                                          </Link>
                                                        : <button className="tc-cb-btn">Call Back</button>
                                                    }
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                </div>
                            ) : (
                                <div style={{ display:'flex', flexDirection:'column', alignItems:'center',
                                    justifyContent:'center', color:MUT, fontSize:12, gap:6,
                                    padding:'20px 0', height:148 }}>
                                    <MdOutlinePhoneInTalk size={26} style={{ opacity:0.35 }}/>
                                    No missed callbacks
                                </div>
                            )}
                        </Card>
                    </div>
                }

                {/* ── Upcoming Calendar ── */}
                {loading
                    ? <Sk h={260} r={14}/>
                    : <UpcomingCalendar initialData={followup_calendar}/>
                }

            </div>
        </>
    );
}
