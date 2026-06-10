import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuSearch, LuX, LuPhone, LuUser, LuPlus, LuChevronLeft, LuChevronRight,
} from 'react-icons/lu';

// ─── Design tokens ────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

export default function Pool({ leads, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');

    function doSearch(e) {
        e.preventDefault();
        router.get('/manager/leads/pool', { search }, { preserveState: true, replace: true });
    }

    function claimLead(url) {
        if (!window.confirm('Claim this lead?')) return;
        router.post(url);
    }

    return (
        <>
            <Head title="Open Lead Pool" />

            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                * { font-family: 'Poppins', sans-serif; }

                .pool-tr { border-bottom:1px solid ${BOR}; transition:background .1s; }
                .pool-tr:hover { background:#FFF4EE; }
                .pool-th {
                    font-size:11px; font-weight:700; color:${MUT}; padding:11px 16px;
                    text-transform:uppercase; letter-spacing:.5px; white-space:nowrap;
                    border-bottom:2px solid ${BOR}; background:#F4F6F8;
                }
                .pool-td { padding:13px 16px; font-size:13px; color:${BDY}; vertical-align:middle; }

                .btn-claim {
                    display:inline-flex; align-items:center; gap:5px;
                    padding:6px 14px; border-radius:9px; font-size:12px; font-weight:600;
                    background:${OR}; color:#fff; border:none; cursor:pointer; transition:all .15s;
                }
                .btn-claim:hover { background:#e04e00; }

                .search-input {
                    border-radius:9px !important; border:1px solid ${BOR} !important;
                    font-size:13px !important; background:#F4F6F8 !important; color:${DK} !important;
                    transition:border-color .15s, box-shadow .15s !important;
                    padding-left:38px !important; height:38px;
                }
                .search-input:focus {
                    border-color:${OR} !important;
                    box-shadow:0 0 0 3px rgba(255,92,0,0.12) !important;
                    background:${WH} !important; outline:none !important;
                }
                .btn-search {
                    display:inline-flex; align-items:center; justify-content:center;
                    width:38px; height:38px; border-radius:9px; border:1px solid ${BOR};
                    background:${WH}; color:${MUT}; cursor:pointer; transition:all .15s; flex-shrink:0;
                }
                .btn-search:hover { background:${OR}; border-color:${OR}; color:#fff; }
                .btn-clear {
                    display:inline-flex; align-items:center; justify-content:center;
                    width:38px; height:38px; border-radius:9px; border:1px solid #fecdd3;
                    background:#fff1f2; color:#be123c; cursor:pointer; transition:all .15s; flex-shrink:0;
                    text-decoration:none;
                }
                .btn-clear:hover { background:#be123c; border-color:#be123c; color:#fff; }

                .lc-chip {
                    font-family:'SFMono-Regular',Consolas,monospace;
                    font-size:11.5px; font-weight:600;
                    background:#F4F6F8; color:${BDY};
                    border:1px solid ${BOR}; padding:3px 8px; border-radius:6px;
                }
                .age-chip {
                    font-size:11px; font-weight:600; color:${MUT};
                    background:#F4F6F8; border:1px solid ${BOR};
                    padding:2px 8px; border-radius:6px; display:inline-block;
                }
                .src-chip {
                    font-size:11px; font-weight:600; color:${BDY};
                    background:#F4F6F8; border:1px solid ${BOR};
                    padding:2px 8px; border-radius:6px; display:inline-block;
                }

                .pool-pager .page-link { background:${WH}; border-color:${BOR}; color:${BDY}; font-size:12px; border-radius:7px; }
                .pool-pager .page-item.active .page-link { background:${OR}; border-color:${OR}; color:#fff; }
                .pool-pager .page-item.disabled .page-link { opacity:.4; }
            `}</style>

            {/* ── Page header (SHead pattern) ─────────────────────────── */}
            <div style={{
                background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
                padding: '20px 24px', marginBottom: 20,
            }}>
                <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 14, flexWrap: 'wrap' }}>
                    <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
                        <div style={{ width: 3, height: 44, borderRadius: 4, background: OR, flexShrink: 0 }} />
                        <div>
                            <h4 style={{ fontSize: 18, fontWeight: 800, color: DK, margin: 0 }}>Open Lead Pool</h4>
                            <p style={{ color: MUT, margin: '2px 0 0', fontSize: 13 }}>
                                Unclaimed leads available to pick up. First-come, first-served.
                            </p>
                        </div>
                    </div>
                    <span style={{
                        display: 'inline-flex', alignItems: 'center', gap: 6,
                        background: '#FFF4EE', color: OR, border: `1.5px solid #FFCFB5`,
                        padding: '6px 14px', borderRadius: 20, fontSize: 13, fontWeight: 700,
                    }}>
                        {leads.total} leads
                    </span>
                </div>

                {/* Search bar */}
                <form onSubmit={doSearch} style={{ marginTop: 16 }}>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap', maxWidth: 480 }}>
                        <div style={{ position: 'relative', flex: '1 1 240px' }}>
                            <LuSearch size={16} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: MUT, pointerEvents: 'none' }} />
                            <input
                                type="text"
                                className="form-control search-input"
                                placeholder="Search name, phone, code…"
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                            />
                        </div>
                        <button type="submit" className="btn-search" title="Search">
                            <LuSearch size={16} />
                        </button>
                        {filters.search && (
                            <Link href="/manager/leads/pool" className="btn-clear" title="Clear search">
                                <LuX size={16} />
                            </Link>
                        )}
                    </div>
                </form>
            </div>

            {leads.data.length === 0 ? (
                <div style={{
                    background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                    boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
                    textAlign: 'center', padding: '64px 20px',
                }}>
                    <div style={{
                        width: 64, height: 64, borderRadius: 18,
                        background: '#F4F6F8',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        margin: '0 auto 16px',
                    }}>
                        <LuUser size={32} color={MUT} />
                    </div>
                    <div style={{ fontSize: 15, fontWeight: 700, color: DK, marginBottom: 6 }}>Pool is empty</div>
                    <p style={{ color: MUT, margin: 0, fontSize: 13 }}>No unclaimed leads in the pool right now.</p>
                </div>
            ) : (
                <>
                    {/* ── Table ─────────────────────────────────────────── */}
                    <div style={{
                        background: WH, borderRadius: 14, border: `1px solid ${BOR}`,
                        boxShadow: '0 2px 8px rgba(0,0,0,0.04)', overflow: 'hidden',
                    }}>
                        {/* Table header */}
                        <div style={{
                            background: DK, padding: '14px 22px',
                            display: 'flex', alignItems: 'center', gap: 10,
                        }}>
                            <div style={{ width: 3, height: 32, borderRadius: 4, background: OR, flexShrink: 0 }} />
                            <div style={{ fontWeight: 700, color: WH, fontSize: 15 }}>Lead Pool</div>
                            <span style={{
                                background: 'rgba(255,92,0,0.2)', color: OR,
                                border: '1px solid rgba(255,92,0,0.35)',
                                fontSize: 11, fontWeight: 700, padding: '2px 10px', borderRadius: 20,
                            }}>
                                {leads.total} records
                            </span>
                        </div>

                        <div className="table-responsive">
                            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                <thead>
                                    <tr>
                                        <th className="pool-th">Code</th>
                                        <th className="pool-th">Name</th>
                                        <th className="pool-th">Phone</th>
                                        <th className="pool-th">Course</th>
                                        <th className="pool-th">Source</th>
                                        <th className="pool-th">Age</th>
                                        <th className="pool-th" style={{ textAlign: 'right', paddingRight: 20 }}>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {leads.data.map(lead => (
                                        <tr key={lead.id} className="pool-tr">
                                            <td className="pool-td">
                                                <span className="lc-chip">{lead.lead_code}</span>
                                            </td>
                                            <td className="pool-td" style={{ fontWeight: 600, color: DK }}>{lead.name}</td>
                                            <td className="pool-td">
                                                <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 13, color: BDY }}>
                                                    <LuPhone size={13} color={MUT} />
                                                    {lead.phone}
                                                </span>
                                            </td>
                                            <td className="pool-td">
                                                <span style={{ fontSize: 13, color: BDY }}>{lead.course || '—'}</span>
                                            </td>
                                            <td className="pool-td">
                                                <span className="src-chip">{lead.source || '—'}</span>
                                            </td>
                                            <td className="pool-td">
                                                <span className="age-chip">{lead.age}</span>
                                            </td>
                                            <td className="pool-td" style={{ textAlign: 'right', paddingRight: 20 }}>
                                                <button className="btn-claim" onClick={() => claimLead(lead.claim_url)}>
                                                    <LuPlus size={14} />
                                                    Claim
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* ── Pagination ────────────────────────────────────── */}
                    {leads.last_page > 1 && (
                        <div style={{ marginTop: 16 }}>
                            <nav>
                                <ul className="pagination pagination-sm mb-0 pool-pager" style={{ gap: 3 }}>
                                    {leads.links.map((link, i) => (
                                        <li key={i} className={['page-item', link.active ? 'active' : '', !link.url ? 'disabled' : ''].join(' ')}>
                                            {link.url
                                                ? <Link href={link.url} className="page-link" dangerouslySetInnerHTML={{ __html: link.label }} />
                                                : <span className="page-link" dangerouslySetInnerHTML={{ __html: link.label }} />
                                            }
                                        </li>
                                    ))}
                                </ul>
                            </nav>
                        </div>
                    )}
                </>
            )}
        </>
    );
}
