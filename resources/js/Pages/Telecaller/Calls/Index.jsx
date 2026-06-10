import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { LuPhone, LuDownload, LuSearch, LuRefreshCw,
         LuPhoneCall, LuPhoneMissed, LuPhoneIncoming, LuHistory, LuFilter } from 'react-icons/lu';
import { MdOutlinePhoneInTalk } from 'react-icons/md';

const OR='#FF5C00', DK='#1D1D1D', WH='#FEFEFE', MUT='#9CA3AF', BOR='#F0F0F0', BDY='#374151';

// ─── Helpers ──────────────────────────────────────────────────────────────────
const OUTCOME_LABELS = {
    interested:'Interested', not_interested:'Not Interested',
    wrong_number:'Wrong Number', call_back_later:'Call Back Later', switched_off:'Switched Off',
};

function fmtSeconds(s) {
    s=Math.max(0,Math.round(s));
    const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;
    if(h>0) return `${h}h ${m}m`;
    if(m>0) return `${m}m ${sec}s`;
    return `${sec}s`;
}

const STATUS_STYLES = {
    completed:    {bg:'#ECFDF5',color:'#16A34A',border:'#BBF7D0'},
    answered:     {bg:'#EFF6FF',color:'#1D4ED8',border:'#BFDBFE'},
    'in-progress':{bg:'#E0F2FE',color:'#0369A1',border:'#7DD3FC'},
    ringing:      {bg:'#FFFBEB',color:'#92400E',border:'#FCD34D'},
    missed:       {bg:'#FEF2F2',color:'#991B1B',border:'#FECACA'},
    'no-answer':  {bg:'#FEF2F2',color:'#991B1B',border:'#FECACA'},
    busy:         {bg:'#FEF2F2',color:'#991B1B',border:'#FECACA'},
    failed:       {bg:'#FEF2F2',color:'#991B1B',border:'#FECACA'},
    canceled:     {bg:'#FEF9C3',color:'#713F12',border:'#FDE68A'},
};

// ─── Sub-components ───────────────────────────────────────────────────────────
function StatusBadge({ status }) {
    const s=status?.toLowerCase()??'';
    const st=STATUS_STYLES[s]??{bg:'#F3F4F6',color:'#6B7280',border:'#E5E7EB'};
    return (
        <span style={{ display:'inline-flex', alignItems:'center', gap:4,
            padding:'3px 10px', borderRadius:20, fontSize:11, fontWeight:700,
            background:st.bg, color:st.color, border:`1px solid ${st.border}`, whiteSpace:'nowrap' }}>
            <span style={{ width:5, height:5, borderRadius:'50%', background:st.color, flexShrink:0 }}/>
            {status ? status.charAt(0).toUpperCase()+status.slice(1) : '—'}
        </span>
    );
}

function OutcomePill({ outcome }) {
    if(!outcome) return <span style={{ color:MUT, fontSize:12 }}>—</span>;
    return (
        <span style={{ padding:'3px 10px', borderRadius:20, fontSize:11, fontWeight:600,
            background:'#FFF7ED', color:OR, border:'1px solid #FED7AA', display:'inline-block' }}>
            {OUTCOME_LABELS[outcome]??outcome}
        </span>
    );
}

function CallButton({ phone, leadId }) {
    const [st,setSt]=useState('idle');
    useEffect(()=>{
        const a=()=>setSt(p=>p==='calling'?'active':p);
        const b=()=>setSt('idle');
        document.addEventListener('gc:callAccepted',a);
        document.addEventListener('gc:callEnded',b);
        return()=>{document.removeEventListener('gc:callAccepted',a);document.removeEventListener('gc:callEnded',b);};
    },[]);
    async function go(){
        if(!window.GC)return;
        if(st==='active'||st==='calling'){window.GC.endCall();return;}
        setSt('calling');
        try{await window.GC.startCall(phone,leadId??null);}catch(_){setSt('idle');}
    }
    const cfg={idle:{bg:'#ECFDF5',color:'#16A34A',border:'#BBF7D0'},calling:{bg:'#FFFBEB',color:'#D97706',border:'#FDE68A'},active:{bg:'#FEF2F2',color:'#DC2626',border:'#FECACA'}};
    const c=cfg[st];
    return (
        <button type="button" onClick={go} disabled={st==='calling'} title="Call back"
            style={{ width:30,height:30,borderRadius:8,background:c.bg,color:c.color,
                border:`1px solid ${c.border}`,display:'inline-flex',alignItems:'center',
                justifyContent:'center',cursor:st==='calling'?'not-allowed':'pointer',transition:'all .15s' }}>
            <MdOutlinePhoneInTalk size={14}/>
        </button>
    );
}

function CallDetailModal({ call, onClose }) {
    if(!call) return null;
    const rows=[
        {label:'Lead',       value:call.lead_name?`${call.lead_name} (${call.lead_code??'—'})`:'—'},
        {label:'Phone',      value:call.lead_phone??call.customer_number??'—'},
        {label:'Direction',  value:call.direction??'—'},
        {label:'Status',     value:call.status??'—'},
        {label:'Outcome',    value:call.outcome?(OUTCOME_LABELS[call.outcome]??call.outcome):'—'},
        {label:'Answered At',value:call.answered_at??'—'},
        {label:'Ended At',   value:call.ended_at??'—'},
        {label:'End Reason', value:call.end_reason??'—'},
        {label:'Call SID',   value:call.call_sid??'—'},
    ];
    return (
        <div className="modal fade show d-block" tabIndex="-1"
            style={{ background:'rgba(0,0,0,.45)' }} onClick={onClose}>
            <div className="modal-dialog modal-dialog-centered" onClick={e=>e.stopPropagation()}>
                <div className="modal-content" style={{ borderRadius:14, border:'none', boxShadow:'0 20px 60px rgba(0,0,0,0.18)' }}>
                    <div className="modal-header" style={{ background:DK, borderRadius:'14px 14px 0 0', border:'none' }}>
                        <h5 className="modal-title" style={{ color:'#fff', fontWeight:700, fontSize:14,
                            display:'flex', alignItems:'center', gap:8, fontFamily:'Poppins,sans-serif' }}>
                            <LuPhone size={15} style={{ color:OR }}/> Call Details
                        </h5>
                        <button type="button" className="btn-close btn-close-white" onClick={onClose}/>
                    </div>
                    <div className="modal-body p-0">
                        <table className="table table-sm mb-0">
                            <tbody>
                                {rows.map(r=>(
                                    <tr key={r.label}>
                                        <th className="ps-3 py-2 fw-semibold"
                                            style={{ width:'35%', background:'#FAFBFC', color:MUT, fontSize:11.5, fontFamily:'Poppins,sans-serif' }}>{r.label}</th>
                                        <td className="ps-3 py-2"
                                            style={{ fontSize:12.5, color:DK, fontFamily:'Poppins,sans-serif' }}>{r.value}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="modal-footer" style={{ borderTop:`1px solid ${BOR}`, padding:'10px 18px' }}>
                        <button type="button" onClick={onClose}
                            style={{ background:'#F3F4F6', color:BDY, border:'none', borderRadius:8,
                                fontWeight:600, padding:'7px 14px', fontSize:12.5, cursor:'pointer', fontFamily:'Poppins,sans-serif' }}>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

const TABS = [
    { key:'outbound', href:'/telecaller/calls/outbound', label:'Outbound', icon:<LuPhoneCall size={14}/>,    accent:OR        },
    { key:'inbound',  href:'/telecaller/calls/inbound',  label:'Inbound',  icon:<LuPhoneIncoming size={14}/>,accent:'#10B981' },
    { key:'missed',   href:'/telecaller/calls/missed',   label:'Missed',   icon:<LuPhoneMissed size={14}/>,  accent:'#EF4444' },
    { key:'history',  href:'/telecaller/calls/history',  label:'History',  icon:<LuHistory size={14}/>,      accent:'#6B7280' },
];

function Card({ children, style={} }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden', ...style }}>
            {children}
        </div>
    );
}
function SHead({ icon, title, sub, right }) {
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', gap:10,
            padding:'13px 18px', borderBottom:`1px solid ${BOR}`,
            background:'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
            <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                <div style={{ width:3, height:28, borderRadius:2, background:OR, flexShrink:0 }}/>
                <div>
                    <div style={{ display:'flex', alignItems:'center', gap:7 }}>
                        {icon && <span style={{ color:OR }}>{icon}</span>}
                        <span style={{ fontSize:13, fontWeight:700, color:DK }}>{title}</span>
                    </div>
                    {sub && <div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>{sub}</div>}
                </div>
            </div>
            {right && <div style={{ flexShrink:0 }}>{right}</div>}
        </div>
    );
}

// ─── Input helpers ────────────────────────────────────────────────────────────
function FI({ label, ...p }) {
    const [f,sf]=useState(false);
    const base={ width:'100%', height:34, borderRadius:8, border:`1px solid ${f?OR:'#E5E7EB'}`,
        fontSize:12.5, color:DK, background:f?'#fff':'#FAFBFC', padding:'0 10px', outline:'none',
        boxShadow:f?'0 0 0 3px rgba(255,92,0,0.09)':'none', transition:'all .15s', fontFamily:'Poppins,sans-serif' };
    return (
        <div>
            {label && <label style={{ fontSize:9.5, fontWeight:700, color:MUT, textTransform:'uppercase',
                letterSpacing:'.5px', display:'block', marginBottom:4, fontFamily:'Poppins,sans-serif' }}>{label}</label>}
            <input {...p} style={base} onFocus={()=>sf(true)} onBlur={()=>sf(false)}/>
        </div>
    );
}
function FS({ label, children, ...p }) {
    const [f,sf]=useState(false);
    const base={ width:'100%', height:34, borderRadius:8, border:`1px solid ${f?OR:'#E5E7EB'}`,
        fontSize:12.5, color:DK, background:f?'#fff':'#FAFBFC', padding:'0 10px', outline:'none',
        boxShadow:f?'0 0 0 3px rgba(255,92,0,0.09)':'none', transition:'all .15s', fontFamily:'Poppins,sans-serif' };
    return (
        <div>
            {label && <label style={{ fontSize:9.5, fontWeight:700, color:MUT, textTransform:'uppercase',
                letterSpacing:'.5px', display:'block', marginBottom:4, fontFamily:'Poppins,sans-serif' }}>{label}</label>}
            <select {...p} style={base} onFocus={()=>sf(true)} onBlur={()=>sf(false)}>{children}</select>
        </div>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ scope, title, callLogs, statusOptions, outcomeOptions, filters, kpi }) {
    const [date,    setDate]    = useState(filters?.date      ?? '');
    const [status,  setStatus]  = useState(filters?.status    ?? '');
    const [outcome, setOutcome] = useState(filters?.outcome   ?? '');
    const [dateFrom,setDateFrom]= useState(filters?.date_from ?? '');
    const [dateTo,  setDateTo]  = useState(filters?.date_to   ?? '');
    const [activeCall, setActiveCall] = useState(null);

    const isDrilldown = !!(filters?.date_from || filters?.date_to);

    function applyFilters(e) {
        e.preventDefault();
        const p={};
        if(date) p.date=date; if(dateFrom) p.date_from=dateFrom;
        if(dateTo) p.date_to=dateTo; if(status) p.status=status; if(outcome) p.outcome=outcome;
        router.get(`/telecaller/calls/${scope}`, p, {preserveScroll:true});
    }
    function resetFilters() {
        setDate(''); setDateFrom(''); setDateTo(''); setStatus(''); setOutcome('');
        router.get(`/telecaller/calls/${scope}`, {}, {preserveScroll:true});
    }
    function exportUrl(format) {
        const p=new URLSearchParams({format});
        if(date) p.set('date',date); if(status) p.set('status',status); if(outcome) p.set('outcome',outcome);
        return `/telecaller/calls/${scope}/export?${p}`;
    }

    const k=kpi??{};
    const successRate=k.total>0?Math.round((k.completed/k.total)*100):0;
    const activeTab=TABS.find(t=>t.key===scope)??TABS[0];
    const colSpan = scope==='missed' ? 5 : 7;

    return (
        <>
            <Head title={title}/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .cl-pg, .cl-pg div, .cl-pg span:not([class*="material"]),
                .cl-pg p,.cl-pg label,.cl-pg button,.cl-pg input,.cl-pg select,
                .cl-pg a,.cl-pg td,.cl-pg th,.cl-pg small {
                    font-family:'Poppins',sans-serif !important;
                }
                .cl-pg { display:flex; flex-direction:column; gap:14px; }

                /* ── KPI cards ── */
                .cl-kpi-row { display:flex; gap:12px; flex-wrap:wrap; }
                .cl-kpi-row > * { flex:1 1 180px; min-width:0; }

                /* ── Two-column body ── */
                .cl-body {
                    display:grid;
                    grid-template-columns:240px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:900px){ .cl-body{ grid-template-columns:1fr; } }

                /* ── Table ── */
                .cl-tbl { width:100%; min-width:600px; border-collapse:collapse; }
                .cl-tbl thead th {
                    background:#FAFBFC; color:${MUT}; font-size:9.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.7px; padding:10px 14px;
                    border-bottom:2px solid ${BOR}; white-space:nowrap;
                    position:sticky; top:0; z-index:1;
                }
                .cl-tbl tbody td {
                    padding:11px 14px; vertical-align:middle; font-size:12.5px; color:${BDY};
                    border-bottom:1px solid #F9FAFB; transition:background .08s;
                }
                .cl-tbl tbody tr:last-child td { border-bottom:none; }
                .cl-tbl tbody tr:hover td { background:#FFF7ED; }
                .cl-tbl tbody tr:hover td:first-child { border-left:3px solid ${OR}; padding-left:11px; }

                /* ── Scrollable table body ── */
                .cl-scroll { max-height:460px; overflow-y:scroll; overflow-x:auto; }
                /* vertical scrollbar — always visible */
                .cl-scroll::-webkit-scrollbar { width:6px; }
                .cl-scroll::-webkit-scrollbar-track { background:#F3F4F6; border-radius:4px; }
                .cl-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .cl-scroll::-webkit-scrollbar-thumb:hover { background:${OR}; }
                /* horizontal scrollbar */
                .cl-scroll::-webkit-scrollbar:horizontal { height:5px; }
                .cl-scroll::-webkit-scrollbar-track:horizontal { background:#F3F4F6; }
                .cl-scroll::-webkit-scrollbar-thumb:horizontal { background:#D1D5DB; border-radius:4px; }
                .cl-scroll::-webkit-scrollbar-thumb:horizontal:hover { background:${OR}; }

                /* ── Action buttons ── */
                .cl-act {
                    width:30px; height:30px; border-radius:8px; background:#F3F4F6; color:${MUT};
                    border:1px solid #E5E7EB; display:inline-flex; align-items:center; justify-content:center;
                    cursor:pointer; transition:all .15s; flex-shrink:0;
                }
                .cl-act:hover { background:${DK}; border-color:${DK}; color:#fff; }

                /* ── Pagination ── */
                .cl-pager {
                    padding:10px 18px; border-top:1px solid ${BOR};
                    display:flex; align-items:center; justify-content:space-between;
                    flex-wrap:wrap; gap:9px; background:#FAFBFC;
                }
                .cl-pager .page-link { background:${WH}; border-color:#E5E7EB; color:${BDY}; font-size:12px; border-radius:7px; }
                .cl-pager .page-item.active .page-link { background:${OR}; border-color:${OR}; color:#fff; }
                .cl-pager .page-item.disabled .page-link { opacity:.4; }

                /* ── Export menu ── */
                .cl-exp-menu { border-radius:9px; border:1px solid #E5E7EB; overflow:hidden; min-width:150px; }
                .cl-exp-menu .dropdown-item { font-size:12.5px; padding:8px 13px; display:flex; align-items:center; gap:7px; color:${BDY}; }
                .cl-exp-menu .dropdown-item:hover { background:${DK}; color:#fff; }
            `}</style>

            {activeCall && <CallDetailModal call={activeCall} onClose={()=>setActiveCall(null)}/>}

            <div className="cl-pg">

                {/* ── Tab navigation (full width) ── */}
                <Card>
                    <div style={{ padding:'12px 18px', display:'flex', alignItems:'center', gap:8, flexWrap:'wrap' }}>
                        {TABS.map(tab=>{
                            const on=scope===tab.key;
                            return (
                                <Link key={tab.key} href={tab.href}
                                    style={{ display:'inline-flex', alignItems:'center', gap:6,
                                        padding:'7px 14px', borderRadius:9, fontSize:12.5, fontWeight:600,
                                        textDecoration:'none', border:`1px solid ${on?tab.accent:BOR}`,
                                        background:on?tab.accent:WH, color:on?'#fff':MUT,
                                        transition:'all .15s' }}>
                                    <span style={{ color:on?'rgba(255,255,255,0.85)':tab.accent }}>{tab.icon}</span>
                                    {tab.label}
                                </Link>
                            );
                        })}
                    </div>
                </Card>

                {/* ── KPI stat cards (full width) ── */}
                <div className="cl-kpi-row">
                    <Card style={{ padding:'13px 16px', display:'flex', alignItems:'center', gap:12 }}>
                        <div style={{ width:40, height:40, borderRadius:10, background:`${activeTab.accent}18`,
                            display:'flex', alignItems:'center', justifyContent:'center',
                            color:activeTab.accent, fontSize:18, flexShrink:0 }}>{activeTab.icon}</div>
                        <div>
                            <div style={{ fontSize:22, fontWeight:800, color:DK, lineHeight:1.1 }}>{k.total??0}</div>
                            <div style={{ fontSize:11, fontWeight:600, color:MUT, marginTop:2 }}>Total Calls</div>
                            <div style={{ fontSize:10.5, color:MUT }}>{title}</div>
                        </div>
                    </Card>
                    <Card style={{ padding:'13px 16px', display:'flex', alignItems:'center', gap:12 }}>
                        <div style={{ width:40, height:40, borderRadius:10, background:'#ECFDF5',
                            display:'flex', alignItems:'center', justifyContent:'center',
                            color:'#10B981', fontSize:18, flexShrink:0 }}><LuPhone size={18}/></div>
                        <div>
                            <div style={{ fontSize:22, fontWeight:800, color:DK, lineHeight:1.1 }}>{k.completed??0}</div>
                            <div style={{ fontSize:11, fontWeight:600, color:MUT, marginTop:2 }}>Completed</div>
                            <div style={{ fontSize:10.5, color:MUT }}>{successRate}% success rate</div>
                        </div>
                    </Card>
                    <Card style={{ padding:'13px 16px', display:'flex', alignItems:'center', gap:12 }}>
                        <div style={{ width:40, height:40, borderRadius:10, background:'#FEF2F2',
                            display:'flex', alignItems:'center', justifyContent:'center',
                            color:'#EF4444', fontSize:18, flexShrink:0 }}><LuPhoneMissed size={18}/></div>
                        <div>
                            <div style={{ fontSize:22, fontWeight:800, color:DK, lineHeight:1.1 }}>{k.failed??0}</div>
                            <div style={{ fontSize:11, fontWeight:600, color:MUT, marginTop:2 }}>{scope==='missed'?'Missed':'Failed / Missed'}</div>
                            <div style={{ fontSize:10.5, color:MUT }}>{k.total>0?`${Math.round(((k.failed??0)/k.total)*100)}% of total`:'—'}</div>
                        </div>
                    </Card>
                    <Card style={{ padding:'13px 16px', display:'flex', alignItems:'center', gap:12 }}>
                        <div style={{ width:40, height:40, borderRadius:10, background:'#FFFBEB',
                            display:'flex', alignItems:'center', justifyContent:'center',
                            color:'#F59E0B', fontSize:18, flexShrink:0 }}><LuHistory size={18}/></div>
                        <div>
                            <div style={{ fontSize:22, fontWeight:800, color:DK, lineHeight:1.1 }}>{fmtSeconds(k.avg_duration??0)}</div>
                            <div style={{ fontSize:11, fontWeight:600, color:MUT, marginTop:2 }}>Avg Duration</div>
                            <div style={{ fontSize:10.5, color:MUT }}>Total: {fmtSeconds(k.total_seconds??0)}</div>
                        </div>
                    </Card>
                </div>

                {/* ── Two-column: filter left + table right ── */}
                <div className="cl-body">

                    {/* ── LEFT: filter ── */}
                    <Card>
                        <SHead icon={<LuFilter size={13}/>} title="Filter" sub="Refine call records"/>
                        <div style={{ padding:'14px 16px', display:'flex', flexDirection:'column', gap:12 }}>

                            <FI label="Date" type="date" value={date} onChange={e=>setDate(e.target.value)}/>

                            <FS label="Status" value={status} onChange={e=>setStatus(e.target.value)}>
                                <option value="">All Statuses</option>
                                {statusOptions.map(s=><option key={s} value={s}>{s.charAt(0).toUpperCase()+s.slice(1)}</option>)}
                            </FS>

                            <FS label="Outcome" value={outcome} onChange={e=>setOutcome(e.target.value)}>
                                <option value="">All Outcomes</option>
                                {(outcomeOptions??[]).map(o=><option key={o} value={o}>{OUTCOME_LABELS[o]??o}</option>)}
                            </FS>

                            {isDrilldown && (
                                <>
                                    <FI label="From" type="date" value={dateFrom} onChange={e=>setDateFrom(e.target.value)}/>
                                    <FI label="To"   type="date" value={dateTo}   onChange={e=>setDateTo(e.target.value)}/>
                                </>
                            )}

                            <button onClick={applyFilters}
                                style={{ width:'100%', background:OR, color:'#fff', border:'none', borderRadius:8,
                                    padding:'9px', fontSize:12.5, fontWeight:600, cursor:'pointer',
                                    display:'flex', alignItems:'center', justifyContent:'center', gap:6,
                                    transition:'background .15s' }}>
                                <LuSearch size={13}/> Apply Filters
                            </button>

                            <button onClick={resetFilters}
                                style={{ width:'100%', background:WH, color:MUT, border:`1px solid ${BOR}`,
                                    borderRadius:8, padding:'8px', fontSize:12.5, fontWeight:600, cursor:'pointer',
                                    display:'flex', alignItems:'center', justifyContent:'center', gap:5 }}>
                                <LuRefreshCw size={12}/> Reset
                            </button>

                            <div style={{ height:1, background:BOR }}/>

                            {/* Export */}
                            <div className="dropdown">
                                <button type="button" data-bs-toggle="dropdown"
                                    style={{ width:'100%', background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                                        borderRadius:8, padding:'8px', fontSize:12.5, fontWeight:600, cursor:'pointer',
                                        display:'flex', alignItems:'center', justifyContent:'center', gap:6 }}>
                                    <LuDownload size={13}/> Export
                                </button>
                                <ul className="dropdown-menu w-100 cl-exp-menu">
                                    <li><a className="dropdown-item" href={exportUrl('excel')} target="_blank" rel="noreferrer">
                                        📊 Excel (.xlsx)</a></li>
                                    <li><a className="dropdown-item" href={exportUrl('pdf')} target="_blank" rel="noreferrer">
                                        📄 PDF</a></li>
                                </ul>
                            </div>

                            {isDrilldown && (
                                <div style={{ background:'#FFF7ED', borderRadius:8, padding:'8px 10px',
                                    fontSize:11.5, color:OR, fontWeight:500 }}>
                                    <LuFilter size={11} style={{ verticalAlign:'middle', marginRight:4 }}/>
                                    Drilldown active
                                    {outcome && ` · ${outcome.replace(/_/g,' ')}`}
                                    <button onClick={resetFilters}
                                        style={{ float:'right', background:'none', border:'none',
                                            cursor:'pointer', color:MUT, fontSize:12, fontWeight:700 }}>×</button>
                                </div>
                            )}
                        </div>
                    </Card>

                    {/* ── RIGHT: table ── */}
                    <Card>
                        <SHead icon={activeTab.icon} title={`${title} List`}
                            sub={`${callLogs.total} records found`}
                            right={
                                <span style={{ background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                                    fontSize:11, fontWeight:700, padding:'2px 10px', borderRadius:20 }}>
                                    {callLogs.total} records
                                </span>
                            }/>

                        {/* Scrollable table */}
                        <div className="cl-scroll">
                            <table className="cl-tbl">
                                <thead>
                                    <tr>
                                        <th style={{ width:38 }}>#</th>
                                        <th>Date &amp; Time</th>
                                        <th>Lead</th>
                                        {scope!=='missed' && <th>Duration</th>}
                                        {scope!=='missed' && <th>Outcome</th>}
                                        <th>Status</th>
                                        <th style={{ width:78 }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {callLogs.data.length===0
                                        ? <tr><td colSpan={colSpan}>
                                            <div style={{ textAlign:'center', padding:'52px 0' }}>
                                                <div style={{ width:58, height:58, borderRadius:16,
                                                    background:scope==='missed'?'#FEF2F2':'#FFF7ED',
                                                    display:'flex', alignItems:'center', justifyContent:'center',
                                                    margin:'0 auto 14px', fontSize:28 }}>
                                                    {scope==='missed'?'📵':'📞'}
                                                </div>
                                                <div style={{ fontSize:14, fontWeight:700, color:DK, marginBottom:6 }}>
                                                    {scope==='missed'?'No missed calls':'No calls yet'}
                                                </div>
                                                <div style={{ fontSize:12, color:MUT, maxWidth:240, margin:'0 auto', lineHeight:1.7 }}>
                                                    {scope==='missed'
                                                        ? 'Great work — no missed calls in this period.'
                                                        : 'Calls will appear here once you start making or receiving them.'}
                                                </div>
                                            </div>
                                          </td></tr>
                                        : callLogs.data.map((call,idx)=>{
                                            const sno=(callLogs.current_page-1)*callLogs.per_page+idx+1;
                                            const cbNum=call.lead_phone||call.customer_number;
                                            return (
                                                <tr key={call.id}>
                                                    <td style={{ color:MUT, fontSize:11, fontWeight:600 }}>{sno}</td>
                                                    <td>
                                                        <div style={{ fontWeight:700, color:DK, fontSize:12.5 }}>
                                                            {call.created_at_fmt?.split(',')[0]}
                                                        </div>
                                                        <div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>
                                                            {call.created_at_fmt?.split(',')?.[1]?.trim()}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div style={{ fontWeight:700, color:DK, fontSize:12.5 }}>
                                                            {call.lead_name??'N/A'}
                                                        </div>
                                                        <div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>
                                                            {call.lead_code??'—'} · {call.lead_phone??call.customer_number??'—'}
                                                        </div>
                                                    </td>
                                                    {scope!=='missed' && (
                                                        <td>
                                                            <span style={{ padding:'2px 9px', borderRadius:20,
                                                                fontSize:11, fontWeight:700, background:'#F3F4F6',
                                                                color:BDY, border:'1px solid #E5E7EB',
                                                                fontFamily:'monospace', display:'inline-block' }}>
                                                                {call.duration_fmt}
                                                            </span>
                                                        </td>
                                                    )}
                                                    {scope!=='missed' && <td><OutcomePill outcome={call.outcome}/></td>}
                                                    <td><StatusBadge status={call.status}/></td>
                                                    <td>
                                                        <div style={{ display:'flex', gap:5 }}>
                                                            <button type="button" className="cl-act" title="View details"
                                                                onClick={()=>setActiveCall(call)}>
                                                                <LuSearch size={13}/>
                                                            </button>
                                                            {cbNum && <CallButton phone={cbNum} leadId={call.lead_id}/>}
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    }
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        <div className="cl-pager">
                            <small style={{ color:MUT }}>
                                Showing {callLogs.from??0}–{callLogs.to??0} of {callLogs.total}
                            </small>
                            {callLogs.last_page>1 && (
                                <nav>
                                    <ul className="pagination pagination-sm mb-0" style={{ gap:3 }}>
                                        {callLogs.links.map((link,i)=>(
                                            <li key={i} className={['page-item',link.active?'active':'',!link.url?'disabled':''].join(' ')}>
                                                {link.url
                                                    ? <Link href={link.url} className="page-link" dangerouslySetInnerHTML={{ __html:link.label }}/>
                                                    : <span className="page-link" dangerouslySetInnerHTML={{ __html:link.label }}/>}
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            )}
                        </div>
                    </Card>

                </div>{/* end cl-body */}
            </div>
        </>
    );
}
