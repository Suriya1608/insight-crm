import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { LuUsers, LuPhone, LuTrendingUp, LuMegaphone } from 'react-icons/lu';
import { MdOutlinePhoneInTalk } from 'react-icons/md';
import { IoCheckmarkCircleOutline } from 'react-icons/io5';

const OR='#FF5C00',DK='#1D1D1D',WH='#FEFEFE',MUT='#9CA3AF',BOR='#F0F0F0';

const STATUS_CFG = {
    active:    { bg:'#ECFDF5', color:'#16A34A', dot:'#22C55E', label:'Active'    },
    paused:    { bg:'#FFFBEB', color:'#CA8A04', dot:'#EAB308', label:'Paused'    },
    completed: { bg:'#F9FAFB', color:'#6B7280', dot:'#9CA3AF', label:'Completed' },
    draft:     { bg:'#F9FAFB', color:'#6B7280', dot:'#D1D5DB', label:'Draft'     },
};

const ACCENT_CYCLE = [
    { color:OR,        bg:'#FFF7ED', shadow:'rgba(255,92,0,0.2)'   },
    { color:'#10B981', bg:'#ECFDF5', shadow:'rgba(16,185,129,0.2)' },
    { color:'#F59E0B', bg:'#FFFBEB', shadow:'rgba(245,158,11,0.2)' },
    { color:'#8B5CF6', bg:'#FAF5FF', shadow:'rgba(139,92,246,0.2)' },
    { color:'#06B6D4', bg:'#ECFEFF', shadow:'rgba(6,182,212,0.2)'  },
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
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
            gap:10, padding:'14px 20px', borderBottom:`1px solid ${BOR}`,
            background:'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
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

function StatCard({ icon, label, value, orange, sub, progress }) {
    const pct = Math.min(100, Math.max(0, progress ?? 0));
    return (
        <div style={{
            background: orange ? OR : WH, borderRadius:12, padding:'14px 16px',
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 16px rgba(255,92,0,0.25)' : '0 2px 8px rgba(0,0,0,0.04)',
            display:'flex', flexDirection:'column', justifyContent:'space-between',
        }}>
            <div style={{ fontSize:18, color: orange?'rgba(255,255,255,0.85)':MUT, marginBottom:6 }}>{icon}</div>
            <div>
                <div style={{ fontSize:9, fontWeight:600, textTransform:'uppercase', letterSpacing:'0.6px', marginBottom:2,
                    color: orange?'rgba(255,255,255,0.75)':MUT }}>{label}</div>
                <div style={{ fontSize:22, fontWeight:800, lineHeight:1, color: orange?'#fff':DK }}>{value}</div>
                {sub && <div style={{ fontSize:10, color: orange?'rgba(255,255,255,0.6)':MUT, marginTop:2 }}>{sub}</div>}
            </div>
            {progress != null && (
                <div style={{ marginTop:10 }}>
                    <div style={{ height:4, borderRadius:20, background: orange?'rgba(255,255,255,0.2)':'#F3F4F6', overflow:'hidden' }}>
                        <div style={{ height:'100%', width:`${pct}%`, borderRadius:20,
                            background: orange?'rgba(255,255,255,0.7)':OR, transition:'width .6s' }}/>
                    </div>
                    <div style={{ fontSize:9.5, color: orange?'rgba(255,255,255,0.65)':MUT, marginTop:3, textAlign:'right' }}>{pct}%</div>
                </div>
            )}
        </div>
    );
}

function CampaignCard({ campaign, accent }) {
    const [hov,setHov] = useState(false);
    const sc = STATUS_CFG[campaign.status] ?? STATUS_CFG.draft;
    return (
        <div onMouseEnter={()=>setHov(true)} onMouseLeave={()=>setHov(false)}
            style={{ background:WH, borderRadius:14, border:`1px solid ${BOR}`,
                boxShadow: hov?`0 8px 24px ${accent.shadow}`:'0 2px 8px rgba(0,0,0,0.04)',
                transform: hov?'translateY(-3px)':'none',
                transition:'all .2s ease', overflow:'hidden', display:'flex', flexDirection:'column',
                height:'100%', position:'relative' }}>
            {/* top accent */}
            <div style={{ height:3, background:accent.color }}/>
            <div style={{ padding:'18px 20px', flex:1, display:'flex', flexDirection:'column' }}>
                {/* header */}
                <div style={{ display:'flex', alignItems:'flex-start', justifyContent:'space-between', gap:10, marginBottom:14 }}>
                    <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                        <div style={{ width:40, height:40, borderRadius:11, background:accent.bg,
                            display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
                            <LuMegaphone size={19} style={{ color:accent.color }}/>
                        </div>
                        <div style={{ minWidth:0 }}>
                            <div style={{ fontSize:14, fontWeight:700, color:DK, lineHeight:1.2, marginBottom:2 }}>
                                {campaign.name}
                            </div>
                            {campaign.description && (
                                <div style={{ fontSize:11, color:MUT, overflow:'hidden', textOverflow:'ellipsis',
                                    whiteSpace:'nowrap', maxWidth:160 }}>
                                    {campaign.description.length>55?campaign.description.slice(0,55)+'…':campaign.description}
                                </div>
                            )}
                        </div>
                    </div>
                    <span style={{ display:'inline-flex', alignItems:'center', gap:5, background:sc.bg,
                        color:sc.color, fontSize:10, fontWeight:700, padding:'3px 9px', borderRadius:20,
                        whiteSpace:'nowrap', flexShrink:0 }}>
                        <span style={{ width:5, height:5, borderRadius:'50%', background:sc.dot }}/>
                        {sc.label}
                    </span>
                </div>

                {/* contact count */}
                <div style={{ background:accent.bg, borderRadius:10, padding:'10px 14px',
                    marginBottom:14, display:'flex', alignItems:'center', gap:10,
                    border:`1px solid ${accent.color}20` }}>
                    <div style={{ width:34, height:34, borderRadius:9, background:accent.color,
                        display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
                        <LuUsers size={16} style={{ color:'#fff' }}/>
                    </div>
                    <div>
                        <div style={{ fontSize:20, fontWeight:800, color:DK, lineHeight:1 }}>
                            {(campaign.my_contacts_count??0).toLocaleString()}
                        </div>
                        <div style={{ fontSize:10.5, color:accent.color, fontWeight:600, marginTop:1 }}>
                            contacts assigned to you
                        </div>
                    </div>
                </div>

                {/* CTA */}
                <div style={{ marginTop:'auto' }}>
                    <Link href={`/telecaller/campaigns/${campaign.encrypted_id}`}
                        style={{ display:'flex', alignItems:'center', justifyContent:'center', gap:7,
                            width:'100%', padding:'10px 16px', borderRadius:10,
                            background:accent.color, color:'#fff', fontWeight:700, fontSize:13,
                            textDecoration:'none', transition:'opacity .15s',
                            boxShadow:`0 4px 12px ${accent.shadow}` }}
                        onMouseEnter={e=>e.currentTarget.style.opacity='.88'}
                        onMouseLeave={e=>e.currentTarget.style.opacity='1'}>
                        <MdOutlinePhoneInTalk size={17}/> Start Calling
                    </Link>
                </div>
            </div>
        </div>
    );
}

function Pagination({ data }) {
    if (!data || data.last_page <= 1) return null;
    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between',
            marginTop:20, flexWrap:'wrap', gap:10 }}>
            <small style={{ color:MUT }}>
                Showing <strong style={{ color:DK }}>{data.from}–{data.to}</strong> of <strong style={{ color:DK }}>{data.total}</strong>
            </small>
            <div style={{ display:'flex', gap:4 }}>
                {data.links.map((link,i) => link.url
                    ? <Link key={i} href={link.url} dangerouslySetInnerHTML={{ __html:link.label }}
                        style={{ display:'inline-flex', alignItems:'center', justifyContent:'center',
                            minWidth:32, height:32, padding:'0 8px', borderRadius:8,
                            fontSize:12, fontWeight:link.active?700:500, textDecoration:'none',
                            background:link.active?OR:'#F9FAFB',
                            color:link.active?'#fff':'#374151',
                            border:`1px solid ${link.active?OR:'#E5E7EB'}` }}/>
                    : <span key={i} dangerouslySetInnerHTML={{ __html:link.label }}
                        style={{ display:'inline-flex', alignItems:'center', justifyContent:'center',
                            minWidth:32, height:32, padding:'0 8px', borderRadius:8,
                            fontSize:12, color:'#D1D5DB', background:'#F9FAFB', border:'1px solid #E5E7EB' }}/>
                )}
            </div>
        </div>
    );
}

export default function Index({ campaigns, totalStats }) {
    const s = totalStats ?? {};
    const calledPct = s.contacts>0 ? Math.round((s.called/s.contacts)*100) : 0;

    return (
        <>
            <Head title="My Campaigns"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .cp-pg, .cp-pg div, .cp-pg span:not([class*="material"]),
                .cp-pg p,.cp-pg h1,.cp-pg h2,.cp-pg label,.cp-pg button,.cp-pg input,.cp-pg select,.cp-pg a,.cp-pg small {
                    font-family:'Poppins',sans-serif !important;
                }
                .cp-pg { display:flex; flex-direction:column; gap:16px; }
                .cp-kpi { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
                @media(max-width:900px){ .cp-kpi{ grid-template-columns:repeat(2,1fr); } }
            `}</style>

            <div className="cp-pg">

                {/* KPI row */}
                <div className="cp-kpi">
                    <StatCard icon={<LuMegaphone size={17}/>} label="Assigned Campaigns" value={s.total??0} orange/>
                    <StatCard icon={<LuUsers size={17}/>}     label="Total Contacts"     value={(s.contacts??0).toLocaleString()} sub={`${s.pending??0} pending`}/>
                    <StatCard icon={<MdOutlinePhoneInTalk size={17}/>} label="Contacts Called" value={(s.called??0).toLocaleString()} progress={calledPct}/>
                    <StatCard icon={<IoCheckmarkCircleOutline size={17}/>} label="Converted" value={s.converted??0} progress={s.conversion_rate??0}/>
                </div>

                {/* Campaign grid */}
                <Card>
                    <SHead icon={<LuMegaphone size={13}/>} title="My Campaigns" sub="Campaigns assigned to you"
                        right={campaigns.data.length>0 &&
                            <span style={{ background:'#FFF7ED', color:OR, border:'1px solid #FED7AA',
                                fontSize:11, fontWeight:700, padding:'2px 10px', borderRadius:20 }}>
                                {campaigns.total} total
                            </span>}/>
                    <div style={{ padding:'18px 20px' }}>
                        {campaigns.data.length === 0
                            ? <div style={{ textAlign:'center', padding:'52px 0' }}>
                                <div style={{ width:60, height:60, borderRadius:16, background:'#FFF7ED',
                                    display:'flex', alignItems:'center', justifyContent:'center',
                                    margin:'0 auto 14px', fontSize:28 }}>📢</div>
                                <div style={{ fontSize:14, fontWeight:700, color:DK, marginBottom:6 }}>No campaigns assigned yet</div>
                                <div style={{ fontSize:12, color:MUT }}>Your manager hasn't assigned any campaigns to you.</div>
                            </div>
                            : <>
                                <div className="row g-3">
                                    {campaigns.data.map((c,i)=>(
                                        <div key={c.id} className="col-12 col-md-6 col-lg-4">
                                            <CampaignCard campaign={c} accent={ACCENT_CYCLE[i%ACCENT_CYCLE.length]}/>
                                        </div>
                                    ))}
                                </div>
                                <Pagination data={campaigns}/>
                            </>
                        }
                    </div>
                </Card>

            </div>
        </>
    );
}
