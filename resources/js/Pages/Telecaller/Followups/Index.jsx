import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import {
    LuCalendar, LuClock, LuExternalLink, LuRefreshCw,
    LuPhone, LuDownload, LuCheck, LuCalendarCheck, LuCalendarClock,
} from 'react-icons/lu';
import { IoCheckmarkCircleOutline } from 'react-icons/io5';
import { MdOutlinePhoneInTalk } from 'react-icons/md';
import { BiError } from 'react-icons/bi';

const OR='#FF5C00', DK='#1D1D1D', WH='#FEFEFE', MUT='#9CA3AF', BOR='#F0F0F0', BDY='#374151';

const TABS = [
    { key:'today',     href:'/telecaller/followups/today',     label:'Today',     icon:<LuCalendar size={14}/>,          accent:OR,        desc:'Due today' },
    { key:'overdue',   href:'/telecaller/followups/overdue',   label:'Overdue',   icon:<BiError size={14}/>,             accent:'#EF4444', desc:'Past due'  },
    { key:'upcoming',  href:'/telecaller/followups/upcoming',  label:'Upcoming',  icon:<LuCalendarClock size={14}/>,     accent:'#10B981', desc:'Scheduled' },
    { key:'completed', href:'/telecaller/followups/completed', label:'Completed', icon:<IoCheckmarkCircleOutline size={14}/>, accent:'#6B7280', desc:'Done' },
];

const STATUS_STYLES = {
    today:     { bg:'#FFF7ED', color:OR,        dot:OR        },
    overdue:   { bg:'#FEF2F2', color:'#DC2626', dot:'#EF4444' },
    upcoming:  { bg:'#ECFDF5', color:'#16A34A', dot:'#10B981' },
    completed: { bg:'#F9FAFB', color:'#6B7280', dot:'#9CA3AF' },
};

const AVATAR_COLORS = [OR,'#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#EC4899'];
const avatarBg = n => AVATAR_COLORS[(n?.charCodeAt(0)??0) % AVATAR_COLORS.length];

// ─── Helpers ──────────────────────────────────────────────────────────────────
function Card({ children, style={} }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden', ...style }}>
            {children}
        </div>
    );
}

function StatusBadge({ label }) {
    const st = STATUS_STYLES[label] ?? { bg:'#F3F4F6', color:'#6B7280', dot:'#9CA3AF' };
    return (
        <span style={{ display:'inline-flex', alignItems:'center', gap:5,
            padding:'3px 10px', borderRadius:20, fontSize:11, fontWeight:700,
            background:st.bg, color:st.color, whiteSpace:'nowrap' }}>
            <span style={{ width:5, height:5, borderRadius:'50%', background:st.dot, flexShrink:0 }}/>
            {label ? label.charAt(0).toUpperCase()+label.slice(1) : '—'}
        </span>
    );
}

function DateChip({ dateFmt, timeFmt, statusLabel }) {
    const isOverdue   = statusLabel === 'overdue';
    const isToday     = statusLabel === 'today';
    const color = isOverdue ? '#DC2626' : isToday ? OR : '#374151';
    const bg    = isOverdue ? '#FEF2F2' : isToday ? '#FFF7ED' : '#F3F4F6';
    return (
        <div>
            <div style={{ display:'inline-flex', alignItems:'center', gap:5,
                fontSize:11.5, fontWeight:700, color, background:bg,
                padding:'3px 8px', borderRadius:6 }}>
                {isOverdue ? <BiError size={12}/> : <LuCalendar size={11}/>}
                {dateFmt || '—'}
            </div>
            {timeFmt && (
                <div style={{ fontSize:10, color:MUT, marginTop:2, display:'flex', alignItems:'center', gap:3 }}>
                    <LuClock size={9}/>{timeFmt}
                </div>
            )}
        </div>
    );
}

function CallBtn({ phone, leadId }) {
    const [st, setSt] = useState('idle');
    useEffect(()=>{
        const a=()=>setSt(p=>p==='calling'?'active':p);
        const b=()=>setSt('idle');
        document.addEventListener('gc:callAccepted',a);
        document.addEventListener('gc:callEnded',b);
        return()=>{document.removeEventListener('gc:callAccepted',a);document.removeEventListener('gc:callEnded',b);};
    },[]);
    async function dial(){
        if(!window.GC)return;
        if(st==='active'||st==='calling'){window.GC.endCall();return;}
        setSt('calling');
        try{await window.GC.startCall(phone,leadId??null);}catch(_){setSt('idle');}
    }
    const cfg={
        idle:   {bg:'#ECFDF5',color:'#16A34A',border:'#BBF7D0'},
        calling:{bg:'#FFFBEB',color:'#D97706',border:'#FDE68A'},
        active: {bg:'#FEF2F2',color:'#DC2626',border:'#FECACA'},
    };
    const c=cfg[st];
    return (
        <button type="button" onClick={dial} disabled={st==='calling'} title="Call"
            style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                background:c.bg, color:c.color, border:`1px solid ${c.border}`,
                display:'inline-flex', alignItems:'center', justifyContent:'center',
                cursor:st==='calling'?'not-allowed':'pointer', transition:'all .15s' }}>
            <MdOutlinePhoneInTalk size={15}/>
        </button>
    );
}

function IconBtn({ onClick, title, icon, hoverBg='#374151' }) {
    const [h,sh] = useState(false);
    return (
        <button type="button" onClick={onClick} title={title}
            style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                background:h?hoverBg:'#F3F4F6',
                color:h?'#fff':MUT,
                border:`1px solid ${h?hoverBg:'#E5E7EB'}`,
                display:'inline-flex', alignItems:'center', justifyContent:'center',
                cursor:'pointer', transition:'all .15s' }}
            onMouseEnter={()=>sh(true)} onMouseLeave={()=>sh(false)}>
            {icon}
        </button>
    );
}

function RescheduleModal({ modalRef, onSubmit, form }) {
    return (
        <div className="modal fade" id="rescheduleModal" tabIndex={-1} aria-hidden="true" ref={modalRef}>
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content" style={{ borderRadius:16, border:'none', boxShadow:'0 20px 60px rgba(0,0,0,0.18)' }}>
                    <div className="modal-header" style={{ background:DK, borderRadius:'16px 16px 0 0', border:'none' }}>
                        <h5 className="modal-title" style={{ color:'#fff', fontWeight:700, fontSize:14,
                            display:'flex', alignItems:'center', gap:8, fontFamily:'Poppins,sans-serif' }}>
                            <LuRefreshCw size={15} style={{ color:OR }}/> Reschedule Follow-up
                        </h5>
                        <button type="button" className="btn-close btn-close-white" data-bs-dismiss="modal"/>
                    </div>
                    <div className="modal-body" style={{ padding:22 }}>
                        {form.errors.next_followup && (
                            <div className="alert alert-danger py-2 mb-3" style={{ borderRadius:9, fontFamily:'Poppins,sans-serif', fontSize:13 }}>
                                {form.errors.next_followup}
                            </div>
                        )}
                        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:12, marginBottom:12 }}>
                            <div>
                                <label style={{ fontSize:10, fontWeight:700, color:MUT, display:'block',
                                    marginBottom:5, textTransform:'uppercase', letterSpacing:'.5px', fontFamily:'Poppins,sans-serif' }}>
                                    Next Date <span style={{ color:'#EF4444' }}>*</span>
                                </label>
                                <input type="date" className="form-control"
                                    style={{ borderRadius:9, border:'1.5px solid #E5E7EB', fontFamily:'Poppins,sans-serif', fontSize:13 }}
                                    value={form.data.next_followup} onChange={e=>form.setData('next_followup',e.target.value)} required/>
                            </div>
                            <div>
                                <label style={{ fontSize:10, fontWeight:700, color:MUT, display:'block',
                                    marginBottom:5, textTransform:'uppercase', letterSpacing:'.5px', fontFamily:'Poppins,sans-serif' }}>
                                    Time <span style={{ color:'#EF4444' }}>*</span>
                                </label>
                                <input type="time" className="form-control"
                                    style={{ borderRadius:9, border:'1.5px solid #E5E7EB', fontFamily:'Poppins,sans-serif', fontSize:13 }}
                                    value={form.data.followup_time} onChange={e=>form.setData('followup_time',e.target.value)} required/>
                            </div>
                        </div>
                        <div>
                            <label style={{ fontSize:10, fontWeight:700, color:MUT, display:'block',
                                marginBottom:5, textTransform:'uppercase', letterSpacing:'.5px', fontFamily:'Poppins,sans-serif' }}>
                                Remarks
                            </label>
                            <textarea className="form-control" rows={3}
                                style={{ borderRadius:9, border:'1.5px solid #E5E7EB', resize:'none', fontFamily:'Poppins,sans-serif', fontSize:13 }}
                                value={form.data.remarks} onChange={e=>form.setData('remarks',e.target.value)}/>
                        </div>
                    </div>
                    <div className="modal-footer" style={{ borderTop:`1px solid ${BOR}`, padding:'10px 18px' }}>
                        <button type="button" data-bs-dismiss="modal"
                            style={{ background:'#F3F4F6', color:BDY, border:'none', borderRadius:8,
                                fontWeight:600, padding:'7px 14px', fontSize:12.5, cursor:'pointer', fontFamily:'Poppins,sans-serif' }}>
                            Cancel
                        </button>
                        <button type="button" disabled={form.processing} onClick={onSubmit}
                            style={{ background:OR, color:'#fff', border:'none', borderRadius:8,
                                fontWeight:700, padding:'7px 18px', fontSize:12.5, cursor:'pointer',
                                opacity:form.processing?.5:1, fontFamily:'Poppins,sans-serif' }}>
                            {form.processing ? 'Saving…' : 'Save Changes'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ scope, title, followups, kpi }) {
    const modalRef     = useRef(null);
    const rescheduleId = useRef(null);
    const form = useForm({ next_followup:'', followup_time:'', remarks:'' });

    function openReschedule(item) {
        rescheduleId.current = item.id;
        form.setData({ next_followup:item.next_followup??'', followup_time:item.followup_time??'', remarks:item.remarks??'' });
        const el = modalRef.current;
        if (el && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(el).show();
    }
    function submitReschedule() {
        const id = rescheduleId.current; if (!id) return;
        const date=form.data.next_followup, time=form.data.followup_time;
        if (!date||!time) return;
        if (new Date(date+'T'+time) <= new Date()) { form.setError('next_followup','The scheduled date & time cannot be in the past.'); return; }
        form.post(`/telecaller/followups/${id}/reschedule`, {
            onSuccess:()=>{ const el=modalRef.current; if(el&&window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(el).hide(); }
        });
    }
    function markComplete(id) { router.post(`/telecaller/followups/${id}/complete`,{},{preserveScroll:true}); }

    const k = kpi ?? {};
    const activeTab = TABS.find(t=>t.key===scope) ?? TABS[0];
    const scopeColor = activeTab.accent;

    return (
        <>
            <Head title={title}/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .fu-pg, .fu-pg div, .fu-pg span:not([class*="material"]),
                .fu-pg p,.fu-pg label,.fu-pg button,.fu-pg input,.fu-pg select,
                .fu-pg a,.fu-pg td,.fu-pg th,.fu-pg small,.fu-pg textarea {
                    font-family:'Poppins',sans-serif !important;
                }
                .fu-pg { display:flex; flex-direction:column; gap:16px; }

                /* ── Two-column body ── */
                .fu-body {
                    display:grid;
                    grid-template-columns:220px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:860px){ .fu-body{ grid-template-columns:1fr; } }

                /* ── KPI cards stacked in left panel ── */
                .fu-kpi-stack { display:flex; flex-direction:column; gap:9px; }

                /* ── Table ── */
                .fu-tbl { width:100%; border-collapse:collapse; min-width:640px; }
                .fu-tbl thead th {
                    background:#FAFBFC; color:${MUT}; font-size:9.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.7px; padding:10px 14px;
                    border-bottom:2px solid ${BOR}; white-space:nowrap;
                    position:sticky; top:0; z-index:1;
                }
                .fu-tbl tbody td {
                    padding:12px 14px; vertical-align:middle; font-size:12.5px; color:${BDY};
                    border-bottom:1px solid #F9FAFB; transition:background .08s;
                }
                .fu-tbl tbody tr:last-child td { border-bottom:none; }
                .fu-tbl tbody tr:hover td { background:#FAFBFC; }
                .fu-tbl tbody tr:hover td:first-child {
                    border-left:3px solid var(--scope-color,${OR}); padding-left:11px;
                }

                /* ── Scrollable wrapper ── */
                .fu-scroll { overflow-y:scroll; overflow-x:auto; max-height:480px; }
                .fu-scroll::-webkit-scrollbar { width:5px; }
                .fu-scroll::-webkit-scrollbar-track { background:#F3F4F6; border-radius:4px; }
                .fu-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .fu-scroll::-webkit-scrollbar-thumb:hover { background:${OR}; }
                .fu-scroll::-webkit-scrollbar:horizontal { height:4px; }
                .fu-scroll::-webkit-scrollbar-thumb:horizontal { background:#D1D5DB; border-radius:4px; }

                /* ── Pagination ── */
                .fu-pager {
                    padding:11px 18px; border-top:1px solid ${BOR};
                    display:flex; align-items:center; justify-content:space-between;
                    flex-wrap:wrap; gap:9px; background:#FAFBFC;
                }
                .fu-pager .page-link { background:${WH}; border-color:#E5E7EB; color:${BDY}; font-size:12px; border-radius:7px; }
                .fu-pager .page-item.active .page-link { background:var(--scope-color,${OR}); border-color:var(--scope-color,${OR}); color:#fff; }
                .fu-pager .page-item.disabled .page-link { opacity:.4; }

                /* ── Export menu ── */
                .fu-exp .dropdown-item { font-size:12.5px; padding:8px 13px; display:flex; align-items:center; gap:7px; color:${BDY}; }
                .fu-exp .dropdown-item:hover { background:${DK}; color:#fff; }

                /* ── Hover row fix ── */
                :root { --scope-color: ${scopeColor}; }
            `}</style>

            <div className="fu-pg">

                {/* ── Scope header card (colored per tab) ── */}
                <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
                    boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden' }}>
                    {/* Top accent stripe */}
                    <div style={{ height:4, background:`linear-gradient(90deg,${scopeColor},${scopeColor}88)` }}/>
                    <div style={{ padding:'16px 20px 14px' }}>
                        {/* Title row */}
                        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
                            gap:12, marginBottom:14, flexWrap:'wrap' }}>
                            <div style={{ display:'flex', alignItems:'center', gap:12 }}>
                                <div style={{ width:44, height:44, borderRadius:12, flexShrink:0,
                                    background:`${scopeColor}15`,
                                    display:'flex', alignItems:'center', justifyContent:'center',
                                    color:scopeColor }}>
                                    {scope==='today'     && <LuCalendar size={22}/>}
                                    {scope==='overdue'   && <BiError size={22}/>}
                                    {scope==='upcoming'  && <LuCalendarClock size={22}/>}
                                    {scope==='completed' && <IoCheckmarkCircleOutline size={22}/>}
                                </div>
                                <div>
                                    <div style={{ fontSize:17, fontWeight:800, color:DK }}>{title}</div>
                                    <div style={{ fontSize:12, color:MUT, marginTop:2 }}>
                                        {scope==='today'    ?'Follow-ups scheduled for today':
                                         scope==='overdue'  ?'Missed follow-ups that need attention':
                                         scope==='upcoming' ?'Upcoming scheduled follow-ups':
                                                             'Completed follow-up calls'}
                                    </div>
                                </div>
                            </div>
                            <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                                <span style={{ background:`${scopeColor}15`, color:scopeColor,
                                    fontSize:12, fontWeight:700, padding:'4px 12px', borderRadius:20 }}>
                                    {followups.total} records
                                </span>
                                {/* Export */}
                                <div className="dropdown">
                                    <button type="button" data-bs-toggle="dropdown"
                                        style={{ background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                                            borderRadius:8, padding:'7px 13px', fontSize:12, fontWeight:600,
                                            display:'inline-flex', alignItems:'center', gap:5, cursor:'pointer' }}>
                                        <LuDownload size={13}/> Export
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow-sm fu-exp"
                                        style={{ borderRadius:10, border:'1px solid #E5E7EB', padding:5, minWidth:155 }}>
                                        <li><a className="dropdown-item" href={`/telecaller/followups/${scope}/export?format=excel`}
                                            target="_blank" rel="noreferrer">📊 Excel (.xlsx)</a></li>
                                        <li><a className="dropdown-item" href={`/telecaller/followups/${scope}/export?format=pdf`}
                                            target="_blank" rel="noreferrer">📄 PDF</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {/* ── Two-column: KPI left + table right ── */}
                <div className="fu-body">

                    {/* LEFT — stacked KPI cards */}
                    <div className="fu-kpi-stack">
                        {TABS.map(t => {
                            const cnt = k[t.key] ?? 0;
                            const on  = scope === t.key;
                            return (
                                <Link key={t.key} href={t.href} style={{ textDecoration:'none' }}>
                                    <div style={{ background:on?t.accent:WH, borderRadius:12,
                                        border:on?'none':`1px solid ${BOR}`,
                                        boxShadow:on?`0 4px 16px ${t.accent}28`:'0 1px 4px rgba(0,0,0,0.04)',
                                        padding:'13px 16px', display:'flex', alignItems:'center', gap:11,
                                        transition:'all .15s', cursor:'pointer' }}>
                                        <div style={{ width:36, height:36, borderRadius:9, flexShrink:0,
                                            background:on?'rgba(255,255,255,0.18)':`${t.accent}15`,
                                            display:'flex', alignItems:'center', justifyContent:'center',
                                            color:on?'#fff':t.accent }}>{t.icon}</div>
                                        <div style={{ flex:1, minWidth:0 }}>
                                            <div style={{ fontSize:22, fontWeight:800, lineHeight:1,
                                                color:on?'#fff':DK }}>{cnt}</div>
                                            <div style={{ fontSize:10, fontWeight:600, marginTop:2,
                                                color:on?'rgba(255,255,255,0.75)':MUT,
                                                textTransform:'uppercase', letterSpacing:'.4px' }}>{t.label}</div>
                                            <div style={{ fontSize:9.5, color:on?'rgba(255,255,255,0.5)':MUT, marginTop:1 }}>
                                                {t.desc}
                                            </div>
                                        </div>
                                        {on && (
                                            <div style={{ width:6, height:6, borderRadius:'50%',
                                                background:'rgba(255,255,255,0.6)', flexShrink:0 }}/>
                                        )}
                                    </div>
                                </Link>
                            );
                        })}
                    </div>

                    {/* RIGHT — table card */}
                    <Card>
                    {/* Colored top border per scope */}
                    <div style={{ height:2, background:`linear-gradient(90deg,${scopeColor},${scopeColor}55)` }}/>

                    <div className="fu-scroll">
                        <table className="fu-tbl">
                            <thead>
                                <tr>
                                    <th style={{ width:38 }}>#</th>
                                    <th>Lead</th>
                                    <th>Date &amp; Time</th>
                                    <th>Phone</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                    <th style={{ width:148 }}>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {followups.data.length === 0
                                    ? <tr><td colSpan={7}>
                                        <div style={{ textAlign:'center', padding:'60px 0' }}>
                                            <div style={{ width:64, height:64, borderRadius:16,
                                                background:`${scopeColor}12`,
                                                display:'flex', alignItems:'center', justifyContent:'center',
                                                margin:'0 auto 16px', color:scopeColor, fontSize:28 }}>
                                                {scope==='overdue'?<BiError size={32}/>:<LuCalendarCheck size={32}/>}
                                            </div>
                                            <div style={{ fontSize:15, fontWeight:700, color:DK, marginBottom:8 }}>
                                                {scope==='overdue'?'No overdue follow-ups':'All clear!'}
                                            </div>
                                            <div style={{ fontSize:12.5, color:MUT, maxWidth:280, margin:'0 auto', lineHeight:1.7 }}>
                                                {scope==='overdue'
                                                    ? "Great job — you're up to date on all follow-ups."
                                                    : 'No follow-ups found in this view.'}
                                            </div>
                                        </div>
                                      </td></tr>
                                    : followups.data.map((item, idx) => {
                                        const sno = (followups.current_page-1)*followups.per_page+idx+1;
                                        const bg  = avatarBg(item.lead_name);
                                        return (
                                            <tr key={item.id}>
                                                <td style={{ color:MUT, fontSize:11, fontWeight:600 }}>{sno}</td>

                                                {/* Lead with avatar */}
                                                <td>
                                                    <div style={{ display:'flex', alignItems:'center', gap:9 }}>
                                                        <div style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                                                            background:`linear-gradient(135deg,${bg},${bg}bb)`,
                                                            display:'flex', alignItems:'center', justifyContent:'center',
                                                            color:'#fff', fontSize:13, fontWeight:800 }}>
                                                            {(item.lead_name||'?')[0].toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <div style={{ fontWeight:700, color:DK, fontSize:12.5, lineHeight:1.2 }}>
                                                                {item.lead_name||'—'}
                                                            </div>
                                                            <div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>
                                                                {item.lead_code||'—'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Colored date chip */}
                                                <td>
                                                    <DateChip
                                                        dateFmt={item.next_followup_fmt}
                                                        timeFmt={item.followup_time_fmt}
                                                        statusLabel={item.status_label}/>
                                                </td>

                                                {/* Phone */}
                                                <td>
                                                    {item.lead_phone
                                                        ? <div style={{ display:'flex', alignItems:'center', gap:4, fontSize:12, color:BDY }}>
                                                            <LuPhone size={10} style={{ color:MUT }}/>{item.lead_phone}
                                                          </div>
                                                        : <span style={{ color:MUT }}>—</span>}
                                                </td>

                                                {/* Remarks */}
                                                <td>
                                                    <div style={{ maxWidth:180, overflow:'hidden', textOverflow:'ellipsis',
                                                        whiteSpace:'nowrap', fontSize:12, color:MUT }}>
                                                        {item.remarks || <span style={{ color:'#E5E7EB' }}>No remarks</span>}
                                                    </div>
                                                </td>

                                                {/* Status */}
                                                <td><StatusBadge label={item.status_label}/></td>

                                                {/* Actions */}
                                                <td>
                                                    <div style={{ display:'flex', gap:5, alignItems:'center' }}>
                                                        {item.lead_phone && <CallBtn phone={item.lead_phone} leadId={item.lead_id}/>}
                                                        {item.encrypted_lead_id && (
                                                            <Link href={`/telecaller/leads/${item.encrypted_lead_id}`}
                                                                style={{ width:32, height:32, borderRadius:9,
                                                                    background:'#F3F4F6', color:MUT,
                                                                    border:'1px solid #E5E7EB',
                                                                    display:'inline-flex', alignItems:'center',
                                                                    justifyContent:'center', textDecoration:'none',
                                                                    transition:'all .15s' }}
                                                                onMouseEnter={e=>{e.currentTarget.style.background=DK;e.currentTarget.style.color='#fff';e.currentTarget.style.borderColor=DK;}}
                                                                onMouseLeave={e=>{e.currentTarget.style.background='#F3F4F6';e.currentTarget.style.color=MUT;e.currentTarget.style.borderColor='#E5E7EB';}}>
                                                                <LuExternalLink size={13}/>
                                                            </Link>
                                                        )}
                                                        {!item.is_completed && <>
                                                            <IconBtn
                                                                onClick={()=>openReschedule(item)}
                                                                title="Reschedule"
                                                                icon={<LuRefreshCw size={13}/>}
                                                                hoverBg="#F59E0B"/>
                                                            <IconBtn
                                                                onClick={()=>markComplete(item.id)}
                                                                title="Mark complete"
                                                                icon={<LuCheck size={13}/>}
                                                                hoverBg="#10B981"/>
                                                        </>}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="fu-pager">
                        <small style={{ color:MUT }}>
                            Showing {followups.from??0}–{followups.to??0} of {followups.total}
                        </small>
                        {followups.last_page > 1 && (
                            <nav>
                                <ul className="pagination pagination-sm mb-0" style={{ gap:3 }}>
                                    {followups.links.map((link,i)=>(
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

                </div>{/* end fu-body */}

            </div>

            <RescheduleModal modalRef={modalRef} form={form} onSubmit={submitReschedule}/>
        </>
    );
}
