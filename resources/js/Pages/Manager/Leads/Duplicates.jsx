import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuCopy, LuPhone, LuUser, LuSearch, LuDownload,
    LuExternalLink, LuRefreshCw, LuUsers,
} from 'react-icons/lu';

// ─── Design tokens ─────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

// ─── Status config ──────────────────────────────────────────────────────────────
const ST = {
    new:            { label: 'New',            bg: '#FFF7ED', c: OR,        b: '#FED7AA', dot: OR        },
    assigned:       { label: 'Assigned',       bg: '#F0FDF4', c: '#16A34A', b: '#BBF7D0', dot: '#16A34A' },
    contacted:      { label: 'Contacted',      bg: '#EFF6FF', c: '#1D4ED8', b: '#BFDBFE', dot: '#1D4ED8' },
    interested:     { label: 'Interested',     bg: '#FFFBEB', c: '#B45309', b: '#FDE68A', dot: '#F59E0B' },
    follow_up:      { label: 'Follow-up',      bg: '#FDF4FF', c: '#7E22CE', b: '#E9D5FF', dot: '#8B5CF6' },
    not_interested: { label: 'Not Interested', bg: '#FFF1F2', c: '#BE123C', b: '#FECDD3', dot: '#EF4444' },
    converted:      { label: 'Converted',      bg: '#ECFDF5', c: '#047857', b: '#6EE7B7', dot: '#10B981' },
    lost:           { label: 'Lost',           bg: '#F9FAFB', c: '#6B7280', b: '#E5E7EB', dot: '#9CA3AF' },
};

function StatusBadge({ status }) {
    const c = ST[status] ?? { label: status, bg: '#F3F4F6', c: '#6B7280', b: '#E5E7EB', dot: '#9CA3AF' };
    return (
        <span style={{ background: c.bg, color: c.c, border: `1px solid ${c.b}`,
            fontSize: 11, fontWeight: 700, padding: '3px 10px', borderRadius: 20,
            whiteSpace: 'nowrap', display: 'inline-flex', alignItems: 'center', gap: 5 }}>
            <span style={{ width: 5, height: 5, borderRadius: '50%', background: c.dot, flexShrink: 0 }} />
            {c.label}
        </span>
    );
}

function AgingBadge({ days }) {
    if (days == null) return null;
    if (days >= 6) return <span style={{ background: '#FEF2F2', color: '#DC2626', border: '1px solid #FECACA', fontSize: 9.5, fontWeight: 700, padding: '2px 7px', borderRadius: 20 }}>{days}d old</span>;
    if (days >= 3) return <span style={{ background: '#FFFBEB', color: '#D97706', border: '1px solid #FDE68A', fontSize: 9.5, fontWeight: 700, padding: '2px 7px', borderRadius: 20 }}>{days}d</span>;
    return <span style={{ background: '#FFF7ED', color: OR, border: `1px solid #FED7AA`, fontSize: 9.5, fontWeight: 700, padding: '2px 7px', borderRadius: 20 }}>Hot</span>;
}

// ─── StatRow — same as telecaller leads ────────────────────────────────────────
function StatRow({ icon, label, value, orange }) {
    return (
        <div style={{
            display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
            background: orange ? OR : WH, borderRadius: 10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.2)' : '0 1px 3px rgba(0,0,0,0.04)',
        }}>
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

// ─── Section heading — orange left bar (telecaller pattern) ────────────────────
function SHead({ icon, title, sub, right }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            gap: 10, padding: '14px 20px', borderBottom: `1px solid ${BOR}`,
            background: 'linear-gradient(135deg,#FAFBFC 0%,#FFFFFF 100%)' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{ width: 3, height: 32, borderRadius: 2, background: OR, flexShrink: 0 }} />
                <div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                        {icon && <span style={{ color: OR }}>{icon}</span>}
                        <span style={{ fontSize: 13.5, fontWeight: 700, color: DK }}>{title}</span>
                    </div>
                    {sub && <div style={{ fontSize: 11, color: MUT, marginTop: 1 }}>{sub}</div>}
                </div>
            </div>
            {right && <div style={{ flexShrink: 0 }}>{right}</div>}
        </div>
    );
}

function Card({ children, style = {} }) {
    return (
        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
            boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden', ...style }}>
            {children}
        </div>
    );
}

function fmtSource(s) {
    if (!s) return '—';
    return s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

const AVATARS = [
    ['#FF5C00', '#FF8C4A'], ['#10B981', '#34D399'], ['#F59E0B', '#FCD34D'],
    ['#EF4444', '#F87171'], ['#8B5CF6', '#A78BFA'], ['#06B6D4', '#67E8F9'],
];

export default function Duplicates({
    leads,
    filters,
    totalDuplicates = 0,
    phoneDuplicates = 0,
    emailDuplicates = 0,
    unassigned = 0,
}) {
    const [search, setSearch] = useState(filters?.search ?? '');

    function applySearch(e) {
        e.preventDefault();
        router.get('/manager/leads/duplicates', search ? { search } : {}, { preserveState: false });
    }

    function reset() {
        setSearch('');
        router.get('/manager/leads/duplicates', {}, { preserveState: false });
    }

    const exportUrl = (extra = {}) => {
        const p = new URLSearchParams({ is_duplicate: '1', ...(search ? { search } : {}), ...extra });
        return `/manager/leads/export?${p}`;
    };

    return (
        <>
            <Head title="Duplicate Leads" />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .dup-pg, .dup-pg div, .dup-pg span:not([class*="material"]),
                .dup-pg p, .dup-pg h1, .dup-pg h2, .dup-pg h3,
                .dup-pg button, .dup-pg input, .dup-pg select, .dup-pg a,
                .dup-pg th, .dup-pg td, .dup-pg label, .dup-pg small {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .dup-pg { display: flex; flex-direction: column; gap: 14px; }

                .dup-kpi { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; }
                @media(max-width:960px){ .dup-kpi{ grid-template-columns:repeat(2,1fr); } }

                /* search input */
                .dup-si {
                    border-radius: 8px; border: 1px solid #E5E7EB; font-size: 12.5px; height: 34px;
                    background: #FAFBFC; color: ${DK}; width: 100%; padding: 0 10px 0 36px;
                    outline: none; transition: border-color .15s, box-shadow .15s;
                }
                .dup-si:focus {
                    border-color: ${OR};
                    box-shadow: 0 0 0 3px rgba(255,92,0,0.09);
                    background: #fff;
                }

                /* table */
                .dup-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
                .dup-tbl thead th {
                    position: sticky; top: 0; z-index: 2;
                    background: #F4F6F8; color: ${MUT}; font-size: 9.5px; font-weight: 700;
                    text-transform: uppercase; letter-spacing: .8px;
                    padding: 10px 14px; white-space: nowrap;
                    border-bottom: 2px solid ${BOR};
                }
                .dup-tbl tbody td {
                    padding: 11px 14px; vertical-align: middle;
                    font-size: 12px; color: ${BDY};
                    border-bottom: 1px solid #F4F6F8;
                    transition: background .08s;
                }
                .dup-tbl tbody tr:last-child td { border-bottom: none; }
                .dup-tbl tbody tr:nth-child(even) td { background: #FAFBFC; }
                .dup-tbl tbody tr:hover td { background: #FFF7ED !important; cursor: pointer; }
                .dup-tbl tbody tr:hover td:first-child { border-left: 3px solid ${OR}; padding-left: 16px; }

                .dup-scroll { overflow-y: auto; max-height: 520px; }
                .dup-scroll::-webkit-scrollbar { width: 5px; }
                .dup-scroll::-webkit-scrollbar-track { background: #F4F6F8; }
                .dup-scroll::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }
                .dup-scroll::-webkit-scrollbar-thumb:hover { background: ${OR}; }

                .dup-code { font-size: 10.5px; font-weight: 700; background: #F3F4F6; color: #4B5563;
                    border: 1px solid #E5E7EB; padding: 2px 7px; border-radius: 5px; white-space: nowrap;
                    font-family: monospace !important; }
                .dup-view {
                    display: inline-flex; align-items: center; gap: 3px;
                    padding: 4px 10px; border-radius: 7px; font-size: 11px; font-weight: 600;
                    color: ${BDY}; background: #F3F4F6; border: 1px solid #E5E7EB;
                    text-decoration: none; transition: all .15s; white-space: nowrap;
                }
                .dup-view:hover { background: ${OR}; color: #fff; border-color: ${OR}; }

                .dup-badge { background: #FFF7ED; color: ${OR}; border: 1px solid #FED7AA;
                    font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px; }

                /* pagination */
                .dup-pager { padding: 10px 18px; border-top: 1px solid ${BOR};
                    display: flex; align-items: center; justify-content: space-between;
                    flex-wrap: wrap; gap: 9px; background: #FAFBFC; }
                .dup-pager .page-link { background: ${WH}; border-color: #E5E7EB;
                    color: ${BDY}; font-size: 11.5px; border-radius: 7px; padding: 4px 9px; }
                .dup-pager .page-item.active .page-link { background: ${OR}; border-color: ${OR}; color: #fff; }
                .dup-pager .page-item.disabled .page-link { opacity: .4; }

                /* export */
                .dup-exp { border-radius: 9px; border: 1px solid #E5E7EB; overflow: hidden; min-width: 150px; }
                .dup-exp .dropdown-item { font-size: 12px; padding: 8px 13px;
                    display: flex; align-items: center; gap: 7px; color: ${BDY}; }
                .dup-exp .dropdown-item:hover { background: ${DK}; color: #fff; }
            `}</style>

            <div className="dup-pg">

                {/* ── KPI Row ── */}
                <div className="dup-kpi">
                    <StatRow icon={<LuCopy size={15}/>}   label="Total Duplicates" value={totalDuplicates} orange={true}  />
                    <StatRow icon={<LuPhone size={15}/>}  label="Phone Duplicates" value={phoneDuplicates} orange={false} />
                    <StatRow icon={<LuUsers size={15}/>}  label="Email Duplicates" value={emailDuplicates} orange={false} />
                    <StatRow icon={<LuUser size={15}/>}   label="Unassigned"       value={unassigned}      orange={false} />
                </div>

                {/* ── Search + Table card ── */}
                <Card>
                    <SHead
                        icon={<LuCopy size={13}/>}
                        title="Duplicate Lead List"
                        sub="Leads sharing the same mobile number or email address"
                        right={
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <span className="dup-badge">{leads.total} records</span>
                                <div className="dropdown">
                                    <button type="button" data-bs-toggle="dropdown"
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
                                            background: WH, color: BDY, border: `1px solid ${BOR}`,
                                            borderRadius: 8, padding: '6px 12px', fontSize: 12, fontWeight: 600, cursor: 'pointer' }}>
                                        <LuDownload size={13} style={{ color: '#10B981' }}/> Export
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow-sm dup-exp">
                                        <li><a className="dropdown-item" href={exportUrl()}
                                            onClick={e => { e.preventDefault(); window.location.href = exportUrl(); }}>
                                            <span>📊</span> Excel (.xlsx)</a></li>
                                        <li><a className="dropdown-item" href={exportUrl({ format: 'pdf' })}
                                            onClick={e => { e.preventDefault(); window.location.href = exportUrl({ format: 'pdf' }); }}>
                                            <span>📄</span> PDF Report</a></li>
                                    </ul>
                                </div>
                            </div>
                        }
                    />

                    {/* Search bar */}
                    <div style={{ padding: '12px 20px', borderBottom: `1px solid ${BOR}`, background: WH }}>
                        <form onSubmit={applySearch}>
                            <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' }}>
                                <div style={{ position: 'relative', flex: '1 1 260px', maxWidth: 420 }}>
                                    <LuSearch size={14} style={{ position: 'absolute', left: 10, top: '50%',
                                        transform: 'translateY(-50%)', color: MUT, pointerEvents: 'none' }}/>
                                    <input type="text" className="dup-si"
                                        placeholder="Name, phone, email or lead code…"
                                        value={search} onChange={e => setSearch(e.target.value)}/>
                                </div>
                                <button type="submit"
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6,
                                        background: OR, color: '#fff', border: 'none',
                                        borderRadius: 8, padding: '7px 16px', fontSize: 12.5,
                                        fontWeight: 600, cursor: 'pointer' }}>
                                    <LuSearch size={13}/> Search
                                </button>
                                <button type="button" onClick={reset}
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 5,
                                        background: WH, color: BDY, border: `1px solid #E5E7EB`,
                                        borderRadius: 8, padding: '7px 14px', fontSize: 12,
                                        fontWeight: 600, cursor: 'pointer' }}>
                                    <LuRefreshCw size={12}/> Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Table */}
                    <div className="dup-scroll">
                        <table className="dup-tbl">
                            <thead>
                                <tr>
                                    <th style={{ width: 40 }}>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th style={{ textAlign: 'right', paddingRight: 16 }}>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {leads.data.length === 0 ? (
                                    <tr><td colSpan={10}>
                                        <div style={{ textAlign: 'center', padding: '52px 0 48px' }}>
                                            <div style={{ width: 60, height: 60, borderRadius: 16, background: '#FFF7ED',
                                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                margin: '0 auto 14px', boxShadow: '0 4px 14px rgba(255,92,0,0.1)' }}>
                                                <LuCopy size={28} color={OR}/>
                                            </div>
                                            <div style={{ fontSize: 14, fontWeight: 700, color: DK, marginBottom: 6 }}>No duplicate leads found</div>
                                            <div style={{ fontSize: 12, color: MUT, maxWidth: 240, margin: '0 auto', lineHeight: 1.7 }}>
                                                No leads share the same mobile number or email address.
                                            </div>
                                        </div>
                                    </td></tr>
                                ) : leads.data.map((lead, idx) => {
                                    const sno = (leads.current_page - 1) * leads.per_page + idx + 1;
                                    const ai  = Math.abs((lead.id ?? idx) % AVATARS.length);
                                    const [c1, c2] = AVATARS[ai];
                                    return (
                                        <tr key={lead.id} onClick={() => router.visit(`/manager/leads/${lead.encrypted_id}`)}>
                                            <td style={{ color: MUT, fontSize: 10.5, fontWeight: 600 }}>{sno}</td>
                                            <td><span className="dup-code">{lead.lead_code}</span></td>
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                                    <div style={{ width: 30, height: 30, borderRadius: 8, flexShrink: 0,
                                                        background: `linear-gradient(135deg,${c1},${c2})`,
                                                        color: '#fff', fontSize: 12, fontWeight: 800,
                                                        display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                        {(lead.name || '?')[0].toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div style={{ display: 'flex', alignItems: 'center', gap: 5, flexWrap: 'wrap' }}>
                                                            <span style={{ fontSize: 12.5, fontWeight: 700, color: DK }}>{lead.name}</span>
                                                            <span style={{ background: '#FFF7ED', color: OR, border: `1px solid #FED7AA`, fontSize: 9, fontWeight: 700, padding: '1px 6px', borderRadius: 20 }}>DUP</span>
                                                            <AgingBadge days={lead.days_aged}/>
                                                        </div>
                                                        {lead.email && <div style={{ fontSize: 10, color: MUT, marginTop: 1 }}>{lead.email}</div>}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: 11.5, fontWeight: 600, color: DK }}>
                                                    <LuPhone size={10} style={{ color: MUT }}/>
                                                    {lead.phone || '—'}
                                                </div>
                                            </td>
                                            <td style={{ fontSize: 11.5, color: BDY }}>{lead.email || '—'}</td>
                                            <td>
                                                <span style={{ background: '#F3F4F6', color: '#4B5563', border: `1px solid #E5E7EB`,
                                                    fontSize: 10.5, fontWeight: 600, padding: '2px 8px', borderRadius: 5,
                                                    display: 'inline-block', whiteSpace: 'nowrap' }}>
                                                    {fmtSource(lead.source)}
                                                </span>
                                            </td>
                                            <td><StatusBadge status={lead.status}/></td>
                                            <td>
                                                {lead.assigned_user
                                                    ? <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                                        <div style={{ width: 24, height: 24, borderRadius: 7, background: '#FFF7ED',
                                                            display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                            <LuUser size={12} color={OR}/>
                                                        </div>
                                                        <span style={{ fontSize: 11.5, fontWeight: 600, color: BDY }}>{lead.assigned_user}</span>
                                                      </div>
                                                    : <span style={{ color: MUT }}>—</span>}
                                            </td>
                                            <td style={{ fontSize: 11, color: MUT }}>{lead.created_at}</td>
                                            <td style={{ paddingRight: 14 }} onClick={e => e.stopPropagation()}>
                                                <Link href={`/manager/leads/${lead.encrypted_id}`} className="dup-view">
                                                    <LuExternalLink size={11}/> View
                                                </Link>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="dup-pager">
                        <small style={{ color: MUT }}>{leads.from ?? 0}–{leads.to ?? 0} of {leads.total} results</small>
                        {leads.last_page > 1 && (
                            <nav>
                                <ul className="pagination pagination-sm mb-0" style={{ gap: 2 }}>
                                    {leads.links.map((link, i) => (
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
                </Card>

            </div>
        </>
    );
}
