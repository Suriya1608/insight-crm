import { Head, Link } from '@inertiajs/react';
import { LuUsers, LuWifi, LuPhone, LuCirclePause, LuChartBar, LuDownload, LuChevronDown } from 'react-icons/lu';

// ─── Design tokens ────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

function formatDuration(seconds) {
    const s = parseInt(seconds) || 0;
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return [h, m, sec].map(v => String(v).padStart(2, '0')).join(':');
}

const PERF_CFG = {
    'A+': { bg: '#ecfdf5', color: '#047857', border: '#6ee7b7' },
    'A':  { bg: '#f0fdf4', color: '#15803d', border: '#bbf7d0' },
    'B':  { bg: '#eff6ff', color: '#1d4ed8', border: '#bfdbfe' },
    'C':  { bg: '#fffbeb', color: '#b45309', border: '#fde68a' },
    'D':  { bg: '#fef2f2', color: '#dc2626', border: '#fecaca' },
};

function PerfBadge({ rating }) {
    const cfg = PERF_CFG[rating] ?? { bg: '#f1f5f9', color: '#475569', border: '#e2e8f0' };
    return (
        <span style={{
            background: cfg.bg, color: cfg.color, border: `1px solid ${cfg.border}`,
            fontSize: 12, fontWeight: 800, padding: '3px 10px', borderRadius: 20,
            display: 'inline-block',
        }}>{rating ?? '—'}</span>
    );
}

function StatusBadge({ online, breakStatus }) {
    if (breakStatus === 'on_call' || online === 'on_call') return (
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: '#FFF4EE', border: `1px solid ${OR}40`, borderRadius: 20, padding: '4px 10px' }}>
            <span style={{ width: 7, height: 7, borderRadius: '50%', background: OR, flexShrink: 0 }} />
            <span style={{ fontSize: 12, fontWeight: 700, color: OR }}>On Call</span>
        </div>
    );
    if (online === 'online' || breakStatus === 'online') return (
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: '#ecfdf5', border: '1px solid #6ee7b7', borderRadius: 20, padding: '4px 10px' }}>
            <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#10b981', flexShrink: 0 }} />
            <span style={{ fontSize: 12, fontWeight: 700, color: '#047857' }}>Online</span>
        </div>
    );
    if (breakStatus === 'idle') return (
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: '#fffbeb', border: '1px solid #fde68a', borderRadius: 20, padding: '4px 10px' }}>
            <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#f59e0b', flexShrink: 0 }} />
            <span style={{ fontSize: 12, fontWeight: 700, color: '#b45309' }}>Idle / Break</span>
        </div>
    );
    return (
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: '#f1f5f9', border: '1px solid #cbd5e1', borderRadius: 20, padding: '4px 10px' }}>
            <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#64748b', flexShrink: 0 }} />
            <span style={{ fontSize: 12, fontWeight: 700, color: '#334155' }}>Offline</span>
        </div>
    );
}

function StatRow({ Icon, label, value, orange }) {
    return (
        <div style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 12px',
            background: orange ? OR : WH, borderRadius:10,
            border: orange ? 'none' : `1px solid ${BOR}`,
            boxShadow: orange ? '0 4px 14px rgba(255,92,0,0.2)' : '0 1px 3px rgba(0,0,0,0.04)' }}>
            <div style={{ width:32, height:32, borderRadius:9, flexShrink:0,
                background: orange ? 'rgba(255,255,255,0.18)' : '#FFF7ED',
                display:'flex', alignItems:'center', justifyContent:'center',
                color: orange ? '#fff' : OR }}><Icon size={15}/></div>
            <div style={{ flex:1, minWidth:0 }}>
                <div style={{ fontSize:9, fontWeight:600, textTransform:'uppercase', letterSpacing:'0.5px',
                    marginBottom:1, color: orange ? 'rgba(255,255,255,0.75)' : MUT }}>{label}</div>
                <div style={{ fontSize:20, fontWeight:800, lineHeight:1,
                    color: orange ? '#fff' : DK }}>{value ?? 0}</div>
            </div>
        </div>
    );
}

const PALETTE = [OR, '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];

export default function Index({
    telecallers,
    totalTelecallers,
    onlineTelecallers,
    offlineTelecallers,
    onCallTelecallers,
    idleTelecallers,
}) {
    const statValues = [totalTelecallers, onlineTelecallers, onCallTelecallers, idleTelecallers + offlineTelecallers];
    const refMax = Math.max(1, totalTelecallers);

    return (
        <>
            <Head title="Telecaller Management" />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .mgr-tc-wrap, .mgr-tc-wrap div, .mgr-tc-wrap span:not([class*="material"]),
                .mgr-tc-wrap p, .mgr-tc-wrap h1, .mgr-tc-wrap h2, .mgr-tc-wrap h3,
                .mgr-tc-wrap button, .mgr-tc-wrap input, .mgr-tc-wrap select, .mgr-tc-wrap a,
                .mgr-tc-wrap th, .mgr-tc-wrap td, .mgr-tc-wrap label, .mgr-tc-wrap small {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .mgr-tc-kpi { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:14px; }
                @media(max-width:960px){ .mgr-tc-kpi{ grid-template-columns:repeat(2,1fr); } }

                /* ─ Table card ─ */
                .mgr-tc { background:${WH}; border-radius:14px; border:1px solid ${BOR}; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
                .mgr-tc-head { background:${WH}; border-bottom:1px solid ${BOR}; padding:16px 22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
                .mgr-sec-bar { width:3px; height:32px; background:${OR}; border-radius:2px; flex-shrink:0; }
                .mgr-tc-badge { background:#FFF4EE; color:${OR}; border:1px solid ${OR}30; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }

                .mgr-lt thead th {
                    background:#F4F6F8; color:${MUT}; font-size:10.5px; font-weight:700;
                    text-transform:uppercase; letter-spacing:.7px;
                    padding:11px 16px; border-bottom:1px solid ${BOR}; white-space:nowrap;
                    position:sticky; top:0; z-index:1;
                }
                .mgr-lt tbody td { padding:13px 16px; vertical-align:middle; font-size:13px; color:${BDY}; border-bottom:1px solid #f8fafc; }
                .mgr-lt tbody tr:last-child td { border-bottom:none; }
                .mgr-lt tbody tr { transition:background .1s, border-left .1s; }
                .mgr-lt tbody tr:hover { background:#FFF7ED; border-left:3px solid ${OR}; }
                .mgr-lt tbody tr:hover td:first-child { padding-left:13px; }

                .mgr-l-avatar {
                    width:36px; height:36px; border-radius:10px;
                    color:#fff; font-size:15px; font-weight:800;
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;
                }
                .mgr-l-name  { font-size:13.5px; font-weight:700; color:${DK}; }
                .mgr-l-sub   { font-size:11.5px; color:${MUT}; margin-top:2px; }

                /* active call pulse */
                .mgr-live-dot {
                    width:8px; height:8px; border-radius:50%; background:${OR}; flex-shrink:0;
                    animation: mgrlivepulse 1.4s ease-in-out infinite;
                }
                @keyframes mgrlivepulse {
                    0%, 100% { box-shadow: 0 0 0 0 rgba(255,92,0,0.5); }
                    50%       { box-shadow: 0 0 0 5px rgba(255,92,0,0); }
                }

                /* ─ Action button ─ */
                .mgr-btn-view {
                    display:inline-flex; align-items:center; gap:5px;
                    padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700;
                    color:#fff; background:${OR}; border:none;
                    text-decoration:none; transition:all .15s; white-space:nowrap;
                }
                .mgr-btn-view:hover { background:#e04e00; color:#fff; }

                /* ─ Export dropdown ─ */
                .mgr-exp-menu { border-radius:10px; border:1px solid ${BOR}; overflow:hidden; min-width:165px; }
                .mgr-exp-menu .dropdown-item { font-size:13px; padding:10px 14px; display:flex; align-items:center; gap:8px; color:${BDY}; }
                .mgr-exp-menu .dropdown-item:hover { background:${DK}; color:#fff; }
                .mgr-btn-export {
                    background:${WH}; color:${BDY};
                    border:1px solid ${BOR};
                    border-radius:9px; padding:7px 14px; font-size:12.5px; font-weight:600;
                    display:inline-flex; align-items:center; gap:6px; cursor:pointer; transition:all .15s;
                }
                .mgr-btn-export:hover { background:#f4f6f8; }
            `}</style>

            <div className="mgr-tc-wrap">
                {/* ── KPI Row ── */}
                <div className="mgr-tc-kpi">
                    <StatRow Icon={LuUsers}       label="Total Telecallers" value={totalTelecallers}                        orange={true}  />
                    <StatRow Icon={LuWifi}         label="Online"            value={onlineTelecallers}                       orange={false} />
                    <StatRow Icon={LuPhone}        label="On Call"           value={onCallTelecallers}                       orange={false} />
                    <StatRow Icon={LuCirclePause}  label="Idle / Offline"    value={idleTelecallers + offlineTelecallers}    orange={false} />
                </div>

                {/* ── Table ─────────────────────────────────────────────────── */}
                <div className="mgr-tc">
                    <div className="mgr-tc-head">
                        <div className="d-flex align-items-center gap-3">
                            <div className="mgr-sec-bar" />
                            <LuChartBar size={20} style={{ color: OR }} />
                            <div>
                                <div style={{ fontSize: 15, fontWeight: 800, color: DK, lineHeight: 1 }}>Telecaller Live Performance Board</div>
                                <div style={{ fontSize: 12, color: MUT, marginTop: 2 }}>Real-time agent stats</div>
                            </div>
                            <span className="mgr-tc-badge">{telecallers.length} agents</span>
                        </div>
                        <div className="dropdown">
                            <button type="button" className="mgr-btn-export" data-bs-toggle="dropdown">
                                <LuDownload size={15} />
                                Export
                                <LuChevronDown size={13} />
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end shadow-sm mgr-exp-menu">
                                <li>
                                    <a className="dropdown-item"
                                        href="/manager/telecallers/export"
                                        onClick={e => { e.preventDefault(); window.location.href = '/manager/telecallers/export'; }}>
                                        <span style={{ width: 16, height: 16, borderRadius: 4, background: '#dcfce7', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
                                            <span style={{ fontSize: 10, color: '#15803d', fontWeight: 800 }}>XL</span>
                                        </span>
                                        Excel (.xlsx)
                                    </a>
                                </li>
                                <li>
                                    <a className="dropdown-item"
                                        href="/manager/telecallers/export?format=pdf"
                                        onClick={e => { e.preventDefault(); window.location.href = '/manager/telecallers/export?format=pdf'; }}>
                                        <span style={{ width: 16, height: 16, borderRadius: 4, background: '#fee2e2', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
                                            <span style={{ fontSize: 9, color: '#dc2626', fontWeight: 800 }}>PDF</span>
                                        </span>
                                        PDF Report
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="table-responsive">
                        <table className="table mgr-lt mb-0">
                            <thead>
                                <tr>
                                    <th style={{ width: 44 }}>S.No</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Active Call</th>
                                    <th>Today Calls</th>
                                    <th>Today Talk Time</th>
                                    <th>Performance</th>
                                    <th>Missed Follow-up</th>
                                    <th style={{ textAlign: 'right', paddingRight: 20 }}>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {telecallers.length === 0 ? (
                                    <tr><td colSpan={9}>
                                        <div style={{ textAlign: 'center', padding: '60px 0 52px' }}>
                                            <div style={{ width: 76, height: 76, borderRadius: 20, background: '#FFF4EE', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 18px', boxShadow: `0 4px 16px ${OR}20` }}>
                                                <LuUsers size={38} style={{ color: OR }} />
                                            </div>
                                            <div style={{ fontSize: 16, fontWeight: 700, color: DK, marginBottom: 8 }}>No telecallers found</div>
                                            <div style={{ fontSize: 13, color: MUT }}>No agents are assigned to your team yet.</div>
                                        </div>
                                    </td></tr>
                                ) : telecallers.map((tele, idx) => {
                                    const avatarBg = PALETTE[Math.abs((tele.id ?? idx) % PALETTE.length)];
                                    return (
                                        <tr key={tele.id}>
                                            <td style={{ color: MUT, fontSize: 12, fontWeight: 600 }}>{idx + 1}</td>

                                            {/* Name + conversion */}
                                            <td>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                    <div className="mgr-l-avatar" style={{ background: avatarBg }}>
                                                        {(tele.name || '?')[0].toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="mgr-l-name">{tele.name}</div>
                                                        <div className="mgr-l-sub">Conv. {Number(tele.conversion_rate).toFixed(2)}%</div>
                                                    </div>
                                                </div>
                                            </td>

                                            {/* Status */}
                                            <td>
                                                <StatusBadge
                                                    online={tele.online_offline_status}
                                                    breakStatus={tele.break_tracking_status}
                                                />
                                            </td>

                                            {/* Active call */}
                                            <td>
                                                {tele.active_call_indicator ? (
                                                    <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: '#FFF4EE', border: `1px solid ${OR}40`, borderRadius: 20, padding: '4px 10px' }}>
                                                        <span className="mgr-live-dot" />
                                                        <span style={{ fontSize: 12, fontWeight: 700, color: OR }}>Live Call</span>
                                                    </div>
                                                ) : (
                                                    <span style={{ fontSize: 12, fontWeight: 600, color: MUT }}>No Active Call</span>
                                                )}
                                            </td>

                                            {/* Today calls */}
                                            <td>
                                                <span style={{
                                                    background: tele.today_call_count > 0 ? '#FFF4EE' : '#f1f5f9',
                                                    color:      tele.today_call_count > 0 ? OR : '#475569',
                                                    border:     `1px solid ${tele.today_call_count > 0 ? OR + '40' : '#cbd5e1'}`,
                                                    fontSize: 13, fontWeight: 800,
                                                    padding: '3px 12px', borderRadius: 20, display: 'inline-block',
                                                }}>{tele.today_call_count}</span>
                                            </td>

                                            {/* Today talk time */}
                                            <td>
                                                <span style={{ fontSize: 13, fontWeight: 700, color: tele.today_talk_time_sec > 0 ? DK : MUT, fontFamily: 'monospace', letterSpacing: '.5px' }}>
                                                    {formatDuration(tele.today_talk_time_sec)}
                                                </span>
                                            </td>

                                            {/* Performance rating */}
                                            <td><PerfBadge rating={tele.performance_rating} /></td>

                                            {/* Missed follow-up */}
                                            <td>
                                                <span style={{
                                                    background: tele.missed_followup_count > 0 ? '#fef2f2' : '#f1f5f9',
                                                    color:      tele.missed_followup_count > 0 ? '#dc2626' : '#475569',
                                                    border:     `1px solid ${tele.missed_followup_count > 0 ? '#fecaca' : '#cbd5e1'}`,
                                                    fontSize: 13, fontWeight: 800,
                                                    padding: '3px 12px', borderRadius: 20, display: 'inline-block',
                                                }}>{tele.missed_followup_count}</span>
                                            </td>

                                            {/* Action */}
                                            <td style={{ textAlign: 'right', paddingRight: 20 }}>
                                                <Link
                                                    href={`/manager/telecallers/${tele.encoded_id}/performance`}
                                                    className="mgr-btn-view"
                                                >
                                                    <LuChartBar size={14} />
                                                    Performance
                                                </Link>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
}
