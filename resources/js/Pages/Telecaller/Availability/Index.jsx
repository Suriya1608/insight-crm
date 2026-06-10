import { Head, router } from '@inertiajs/react';
import { useState, useRef, useEffect } from 'react';
import {
    LuChevronLeft, LuChevronRight, LuCalendar,
    LuShieldOff, LuMousePointer2, LuCalendarCheck,
} from 'react-icons/lu';
import { MdOutlineEventBusy, MdOutlineEventAvailable } from 'react-icons/md';

const OR='#FF5C00', DK='#1D1D1D', WH='#FEFEFE', MUT='#9CA3AF', BOR='#F0F0F0';

const MONTH_NAMES = ['January','February','March','April','May','June',
    'July','August','September','October','November','December'];
const DOW = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

function buildCalendar(year, month) {
    const first=new Date(year,month-1,1), last=new Date(year,month,0), days=[];
    for(let i=0;i<first.getDay();i++) days.push(null);
    for(let d=1;d<=last.getDate();d++) days.push(`${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
    return days;
}
function fmtDate(date)  { return new Date(date+'T00:00:00').toLocaleDateString('en-IN',{weekday:'short',day:'numeric',month:'short'}); }
function fmtShort(date) { return new Date(date+'T00:00:00').toLocaleDateString('en-IN',{day:'numeric',month:'short',weekday:'short'}); }

export default function AvailabilityIndex({ blocked_dates, year, month, today, urls }) {
    const [blockedMap,  setBlockedMap]  = useState(()=>{ const m={}; blocked_dates.forEach(b=>{m[b.date]=b.reason??'';}); return m; });
    const [selectedSet, setSelectedSet] = useState(new Set());
    const [reason,      setReason]      = useState('');
    const [submitting,  setSubmitting]  = useState(false);
    const isDragging = useRef(false);
    const dragMode   = useRef('select');
    const days = buildCalendar(year, month);

    useEffect(()=>{
        const stop=()=>{ isDragging.current=false; };
        window.addEventListener('mouseup',stop);
        return ()=>window.removeEventListener('mouseup',stop);
    },[]);

    function prevMonth(){ let y=year,m=month-1; if(m<1){m=12;y--;} router.get(window.location.pathname,{year:y,month:m},{preserveState:false}); }
    function nextMonth(){ let y=year,m=month+1; if(m>12){m=1;y++;} router.get(window.location.pathname,{year:y,month:m},{preserveState:false}); }

    function handleMouseDown(date) {
        if(!date||date<today||blockedMap[date]!==undefined) return;
        isDragging.current=true;
        const already=selectedSet.has(date);
        dragMode.current=already?'deselect':'select';
        setSelectedSet(prev=>{ const n=new Set(prev); already?n.delete(date):n.add(date); return n; });
    }
    function handleMouseEnter(date) {
        if(!isDragging.current||!date||date<today||blockedMap[date]!==undefined) return;
        setSelectedSet(prev=>{ const n=new Set(prev); dragMode.current==='select'?n.add(date):n.delete(date); return n; });
    }
    function unblockDate(date) {
        if(!confirm('Unblock '+fmtDate(date)+'?')) return;
        setSubmitting(true);
        router.delete(urls.destroy.replace('__DATE__',date),{
            onSuccess:()=>setBlockedMap(prev=>{const n={...prev};delete n[date];return n;}),
            onFinish:()=>setSubmitting(false),
        });
    }
    function submitBlock(e) {
        e.preventDefault();
        const dates=[...selectedSet].sort(); if(!dates.length) return;
        setSubmitting(true);
        router.post(urls.store,{dates,reason},{
            onSuccess:()=>{ const x={}; dates.forEach(d=>{x[d]=reason;}); setBlockedMap(p=>({...p,...x})); setSelectedSet(new Set()); setReason(''); },
            onFinish:()=>setSubmitting(false),
        });
    }
    function clearSelection(){ setSelectedSet(new Set()); setReason(''); }

    function getDayState(date) {
        if(!date)                        return 'empty';
        if(date<today)                   return 'past';
        if(blockedMap[date]!==undefined) return 'blocked';
        if(selectedSet.has(date))        return 'selected';
        if(date===today)                 return 'today';
        return 'future';
    }

    const selCount     = selectedSet.size;
    const blockedCount = Object.keys(blockedMap).length;
    const totalDays    = new Date(year, month, 0).getDate();
    const availableDays = totalDays - blockedCount;

    const DAY_STYLES = {
        empty:   { background:'transparent', border:'none' },
        past:    { color:'#D1D5DB', cursor:'default', background:'transparent' },
        blocked: { background:'#FEF2F2', color:'#EF4444', fontWeight:700, cursor:'pointer' },
        selected:{ background:OR, color:'#fff', fontWeight:700, cursor:'pointer', boxShadow:`0 3px 10px rgba(255,92,0,0.35)` },
        today:   { background:'#FFF7ED', color:OR, border:`2px solid #FED7AA`, fontWeight:700, cursor:'pointer' },
        future:  { background:'transparent', color:'#374151', cursor:'pointer' },
    };

    return (
        <>
            <Head title="My Availability"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .av-pg, .av-pg div, .av-pg span:not([class*="material"]),
                .av-pg p,.av-pg h1,.av-pg h2,.av-pg label,.av-pg button,.av-pg input,.av-pg a,.av-pg li,.av-pg small {
                    font-family:'Poppins',sans-serif !important; box-sizing:border-box;
                }
                .av-pg { display:flex; flex-direction:column; gap:16px; }

                /* ── Stat cards row ── */
                .av-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
                @media(max-width:640px){ .av-stats{ grid-template-columns:1fr 1fr; } }

                /* ── Main two-column grid ── */
                .av-main { display:grid; grid-template-columns:1fr 300px; gap:16px; align-items:start; }
                @media(max-width:860px){ .av-main{ grid-template-columns:1fr; } }

                /* ── Calendar nav buttons ── */
                .av-nav-btn {
                    width:34px; height:34px; border-radius:9px; display:flex;
                    align-items:center; justify-content:center; cursor:pointer;
                    background:#FFF7ED; border:1px solid #FED7AA; color:${OR};
                    transition:all .15s; flex-shrink:0;
                }
                .av-nav-btn:hover { background:${OR}; border-color:${OR}; color:#fff; box-shadow:0 3px 10px rgba(255,92,0,0.28); }

                /* ── Day cells ── */
                .av-day {
                    height:52px; display:flex; align-items:center; justify-content:center;
                    font-size:13.5px; font-weight:500; border-radius:10px; position:relative;
                    transition:all .14s ease; user-select:none; border:1px solid transparent;
                }
                .av-day:hover:not(.av-day--past):not(.av-day--empty) { transform:scale(1.1); z-index:2; }
                .av-day--future:hover { background:#FFF7ED !important; color:${OR} !important; }
                .av-day--blocked:hover { background:#FEE2E2 !important; box-shadow:0 3px 10px rgba(239,68,68,0.2) !important; }
                .av-day--selected { animation:av-pop .18s ease; }
                @keyframes av-pop { 0%{transform:scale(0.88)} 60%{transform:scale(1.07)} 100%{transform:scale(1)} }

                /* ── Chip ── */
                .av-chip {
                    display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:600;
                    background:#FFF7ED; color:${OR}; border-radius:20px; padding:2px 8px;
                }
                .av-chip-x { cursor:pointer; font-size:13px; color:${MUT}; line-height:1; }
                .av-chip-x:hover { color:#EF4444; }

                /* ── Input ── */
                .av-input {
                    width:100%; padding:8px 10px; border-radius:8px; border:1px solid #E5E7EB;
                    font-size:12.5px; outline:none; color:${DK}; background:#FAFBFC;
                    transition:border-color .15s, box-shadow .15s;
                }
                .av-input:focus { border-color:${OR}; box-shadow:0 0 0 3px rgba(255,92,0,0.09); background:#fff; }
                .av-input::placeholder { color:${MUT}; }

                /* ── Blocked list scrollbar ── */
                .av-blocked-list { max-height:220px; overflow-y:auto; }
                .av-blocked-list::-webkit-scrollbar { width:4px; }
                .av-blocked-list::-webkit-scrollbar-track { background:#F3F4F6; border-radius:4px; }
                .av-blocked-list::-webkit-scrollbar-thumb { background:#FECACA; border-radius:4px; }
                .av-blocked-list::-webkit-scrollbar-thumb:hover { background:#EF4444; }

                /* ── Chip scroll ── */
                .av-chip-scroll { max-height:90px; overflow-y:auto; display:flex; flex-wrap:wrap; gap:5px; }
                .av-chip-scroll::-webkit-scrollbar { height:3px; width:3px; }
                .av-chip-scroll::-webkit-scrollbar-thumb { background:#FED7AA; border-radius:3px; }
            `}</style>

            <div className="av-pg">

                {/* ── Page header ── */}
                <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
                    boxShadow:'0 2px 8px rgba(0,0,0,0.04)', padding:'16px 20px',
                    display:'flex', alignItems:'center', gap:14 }}>
                    <div style={{ width:44, height:44, borderRadius:12, flexShrink:0, background:DK,
                        display:'flex', alignItems:'center', justifyContent:'center',
                        boxShadow:'0 4px 14px rgba(0,0,0,0.18)' }}>
                        <LuCalendar size={22} style={{ color:OR }}/>
                    </div>
                    <div>
                        <div style={{ fontSize:17, fontWeight:800, color:DK }}>My Availability Calendar</div>
                        <div style={{ fontSize:12, color:MUT, marginTop:2 }}>
                            Click or drag across future dates to select, then block them all at once.
                        </div>
                    </div>
                </div>

                {/* ── Stats row ── */}
                <div className="av-stats">
                    {[
                        { icon:<MdOutlineEventAvailable size={20}/>, label:'Available Days',  value:availableDays, color:'#10B981', bg:'#ECFDF5' },
                        { icon:<MdOutlineEventBusy size={20}/>,      label:'Blocked This Month', value:blockedCount, color:'#EF4444', bg:'#FEF2F2' },
                        { icon:<LuMousePointer2 size={20}/>,         label:'Selected',        value:selCount,     color:OR,        bg:'#FFF7ED' },
                    ].map(s=>(
                        <div key={s.label} style={{ background:WH, borderRadius:12, border:`1px solid ${BOR}`,
                            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', padding:'14px 16px',
                            display:'flex', alignItems:'center', gap:12 }}>
                            <div style={{ width:38, height:38, borderRadius:10, background:s.bg,
                                display:'flex', alignItems:'center', justifyContent:'center',
                                color:s.color, flexShrink:0 }}>{s.icon}</div>
                            <div>
                                <div style={{ fontSize:24, fontWeight:800, color:DK, lineHeight:1 }}>{s.value}</div>
                                <div style={{ fontSize:10, fontWeight:600, color:MUT,
                                    textTransform:'uppercase', letterSpacing:'.5px', marginTop:2 }}>{s.label}</div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* ── Main: Calendar + Panel ── */}
                <div className="av-main">

                    {/* ── LEFT: Calendar card ── */}
                    <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
                        boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden', userSelect:'none' }}>

                        {/* Month navigation — clean white, orange accents */}
                        <div style={{ padding:'16px 20px 14px', borderBottom:`1px solid ${BOR}`,
                            display:'flex', alignItems:'center', justifyContent:'space-between' }}>
                            <button className="av-nav-btn" onClick={prevMonth}>
                                <LuChevronLeft size={16}/>
                            </button>
                            <div style={{ textAlign:'center' }}>
                                <div style={{ fontWeight:800, color:DK, fontSize:18 }}>{MONTH_NAMES[month-1]}</div>
                                <div style={{ color:MUT, fontSize:12, fontWeight:600, marginTop:1 }}>{year}</div>
                            </div>
                            <button className="av-nav-btn" onClick={nextMonth}>
                                <LuChevronRight size={16}/>
                            </button>
                        </div>

                        <div style={{ padding:'16px 20px 20px' }}>
                            {/* DOW headers */}
                            <div style={{ display:'grid', gridTemplateColumns:'repeat(7,1fr)',
                                gap:5, marginBottom:8 }}>
                                {DOW.map((d,i)=>(
                                    <div key={d} style={{ textAlign:'center', fontSize:11, fontWeight:700,
                                        color:(i===0||i===6)?'#FCA5A5':MUT, letterSpacing:'.4px',
                                        padding:'6px 0', background:'#FAFBFC', borderRadius:7 }}>
                                        {d}
                                    </div>
                                ))}
                            </div>

                            {/* Day grid */}
                            <div style={{ display:'grid', gridTemplateColumns:'repeat(7,1fr)', gap:5 }}>
                                {days.map((date,i)=>{
                                    const state = getDayState(date);
                                    const dow   = date ? new Date(date+'T00:00:00').getDay() : null;
                                    const weekend = dow===0||dow===6;
                                    const st    = DAY_STYLES[state]||{};
                                    const extra = state==='future'&&weekend ? {color:'#FCA5A5'} : {};
                                    return (
                                        <div key={i}
                                            className={`av-day av-day--${state}${state==='selected'?' av-day--selected':''}`}
                                            style={{...st,...extra}}
                                            onMouseDown={()=>handleMouseDown(date)}
                                            onMouseEnter={()=>handleMouseEnter(date)}
                                            onClick={()=>{ if(date&&date>=today&&blockedMap[date]!==undefined) unblockDate(date); }}>
                                            {date ? parseInt(date.slice(-2)) : ''}
                                            {/* Blocked dot */}
                                            {date&&blockedMap[date]!==undefined&&(
                                                <span style={{ position:'absolute', bottom:5, left:'50%',
                                                    transform:'translateX(-50%)',
                                                    width:4, height:4, borderRadius:'50%', background:'#EF4444' }}/>
                                            )}
                                            {/* Today ring */}
                                            {state==='today'&&(
                                                <span style={{ position:'absolute', inset:-1, borderRadius:10,
                                                    border:`2px solid ${OR}`, opacity:.35 }}/>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Legend */}
                            <div style={{ display:'flex', flexWrap:'wrap', gap:14, marginTop:16,
                                paddingTop:14, borderTop:`1px solid ${BOR}`, alignItems:'center' }}>
                                {[
                                    { bg:'#FEF2F2',     border:'#FECACA', label:'Blocked'  },
                                    { bg:'#FFF7ED',     border:'#FED7AA', label:'Today'    },
                                    { bg:OR,            border:OR,        label:'Selected' },
                                    { bg:'transparent', border:'#D1D5DB', label:'Available'},
                                ].map(l=>(
                                    <div key={l.label} style={{ display:'flex', alignItems:'center', gap:6 }}>
                                        <div style={{ width:16, height:16, borderRadius:5, flexShrink:0,
                                            background:l.bg, border:`2px solid ${l.border}` }}/>
                                        <span style={{ fontSize:11.5, color:MUT, fontWeight:500 }}>{l.label}</span>
                                    </div>
                                ))}
                                <span style={{ marginLeft:'auto', fontSize:11, color:'#D1D5DB', fontStyle:'italic' }}>
                                    Tap blocked → unblock
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* ── RIGHT: Action panel ── */}
                    <div style={{ display:'flex', flexDirection:'column', gap:14 }}>

                        {/* Block form / empty state */}
                        {selCount > 0 ? (
                            <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
                                boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden' }}>
                                {/* Section head */}
                                <div style={{ padding:'13px 16px', borderBottom:`1px solid ${BOR}`,
                                    background:'linear-gradient(135deg,#FAFBFC,#FFFFFF)',
                                    display:'flex', alignItems:'center', gap:10 }}>
                                    <div style={{ width:3, height:32, borderRadius:2, background:'#EF4444', flexShrink:0 }}/>
                                    <div style={{ flex:1 }}>
                                        <div style={{ fontSize:13, fontWeight:700, color:DK }}>
                                            Block Dates
                                        </div>
                                        <div style={{ fontSize:11, color:MUT, marginTop:1 }}>
                                            {selCount} date{selCount>1?'s':''} selected
                                        </div>
                                    </div>
                                    <span style={{ background:'#FEF2F2', color:'#EF4444',
                                        border:'1px solid #FECACA',
                                        fontSize:12, fontWeight:800, padding:'3px 10px', borderRadius:20 }}>
                                        {selCount}
                                    </span>
                                </div>

                                <div style={{ padding:'14px 16px' }}>
                                    {/* Selected chips */}
                                    <div className="av-chip-scroll" style={{ marginBottom:12 }}>
                                        {[...selectedSet].sort().map(d=>(
                                            <span key={d} className="av-chip">
                                                {fmtDate(d)}
                                                <span className="av-chip-x"
                                                    onClick={()=>setSelectedSet(p=>{const n=new Set(p);n.delete(d);return n;})}>×</span>
                                            </span>
                                        ))}
                                    </div>
                                    <form onSubmit={submitBlock}>
                                        <label style={{ fontSize:9.5, fontWeight:700, color:MUT,
                                            textTransform:'uppercase', letterSpacing:'.5px',
                                            display:'block', marginBottom:5 }}>
                                            Reason (optional)
                                        </label>
                                        <input type="text" className="av-input"
                                            value={reason} onChange={e=>setReason(e.target.value)}
                                            placeholder="e.g. Medical leave" maxLength={191}
                                            style={{ marginBottom:12 }}/>
                                        <div style={{ display:'flex', gap:7 }}>
                                            <button type="submit" disabled={submitting}
                                                style={{ flex:1, padding:'9px 0', borderRadius:9,
                                                    background:'linear-gradient(135deg,#EF4444,#DC2626)',
                                                    color:'#fff', border:'none', fontWeight:700, fontSize:13,
                                                    cursor:submitting?'not-allowed':'pointer',
                                                    opacity:submitting?.6:1, transition:'all .15s',
                                                    boxShadow:'0 3px 10px rgba(239,68,68,0.28)' }}>
                                                {submitting?'Saving…':`Block ${selCount>1?selCount+' Dates':'Date'}`}
                                            </button>
                                            <button type="button" onClick={clearSelection}
                                                style={{ padding:'9px 14px', borderRadius:9,
                                                    background:'#F3F4F6', color:MUT,
                                                    border:`1px solid ${BOR}`, fontWeight:600,
                                                    fontSize:13, cursor:'pointer', transition:'all .15s' }}>
                                                Clear
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        ) : (
                            <div style={{ background:WH, borderRadius:14, border:`2px dashed ${BOR}`,
                                padding:'28px 20px', textAlign:'center' }}>
                                <div style={{ width:52, height:52, borderRadius:14, background:'#FFF7ED',
                                    display:'flex', alignItems:'center', justifyContent:'center',
                                    margin:'0 auto 12px' }}>
                                    <LuMousePointer2 size={26} style={{ color:OR }}/>
                                </div>
                                <div style={{ fontSize:13, fontWeight:700, color:DK, marginBottom:4 }}>
                                    Select dates to block
                                </div>
                                <div style={{ fontSize:11.5, color:MUT, lineHeight:1.7 }}>
                                    Click or drag across future dates<br/>on the calendar
                                </div>
                            </div>
                        )}

                        {/* Blocked dates list */}
                        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
                            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden' }}>
                            {/* Head */}
                            <div style={{ padding:'12px 16px', borderBottom:`1px solid ${BOR}`,
                                background:'linear-gradient(135deg,#FAFBFC,#FFFFFF)',
                                display:'flex', alignItems:'center', gap:10 }}>
                                <div style={{ width:3, height:28, borderRadius:2, background:'#EF4444', flexShrink:0 }}/>
                                <div style={{ display:'flex', alignItems:'center', gap:7 }}>
                                    <MdOutlineEventBusy size={16} style={{ color:'#EF4444' }}/>
                                    <span style={{ fontSize:13, fontWeight:700, color:DK }}>
                                        Blocked This Month
                                    </span>
                                </div>
                                {blockedCount>0&&(
                                    <span style={{ marginLeft:'auto', background:'#FEF2F2', color:'#EF4444',
                                        border:'1px solid #FECACA', fontSize:11, fontWeight:700,
                                        padding:'2px 9px', borderRadius:20 }}>
                                        {blockedCount}
                                    </span>
                                )}
                            </div>

                            <div style={{ padding:'10px 14px' }}>
                                {blockedCount===0
                                    ? <div style={{ textAlign:'center', padding:'18px 0' }}>
                                        <div style={{ width:40, height:40, borderRadius:10, background:'#ECFDF5',
                                            display:'flex', alignItems:'center', justifyContent:'center',
                                            margin:'0 auto 10px' }}>
                                            <LuCalendarCheck size={20} style={{ color:'#10B981' }}/>
                                        </div>
                                        <div style={{ fontSize:12.5, fontWeight:600, color:DK, marginBottom:3 }}>
                                            All days available
                                        </div>
                                        <div style={{ fontSize:11, color:MUT }}>No blocked dates this month</div>
                                      </div>
                                    : <ul className="av-blocked-list"
                                          style={{ margin:0, padding:0, listStyle:'none',
                                              display:'flex', flexDirection:'column', gap:6 }}>
                                        {Object.entries(blockedMap).sort().map(([date,rsn])=>(
                                            <li key={date} style={{ display:'flex', alignItems:'center',
                                                justifyContent:'space-between', gap:8,
                                                padding:'8px 10px', background:'#FEF2F2',
                                                borderRadius:9 }}>
                                                <div style={{ display:'flex', alignItems:'flex-start', gap:7, flex:1, minWidth:0 }}>
                                                    <MdOutlineEventBusy size={13}
                                                        style={{ color:'#F87171', marginTop:1, flexShrink:0 }}/>
                                                    <div style={{ minWidth:0 }}>
                                                        <div style={{ fontSize:11.5, fontWeight:700, color:'#EF4444' }}>
                                                            {fmtShort(date)}
                                                        </div>
                                                        {rsn&&(
                                                            <div style={{ fontSize:10.5, color:MUT, marginTop:1,
                                                                whiteSpace:'nowrap', overflow:'hidden',
                                                                textOverflow:'ellipsis' }}>{rsn}</div>
                                                        )}
                                                    </div>
                                                </div>
                                                <button disabled={submitting} onClick={()=>unblockDate(date)}
                                                    title="Unblock"
                                                    style={{ background:'none', border:'1px solid #FECACA',
                                                        borderRadius:7, width:26, height:26,
                                                        display:'flex', alignItems:'center', justifyContent:'center',
                                                        cursor:'pointer', color:'#FECACA',
                                                        fontSize:15, lineHeight:1, flexShrink:0,
                                                        transition:'all .15s' }}
                                                    onMouseEnter={e=>{ e.currentTarget.style.background='#EF4444'; e.currentTarget.style.color='#fff'; e.currentTarget.style.borderColor='#EF4444'; }}
                                                    onMouseLeave={e=>{ e.currentTarget.style.background='none'; e.currentTarget.style.color='#FECACA'; e.currentTarget.style.borderColor='#FECACA'; }}>
                                                    ×
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                }
                            </div>
                        </div>

                    </div>{/* end right panel */}
                </div>{/* end main */}
            </div>
        </>
    );
}
