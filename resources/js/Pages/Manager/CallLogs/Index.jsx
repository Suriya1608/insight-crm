import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuPhone, LuPhoneMissed, LuArrowUpRight, LuArrowDownLeft,
    LuFilter, LuSearch, LuRefreshCw, LuDownload,
    LuExternalLink, LuCalendar, LuUser,
    LuCheck, LuX, LuClock, LuEye, LuList,
} from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

// ─── Scope config ──────────────────────────────────────────────────────────────
const SCOPES = [
    { key: 'all',      label: 'All Calls', icon: <LuPhone size={14}/>,         accent: DK        },
    { key: 'inbound',  label: 'Inbound',   icon: <LuArrowDownLeft size={14}/>, accent: '#10B981' },
    { key: 'outbound', label: 'Outbound',  icon: <LuArrowUpRight size={14}/>,  accent: OR        },
    { key: 'missed',   label: 'Missed',    icon: <LuPhoneMissed size={14}/>,   accent: '#EF4444' },
];

// ─── Helpers ───────────────────────────────────────────────────────────────────
function StatusBadge({ status }) {
    const map = {
        completed:      { bg: '#ECFDF5', color: '#065F46', dot: '#10B981' },
        answered:       { bg: '#DBEAFE', color: '#1E40AF', dot: '#60A5FA' },
        'in-progress':  { bg: '#E0F2FE', color: '#0369A1', dot: '#7DD3FC' },
        ringing:        { bg: '#FFFBEB', color: '#92400E', dot: '#FCD34D' },
        initiated:      { bg: '#F5F3FF', color: '#7E22CE', dot: '#C4B5FD' },
        missed:         { bg: '#FEF2F2', color: '#991B1B', dot: '#F87171' },
        'no-answer':    { bg: '#FEF2F2', color: '#991B1B', dot: '#F87171' },
        busy:           { bg: '#FEF2F2', color: '#991B1B', dot: '#F87171' },
        failed:         { bg: '#FEF2F2', color: '#991B1B', dot: '#F87171' },
        canceled:       { bg: '#FFFBEB', color: '#713F12', dot: '#FDE68A' },
    };
    const s  = status?.toLowerCase() ?? '';
    const st = map[s] ?? { bg: '#F1F5F9', color: '#475569', dot: '#CBD5E1' };
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4,
            padding: '3px 10px', borderRadius: 20, fontSize: 11, fontWeight: 700,
            background: st.bg, color: st.color, whiteSpace: 'nowrap' }}>
            <span style={{ width: 5, height: 5, borderRadius: '50%', background: st.dot, flexShrink: 0 }}/>
            {status ? status.charAt(0).toUpperCase() + status.slice(1) : '—'}
        </span>
    );
}

function TypeBadge({ type }) {
    const isOut = type === 'outbound';
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4,
            padding: '3px 9px', borderRadius: 20, fontSize: 11, fontWeight: 700,
            background: isOut ? '#FFF7ED' : '#ECFDF5',
            color: isOut ? OR : '#065F46',
            border: `1px solid ${isOut ? '#FED7AA' : '#6EE7B7'}`,
            whiteSpace: 'nowrap' }}>
            {isOut ? <LuArrowUpRight size={11}/> : <LuArrowDownLeft size={11}/>}
            {isOut ? 'Outbound' : 'Inbound'}
        </span>
    );
}

function DurationChip({ fmt }) {
    const isZero = !fmt || fmt === '00:00:00';
    return (
        <span style={{ display: 'inline-block', padding: '3px 9px', borderRadius: 20,
            fontSize: 11, fontWeight: 700, fontFamily: 'monospace',
            background: isZero ? '#F3F4F6' : '#E0F2FE',
            color: isZero ? MUT : '#0369A1',
            border: `1px solid ${isZero ? BOR : '#7DD3FC'}` }}>
            {fmt ?? '00:00:00'}
        </span>
    );
}

// ─── StatRow — telecaller pattern ─────────────────────────────────────────────
function StatRow({ icon, label, value, orange }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
            background: orange ? OR : WH, borderRadius: 10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.2)' : '0 1px 3px rgba(0,0,0,0.04)' }}>
            <div style={{ width: 32, height: 32, borderRadius: 9, flexShrink: 0,
                background: orange ? 'rgba(255,255,255,0.18)' : '#FFF7ED',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: orange ? '#fff' : OR }}>{icon}</div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 9, fontWeight: 600, textTransform: 'uppercase',
                    letterSpacing: '0.5px', marginBottom: 1,
                    color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize: 20, fontWeight: 800, lineHeight: 1,
                    color: orange ? '#fff' : DK }}>{value ?? 0}</div>
            </div>
        </div>
    );
}

// ─── Call detail modal ─────────────────────────────────────────────────────────
function CallDetailModal({ call, onClose }) {
    if (!call) return null;
    const rows = [
        { label: 'Lead',       value: call.lead_name ? `${call.lead_name} (${call.lead_code ?? '—'})` : '—' },
        { label: 'Phone',      value: call.lead_phone ?? '—' },
        { label: 'Type',       value: call.type ? call.type.charAt(0).toUpperCase() + call.type.slice(1) : '—' },
        { label: 'Status',     value: call.status ?? '—' },
        { label: 'Duration',   value: call.duration_fmt ?? '—' },
        { label: 'Telecaller', value: call.telecaller ?? '—' },
        { label: 'Date',       value: call.created_at ?? '—' },
    ];
    return (
        <div className="modal fade show d-block" tabIndex="-1"
            style={{ background: 'rgba(0,0,0,.45)' }} onClick={onClose}>
            <div className="modal-dialog modal-dialog-centered" onClick={e => e.stopPropagation()}>
                <div className="modal-content" style={{ borderRadius: 16, border: 'none', boxShadow: '0 20px 60px rgba(0,0,0,0.2)' }}>
                    <div className="modal-header" style={{ background: DK, borderRadius: '16px 16px 0 0', border: 'none', padding: '14px 20px' }}>
                        <h5 className="modal-title" style={{ color: '#fff', fontWeight: 700, fontSize: 14,
                            display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'Poppins,sans-serif' }}>
                            <LuPhone size={16} color={OR}/> Call Details
                        </h5>
                        <button type="button" className="btn-close btn-close-white" onClick={onClose}/>
                    </div>
                    <div className="modal-body p-0">
                        <table className="table table-sm mb-0">
                            <tbody>
                                {rows.map(r => (
                                    <tr key={r.label}>
                                        <th className="ps-3 py-2 fw-semibold" style={{ width: '35%', background: '#F4F6F8', color: MUT, fontSize: 11.5, fontFamily: 'Poppins,sans-serif' }}>{r.label}</th>
                                        <td className="ps-3 py-2" style={{ fontSize: 12.5, color: DK, fontFamily: 'Poppins,sans-serif' }}>{r.value}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="modal-footer" style={{ borderTop: `1px solid ${BOR}`, padding: '10px 18px' }}>
                        <button type="button" onClick={onClose}
                            style={{ background: '#F3F4F6', color: BDY, border: 'none', borderRadius: 8,
                                fontWeight: 600, padding: '7px 16px', fontSize: 12, cursor: 'pointer',
                                fontFamily: 'Poppins,sans-serif' }}>Close</button>
                        {call.encrypted_lead_id && (
                            <Link href={`/manager/leads/${call.encrypted_lead_id}`}
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
                                    padding: '7px 16px', borderRadius: 8, fontSize: 12, fontWeight: 600,
                                    background: OR, color: '#fff', textDecoration: 'none',
                                    fontFamily: 'Poppins,sans-serif' }}>
                                <LuExternalLink size={13}/> View Lead
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

// ─── Input helpers ─────────────────────────────────────────────────────────────
const inputBase = { borderRadius: 8, border: `1px solid #E5E7EB`, fontSize: 12.5, height: 34,
    background: '#FAFBFC', color: DK, width: '100%', padding: '0 10px',
    fontFamily: 'Poppins,sans-serif', outline: 'none', transition: 'border-color .15s, box-shadow .15s' };
function FI({ style: s, ...p }) {
    const [f, sf] = useState(false);
    return <input {...p} style={{ ...inputBase, ...(f ? { borderColor: OR, boxShadow: '0 0 0 3px rgba(255,92,0,0.09)', background: '#fff' } : {}), ...s }}
        onFocus={() => sf(true)} onBlur={() => sf(false)}/>;
}
function FS({ style: s, children, ...p }) {
    const [f, sf] = useState(false);
    return <select {...p} style={{ ...inputBase, ...(f ? { borderColor: OR, boxShadow: '0 0 0 3px rgba(255,92,0,0.09)', background: '#fff' } : {}), ...s }}>
        {children}</select>;
}

// ─── Main ─────────────────────────────────────────────────────────────────────
export default function Index({ callLogs, telecallers, statusOptions, scope, filters, kpi = {} }) {
    const [date,         setDate]        = useState(filters?.date       ?? '');
    const [telecaller,   setTelecaller]  = useState(filters?.telecaller ?? '');
    const [status,       setStatus]      = useState(filters?.status     ?? '');
    const [activeCall,   setActiveCall]  = useState(null);
    const [emailSending, setEmailSending] = useState(false);
    const [emailMsg,     setEmailMsg]    = useState('');

    function buildParams(overrideScope) {
        const p = { scope: overrideScope ?? scope };
        if (date)       p.date       = date;
        if (telecaller) p.telecaller = telecaller;
        if (status)     p.status     = status;
        return p;
    }
    function applyFilters(e) { e.preventDefault(); router.get('/manager/call-logs', buildParams(), { preserveState: false }); }
    function resetFilters()  { setDate(''); setTelecaller(''); setStatus(''); router.get('/manager/call-logs', { scope }, { preserveState: false }); }
    function switchScope(s)  { router.get('/manager/call-logs', buildParams(s), { preserveState: false }); }

    function exportPdfUrl() {
        const p = new URLSearchParams({ scope });
        if (date)       p.set('date',       date);
        if (telecaller) p.set('telecaller', telecaller);
        if (status)     p.set('status',     status);
        return `/manager/call-logs/export/pdf?${p}`;
    }
    function handleEmail() {
        setEmailSending(true); setEmailMsg('');
        const p = new URLSearchParams({ scope });
        if (date)       p.set('date',       date);
        if (telecaller) p.set('telecaller', telecaller);
        if (status)     p.set('status',     status);
        fetch(`/manager/call-logs/export/email?${p}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '', 'Accept': 'application/json' },
        }).then(r => r.json()).then(d => setEmailMsg(d.message ?? 'Sent!')).catch(() => setEmailMsg('Failed.')).finally(() => setEmailSending(false));
    }

    const k = kpi;
    const successRate = k.total > 0 ? Math.round((k.completed / k.total) * 100) : 0;
    const activeScope = SCOPES.find(s => s.key === scope) ?? SCOPES[0];
    const scopeColor  = activeScope.accent;

    return (
        <>
            <Head title="Call Logs"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .cl-pg, .cl-pg div, .cl-pg span:not([class*="material"]),
                .cl-pg p, .cl-pg h1, .cl-pg h2, .cl-pg h3,
                .cl-pg button, .cl-pg input, .cl-pg select, .cl-pg a,
                .cl-pg th, .cl-pg td, .cl-pg label, .cl-pg small {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .cl-pg { display: flex; flex-direction: column; gap: 14px; }

                .cl-kpi { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; }
                @media(max-width:960px){ .cl-kpi{ grid-template-columns:repeat(2,1fr); } }

                .cl-body {
                    display: grid;
                    grid-template-columns: 220px 1fr;
                    gap: 14px;
                    align-items: start;
                }
                @media(max-width:900px){ .cl-body{ grid-template-columns:1fr; } }

                /* scope tab cards */
                .cl-scope-stack { display: flex; flex-direction: column; gap: 7px; }

                /* filter inputs */
                .cl-lbl { font-size: 9.5px; font-weight: 700; color: ${MUT}; text-transform: uppercase;
                    letter-spacing: .5px; display: block; margin-bottom: 4px; }

                /* table */
                .cl-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
                .cl-tbl thead th {
                    position: sticky; top: 0; z-index: 2;
                    background: #F4F6F8; color: ${MUT}; font-size: 9.5px; font-weight: 700;
                    text-transform: uppercase; letter-spacing: .8px;
                    padding: 10px 13px; white-space: nowrap;
                    border-bottom: 2px solid ${BOR};
                }
                .cl-tbl tbody td {
                    padding: 10px 13px; vertical-align: middle;
                    font-size: 12px; color: ${BDY};
                    border-bottom: 1px solid #F4F6F8;
                    transition: background .08s;
                }
                .cl-tbl tbody tr:last-child td { border-bottom: none; }
                .cl-tbl tbody tr:nth-child(even) td { background: #FAFBFC; }
                .cl-tbl tbody tr:hover td { background: #FFF7ED !important; }
                .cl-tbl tbody tr:hover td:first-child { border-left: 3px solid ${scopeColor}; padding-left: 15px; }

                .cl-scroll { overflow-y: auto; overflow-x: auto; max-height: 500px; }
                .cl-scroll::-webkit-scrollbar { width: 5px; }
                .cl-scroll::-webkit-scrollbar-track { background: #F4F6F8; }
                .cl-scroll::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }
                .cl-scroll::-webkit-scrollbar-thumb:hover { background: ${OR}; }

                .cl-act-btn {
                    width: 28px; height: 28px; border-radius: 7px; border: 1px solid ${BOR};
                    background: #F3F4F6; color: ${MUT}; display: inline-flex; align-items: center;
                    justify-content: center; cursor: pointer; transition: all .15s; text-decoration: none;
                }
                .cl-act-btn:hover { background: ${OR}; border-color: ${OR}; color: #fff; }

                .cl-badge { background: #FFF7ED; color: ${OR}; border: 1px solid #FED7AA;
                    font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px; }

                .cl-pager { padding: 10px 18px; border-top: 1px solid ${BOR};
                    display: flex; align-items: center; justify-content: space-between;
                    flex-wrap: wrap; gap: 9px; background: #FAFBFC; }
                .cl-pager .page-link { background: ${WH}; border-color: #E5E7EB; color: ${BDY}; font-size: 11.5px; border-radius: 7px; padding: 4px 9px; }
                .cl-pager .page-item.active .page-link { background: ${scopeColor}; border-color: ${scopeColor}; color: #fff; }
                .cl-pager .page-item.disabled .page-link { opacity: .4; }
            `}</style>

            <div className="cl-pg">
                {activeCall && <CallDetailModal call={activeCall} onClose={() => setActiveCall(null)}/>}

                {/* ── KPI row ── */}
                <div className="cl-kpi">
                    <StatRow icon={<LuPhone size={15}/>}         label="Total Calls" value={k.total ?? 0}    orange={true}  />
                    <StatRow icon={<LuArrowDownLeft size={15}/>}  label="Inbound"     value={k.inbound ?? 0}  orange={false} />
                    <StatRow icon={<LuArrowUpRight size={15}/>}   label="Outbound"    value={k.outbound ?? 0} orange={false} />
                    <StatRow icon={<LuPhoneMissed size={15}/>}    label="Missed"      value={k.missed ?? 0}   orange={false} />
                </div>

                {/* ── Two-column: scope+filter left | table right ── */}
                <div className="cl-body">

                    {/* LEFT — scope tabs + filter */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>

                        {/* Scope cards */}
                        <div className="cl-scope-stack">
                            {SCOPES.map(sc => {
                                const on = scope === sc.key;
                                return (
                                    <button key={sc.key} type="button" onClick={() => switchScope(sc.key)}
                                        style={{ background: on ? sc.accent : WH, borderRadius: 11,
                                            border: on ? 'none' : `1px solid ${BOR}`,
                                            boxShadow: on ? `0 4px 14px ${sc.accent}30` : '0 1px 3px rgba(0,0,0,0.04)',
                                            padding: '11px 14px', display: 'flex', alignItems: 'center', gap: 10,
                                            transition: 'all .15s', cursor: 'pointer', width: '100%', textAlign: 'left' }}>
                                        <div style={{ width: 32, height: 32, borderRadius: 9, flexShrink: 0,
                                            background: on ? 'rgba(255,255,255,0.18)' : `${sc.accent}15`,
                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                            color: on ? '#fff' : sc.accent }}>{sc.icon}</div>
                                        <div>
                                            <div style={{ fontSize: 12.5, fontWeight: 700,
                                                color: on ? '#fff' : DK }}>{sc.label}</div>
                                            <div style={{ fontSize: 10, color: on ? 'rgba(255,255,255,0.65)' : MUT, marginTop: 1 }}>
                                                {sc.key === 'all'      ? `${k.total ?? 0} calls` :
                                                 sc.key === 'inbound'  ? `${k.inbound ?? 0} calls` :
                                                 sc.key === 'outbound' ? `${k.outbound ?? 0} calls` :
                                                                         `${k.missed ?? 0} calls`}
                                            </div>
                                        </div>
                                        {on && <div style={{ marginLeft: 'auto', width: 6, height: 6, borderRadius: '50%',
                                            background: 'rgba(255,255,255,0.6)' }}/>}
                                    </button>
                                );
                            })}
                        </div>

                        {/* Filter card */}
                        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                            boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 16px',
                                borderBottom: `1px solid ${BOR}`,
                                background: 'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
                                <div style={{ width: 3, height: 20, borderRadius: 2, background: OR }}/>
                                <LuFilter size={13} color={OR}/>
                                <span style={{ fontSize: 12.5, fontWeight: 700, color: DK }}>Filters</span>
                            </div>
                            <div style={{ padding: '12px 14px' }}>
                                <form onSubmit={applyFilters}>
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 9 }}>
                                        <div>
                                            <label className="cl-lbl"><LuCalendar size={10}/> Date</label>
                                            <FI type="date" value={date} onChange={e => setDate(e.target.value)}/>
                                        </div>
                                        <div>
                                            <label className="cl-lbl"><LuUser size={10}/> Telecaller</label>
                                            <FS value={telecaller} onChange={e => setTelecaller(e.target.value)}>
                                                <option value="">All Telecallers</option>
                                                {telecallers.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                            </FS>
                                        </div>
                                        <div>
                                            <label className="cl-lbl"><LuPhone size={10}/> Status</label>
                                            <FS value={status} onChange={e => setStatus(e.target.value)}>
                                                <option value="">All Statuses</option>
                                                {statusOptions.map(s => <option key={s} value={s}>{s.charAt(0).toUpperCase()+s.slice(1)}</option>)}
                                            </FS>
                                        </div>
                                        <button type="submit"
                                            style={{ width: '100%', background: OR, color: '#fff', border: 'none',
                                                borderRadius: 8, padding: '8px', fontSize: 12.5, fontWeight: 600,
                                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                gap: 6, cursor: 'pointer', marginTop: 2 }}>
                                            <LuSearch size={13}/> Apply
                                        </button>
                                        <button type="button" onClick={resetFilters}
                                            style={{ width: '100%', background: WH, color: BDY, border: `1px solid #E5E7EB`,
                                                borderRadius: 8, padding: '7px', fontSize: 12, fontWeight: 600,
                                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                gap: 5, cursor: 'pointer' }}>
                                            <LuRefreshCw size={11}/> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {/* RIGHT — table card */}
                    <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                        boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden' }}>

                        {/* SHead */}
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                            gap: 10, padding: '13px 18px', borderBottom: `1px solid ${BOR}`,
                            background: 'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                                <div style={{ width: 3, height: 30, borderRadius: 2, background: scopeColor, flexShrink: 0 }}/>
                                <div>
                                    <div style={{ fontSize: 13.5, fontWeight: 700, color: DK, display: 'flex', alignItems: 'center', gap: 6 }}>
                                        <span style={{ color: scopeColor }}><LuList size={13}/></span>
                                        {activeScope.label} List
                                    </div>
                                    <div style={{ fontSize: 11, color: MUT, marginTop: 1 }}>{callLogs.total} records found</div>
                                </div>
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <span className="cl-badge">{callLogs.total}</span>
                                <div className="dropdown">
                                    <button type="button" data-bs-toggle="dropdown"
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
                                            background: WH, color: BDY, border: `1px solid ${BOR}`,
                                            borderRadius: 8, padding: '6px 12px', fontSize: 12, fontWeight: 600, cursor: 'pointer' }}>
                                        <LuDownload size={13} style={{ color: '#10B981' }}/> Export
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end"
                                        style={{ borderRadius: 10, border: `1px solid ${BOR}`, padding: 5, minWidth: 160 }}>
                                        <li><a className="dropdown-item" href={exportPdfUrl()} target="_blank" rel="noreferrer"
                                            style={{ fontSize: 12, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 7, fontWeight: 600, borderRadius: 8 }}>
                                            <span>📄</span> Download PDF
                                        </a></li>
                                        <li><button type="button" className="dropdown-item" disabled={emailSending} onClick={handleEmail}
                                            style={{ fontSize: 12, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 7, fontWeight: 600, borderRadius: 8, width: '100%', background: 'none', border: 'none', cursor: emailSending ? 'default' : 'pointer' }}>
                                            {emailSending ? <LuClock size={13} style={{ color: MUT }}/> : <LuCheck size={13} style={{ color: '#10B981' }}/>}
                                            {emailSending ? 'Sending…' : 'Email Report'}
                                        </button></li>
                                    </ul>
                                </div>
                                {emailMsg && (
                                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 11, fontWeight: 600,
                                        background: emailMsg.startsWith('Failed') ? '#FEF2F2' : '#F0FDF4',
                                        color: emailMsg.startsWith('Failed') ? '#DC2626' : '#16A34A',
                                        padding: '4px 9px', borderRadius: 8 }}>
                                        {emailMsg.startsWith('Failed') ? <LuX size={11}/> : <LuCheck size={11}/>} {emailMsg}
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="cl-scroll">
                            <table className="cl-tbl">
                                <thead>
                                    <tr>
                                        <th style={{ width: 38 }}>#</th>
                                        <th>Date &amp; Time</th>
                                        <th>Code</th>
                                        <th>Lead</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Telecaller</th>
                                        <th style={{ textAlign: 'right', paddingRight: 14 }}>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {callLogs.data.length === 0 ? (
                                        <tr><td colSpan={9}>
                                            <div style={{ textAlign: 'center', padding: '52px 0 48px' }}>
                                                <div style={{ width: 60, height: 60, borderRadius: 16,
                                                    background: scope === 'missed' ? '#FEF2F2' : '#FFF7ED',
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    margin: '0 auto 14px' }}>
                                                    {scope === 'missed'
                                                        ? <LuPhoneMissed size={28} color="#F87171"/>
                                                        : <LuPhone size={28} color={OR} style={{ opacity: .5 }}/>}
                                                </div>
                                                <div style={{ fontSize: 14, fontWeight: 700, color: DK, marginBottom: 6 }}>
                                                    {scope === 'missed' ? 'No missed calls' : 'No calls found'}
                                                </div>
                                                <div style={{ fontSize: 12, color: MUT, maxWidth: 250, margin: '0 auto', lineHeight: 1.7 }}>
                                                    {scope === 'missed' ? 'No missed calls for the selected filters.' : 'Try adjusting the filters.'}
                                                </div>
                                            </div>
                                        </td></tr>
                                    ) : callLogs.data.map((call, idx) => {
                                        const sno = (callLogs.current_page - 1) * callLogs.per_page + idx + 1;
                                        return (
                                            <tr key={call.id}>
                                                <td style={{ color: MUT, fontSize: 10.5, fontWeight: 600 }}>{sno}</td>
                                                <td>
                                                    <div style={{ fontSize: 12.5, fontWeight: 600, color: DK }}>{call.created_at?.split(',')[0]}</div>
                                                    <div style={{ fontSize: 10.5, color: MUT, marginTop: 1 }}>{call.created_at?.split(',')?.[1]?.trim()}</div>
                                                </td>
                                                <td>
                                                    <span style={{ fontSize: 11, fontWeight: 700, color: OR, background: '#FFF7ED',
                                                        padding: '2px 8px', borderRadius: 6, border: `1px solid #FED7AA` }}>
                                                        {call.lead_code}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style={{ fontWeight: 700, color: DK, fontSize: 12.5 }}>{call.lead_name}</div>
                                                    <div style={{ fontSize: 10.5, color: MUT, marginTop: 1, display: 'flex', alignItems: 'center', gap: 3 }}>
                                                        <LuPhone size={10}/>{call.lead_phone}
                                                    </div>
                                                </td>
                                                <td><TypeBadge type={call.type}/></td>
                                                <td><StatusBadge status={call.status}/></td>
                                                <td><DurationChip fmt={call.duration_fmt}/></td>
                                                <td style={{ fontSize: 12, fontWeight: 600, color: BDY }}>{call.telecaller}</td>
                                                <td style={{ paddingRight: 14 }}>
                                                    <div style={{ display: 'flex', gap: 5, justifyContent: 'flex-end' }}>
                                                        <button type="button" className="cl-act-btn" title="Details"
                                                            onClick={() => setActiveCall(call)}>
                                                            <LuEye size={13}/>
                                                        </button>
                                                        {call.encrypted_lead_id && (
                                                            <Link href={`/manager/leads/${call.encrypted_lead_id}`}
                                                                className="cl-act-btn" title="View Lead">
                                                                <LuExternalLink size={13}/>
                                                            </Link>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        <div className="cl-pager">
                            <small style={{ color: MUT }}>{callLogs.from ?? 0}–{callLogs.to ?? 0} of {callLogs.total} results</small>
                            {callLogs.last_page > 1 && (
                                <nav>
                                    <ul className="pagination pagination-sm mb-0" style={{ gap: 2 }}>
                                        {callLogs.links.map((link, i) => (
                                            <li key={i} className={['page-item', link.active ? 'active' : '', !link.url ? 'disabled' : ''].join(' ')}>
                                                {link.url
                                                    ? <Link href={link.url} className="page-link" dangerouslySetInnerHTML={{ __html: link.label }}/>
                                                    : <span className="page-link" dangerouslySetInnerHTML={{ __html: link.label }}/>}
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            )}
                        </div>
                    </div>

                </div>
            </div>
        </>
    );
}
