import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import {
    LuPhone, LuUsers, LuTrendingUp, LuClock, LuStar,
    LuSearch, LuFilter, LuDownload, LuExternalLink, LuRefreshCw,
    LuChevronUp, LuChevronDown, LuChevronsUpDown, LuSettings2,
    LuCalendar, LuList, LuMail,
} from 'react-icons/lu';
import { MdOutlinePhoneInTalk, MdOutlineViewKanban } from 'react-icons/md';
import { IoCheckmarkCircleOutline } from 'react-icons/io5';
import { BiError } from 'react-icons/bi';

// ─── Brand tokens ─────────────────────────────────────────────────────────────
const OR  = '#FF5C00';   // orange primary
const ORD = '#e05200';   // orange dark
const DK  = '#0f172a';   // dark slate
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BDY = '#374151';
const BOR = '#F0F0F0';

// ─── Status config ─────────────────────────────────────────────────────────────
const ST = {
    new:            { label:'New',            bg:'#fff3eb', c:OR,        b:'#fed7aa',  dot:OR        },
    assigned:       { label:'Assigned',       bg:'#F0FDF4', c:'#16A34A', b:'#BBF7D0',  dot:'#16A34A' },
    contacted:      { label:'Contacted',      bg:'#EFF6FF', c:'#1D4ED8', b:'#BFDBFE',  dot:'#1D4ED8' },
    interested:     { label:'Interested',     bg:'#FFFBEB', c:'#B45309', b:'#FDE68A',  dot:'#F59E0B' },
    follow_up:      { label:'Follow-up',      bg:'#FDF4FF', c:'#7E22CE', b:'#E9D5FF',  dot:'#8B5CF6' },
    not_interested: { label:'Not Interested', bg:'#FFF1F2', c:'#BE123C', b:'#FECDD3',  dot:'#EF4444' },
    converted:      { label:'Converted',      bg:'#ECFDF5', c:'#047857', b:'#6EE7B7',  dot:'#10B981' },
    lost:           { label:'Lost',           bg:'#F9FAFB', c:'#6B7280', b:'#E5E7EB',  dot:'#9CA3AF' },
};

const ACT = {
    call:          { icon:'📞', color:OR        },
    note:          { icon:'📝', color:'#06B6D4' },
    status_change: { icon:'🔄', color:'#F59E0B' },
    email:         { icon:'✉️', color:'#8B5CF6' },
    followup:      { icon:'📅', color:'#10B981' },
    assignment:    { icon:'👤', color:OR        },
    whatsapp:      { icon:'💬', color:'#25D366' },
};

const AVATARS = [
    ['#FF5C00','#FF8C4A'], ['#10B981','#34D399'], ['#F59E0B','#FCD34D'],
    ['#EF4444','#F87171'], ['#8B5CF6','#A78BFA'], ['#06B6D4','#67E8F9'], ['#EC4899','#F9A8D4'],
];

const ADV_KEYS = ['academic_year_id','quota','gender','state','city','followup','last_call_days','has_whatsapp'];
function hasAdv(f) { return ADV_KEYS.some(k => f[k]); }

// Returns null if the value looks like a date/timestamp instead of a real name.
// Catches formats: "09/27/2025 9:19pm", "2025-09-27", "27-09-2025", etc.
function cleanName(raw) {
    if (!raw) return null;
    if (/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(raw.trim())) return null; // MM/DD/YYYY or DD-MM-YYYY
    if (/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}/.test(raw.trim())) return null;   // YYYY-MM-DD
    return raw;
}

function ago(iso) {
    if (!iso) return null;
    const d = Math.floor((Date.now() - new Date(iso)) / 60000);
    if (d < 1)  return 'Just now';
    if (d < 60) return `${d}m ago`;
    const h = Math.floor(d / 60);
    if (h < 24) return `${h}h ago`;
    const dy = Math.floor(h / 24);
    return dy < 30 ? `${dy}d ago` : new Date(iso).toLocaleDateString('en-GB', { day:'2-digit', month:'short' });
}

// ─── Shared Card wrapper ──────────────────────────────────────────────────────
function Card({ children, style = {} }) {
    return (
        <div style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
            boxShadow:'0 2px 8px rgba(0,0,0,0.04)', overflow:'hidden', ...style }}>
            {children}
        </div>
    );
}

// ─── Section heading — indigo left bar (NO dark header) ──────────────────────
function SHead({ icon, title, sub, right }) {
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
            gap:10, padding:'14px 20px', borderBottom:`1px solid ${BOR}`,
            background:'linear-gradient(135deg,#FAFBFC 0%,#FFFFFF 100%)' }}>
            <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                <div style={{ width:3, height:32, borderRadius:2, background:'linear-gradient(180deg,'+OR+','+ORD+')', flexShrink:0 }}/>
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

// ─── Compact stat row for left column ────────────────────────────────────────
function StatRow({ icon, label, value, orange }) {
    return (
        <div style={{
            display:'flex', alignItems:'center', gap:10, padding:'10px 12px',
            background: orange ? `linear-gradient(135deg,${OR},${ORD})` : WH, borderRadius:10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.25)' : '0 1px 3px rgba(0,0,0,0.04)',
        }}>
            <div style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                background: orange ? 'rgba(255,255,255,0.18)' : '#fff3eb',
                display:'flex', alignItems:'center', justifyContent:'center', fontSize:15,
                color: orange ? '#fff' : OR }}>{icon}</div>
            <div style={{ flex:1, minWidth:0 }}>
                <div style={{ fontSize:9, fontWeight:600, textTransform:'uppercase',
                    letterSpacing:'0.5px', marginBottom:1,
                    color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize:20, fontWeight:800, lineHeight:1,
                    color: orange ? '#fff' : DK }}>{value}</div>
            </div>
        </div>
    );
}

// ─── Input helpers ────────────────────────────────────────────────────────────
const inputBase = {
    borderRadius:8, border:'1px solid #E5E7EB', fontSize:12.5, height:34,
    background:'#FAFBFC', color:DK, width:'100%', padding:'0 10px',
    fontFamily:'Poppins,sans-serif', outline:'none',
    transition:'border-color .15s, box-shadow .15s',
};
function FI({ style:s, ...p }) {
    const [f,sf] = useState(false);
    return <input {...p} style={{ ...inputBase, ...(f?{borderColor:OR,boxShadow:'0 0 0 3px rgba(255,92,0,0.12)',background:'#fff'}:{}), ...s }}
        onFocus={()=>sf(true)} onBlur={()=>sf(false)}/>;
}
function FS({ style:s, children, ...p }) {
    const [f,sf] = useState(false);
    return <select {...p} style={{ ...inputBase, ...(f?{borderColor:OR,boxShadow:'0 0 0 3px rgba(255,92,0,0.12)',background:'#fff'}:{}), ...s }}>
        {children}</select>;
}

// ─── Table cells ──────────────────────────────────────────────────────────────
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
    return <span style={{ background:'#fff3eb', color:OR, border:`1px solid #fed7aa`, fontSize:9.5, fontWeight:700, padding:'2px 7px', borderRadius:20 }}>Hot</span>;
}

function FollowupCell({ dateStr }) {
    if (!dateStr) return <span style={{ color:MUT, fontSize:11 }}>—</span>;
    const due = new Date(dateStr), now = new Date();
    due.setHours(0,0,0,0); now.setHours(0,0,0,0);
    const d   = Math.round((due - now) / 86400000);
    const lbl = due.toLocaleDateString('en-GB', { day:'2-digit', month:'short' });
    if (d < 0)  return (
        <div>
            <div style={{ display:'inline-flex', alignItems:'center', gap:4,
                fontSize:11, fontWeight:700, color:'#DC2626',
                background:'#FEF2F2', border:'1px solid #FECACA', padding:'2px 8px', borderRadius:6 }}>
                ⚠ {lbl}
            </div>
            <div style={{ fontSize:9.5, color:'#EF4444', fontWeight:700, marginTop:2 }}>Overdue</div>
        </div>
    );
    if (d === 0) return (
        <div>
            <div style={{ display:'inline-flex', alignItems:'center', gap:4,
                fontSize:11, fontWeight:700, color:'#D97706',
                background:'#FFFBEB', border:'1px solid #FDE68A', padding:'2px 8px', borderRadius:6 }}>
                🔔 Today
            </div>
            <div style={{ fontSize:9.5, color:'#D97706', fontWeight:600, marginTop:2 }}>{lbl}</div>
        </div>
    );
    return (
        <div>
            <div style={{ fontSize:11.5, fontWeight:600, color:BDY }}>{lbl}</div>
            <div style={{ fontSize:9.5, color:MUT, marginTop:1 }}>in {d}d</div>
        </div>
    );
}

function LastActCell({ type, isoStr }) {
    if (!isoStr) return <span style={{ color:MUT, fontSize:11 }}>—</span>;
    const m   = ACT[type] ?? { icon:'🕐', color:MUT };
    const lbl = type ? type.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) : 'Activity';
    return (
        <div style={{ display:'flex', alignItems:'center', gap:7 }}>
            <span style={{ width:26, height:26, borderRadius:7, flexShrink:0,
                background:m.color+'18', display:'flex', alignItems:'center',
                justifyContent:'center', fontSize:12 }}>{m.icon}</span>
            <div>
                <div style={{ fontSize:11.5, fontWeight:600, color:DK, lineHeight:1.2 }}>{lbl}</div>
                <div style={{ fontSize:10, color:MUT, marginTop:1 }}>{ago(isoStr)}</div>
            </div>
        </div>
    );
}

function CallBtn({ phone, leadId }) {
    const [busy, set] = useState(false);
    async function dial(e) {
        e.stopPropagation();
        if (!phone || busy) return;
        set(true);
        try { await window.GC?.startCall(phone, leadId); } catch (_) {}
        setTimeout(() => set(false), 3000);
    }
    if (!phone) return null;
    return (
        <button onClick={dial} title={`Call ${phone}`}
            style={{ width:28, height:28, borderRadius:7, border:'none', flexShrink:0,
                background: busy ? '#fff3eb' : '#fff3eb', cursor:'pointer',
                display:'inline-flex', alignItems:'center', justifyContent:'center',
                transition:'all .15s' }}>
            <MdOutlinePhoneInTalk style={{ fontSize:14, color: busy ? ORD : OR }}/>
        </button>
    );
}

function SortTh({ children, field, sort, dir, p, style }) {
    const on  = sort === field;
    const nxt = on && dir === 'asc' ? 'desc' : 'asc';
    function go() {
        const q = { ...p, sort:field, sort_dir:nxt };
        Object.keys(q).forEach(k => { if (!q[k]) delete q[k]; });
        router.get('/telecaller/leads', q, { preserveState:false });
    }
    const Ico = on ? (dir==='asc' ? LuChevronUp : LuChevronDown) : LuChevronsUpDown;
    return (
        <th onClick={go} style={{ cursor:'pointer', userSelect:'none', ...style }}>
            <span style={{ display:'inline-flex', alignItems:'center', gap:3, whiteSpace:'nowrap' }}>
                {children}<Ico size={10} style={{ color: on ? OR : '#fed7aa', flexShrink:0 }}/>
            </span>
        </th>
    );
}

function SkRows({ n = 8 }) {
    return Array.from({ length:n }).map((_,i) => (
        <tr key={i} style={{ background: i%2===0 ? WH : '#FAFBFC' }}>
            {[36,40,80,180,100,100,90,130,90,90].map((w,j) => (
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
                <div style={{ width:60, height:60, borderRadius:16, background:'#fff3eb',
                    display:'flex', alignItems:'center', justifyContent:'center',
                    margin:'0 auto 14px', fontSize:28,
                    boxShadow:'0 4px 14px rgba(255,92,0,0.12)' }}>🔍</div>
                <div style={{ fontSize:14, fontWeight:700, color:DK, marginBottom:6 }}>No leads found</div>
                <div style={{ fontSize:12, color:MUT, maxWidth:240, margin:'0 auto', lineHeight:1.7 }}>
                    Adjust your filters or check back when new leads are assigned.
                </div>
            </div>
        </td></tr>
    );
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ stats, leads, filters, courses, sources, academicYears }) {
    const s   = stats ?? {};
    const srt = filters?.sort ?? '';
    const dir = filters?.sort_dir ?? 'desc';

    const blank = { search:'',status:'',date_range:'',date_from:'',date_to:'',
        course_id:'',source:'',academic_year_id:'',quota:'',gender:'',
        state:'',city:'',followup:'',last_call_days:'',has_whatsapp:'' };

    const [form,   setF]   = useState({ ...blank, ...Object.fromEntries(Object.entries(filters??{}).filter(([,v])=>v!=null&&v!=='')) });
    const [adv,    setAdv] = useState(() => hasAdv(form));
    const [selIds, setSel] = useState(new Set());
    const [bulkSt, setBSt] = useState('');
    const [bulkLd, setBLd] = useState(false);
    const [navLd,  setNLd] = useState(false);

    useEffect(() => {
        const a = router.on('start',  () => setNLd(true));
        const b = router.on('finish', () => setNLd(false));
        return () => { a(); b(); };
    }, []);

    function qp(extra = {}) {
        const p = {};
        Object.keys(blank).forEach(k => { if (form[k]) p[k] = form[k]; });
        if (srt)               p.sort     = srt;
        if (dir)               p.sort_dir = dir;
        if (filters?.per_page) p.per_page = filters.per_page;
        return { ...p, ...extra };
    }
    function toggleOne(id) { setSel(prev => { const n=new Set(prev); n.has(id)?n.delete(id):n.add(id); return n; }); }
    function toggleAll()   { setSel(selIds.size===leads.data.length ? new Set() : new Set(leads.data.map(l=>l.id))); }
    async function applyBulk() {
        if (!bulkSt||selIds.size===0) return;
        setBLd(true);
        try {
            const r = await fetch('/telecaller/leads/bulk-status', {
                method:'POST',
                headers:{ 'Content-Type':'application/json',
                    'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content??'' },
                body:JSON.stringify({ ids:[...selIds], status:bulkSt }),
            });
            if (r.ok) { setSel(new Set()); setBSt(''); router.reload({ preserveScroll:true }); }
        } finally { setBLd(false); }
    }
    function submit(e) { e.preventDefault(); router.get('/telecaller/leads', qp(), { preserveState:false }); }
    function reset()   { setF(blank); setAdv(false); router.get('/telecaller/leads', {}, { preserveState:false }); }
    function perPage(v){ router.get('/telecaller/leads', qp({ per_page:v }), { preserveState:false }); }
    function expUrl(f) { const p=new URLSearchParams({format:f}); Object.entries(qp()).forEach(([k,v])=>p.set(k,v)); return `/telecaller/leads/export?${p}`; }

    const activeN = Object.entries(form).filter(([k,v])=>!['sort','sort_dir','per_page'].includes(k)&&v!=='').length;
    const KPI = [
        { label:'Total Leads',       icon:<LuUsers size={15}/>,                 v:s.total,           orange:true  },
        { label:'New Leads',         icon:<LuStar  size={15}/>,                 v:s.new,             orange:false },
        { label:'Interested',        icon:<LuTrendingUp size={15}/>,            v:s.interested,      orange:false },
        { label:'Follow-up Today',   icon:<LuCalendar size={15}/>,              v:s.followup,        orange:false },
        { label:'Overdue',           icon:<BiError size={15}/>,                 v:s.overdue,         orange:false },
        { label:'Converted (Month)', icon:<IoCheckmarkCircleOutline size={15}/>,v:s.converted_month, orange:false },
    ];

    return (
        <>
            <Head title="My Leads"/>
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

                /* ── Single row of 6 KPI cards ── */
                .ld-kpi-row {
                    display:grid;
                    grid-template-columns:repeat(6,1fr);
                    gap:12px;
                }
                @media(max-width:1100px){ .ld-kpi-row{ grid-template-columns:repeat(3,1fr); } }
                @media(max-width:640px) { .ld-kpi-row{ grid-template-columns:repeat(2,1fr); } }

                /* ── Body: filter left | table right ── */
                .ld-body {
                    display:grid;
                    grid-template-columns:280px 1fr;
                    gap:14px;
                    align-items:start;
                }
                @media(max-width:960px){ .ld-body{ grid-template-columns:1fr; } }

                /* ── View toggle ── */
                .ld-vt { display:inline-flex; border-radius:10px; overflow:hidden; border:1px solid ${BOR}; }
                .ld-vt a { display:inline-flex; align-items:center; gap:5px; padding:7px 15px;
                    font-size:12px; font-weight:600; text-decoration:none; transition:all .15s; }
                .ld-on  { background:linear-gradient(135deg,${OR},${ORD}); color:#fff; }
                .ld-off { background:${WH}; color:${MUT}; }
                .ld-off:hover { background:${OR}; color:#fff; }

                /* ── Modern table ── */
                .ld-tbl { width:100%; border-collapse:separate; border-spacing:0; }

                /* sticky header */
                .ld-tbl thead th {
                    position:sticky; top:0; z-index:2;
                    background:#F4F6F8;
                    color:${MUT}; font-size:9.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.8px;
                    padding:10px 12px; white-space:nowrap;
                    border-bottom:2px solid ${BOR};
                }
                .ld-tbl thead th:first-child { border-radius:0; }

                /* rows */
                .ld-tbl tbody td {
                    padding:10px 12px; vertical-align:middle;
                    font-size:12px; color:${BDY};
                    border-bottom:1px solid #F4F6F8;
                    transition:background .08s;
                }
                .ld-tbl tbody tr:last-child td { border-bottom:none; }

                /* zebra */
                .ld-tbl tbody tr.ld-even td { background:#FAFBFC; }
                .ld-tbl tbody tr.ld-odd  td { background:${WH}; }

                /* hover */
                .ld-tbl tbody tr.ld-tr:hover td { background:#fafbff !important; cursor:pointer; }
                .ld-tbl tbody tr.ld-tr:hover td:first-child  { border-left:3px solid ${OR}; padding-left:14px; }
                .ld-tbl tbody tr.ld-tr.sel td { background:#fff3eb !important; }
                .ld-tbl tbody tr.ld-tr.sel td:first-child    { border-left:3px solid ${OR}; padding-left:14px; }

                /* action col — show on hover */
                .ld-action-wrap { display:flex; align-items:center; gap:5px; justify-content:flex-end; opacity:.5; transition:opacity .15s; }
                .ld-tbl tbody tr:hover .ld-action-wrap { opacity:1; }

                /* scroll container */
                .ld-scroll {
                    overflow-y:auto;
                    max-height:440px;
                }
                .ld-scroll::-webkit-scrollbar { width:5px; }
                .ld-scroll::-webkit-scrollbar-track { background:#F4F6F8; }
                .ld-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .ld-scroll::-webkit-scrollbar-thumb:hover { background:${OR}; border-radius:4px; }

                /* cells */
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
                .ld-course { background:#fff3eb; color:${OR}; border:1px solid #fed7aa;
                    font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:5px;
                    display:inline-block; white-space:nowrap; max-width:120px;
                    overflow:hidden; text-overflow:ellipsis; }
                .ld-view {
                    display:inline-flex; align-items:center; gap:3px;
                    padding:4px 10px; border-radius:7px; font-size:11px; font-weight:600;
                    color:${BDY}; background:#F3F4F6; border:1px solid #E5E7EB;
                    text-decoration:none; transition:all .15s; white-space:nowrap;
                }
                .ld-view:hover { background:${OR}; color:#fff; border-color:${OR}; }

                /* bulk bar */
                .ld-bulk { background:#fff3eb; border-bottom:1px solid #fed7aa;
                    padding:9px 18px; display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
                .ld-bulk-sel { width:180px; border-radius:7px; font-size:12px;
                    border:1px solid #fed7aa; background:#fff; color:${DK}; height:32px; padding:0 9px; }
                .ld-btn-bulk { background:${OR}; color:#fff; border:none; border-radius:7px;
                    padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer; transition:background .15s; }
                .ld-btn-bulk:hover:not(:disabled) { background:${ORD}; }
                .ld-btn-bulk:disabled { opacity:.5; cursor:not-allowed; }
                .ld-btn-bulk-clr { background:transparent; color:${OR}; border:1px solid #fed7aa;
                    border-radius:7px; padding:6px 10px; font-size:12px; font-weight:600; cursor:pointer; }

                /* pager */
                .ld-pager { padding:10px 18px; border-top:1px solid ${BOR};
                    display:flex; align-items:center; justify-content:space-between;
                    flex-wrap:wrap; gap:9px; background:#FAFBFC; }
                .ld-pager .page-link { background:${WH}; border-color:#E5E7EB;
                    color:${BDY}; font-size:11.5px; border-radius:7px; padding:4px 9px; }
                .ld-pager .page-item.active .page-link { background:${OR}; border-color:${OR}; color:#fff; }
                .ld-pager .page-item.disabled .page-link { opacity:.4; }

                /* filter pills */
                .ld-pill { display:inline-flex; align-items:center; gap:4px;
                    background:#fff3eb; color:${OR}; border:1px solid #fed7aa;
                    border-radius:20px; padding:2px 9px; font-size:10.5px; font-weight:600; }
                .ld-px { background:none; border:none; color:${OR}; font-size:13px; line-height:1; padding:0; cursor:pointer; }
                .ld-px:hover { color:#DC2626; }
                .ld-adv  { background:#FAFBFC; border:1px solid ${BOR}; border-radius:9px; padding:12px; }
                .ld-albl { font-size:9.5px; color:${MUT}; font-weight:700; letter-spacing:.6px; text-transform:uppercase; display:block; margin-bottom:3px; }

                /* badge */
                .ld-badge { background:#fff3eb; color:${OR}; border:1px solid #fed7aa;
                    font-size:11px; font-weight:700; padding:2px 10px; border-radius:20px; }

                /* export menu */
                .ld-exp { border-radius:9px; border:1px solid #E5E7EB; overflow:hidden; min-width:150px; }
                .ld-exp .dropdown-item { font-size:12px; padding:8px 13px;
                    display:flex; align-items:center; gap:7px; color:${BDY}; }
                .ld-exp .dropdown-item:hover { background:${OR}; color:#fff; }
            `}</style>

            <div className="ld-pg">

                {/* ── Top bar: view toggle ── */}
                <div style={{ display:'flex', alignItems:'center', gap:12 }}>
                    <div className="ld-vt">
                        <Link href="/telecaller/leads" className="ld-on"><LuList size={13}/> List</Link>
                        <Link href="/telecaller/leads/pipeline" className="ld-off"><MdOutlineViewKanban size={13}/> Pipeline</Link>
                    </div>
                    <span style={{ fontSize:11.5, color:MUT }}>{leads.total} total lead{leads.total!==1?'s':''}</span>
                </div>

                {/* ── KPI cards — full width single row ── */}
                <div className="ld-kpi-row">
                    {KPI.map(k => (
                        <StatRow key={k.label} icon={k.icon} label={k.label}
                            value={k.v ?? 0} orange={k.orange}/>
                    ))}
                </div>

                {/* ── Body: filter left | table right ── */}
                <div className="ld-body">

                    {/* LEFT — filter */}
                    <div>

                        {/* ── Filter card ── */}
                        <Card>
                            <SHead icon={<LuFilter size={13}/>} title="Filter Leads"
                                sub="Search, status, date, course"
                                right={activeN > 0 && <span className="ld-badge">{activeN} active</span>}/>
                            <div style={{ padding:'14px 16px' }}>
                                <form onSubmit={submit}>
                                    {/* All inputs stacked — fits 280px column */}
                                    <div style={{ display:'flex', flexDirection:'column', gap:8, marginBottom:10 }}>
                                        <FI type="text" placeholder="Name, phone or lead code…"
                                            value={form.search} onChange={e=>setF({...form,search:e.target.value})}/>
                                        <FS value={form.status} onChange={e=>setF({...form,status:e.target.value})}>
                                            <option value="">All Statuses</option>
                                            {Object.entries(ST).map(([k,v])=><option key={k} value={k}>{v.label}</option>)}
                                        </FS>
                                        <FS value={form.date_range} onChange={e=>setF({...form,date_range:e.target.value})}>
                                            <option value="">Any Date</option>
                                            <option value="today">Today</option>
                                            <option value="7">Last 7 Days</option>
                                            <option value="30">Last 30 Days</option>
                                            <option value="custom">Custom Range</option>
                                        </FS>
                                        {form.date_range==='custom' && (
                                            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                <FI type="date" value={form.date_from} onChange={e=>setF({...form,date_from:e.target.value})}/>
                                                <FI type="date" value={form.date_to} onChange={e=>setF({...form,date_to:e.target.value})}/>
                                            </div>
                                        )}
                                        <FS value={form.course_id} onChange={e=>setF({...form,course_id:e.target.value})}>
                                            <option value="">All Courses</option>
                                            {(courses??[]).map(c=><option key={c.id} value={c.id}>{c.name}</option>)}
                                        </FS>
                                        <FS value={form.source} onChange={e=>setF({...form,source:e.target.value})}>
                                            <option value="">All Sources</option>
                                            {(sources??[]).map(s=><option key={s} value={s}>{s}</option>)}
                                        </FS>
                                    </div>

                                    <div className="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <button type="button"
                                            onClick={()=>setAdv(v=>!v)}
                                            style={{ background:adv?'#fff3eb':'transparent',
                                                border:`1px solid ${adv?OR:'#E5E7EB'}`,
                                                borderRadius:7, padding:'4px 12px', fontSize:11.5,
                                                fontWeight:600, color:adv?OR:MUT,
                                                display:'inline-flex', alignItems:'center', gap:4, cursor:'pointer' }}>
                                            <LuSettings2 size={12}/>
                                            {adv?'Less':'Advanced'}
                                            {hasAdv(form)&&<span style={{background:OR,color:'#fff',fontSize:8,fontWeight:700,padding:'1px 5px',borderRadius:8}}>ON</span>}
                                        </button>
                                        {activeN > 0 && (
                                            <div className="d-flex gap-1 flex-wrap">
                                                {form.search&&<span className="ld-pill">"{form.search.slice(0,12)}{form.search.length>12?'…':''}"<button className="ld-px" type="button" onClick={()=>setF({...form,search:''})}>×</button></span>}
                                                {form.status&&<span className="ld-pill">{ST[form.status]?.label??form.status}<button className="ld-px" type="button" onClick={()=>setF({...form,status:''})}>×</button></span>}
                                                {form.date_range&&<span className="ld-pill">{form.date_range==='today'?'Today':form.date_range==='custom'?'Custom':`${form.date_range}d`}<button className="ld-px" type="button" onClick={()=>setF({...form,date_range:'',date_from:'',date_to:''})}>×</button></span>}
                                                {form.course_id&&<span className="ld-pill">Course<button className="ld-px" type="button" onClick={()=>setF({...form,course_id:''})}>×</button></span>}
                                            </div>
                                        )}
                                    </div>

                                    {adv && (
                                        <div className="ld-adv mb-2">
                                            <div style={{ display:'flex', flexDirection:'column', gap:7 }}>
                                                <div>
                                                    <label className="ld-albl">Follow-up Due</label>
                                                    <FS value={form.followup} onChange={e=>setF({...form,followup:e.target.value})}>
                                                        <option value="">Any</option>
                                                        <option value="today">Due Today</option>
                                                        <option value="overdue">Overdue</option>
                                                        <option value="this_week">This Week</option>
                                                        <option value="none">None Set</option>
                                                    </FS>
                                                </div>
                                                <div>
                                                    <label className="ld-albl">Academic Year</label>
                                                    <FS value={form.academic_year_id} onChange={e=>setF({...form,academic_year_id:e.target.value})}>
                                                        <option value="">All Years</option>
                                                        {(academicYears??[]).map(y=><option key={y.id} value={y.id}>{y.name}</option>)}
                                                    </FS>
                                                </div>
                                                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                    <div>
                                                        <label className="ld-albl">Gender</label>
                                                        <FS value={form.gender} onChange={e=>setF({...form,gender:e.target.value})}>
                                                            <option value="">All</option>
                                                            <option value="male">Male</option>
                                                            <option value="female">Female</option>
                                                            <option value="other">Other</option>
                                                        </FS>
                                                    </div>
                                                    <div>
                                                        <label className="ld-albl">Not Called (Days)</label>
                                                        <FI type="number" min="1" max="365" placeholder="7" value={form.last_call_days} onChange={e=>setF({...form,last_call_days:e.target.value})}/>
                                                    </div>
                                                </div>
                                                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                                    <div>
                                                        <label className="ld-albl">State</label>
                                                        <FI type="text" placeholder="Tamil Nadu" value={form.state} onChange={e=>setF({...form,state:e.target.value})}/>
                                                    </div>
                                                    <div>
                                                        <label className="ld-albl">City</label>
                                                        <FI type="text" placeholder="Chennai" value={form.city} onChange={e=>setF({...form,city:e.target.value})}/>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label className="ld-albl">WhatsApp</label>
                                                    <FS value={form.has_whatsapp} onChange={e=>setF({...form,has_whatsapp:e.target.value})}>
                                                        <option value="">All Leads</option>
                                                        <option value="1">Has Conversation</option>
                                                    </FS>
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {/* Action buttons — full width stacked for narrow column */}
                                    <button type="submit"
                                        style={{ width:'100%', background:`linear-gradient(135deg,${OR},${ORD})`,
                                            color:'#fff', border:'none',
                                            borderRadius:8, padding:'8px', fontSize:12.5, fontWeight:600,
                                            display:'flex', alignItems:'center', justifyContent:'center',
                                            gap:6, cursor:'pointer', marginBottom:6,
                                            boxShadow:'0 2px 8px rgba(255,92,0,0.3)' }}>
                                        <LuFilter size={13}/> Apply Filters
                                    </button>
                                    <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
                                        <button type="button" onClick={reset}
                                            style={{ background:WH, color:BDY, border:'1px solid #E5E7EB',
                                                borderRadius:8, padding:'7px', fontSize:12, fontWeight:600,
                                                display:'flex', alignItems:'center', justifyContent:'center',
                                                gap:5, cursor:'pointer' }}>
                                            <LuRefreshCw size={12}/> Reset
                                        </button>
                                        <div className="dropdown">
                                            <button type="button" data-bs-toggle="dropdown"
                                                style={{ width:'100%', background:WH, color:BDY,
                                                    border:'1px solid #E5E7EB', borderRadius:8, padding:'7px',
                                                    fontSize:12, fontWeight:600, display:'flex',
                                                    alignItems:'center', justifyContent:'center',
                                                    gap:5, cursor:'pointer' }}>
                                                <LuDownload size={12} style={{color:'#10B981'}}/> Export
                                            </button>
                                            <ul className="dropdown-menu shadow-sm ld-exp">
                                                <li><a className="dropdown-item" href={expUrl('excel')} target="_blank" rel="noreferrer"><span>📊</span> Excel</a></li>
                                                <li><a className="dropdown-item" href={expUrl('pdf')} onClick={e=>{e.preventDefault();window.location.href=expUrl('pdf');}}><span>📄</span> PDF</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </Card>

                    </div>{/* end LEFT filter column */}

                    {/* RIGHT — table card (direct second child of ld-body grid) */}
                    <Card>
                            <SHead
                                icon={<LuList size={13}/>}
                                title="My Lead List"
                                sub={`${leads.from??0}–${leads.to??0} of ${leads.total} results`}
                                right={
                                    <div style={{ display:'flex', alignItems:'center', gap:8 }}>
                                        <span className="ld-badge">{leads.total} records</span>
                                        {selIds.size > 0 && (
                                            <span style={{ fontSize:11, color:OR, fontWeight:700 }}>{selIds.size} selected</span>
                                        )}
                                    </div>
                                }
                            />

                            {selIds.size > 0 && (
                                <div className="ld-bulk">
                                    <select className="ld-bulk-sel" value={bulkSt} onChange={e=>setBSt(e.target.value)}>
                                        <option value="">Change status to…</option>
                                        {Object.entries(ST).slice(0,6).map(([k,v])=><option key={k} value={k}>{v.label}</option>)}
                                    </select>
                                    <button className="ld-btn-bulk" disabled={!bulkSt||bulkLd} onClick={applyBulk}>{bulkLd?'…':'Apply'}</button>
                                    <button className="ld-btn-bulk-clr" onClick={()=>setSel(new Set())}>Clear</button>
                                </div>
                            )}

                            {/* scrollable table */}
                            <div className="ld-scroll">
                                <table className="ld-tbl">
                                    <thead>
                                        <tr>
                                            <th style={{ width:34, paddingLeft:18 }}>
                                                <input type="checkbox"
                                                    checked={leads.data.length>0&&selIds.size===leads.data.length}
                                                    onChange={toggleAll}/>
                                            </th>
                                            <th style={{ width:36 }}>#</th>
                                            <th>Code</th>
                                            <SortTh field="name" sort={srt} dir={dir} p={qp()}>Name</SortTh>
                                            <th>Phone</th>
                                            <th>Course</th>
                                            <th>Status</th>
                                            <th>Last Activity</th>
                                            <SortTh field="next_followup" sort={srt} dir={dir} p={qp()}>Follow-up</SortTh>
                                            <th style={{ textAlign:'right', paddingRight:16 }}>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {navLd
                                            ? <SkRows n={leads.data.length||8}/>
                                            : leads.data.length===0
                                                ? <EmptyState/>
                                                : leads.data.map((lead,idx) => {
                                                    const sno  = (leads.current_page-1)*leads.per_page+idx+1;
                                                    const href = `/telecaller/leads/${lead.encrypted_id}`;
                                                    const sel  = selIds.has(lead.id);
                                                    const ai   = Math.abs(lead.id % AVATARS.length);
                                                    const [c1,c2] = AVATARS[ai];
                                                    return (
                                                        <tr key={lead.id}
                                                            className={`ld-tr ${idx%2===0?'ld-even':'ld-odd'}${sel?' sel':''}`}
                                                            onClick={()=>router.visit(href)}>
                                                            <td style={{ paddingLeft:18 }} onClick={e=>e.stopPropagation()}>
                                                                <input type="checkbox" checked={sel} onChange={()=>toggleOne(lead.id)}/>
                                                            </td>
                                                            <td style={{ color:MUT, fontSize:10.5, fontWeight:600 }}>{sno}</td>
                                                            <td><span className="ld-code">{lead.lead_code}</span></td>
                                                            <td>
                                                                {(() => {
                                                                    const name = cleanName(lead.name);
                                                                    return (
                                                                        <div className="ld-avatar-wrap">
                                                                            <div className="ld-avatar"
                                                                                style={{ background: name
                                                                                    ? `linear-gradient(135deg,${c1},${c2})`
                                                                                    : 'linear-gradient(135deg,#9CA3AF,#6B7280)' }}>
                                                                                {name ? name[0].toUpperCase() : '?'}
                                                                            </div>
                                                                            <div>
                                                                                <div style={{ display:'flex', alignItems:'center', gap:5, flexWrap:'wrap' }}>
                                                                                    {name
                                                                                        ? <span className="ld-lead-name">{name}</span>
                                                                                        : <span style={{ fontSize:12, fontStyle:'italic', color:MUT }}>No name</span>
                                                                                    }
                                                                                    <AgingBadge days={lead.days_aged}/>
                                                                                </div>
                                                                                {lead.email && <div className="ld-lead-email">{lead.email}</div>}
                                                                            </div>
                                                                        </div>
                                                                    );
                                                                })()}
                                                            </td>
                                                            <td>
                                                                <div className="ld-phone-val">
                                                                    <LuPhone size={10} style={{ color:MUT, marginRight:3, verticalAlign:'middle' }}/>
                                                                    {lead.phone||'—'}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                {lead.course
                                                                    ? <span className="ld-course" title={lead.course}>{lead.course}</span>
                                                                    : <span style={{color:MUT}}>—</span>}
                                                            </td>
                                                            <td><StatusBadge status={lead.status}/></td>
                                                            <td><LastActCell type={lead.last_activity_type} isoStr={lead.last_activity_at}/></td>
                                                            <td><FollowupCell dateStr={lead.next_followup}/></td>
                                                            <td style={{ paddingRight:16 }} onClick={e=>e.stopPropagation()}>
                                                                <div className="ld-action-wrap">
                                                                    <CallBtn phone={lead.phone} leadId={lead.id}/>
                                                                    <Link href={href} className="ld-view">
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

                            {/* pagination */}
                            <div className="ld-pager">
                                <div className="d-flex align-items-center gap-3 flex-wrap">
                                    <small style={{ color:MUT }}>
                                        {leads.from??0}–{leads.to??0} of {leads.total}
                                    </small>
                                    <div className="d-flex align-items-center gap-2">
                                        <small style={{ color:MUT }}>Per page:</small>
                                        <select style={{ width:62, borderRadius:7, borderColor:'#E5E7EB',
                                            fontSize:11.5, height:28, padding:'0 6px', border:'1px solid #E5E7EB' }}
                                            value={filters?.per_page||'15'} onChange={e=>perPage(e.target.value)}>
                                            <option value="15">15</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                </div>
                                {leads.last_page > 1 && (
                                    <nav>
                                        <ul className="pagination pagination-sm mb-0" style={{ gap:2 }}>
                                            {leads.links.map((link,i) => (
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

                    {/* end right: table card is directly the second grid child */}
                </div>{/* end ld-body */}

            </div>
        </>
    );
}
