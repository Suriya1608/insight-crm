import { Head, router } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';

// ── Helpers ───────────────────────────────────────────────────────────────────
function toTimeLabel(sec) {
    const s = Number(sec || 0);
    const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), ss = s % 60;
    return [h, m, ss].map(v => String(v).padStart(2, '0')).join(':');
}
const AVATAR_COLORS = ['#6366F1','#10B981','#F59E0B','#F43F5E','#8B5CF6','#06B6D4','#EC4899','#14B8A6'];
function avatarColor(str) {
    let h = 0;
    for (let i = 0; i < (str||'').length; i++) h = (h * 31 + str.charCodeAt(i)) >>> 0;
    return AVATAR_COLORS[h % AVATAR_COLORS.length];
}
function initials(name) {
    const p = (name||'?').trim().split(/\s+/);
    return p.length >= 2 ? p[0][0]+p[1][0] : p[0].slice(0,2);
}
function useCountUp(target, duration = 1200) {
    const [val, setVal] = useState(0);
    useEffect(() => {
        if (!target) { setVal(0); return; }
        let start = null;
        const step = ts => {
            if (!start) start = ts;
            const p = Math.min((ts - start) / duration, 1);
            setVal(Math.floor((1 - Math.pow(1-p, 3)) * target));
            if (p < 1) requestAnimationFrame(step);
        };
        const raf = requestAnimationFrame(step);
        return () => cancelAnimationFrame(raf);
    }, [target, duration]);
    return val;
}

// Singleton ApexCharts loader — prevents duplicate script tags
let _apexPromise = null;
function loadApex() {
    if (typeof window === 'undefined') return Promise.resolve(null);
    if (window.ApexCharts) return Promise.resolve(window.ApexCharts);
    if (_apexPromise) return _apexPromise;
    _apexPromise = new Promise(resolve => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js';
        s.onload = () => resolve(window.ApexCharts);
        document.head.appendChild(s);
    });
    return _apexPromise;
}
function useApexChart(factory, deps = []) {
    const containerRef = useRef(null);
    const chartRef     = useRef(null);
    const factoryRef   = useRef(factory);
    factoryRef.current = factory;
    useEffect(() => {
        let active = true;
        loadApex().then(AC => {
            if (!active || !containerRef.current || !AC) return;
            if (chartRef.current) { chartRef.current.destroy(); chartRef.current = null; }
            const opts = factoryRef.current(AC);
            if (!opts) return;
            chartRef.current = new AC(containerRef.current, opts);
            chartRef.current.render();
        });
        return () => {
            active = false;
            if (chartRef.current) { chartRef.current.destroy(); chartRef.current = null; }
        };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, deps);
    return containerRef;
}

// ── Quick Reports Bar (shown at TOP) ─────────────────────────────────────────
function QuickReportsBar({ reportsUrl }) {
    const reports = [
        { key: 'telecallerPerformance', icon: 'support_agent',   label: 'Telecaller Performance', color: '#6366F1' },
        { key: 'managerPerformance',    icon: 'manage_accounts', label: 'Manager Performance',    color: '#8B5CF6' },
        { key: 'conversion',            icon: 'trending_up',     label: 'Conversion Report',      color: '#10B981' },
        { key: 'leadSource',            icon: 'track_changes',   label: 'Lead Source Report',     color: '#06B6D4' },
        { key: 'period',                icon: 'date_range',      label: 'Period Report',          color: '#F59E0B' },
        { key: 'callEfficiency',        icon: 'call_made',       label: 'Call Efficiency',        color: '#F43F5E' },
        { key: 'responseTime',          icon: 'timer',           label: 'Response Time',          color: '#EC4899' },
    ];
    return (
        <div style={{
            background: '#0D1729', border: '1px solid #1E293B', borderRadius: 14,
            padding: '12px 18px', marginBottom: 20,
            display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap',
        }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6, flexShrink: 0 }}>
                <span className="material-icons" style={{ fontSize: 15, color: '#6366F1' }}>bolt</span>
                <span style={{ fontSize: 11, fontWeight: 700, color: '#94A3B8', letterSpacing: '0.06em' }}>QUICK LINKS</span>
            </div>
            <div style={{ width: 1, height: 22, background: '#1E293B', flexShrink: 0 }} />
            {reports.map(r => (
                <a key={r.key} href={reportsUrl[r.key]} style={{ textDecoration: 'none' }}>
                    <div
                        style={{ display: 'flex', alignItems: 'center', gap: 6, background: `${r.color}12`, border: `1px solid ${r.color}30`, borderRadius: 20, padding: '5px 12px 5px 8px', cursor: 'pointer', transition: 'all 0.15s' }}
                        onMouseEnter={e => { e.currentTarget.style.background = `${r.color}25`; e.currentTarget.style.borderColor = `${r.color}55`; }}
                        onMouseLeave={e => { e.currentTarget.style.background = `${r.color}12`; e.currentTarget.style.borderColor = `${r.color}30`; }}
                    >
                        <span className="material-icons" style={{ fontSize: 14, color: r.color }}>{r.icon}</span>
                        <span style={{ fontSize: 11, fontWeight: 700, color: r.color }}>{r.label}</span>
                    </div>
                </a>
            ))}
        </div>
    );
}

// ── KPI Card ─────────────────────────────────────────────────────────────────
function KpiCard({ icon, label, rawValue, displayValue, sub, iconBg, iconColor, iconShadow, delay, accentColor }) {
    const count   = useCountUp(rawValue ?? 0);
    const display = rawValue != null ? count.toLocaleString() : (displayValue ?? '—');
    return (
        <div className="kpi-card" style={{ animationDelay: `${delay}ms`, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 2, background: `linear-gradient(90deg, ${accentColor || '#6366F1'}, transparent)` }} />
            <div className="kpi-card-top">
                <div className="kpi-icon" style={{ background: iconBg, boxShadow: iconShadow }}>
                    <span className="material-icons" style={{ color: iconColor }}>{icon}</span>
                </div>
            </div>
            <div className="kpi-value">{display}</div>
            <div className="kpi-label">{label}</div>
            {sub && <div style={{ fontSize: 11, color: '#64748b', marginTop: 2 }}>{sub}</div>}
        </div>
    );
}

// ── Insight Strip ─────────────────────────────────────────────────────────────
function InsightStrip({ telecallerStats, managerStats, courseStats }) {
    const topTc     = telecallerStats[0];
    const topMgr    = managerStats.length ? managerStats.reduce((a, b) => parseFloat(b.conversion_rate) > parseFloat(a.conversion_rate) ? b : a) : null;
    const topCourse = courseStats.length  ? courseStats.reduce((a, b) => parseFloat(b.rate) > parseFloat(a.rate) ? b : a) : null;
    const insights  = [
        topTc && { icon: 'emoji_events',     color: '#F59E0B', label: 'Top Caller',   name: topTc.name,    stat: `${topTc.total_calls} calls · ${toTimeLabel(topTc.talk_time_sec)} talk time` },
        topMgr && { icon: 'workspace_premium', color: '#6366F1', label: 'Top Manager',  name: topMgr.name,   stat: `${parseFloat(topMgr.conversion_rate).toFixed(1)}% conversion rate` },
        topCourse && parseFloat(topCourse.rate) > 0 && { icon: 'school', color: '#10B981', label: 'Best Course', name: topCourse.course, stat: `${topCourse.rate}% conv · ${topCourse.total} leads` },
    ].filter(Boolean);
    if (!insights.length) return null;
    return (
        <div style={{ display: 'grid', gridTemplateColumns: `repeat(${Math.min(insights.length, 3)}, 1fr)`, gap: 12, marginBottom: 20 }}>
            {insights.map(ins => (
                <div key={ins.label} style={{ background: `linear-gradient(135deg, ${ins.color}08 0%, ${ins.color}18 100%)`, border: `1px solid ${ins.color}30`, borderRadius: 12, padding: '14px 16px', display: 'flex', alignItems: 'center', gap: 14 }}>
                    <div style={{ width: 44, height: 44, borderRadius: 12, flexShrink: 0, background: `${ins.color}20`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <span className="material-icons" style={{ fontSize: 22, color: ins.color }}>{ins.icon}</span>
                    </div>
                    <div style={{ minWidth: 0 }}>
                        <div style={{ fontSize: 10, fontWeight: 700, color: ins.color, textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 3 }}>{ins.label}</div>
                        <div style={{ fontSize: 14, fontWeight: 800, color: '#F1F5F9', lineHeight: 1.2, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{ins.name}</div>
                        <div style={{ fontSize: 11, color: '#64748b', marginTop: 3 }}>{ins.stat}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}

// ── 14-day Activity Trend Chart ───────────────────────────────────────────────
function TrendChart({ dailyTrend }) {
    const labels = dailyTrend.map(d => d.label);
    const leads  = dailyTrend.map(d => d.leads);
    const calls  = dailyTrend.map(d => d.calls);
    const ref = useApexChart(() => ({
        chart: { type: 'area', height: 210, background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
        series: [{ name: 'Leads', data: leads }, { name: 'Calls', data: calls }],
        colors: ['#6366F1', '#10B981'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 100] } },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: { categories: labels, labels: { style: { colors: '#475569', fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: '#475569', fontSize: '11px' } }, min: 0 },
        grid: { borderColor: '#1E293B', strokeDashArray: 4 },
        theme: { mode: 'dark' },
        legend: { labels: { colors: '#94A3B8' }, markers: { radius: 3, width: 10, height: 10 } },
        tooltip: { theme: 'dark' },
        dataLabels: { enabled: false },
        markers: { size: 0, hover: { size: 4 } },
    }), [JSON.stringify(dailyTrend)]);
    return (
        <div className="dark-card" style={{ marginBottom: 0 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                <div>
                    <p className="dark-card-title" style={{ marginBottom: 2 }}>Activity Trend</p>
                    <p style={{ fontSize: 12, color: '#475569', margin: 0 }}>Daily leads & calls — last 14 days</p>
                </div>
                <div style={{ display: 'flex', gap: 14 }}>
                    {[['#6366F1','Leads'],['#10B981','Calls']].map(([c,l]) => (
                        <span key={l} style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 11, color: '#64748b' }}>
                            <span style={{ width: 10, height: 3, background: c, borderRadius: 2, display: 'inline-block' }} />{l}
                        </span>
                    ))}
                </div>
            </div>
            <div ref={ref} />
        </div>
    );
}

// ── People & Lead Overview ────────────────────────────────────────────────────
function PeopleLeadOverview({ totalManagers, totalTelecallers, totalLeadsAll, assignedLeads, unassignedLeads, contactedLeads, convertedLeads }) {
    const tiles = [
        { label: 'Total Managers',    value: totalManagers,    icon: 'manage_accounts', color: '#6366F1', bg: 'rgba(99,102,241,0.12)',  border: 'rgba(99,102,241,0.25)' },
        { label: 'Total Telecallers', value: totalTelecallers, icon: 'support_agent',   color: '#8B5CF6', bg: 'rgba(139,92,246,0.12)', border: 'rgba(139,92,246,0.25)' },
        { label: 'Total Leads',       value: totalLeadsAll,    icon: 'groups',          color: '#06B6D4', bg: 'rgba(6,182,212,0.12)',   border: 'rgba(6,182,212,0.25)'  },
        { label: 'Assigned',          value: assignedLeads,    icon: 'assignment_ind',  color: '#10B981', bg: 'rgba(16,185,129,0.12)', border: 'rgba(16,185,129,0.25)' },
        { label: 'Unassigned',        value: unassignedLeads,  icon: 'assignment_late', color: '#F59E0B', bg: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.25)' },
        { label: 'Contacted',         value: contactedLeads,   icon: 'call_made',       color: '#EC4899', bg: 'rgba(236,72,153,0.12)', border: 'rgba(236,72,153,0.25)' },
        { label: 'Converted',         value: convertedLeads,   icon: 'verified',        color: '#10B981', bg: 'rgba(16,185,129,0.12)', border: 'rgba(16,185,129,0.25)' },
    ];
    const total        = totalLeadsAll || 1;
    const assignedPct  = Math.round((assignedLeads  / total) * 100);
    const convertedPct = Math.round((convertedLeads / total) * 100);
    const contactedPct = Math.round((contactedLeads / total) * 100);
    const unassignedPct= Math.round((unassignedLeads/ total) * 100);
    return (
        <div className="dark-card mgr-section">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 18 }}>
                <div>
                    <p className="dark-card-title" style={{ marginBottom: 2 }}>People & Lead Overview</p>
                    <p style={{ fontSize: 12, color: '#475569', margin: 0 }}>All-time org-wide snapshot</p>
                </div>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(140px, 1fr))', gap: 12, marginBottom: 24 }}>
                {tiles.map(t => <Tile key={t.label} {...t} />)}
            </div>
            <p style={{ fontSize: 13, fontWeight: 700, color: '#CBD5E1', marginBottom: 12 }}>Lead Pipeline</p>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                <FunnelBar label="Total Leads" value={totalLeadsAll}  pct={100}           color="#06B6D4" />
                <FunnelBar label="Assigned"    value={assignedLeads}  pct={assignedPct}   color="#10B981" />
                <FunnelBar label="Contacted"   value={contactedLeads} pct={contactedPct}  color="#EC4899" />
                <FunnelBar label="Converted"   value={convertedLeads} pct={convertedPct}  color="#6366F1" />
                <FunnelBar label="Unassigned"  value={unassignedLeads}pct={unassignedPct} color="#F59E0B" />
            </div>
        </div>
    );
}
function Tile({ label, value, icon, color, bg, border }) {
    const count = useCountUp(value);
    return (
        <div style={{ background: bg, border: `1px solid ${border}`, borderRadius: 12, padding: '14px 14px 12px', display: 'flex', flexDirection: 'column', gap: 8 }}>
            <div style={{ width: 32, height: 32, borderRadius: 8, background: `${color}22`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <span className="material-icons" style={{ fontSize: 17, color }}>{icon}</span>
            </div>
            <div style={{ fontSize: 22, fontWeight: 800, color: '#F1F5F9', lineHeight: 1 }}>{count.toLocaleString()}</div>
            <div style={{ fontSize: 11, fontWeight: 600, color: '#94A3B8' }}>{label}</div>
        </div>
    );
}
function FunnelBar({ label, value, pct, color }) {
    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 5 }}>
                <span style={{ fontSize: 12, color: '#CBD5E1', fontWeight: 600 }}>{label}</span>
                <span style={{ fontSize: 12, color, fontWeight: 700 }}>{value.toLocaleString()} ({pct}%)</span>
            </div>
            <div style={{ height: 8, background: '#1E293B', borderRadius: 4 }}>
                <div style={{ height: 8, width: `${pct}%`, background: `linear-gradient(90deg, ${color}, ${color}99)`, borderRadius: 4, transition: 'width 1.2s ease' }} />
            </div>
        </div>
    );
}

// ── Lead Source Donut ─────────────────────────────────────────────────────────
const SOURCE_COLORS = [
    '#6366F1','#10B981','#F59E0B','#F43F5E','#8B5CF6',
    '#06B6D4','#EC4899','#14B8A6','#F97316','#3B82F6',
    '#A855F7','#EF4444','#22D3EE','#84CC16','#FBBF24',
];

function LeadSourceChart({ leadSource }) {
    const total  = leadSource.reduce((a, r) => a + Number(r.total), 0);
    const colors = SOURCE_COLORS.slice(0, leadSource.length);

    const ref = useApexChart(() => {
        if (!total) return null;
        return {
            chart: {
                type: 'donut', height: 240,
                background: 'transparent',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
            },
            series: leadSource.map(r => Number(r.total)),
            labels: leadSource.map(r => r.source || 'Unknown'),
            colors,
            theme: { mode: 'dark' },
            plotOptions: { pie: { donut: { size: '68%', labels: {
                show: true,
                total: {
                    show: true, label: 'Total',
                    color: '#94A3B8', fontSize: '13px', fontWeight: 600,
                    formatter: () => String(total),
                },
                value: { color: '#F8FAFC', fontSize: '26px', fontWeight: 800, offsetY: 4 },
                name:  { color: '#94A3B8', fontSize: '12px', offsetY: -4 },
            } } } },
            stroke: { width: 2, colors: ['#0F172A'] },
            legend: { show: false },
            dataLabels: { enabled: false },
            tooltip: {
                theme: 'dark',
                y: { formatter: v => `${v} leads (${total ? Math.round(v / total * 100) : 0}%)` },
            },
        };
    }, [JSON.stringify(leadSource)]);

    return (
        <div className="dark-card" style={{ marginBottom: 0, height: '100%' }}>
            <p className="dark-card-title">Lead Source Overview</p>
            <p className="dark-card-sub">{total} total leads by source</p>

            {total > 0 ? (
                <>
                    {/* Donut chart — legend disabled */}
                    <div ref={ref} />

                    {/* Custom legend — 2-column grid, full names, no truncation */}
                    <div style={{
                        borderTop: '1px solid #1E293B',
                        paddingTop: 12, marginTop: 4,
                        display: 'grid',
                        gridTemplateColumns: '1fr 1fr',
                        gap: '8px 12px',
                    }}>
                        {leadSource.map((r, i) => {
                            const color = colors[i] ?? '#6366F1';
                            const count = Number(r.total);
                            const pct   = total > 0 ? Math.round((count / total) * 100) : 0;
                            return (
                                <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 7, minWidth: 0 }}>
                                    {/* Color dot */}
                                    <div style={{
                                        width: 9, height: 9, borderRadius: '50%',
                                        background: color, flexShrink: 0,
                                    }} />
                                    {/* Source name */}
                                    <span
                                        title={r.source || 'Unknown'}
                                        style={{
                                            flex: 1, fontSize: 11, fontWeight: 600,
                                            color: '#CBD5E1',
                                            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                                        }}
                                    >
                                        {r.source || 'Unknown'}
                                    </span>
                                    {/* Count + pct */}
                                    <span style={{ fontSize: 10, fontWeight: 700, color, flexShrink: 0 }}>
                                        {count} <span style={{ color: '#475569', fontWeight: 500 }}>({pct}%)</span>
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </>
            ) : (
                <div className="dark-empty">
                    <span className="material-icons">pie_chart</span>
                    <p>No source data yet</p>
                </div>
            )}
        </div>
    );
}

// ── Status Breakdown ──────────────────────────────────────────────────────────
function StatusBreakdown({ statusBreakdown }) {
    const total = statusBreakdown.reduce((a, r) => a + r.total, 0);
    const COLOR_MAP = {
        converted: '#10B981', new: '#6366F1', interested: '#8B5CF6',
        'not-interested': '#F43F5E', 'follow_up': '#F59E0B', 'follow-up': '#F59E0B',
        lost: '#94A3B8', contacted: '#06B6D4', assigned: '#EC4899',
    };
    return (
        <div className="dark-card" style={{ marginBottom: 0, height: '100%' }}>
            <p className="dark-card-title">Lead Status Breakdown</p>
            <p className="dark-card-sub">Org-wide pipeline snapshot</p>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: 14 }}>
                {statusBreakdown.map(r => {
                    const color = COLOR_MAP[r.status] ?? '#6366F1';
                    const pct   = total > 0 ? Math.round((r.total / total) * 100) : 0;
                    return (
                        <div key={r.status}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#CBD5E1', textTransform: 'capitalize' }}>{r.status.replace(/_/g,' ')}</span>
                                <span style={{ fontSize: 12, color, fontWeight: 700 }}>{r.total} ({pct}%)</span>
                            </div>
                            <div style={{ height: 6, background: '#1E293B', borderRadius: 3 }}>
                                <div style={{ height: 6, width: `${pct}%`, background: color, borderRadius: 3, transition: 'width 0.8s ease' }} />
                            </div>
                        </div>
                    );
                })}
                {!statusBreakdown.length && <div className="dark-empty"><span className="material-icons">donut_large</span><p>No data yet</p></div>}
            </div>
        </div>
    );
}

// ── Answer Rate Radial Gauge ──────────────────────────────────────────────────
function AnswerRateGauge({ answerRate, totalCallsMade, answeredCalls }) {
    const color = answerRate >= 70 ? '#10B981' : answerRate >= 40 ? '#F59E0B' : '#F43F5E';
    const ref   = useApexChart(() => ({
        chart: { type: 'radialBar', height: 200, background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif' },
        series: [answerRate],
        colors: [color],
        plotOptions: { radialBar: {
            hollow: { size: '58%' },
            track: { background: '#1E293B', strokeWidth: '100%' },
            dataLabels: {
                name:  { fontSize: '11px', color: '#64748b', offsetY: 20 },
                value: { fontSize: '24px', fontWeight: 800, color: '#F1F5F9', offsetY: -8, formatter: v => `${Math.round(v)}%` },
            },
        } },
        labels: ['Answer Rate'],
        theme: { mode: 'dark' },
    }), [answerRate]);
    return (
        <div style={{ textAlign: 'center' }}>
            <div ref={ref} />
            <div style={{ display: 'flex', justifyContent: 'center', gap: 24, marginTop: -8 }}>
                <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: 18, fontWeight: 800, color: '#10B981' }}>{answeredCalls.toLocaleString()}</div>
                    <div style={{ fontSize: 10, color: '#64748b', fontWeight: 600 }}>ANSWERED</div>
                </div>
                <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: 18, fontWeight: 800, color: '#F43F5E' }}>{(totalCallsMade - answeredCalls).toLocaleString()}</div>
                    <div style={{ fontSize: 10, color: '#64748b', fontWeight: 600 }}>MISSED</div>
                </div>
            </div>
        </div>
    );
}

// ── Call Outcome Donut ────────────────────────────────────────────────────────
function OutcomeDonut({ callOutcomes }) {
    const total = callOutcomes.reduce((a, r) => a + r.total, 0);
    const ref   = useApexChart(() => {
        if (!total) return null;
        return {
            chart: { type: 'donut', height: 200, background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif' },
            series: callOutcomes.map(r => r.total),
            labels: callOutcomes.map(r => r.outcome.replace(/-/g,' ').replace(/\b\w/g, c=>c.toUpperCase())),
            colors: ['#10B981','#6366F1','#F59E0B','#F43F5E','#94A3B8','#F97316','#EC4899'],
            theme: { mode: 'dark' },
            plotOptions: { pie: { donut: { size: '60%', labels: {
                show: true,
                total: { show: true, label: 'Total', color: '#94A3B8', fontSize: '11px', fontWeight: 600, formatter: () => String(total) },
                value: { color: '#F8FAFC', fontSize: '20px', fontWeight: 800 },
            } } } },
            stroke: { width: 2, colors: ['#111827'] },
            legend: { position: 'bottom', labels: { colors: '#94A3B8' }, markers: { radius: 3, width: 8, height: 8 }, fontSize: '11px', itemMargin: { horizontal: 6, vertical: 3 } },
            dataLabels: { enabled: false },
            tooltip: { theme: 'dark', y: { formatter: v => `${v} calls` } },
        };
    }, [JSON.stringify(callOutcomes)]);
    return total > 0
        ? <div ref={ref} />
        : <div className="dark-empty"><span className="material-icons">summarize</span><p>No outcome data</p></div>;
}

// ── Call Summary Tile ─────────────────────────────────────────────────────────
function CallTile({ icon, label, value, display, color }) {
    const count = useCountUp(value ?? 0);
    const shown = value != null ? count.toLocaleString() : display;
    return (
        <div style={{ background: `${color}0f`, border: `1px solid ${color}30`, borderRadius: 10, padding: '12px 14px', display: 'flex', flexDirection: 'column', gap: 6 }}>
            <span className="material-icons" style={{ fontSize: 18, color }}>{icon}</span>
            <div style={{ fontSize: 20, fontWeight: 800, color: '#F1F5F9' }}>{shown}</div>
            <div style={{ fontSize: 11, color: '#64748b', fontWeight: 600 }}>{label}</div>
        </div>
    );
}

// ── Call Performance ──────────────────────────────────────────────────────────
function CallPerformance({ totalCallsMade, answeredCalls, avgCallDurationSec, callStatusBreakdown, callOutcomes, reportsUrl }) {
    const missedCalls = totalCallsMade - answeredCalls;
    const answerRate  = totalCallsMade > 0 ? Math.round((answeredCalls / totalCallsMade) * 100) : 0;
    const STATUS_COLORS = {
        answered: '#10B981', 'no-answer': '#F43F5E', busy: '#F59E0B',
        failed: '#EF4444', completed: '#6366F1', initiated: '#06B6D4',
        cancelled: '#94A3B8', rejected: '#F97316',
    };
    const statusTotal = callStatusBreakdown.reduce((a, r) => a + r.total, 0);
    return (
        <div className="dark-card mgr-section">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
                <div>
                    <p className="dark-card-title" style={{ marginBottom: 2 }}>Call Performance</p>
                    <p style={{ fontSize: 12, color: '#475569', margin: 0 }}>Selected period call analytics</p>
                </div>
                <a href={reportsUrl.callEfficiency} style={{ fontSize: 12, color: '#6366F1', fontWeight: 600, textDecoration: 'none' }}>Full Report →</a>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(130px, 1fr))', gap: 12, marginBottom: 24 }}>
                <CallTile icon="call"          label="Total Calls"  value={totalCallsMade} color="#6366F1" />
                <CallTile icon="call_received" label="Answered"     value={answeredCalls}  color="#10B981" />
                <CallTile icon="call_missed"   label="Missed"       value={missedCalls}    color="#F43F5E" />
                <CallTile icon="percent"       label="Answer Rate"  value={null} display={`${answerRate}%`} color="#06B6D4" />
                <CallTile icon="timer"         label="Avg Duration" value={null} display={toTimeLabel(avgCallDurationSec)} color="#F59E0B" />
            </div>
            <div className="row g-3">
                <div className="col-md-4">
                    <p style={{ fontSize: 13, fontWeight: 700, color: '#CBD5E1', marginBottom: 4 }}>Answer Rate</p>
                    <AnswerRateGauge answerRate={answerRate} totalCallsMade={totalCallsMade} answeredCalls={answeredCalls} />
                </div>
                <div className="col-md-4">
                    <p style={{ fontSize: 13, fontWeight: 700, color: '#CBD5E1', marginBottom: 4 }}>Call Outcomes</p>
                    <OutcomeDonut callOutcomes={callOutcomes} />
                </div>
                <div className="col-md-4">
                    <p style={{ fontSize: 13, fontWeight: 700, color: '#CBD5E1', marginBottom: 12 }}>By Call Status</p>
                    {callStatusBreakdown.length > 0 ? (
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                            {callStatusBreakdown.map(r => {
                                const color = STATUS_COLORS[r.status] ?? '#6366F1';
                                const pct   = statusTotal > 0 ? Math.round((r.total / statusTotal) * 100) : 0;
                                return (
                                    <div key={r.status}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 3 }}>
                                            <span style={{ fontSize: 12, color: '#94A3B8', textTransform: 'capitalize' }}>{r.status}</span>
                                            <span style={{ fontSize: 12, color, fontWeight: 700 }}>{r.total} ({pct}%)</span>
                                        </div>
                                        <div style={{ height: 6, background: '#1E293B', borderRadius: 3 }}>
                                            <div style={{ height: 6, width: `${pct}%`, background: color, borderRadius: 3, transition: 'width 0.8s ease' }} />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="dark-empty"><span className="material-icons">call</span><p>No call data yet</p></div>
                    )}
                </div>
            </div>
        </div>
    );
}

// ── Manager Table ─────────────────────────────────────────────────────────────
function ManagerTable({ managerStats, reportsUrl }) {
    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 }}>
                <div>
                    <p className="dark-card-title" style={{ marginBottom: 2 }}>Manager Performance</p>
                    <p style={{ fontSize: 12, color: '#475569', margin: 0 }}>Lead assignment & conversion by manager</p>
                </div>
                <a href={reportsUrl.managerPerformance} style={{ fontSize: 12, color: '#6366F1', fontWeight: 600, textDecoration: 'none' }}>Full Report →</a>
            </div>
            <div className="mgr-table-wrap">
                <table className="mgr-table">
                    <thead>
                        <tr><th>Manager</th><th>Total Leads</th><th>Assigned</th><th>Unassigned</th><th>Converted</th><th>Conv. Rate</th><th style={{ minWidth: 110 }}>Progress</th></tr>
                    </thead>
                    <tbody>
                        {managerStats.length > 0 ? managerStats.map(m => {
                            const rate = parseFloat(m.conversion_rate);
                            const rateClass = rate >= 30 ? 'high' : rate >= 10 ? 'medium' : 'low';
                            const color = avatarColor(m.name);
                            return (
                                <tr key={m.id}>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                            <div className="tc-avatar" style={{ background: color }}>{initials(m.name).toUpperCase()}</div>
                                            <span style={{ fontWeight: 600, color: '#E2E8F0' }}>{m.name}</span>
                                        </div>
                                    </td>
                                    <td style={{ color: '#94A3B8' }}>{m.total_leads}</td>
                                    <td style={{ color: '#10B981', fontWeight: 600 }}>{m.assigned_leads}</td>
                                    <td style={{ color: '#F59E0B', fontWeight: 600 }}>{m.unassigned_leads}</td>
                                    <td style={{ color: '#6366F1', fontWeight: 600 }}>{m.converted_leads}</td>
                                    <td><span className={`rate-badge ${rateClass}`}>{rate.toFixed(1)}%</span></td>
                                    <td><div className="conv-bar-track"><div className="conv-bar-fill" style={{ width: `${Math.min(rate, 100)}%` }} /></div></td>
                                </tr>
                            );
                        }) : (
                            <tr><td colSpan={7}><div className="dark-empty"><span className="material-icons">manage_accounts</span><p>No managers found.</p></div></td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// ── Telecaller Leaderboard + Bar Chart ────────────────────────────────────────
function TelecallerLeaderboard({ telecallerStats, reportsUrl }) {
    const medals = ['🥇','🥈','🥉'];
    const top8   = telecallerStats.slice(0, 8);
    const barRef = useApexChart(() => {
        if (!top8.length) return null;
        return {
            chart: { type: 'bar', height: Math.max(160, top8.length * 38), background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif', toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%' } },
            series: [
                { name: 'Total Calls', data: top8.map(t => t.total_calls) },
                { name: 'Converted',   data: top8.map(t => t.converted_count) },
            ],
            xaxis: { categories: top8.map(t => t.name), labels: { style: { colors: '#475569', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#94A3B8', fontSize: '11px' } } },
            colors: ['#6366F1','#10B981'],
            grid: { borderColor: '#1E293B', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            theme: { mode: 'dark' },
            legend: { labels: { colors: '#94A3B8' }, markers: { radius: 3, width: 10, height: 10 } },
            dataLabels: { enabled: false },
            tooltip: { theme: 'dark' },
        };
    }, [JSON.stringify(telecallerStats)]);
    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 }}>
                <div>
                    <p className="dark-card-title" style={{ marginBottom: 2 }}>Telecaller Performance</p>
                    <p style={{ fontSize: 12, color: '#475569', margin: 0 }}>Ranked by total calls made</p>
                </div>
                <a href={reportsUrl.telecallerPerformance} style={{ fontSize: 12, color: '#6366F1', fontWeight: 600, textDecoration: 'none' }}>Full Report →</a>
            </div>
            {top8.length > 0 && <div style={{ marginBottom: 20 }}><div ref={barRef} /></div>}
            <div className="mgr-table-wrap">
                <table className="mgr-table">
                    <thead>
                        <tr><th style={{ width: 40 }}>#</th><th>Telecaller</th><th>Assigned</th><th>Calls</th><th>Talk Time</th><th>Converted</th><th>Conv. Rate</th></tr>
                    </thead>
                    <tbody>
                        {telecallerStats.length > 0 ? telecallerStats.map((t, idx) => {
                            const rate = parseFloat(t.conversion_rate);
                            const rateClass = rate >= 30 ? 'high' : rate >= 10 ? 'medium' : 'low';
                            const color = avatarColor(t.name);
                            return (
                                <tr key={t.id}>
                                    <td>
                                        {idx < 3
                                            ? <span style={{ fontSize: 18 }}>{medals[idx]}</span>
                                            : <span style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 24, height: 24, borderRadius: '50%', background: '#1E293B', color: '#94A3B8', fontSize: 11, fontWeight: 700 }}>{idx + 1}</span>
                                        }
                                    </td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                            <div className="tc-avatar" style={{ background: color }}>{initials(t.name).toUpperCase()}</div>
                                            <span style={{ fontWeight: 600, color: '#E2E8F0' }}>{t.name}</span>
                                        </div>
                                    </td>
                                    <td style={{ color: '#94A3B8' }}>{t.assigned_count}</td>
                                    <td style={{ color: '#94A3B8' }}>{t.total_calls}</td>
                                    <td style={{ color: '#06B6D4', fontSize: 12, fontWeight: 600, fontVariantNumeric: 'tabular-nums' }}>{toTimeLabel(t.talk_time_sec)}</td>
                                    <td style={{ color: '#10B981', fontWeight: 600 }}>{t.converted_count}</td>
                                    <td><span className={`rate-badge ${rateClass}`}>{rate.toFixed(1)}%</span></td>
                                </tr>
                            );
                        }) : (
                            <tr><td colSpan={7}><div className="dark-empty"><span className="material-icons">leaderboard</span><p>No data yet.</p></div></td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// ── Course Performance ────────────────────────────────────────────────────────
function CoursePerformance({ courseStats, reportsUrl }) {
    if (!courseStats.length) return null;
    const maxTotal = Math.max(...courseStats.map(r => r.total), 1);
    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                <div>
                    <p className="dark-card-title" style={{ marginBottom: 2 }}>Course Performance</p>
                    <p style={{ fontSize: 12, color: '#475569', margin: 0 }}>Lead volume & conversion by course</p>
                </div>
                <a href={reportsUrl.conversion} style={{ fontSize: 12, color: '#6366F1', fontWeight: 600, textDecoration: 'none' }}>Conversion Report →</a>
            </div>

            {/* CSS bar chart — course name on left, count inside bar, rate on right */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 24 }}>
                {courseStats.map((r, i) => {
                    const pct     = Math.round((r.total / maxTotal) * 100);
                    const rate    = parseFloat(r.rate);
                    const rateClr = rate >= 30 ? '#10B981' : rate >= 10 ? '#F59E0B' : '#F43F5E';
                    return (
                        <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            {/* Course label — fixed 170 px, right-aligned, ellipsis for long names */}
                            <div
                                title={r.course || 'Unknown'}
                                style={{
                                    width: 170, flexShrink: 0, textAlign: 'right',
                                    fontSize: 11, fontWeight: 600, color: '#CBD5E1',
                                    overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                                }}
                            >
                                {r.course || 'Unknown'}
                            </div>

                            {/* Bar track */}
                            <div style={{ flex: 1, height: 34, background: '#1E293B', borderRadius: 7, overflow: 'hidden' }}>
                                <div style={{
                                    width: `${Math.max(pct, 5)}%`,
                                    height: '100%',
                                    background: 'linear-gradient(90deg, #7C3AED, #8B5CF6)',
                                    borderRadius: 7,
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'flex-end',
                                    paddingRight: 10,
                                    transition: 'width 0.9s cubic-bezier(0.4,0,0.2,1)',
                                }}>
                                    <span style={{ fontSize: 12, fontWeight: 800, color: '#fff' }}>{r.total}</span>
                                </div>
                            </div>

                            {/* Conversion rate — fixed 50 px */}
                            <div style={{ width: 50, flexShrink: 0, textAlign: 'right', fontSize: 11, fontWeight: 700, color: rateClr }}>
                                {rate.toFixed(1)}%
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Detail table */}
            <div className="mgr-table-wrap">
                <table className="mgr-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Leads</th>
                            <th>Converted</th>
                            <th>Rate</th>
                            <th style={{ minWidth: 160 }}>Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        {courseStats.map((row, i) => {
                            const rate      = parseFloat(row.rate);
                            const rateClass = rate >= 30 ? 'high' : rate >= 10 ? 'medium' : 'low';
                            const barPct    = Math.round((row.total / maxTotal) * 100);
                            return (
                                <tr key={i}>
                                    <td style={{ fontWeight: 600, color: '#E2E8F0' }}>{row.course || '—'}</td>
                                    <td style={{ color: '#94A3B8', fontSize: 12 }}>{row.total}</td>
                                    <td style={{ color: '#94A3B8', fontSize: 12 }}>{row.conversions}</td>
                                    <td><span className={`rate-badge ${rateClass}`}>{rate.toFixed(1)}%</span></td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <div className="course-bar-track"><div className="course-bar-fill" style={{ width: `${barPct}%` }} /></div>
                                            <span style={{ fontSize: 11, color: '#475569' }}>{row.total}</span>
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

// ── Main Page ─────────────────────────────────────────────────────────────────
export default function Dashboard({
    period, dailyTrend = [],
    totalManagers, totalTelecallers,
    leadsToday, leadsWeek, leadsMonth,
    totalLeadsAll, assignedLeads, unassignedLeads, contactedLeads, convertedLeads,
    totalCallsMade, totalCallDurationSec, answeredCalls, avgCallDurationSec,
    callStatusBreakdown, callOutcomes,
    conversionRate, missedFollowups,
    leadSource, managerStats, telecallerStats,
    courseStats, statusBreakdown, reportsUrl,
}) {
    const periodLabels = { today: 'Today', week: 'This Week', month: 'This Month' };
    const periodLeads  = { today: leadsToday, week: leadsWeek, month: leadsMonth }[period] ?? leadsToday;

    function changePeriod(e) {
        router.get(window.location.pathname, { period: e.target.value }, { preserveScroll: false });
    }

    const kpiCards = [
        { icon: 'groups',          label: `New Leads (${periodLabels[period]})`,  rawValue: periodLeads,    sub: `T: ${leadsToday} · W: ${leadsWeek} · M: ${leadsMonth}`, iconBg: 'linear-gradient(135deg,#6366F1,#4f46e5)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(99,102,241,0.4)',  accentColor: '#6366F1' },
        { icon: 'call',            label: `Calls Made (${periodLabels[period]})`, rawValue: totalCallsMade,  iconBg: 'linear-gradient(135deg,#8B5CF6,#7C3AED)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(139,92,246,0.4)', accentColor: '#8B5CF6' },
        { icon: 'timer',           label: 'Total Call Duration', rawValue: null, displayValue: toTimeLabel(totalCallDurationSec), iconBg: 'linear-gradient(135deg,#F59E0B,#D97706)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(245,158,11,0.35)',  accentColor: '#F59E0B' },
        { icon: 'insights',        label: 'Conversion Rate',     rawValue: null, displayValue: `${parseFloat(conversionRate).toFixed(1)}%`, sub: 'Org-wide for selected period', iconBg: 'linear-gradient(135deg,#06B6D4,#0891B2)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(6,182,212,0.35)', accentColor: '#06B6D4' },
        { icon: 'manage_accounts', label: 'Active Managers',     rawValue: totalManagers,    iconBg: 'linear-gradient(135deg,#10B981,#059669)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(16,185,129,0.35)', accentColor: '#10B981' },
        { icon: 'support_agent',   label: 'Active Telecallers',  rawValue: totalTelecallers, iconBg: 'linear-gradient(135deg,#8B5CF6,#6D28D9)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(139,92,246,0.35)', accentColor: '#8B5CF6' },
        { icon: 'event_busy',      label: 'Missed Follow-Ups',   rawValue: missedFollowups,  iconBg: 'linear-gradient(135deg,#F43F5E,#E11D48)', iconColor: '#fff', iconShadow: '0 4px 14px rgba(244,63,94,0.35)',  accentColor: '#F43F5E' },
    ];

    return (
        <>
            <Head title="Report Viewer Dashboard" />
            <div className="mgr-dash">

                {/* TopBar */}
                <div className="mgr-topbar">
                    <div className="mgr-title">
                        <h2>Analytics Overview</h2>
                        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: 'rgba(99,102,241,0.12)', border: '1px solid rgba(99,102,241,0.3)', borderRadius: 20, padding: '4px 12px', marginLeft: 10 }}>
                            <span className="material-icons" style={{ fontSize: 13, color: '#6366F1' }}>lock</span>
                            <span style={{ fontSize: 11, fontWeight: 700, color: '#6366F1' }}>READ ONLY</span>
                        </div>
                    </div>
                    <div className="mgr-period-select">
                        <span className="material-icons" style={{ fontSize: 16 }}>calendar_today</span>
                        <select value={period} onChange={changePeriod}>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                        <span className="material-icons" style={{ fontSize: 16 }}>expand_more</span>
                    </div>
                </div>

                {/* Quick Links — moved to top */}
                <QuickReportsBar reportsUrl={reportsUrl} />

                {/* KPI Cards */}
                <div className="kpi-grid mgr-section">
                    {kpiCards.map((card, i) => <KpiCard key={card.label} {...card} delay={i * 70} />)}
                </div>

                {/* Insight Strip */}
                <InsightStrip telecallerStats={telecallerStats} managerStats={managerStats} courseStats={courseStats} />

                {/* Activity Trend Chart */}
                {dailyTrend.length > 0 && (
                    <div className="mgr-section">
                        <TrendChart dailyTrend={dailyTrend} />
                    </div>
                )}

                {/* People & Lead Overview */}
                <PeopleLeadOverview
                    totalManagers={totalManagers} totalTelecallers={totalTelecallers}
                    totalLeadsAll={totalLeadsAll} assignedLeads={assignedLeads}
                    unassignedLeads={unassignedLeads} contactedLeads={contactedLeads} convertedLeads={convertedLeads}
                />

                {/* Lead Source + Status Breakdown */}
                <div className="row g-3 mgr-section">
                    <div className="col-lg-5"><LeadSourceChart leadSource={leadSource} /></div>
                    <div className="col-lg-7"><StatusBreakdown statusBreakdown={statusBreakdown} /></div>
                </div>

                {/* Call Performance */}
                <CallPerformance
                    totalCallsMade={totalCallsMade} answeredCalls={answeredCalls}
                    avgCallDurationSec={avgCallDurationSec}
                    callStatusBreakdown={callStatusBreakdown} callOutcomes={callOutcomes}
                    reportsUrl={reportsUrl}
                />

                {/* Manager Performance */}
                <div className="dark-card mgr-section">
                    <ManagerTable managerStats={managerStats} reportsUrl={reportsUrl} />
                </div>

                {/* Telecaller Performance */}
                <div className="dark-card mgr-section">
                    <TelecallerLeaderboard telecallerStats={telecallerStats} reportsUrl={reportsUrl} />
                </div>

                {/* Course Performance */}
                {courseStats.length > 0 && (
                    <div className="dark-card mgr-section">
                        <CoursePerformance courseStats={courseStats} reportsUrl={reportsUrl} />
                    </div>
                )}

            </div>
        </>
    );
}
