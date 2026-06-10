import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { LuUsers, LuClock, LuPhone, LuCalendar, LuSearch, LuRefreshCw,
         LuArrowLeft, LuExternalLink, LuFilter } from 'react-icons/lu';
import { MdOutlinePhoneInTalk } from 'react-icons/md';
import { IoCheckmarkCircleOutline } from 'react-icons/io5';

const OR='#FF5C00', DK='#1D1D1D', WH='#FEFEFE', MUT='#9CA3AF', BOR='#F0F0F0', BDY='#374151';

const CAMPAIGN_STATUS = {
    active:    { bg:'#ECFDF5', color:'#16A34A', dot:'#22C55E' },
    paused:    { bg:'#FFFBEB', color:'#CA8A04', dot:'#EAB308' },
    completed: { bg:'#F9FAFB', color:'#6B7280', dot:'#9CA3AF' },
    draft:     { bg:'#F9FAFB', color:'#6B7280', dot:'#D1D5DB' },
};

const STATUS_PILL = {
    new:            { bg:'#F9FAFB', color:'#6B7280' },
    pending:        { bg:'#F9FAFB', color:'#6B7280' },
    assigned:       { bg:'#FFF7ED', color:OR         },
    contacted:      { bg:'#EFF6FF', color:'#1D4ED8'  },
    called:         { bg:'#EFF6FF', color:'#1D4ED8'  },
    interested:     { bg:'#ECFDF5', color:'#16A34A'  },
    not_interested: { bg:'#FEF2F2', color:'#DC2626'  },
    no_answer:      { bg:'#FFFBEB', color:'#B45309'  },
    callback:       { bg:'#FDF4FF', color:'#7C3AED'  },
    converted:      { bg:'#D1FAE5', color:'#065F46'  },
    follow_up:      { bg:'#FFFBEB', color:'#CA8A04'  },
    lost:           { bg:'#FEF2F2', color:'#991B1B'  },
};

const AVATAR_PALETTE = [OR,'#8B5CF6','#0EA5E9','#10B981','#F59E0B','#F43F5E','#06B6D4','#EC4899'];
const STATUSES = ['pending','called','interested','not_interested','no_answer','callback','converted'];

function fmtDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB',{ day:'2-digit', month:'short', year:'numeric' });
}
function avatarColor(name) { return AVATAR_PALETTE[(name?.charCodeAt(0)??0) % AVATAR_PALETTE.length]; }

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
            padding:'13px 20px', borderBottom:`1px solid ${BOR}`,
            background:'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
            <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                <div style={{ width:3, height:30, borderRadius:2, background:OR, flexShrink:0 }}/>
                <div>
                    <div style={{ display:'flex', alignItems:'center', gap:7 }}>
                        {icon&&<span style={{ color:OR }}>{icon}</span>}
                        <span style={{ fontSize:13, fontWeight:700, color:DK }}>{title}</span>
                    </div>
                    {sub&&<div style={{ fontSize:10.5, color:MUT, marginTop:1 }}>{sub}</div>}
                </div>
            </div>
            {right&&<div style={{ flexShrink:0 }}>{right}</div>}
        </div>
    );
}

function StatCard({ icon, label, value, accent }) {
    return (
        <div style={{ background:WH, borderRadius:12, border:`1px solid ${BOR}`,
            boxShadow:'0 1px 4px rgba(0,0,0,0.04)', padding:'13px 16px',
            display:'flex', alignItems:'center', gap:10 }}>
            <div style={{ width:36, height:36, borderRadius:9, flexShrink:0,
                background:`${accent}18`, display:'flex', alignItems:'center',
                justifyContent:'center', color:accent, fontSize:17 }}>{icon}</div>
            <div>
                <div style={{ fontSize:20, fontWeight:800, color:DK, lineHeight:1 }}>{value}</div>
                <div style={{ fontSize:10, fontWeight:600, color:MUT, textTransform:'uppercase',
                    letterSpacing:'.5px', marginTop:2 }}>{label}</div>
            </div>
        </div>
    );
}

function Pagination({ data }) {
    if (!data || data.last_page <= 1) return null;
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
            marginTop:14, flexWrap:'wrap', gap:8 }}>
            <small style={{ color:MUT }}>
                Showing <strong style={{ color:DK }}>{data.from??0}–{data.to??0}</strong> of <strong style={{ color:DK }}>{data.total}</strong>
            </small>
            <div style={{ display:'flex', gap:4 }}>
                {data.links.map((link,i) => link.url
                    ? <Link key={i} href={link.url} dangerouslySetInnerHTML={{ __html:link.label }}
                        style={{ display:'inline-flex', alignItems:'center', justifyContent:'center',
                            minWidth:32, height:32, padding:'0 8px', borderRadius:8,
                            fontSize:12, fontWeight:link.active?700:500, textDecoration:'none',
                            background:link.active?OR:'#F9FAFB', color:link.active?'#fff':BDY,
                            border:`1px solid ${link.active?OR:'#E5E7EB'}` }}/>
                    : <span key={i} dangerouslySetInnerHTML={{ __html:link.label }}
                        style={{ display:'inline-flex', alignItems:'center', justifyContent:'center',
                            minWidth:32, height:32, padding:'0 8px', borderRadius:8,
                            fontSize:12, color:MUT, background:'#F9FAFB', border:'1px solid #E5E7EB' }}/>
                )}
            </div>
        </div>
    );
}

export default function Show({ campaign, contacts, stats, filters }) {
    const s   = stats ?? {};
    const total = s.total ?? 0;
    const calledPct    = total ? Math.round(((s.called    ??0)/total)*100) : 0;
    const convertedPct = total ? Math.round(((s.converted ??0)/total)*100) : 0;
    const csc = CAMPAIGN_STATUS[campaign.status] ?? CAMPAIGN_STATUS.draft;

    const [form,setForm] = useState({ search:filters?.search??'', status:filters?.status??'' });

    function handleFilter(e) {
        e.preventDefault();
        const p={}; if(form.search) p.search=form.search; if(form.status) p.status=form.status;
        router.get(`/telecaller/campaigns/${campaign.encrypted_id}`,p,{preserveState:false});
    }
    function resetFilter() {
        setForm({search:'',status:''});
        router.get(`/telecaller/campaigns/${campaign.encrypted_id}`,{},{preserveState:false});
    }

    return (
        <>
            <Head title={campaign.name}/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .cs-pg, .cs-pg div, .cs-pg span:not([class*="material"]),
                .cs-pg p,.cs-pg h1,.cs-pg h2,.cs-pg label,.cs-pg button,.cs-pg input,.cs-pg select,.cs-pg a,
                .cs-pg td,.cs-pg th,.cs-pg small { font-family:'Poppins',sans-serif !important; }
                .cs-pg { display:flex; flex-direction:column; gap:14px; }
                .cs-tbl { width:100%; border-collapse:collapse; }
                .cs-tbl thead th { background:#FAFBFC; color:${MUT}; font-size:9.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.7px; padding:9px 14px;
                    border-bottom:2px solid ${BOR}; white-space:nowrap; position:sticky; top:0; z-index:1; }
                .cs-tbl tbody td { padding:10px 14px; vertical-align:middle; font-size:12.5px;
                    color:${BDY}; border-bottom:1px solid #F9FAFB; }
                .cs-tbl tbody tr:last-child td { border-bottom:none; }
                .cs-tbl tbody tr:hover td { background:#FFF7ED; }
                .cs-tbl tbody tr:hover td:first-child { border-left:3px solid ${OR}; padding-left:11px; }
                .cs-scroll { max-height:480px; overflow-y:auto; }
                .cs-scroll::-webkit-scrollbar { width:5px; }
                .cs-scroll::-webkit-scrollbar-thumb { background:#D1D5DB; border-radius:4px; }
                .cs-scroll::-webkit-scrollbar-thumb:hover { background:${OR}; }
                .cs-kpi { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
                @media(max-width:800px){ .cs-kpi{ grid-template-columns:repeat(2,1fr); } }
            `}</style>

            <div className="cs-pg">

                {/* Page header */}
                <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
                    flexWrap:'wrap', gap:10 }}>
                    <div style={{ display:'flex', alignItems:'center', gap:12 }}>
                        <Link href="/telecaller/campaigns"
                            style={{ width:36, height:36, borderRadius:10, background:WH, border:`1px solid ${BOR}`,
                                display:'flex', alignItems:'center', justifyContent:'center',
                                textDecoration:'none', color:DK, flexShrink:0 }}>
                            <LuArrowLeft size={16}/>
                        </Link>
                        <div>
                            <div style={{ fontSize:18, fontWeight:800, color:DK, lineHeight:1.2 }}>{campaign.name}</div>
                            <div style={{ fontSize:11, color:MUT, marginTop:2 }}>Campaign contacts assigned to you</div>
                        </div>
                    </div>
                    <span style={{ display:'inline-flex', alignItems:'center', gap:5,
                        background:csc.bg, color:csc.color, fontSize:11, fontWeight:700,
                        padding:'4px 12px', borderRadius:20 }}>
                        <span style={{ width:6, height:6, borderRadius:'50%', background:csc.dot }}/>
                        {(campaign.status??'').charAt(0).toUpperCase()+(campaign.status??'').slice(1)}
                    </span>
                </div>

                {/* KPI cards */}
                <div className="cs-kpi">
                    <StatCard icon={<LuUsers size={17}/>}                  label="My Contacts" value={(s.total??0).toLocaleString()}     accent={OR}       />
                    <StatCard icon={<LuClock size={17}/>}                  label="Pending"     value={(s.pending??0).toLocaleString()}   accent="#F59E0B"  />
                    <StatCard icon={<MdOutlinePhoneInTalk size={17}/>}     label="Contacted"   value={(s.called??0).toLocaleString()}    accent="#06B6D4"  />
                    <StatCard icon={<IoCheckmarkCircleOutline size={17}/>} label="Converted"   value={(s.converted??0).toLocaleString()} accent="#10B981"  />
                </div>

                {/* Progress bar */}
                {total > 0 && (
                    <Card style={{ padding:'16px 20px' }}>
                        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:8 }}>
                            <span style={{ fontSize:12.5, fontWeight:700, color:DK }}>Overall Progress</span>
                            <span style={{ fontSize:12, color:MUT, fontWeight:600 }}>{calledPct}% contacted</span>
                        </div>
                        <div style={{ background:'#F3F4F6', borderRadius:8, height:7, overflow:'hidden' }}>
                            <div style={{ width:`${calledPct}%`, height:'100%', background:`linear-gradient(90deg,${OR},#FF8C4A)`,
                                borderRadius:8, transition:'width .6s ease' }}/>
                        </div>
                        <div style={{ display:'flex', gap:18, marginTop:9 }}>
                            {[
                                { color:OR,        label:`Contacted ${calledPct}%`    },
                                { color:'#10B981', label:`Converted ${convertedPct}%` },
                                { color:'#D1D5DB', label:`Remaining ${100-calledPct}%`},
                            ].map(item=>(
                                <div key={item.label} style={{ display:'flex', alignItems:'center', gap:5 }}>
                                    <span style={{ width:7, height:7, borderRadius:'50%', background:item.color, flexShrink:0 }}/>
                                    <span style={{ fontSize:11, color:MUT, fontWeight:500 }}>{item.label}</span>
                                </div>
                            ))}
                        </div>
                    </Card>
                )}

                {/* Contact list */}
                <Card>
                    <SHead icon={<LuUsers size={13}/>} title="Contact List"
                        right={<span style={{ background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                            fontSize:11, fontWeight:700, padding:'2px 10px', borderRadius:20 }}>
                            {contacts.total} contacts</span>}/>

                    {/* Filters */}
                    <div style={{ padding:'12px 18px', borderBottom:`1px solid ${BOR}` }}>
                        <form onSubmit={handleFilter} style={{ display:'flex', flexWrap:'wrap', gap:8, alignItems:'flex-end' }}>
                            <input type="text" placeholder="Search name, phone…"
                                value={form.search} onChange={e=>setForm({...form,search:e.target.value})}
                                style={{ flex:'1 1 180px', height:34, borderRadius:8, border:'1px solid #E5E7EB',
                                    fontSize:12.5, color:DK, background:'#FAFBFC', padding:'0 10px', outline:'none' }}/>
                            <select value={form.status} onChange={e=>setForm({...form,status:e.target.value})}
                                style={{ flex:'0 1 165px', height:34, borderRadius:8, border:'1px solid #E5E7EB',
                                    fontSize:12.5, color:DK, background:'#FAFBFC', padding:'0 10px', outline:'none' }}>
                                <option value="">All Statuses</option>
                                {STATUSES.map(st=><option key={st} value={st}>{st.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>)}
                            </select>
                            <button type="submit"
                                style={{ background:OR, color:'#fff', border:'none', borderRadius:8,
                                    padding:'0 14px', height:34, fontSize:12.5, fontWeight:600,
                                    display:'inline-flex', alignItems:'center', gap:5, cursor:'pointer' }}>
                                <LuFilter size={13}/> Filter
                            </button>
                            <button type="button" onClick={resetFilter}
                                style={{ background:WH, color:MUT, border:'1px solid #E5E7EB', borderRadius:8,
                                    width:34, height:34, display:'inline-flex', alignItems:'center',
                                    justifyContent:'center', cursor:'pointer' }}>
                                <LuRefreshCw size={13}/>
                            </button>
                        </form>
                    </div>

                    {contacts.data.length === 0
                        ? <div style={{ textAlign:'center', padding:'52px 0' }}>
                            <div style={{ fontSize:28, marginBottom:12 }}>👥</div>
                            <div style={{ fontSize:14, fontWeight:700, color:DK, marginBottom:5 }}>No contacts found</div>
                            <div style={{ fontSize:12, color:MUT }}>Try adjusting your filters above.</div>
                          </div>
                        : <div className="cs-scroll">
                            <table className="cs-tbl">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Mobile</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Follow-up</th>
                                        <th>Calls</th>
                                        <th style={{ textAlign:'right', paddingRight:18 }}></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {contacts.data.map(contact => {
                                        const col = avatarColor(contact.name);
                                        const init = (contact.name??'?').charAt(0).toUpperCase();
                                        const sp = STATUS_PILL[contact.status] ?? STATUS_PILL.new;
                                        return (
                                            <tr key={contact.id}>
                                                <td>
                                                    <div style={{ display:'flex', alignItems:'center', gap:9 }}>
                                                        <div style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                                                            background:`${col}18`, display:'flex', alignItems:'center',
                                                            justifyContent:'center', color:col, fontSize:13, fontWeight:800 }}>
                                                            {init}
                                                        </div>
                                                        <div>
                                                            <div style={{ fontSize:12.5, fontWeight:700, color:DK }}>{contact.name}</div>
                                                            {contact.city && <div style={{ fontSize:10.5, color:MUT }}>{contact.city}</div>}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href={`tel:${contact.phone}`}
                                                        style={{ fontSize:12, color:OR, fontWeight:600,
                                                            textDecoration:'none', display:'flex', alignItems:'center', gap:4 }}>
                                                        <LuPhone size={11} style={{ color:MUT, flexShrink:0 }}/>{contact.phone}
                                                    </a>
                                                </td>
                                                <td style={{ fontSize:12, color:MUT }}>{contact.course||'—'}</td>
                                                <td>
                                                    <span style={{ background:sp.bg, color:sp.color, fontSize:10.5,
                                                        fontWeight:700, padding:'3px 9px', borderRadius:20, whiteSpace:'nowrap' }}>
                                                        {contact.status?.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())||'—'}
                                                    </span>
                                                </td>
                                                <td style={{ fontSize:11.5, color:MUT }}>
                                                    {contact.next_followup
                                                        ? <span style={{ display:'flex', alignItems:'center', gap:4 }}>
                                                            <LuCalendar size={11} style={{ color:OR, flexShrink:0 }}/>{fmtDate(contact.next_followup)}
                                                          </span>
                                                        : '—'}
                                                </td>
                                                <td>
                                                    <span style={{ display:'inline-flex', alignItems:'center', gap:4,
                                                        background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                                                        borderRadius:7, padding:'3px 9px', fontSize:12, fontWeight:700 }}>
                                                        <MdOutlinePhoneInTalk size={12}/>{contact.call_count}
                                                    </span>
                                                </td>
                                                <td style={{ textAlign:'right', paddingRight:18 }}>
                                                    <Link href={`/telecaller/campaigns/${campaign.encrypted_id}/contacts/${contact.encrypted_id}`}
                                                        style={{ display:'inline-flex', alignItems:'center', gap:4,
                                                            padding:'5px 12px', borderRadius:8,
                                                            background:OR, color:'#fff', fontWeight:700,
                                                            fontSize:12, textDecoration:'none',
                                                            boxShadow:'0 2px 8px rgba(255,92,0,0.2)',
                                                            whiteSpace:'nowrap' }}>
                                                        Open <LuExternalLink size={12}/>
                                                    </Link>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                            <div style={{ padding:'10px 18px', borderTop:`1px solid ${BOR}`, background:'#FAFBFC' }}>
                                <Pagination data={contacts}/>
                            </div>
                          </div>
                    }
                </Card>

            </div>
        </>
    );
}
