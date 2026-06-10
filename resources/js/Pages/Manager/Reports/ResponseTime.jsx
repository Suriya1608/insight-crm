import { Head } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import ReportFilters from './_Filters';
import { ReportNavBar } from './Home';
import {
    LuChevronLeft, LuChevronRight, LuTimer, LuFileSpreadsheet,
    LuFileText, LuUsers, LuClock, LuHeadphones, LuMinus,
} from 'react-icons/lu';

const PAGE_SIZE = 20;

function fmtMinutes(min) {
    if (min === null || min === undefined) return null;
    const m = parseInt(min);
    if (m < 60)  return `${m}m`;
    const h = Math.floor(m / 60), rem = m % 60;
    return rem > 0 ? `${h}h ${rem}m` : `${h}h`;
}

function fmtDate(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    if (isNaN(d)) return dt;
    return d.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
}

function ResponseBadge({ minutes }) {
    if (minutes === null || minutes === undefined) {
        return (
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '3px 10px', borderRadius: 20, background: '#f1f5f9', color: '#94a3b8', fontSize: 11, fontWeight: 600 }}>
                <LuMinus style={{ fontSize: 12, width: 12, height: 12 }} />
                No response
            </span>
        );
    }
    const m = parseInt(minutes);
    const label = fmtMinutes(m);
    const isGood = m <= 30, isMed = m <= 120;
    const [bg, color, Icon] = isGood
        ? ['#dcfce7', '#16a34a', LuTimer]
        : isMed
        ? ['#fef9c3', '#b45309', LuClock]
        : ['#fee2e2', '#dc2626', LuTimer];
    return (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '4px 10px', borderRadius: 20, background: bg, color, fontSize: 12, fontWeight: 700 }}>
            <Icon style={{ fontSize: 13, width: 13, height: 13 }} />
            {label}
        </span>
    );
}

function KpiCard({ icon: Icon, label, value, sub, gradient, iconBg }) {
    return (
        <div style={{ background: '#fff', borderRadius: 16, padding: '20px 22px', boxShadow: '0 2px 12px rgba(15,23,42,0.07)', border: '1px solid #e2e8f0', display: 'flex', alignItems: 'flex-start', gap: 16, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 3, background: gradient, borderRadius: '16px 16px 0 0' }} />
            <div style={{ width: 48, height: 48, borderRadius: 13, background: gradient, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: `0 4px 12px ${iconBg}40` }}>
                <Icon style={{ color: '#fff', fontSize: 22, width: 22, height: 22 }} />
            </div>
            <div style={{ minWidth: 0 }}>
                <div style={{ fontSize: 26, fontWeight: 800, color: '#1D1D1D', lineHeight: 1.1, letterSpacing: '-0.5px' }}>{value}</div>
                <div style={{ fontSize: 12, fontWeight: 600, color: '#64748b', marginTop: 4 }}>{label}</div>
                {sub && <div style={{ fontSize: 11, color: '#94a3b8', marginTop: 2 }}>{sub}</div>}
            </div>
        </div>
    );
}

function Pagination({ page, totalPages, onChange }) {
    if (totalPages <= 1) return null;
    const pages = [];
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || Math.abs(i - page) <= 2) pages.push(i);
        else if (pages[pages.length - 1] !== '...') pages.push('...');
    }
    const navBtn = (content, target, disabled) => (
        <button key={String(target) + String(content)} disabled={disabled || target === page}
            onClick={() => !disabled && onChange(target)}
            style={{
                minWidth: 34, height: 34, borderRadius: 8, border: '1.5px solid',
                borderColor: target === page ? 'transparent' : '#e2e8f0',
                background: target === page ? '#FF5C00' : '#fff',
                color: target === page ? '#fff' : disabled ? '#cbd5e1' : '#475569',
                fontWeight: target === page ? 700 : 500, fontSize: 13,
                cursor: disabled ? 'default' : 'pointer',
                display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: target === page ? '0 2px 8px rgba(255,92,0,0.2)' : 'none',
            }}>{content}</button>
    );
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 4, flexWrap: 'wrap' }}>
            {navBtn(<LuChevronLeft style={{ fontSize: 16, width: 16, height: 16 }} />, page - 1, page === 1)}
            {pages.map((p, i) => p === '...'
                ? <span key={`e${i}`} style={{ padding: '0 4px', color: '#94a3b8' }}>…</span>
                : navBtn(p, p, false)
            )}
            {navBtn(<LuChevronRight style={{ fontSize: 16, width: 16, height: 16 }} />, page + 1, page === totalPages)}
        </div>
    );
}

export default function ResponseTime({ filters, filterOptions, rows: rawRows, avgResponse }) {
    const rows = rawRows ?? [];
    const [page, setPage] = useState(1);

    const exportUrl = (fmt) => {
        const p = new URLSearchParams({
            date_range: filters?.date_range ?? '30',
            source:     filters?.source     ?? 'all',
            telecaller: filters?.telecaller ?? 'all',
        });
        return `/manager/reports/export/response-time/${fmt}?${p}`;
    };

    const withResponse  = rows.filter(r => r.response_minutes !== null && r.response_minutes !== undefined);
    const fast          = withResponse.filter(r => parseInt(r.response_minutes) <= 30);
    const fastRate      = withResponse.length > 0 ? Math.round((fast.length / withResponse.length) * 100) : 0;

    const totalPages = Math.max(1, Math.ceil(rows.length / PAGE_SIZE));
    const safePage   = Math.min(page, totalPages);
    const paged      = rows.slice((safePage - 1) * PAGE_SIZE, safePage * PAGE_SIZE);

    const handlePage = (p) => { setPage(p); window.scrollTo({ top: 0, behavior: 'smooth' }); };

    const kpis = [
        {
            icon: LuTimer, label: 'Avg Response Time',
            value: fmtMinutes(avgResponse) ?? '—',
            sub: 'Across all leads with response',
            gradient: 'linear-gradient(135deg,#f59e0b,#d97706)', iconBg: '#f59e0b',
        },
        {
            icon: LuUsers, label: 'Leads Analysed',
            value: rows.length,
            sub: 'Total leads in selected period',
            gradient: 'linear-gradient(135deg,#1D1D1D,#2d2d2d)', iconBg: '#1D1D1D',
        },
        {
            icon: LuTimer, label: 'With Response',
            value: withResponse.length,
            sub: `${rows.length > 0 ? Math.round((withResponse.length / rows.length) * 100) : 0}% of analysed leads`,
            gradient: 'linear-gradient(135deg,#10b981,#059669)', iconBg: '#10b981',
        },
        {
            icon: LuTimer, label: 'Fast Response (≤30m)',
            value: fast.length,
            sub: `${fastRate}% of responded leads`,
            gradient: 'linear-gradient(135deg,#FF5C00,#e04e00)', iconBg: '#FF5C00',
        },
    ];

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.rpt-resp,.rpt-resp div,.rpt-resp span:not([class*="material"]),.rpt-resp p,.rpt-resp h1,.rpt-resp h2,.rpt-resp h3,.rpt-resp h4,.rpt-resp h5,.rpt-resp h6,.rpt-resp button,.rpt-resp input,.rpt-resp select,.rpt-resp a,.rpt-resp th,.rpt-resp td,.rpt-resp label,.rpt-resp small{font-family:'Poppins',sans-serif!important;box-sizing:border-box;}`}</style>
            <Head title="Response Time Report" />
            <div className="rpt-resp">
            <ReportNavBar active="/manager/reports/response-time" />
            <ReportFilters filters={filters} filterOptions={filterOptions} url="/manager/reports/response-time" exportSlug="response-time" />

            {/* KPI Cards */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(200px,1fr))', gap: 16, marginBottom: 24 }}>
                {kpis.map(k => <KpiCard key={k.label} {...k} />)}
            </div>

            {/* Table card */}
            <div style={{ background: '#fff', borderRadius: 16, border: '1px solid #e2e8f0', overflow: 'hidden', boxShadow: '0 2px 12px rgba(15,23,42,0.05)' }}>

                {/* Header */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10, padding: '13px 18px', borderBottom: '1px solid #F0F0F0', background: 'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                        <div style={{ width: 3, height: 28, borderRadius: 2, background: '#FF5C00', flexShrink: 0 }} />
                        <div>
                            <div style={{ fontSize: 13.5, fontWeight: 700, color: '#1D1D1D' }}>Lead Response Time</div>
                            <div style={{ fontSize: 11, color: '#9CA3AF', marginTop: 1 }}>{rows.length} total leads</div>
                        </div>
                    </div>
                    <div style={{ display: 'flex', gap: 8 }}>
                        <a href={exportUrl('excel')}
                            style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 8, background: '#F0FDF4', border: '1.5px solid #BBF7D0', color: '#16A34A', fontSize: 12, fontWeight: 600, textDecoration: 'none' }}>
                            <LuFileSpreadsheet style={{ fontSize: 14, width: 14, height: 14 }} />
                            Export Excel
                        </a>
                        <a href={exportUrl('pdf')} target="_blank" rel="noreferrer"
                            style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 8, background: '#FEF2F2', border: '1.5px solid #FECACA', color: '#DC2626', fontSize: 12, fontWeight: 600, textDecoration: 'none' }}>
                            <LuFileText style={{ fontSize: 14, width: 14, height: 14 }} />
                            Export PDF
                        </a>
                    </div>
                </div>

                {/* Legend */}
                <div style={{ padding: '10px 22px', borderBottom: '1px solid #f1f5f9', background: '#f8fafc', display: 'flex', gap: 18, flexWrap: 'wrap' }}>
                    {[
                        { color: '#16a34a', bg: '#dcfce7', label: '≤ 30m  Fast' },
                        { color: '#b45309', bg: '#fef9c3', label: '31–120m  Medium' },
                        { color: '#dc2626', bg: '#fee2e2', label: '> 120m  Slow' },
                        { color: '#94a3b8', bg: '#f1f5f9', label: 'No response' },
                    ].map(l => (
                        <div key={l.label} style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <span style={{ width: 10, height: 10, borderRadius: 3, background: l.bg, border: `1.5px solid ${l.color}`, display: 'inline-block' }} />
                            <span style={{ fontSize: 11, color: '#64748b', fontWeight: 500 }}>{l.label}</span>
                        </div>
                    ))}
                </div>

                {/* Table */}
                <div style={{ overflowX: 'auto' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                        <thead>
                            <tr style={{ background: '#f8fafc', borderBottom: '2px solid #e2e8f0' }}>
                                {['#', 'Lead Code', 'Lead Name', 'Telecaller', 'Created At', 'First Response', 'Response Time'].map((h, i) => (
                                    <th key={h} style={{ padding: '11px 16px', textAlign: i >= 4 ? 'center' : i === 0 ? 'center' : 'left', fontSize: 11, fontWeight: 700, color: '#64748b', textTransform: 'uppercase', letterSpacing: '.06em', whiteSpace: 'nowrap' }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {paged.length === 0 ? (
                                <tr>
                                    <td colSpan={7} style={{ padding: '56px 20px', textAlign: 'center' }}>
                                        <LuTimer style={{ fontSize: 44, color: '#cbd5e1', display: 'block', marginBottom: 10, width: 44, height: 44, margin: '0 auto 10px' }} />
                                        <div style={{ color: '#94a3b8', fontSize: 14, fontWeight: 500 }}>No data for selected period</div>
                                    </td>
                                </tr>
                            ) : paged.map((r, i) => {
                                const isEven = i % 2 === 1;
                                const sno    = (safePage - 1) * PAGE_SIZE + i + 1;
                                const mins   = r.response_minutes;
                                const rowAccent = mins === null || mins === undefined ? 'transparent'
                                    : parseInt(mins) <= 30  ? '#10b981'
                                    : parseInt(mins) <= 120 ? '#f59e0b'
                                    : '#ef4444';

                                return (
                                    <tr key={i}
                                        style={{ background: isEven ? '#fafbfc' : '#fff', borderBottom: '1px solid #f1f5f9', transition: 'background .12s' }}
                                        onMouseEnter={e => e.currentTarget.style.background = '#f0f4ff'}
                                        onMouseLeave={e => e.currentTarget.style.background = isEven ? '#fafbfc' : '#fff'}>

                                        {/* # */}
                                        <td style={{ padding: '12px 16px', textAlign: 'center', color: '#94a3b8', fontSize: 12, fontWeight: 600 }}>{sno}</td>

                                        {/* Lead Code */}
                                        <td style={{ padding: '12px 16px' }}>
                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, background: '#f1f5f9', color: '#334155', fontSize: 12, fontWeight: 700, padding: '3px 10px', borderRadius: 8, fontFamily: 'monospace', letterSpacing: '.03em', borderLeft: `3px solid ${rowAccent}` }}>
                                                {r.lead_code}
                                            </span>
                                        </td>

                                        {/* Lead Name */}
                                        <td style={{ padding: '12px 16px' }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                                <div style={{ width: 30, height: 30, borderRadius: 8, background: 'linear-gradient(135deg,#ffe8d6,#ffd0b5)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                    <span style={{ fontSize: 12, fontWeight: 800, color: '#FF5C00' }}>{(r.lead_name ?? '?')[0].toUpperCase()}</span>
                                                </div>
                                                <span style={{ fontWeight: 600, color: '#1D1D1D', fontSize: 13 }}>{r.lead_name}</span>
                                            </div>
                                        </td>

                                        {/* Telecaller */}
                                        <td style={{ padding: '12px 16px' }}>
                                            {r.telecaller === 'Unassigned' ? (
                                                <span style={{ fontSize: 12, color: '#94a3b8', fontStyle: 'italic' }}>Unassigned</span>
                                            ) : (
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                                    <LuHeadphones style={{ fontSize: 14, color: '#FF5C00', width: 14, height: 14 }} />
                                                    <span style={{ fontSize: 13, color: '#334155', fontWeight: 500 }}>{r.telecaller}</span>
                                                </div>
                                            )}
                                        </td>

                                        {/* Created At */}
                                        <td style={{ padding: '12px 16px', textAlign: 'center' }}>
                                            <div style={{ fontSize: 12, color: '#475569' }}>{fmtDate(r.created_at)}</div>
                                        </td>

                                        {/* First Response */}
                                        <td style={{ padding: '12px 16px', textAlign: 'center' }}>
                                            {r.first_response_at
                                                ? <div style={{ fontSize: 12, color: '#10b981', fontWeight: 500 }}>{fmtDate(r.first_response_at)}</div>
                                                : <span style={{ color: '#cbd5e1', fontSize: 13 }}>—</span>}
                                        </td>

                                        {/* Response Time badge */}
                                        <td style={{ padding: '12px 16px', textAlign: 'center' }}>
                                            <ResponseBadge minutes={mins} />
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>

                {/* Pagination footer */}
                {rows.length > 0 && (
                    <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderTop: '1px solid #f1f5f9', flexWrap: 'wrap', gap: 10, background: '#fafbfc' }}>
                        <div style={{ fontSize: 12, color: '#64748b', fontWeight: 500 }}>
                            Showing <strong>{(safePage - 1) * PAGE_SIZE + 1}–{Math.min(safePage * PAGE_SIZE, rows.length)}</strong> of <strong>{rows.length}</strong> leads
                        </div>
                        <Pagination page={safePage} totalPages={totalPages} onChange={handlePage} />
                    </div>
                )}
            </div>
            </div>
        </>
    );
}
