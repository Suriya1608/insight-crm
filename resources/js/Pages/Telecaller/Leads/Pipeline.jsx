import { Head, Link, router } from '@inertiajs/react';
import { useState, useRef, useCallback } from 'react';
import {
    LuSearch, LuRotateCcw, LuLayoutList, LuPhone, LuCalendar,
    LuExternalLink, LuUsers, LuUser, LuTrendingUp, LuRefreshCw,
    LuX, LuCheck, LuFilter,
} from 'react-icons/lu';
import { MdOutlinePhoneInTalk } from 'react-icons/md';

const OR='#FF5C00', DK='#1D1D1D', WH='#FEFEFE', MUT='#9CA3AF', BOR='#F0F0F0', BDY='#374151';

const STATUS_CFG = {
    new:            { label:'New',            color:'#3B82F6', bg:'#EFF6FF', light:'#BFDBFE', icon:<LuUsers size={14}/> },
    assigned:       { label:'Assigned',       color:OR,        bg:'#FFF7ED', light:'#FED7AA', icon:<LuUser size={14}/> },
    contacted:      { label:'Contacted',      color:'#06B6D4', bg:'#ECFEFF', light:'#A5F3FC', icon:<MdOutlinePhoneInTalk size={14}/> },
    interested:     { label:'Interested',     color:'#10B981', bg:'#ECFDF5', light:'#6EE7B7', icon:<LuTrendingUp size={14}/> },
    follow_up:      { label:'Follow Up',      color:'#8B5CF6', bg:'#FAF5FF', light:'#C4B5FD', icon:<LuRefreshCw size={14}/> },
    not_interested: { label:'Not Interested', color:'#EF4444', bg:'#FEF2F2', light:'#FCA5A5', icon:<LuX size={14}/> },
    converted:      { label:'Converted',      color:'#059669', bg:'#D1FAE5', light:'#6EE7B7', icon:<LuCheck size={14}/> },
};

const AVATARS = [OR,'#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#EC4899','#3B82F6'];
const avatarBg = n => AVATARS[(n?.charCodeAt(0)??0) % AVATARS.length];

function AgingBadge({ days }) {
    if (!days && days!==0) return null;
    if (days>=6) return <span style={{ background:'#FEF2F2', color:'#DC2626', fontSize:9, fontWeight:700, padding:'1px 7px', borderRadius:20 }}>{days}d old</span>;
    if (days>=3) return <span style={{ background:'#FFFBEB', color:'#D97706', fontSize:9, fontWeight:700, padding:'1px 7px', borderRadius:20 }}>{days}d</span>;
    return null;
}

function Toast({ message, type }) {
    if (!message) return null;
    return (
        <div style={{ position:'fixed', bottom:28, right:28, zIndex:10000,
            padding:'12px 20px', borderRadius:12, fontSize:12.5, fontWeight:600,
            color:'#fff', boxShadow:'0 8px 28px rgba(0,0,0,.18)',
            background:type==='success'?'#10B981':'#EF4444',
            display:'flex', alignItems:'center', gap:8,
            pointerEvents:'none', fontFamily:'Poppins,sans-serif' }}>
            {type==='success'?<LuCheck size={14}/>:<LuX size={14}/>} {message}
        </div>
    );
}

// ─── Lead card ────────────────────────────────────────────────────────────────
function LeadCard({ lead, urls, isDragging, onDragStart, onDragEnd }) {
    const bg = avatarBg(lead.name);
    return (
        <div draggable onDragStart={e=>onDragStart(e,lead)} onDragEnd={onDragEnd}
            className="pl-card" style={{ opacity:isDragging?.35:1 }}>
            <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', marginBottom:7 }}>
                <code style={{ fontSize:9.5, fontWeight:700, color:MUT, background:'#F3F4F6',
                    padding:'2px 7px', borderRadius:5 }}>
                    {lead.lead_code}
                </code>
                <AgingBadge days={lead.days_aged}/>
            </div>
            <div style={{ display:'flex', gap:8, alignItems:'flex-start', marginBottom:8 }}>
                <div style={{ width:30, height:30, borderRadius:8, flexShrink:0,
                    background:`linear-gradient(135deg,${bg},${bg}bb)`,
                    display:'flex', alignItems:'center', justifyContent:'center',
                    color:'#fff', fontSize:12, fontWeight:800 }}>
                    {(lead.name||'?')[0].toUpperCase()}
                </div>
                <div style={{ flex:1, minWidth:0 }}>
                    <div style={{ fontSize:12, fontWeight:700, color:DK, lineHeight:1.25,
                        overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>
                        {lead.name}
                    </div>
                    {lead.phone && (
                        <div style={{ display:'flex', alignItems:'center', gap:3, fontSize:10.5, color:MUT, marginTop:2 }}>
                            <LuPhone size={10} style={{ flexShrink:0 }}/>{lead.phone}
                        </div>
                    )}
                </div>
            </div>
            {lead.course && (
                <div style={{ fontSize:10, color:BDY, marginBottom:6,
                    overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap',
                    background:'#F9FAFB', padding:'2px 7px', borderRadius:5 }}>
                    {lead.course}
                </div>
            )}
            {lead.next_followup && (
                <div style={{ display:'inline-flex', alignItems:'center', gap:4,
                    fontSize:10, color:OR, fontWeight:600, marginBottom:7,
                    background:'#FFF7ED', padding:'2px 7px', borderRadius:5 }}>
                    <LuCalendar size={9} style={{ flexShrink:0 }}/>
                    {new Date(lead.next_followup).toLocaleDateString('en-GB',{ day:'2-digit', month:'short' })}
                </div>
            )}
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center',
                paddingTop:7, borderTop:`1px solid ${BOR}` }}>
                <span style={{ fontSize:9, color:MUT }}>{lead.created_at}</span>
                <a href={`${urls.lead_show_base}/${lead.encrypted_id}`}
                    onClick={e=>e.stopPropagation()} className="pl-view-btn">
                    View <LuExternalLink size={9}/>
                </a>
            </div>
        </div>
    );
}

// ─── Pipeline column ──────────────────────────────────────────────────────────
function PipelineCol({ statusKey, cfg, leads, urls, draggingLead, onDragStart, onDragEnd, onDrop, onDragOver, onDragLeave, isDragOver }) {
    return (
        <div data-status={statusKey}
            onDrop={e=>onDrop(e,statusKey)} onDragOver={e=>onDragOver(e,statusKey)} onDragLeave={onDragLeave}
            className="pl-col"
            style={{ boxShadow:isDragOver?`0 4px 20px ${cfg.color}22`:'0 1px 4px rgba(0,0,0,0.05)' }}>
            {/* Tinted header */}
            <div style={{ background:cfg.bg, borderRadius:'11px 11px 0 0',
                padding:'11px 13px', borderBottom:`1px solid ${cfg.light}` }}>
                <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between' }}>
                    <div style={{ display:'flex', alignItems:'center', gap:7 }}>
                        <div style={{ width:26, height:26, borderRadius:7,
                            background:`${cfg.color}18`,
                            display:'flex', alignItems:'center', justifyContent:'center',
                            color:cfg.color, flexShrink:0 }}>{cfg.icon}</div>
                        <div>
                            <div style={{ fontSize:12, fontWeight:700, color:DK }}>{cfg.label}</div>
                            <div style={{ fontSize:9.5, color:`${cfg.color}99` }}>
                                {leads.length} lead{leads.length!==1?'s':''}
                            </div>
                        </div>
                    </div>
                    <span style={{ background:leads.length>0?cfg.color:'#E5E7EB',
                        color:leads.length>0?'#fff':MUT,
                        fontSize:11, fontWeight:800, padding:'2px 8px',
                        borderRadius:20, transition:'all .2s' }}>
                        {leads.length}
                    </span>
                </div>
            </div>

            {/* Scrollable body */}
            <div className="pl-col-body"
                style={{ background:isDragOver?`${cfg.bg}55`:'#FAFBFC' }}>
                {leads.length===0
                    ? <div style={{ textAlign:'center', padding:'22px 10px' }}>
                        <div style={{ width:34, height:34, borderRadius:9, background:cfg.bg,
                            display:'flex', alignItems:'center', justifyContent:'center',
                            margin:'0 auto 7px', color:`${cfg.color}77` }}>{cfg.icon}</div>
                        <div style={{ fontSize:11, color:MUT }}>No leads</div>
                        <div style={{ fontSize:10, color:'#D1D5DB', marginTop:1 }}>Drop here</div>
                      </div>
                    : leads.map(lead=>(
                        <LeadCard key={lead.id} lead={lead} urls={urls}
                            isDragging={draggingLead?.id===lead.id}
                            onDragStart={onDragStart} onDragEnd={onDragEnd}/>
                    ))
                }
            </div>
        </div>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Pipeline({ columns:init, filters, urls }) {
    const [columns,      setColumns]      = useState(init??{});
    const [draggingLead, setDraggingLead] = useState(null);
    const [draggingFrom, setDraggingFrom] = useState(null);
    const [dragOverCol,  setDragOver]     = useState(null);
    const [saving,       setSaving]       = useState(false);
    const [toast,        setToast]        = useState(null);
    const timer = useRef(null);

    const [search,    setSearch]    = useState(filters?.search??'');
    const [dateRange, setDateRange] = useState(filters?.date_range??'');

    function showToast(msg,type) {
        setToast({message:msg,type});
        clearTimeout(timer.current);
        timer.current = setTimeout(()=>setToast(null),3200);
    }
    function applyFilters(e) { e?.preventDefault(); router.get(urls.pipeline,{search,date_range:dateRange},{preserveState:true}); }
    function resetFilters()  { setSearch(''); setDateRange(''); router.get(urls.pipeline,{},{preserveState:false}); }

    const handleDragStart = useCallback((e,lead)=>{
        const from=Object.keys(columns).find(k=>columns[k].some(l=>l.id===lead.id));
        setDraggingLead(lead); setDraggingFrom(from);
        e.dataTransfer.effectAllowed='move';
    },[columns]);
    const handleDragEnd   = useCallback(()=>{ setDraggingLead(null); setDraggingFrom(null); setDragOver(null); },[]);
    const handleDragOver  = useCallback((e,s)=>{ e.preventDefault(); e.dataTransfer.dropEffect='move'; setDragOver(s); },[]);
    const handleDragLeave = useCallback(()=>setDragOver(null),[]);
    const handleDrop      = useCallback((e,toStatus)=>{
        e.preventDefault(); setDragOver(null);
        if(!draggingLead||!draggingFrom||draggingFrom===toStatus){ setDraggingLead(null); setDraggingFrom(null); return; }
        const from=draggingFrom, lead=draggingLead;
        setColumns(prev=>{ const n={...prev}; n[from]=prev[from].filter(l=>l.id!==lead.id); n[toStatus]=[lead,...prev[toStatus]]; return n; });
        setSaving(true);
        fetch(urls.pipeline_status,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content??''},
            body:JSON.stringify({lead_id:lead.encrypted_id,status:toStatus}),
        }).then(r=>r.json()).then(data=>{
            setSaving(false);
            if(data.success) showToast(`Moved to ${toStatus.replace(/_/g,' ')}`, 'success');
            else{ setColumns(prev=>{const n={...prev};n[toStatus]=prev[toStatus].filter(l=>l.id!==lead.id);n[from]=[lead,...prev[from]];return n;}); showToast(data.message||'Update failed.','error'); }
        }).catch(()=>{
            setSaving(false);
            setColumns(prev=>{const n={...prev};n[toStatus]=prev[toStatus].filter(l=>l.id!==lead.id);n[from]=[lead,...prev[from]];return n;});
            showToast('Network error.','error');
        });
        setDraggingLead(null); setDraggingFrom(null);
    },[draggingLead,draggingFrom,urls.pipeline_status]);

    const totalLeads = Object.values(columns).reduce((s,arr)=>s+(arr?.length??0), 0);

    return (
        <>
            <Head title="My Lead Pipeline"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .pl-pg, .pl-pg div, .pl-pg span:not([class*="material"]),
                .pl-pg p,.pl-pg label,.pl-pg button,.pl-pg input,
                .pl-pg select,.pl-pg a,.pl-pg small,.pl-pg code {
                    font-family:'Poppins',sans-serif !important; box-sizing:border-box;
                }
                .pl-pg { display:flex; flex-direction:column; gap:14px; }

                /* ── Full-width page header ── */
                .pl-header {
                    background:${WH}; border-radius:14px; border:1px solid ${BOR};
                    box-shadow:0 2px 8px rgba(0,0,0,0.04);
                    padding:14px 20px;
                    display:flex; align-items:center; justify-content:space-between; gap:10px;
                }

                /* ── Two-column layout ── */
                .pl-layout {
                    display:grid;
                    grid-template-columns:240px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:860px){ .pl-layout{ grid-template-columns:1fr; } }

                /* ── Left: filter panel ── */
                .pl-left {
                    background:${WH}; border-radius:14px; border:1px solid ${BOR};
                    box-shadow:0 2px 8px rgba(0,0,0,0.04);
                    overflow:hidden;
                }
                .pl-left-head {
                    padding:13px 16px 11px; border-bottom:1px solid ${BOR};
                    background:linear-gradient(135deg,#FAFBFC,${WH});
                    display:flex; align-items:center; gap:10px;
                }
                .pl-filter-body { padding:14px 16px; display:flex; flex-direction:column; gap:12px; }
                .pl-lbl { font-size:9.5px; font-weight:700; color:${MUT}; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:4px; }
                .pl-fi {
                    width:100%; height:34px; border-radius:8px; border:1px solid #E5E7EB;
                    font-size:12px; color:${DK}; background:#FAFBFC; padding:0 10px; outline:none;
                    transition:border-color .15s, box-shadow .15s;
                }
                .pl-fi:focus { border-color:${OR}; box-shadow:0 0 0 3px rgba(255,92,0,0.09); background:#fff; }

                /* filter action btns */
                .pl-apply-btn {
                    width:100%; height:36px; background:${OR}; color:#fff; border:none;
                    border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;
                    display:flex; align-items:center; justify-content:center; gap:6px;
                    transition:background .15s;
                }
                .pl-apply-btn:hover { background:#e05200; }
                .pl-reset-btn {
                    width:100%; height:34px; background:${WH}; color:${MUT}; border:1px solid #E5E7EB;
                    border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;
                    display:flex; align-items:center; justify-content:center; gap:5px;
                    transition:all .15s;
                }
                .pl-reset-btn:hover { border-color:${DK}; color:${DK}; }

                /* divider */
                .pl-divider { height:1px; background:${BOR}; margin:4px 0; }

                /* status list in left panel */
                .pl-status-list { display:flex; flex-direction:column; gap:6px; }
                .pl-status-row-item {
                    display:flex; align-items:center; gap:8px;
                    padding:7px 10px; border-radius:9px; cursor:default;
                    transition:background .12s;
                }
                .pl-status-row-item:hover { background:#FAFBFC; }

                /* ── Right: board ── */
                .pl-board {
                    display:grid;
                    grid-template-columns:repeat(3,1fr);
                    gap:12px;
                }
                @media(max-width:1200px){ .pl-board{ grid-template-columns:repeat(2,1fr); } }
                @media(max-width:700px) { .pl-board{ grid-template-columns:1fr; } }

                /* ── Column ── */
                .pl-col {
                    background:${WH}; border-radius:11px;
                    border:1px solid ${BOR};
                    display:flex; flex-direction:column;
                    transition:box-shadow .2s;
                }
                .pl-col-body {
                    overflow-y:auto; max-height:360px; min-height:70px;
                    padding:8px; display:flex; flex-direction:column; gap:7px;
                    transition:background .15s; border-radius:0 0 11px 11px;
                }
                .pl-col-body::-webkit-scrollbar { width:4px; }
                .pl-col-body::-webkit-scrollbar-track { background:#F0F2F5; border-radius:4px; margin:3px; }
                .pl-col-body::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .pl-col-body::-webkit-scrollbar-thumb:hover { background:#9CA3AF; }

                /* ── Lead card ── */
                .pl-card {
                    background:${WH}; border-radius:9px; padding:11px;
                    cursor:grab; user-select:none;
                    box-shadow:0 1px 3px rgba(0,0,0,0.07);
                    transition:box-shadow .18s, transform .18s;
                    display:flex; flex-direction:column;
                }
                .pl-card:hover { box-shadow:0 5px 18px rgba(0,0,0,0.10); transform:translateY(-2px); }
                .pl-card:active { cursor:grabbing; transform:scale(0.98); }

                /* ── View button ── */
                .pl-view-btn {
                    display:inline-flex; align-items:center; gap:3px;
                    font-size:10.5px; font-weight:600; padding:2px 8px;
                    background:#F3F4F6; color:${BDY}; border-radius:6px;
                    text-decoration:none; transition:background .15s, color .15s;
                }
                .pl-view-btn:hover { background:${OR}; color:#fff; }
            `}</style>

            <div className="pl-pg">

                {/* ── Full-width header ── */}
                <div className="pl-header">
                    <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                        <div style={{ width:3, height:22, borderRadius:2, background:OR, flexShrink:0 }}/>
                        <span style={{ fontSize:16, fontWeight:800, color:DK }}>Lead Pipeline</span>
                        <span style={{ background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                            fontSize:11, fontWeight:700, padding:'3px 11px', borderRadius:20 }}>
                            {totalLeads} leads
                        </span>
                    </div>
                    <Link href={urls.leads_index}
                        style={{ display:'inline-flex', alignItems:'center', gap:6,
                            padding:'7px 15px', borderRadius:9, fontSize:12.5, fontWeight:600,
                            background:DK, color:'#fff', textDecoration:'none',
                            transition:'background .15s' }}
                        onMouseEnter={e=>e.currentTarget.style.background=OR}
                        onMouseLeave={e=>e.currentTarget.style.background=DK}>
                        <LuLayoutList size={14}/> List View
                    </Link>
                </div>

                {/* ── Two-column: filter left + board right ── */}
                <div className="pl-layout">

                    {/* ── LEFT: filter + status summary ── */}
                    <div className="pl-left">
                        {/* Filter header */}
                        <div className="pl-left-head">
                            <div style={{ width:3, height:22, borderRadius:2, background:OR, flexShrink:0 }}/>
                            <div style={{ width:30, height:30, borderRadius:8, background:'#FFF7ED',
                                display:'flex', alignItems:'center', justifyContent:'center',
                                color:OR, flexShrink:0 }}>
                                <LuFilter size={15}/>
                            </div>
                            <div>
                                <div style={{ fontSize:13, fontWeight:700, color:DK }}>Filter</div>
                                <div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>Refine your leads</div>
                            </div>
                        </div>

                        {/* Filter inputs */}
                        <div className="pl-filter-body">
                            <div>
                                <label className="pl-lbl">Date Range</label>
                                <select value={dateRange} onChange={e=>setDateRange(e.target.value)} className="pl-fi">
                                    <option value="">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="7">Last 7 Days</option>
                                    <option value="30">Last 30 Days</option>
                                </select>
                            </div>
                            <div>
                                <label className="pl-lbl">Search</label>
                                <input type="text" placeholder="Name, phone or code…"
                                    value={search} onChange={e=>setSearch(e.target.value)}
                                    onKeyDown={e=>{ if(e.key==='Enter') applyFilters(e); }}
                                    className="pl-fi"/>
                            </div>
                            <button onClick={applyFilters} className="pl-apply-btn">
                                <LuFilter size={13}/> Apply Filters
                            </button>
                            <button onClick={resetFilters} className="pl-reset-btn">
                                <LuRotateCcw size={12}/> Reset
                            </button>

                            <div className="pl-divider"/>

                            {/* Status summary */}
                            <div>
                                <div style={{ fontSize:9.5, fontWeight:700, color:MUT,
                                    textTransform:'uppercase', letterSpacing:'.5px', marginBottom:8 }}>
                                    Pipeline Status
                                </div>
                                <div className="pl-status-list">
                                    {Object.entries(STATUS_CFG).map(([key,cfg])=>{
                                        const count=(columns[key]??[]).length;
                                        return (
                                            <div key={key} className="pl-status-row-item">
                                                <div style={{ width:26, height:26, borderRadius:7, flexShrink:0,
                                                    background:cfg.bg, display:'flex', alignItems:'center',
                                                    justifyContent:'center', color:cfg.color }}>
                                                    {cfg.icon}
                                                </div>
                                                <span style={{ flex:1, fontSize:12, fontWeight:600, color:BDY }}>
                                                    {cfg.label}
                                                </span>
                                                <span style={{ background:count>0?cfg.color:'#F3F4F6',
                                                    color:count>0?'#fff':MUT,
                                                    fontSize:10.5, fontWeight:800,
                                                    padding:'2px 8px', borderRadius:20,
                                                    minWidth:24, textAlign:'center', transition:'all .2s' }}>
                                                    {count}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* ── RIGHT: kanban board ── */}
                    <div className="pl-board">
                        {Object.entries(STATUS_CFG).map(([statusKey,cfg])=>(
                            <PipelineCol key={statusKey} statusKey={statusKey} cfg={cfg}
                                leads={columns[statusKey]??[]} urls={urls}
                                draggingLead={draggingLead}
                                onDragStart={handleDragStart} onDragEnd={handleDragEnd}
                                onDrop={handleDrop} onDragOver={handleDragOver}
                                onDragLeave={handleDragLeave}
                                isDragOver={dragOverCol===statusKey}/>
                        ))}
                    </div>

                </div>
            </div>

            {saving && (
                <div style={{ position:'fixed', inset:0, background:'rgba(255,255,255,.5)',
                    zIndex:9999, display:'flex', alignItems:'center', justifyContent:'center' }}>
                    <div style={{ background:WH, borderRadius:12, padding:'16px 24px',
                        boxShadow:'0 8px 32px rgba(0,0,0,0.12)',
                        display:'flex', alignItems:'center', gap:10 }}>
                        <div className="spinner-border spinner-border-sm" style={{ color:OR }}/>
                        <span style={{ fontSize:13, fontWeight:600, color:DK, fontFamily:'Poppins,sans-serif' }}>
                            Updating…
                        </span>
                    </div>
                </div>
            )}
            <Toast message={toast?.message} type={toast?.type}/>
        </>
    );
}
