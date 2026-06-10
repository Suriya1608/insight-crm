import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import {
    LuUsers, LuPhone, LuTimer, LuMessageSquare, LuTrendingUp, LuTrophy,
    LuCalendarX, LuUserCog, LuCalendarDays, LuRefreshCw, LuBellRing,
    LuZap, LuUserPlus, LuChartBar, LuHeadphones, LuCircleCheck,
    LuChartPie, LuFilter, LuArrowRight, LuPhoneMissed, LuPhoneIncoming,
    LuGraduationCap, LuChevronLeft, LuChevronRight, LuCalendar, LuChevronDown,
    LuSettings2,
} from 'react-icons/lu';

// ── Helpers ────────────────────────────────────────────────────────────────────
function toTimeLabel(sec) {
    const s = Number(sec || 0);
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const ss = s % 60;
    return [h, m, ss].map(v => String(v).padStart(2, '0')).join(':');
}
function pad(n) { return String(n).padStart(2, '0'); }

const MONTH_NAMES = ['January','February','March','April','May','June',
    'July','August','September','October','November','December'];

const AVATAR_COLORS = ['#FF5C00','#10B981','#F59E0B','#F43F5E','#8B5CF6','#06B6D4','#EC4899','#14B8A6'];
function avatarColor(str) {
    let h = 0;
    for (let i = 0; i < (str || '').length; i++) h = (h * 31 + str.charCodeAt(i)) >>> 0;
    return AVATAR_COLORS[h % AVATAR_COLORS.length];
}
function initials(name) {
    const parts = (name || '?').trim().split(/\s+/);
    return parts.length >= 2 ? parts[0][0] + parts[1][0] : parts[0].slice(0, 2);
}

function useApexCharts(onReady) {
    useEffect(() => {
        if (window.ApexCharts) { onReady(window.ApexCharts); return; }
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js';
        s.onload = () => onReady(window.ApexCharts);
        document.head.appendChild(s);
    }, []);
}

// ── Embedded CSS — all rules strictly scoped under .mgr-dash ──────────────────
const DASH_CSS = `
@keyframes mgrPulse { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.55;transform:scale(1.7);} }
@keyframes mgrBlobFloat { 0%,100%{transform:translateY(0) scale(1);} 50%{transform:translateY(-12px) scale(1.04);} }

.mgr-dash { font-family:'Poppins',sans-serif; }
.mgr-dash * { font-family:'Poppins',sans-serif; box-sizing:border-box; }

/* ── Hero Banner ── */
.mgr-dash .mgr-hero-banner {
    background: #1D1D1D;
    border-radius: 14px; padding: 24px 28px; color: #fff;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
    margin-bottom: 16px;
}
.mgr-dash .mgr-blob { position:absolute; border-radius:50%; pointer-events:none; }
.mgr-dash .mgr-blob-1 { width:220px;height:220px;background:rgba(255,92,0,0.10);top:-70px;right:-50px;animation:mgrBlobFloat 7s ease-in-out infinite; }
.mgr-dash .mgr-blob-2 { width:140px;height:140px;background:rgba(255,140,74,0.07);bottom:-50px;right:140px;animation:mgrBlobFloat 9s ease-in-out infinite reverse; }
.mgr-dash .mgr-blob-3 { width:90px;height:90px;background:rgba(255,255,255,0.04);top:20px;left:240px;animation:mgrBlobFloat 6s ease-in-out infinite 2s; }

.mgr-dash .mgr-hero-inner { display:flex;align-items:stretch;gap:24px;position:relative;z-index:1; }

.mgr-dash .mgr-profile-card {
    background:rgba(255,255,255,0.07);backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,0.12);border-radius:12px;
    padding:18px 22px;display:flex;flex-direction:column;align-items:center;gap:6px;
    min-width:190px;flex-shrink:0;
}
.mgr-dash .mgr-profile-avatar {
    width:56px;height:56px;border-radius:50%;
    background:linear-gradient(135deg,#FF5C00,#FF8C4A);
    border:3px solid rgba(255,92,0,0.40);
    display:flex;align-items:center;justify-content:center;
    font-size:20px;font-weight:800;color:#fff;
    box-shadow:0 4px 16px rgba(0,0,0,0.30);margin-bottom:4px;
}
.mgr-dash .mgr-profile-name { font-size:13px;font-weight:700;color:#fff;text-align:center;line-height:1.3; }
.mgr-dash .mgr-profile-role { display:flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:rgba(255,140,74,0.85);text-transform:uppercase;letter-spacing:1px; }
.mgr-dash .mgr-profile-meta-row { display:flex;align-items:center;margin-top:10px;background:rgba(0,0,0,0.22);border-radius:10px;padding:8px 12px;width:100%;justify-content:space-around; }
.mgr-dash .mgr-profile-meta-item { text-align:center; }
.mgr-dash .mgr-profile-meta-val { font-size:19px;font-weight:800;color:#fff;line-height:1; }
.mgr-dash .mgr-profile-meta-key { font-size:9px;font-weight:600;color:rgba(255,200,160,0.65);text-transform:uppercase;letter-spacing:.5px;margin-top:2px; }
.mgr-dash .mgr-profile-meta-sep { width:1px;height:28px;background:rgba(255,255,255,0.15);margin:0 8px; }

.mgr-dash .mgr-hero-right { flex:1;display:flex;flex-direction:column;justify-content:space-between;gap:14px; }
.mgr-dash .mgr-hero-meta { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.mgr-dash .mgr-hero-meta-item { display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;background:rgba(255,255,255,0.08);padding:5px 13px;border-radius:20px;border:1px solid rgba(255,255,255,0.10); }
.mgr-dash .mgr-hero-alert { background:rgba(239,68,68,0.22);border-color:rgba(239,68,68,0.35); }
.mgr-dash .mgr-live-dot { width:7px;height:7px;background:#4ade80;border-radius:50%;animation:mgrPulse 1.4s infinite;flex-shrink:0; }

.mgr-dash .mgr-rings { display:flex;gap:20px;align-items:center; }
.mgr-dash .mgr-ring-item { position:relative;width:78px;height:78px;display:flex;align-items:center;justify-content:center; }
.mgr-dash .mgr-ring-label { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center; }
.mgr-dash .mgr-ring-val { font-size:17px;font-weight:800;color:#fff;line-height:1; }
.mgr-dash .mgr-ring-sub { font-size:9px;font-weight:600;color:rgba(255,200,160,0.80);text-transform:uppercase;letter-spacing:.4px; }

/* ── Cards ── */
.mgr-dash .mgr-card {
    background:#FEFEFE; border:1px solid #F0F0F0;
    border-radius:14px; padding:18px 20px;
    box-shadow:0 2px 8px rgba(0,0,0,0.04);
}

.mgr-dash .mgr-stat-badge { display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;padding:3px 8px;border-radius:20px;letter-spacing:.3px;box-shadow:0 2px 8px rgba(0,0,0,0.15); }

/* ── Section heads ── */
.mgr-dash .mgr-section-title { font-size:14px;font-weight:700;color:#1D1D1D;margin:0;display:flex;align-items:center;gap:7px; }
.mgr-dash .mgr-section-sub { font-size:11px;color:#9CA3AF;margin-top:2px; }
.mgr-dash .mgr-section-badge { font-size:11px;font-weight:600;color:#6B7280;background:#F3F4F6;border:1px solid #E5E7EB;padding:3px 10px;border-radius:20px; }

/* ── Quick Actions ── */
.mgr-dash .mgr-qa-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.mgr-dash .mgr-qa-card { display:flex;align-items:center;gap:12px;padding:13px 15px;border-radius:12px;text-decoration:none;cursor:pointer;border:none;transition:all .18s ease;text-align:left; }
.mgr-dash .mgr-qa-card:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,0.14); }
.mgr-dash .mgr-qa-icon { width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.20);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.mgr-dash .mgr-qa-label { font-size:12.5px;font-weight:700; }
.mgr-dash .mgr-qa-hint { font-size:11px;opacity:.78;margin-top:1px; }
.mgr-dash .mgr-qa-dark   { background:#1D1D1D;color:#fff;box-shadow:0 3px 12px rgba(0,0,0,0.25); }
.mgr-dash .mgr-qa-orange { background:#FF5C00;color:#fff;box-shadow:0 3px 12px rgba(255,92,0,0.30); }
.mgr-dash .mgr-qa-success{ background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 3px 12px rgba(16,185,129,0.25); }
.mgr-dash .mgr-qa-amber  { background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;box-shadow:0 3px 12px rgba(245,158,11,0.25); }

/* ── Perf strip ── */
.mgr-dash .mgr-perf-strip { display:flex;align-items:center;gap:18px;background:#F9FAFB;border:1px solid #F0F0F0;border-radius:10px;padding:10px 14px; }
.mgr-dash .mgr-perf-item { display:flex;align-items:center;gap:9px; }
.mgr-dash .mgr-perf-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
.mgr-dash .mgr-perf-val { font-size:16px;font-weight:800;color:#1D1D1D;line-height:1; }
.mgr-dash .mgr-perf-key { font-size:10px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;margin-top:1px; }
.mgr-dash .mgr-perf-divider { width:1px;height:26px;background:#E5E7EB; }

/* ── Empty state ── */
.mgr-dash .mgr-empty-state { display:flex;flex-direction:column;align-items:center;gap:6px;padding:26px 0;color:#9CA3AF;font-size:12px;font-weight:500; }

/* ── Period selector ── */
.mgr-dash .mgr-period-wrap { display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #E5E7EB;border-radius:8px;padding:6px 12px;color:#6B7280;font-size:12px;font-weight:500;cursor:pointer; }
.mgr-dash .mgr-period-wrap select { background:transparent;border:none;color:#1D1D1D;font-size:12px;font-weight:500;cursor:pointer;outline:none; }
.mgr-dash .mgr-period-wrap select option { background:#fff;color:#1D1D1D; }

/* ── Tables ── */
.mgr-dash .mgr-th { padding:7px 10px;text-align:left;font-size:11px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;border-bottom:1px solid #F3F4F6;position:sticky;top:0;background:#FEFEFE;z-index:1; }
.mgr-dash .mgr-td { padding:9px 10px;vertical-align:middle;font-size:12px;color:#374151;border-bottom:1px solid #F9FAFB; }

@media (max-width:640px) {
    .mgr-dash .mgr-hero-inner { flex-direction:column;gap:14px; }
    .mgr-dash .mgr-profile-card { min-width:unset;width:100%; }
    .mgr-dash .mgr-rings { display:none; }
    .mgr-dash .mgr-qa-grid { grid-template-columns:1fr 1fr; }
    .mgr-dash .mgr-hero-banner { padding:18px; }
}
`;

// ── Hero Banner ────────────────────────────────────────────────────────────────
function HeroBanner({ name, periodLeads, totalCallsMade, conversionRate, missedFollowups, overallLeads, overallCalls }) {
    const [dateStr, setDateStr] = useState('');
    const [greeting, setGreeting] = useState('Good Morning');

    useEffect(() => {
        const h = new Date().getHours();
        setGreeting(h < 12 ? 'Good Morning' : h < 17 ? 'Good Afternoon' : 'Good Evening');
        setDateStr(new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }));
    }, []);

    const CIRC = 201.1;
    const callsOffset = Math.max(0, CIRC - Math.min(1, totalCallsMade / Math.max(1, 50)) * CIRC);
    const convOffset  = Math.max(0, CIRC - Math.min(1, parseFloat(conversionRate) / 100) * CIRC);

    return (
        <div className="mgr-hero-banner">
            <div className="mgr-blob mgr-blob-1" />
            <div className="mgr-blob mgr-blob-2" />
            <div className="mgr-blob mgr-blob-3" />
            <div className="mgr-hero-inner">
                <div className="mgr-profile-card">
                    <div className="mgr-profile-avatar">{initials(name).toUpperCase()}</div>
                    <div className="mgr-profile-name">{greeting}, {name}</div>
                    <div className="mgr-profile-role">
                        <LuSettings2 size={11} />
                        Manager Panel
                    </div>
                    <div className="mgr-profile-meta-row">
                        <div className="mgr-profile-meta-item">
                            <div className="mgr-profile-meta-val">{periodLeads}</div>
                            <div className="mgr-profile-meta-key">Leads</div>
                        </div>
                        <div className="mgr-profile-meta-sep" />
                        <div className="mgr-profile-meta-item">
                            <div className="mgr-profile-meta-val">{totalCallsMade}</div>
                            <div className="mgr-profile-meta-key">Calls</div>
                        </div>
                        <div className="mgr-profile-meta-sep" />
                        <div className="mgr-profile-meta-item">
                            <div className="mgr-profile-meta-val">{parseFloat(conversionRate).toFixed(1)}%</div>
                            <div className="mgr-profile-meta-key">Conv.</div>
                        </div>
                    </div>
                    <div className="mgr-profile-meta-row" style={{ marginTop: 6, background: 'rgba(255,255,255,0.05)' }}>
                        <div className="mgr-profile-meta-item">
                            <div className="mgr-profile-meta-val" style={{ fontSize: 16 }}>{overallLeads}</div>
                            <div className="mgr-profile-meta-key" style={{ color: 'rgba(255,200,160,0.5)' }}>All Leads</div>
                        </div>
                        <div className="mgr-profile-meta-sep" />
                        <div className="mgr-profile-meta-item">
                            <div className="mgr-profile-meta-val" style={{ fontSize: 16 }}>{overallCalls}</div>
                            <div className="mgr-profile-meta-key" style={{ color: 'rgba(255,200,160,0.5)' }}>All Calls</div>
                        </div>
                    </div>
                </div>

                <div className="mgr-hero-right">
                    <div className="mgr-hero-meta">
                        <div className="mgr-hero-meta-item">
                            <LuCalendarDays size={13} />
                            <span>{dateStr}</span>
                        </div>
                        <div className="mgr-hero-meta-item">
                            <div className="mgr-live-dot" />
                            Live
                            <LuRefreshCw size={11} style={{ opacity: 0.7 }} />
                        </div>
                        {missedFollowups > 0 && (
                            <div className="mgr-hero-meta-item mgr-hero-alert">
                                <LuBellRing size={13} />
                                {missedFollowups} missed follow-up{missedFollowups !== 1 ? 's' : ''}
                            </div>
                        )}
                    </div>
                    <div className="mgr-rings">
                        <div className="mgr-ring-item">
                            <svg width="78" height="78" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,0.10)" strokeWidth="6" />
                                <circle cx="40" cy="40" r="32" fill="none" stroke="#FF5C00" strokeWidth="6"
                                    strokeDasharray="201.1" strokeDashoffset={callsOffset}
                                    strokeLinecap="round" transform="rotate(-90 40 40)" />
                            </svg>
                            <div className="mgr-ring-label">
                                <span className="mgr-ring-val">{totalCallsMade}</span>
                                <span className="mgr-ring-sub">Calls</span>
                            </div>
                        </div>
                        <div className="mgr-ring-item">
                            <svg width="78" height="78" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,0.10)" strokeWidth="6" />
                                <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,140,74,0.70)" strokeWidth="6"
                                    strokeDasharray="201.1" strokeDashoffset={convOffset}
                                    strokeLinecap="round" transform="rotate(-90 40 40)" />
                            </svg>
                            <div className="mgr-ring-label">
                                <span className="mgr-ring-val">{parseFloat(conversionRate).toFixed(0)}%</span>
                                <span className="mgr-ring-sub">Conv.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

// ── Stat Card ─────────────────────────────────────────────────────────────────
function StatCard({ icon, label, value, displayValue, sub, badgeGrad, iconClass, barColor, barPct, highlight }) {
    const displayVal = value != null ? String(value) : (displayValue ?? '—');
    const pct = barPct != null ? Math.min(99, Math.round(barPct)) : null;
    return (
        <div className={`stat-card stat-card-v2${highlight ? ` highlight-${highlight}` : ''}`} style={{ overflow: 'visible' }}>
            {pct != null && (
                <div className="mgr-stat-badge" style={{ background: badgeGrad }}>{pct}%</div>
            )}
            <div className="d-flex align-items-start justify-content-between mt-1">
                <div>
                    <div className="stat-label">{label}</div>
                    <div className="stat-value">{displayVal}</div>
                    {sub && <div style={{ fontSize: 11, color: '#9CA3AF', marginTop: 3 }}>{sub}</div>}
                </div>
                <div className={`stat-icon ${iconClass}`} style={{ display:'flex',alignItems:'center',justifyContent:'center' }}>
                    {icon}
                </div>
            </div>
            {barColor && barPct != null && (
                <div className="stat-card-bar" style={{ '--bar-color': barColor, '--bar-pct': `${Math.min(100, barPct)}%` }} />
            )}
        </div>
    );
}

// ── Quick Actions ──────────────────────────────────────────────────────────────
function QuickActions({ leadsCreateUrl, telecallersUrl, missedFollowups, periodLeads, leadsContacted }) {
    return (
        <div className="mgr-card" style={{ height: '100%' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
                <div>
                    <div className="mgr-section-title">
                        <LuZap size={16} color="#FF5C00" />
                        Quick Actions
                    </div>
                    <div className="mgr-section-sub">Team Workflow</div>
                </div>
                <span className="mgr-section-badge">4 shortcuts</span>
            </div>
            <div className="mgr-qa-grid">
                <a href={leadsCreateUrl} className="mgr-qa-card mgr-qa-dark">
                    <div className="mgr-qa-icon"><LuUserPlus size={18} /></div>
                    <div>
                        <div className="mgr-qa-label">Add Lead</div>
                        <div className="mgr-qa-hint">New admission</div>
                    </div>
                </a>
                <a href="/manager/reports" className="mgr-qa-card mgr-qa-orange">
                    <div className="mgr-qa-icon"><LuChartBar size={18} /></div>
                    <div>
                        <div className="mgr-qa-label">View Reports</div>
                        <div className="mgr-qa-hint">Analytics &amp; insights</div>
                    </div>
                </a>
                <a href={telecallersUrl} className="mgr-qa-card mgr-qa-success">
                    <div className="mgr-qa-icon"><LuHeadphones size={18} /></div>
                    <div>
                        <div className="mgr-qa-label">Telecallers</div>
                        <div className="mgr-qa-hint">Manage team</div>
                    </div>
                </a>
                <a href="/manager/followups/overdue" className="mgr-qa-card mgr-qa-amber">
                    <div className="mgr-qa-icon"><LuCalendarX size={18} /></div>
                    <div>
                        <div className="mgr-qa-label">Missed Follow-ups</div>
                        <div className="mgr-qa-hint">{missedFollowups} overdue</div>
                    </div>
                </a>
            </div>
            <div className="mgr-perf-strip mt-3">
                <div className="mgr-perf-item">
                    <div className="mgr-perf-dot" style={{ background: '#1D1D1D' }} />
                    <div>
                        <div className="mgr-perf-val">{periodLeads}</div>
                        <div className="mgr-perf-key">Leads</div>
                    </div>
                </div>
                <div className="mgr-perf-divider" />
                <div className="mgr-perf-item">
                    <div className="mgr-perf-dot" style={{ background: '#FF5C00' }} />
                    <div>
                        <div className="mgr-perf-val">{leadsContacted}</div>
                        <div className="mgr-perf-key">Contacted</div>
                    </div>
                </div>
                <div className="mgr-perf-divider" />
                <div className="mgr-perf-item">
                    <div className="mgr-perf-dot" style={{ background: '#f59e0b' }} />
                    <div>
                        <div className="mgr-perf-val">{missedFollowups}</div>
                        <div className="mgr-perf-key">Missed FU</div>
                    </div>
                </div>
            </div>
        </div>
    );
}

// ── Missed Followups Panel ─────────────────────────────────────────────────────
function MissedFollowupsPanel({ count, list }) {
    return (
        <div className="mgr-card" style={{ height: '100%' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 }}>
                <div>
                    <div className="mgr-section-title">
                        <LuCalendarX size={16} color="#ef4444" />
                        Missed Follow-Ups
                    </div>
                    <div className="mgr-section-sub">{count} pending action{count !== 1 ? 's' : ''}</div>
                </div>
                {count > 0 && (
                    <span style={{ background: '#FEE2E2', color: '#DC2626', fontSize: 11, fontWeight: 700, padding: '3px 10px', borderRadius: 20 }}>{count}</span>
                )}
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, maxHeight: 260, overflowY: 'auto' }}>
                {list.length > 0 ? list.map(f => (
                    <div key={f.id} style={{ display: 'flex', alignItems: 'flex-start', gap: 10, padding: '9px 11px', borderRadius: 10, border: '1px solid #F0F0F0', background: '#FEFEFE' }}>
                        <div style={{ width: 34, height: 34, borderRadius: '50%', background: '#FF5C00', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 13, fontWeight: 800, flexShrink: 0 }}>
                            {(f.lead?.name || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <div style={{ fontSize: 12.5, fontWeight: 600, color: '#1D1D1D', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                {f.lead?.name ?? `Lead #${f.lead_id}`}
                            </div>
                            <div style={{ fontSize: 11, color: '#9CA3AF', marginTop: 2 }}>
                                {f.lead?.assigned_user?.name ?? 'Unassigned'} ·{' '}
                                {new Date(f.next_followup).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })}
                            </div>
                        </div>
                        <span style={{ fontSize: 10, fontWeight: 700, padding: '2px 7px', background: '#FEE2E2', color: '#DC2626', borderRadius: 12, flexShrink: 0 }}>LATE</span>
                    </div>
                )) : (
                    <div className="mgr-empty-state">
                        <LuCircleCheck size={32} color="#10b981" style={{ opacity: 0.5 }} />
                        <span>No missed follow-ups!</span>
                    </div>
                )}
            </div>
        </div>
    );
}

// ── Lead Source Chart ──────────────────────────────────────────────────────────
function LeadSourceChart({ leadSource }) {
    const containerRef = useRef(null);
    const chartRef = useRef(null);

    useApexCharts((ApexCharts) => {
        if (!containerRef.current) return;
        if (chartRef.current) { chartRef.current.destroy(); chartRef.current = null; }
        const labels = leadSource.map(r => r.source || 'Unknown');
        const values = leadSource.map(r => Number(r.total));
        const total  = values.reduce((a, b) => a + b, 0);
        if (!total) return;

        chartRef.current = new ApexCharts(containerRef.current, {
            chart: { type: 'donut', height: 260, background: 'transparent', fontFamily: 'Poppins, sans-serif' },
            series: values,
            labels,
            colors: ['#FF5C00','#FF8C4A','#1D1D1D','#F59E0B','#10B981','#06B6D4','#8B5CF6','#EC4899'],
            plotOptions: {
                pie: { donut: { size: '66%', labels: {
                    show: true,
                    total: { show: true, label: 'Total', color: '#9CA3AF', fontSize: '12px', fontWeight: 600, formatter: () => String(total) },
                    value: { color: '#1D1D1D', fontSize: '22px', fontWeight: 800 },
                    name: { color: '#9CA3AF', fontSize: '12px' },
                }}},
            },
            stroke: { width: 2, colors: ['#ffffff'] },
            legend: { position: 'bottom', labels: { colors: '#6B7280' }, markers: { radius: 3, width: 10, height: 10 }, itemMargin: { horizontal: 8, vertical: 4 }, fontSize: '11px' },
            tooltip: { theme: 'light', y: { formatter: (v) => `${v} leads (${total ? Math.round(v / total * 100) : 0}%)` } },
            dataLabels: { enabled: false },
        });
        chartRef.current.render();
    });

    useEffect(() => () => { if (chartRef.current) { chartRef.current.destroy(); chartRef.current = null; } }, []);
    const total = leadSource.reduce((a, r) => a + Number(r.total), 0);

    return (
        <div className="mgr-card" style={{ height: '100%' }}>
            <div style={{ marginBottom: 12 }}>
                <div className="mgr-section-title">
                    <LuChartPie size={16} color="#FF5C00" />
                    Lead Source Overview
                </div>
                <div className="mgr-section-sub">{total} total leads by source</div>
            </div>
            {total > 0 ? <div ref={containerRef} /> : (
                <div className="mgr-empty-state">
                    <LuChartPie size={32} style={{ opacity: 0.3 }} />
                    <span>No source data yet</span>
                </div>
            )}
        </div>
    );
}

// ── Pipeline Funnel ────────────────────────────────────────────────────────────
function PipelineFunnel({ overallLeads, pipelineStages = {} }) {
    const stages = [
        { label: 'New Leads',  count: overallLeads,                    color: '#1D1D1D' },
        { label: 'Contacted',  count: pipelineStages.contacted  ?? 0,  color: '#FF5C00' },
        { label: 'Interested', count: pipelineStages.interested ?? 0,  color: '#FF8C4A' },
        { label: 'Follow-up',  count: pipelineStages.followup   ?? 0,  color: '#f59e0b' },
        { label: 'Converted',  count: pipelineStages.converted  ?? 0,  color: '#10b981' },
    ];
    const maxCount = Math.max(...stages.map(s => s.count), 1);

    return (
        <div className="mgr-card" style={{ height: '100%' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
                <div>
                    <div className="mgr-section-title">
                        <LuFilter size={16} color="#FF5C00" />
                        Lead Pipeline
                    </div>
                    <div className="mgr-section-sub">Stage-by-stage breakdown</div>
                </div>
                <span className="mgr-section-badge">5 stages</span>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                {stages.map((s, i) => {
                    const pct = Math.round((s.count / maxCount) * 100);
                    const prev = stages[i - 1];
                    const dropOff = prev && prev.count > 0 ? Math.round((1 - s.count / prev.count) * 100) : null;
                    return (
                        <div key={s.label} style={{ display: 'grid', gridTemplateColumns: '90px 1fr 44px 36px', gap: 8, alignItems: 'center' }}>
                            <div style={{ fontSize: 12, fontWeight: 600, color: '#6B7280' }}>{s.label}</div>
                            <div style={{ background: '#F3F4F6', borderRadius: 6, height: 22, overflow: 'hidden' }}>
                                <div style={{ height: '100%', width: `${pct}%`, background: s.color, borderRadius: 6, display: 'flex', alignItems: 'center', paddingLeft: 8, minWidth: s.count > 0 ? 26 : 0, opacity: 0.85 }}>
                                    {s.count > 0 && <span style={{ fontSize: 10, fontWeight: 700, color: '#fff' }}>{s.count}</span>}
                                </div>
                            </div>
                            <div style={{ fontSize: 13, fontWeight: 700, color: s.color, textAlign: 'right' }}>{s.count}</div>
                            <div style={{ fontSize: 10, fontWeight: 700, color: '#ef4444', textAlign: 'right' }}>
                                {dropOff != null && dropOff > 0 ? `↓${dropOff}%` : ''}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

// ── Leaderboard ────────────────────────────────────────────────────────────────
function LeaderboardTable({ telecallerStats, telecallersUrl }) {
    const medals = ['🥇', '🥈', '🥉'];
    return (
        <div className="mgr-card" style={{ marginBottom: 16 }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 4 }}>
                <div>
                    <div className="mgr-section-title">
                        <LuTrophy size={16} color="#f59e0b" />
                        Performance Leaderboard
                    </div>
                    <div className="mgr-section-sub">Ranked by conversion rate</div>
                </div>
                <a href={telecallersUrl} style={{ fontSize: 12, color: '#FF5C00', fontWeight: 600, textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4, background: 'rgba(255,92,0,0.08)', padding: '5px 12px', borderRadius: 8 }}>
                    View All <LuArrowRight size={13} />
                </a>
            </div>
            <div style={{ overflowX: 'auto', marginTop: 14 }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
                    <thead>
                        <tr>
                            <th className="mgr-th" style={{ width: 40 }}>#</th>
                            <th className="mgr-th">Telecaller</th>
                            <th className="mgr-th">Assigned</th>
                            <th className="mgr-th">Calls</th>
                            <th className="mgr-th" style={{ textAlign: 'center' }}>Pending FU</th>
                            <th className="mgr-th" style={{ textAlign: 'right' }}>Conv. Rate</th>
                            <th className="mgr-th" style={{ minWidth: 100 }}>Progress</th>
                            <th className="mgr-th">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {telecallerStats.length > 0 ? (
                            [...telecallerStats].sort((a, b) => parseFloat(b.conversion_rate) - parseFloat(a.conversion_rate)).map((t, idx) => {
                                const rate = parseFloat(t.conversion_rate);
                                const rateColor = rate >= 30 ? '#16a34a' : rate >= 10 ? '#d97706' : '#dc2626';
                                const rateBg    = rate >= 30 ? '#DCFCE7' : rate >= 10 ? '#FEF9C3' : '#FEE2E2';
                                const color     = avatarColor(t.name);
                                return (
                                    <tr key={t.id} style={{ borderBottom: '1px solid #F9FAFB' }}>
                                        <td className="mgr-td">
                                            {idx < 3 ? <span style={{ fontSize: 17 }}>{medals[idx]}</span> : (
                                                <span style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 24, height: 24, borderRadius: '50%', background: '#F3F4F6', color: '#6B7280', fontSize: 11, fontWeight: 700 }}>{idx + 1}</span>
                                            )}
                                        </td>
                                        <td className="mgr-td">
                                            <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                                                <div style={{ width: 30, height: 30, borderRadius: '50%', background: color, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 11, fontWeight: 700, flexShrink: 0 }}>
                                                    {initials(t.name).toUpperCase()}
                                                </div>
                                                <span style={{ fontWeight: 600, color: '#1D1D1D' }}>{t.name}</span>
                                            </div>
                                        </td>
                                        <td className="mgr-td" style={{ color: '#9CA3AF' }}>{t.assigned_count}</td>
                                        <td className="mgr-td" style={{ color: '#9CA3AF' }}>{t.total_calls}</td>
                                        <td className="mgr-td" style={{ textAlign: 'center' }}>
                                            <span style={{ color: t.pending_followups > 0 ? '#d97706' : '#9CA3AF', fontWeight: t.pending_followups > 0 ? 700 : 400 }}>{t.pending_followups}</span>
                                        </td>
                                        <td className="mgr-td" style={{ textAlign: 'right' }}>
                                            <span style={{ background: rateBg, color: rateColor, padding: '3px 8px', borderRadius: 6, fontSize: 11, fontWeight: 700 }}>{rate.toFixed(1)}%</span>
                                        </td>
                                        <td className="mgr-td">
                                            <div style={{ background: '#F3F4F6', borderRadius: 4, height: 7, overflow: 'hidden', minWidth: 80 }}>
                                                <div style={{ background: 'linear-gradient(90deg,#FF5C00,#FF8C4A)', height: '100%', width: `${Math.min(rate, 100)}%`, borderRadius: 4 }} />
                                            </div>
                                        </td>
                                        <td className="mgr-td">
                                            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#DCFCE7', color: '#16A34A', padding: '2px 8px', borderRadius: 20, fontSize: 11, fontWeight: 600 }}>
                                                <div style={{ width: 5, height: 5, borderRadius: '50%', background: '#10b981' }} />
                                                Active
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })
                        ) : (
                            <tr><td colSpan={8}>
                                <div className="mgr-empty-state">
                                    <LuTrophy size={32} style={{ opacity: 0.3 }} />
                                    <span>No data yet.</span>
                                </div>
                            </td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// ── Activity Feed ──────────────────────────────────────────────────────────────
function ActivityFeed({ initial, snapshotUrl, telecallerStats }) {
    const [presence, setPresence] = useState(initial ?? []);

    const refresh = useCallback(async () => {
        try {
            const res = await fetch(snapshotUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (Array.isArray(data.telecallers)) setPresence(data.telecallers);
        } catch (_) {}
    }, [snapshotUrl]);

    useEffect(() => { refresh(); const t = setInterval(refresh, 30_000); return () => clearInterval(t); }, [refresh]);

    const enriched = telecallerStats.map(tc => ({ ...tc, is_online: Boolean((presence.find(x => x.id === tc.id) || {}).is_online) }));
    const online   = enriched.filter(t => t.is_online);

    const feedItems = [];
    enriched.forEach(tc => {
        if (tc.total_calls > 0) feedItems.push({ id: `call-${tc.id}`, type: 'call', iconBg: 'rgba(255,92,0,0.10)', iconColor: '#FF5C00', text: `${tc.name} made ${tc.total_calls} call${tc.total_calls !== 1 ? 's' : ''}`, sub: `${tc.conversion_rate}% conversion · ${tc.pending_followups} pending`, online: tc.is_online });
        if (parseFloat(tc.conversion_rate) > 0) feedItems.push({ id: `conv-${tc.id}`, type: 'check', iconBg: 'rgba(16,185,129,0.10)', iconColor: '#10b981', text: `${tc.name} achieved ${tc.conversion_rate}% conversion`, sub: `${tc.assigned_count} leads assigned`, online: tc.is_online });
    });

    return (
        <div className="mgr-card" style={{ height: '100%' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
                <div>
                    <div className="mgr-section-title">
                        <LuHeadphones size={16} color="#10b981" />
                        Team Status
                    </div>
                    <div className="mgr-section-sub">Live activity &amp; presence</div>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 6, background: '#DCFCE7', border: '1px solid rgba(16,185,129,0.25)', borderRadius: 20, padding: '4px 12px' }}>
                    <div style={{ width: 6, height: 6, borderRadius: '50%', background: '#10b981', animation: 'mgrPulse 1.6s infinite' }} />
                    <span style={{ fontSize: 11, fontWeight: 700, color: '#16A34A' }}>{online.length} online</span>
                </div>
            </div>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginBottom: 14 }}>
                {enriched.map(tc => (
                    <div key={tc.id} style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: tc.is_online ? '#DCFCE7' : '#F9FAFB', border: `1px solid ${tc.is_online ? 'rgba(16,185,129,0.30)' : '#E5E7EB'}`, borderRadius: 20, padding: '4px 10px' }}>
                        <div style={{ width: 6, height: 6, borderRadius: '50%', background: tc.is_online ? '#10b981' : '#D1D5DB' }} />
                        <span style={{ fontSize: 11, fontWeight: 600, color: tc.is_online ? '#065f46' : '#9CA3AF' }}>{tc.name}</span>
                    </div>
                ))}
                {enriched.length === 0 && <span style={{ fontSize: 12, color: '#9CA3AF' }}>No telecallers found.</span>}
            </div>
            {feedItems.length > 0 && (
                <>
                    <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.8px', textTransform: 'uppercase', color: '#9CA3AF', marginBottom: 8 }}>Recent Activity</div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 7 }}>
                        {feedItems.slice(0, 6).map(item => (
                            <div key={item.id} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 10px', borderRadius: 10, border: '1px solid #F0F0F0', background: '#FAFAFA' }}>
                                <div style={{ width: 30, height: 30, borderRadius: 8, flexShrink: 0, background: item.iconBg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    {item.type === 'call'
                                        ? <LuPhone size={14} color={item.iconColor} />
                                        : <LuCircleCheck size={14} color={item.iconColor} />
                                    }
                                </div>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <p style={{ fontSize: 12, fontWeight: 600, color: '#1D1D1D', margin: 0, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{item.text}</p>
                                    <p style={{ fontSize: 11, color: '#9CA3AF', margin: 0, marginTop: 1 }}>{item.sub}</p>
                                </div>
                                <div style={{ width: 7, height: 7, borderRadius: '50%', background: item.online ? '#10b981' : '#E5E7EB', flexShrink: 0 }} />
                            </div>
                        ))}
                    </div>
                </>
            )}
            {feedItems.length === 0 && enriched.length === 0 && (
                <div className="mgr-empty-state">
                    <LuHeadphones size={32} style={{ opacity: 0.3 }} />
                    <span>No telecaller activity yet.</span>
                </div>
            )}
        </div>
    );
}

// ── Missed Callbacks Table ─────────────────────────────────────────────────────
function MissedCallbacksTable({ calls }) {
    return (
        <div className="mgr-card" style={{ marginBottom: 16 }}>
            <div style={{ marginBottom: 4 }}>
                <div className="mgr-section-title">
                    <LuPhoneMissed size={16} color="#ef4444" />
                    Missed Inbound Callbacks
                </div>
                <div className="mgr-section-sub">Inbound calls awaiting follow-up</div>
            </div>
            <div style={{ overflowX: 'auto', marginTop: 14 }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
                    <thead>
                        <tr>
                            <th className="mgr-th">Time</th>
                            <th className="mgr-th">Lead</th>
                            <th className="mgr-th">Number</th>
                            <th className="mgr-th">Status</th>
                            <th className="mgr-th">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {calls.length > 0 ? calls.map(c => (
                            <tr key={c.id} style={{ borderBottom: '1px solid #F9FAFB' }}>
                                <td className="mgr-td" style={{ color: '#9CA3AF', whiteSpace: 'nowrap' }}>{c.created_at_formatted}</td>
                                <td className="mgr-td" style={{ fontWeight: 600, color: '#1D1D1D' }}>{c.lead_code ? `${c.lead_code} – ${c.lead_name}` : (c.lead_name || 'Unknown')}</td>
                                <td className="mgr-td" style={{ color: '#9CA3AF', fontFamily: 'monospace', fontSize: 11 }}>{c.customer_number || c.lead_phone || '—'}</td>
                                <td className="mgr-td"><span style={{ background: '#FEE2E2', color: '#DC2626', fontSize: 11, fontWeight: 600, padding: '3px 9px', borderRadius: 20 }}>Missed</span></td>
                                <td className="mgr-td">
                                    {c.encrypted_lead_id ? (
                                        <a href={`/manager/leads/${c.encrypted_lead_id}`} style={{ background: '#FF5C00', color: '#fff', border: 'none', borderRadius: 7, padding: '5px 12px', fontSize: 11, fontWeight: 600, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}>
                                            <LuPhone size={11} /> Call Back
                                        </a>
                                    ) : (
                                        <button style={{ background: '#F3F4F6', color: '#9CA3AF', border: 'none', borderRadius: 7, padding: '5px 12px', fontSize: 11, fontWeight: 600, cursor: 'not-allowed' }} disabled>Call Back</button>
                                    )}
                                </td>
                            </tr>
                        )) : (
                            <tr><td colSpan={5}>
                                <div className="mgr-empty-state">
                                    <LuPhoneIncoming size={32} style={{ opacity: 0.3 }} />
                                    <span>No missed inbound callbacks.</span>
                                </div>
                            </td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// ── Course Performance ─────────────────────────────────────────────────────────
function CoursePerformance({ courseStats }) {
    if (!courseStats.length) return null;
    const maxTotal = Math.max(...courseStats.map(r => r.total), 1);
    return (
        <div className="mgr-card" style={{ marginBottom: 16 }}>
            <div style={{ marginBottom: 4 }}>
                <div className="mgr-section-title">
                    <LuGraduationCap size={16} color="#8b5cf6" />
                    Course Performance
                </div>
                <div className="mgr-section-sub">Lead volume &amp; conversion by course</div>
            </div>
            <div style={{ overflowX: 'auto', marginTop: 14 }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
                    <thead>
                        <tr>
                            <th className="mgr-th">Course</th>
                            <th className="mgr-th">Leads</th>
                            <th className="mgr-th">Converted</th>
                            <th className="mgr-th">Rate</th>
                            <th className="mgr-th" style={{ minWidth: 180 }}>Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        {courseStats.map((row, i) => {
                            const rate = parseFloat(row.rate);
                            const rateColor = rate >= 30 ? '#16a34a' : rate >= 10 ? '#d97706' : '#dc2626';
                            const rateBg    = rate >= 30 ? '#DCFCE7' : rate >= 10 ? '#FEF9C3' : '#FEE2E2';
                            const barPct    = Math.round((row.total / maxTotal) * 100);
                            return (
                                <tr key={i} style={{ borderBottom: '1px solid #F9FAFB' }}>
                                    <td className="mgr-td" style={{ fontWeight: 600, color: '#1D1D1D' }}>{row.course}</td>
                                    <td className="mgr-td" style={{ color: '#9CA3AF' }}>{row.total}</td>
                                    <td className="mgr-td" style={{ color: '#9CA3AF' }}>{row.conversions}</td>
                                    <td className="mgr-td"><span style={{ background: rateBg, color: rateColor, padding: '3px 8px', borderRadius: 6, fontSize: 11, fontWeight: 700 }}>{rate.toFixed(1)}%</span></td>
                                    <td className="mgr-td">
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <div style={{ flex: 1, background: '#F3F4F6', borderRadius: 4, height: 9, overflow: 'hidden', minWidth: 100 }}>
                                                <div style={{ background: '#FF5C00', height: '100%', width: `${barPct}%`, borderRadius: 4, minWidth: row.total > 0 ? 4 : 0 }} />
                                            </div>
                                            <span style={{ fontSize: 11, color: '#9CA3AF', whiteSpace: 'nowrap' }}>{row.total} leads</span>
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// ── Follow-Up Calendar ─────────────────────────────────────────────────────────
function FollowupCalendar({ initialData, calendarDataUrl }) {
    const todayDate = new Date();
    const todayY = todayDate.getFullYear(), todayM = todayDate.getMonth() + 1, todayD = todayDate.getDate();
    const [state, setState] = useState({ year: todayY, month: todayM, days: initialData ?? {} });
    const [loading, setLoading] = useState(false);

    async function navigate(year, month) {
        setLoading(true);
        try {
            const res = await fetch(`${calendarDataUrl}?year=${year}&month=${month}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            setState({ year: data.year, month: data.month, days: data.days || {} });
        } catch (_) {}
        setLoading(false);
    }
    function prevMonth() { let { year, month } = state; if (--month < 1) { month = 12; year--; } navigate(year, month); }
    function nextMonth() { let { year, month } = state; if (++month > 12) { month = 1; year++; } navigate(year, month); }

    const { year, month, days } = state;
    const daysInMonth = new Date(year, month, 0).getDate();
    const firstDow    = (new Date(year, month - 1, 1).getDay() + 6) % 7;
    const isThisMonth = year === todayY && month === todayM;

    const cells = [];
    for (let i = 0; i < firstDow; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) cells.push(d);

    function densityStyle(count) {
        if (!count) return null;
        if (count <= 3) return { bg: '#DCFCE7', color: '#16A34A' };
        if (count <= 7) return { bg: '#FEF9C3', color: '#CA8A04' };
        return { bg: '#FFE4CC', color: '#FF5C00' };
    }

    return (
        <div style={{ display: 'grid', gridTemplateColumns: '260px 1fr', borderRadius: 14, overflow: 'hidden', boxShadow: '0 2px 8px rgba(0,0,0,0.06)', marginBottom: 16 }}>
            {/* Left info panel */}
            <div style={{ background: '#1D1D1D', padding: '26px 22px', display: 'flex', flexDirection: 'column' }}>
                <div style={{ fontSize: 10, fontWeight: 700, color: '#FF5C00', textTransform: 'uppercase', letterSpacing: '1px', marginBottom: 10 }}>Schedule</div>
                <h3 style={{ fontSize: 19, fontWeight: 700, color: '#FEFEFE', marginBottom: 12, lineHeight: 1.3 }}>Team Follow-Up Calendar</h3>
                <p style={{ fontSize: 12, color: '#9CA3AF', lineHeight: 1.75, marginBottom: 24, flex: 1 }}>
                    Track your team's scheduled follow-ups. Highlighted dates show the follow-up load — click to view details.
                </p>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                    {[
                        { color: '#FF5C00', label: 'High',     desc: '8+ follow-ups scheduled' },
                        { color: '#F59E0B', label: 'Moderate', desc: '4–7 follow-ups scheduled' },
                        { color: '#16A34A', label: 'Low',      desc: '1–3 follow-ups scheduled' },
                    ].map(x => (
                        <div key={x.label} style={{ display: 'flex', gap: 10, alignItems: 'flex-start' }}>
                            <div style={{ width: 10, height: 10, borderRadius: 3, background: x.color, flexShrink: 0, marginTop: 2 }} />
                            <div>
                                <div style={{ fontSize: 12, fontWeight: 600, color: '#FEFEFE', marginBottom: 2 }}>{x.label} Call Count</div>
                                <div style={{ fontSize: 11, color: '#6B7280', lineHeight: 1.5 }}>{x.desc}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Right calendar grid */}
            <div style={{ background: '#FEFEFE', padding: 24, border: '1px solid #F0F0F0', borderLeft: 'none', opacity: loading ? 0.5 : 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
                    <button onClick={prevMonth} style={{ width: 30, height: 30, border: '1px solid #E5E7EB', borderRadius: 7, background: '#fff', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#6B7280' }}>
                        <LuChevronLeft size={15} />
                    </button>
                    <span style={{ fontSize: 14, fontWeight: 700, color: '#1D1D1D' }}>{MONTH_NAMES[month - 1]} {year}</span>
                    <button onClick={nextMonth} style={{ width: 30, height: 30, border: '1px solid #E5E7EB', borderRadius: 7, background: '#fff', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#6B7280' }}>
                        <LuChevronRight size={15} />
                    </button>
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7,1fr)', gap: 4, marginBottom: 4 }}>
                    {['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d => (
                        <div key={d} style={{ textAlign: 'center', fontSize: 10, fontWeight: 700, color: '#9CA3AF', textTransform: 'uppercase', letterSpacing: '.4px', padding: '5px 0' }}>{d}</div>
                    ))}
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7,1fr)', gap: 4 }}>
                    {cells.map((d, i) => {
                        if (d === null) return <div key={`e${i}`} />;
                        const key   = `${year}-${pad(month)}-${pad(d)}`;
                        const count = days[key] || 0;
                        const isToday = isThisMonth && d === todayD;
                        const isPast  = new Date(year, month - 1, d) < new Date(todayY, todayM - 1, todayD);
                        const ds      = densityStyle(count);
                        const href    = count > 0 ? (isToday ? '/manager/followups/today' : isPast ? '/manager/followups/overdue' : '/manager/followups/upcoming') : '';
                        return (
                            <div key={key} onClick={() => href && (window.location.href = href)}
                                style={{
                                    textAlign: 'center', fontSize: 12, padding: '7px 3px', borderRadius: 8,
                                    border: isToday ? '2px solid #FF5C00' : `1px solid ${ds ? ds.bg : 'transparent'}`,
                                    background: ds ? ds.bg : (isPast ? '#F9FAFB' : '#fff'),
                                    color: isToday ? '#FF5C00' : ds ? ds.color : (isPast ? '#D1D5DB' : '#374151'),
                                    fontWeight: isToday ? 700 : ds ? 600 : 400,
                                    cursor: count > 0 ? 'pointer' : 'default',
                                    position: 'relative',
                                }}
                                title={count > 0 ? `${count} follow-up${count !== 1 ? 's' : ''}` : undefined}>
                                {d}
                                {count > 0 && <span style={{ position: 'absolute', bottom: 2, left: '50%', transform: 'translateX(-50%)', width: 4, height: 4, borderRadius: '50%', background: 'currentColor', display: 'block' }} />}
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}

// ── Main Dashboard ─────────────────────────────────────────────────────────────
export default function Dashboard({
    period, leadsToday, leadsWeek, leadsMonth,
    overallLeads, overallCalls,
    totalCallsMade, totalCallDurationSec, leadsContacted, pipelineStages,
    whatsAppConversations, conversionRate,
    bestPerformingTelecaller,
    missedFollowups, missedFollowupList,
    leadSource, telecallerStats,
    telecallerPresence, missedInboundCalls,
    courseStats, followupCalendar,
    presenceSnapshotUrl, calendarDataUrl,
    telecallersUrl, leadsCreateUrl,
}) {
    const { auth } = usePage().props;
    const userName  = auth?.user?.name ?? 'Manager';

    const periodLabels = { today: 'Today', week: 'This Week', month: 'This Month' };
    const periodLeads  = { today: leadsToday, week: leadsWeek, month: leadsMonth }[period] ?? leadsToday;
    const durationLabel = toTimeLabel(totalCallDurationSec);

    function changePeriod(e) {
        router.get(window.location.pathname, { period: e.target.value }, { preserveScroll: false });
    }

    return (
        <>
            <Head>
                <title>Manager Dashboard</title>
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
            </Head>
            <style>{DASH_CSS}</style>

            <div className="mgr-dash">
                {/* Top bar */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16, flexWrap: 'wrap', gap: 12 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <h2 style={{ fontSize: 20, fontWeight: 700, color: '#1D1D1D', margin: 0 }}>Manager Dashboard</h2>
                        <div className="live-badge"><div className="live-dot" />LIVE</div>
                    </div>
                    <div className="mgr-period-wrap">
                        <LuCalendar size={14} color="#9CA3AF" />
                        <select value={period} onChange={changePeriod}>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                        <LuChevronDown size={14} color="#9CA3AF" />
                    </div>
                </div>

                {/* Hero Banner */}
                <HeroBanner
                    name={userName}
                    periodLeads={periodLeads}
                    totalCallsMade={totalCallsMade}
                    conversionRate={conversionRate}
                    missedFollowups={missedFollowups}
                    overallLeads={overallLeads}
                    overallCalls={overallCalls}
                />

                {/* KPI Row 1 — 4 cards */}
                <div className="row g-3 mb-3">
                    <div className="col-6 col-md-3">
                        <StatCard icon={<LuUsers size={20} />} label={`Total Leads (${periodLabels[period]})`} value={periodLeads}
                            sub={`T: ${leadsToday} · W: ${leadsWeek} · M: ${leadsMonth} · Overall: ${overallLeads}`}
                            badgeGrad="linear-gradient(135deg,#FF5C00,#FF8C4A)" iconClass="blue"
                            barColor="#FF5C00" barPct={(periodLeads / Math.max(1, 100)) * 100} />
                    </div>
                    <div className="col-6 col-md-3">
                        <StatCard icon={<LuPhone size={20} />} label={`Calls Made (${periodLabels[period]})`} value={totalCallsMade}
                            sub={`Overall: ${overallCalls}`}
                            badgeGrad="linear-gradient(135deg,#1D1D1D,#374151)" iconClass="purple"
                            barColor="#1D1D1D" barPct={(totalCallsMade / Math.max(1, 50)) * 100} />
                    </div>
                    <div className="col-6 col-md-3">
                        <StatCard icon={<LuTimer size={20} />} label="Call Duration" displayValue={durationLabel}
                            badgeGrad="linear-gradient(135deg,#f59e0b,#d97706)" iconClass="amber"
                            barColor="#f59e0b" barPct={(totalCallDurationSec / Math.max(1, 7200)) * 100} />
                    </div>
                    <div className="col-6 col-md-3">
                        <StatCard icon={<LuMessageSquare size={20} />} label="WhatsApp Chats" value={whatsAppConversations}
                            badgeGrad="linear-gradient(135deg,#10b981,#059669)" iconClass="green"
                            barColor="#10b981" barPct={(whatsAppConversations / Math.max(1, 20)) * 100} />
                    </div>
                </div>

                {/* KPI Row 2 — 3 cards */}
                <div className="row g-3 mb-4">
                    <div className="col-6 col-md-4">
                        <StatCard icon={<LuTrendingUp size={20} />} label="Conversion Rate" displayValue={`${parseFloat(conversionRate).toFixed(1)}%`}
                            badgeGrad="linear-gradient(135deg,#FF5C00,#FF8C4A)" iconClass="cyan"
                            barColor="#FF5C00" barPct={parseFloat(conversionRate)} />
                    </div>
                    <div className="col-6 col-md-4">
                        <StatCard icon={<LuTrophy size={20} />} label="Top Telecaller" displayValue={bestPerformingTelecaller?.name ?? '—'}
                            sub={bestPerformingTelecaller ? `${parseFloat(bestPerformingTelecaller.conversion_rate).toFixed(1)}% conversion` : null}
                            badgeGrad="linear-gradient(135deg,#f59e0b,#ef4444)" iconClass="amber"
                            barColor="#f59e0b" barPct={bestPerformingTelecaller ? parseFloat(bestPerformingTelecaller.conversion_rate) : 0} />
                    </div>
                    <div className="col-12 col-md-4">
                        <StatCard icon={<LuCalendarX size={20} />} label="Missed Follow-Ups" value={missedFollowups}
                            badgeGrad="linear-gradient(135deg,#ef4444,#dc2626)" iconClass="red"
                            barColor="#ef4444" barPct={(missedFollowups / Math.max(1, 10)) * 100}
                            highlight={missedFollowups > 0 ? 'danger' : null} />
                    </div>
                </div>

                {/* Quick Actions + Missed Followups */}
                <div className="row g-3 mb-4">
                    <div className="col-lg-8">
                        <QuickActions
                            leadsCreateUrl={leadsCreateUrl}
                            telecallersUrl={telecallersUrl}
                            missedFollowups={missedFollowups}
                            periodLeads={periodLeads}
                            leadsContacted={leadsContacted}
                        />
                    </div>
                    <div className="col-lg-4">
                        <MissedFollowupsPanel count={missedFollowups} list={missedFollowupList} />
                    </div>
                </div>

                {/* Charts: Lead Source + Pipeline */}
                <div className="row g-3 mb-4">
                    <div className="col-lg-5">
                        <LeadSourceChart leadSource={leadSource} />
                    </div>
                    <div className="col-lg-7">
                        <PipelineFunnel overallLeads={overallLeads} pipelineStages={pipelineStages} />
                    </div>
                </div>

                {/* Leaderboard */}
                <LeaderboardTable telecallerStats={telecallerStats} telecallersUrl={telecallersUrl} />

                {/* Team Status + Missed Followups detail */}
                <div className="row g-3 mb-4">
                    <div className="col-lg-7">
                        <ActivityFeed initial={telecallerPresence} snapshotUrl={presenceSnapshotUrl} telecallerStats={telecallerStats} />
                    </div>
                    <div className="col-lg-5">
                        <MissedFollowupsPanel count={missedFollowups} list={missedFollowupList} />
                    </div>
                </div>

                {/* Missed Inbound Callbacks */}
                <MissedCallbacksTable calls={missedInboundCalls} />

                {/* Course Performance */}
                <CoursePerformance courseStats={courseStats} />

                {/* Follow-Up Calendar */}
                <FollowupCalendar initialData={followupCalendar} calendarDataUrl={calendarDataUrl} />
            </div>
        </>
    );
}
