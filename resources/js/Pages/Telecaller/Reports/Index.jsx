import { useState } from 'react';
import { Head } from '@inertiajs/react';
import {
    LuFilter, LuDownload, LuRefreshCw, LuInfo,
    LuCalendar, LuUser, LuFileSpreadsheet, LuFileText,
    LuCircleCheck, LuArrowRight,
} from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0';
const STATUSES = ['new','assigned','contacted','interested','converted','not_interested'];

// ─── Input helpers ─────────────────────────────────────────────────────────────
function FI({ label, ...p }) {
    const [f,sf] = useState(false);
    const base = {
        width:'100%', height:34, borderRadius:8,
        border:`1px solid ${f?OR:'#E5E7EB'}`,
        fontSize:12.5, color:DK, background:f?'#fff':'#FAFBFC',
        padding:'0 10px', outline:'none', fontFamily:'Poppins,sans-serif',
        boxShadow:f?'0 0 0 3px rgba(255,92,0,0.09)':'none',
        transition:'all .15s',
    };
    return (
        <div>
            {label && <label style={{ fontSize:9.5, fontWeight:700, color:MUT,
                textTransform:'uppercase', letterSpacing:'.6px', display:'block',
                marginBottom:4, fontFamily:'Poppins,sans-serif' }}>{label}</label>}
            <input {...p} style={base} onFocus={()=>sf(true)} onBlur={()=>sf(false)}/>
        </div>
    );
}
function FS({ label, children, ...p }) {
    const [f,sf] = useState(false);
    const base = {
        width:'100%', height:34, borderRadius:8,
        border:`1px solid ${f?OR:'#E5E7EB'}`,
        fontSize:12.5, color:DK, background:f?'#fff':'#FAFBFC',
        padding:'0 10px', outline:'none', fontFamily:'Poppins,sans-serif',
        boxShadow:f?'0 0 0 3px rgba(255,92,0,0.09)':'none',
        transition:'all .15s',
    };
    return (
        <div>
            {label && <label style={{ fontSize:9.5, fontWeight:700, color:MUT,
                textTransform:'uppercase', letterSpacing:'.6px', display:'block',
                marginBottom:4, fontFamily:'Poppins,sans-serif' }}>{label}</label>}
            <select {...p} style={base} onFocus={()=>sf(true)} onBlur={()=>sf(false)}>{children}</select>
        </div>
    );
}

// ─── Card wrapper ──────────────────────────────────────────────────────────────
function Card({ children, style={} }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden', ...style }}>
            {children}
        </div>
    );
}

// ─── Section head ──────────────────────────────────────────────────────────────
function SHead({ icon, title, sub, right }) {
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
            gap:10, padding:'13px 18px', borderBottom:`1px solid ${BOR}`,
            background:'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
            <div style={{ display:'flex', alignItems:'center', gap:9 }}>
                <div style={{ width:3, height:28, borderRadius:2, background:OR, flexShrink:0 }}/>
                <div style={{ width:28, height:28, borderRadius:7, background:'#FFF7ED',
                    display:'flex', alignItems:'center', justifyContent:'center', color:OR }}>
                    {icon}
                </div>
                <div>
                    <div style={{ fontSize:13, fontWeight:700, color:DK }}>{title}</div>
                    {sub && <div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>{sub}</div>}
                </div>
            </div>
            {right && <div style={{ flexShrink:0 }}>{right}</div>}
        </div>
    );
}

// ─── Download button card ──────────────────────────────────────────────────────
function DownloadCard({ href, icon, title, sub, accentColor, accentBg, features }) {
    const [hov,setHov] = useState(false);
    return (
        <a href={href} target="_blank" rel="noreferrer"
            onMouseEnter={()=>setHov(true)} onMouseLeave={()=>setHov(false)}
            style={{ display:'block', textDecoration:'none',
                background:WH, borderRadius:12,
                border:`2px solid ${hov?accentColor:BOR}`,
                boxShadow:hov?`0 6px 20px ${accentColor}22`:'0 1px 4px rgba(0,0,0,0.05)',
                transition:'all .2s ease', overflow:'hidden' }}>
            {/* Top accent strip */}
            <div style={{ height:3, background:accentColor }}/>
            <div style={{ padding:'18px 20px' }}>
                {/* Header row */}
                <div style={{ display:'flex', alignItems:'center', gap:12, marginBottom:14 }}>
                    <div style={{ width:48, height:48, borderRadius:12, flexShrink:0,
                        background:accentBg, display:'flex', alignItems:'center',
                        justifyContent:'center', color:accentColor, fontSize:22 }}>
                        {icon}
                    </div>
                    <div style={{ flex:1 }}>
                        <div style={{ fontSize:15, fontWeight:700, color:DK }}>{title}</div>
                        <div style={{ fontSize:11.5, color:MUT, marginTop:2 }}>{sub}</div>
                    </div>
                    <div style={{ width:32, height:32, borderRadius:8, flexShrink:0,
                        background:hov?accentColor:accentBg,
                        display:'flex', alignItems:'center', justifyContent:'center',
                        transition:'background .2s' }}>
                        <LuArrowRight size={15} style={{ color:hov?'#fff':accentColor }}/>
                    </div>
                </div>

                {/* Feature list */}
                <div style={{ display:'flex', flexDirection:'column', gap:6 }}>
                    {features.map((f,i)=>(
                        <div key={i} style={{ display:'flex', alignItems:'center', gap:6 }}>
                            <LuCircleCheck size={12} style={{ color:accentColor, flexShrink:0 }}/>
                            <span style={{ fontSize:11.5, color:MUT }}>{f}</span>
                        </div>
                    ))}
                </div>

                {/* CTA */}
                <div style={{ marginTop:16, padding:'9px 16px', borderRadius:9, textAlign:'center',
                    background:hov?accentColor:accentBg,
                    color:hov?'#fff':accentColor, fontSize:13, fontWeight:700,
                    transition:'all .2s', display:'flex', alignItems:'center',
                    justifyContent:'center', gap:6 }}>
                    <LuDownload size={14}/> Download {title.split(' ')[1]}
                </div>
            </div>
        </a>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ courseWiseRows=[], finalCourseRows=[] }) {
    const today        = new Date().toISOString().slice(0,10);
    const firstOfMonth = new Date(new Date().getFullYear(),new Date().getMonth(),1).toISOString().slice(0,10);

    const [f,setF] = useState({
        date_from:firstOfMonth, date_to:today,
        status:'all', gender:'all', course_id:'all', final_course_id:'all', quota:'all',
    });
    const set   = (k,v) => setF(p=>({...p,[k]:v}));
    const reset = ()    => setF({ date_from:firstOfMonth, date_to:today,
        status:'all', gender:'all', course_id:'all', final_course_id:'all', quota:'all' });
    const buildUrl = fmt => {
        const p = new URLSearchParams({format:fmt});
        Object.entries(f).forEach(([k,v])=>{ if(v&&v!=='all') p.set(k,v); });
        return `/telecaller/reports/download?${p}`;
    };
    const activeN = Object.entries(f)
        .filter(([k,v])=>v&&v!=='all'&&k!=='date_from'&&k!=='date_to').length;

    return (
        <>
            <Head title="My Reports"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .rp-pg, .rp-pg div, .rp-pg span:not([class*="material"]),
                .rp-pg p,.rp-pg h1,.rp-pg h2,.rp-pg label,.rp-pg button,.rp-pg input,.rp-pg select,.rp-pg a,.rp-pg small {
                    font-family:'Poppins',sans-serif !important;
                }
                .rp-pg { display:flex; flex-direction:column; gap:16px; }

                /* Two-column layout */
                .rp-body {
                    display:grid;
                    grid-template-columns:300px 1fr;
                    gap:16px;
                    align-items:start;
                }
                @media(max-width:860px){ .rp-body{ grid-template-columns:1fr; } }

                /* Filter label */
                .rp-section-label {
                    font-size:9.5px; font-weight:700; color:${OR};
                    text-transform:uppercase; letter-spacing:.7px;
                    display:flex; align-items:center; gap:5px;
                    margin-bottom:10px;
                }
                .rp-divider { height:1px; background:${BOR}; margin:14px 0; }
            `}</style>

            <div className="rp-pg">

                {/* ── Page header ── */}
                <div style={{ background:DK, borderRadius:14, padding:'20px 22px',
                    boxShadow:'0 4px 16px rgba(0,0,0,0.16)', overflow:'hidden', position:'relative' }}>
                    {/* Subtle dot grid */}
                    <div style={{ position:'absolute', inset:0, opacity:.04,
                        backgroundImage:'radial-gradient(circle, #fff 1px, transparent 1px)',
                        backgroundSize:'22px 22px' }}/>
                    <div style={{ position:'relative' }}>
                        <div style={{ display:'flex', alignItems:'center', gap:14 }}>
                            <div style={{ width:46, height:46, borderRadius:12, flexShrink:0,
                                background:'rgba(255,92,0,0.18)', border:'1px solid rgba(255,92,0,0.3)',
                                display:'flex', alignItems:'center', justifyContent:'center' }}>
                                <LuFileText size={22} style={{ color:OR }}/>
                            </div>
                            <div>
                                <div style={{ fontSize:10, fontWeight:700, color:OR,
                                    textTransform:'uppercase', letterSpacing:'1px', marginBottom:4 }}>
                                    Reports
                                </div>
                                <div style={{ fontSize:20, fontWeight:800, color:'#fff', lineHeight:1.2 }}>
                                    My Lead Reports
                                </div>
                                <div style={{ fontSize:12, color:'rgba(255,255,255,0.5)', marginTop:3 }}>
                                    Filter your leads and export as Excel or PDF
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Two-column body ── */}
                <div className="rp-body">

                    {/* ── LEFT: filter panel ── */}
                    <Card>
                        <SHead icon={<LuFilter size={13}/>} title="Filter Leads"
                            sub="Select criteria for your report"
                            right={activeN>0 && (
                                <span style={{ background:'#FFF7ED', color:OR,
                                    border:'1px solid #FED7AA', fontSize:11, fontWeight:700,
                                    padding:'2px 9px', borderRadius:20 }}>
                                    {activeN} active
                                </span>
                            )}/>

                        <div style={{ padding:'16px 18px', display:'flex', flexDirection:'column', gap:12 }}>

                            {/* Date range */}
                            <div>
                                <div className="rp-section-label">
                                    <LuCalendar size={11}/> Date Range
                                </div>
                                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8 }}>
                                    <FI label="From" type="date" value={f.date_from}
                                        onChange={e=>set('date_from',e.target.value)}/>
                                    <FI label="To" type="date" value={f.date_to}
                                        onChange={e=>set('date_to',e.target.value)}/>
                                </div>
                            </div>

                            <div className="rp-divider"/>

                            {/* Lead filters */}
                            <div>
                                <div className="rp-section-label">
                                    <LuUser size={11}/> Lead Filters
                                </div>
                                <div style={{ display:'flex', flexDirection:'column', gap:9 }}>
                                    <FS label="Status" value={f.status} onChange={e=>set('status',e.target.value)}>
                                        <option value="all">All Statuses</option>
                                        {STATUSES.map(s=><option key={s} value={s}>{s.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>)}
                                    </FS>
                                    <FS label="Gender" value={f.gender} onChange={e=>set('gender',e.target.value)}>
                                        <option value="all">All Genders</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="not_specified">Not Specified</option>
                                    </FS>
                                    <FS label="Quota" value={f.quota} onChange={e=>set('quota',e.target.value)}>
                                        <option value="all">All Quotas</option>
                                        <option value="management">Management</option>
                                        <option value="counselling">Counselling</option>
                                    </FS>
                                    <FS label="Enquired Course" value={f.course_id} onChange={e=>set('course_id',e.target.value)}>
                                        <option value="all">All Courses</option>
                                        {courseWiseRows.map(r=><option key={r.course_id} value={r.course_id}>{r.course}</option>)}
                                    </FS>
                                    <FS label="Final Selected Course" value={f.final_course_id} onChange={e=>set('final_course_id',e.target.value)}>
                                        <option value="all">All Final Courses</option>
                                        {finalCourseRows.map(r=><option key={r.course_id} value={r.course_id}>{r.course}</option>)}
                                    </FS>
                                </div>
                            </div>

                            {/* Clear */}
                            {activeN > 0 && (
                                <>
                                    <div className="rp-divider"/>
                                    <button onClick={reset}
                                        style={{ background:WH, color:MUT, border:`1px solid ${BOR}`,
                                            borderRadius:8, padding:'8px', fontSize:12.5, fontWeight:600,
                                            cursor:'pointer', display:'flex', alignItems:'center',
                                            justifyContent:'center', gap:5, transition:'all .15s' }}>
                                        <LuRefreshCw size={13}/> Clear All Filters
                                    </button>
                                </>
                            )}
                        </div>
                    </Card>

                    {/* ── RIGHT: download section ── */}
                    <div style={{ display:'flex', flexDirection:'column', gap:14 }}>

                        {/* Section title */}
                        <Card style={{ padding:0 }}>
                            <SHead icon={<LuDownload size={13}/>} title="Download Report"
                                sub="Export your filtered leads in preferred format"/>
                            <div style={{ padding:'18px 20px', display:'flex', flexDirection:'column', gap:14 }}>
                                <DownloadCard
                                    href={buildUrl('excel')}
                                    icon={<LuFileSpreadsheet size={22}/>}
                                    title="Download Excel"
                                    sub="Spreadsheet format (.xlsx)"
                                    accentColor="#10B981"
                                    accentBg="#ECFDF5"
                                    features={[
                                        'Up to 1,000 leads per export',
                                        'All columns included — name, phone, status, course',
                                        'Ready for analysis in Excel or Google Sheets',
                                    ]}
                                />
                                <DownloadCard
                                    href={buildUrl('pdf')}
                                    icon={<LuFileText size={22}/>}
                                    title="Download PDF"
                                    sub="Printable report (landscape A4)"
                                    accentColor="#EF4444"
                                    accentBg="#FEF2F2"
                                    features={[
                                        'Formatted for printing or sharing',
                                        'Includes all selected filters as header',
                                        'Landscape A4 layout for easy reading',
                                    ]}
                                />
                            </div>
                        </Card>

                        {/* Info note */}
                        <div style={{ display:'flex', alignItems:'flex-start', gap:10,
                            background:'#FFF7ED', border:'1px solid #FED7AA',
                            borderRadius:10, padding:'12px 14px' }}>
                            <LuInfo size={15} style={{ color:OR, flexShrink:0, marginTop:1 }}/>
                            <div>
                                <div style={{ fontSize:12.5, fontWeight:600, color:DK, marginBottom:2 }}>
                                    About your report
                                </div>
                                <div style={{ fontSize:11.5, color:'#92400E', lineHeight:1.65 }}>
                                    Reports include only your assigned leads that match the selected filters.
                                    Date range applies to lead creation date.
                                    {activeN === 0 && <span style={{ display:'block', marginTop:4, color:MUT }}>
                                        No active filters — all your leads will be included.
                                    </span>}
                                </div>
                            </div>
                        </div>

                    </div>{/* end right */}
                </div>{/* end body */}

            </div>
        </>
    );
}
