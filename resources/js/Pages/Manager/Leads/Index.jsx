import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import {
    LuPhone, LuUsers, LuCalendar, LuUserCheck, LuStar,
    LuFilter, LuDownload, LuRefreshCw, LuExternalLink, LuSettings2,
    LuList, LuPlus, LuUpload, LuPencil, LuToggleLeft, LuToggleRight,
    LuShieldAlert, LuCopy,
} from 'react-icons/lu';
import { MdOutlineViewKanban } from 'react-icons/md';

// ─── Brand tokens ─────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BDY = '#374151';
const BOR = '#F0F0F0';

// ─── Status config ─────────────────────────────────────────────────────────────
const ST = {
    new:            { label:'New',            bg:'#FFF7ED', c:OR,        b:'#FED7AA',  dot:'#FF5C00' },
    assigned:       { label:'Assigned',       bg:'#F0FDF4', c:'#16A34A', b:'#BBF7D0',  dot:'#16A34A' },
    contacted:      { label:'Contacted',      bg:'#EFF6FF', c:'#1D4ED8', b:'#BFDBFE',  dot:'#1D4ED8' },
    interested:     { label:'Interested',     bg:'#FFFBEB', c:'#B45309', b:'#FDE68A',  dot:'#F59E0B' },
    follow_up:      { label:'Follow-up',      bg:'#FDF4FF', c:'#7E22CE', b:'#E9D5FF',  dot:'#8B5CF6' },
    not_interested: { label:'Not Interested', bg:'#FFF1F2', c:'#BE123C', b:'#FECDD3',  dot:'#EF4444' },
    converted:      { label:'Converted',      bg:'#ECFDF5', c:'#047857', b:'#6EE7B7',  dot:'#10B981' },
    lost:           { label:'Lost',           bg:'#F9FAFB', c:'#6B7280', b:'#E5E7EB',  dot:'#9CA3AF' },
};

// ─── Card ─────────────────────────────────────────────────────────────────────
function Card({ children, style = {} }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden', ...style }}>
            {children}
        </div>
    );
}

// ─── Section heading with orange left bar ─────────────────────────────────────
function SHead({ icon, title, sub, right }) {
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
            gap:10, padding:'14px 20px', borderBottom:`1px solid ${BOR}`,
            background:'linear-gradient(135deg,#FAFBFC 0%,#FFFFFF 100%)' }}>
            <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                <div style={{ width:3, height:32, borderRadius:2, background:OR, flexShrink:0 }}/>
                <div>
                    <div style={{ display:'flex', alignItems:'center', gap:7 }}>
                        {icon && <span style={{ color:OR }}>{icon}</span>}
                        <span style={{ fontSize:13.5, fontWeight:700, color:DK }}>{title}</span>
                    </div>
                    {sub && <div style={{ fontSize:11, color:MUT, marginTop:1 }}>{sub}</div>}
                </div>
            </div>
            {right && <div style={{ flexShrink:0 }}>{right}</div>}
        </div>
    );
}

// ─── KPI stat row ─────────────────────────────────────────────────────────────
function StatRow({ icon, label, value, orange }) {
    return (
        <div style={{
            display:'flex', alignItems:'center', gap:10, padding:'10px 12px',
            background: orange ? OR : WH, borderRadius:10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.2)' : '0 1px 3px rgba(0,0,0,0.04)',
        }}>
            <div style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                background: orange ? 'rgba(255,255,255,0.18)' : '#FFF7ED',
                display:'flex', alignItems:'center', justifyContent:'center',
                color: orange ? '#fff' : OR }}>{icon}</div>
            <div style={{ flex:1, minWidth:0 }}>
                <div style={{ fontSize:9, fontWeight:600, textTransform:'uppercase',
                    letterSpacing:'0.5px', marginBottom:1,
                    color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize:20, fontWeight:800, lineHeight:1,
                    color: orange ? '#fff' : DK }}>{value ?? 0}</div>
            </div>
        </div>
    );
}

// ─── Inputs ───────────────────────────────────────────────────────────────────
const inputBase = {
    borderRadius:8, border:'1px solid #E5E7EB', fontSize:12.5, height:34,
    background:'#FAFBFC', color:DK, width:'100%', padding:'0 10px',
    fontFamily:'Poppins,sans-serif', outline:'none',
    transition:'border-color .15s, box-shadow .15s',
};
function FI({ style:s, ...p }) {
    const [f,sf] = useState(false);
    return <input {...p} style={{ ...inputBase, ...(f?{borderColor:OR,boxShadow:`0 0 0 3px rgba(255,92,0,0.09)`,background:'#fff'}:{}), ...s }}
        onFocus={()=>sf(true)} onBlur={()=>sf(false)}/>;
}
function FS({ style:s, children, ...p }) {
    const [f,sf] = useState(false);
    return <select {...p} style={{ ...inputBase, ...(f?{borderColor:OR,boxShadow:`0 0 0 3px rgba(255,92,0,0.09)`,background:'#fff'}:{}), ...s }}>
        {children}</select>;
}

// ─── Badges ───────────────────────────────────────────────────────────────────
function StatusBadge({ status }) {
    const c = ST[status] ?? { label:status, bg:'#F3F4F6', c:'#6B7280', b:'#E5E7EB', dot:'#9CA3AF' };
    return (
        <span style={{ background:c.bg, color:c.c, border:`1px solid ${c.b}`,
            fontSize:11, fontWeight:700, padding:'3px 10px', borderRadius:20,
            whiteSpace:'nowrap', display:'inline-flex', alignItems:'center', gap:5 }}>
            <span style={{ width:5, height:5, borderRadius:'50%', background:c.dot, flexShrink:0 }}/>
            {c.label}
        </span>
    );
}

function AgingBadge({ days }) {
    if (days == null) return null;
    if (days >= 6) return <span style={{ background:'#FEF2F2', color:'#DC2626', border:'1px solid #FECACA', fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>{days}d old</span>;
    if (days >= 3) return <span style={{ background:'#FFFBEB', color:'#D97706', border:'1px solid #FDE68A', fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>{days}d</span>;
    return <span style={{ background:'#FFF7ED', color:OR, border:`1px solid #FED7AA`, fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>Hot</span>;
}

function SlaLevelBadge({ level, escalated }) {
    if (level >= 2) return <span style={{ background:'#FEF2F2', color:'#DC2626', border:'1px solid #FECACA', fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>SLA L2</span>;
    if (level >= 1) return <span style={{ background:'#FFF7ED', color:'#EA580C', border:'1px solid #FED7AA', fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>SLA L1</span>;
    if (escalated)  return <span style={{ background:'#FEFCE8', color:'#CA8A04', border:'1px solid #FDE68A', fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>ESC</span>;
    return null;
}

function FollowupCell({ dateStr }) {
    if (!dateStr) return <span style={{ color:MUT, fontSize:11 }}>—</span>;
    const due = new Date(dateStr), now = new Date();
    due.setHours(0,0,0,0); now.setHours(0,0,0,0);
    const d   = Math.round((due - now) / 86400000);
    const lbl = due.toLocaleDateString('en-GB', { day:'2-digit', month:'short' });
    if (d < 0) return (
        <div>
            <div style={{ display:'inline-flex', alignItems:'center', gap:4, fontSize:11, fontWeight:700, color:'#DC2626',
                background:'#FEF2F2', border:'1px solid #FECACA', padding:'2px 8px', borderRadius:6 }}>⚠ {lbl}</div>
            <div style={{ fontSize:9.5, color:'#EF4444', fontWeight:700, marginTop:2 }}>Overdue</div>
        </div>
    );
    if (d === 0) return (
        <div>
            <div style={{ display:'inline-flex', alignItems:'center', gap:4, fontSize:11, fontWeight:700, color:'#D97706',
                background:'#FFFBEB', border:'1px solid #FDE68A', padding:'2px 8px', borderRadius:6 }}>🔔 Today</div>
            <div style={{ fontSize:9.5, color:'#D97706', fontWeight:600, marginTop:2 }}>{lbl}</div>
        </div>
    );
    return <div><div style={{ fontSize:11.5, fontWeight:600, color:BDY }}>{lbl}</div><div style={{ fontSize:9.5, color:MUT, marginTop:1 }}>in {d}d</div></div>;
}

// ─── Skeleton / empty ─────────────────────────────────────────────────────────
function SkRows({ n = 8 }) {
    return Array.from({ length:n }).map((_,i) => (
        <tr key={i} style={{ background: i%2===0 ? WH : '#FAFBFC' }}>
            {[30,60,160,90,80,100,100,100,90,80].map((w,j) => (
                <td key={j} style={{ padding:'13px 14px' }}>
                    <div style={{ height:10, borderRadius:5, width:w,
                        background:'linear-gradient(90deg,#F0F2F5 25%,#E8EAED 50%,#F0F2F5 75%)',
                        backgroundSize:'800px 100%', animation:'ld-sh 1.4s infinite linear' }}/>
                </td>
            ))}
        </tr>
    ));
}

function EmptyState() {
    return (
        <tr><td colSpan={10}>
            <div style={{ textAlign:'center', padding:'52px 0 48px' }}>
                <div style={{ width:60, height:60, borderRadius:16, background:'#FFF7ED',
                    display:'flex', alignItems:'center', justifyContent:'center',
                    margin:'0 auto 14px', fontSize:28,
                    boxShadow:'0 4px 14px rgba(255,92,0,0.1)' }}>🔍</div>
                <div style={{ fontSize:14, fontWeight:700, color:DK, marginBottom:6 }}>No leads found</div>
                <div style={{ fontSize:12, color:MUT, maxWidth:240, margin:'0 auto', lineHeight:1.7 }}>
                    Adjust your filters or add a new lead.
                </div>
            </div>
        </td></tr>
    );
}

// ─── Edit Contact Modal ────────────────────────────────────────────────────────
function EditContactModal({ lead, urls, onSaved, onClose }) {
    const [phone, setPhone] = useState(lead.phone || '');
    const [email, setEmail] = useState(lead.email || '');
    const [err,   setErr]   = useState('');
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function handleSubmit(e) {
        e.preventDefault(); setErr('');
        const res = await fetch(urls.update_contact, {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf(), Accept:'application/json' },
            body: JSON.stringify({ phone, email }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { setErr(data.message || 'Failed to update.'); return; }
        onSaved({ phone, email });
        onClose();
    }

    return (
        <div className="modal fade show" style={{ display:'block', background:'rgba(0,0,0,.4)' }} tabIndex={-1}>
            <div className="modal-dialog">
                <form onSubmit={handleSubmit}>
                    <div className="modal-content">
                        <div className="modal-header" style={{ borderBottom:`1px solid ${BOR}` }}>
                            <h5 className="modal-title" style={{ fontSize:14, fontWeight:700, color:DK, display:'flex', alignItems:'center', gap:8 }}>
                                <LuPencil size={15} color={OR}/> Edit Contact Details
                            </h5>
                            <button type="button" className="btn-close" onClick={onClose}/>
                        </div>
                        <div className="modal-body">
                            {err && <div className="alert alert-danger py-2" style={{ fontSize:12 }}>{err}</div>}
                            <div className="mb-3">
                                <label className="form-label fw-semibold" style={{ fontSize:12 }}>Mobile Number <span className="text-danger">*</span></label>
                                <input type="text" className="form-control" required maxLength={20}
                                    value={phone} onChange={e=>setPhone(e.target.value)} placeholder="e.g. 9876543210"/>
                            </div>
                            <div className="mb-3">
                                <label className="form-label fw-semibold" style={{ fontSize:12 }}>Email Address</label>
                                <input type="email" className="form-control" maxLength={255}
                                    value={email} onChange={e=>setEmail(e.target.value)} placeholder="e.g. student@example.com"/>
                            </div>
                        </div>
                        <div className="modal-footer" style={{ borderTop:`1px solid ${BOR}` }}>
                            <button type="button" className="btn btn-sm btn-light" onClick={onClose}>Cancel</button>
                            <button type="submit" className="btn btn-sm"
                                style={{ background:OR, color:'#fff', border:'none', borderRadius:8, padding:'6px 16px', fontWeight:600, fontSize:12 }}>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}

// ─── Advanced filter keys ─────────────────────────────────────────────────────
const EMPTY_FORM = {
    search:'', telecaller:'', status:'', date_range:'',
    date_from:'', date_to:'',
    course_id:'', academic_year_id:'', quota:'', source:'', gender:'',
    state:'', city:'', district:'',
    followup:'', no_activity_days:'',
    sla:'', is_duplicate:'', is_active:'',
    aged_min:'', aged_max:'',
};
const ADV_KEYS = ['course_id','academic_year_id','quota','source','gender','state','city','district','followup','no_activity_days','sla','is_duplicate','is_active','aged_min','aged_max'];
function hasAdv(f) { return ADV_KEYS.some(k => f[k] !== '' && f[k] != null); }
function fmtSource(s) { if (!s) return '—'; return s.replace(/\b\w/g, c=>c.toUpperCase()); }

const AVATARS = [
    ['#FF5C00','#FF8C4A'], ['#10B981','#34D399'], ['#F59E0B','#FCD34D'],
    ['#EF4444','#F87171'], ['#8B5CF6','#A78BFA'], ['#06B6D4','#67E8F9'], ['#EC4899','#F9A8D4'],
];

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ leads: iLeads, telecallers, courses, academicYears, sources, totalLeads, newLeads, assignedLeads, followupToday, filters }) {
    const [leadsState, setLeadsState] = useState(iLeads);
    const [form,       setForm]       = useState({ ...EMPTY_FORM, ...filters });
    const [adv,        setAdv]        = useState(() => hasAdv({ ...EMPTY_FORM, ...filters }));
    const [navLd,      setNLd]        = useState(false);
    const [editTarget, setEditTarget] = useState(null);

    useEffect(() => {
        const a = router.on('start',  () => setNLd(true));
        const b = router.on('finish', () => setNLd(false));
        return () => { a(); b(); };
    }, []);

    function submit(e) {
        e.preventDefault();
        const p = {};
        Object.entries(form).forEach(([k,v]) => { if (v !== '' && v != null) p[k] = v; });
        router.get('/manager/leads', p, { preserveState:false });
    }
    function reset() { setForm(EMPTY_FORM); setAdv(false); router.get('/manager/leads', {}, { preserveState:false }); }

    const activeN = Object.entries(form).filter(([,v]) => v !== '' && v != null).length;
    const exportUrl = (extra = {}) => {
        const p = new URLSearchParams({ ...filters, ...extra });
        return `/manager/leads/export?${p}`;
    };

    const KPI = [
        { label:'Total Leads',     icon:<LuUsers size={15}/>,     v:totalLeads,    orange:true  },
        { label:'New Leads',       icon:<LuStar size={15}/>,      v:newLeads,      orange:false },
        { label:'Assigned Leads',  icon:<LuUserCheck size={15}/>, v:assignedLeads, orange:false },
        { label:'Follow-up Today', icon:<LuCalendar size={15}/>,  v:followupToday, orange:false },
    ];

    return (
        <>
            <Head title="Lead Management"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .ld-pg, .ld-pg div, .ld-pg span:not([class*="material"]),
                .ld-pg p,.ld-pg h1,.ld-pg h2,.ld-pg h3,.ld-pg h4,
                .ld-pg button,.ld-pg input,.ld-pg select,.ld-pg a,
                .ld-pg th,.ld-pg td,.ld-pg label,.ld-pg small {
                    font-family:'Poppins',sans-serif !important;
                    box-sizing:border-box;
                }
                .ld-pg { display:flex; flex-direction:column; gap:14px; }

                @keyframes ld-sh {
                    0%  { background-position:-400px 0 }
                    100%{ background-position: 400px 0 }
                }

                .ld-kpi-row {
                    display:grid;
                    grid-template-columns:repeat(4,1fr);
                    gap:12px;
                }
                @media(max-width:960px){ .ld-kpi-row{ grid-template-columns:repeat(2,1fr); } }
                @media(max-width:480px){ .ld-kpi-row{ grid-template-columns:1fr 1fr; } }

                .ld-body {
                    display:grid;
                    grid-template-columns:280px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:960px){ .ld-body{ grid-template-columns:1fr; } }

                /* view toggle */
                .ld-vt { display:inline-flex; border-radius:10px; overflow:hidden; border:1px solid ${BOR}; }
                .ld-vt a { display:inline-flex; align-items:center; gap:5px; padding:7px 15px;
                    font-size:12px; font-weight:600; text-decoration:none; transition:all .15s; }
                .ld-on  { background:${DK}; color:#fff; }
                .ld-off { background:${WH}; color:${MUT}; }
                .ld-off:hover { background:${OR}; color:#fff; }

                /* table */
                .ld-tbl { width:100%; border-collapse:separate; border-spacing:0; }
                .ld-tbl thead th {
                    position:sticky; top:0; z-index:2;
                    background:#F4F6F8;
                    color:${MUT}; font-size:9.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.8px;
                    padding:10px 12px; white-space:nowrap;
                    border-bottom:2px solid ${BOR};
                }
                .ld-tbl tbody td {
                    padding:10px 12px; vertical-align:middle;
                    font-size:12px; color:${BDY};
                    border-bottom:1px solid #F4F6F8;
                    transition:background .08s;
                }
                .ld-tbl tbody tr:last-child td { border-bottom:none; }
                .ld-tbl tbody tr.ld-even td { background:#FAFBFC; }
                .ld-tbl tbody tr.ld-odd  td { background:${WH}; }
                .ld-tbl tbody tr.ld-tr:hover td { background:#FFF7ED !important; cursor:pointer; }
                .ld-tbl tbody tr.ld-tr:hover td:first-child { border-left:3px solid ${OR}; padding-left:14px; }

                .ld-action-wrap { display:flex; align-items:center; gap:5px; justify-content:flex-end; opacity:.5; transition:opacity .15s; }
                .ld-tbl tbody tr:hover .ld-action-wrap { opacity:1; }

                .ld-scroll { overflow-y:auto; max-height:480px; }
                .ld-scroll::-webkit-scrollbar { width:5px; }
                .ld-scroll::-webkit-scrollbar-track { background:#F4F6F8; }
                .ld-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .ld-scroll::-webkit-scrollbar-thumb:hover { background:${OR}; }

                .ld-code { font-size:10.5px; font-weight:700; background:#F3F4F6; color:#4B5563;
                    border:1px solid #E5E7EB; padding:2px 7px; border-radius:5px; white-space:nowrap;
                    font-family:monospace !important; letter-spacing:.3px; }
                .ld-avatar-wrap { display:flex; align-items:center; gap:8px; }
                .ld-avatar { width:30px; height:30px; border-radius:8px; color:#fff;
                    font-size:12px; font-weight:800; display:flex; align-items:center;
                    justify-content:center; flex-shrink:0; }
                .ld-lead-name  { font-size:12.5px; font-weight:700; color:${DK}; white-space:nowrap; }
                .ld-lead-email { font-size:10px; color:${MUT}; margin-top:1px; }
                .ld-phone-val  { font-size:11.5px; font-weight:600; color:${DK}; white-space:nowrap; }
                .ld-course { background:#FFF7ED; color:${OR}; border:1px solid #FED7AA;
                    font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:5px;
                    display:inline-block; white-space:nowrap; max-width:120px;
                    overflow:hidden; text-overflow:ellipsis; }
                .ld-src { background:#F3F4F6; color:#4B5563; border:1px solid #E5E7EB;
                    font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:5px;
                    display:inline-block; white-space:nowrap; }
                .ld-view {
                    display:inline-flex; align-items:center; gap:3px;
                    padding:4px 10px; border-radius:7px; font-size:11px; font-weight:600;
                    color:${BDY}; background:#F3F4F6; border:1px solid #E5E7EB;
                    text-decoration:none; transition:all .15s; white-space:nowrap;
                }
                .ld-view:hover { background:${OR}; color:#fff; border-color:${OR}; }
                .ld-icon-btn {
                    width:28px; height:28px; border-radius:7px; border:1px solid #E5E7EB;
                    background:#F9FAFB; display:inline-flex; align-items:center;
                    justify-content:center; cursor:pointer; transition:all .15s; flex-shrink:0;
                }
                .ld-icon-btn:hover { background:${OR}; border-color:${OR}; }

                /* pager */
                .ld-pager { padding:10px 18px; border-top:1px solid ${BOR};
                    display:flex; align-items:center; justify-content:space-between;
                    flex-wrap:wrap; gap:9px; background:#FAFBFC; }
                .ld-pager .page-link { background:${WH}; border-color:#E5E7EB;
                    color:${BDY}; font-size:11.5px; border-radius:7px; padding:4px 9px; }
                .ld-pager .page-item.active .page-link { background:${OR}; border-color:${OR}; color:#fff; }
                .ld-pager .page-item.disabled .page-link { opacity:.4; }

                /* filter helpers */
                .ld-badge { background:#FFF7ED; color:${OR}; border:1px solid #FED7AA;
                    font-size:11px; font-weight:700; padding:2px 10px; border-radius:20px; }
                .ld-pill { display:inline-flex; align-items:center; gap:4px;
                    background:#FFF7ED; color:${OR}; border:1px solid #FED7AA;
                    border-radius:20px; padding:2px 9px; font-size:10.5px; font-weight:600; }
                .ld-px { background:none; border:none; color:${OR}; font-size:13px; line-height:1; padding:0; cursor:pointer; }
                .ld-px:hover { color:#DC2626; }
                .ld-adv  { background:#FAFBFC; border:1px solid ${BOR}; border-radius:9px; padding:12px; }
                .ld-albl { font-size:9.5px; color:${MUT}; font-weight:700; letter-spacing:.6px; text-transform:uppercase; display:block; margin-bottom:3px; }

                /* export menu */
                .ld-exp { border-radius:9px; border:1px solid #E5E7EB; overflow:hidden; min-width:150px; }
                .ld-exp .dropdown-item { font-size:12px; padding:8px 13px;
                    display:flex; align-items:center; gap:7px; color:${BDY}; }
                .ld-exp .dropdown-item:hover { background:${DK}; color:#fff; }
            `}</style>

            <div className="ld-pg">

                {/* ── Toolbar ── */}
                <div style={{ display:'flex', alignItems:'center', gap:10, flexWrap:'wrap' }}>
                    <div className="ld-vt">
                        <Link href="/manager/leads" className="ld-on"><LuList size={13}/> List</Link>
                        <Link href="/manager/leads/pipeline" className="ld-off"><MdOutlineViewKanban size={13}/> Pipeline</Link>
                    </div>
                    <span style={{ fontSize:11.5, color:MUT }}>{leadsState.total} total lead{leadsState.total!==1?'s':''}</span>

                    <div style={{ marginLeft:'auto', display:'flex', gap:8, flexWrap:'wrap' }}>
                        <Link href="/manager/leads/create"
                            style={{ display:'inline-flex', alignItems:'center', gap:6,
                                background:OR, color:'#fff', border:'none', borderRadius:8,
                                padding:'7px 14px', fontSize:12, fontWeight:600, textDecoration:'none' }}>
                            <LuPlus size={13}/> Add Lead
                        </Link>
                        <a href="/manager/leads/import"
                            onClick={e=>{ e.preventDefault(); window.location.href='/manager/leads/import'; }}
                            style={{ display:'inline-flex', alignItems:'center', gap:6,
                                background:WH, color:BDY, border:`1px solid ${BOR}`, borderRadius:8,
                                padding:'7px 14px', fontSize:12, fontWeight:600, textDecoration:'none',
                                cursor:'pointer' }}>
                            <LuUpload size={13}/> Import Excel
                        </a>
                        <div className="dropdown">
                            <button type="button" data-bs-toggle="dropdown"
                                style={{ display:'inline-flex', alignItems:'center', gap:6,
                                    background:WH, color:BDY, border:`1px solid ${BOR}`, borderRadius:8,
                                    padding:'7px 14px', fontSize:12, fontWeight:600, cursor:'pointer' }}>
                                <LuDownload size={13} style={{ color:'#10B981' }}/> Export
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end shadow-sm ld-exp">
                                <li><a className="dropdown-item" href={exportUrl()}
                                    onClick={e=>{ e.preventDefault(); window.location.href=exportUrl(); }}>
                                    <span>📊</span> Excel (.xlsx)</a></li>
                                <li><a className="dropdown-item" href={exportUrl({ format:'pdf' })}
                                    onClick={e=>{ e.preventDefault(); window.location.href=exportUrl({ format:'pdf' }); }}>
                                    <span>📄</span> PDF Report</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {/* ── KPI row ── */}
                <div className="ld-kpi-row">
                    {KPI.map(k => <StatRow key={k.label} icon={k.icon} label={k.label} value={k.v} orange={k.orange}/>)}
                </div>

                {/* ── Body: filter | table ── */}
                <div className="ld-body">

                    {/* LEFT — filter */}
                    <Card>
                        <SHead icon={<LuFilter size={13}/>} title="Filter Leads"
                            sub="Search &amp; refine"
                            right={activeN > 0 && <span className="ld-badge">{activeN} active</span>}/>
                        <div style={{ padding:'14px 16px' }}>
                            <form onSubmit={submit}>
                                <div style={{ display:'flex', flexDirection:'column', gap:8, marginBottom:10 }}>
                                    <FI type="text" placeholder="Name, phone, code or email…"
                                        value={form.search} onChange={e=>setForm({...form,search:e.target.value})}/>
                                    <FS value={form.telecaller} onChange={e=>setForm({...form,telecaller:e.target.value})}>
                                        <option value="">All Telecallers</option>
                                        {telecallers.map(t=><option key={t.id} value={t.id}>{t.name}</option>)}
                                    </FS>
                                    <FS value={form.status} onChange={e=>setForm({...form,status:e.target.value})}>
                                        <option value="">All Statuses</option>
                                        {Object.entries(ST).map(([k,v])=><option key={k} value={k}>{v.label}</option>)}
                                    </FS>
                                    <FS value={form.date_range} onChange={e=>setForm({...form,date_range:e.target.value})}>
                                        <option value="">Any Date</option>
                                        <option value="today">Today</option>
                                        <option value="7">Last 7 Days</option>
                                        <option value="30">Last 30 Days</option>
                                        <option value="custom">Custom Range</option>
                                    </FS>
                                    {form.date_range==='custom' && (
                                        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                            <FI type="date" value={form.date_from} onChange={e=>setForm({...form,date_from:e.target.value})}/>
                                            <FI type="date" value={form.date_to}   onChange={e=>setForm({...form,date_to:e.target.value})}/>
                                        </div>
                                    )}
                                </div>

                                {/* Advanced toggle */}
                                <div style={{ display:'flex', alignItems:'center', gap:8, marginBottom:8, flexWrap:'wrap' }}>
                                    <button type="button" onClick={()=>setAdv(v=>!v)}
                                        style={{ background:adv?'#FFF7ED':'transparent',
                                            border:`1px solid ${adv?OR:'#E5E7EB'}`,
                                            borderRadius:7, padding:'4px 12px', fontSize:11.5,
                                            fontWeight:600, color:adv?OR:MUT,
                                            display:'inline-flex', alignItems:'center', gap:4, cursor:'pointer' }}>
                                        <LuSettings2 size={12}/>
                                        {adv?'Less':'Advanced'}
                                        {hasAdv(form)&&<span style={{background:OR,color:'#fff',fontSize:8,fontWeight:700,padding:'1px 5px',borderRadius:8}}>ON</span>}
                                    </button>
                                    {activeN > 0 && (
                                        <div style={{ display:'flex', gap:4, flexWrap:'wrap' }}>
                                            {form.search&&<span className="ld-pill">"{form.search.slice(0,10)}{form.search.length>10?'…':''}"<button className="ld-px" type="button" onClick={()=>setForm({...form,search:''})}>×</button></span>}
                                            {form.status&&<span className="ld-pill">{ST[form.status]?.label??form.status}<button className="ld-px" type="button" onClick={()=>setForm({...form,status:''})}>×</button></span>}
                                            {form.telecaller&&<span className="ld-pill">Telecaller<button className="ld-px" type="button" onClick={()=>setForm({...form,telecaller:''})}>×</button></span>}
                                        </div>
                                    )}
                                </div>

                                {/* Advanced section */}
                                {adv && (
                                    <div className="ld-adv mb-2">
                                        <div style={{ display:'flex', flexDirection:'column', gap:7 }}>
                                            <div><label className="ld-albl">Course</label>
                                                <FS value={form.course_id} onChange={e=>setForm({...form,course_id:e.target.value})}>
                                                    <option value="">All Courses</option>
                                                    {(courses??[]).map(c=><option key={c.id} value={c.id}>{c.name}</option>)}
                                                </FS>
                                            </div>
                                            <div><label className="ld-albl">Academic Year</label>
                                                <FS value={form.academic_year_id} onChange={e=>setForm({...form,academic_year_id:e.target.value})}>
                                                    <option value="">All Years</option>
                                                    {(academicYears??[]).map(y=><option key={y.id} value={y.id}>{y.name}</option>)}
                                                </FS>
                                            </div>
                                            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                <div><label className="ld-albl">Quota</label>
                                                    <FS value={form.quota} onChange={e=>setForm({...form,quota:e.target.value})}>
                                                        <option value="">All</option>
                                                        <option value="management">Management</option>
                                                        <option value="counselling">Counselling</option>
                                                    </FS>
                                                </div>
                                                <div><label className="ld-albl">Source</label>
                                                    <FS value={form.source} onChange={e=>setForm({...form,source:e.target.value})}>
                                                        <option value="">All Sources</option>
                                                        {(sources??[]).map(s=><option key={s} value={s}>{fmtSource(s)}</option>)}
                                                    </FS>
                                                </div>
                                            </div>
                                            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                <div><label className="ld-albl">Gender</label>
                                                    <FS value={form.gender} onChange={e=>setForm({...form,gender:e.target.value})}>
                                                        <option value="">All</option>
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                        <option value="other">Other</option>
                                                    </FS>
                                                </div>
                                                <div><label className="ld-albl">Follow-up</label>
                                                    <FS value={form.followup} onChange={e=>setForm({...form,followup:e.target.value})}>
                                                        <option value="">Any</option>
                                                        <option value="today">Due Today</option>
                                                        <option value="overdue">Overdue</option>
                                                        <option value="this_week">This Week</option>
                                                        <option value="none">None Set</option>
                                                    </FS>
                                                </div>
                                            </div>
                                            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                <div><label className="ld-albl">State</label>
                                                    <FI type="text" placeholder="Tamil Nadu" value={form.state} onChange={e=>setForm({...form,state:e.target.value})}/>
                                                </div>
                                                <div><label className="ld-albl">City</label>
                                                    <FI type="text" placeholder="Chennai" value={form.city} onChange={e=>setForm({...form,city:e.target.value})}/>
                                                </div>
                                            </div>
                                            <div><label className="ld-albl">District</label>
                                                <FI type="text" placeholder="Coimbatore" value={form.district} onChange={e=>setForm({...form,district:e.target.value})}/>
                                            </div>
                                            <div><label className="ld-albl">No Activity (Days)</label>
                                                <FI type="number" min="1" max="365" placeholder="e.g. 7"
                                                    value={form.no_activity_days} onChange={e=>setForm({...form,no_activity_days:e.target.value})}/>
                                            </div>
                                            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                <div><label className="ld-albl">SLA Status</label>
                                                    <FS value={form.sla} onChange={e=>setForm({...form,sla:e.target.value})}>
                                                        <option value="">Any</option>
                                                        <option value="escalated">Escalated</option>
                                                        <option value="1">Level 1+</option>
                                                        <option value="2">Level 2+</option>
                                                    </FS>
                                                </div>
                                                <div><label className="ld-albl">Duplicate</label>
                                                    <FS value={form.is_duplicate} onChange={e=>setForm({...form,is_duplicate:e.target.value})}>
                                                        <option value="">All</option>
                                                        <option value="1">Duplicates</option>
                                                        <option value="0">Clean</option>
                                                    </FS>
                                                </div>
                                            </div>
                                            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                <div><label className="ld-albl">Active Status</label>
                                                    <FS value={form.is_active} onChange={e=>setForm({...form,is_active:e.target.value})}>
                                                        <option value="">All</option>
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </FS>
                                                </div>
                                                <div><label className="ld-albl">Min Age (d)</label>
                                                    <FI type="number" min="0" placeholder="0"
                                                        value={form.aged_min} onChange={e=>setForm({...form,aged_min:e.target.value})}/>
                                                </div>
                                            </div>
                                            <div><label className="ld-albl">Max Age (d)</label>
                                                <FI type="number" min="0" placeholder="30"
                                                    value={form.aged_max} onChange={e=>setForm({...form,aged_max:e.target.value})}/>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {/* Action buttons */}
                                <button type="submit"
                                    style={{ width:'100%', background:OR, color:'#fff', border:'none',
                                        borderRadius:8, padding:'8px', fontSize:12.5, fontWeight:600,
                                        display:'flex', alignItems:'center', justifyContent:'center',
                                        gap:6, cursor:'pointer', marginBottom:6 }}>
                                    <LuFilter size={13}/> Apply Filters
                                </button>
                                <button type="button" onClick={reset}
                                    style={{ width:'100%', background:WH, color:BDY, border:`1px solid #E5E7EB`,
                                        borderRadius:8, padding:'7px', fontSize:12, fontWeight:600,
                                        display:'flex', alignItems:'center', justifyContent:'center',
                                        gap:5, cursor:'pointer' }}>
                                    <LuRefreshCw size={12}/> Reset Filters
                                </button>
                            </form>
                        </div>
                    </Card>

                    {/* RIGHT — table */}
                    <Card>
                        <SHead
                            icon={<LuList size={13}/>}
                            title="Lead List"
                            sub={`${leadsState.from??0}–${leadsState.to??0} of ${leadsState.total} results`}
                            right={<span className="ld-badge">{leadsState.total} records</span>}
                        />

                        <div className="ld-scroll">
                            <table className="ld-tbl">
                                <thead>
                                    <tr>
                                        <th style={{ width:36 }}>#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Source</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Follow-up</th>
                                        <th style={{ textAlign:'right', paddingRight:16 }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {navLd
                                        ? <SkRows n={leadsState.data.length||8}/>
                                        : leadsState.data.length === 0
                                            ? <EmptyState/>
                                            : leadsState.data.map((lead, idx) => {
                                                const sno = (leadsState.current_page-1)*leadsState.per_page+idx+1;
                                                const ai  = Math.abs((lead.id??idx) % AVATARS.length);
                                                const [c1,c2] = AVATARS[ai];
                                                return (
                                                    <tr key={lead.id}
                                                        className={`ld-tr ${idx%2===0?'ld-even':'ld-odd'}`}
                                                        onClick={()=>router.visit(`/manager/leads/${lead.encrypted_id}`)}>
                                                        <td style={{ color:MUT, fontSize:10.5, fontWeight:600 }}>{sno}</td>
                                                        <td><span className="ld-code">{lead.lead_code}</span></td>
                                                        <td>
                                                            <div className="ld-avatar-wrap">
                                                                <div className="ld-avatar" style={{ background:`linear-gradient(135deg,${c1},${c2})` }}>
                                                                    {(lead.name||'?')[0].toUpperCase()}
                                                                </div>
                                                                <div>
                                                                    <div style={{ display:'flex', alignItems:'center', gap:5, flexWrap:'wrap' }}>
                                                                        <span className="ld-lead-name">{lead.name}</span>
                                                                        {lead.is_duplicate && <LuCopy size={10} color={OR} title="Duplicate"/>}
                                                                        <AgingBadge days={lead.days_aged}/>
                                                                        {(lead.sla_escalated||lead.sla_level>0) && <SlaLevelBadge level={lead.sla_level} escalated={lead.sla_escalated}/>}
                                                                    </div>
                                                                    {lead.email && <div className="ld-lead-email">{lead.email}</div>}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div className="ld-phone-val">
                                                                <LuPhone size={10} style={{ color:MUT, marginRight:3, verticalAlign:'middle' }}/>
                                                                {lead.phone||'—'}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {lead.source
                                                                ? <span className="ld-src">{fmtSource(lead.source)}</span>
                                                                : <span style={{ color:MUT }}>—</span>}
                                                        </td>
                                                        <td>
                                                            {lead.course
                                                                ? <span className="ld-course" title={lead.course}>{lead.course}</span>
                                                                : <span style={{ color:MUT }}>—</span>}
                                                        </td>
                                                        <td>
                                                            <div style={{ display:'flex', flexDirection:'column', gap:4 }}>
                                                                <div style={{ display:'flex', alignItems:'center', gap:5 }}>
                                                                    <span style={{ width:7, height:7, borderRadius:'50%', flexShrink:0,
                                                                        background: lead.is_active ? '#10b981' : '#EF4444' }}/>
                                                                    <StatusBadge status={lead.status}/>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {lead.assigned_user
                                                                ? <div style={{ display:'flex', alignItems:'center', gap:6 }}>
                                                                    <div style={{ width:24, height:24, borderRadius:7, background:'#FFF7ED',
                                                                        display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
                                                                        <LuUserCheck size={12} color={OR}/>
                                                                    </div>
                                                                    <span style={{ fontSize:11.5, fontWeight:600, color:BDY }}>{lead.assigned_user}</span>
                                                                  </div>
                                                                : <span style={{ color:MUT }}>—</span>}
                                                        </td>
                                                        <td><FollowupCell dateStr={lead.next_followup}/></td>
                                                        <td style={{ paddingRight:14 }} onClick={e=>e.stopPropagation()}>
                                                            <div className="ld-action-wrap">
                                                                <button type="button" className="ld-icon-btn" title="Edit contact"
                                                                    onClick={()=>setEditTarget(lead)}>
                                                                    <LuPencil size={12} color={MUT}/>
                                                                </button>
                                                                <button type="button" className="ld-icon-btn"
                                                                    title={lead.is_active?'Deactivate':'Activate'}
                                                                    style={{ border:`1px solid ${lead.is_active?'#FECACA':'#BBF7D0'}`,
                                                                        background: lead.is_active?'#FFF1F2':'#F0FDF4' }}
                                                                    onClick={async()=>{
                                                                        if(!confirm(`${lead.is_active?'Deactivate':'Activate'} this lead?`)) return;
                                                                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content||'';
                                                                        const res = await fetch(lead.urls?.toggle_active,{
                                                                            method:'POST',
                                                                            headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'},
                                                                        });
                                                                        if(res.ok) setLeadsState(prev=>({...prev,data:prev.data.map(l=>l.id===lead.id?{...l,is_active:!l.is_active}:l)}));
                                                                    }}>
                                                                    {lead.is_active
                                                                        ? <LuToggleRight size={14} color="#EF4444"/>
                                                                        : <LuToggleLeft  size={14} color="#16A34A"/>}
                                                                </button>
                                                                <Link href={`/manager/leads/${lead.encrypted_id}`} className="ld-view" onClick={e=>e.stopPropagation()}>
                                                                    <LuExternalLink size={11}/> View
                                                                </Link>
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
                        <div className="ld-pager">
                            <div style={{ display:'flex', alignItems:'center', gap:12, flexWrap:'wrap' }}>
                                <small style={{ color:MUT }}>{leadsState.from??0}–{leadsState.to??0} of {leadsState.total}</small>
                            </div>
                            {leadsState.last_page > 1 && (
                                <nav>
                                    <ul className="pagination pagination-sm mb-0" style={{ gap:2 }}>
                                        {leadsState.links.map((link,i) => (
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

                </div>{/* end ld-body */}

            </div>

            {editTarget && (
                <EditContactModal
                    lead={editTarget}
                    urls={editTarget.urls}
                    onSaved={({ phone, email }) => {
                        setLeadsState(prev => ({
                            ...prev,
                            data: prev.data.map(l => l.id === editTarget.id ? { ...l, phone, email } : l),
                        }));
                    }}
                    onClose={() => setEditTarget(null)}
                />
            )}
        </>
    );
}
