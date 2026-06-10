import { Head } from '@inertiajs/react';
import { useState } from 'react';
import ReportFilters from './_Filters';
import { ReportNavBar } from './Home';
import {
    LuChevronLeft, LuChevronRight, LuPhone, LuFileSpreadsheet,
    LuFileText, LuCheck, LuX, LuTimer, LuHeadphones,
} from 'react-icons/lu';

const PAGE_SIZE = 20;

function fmtDuration(seconds) {
    const s = Math.round(parseFloat(seconds) || 0);
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return [h, m, sec].map(v => String(v).padStart(2, '0')).join(':');
}

function RateBar({ rate }) {
    const r   = Math.min(100, Math.max(0, Number(rate) || 0));
    const col = r >= 70 ? '#10b981' : r >= 40 ? '#f59e0b' : '#ef4444';
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <div style={{ flex: 1, height: 6, background: '#f1f5f9', borderRadius: 3, minWidth: 60 }}>
                <div style={{ width: `${r}%`, height: '100%', background: col, borderRadius: 3, transition: 'width .4s' }} />
            </div>
            <span style={{ fontSize: 12, fontWeight: 700, color: col, minWidth: 36 }}>{r.toFixed(1)}%</span>
        </div>
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

export default function CallEfficiency({ filters, filterOptions, rows: rawRows }) {
    const rows = (rawRows ?? []).map(r => ({
        ...r,
        total_calls:     Number(r.total_calls     ?? 0),
        completed_calls: Number(r.completed_calls ?? 0),
        missed_calls:    Number(r.missed_calls    ?? 0),
        total_duration:  Number(r.total_duration  ?? 0),
        avg_duration:    Number(r.avg_duration     ?? 0),
        completion_rate: Number(r.completion_rate  ?? 0),
    }));

    const [page, setPage] = useState(1);

    const exportUrl = (fmt) => {
        const p = new URLSearchParams({
            date_range: filters?.date_range ?? '30',
            source:     filters?.source     ?? 'all',
            telecaller: filters?.telecaller ?? 'all',
        });
        return `/manager/reports/export/call-efficiency/${fmt}?${p}`;
    };

    const totalCalls     = rows.reduce((s, r) => s + r.total_calls,     0);
    const completedCalls = rows.reduce((s, r) => s + r.completed_calls, 0);
    const missedCalls    = rows.reduce((s, r) => s + r.missed_calls,    0);
    const overallRate    = totalCalls > 0 ? (completedCalls / totalCalls) * 100 : 0;
    const totalDurationSecs = rows.reduce((s, r) => s + r.total_duration, 0);

    const totalPages = Math.max(1, Math.ceil(rows.length / PAGE_SIZE));
    const safePage   = Math.min(page, totalPages);
    const paged      = rows.slice((safePage - 1) * PAGE_SIZE, safePage * PAGE_SIZE);

    const handlePage = (p) => { setPage(p); window.scrollTo({ top: 0, behavior: 'smooth' }); };

    const kpis = [
        {
            icon: LuPhone, label: 'Total Calls',
            value: totalCalls.toLocaleString(),
            sub: `${rows.length} telecaller${rows.length !== 1 ? 's' : ''}`,
            gradient: 'linear-gradient(135deg,#1D1D1D,#2d2d2d)', iconBg: '#1D1D1D',
        },
        {
            icon: LuPhone, label: 'Completed',
            value: completedCalls.toLocaleString(),
            sub: totalCalls > 0 ? `${Math.round((completedCalls / totalCalls) * 100)}% of total calls` : 'No calls yet',
            gradient: 'linear-gradient(135deg,#10b981,#059669)', iconBg: '#10b981',
        },
        {
            icon: LuPhone, label: 'Missed / Failed',
            value: missedCalls.toLocaleString(),
            sub: totalCalls > 0 ? `${Math.round((missedCalls / totalCalls) * 100)}% of total calls` : '—',
            gradient: 'linear-gradient(135deg,#ef4444,#dc2626)', iconBg: '#ef4444',
        },
        {
            icon: LuPhone, label: 'Completion Rate',
            value: totalCalls > 0 ? `${overallRate.toFixed(1)}%` : '—',
            sub: 'Overall across all telecallers',
            gradient: overallRate >= 70
                ? 'linear-gradient(135deg,#10b981,#059669)'
                : overallRate >= 40
                ? 'linear-gradient(135deg,#f59e0b,#d97706)'
                : 'linear-gradient(135deg,#FF5C00,#e04e00)',
            iconBg: '#FF5C00',
        },
    ];

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.rpt-ce,.rpt-ce div,.rpt-ce span:not([class*="material"]),.rpt-ce p,.rpt-ce h1,.rpt-ce h2,.rpt-ce h3,.rpt-ce h4,.rpt-ce h5,.rpt-ce h6,.rpt-ce button,.rpt-ce input,.rpt-ce select,.rpt-ce a,.rpt-ce th,.rpt-ce td,.rpt-ce label,.rpt-ce small{font-family:'Poppins',sans-serif!important;box-sizing:border-box;}`}</style>
            <Head title="Call Efficiency" />
            <div className="rpt-ce">
            <ReportNavBar active="/manager/reports/call-efficiency" />
            <ReportFilters filters={filters} filterOptions={filterOptions} url="/manager/reports/call-efficiency" exportSlug="call-efficiency" />

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
                            <div style={{ fontSize: 13.5, fontWeight: 700, color: '#1D1D1D' }}>Call Efficiency by Telecaller</div>
                            <div style={{ fontSize: 11, color: '#9CA3AF', marginTop: 1 }}>{rows.length} telecaller{rows.length !== 1 ? 's' : ''} · Total talk time: {fmtDuration(totalDurationSecs)}</div>
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

                {/* Summary strip */}
                <div style={{ display: 'flex', borderBottom: '1px solid #f1f5f9', background: '#f8fafc' }}>
                    {[
                        { label: 'Telecallers',  value: rows.length,                     Icon: LuHeadphones, color: '#1D1D1D' },
                        { label: 'Total Calls',  value: totalCalls.toLocaleString(),     Icon: LuPhone,      color: '#1D1D1D' },
                        { label: 'Completed',    value: completedCalls.toLocaleString(), Icon: LuPhone,      color: '#10b981' },
                        { label: 'Missed',       value: missedCalls.toLocaleString(),    Icon: LuPhone,      color: '#ef4444' },
                    ].map(s => (
                        <div key={s.label} style={{ flex: 1, padding: '12px 18px', display: 'flex', alignItems: 'center', gap: 10, borderRight: '1px solid #e2e8f0' }}>
                            <s.Icon style={{ fontSize: 18, color: s.color, width: 18, height: 18 }} />
                            <div>
                                <div style={{ fontSize: 18, fontWeight: 800, color: s.color, lineHeight: 1.1 }}>{s.value}</div>
                                <div style={{ fontSize: 11, color: '#94a3b8', fontWeight: 500 }}>{s.label}</div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Table */}
                <div style={{ overflowX: 'auto' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                        <thead>
                            <tr style={{ background: '#f8fafc', borderBottom: '2px solid #e2e8f0' }}>
                                {['#', 'Telecaller', 'Total Calls', 'Completed', 'Missed', 'Avg Duration', 'Completion Rate'].map((h, i) => (
                                    <th key={h} style={{ padding: '11px 16px', textAlign: i <= 1 ? 'left' : 'center', fontSize: 11, fontWeight: 700, color: '#64748b', textTransform: 'uppercase', letterSpacing: '.06em', whiteSpace: 'nowrap' }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {paged.length === 0 ? (
                                <tr>
                                    <td colSpan={7} style={{ padding: '56px 20px', textAlign: 'center' }}>
                                        <LuPhone style={{ fontSize: 44, color: '#cbd5e1', display: 'block', marginBottom: 10, width: 44, height: 44, margin: '0 auto 10px' }} />
                                        <div style={{ color: '#94a3b8', fontSize: 14, fontWeight: 500 }}>No call data for selected period</div>
                                    </td>
                                </tr>
                            ) : paged.map((r, i) => {
                                const isEven = i % 2 === 1;
                                const sno    = (safePage - 1) * PAGE_SIZE + i + 1;
                                const rate   = r.completion_rate;
                                const rateColor = rate >= 70 ? '#10b981' : rate >= 40 ? '#f59e0b' : '#ef4444';
                                const initial   = (r.telecaller_name ?? '?')[0].toUpperCase();

                                return (
                                    <tr key={i}
                                        style={{ background: isEven ? '#fafbfc' : '#fff', borderBottom: '1px solid #f1f5f9', transition: 'background .12s' }}
                                        onMouseEnter={e => e.currentTarget.style.background = '#f0f4ff'}
                                        onMouseLeave={e => e.currentTarget.style.background = isEven ? '#fafbfc' : '#fff'}>

                                        {/* # */}
                                        <td style={{ padding: '13px 16px', color: '#94a3b8', fontSize: 12, fontWeight: 600, width: 50 }}>{sno}</td>

                                        {/* Telecaller */}
                                        <td style={{ padding: '13px 16px' }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                <div style={{ width: 36, height: 36, borderRadius: 10, background: 'linear-gradient(135deg,#1D1D1D,#2d2d2d)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 2px 6px rgba(29,29,29,0.2)' }}>
                                                    <span style={{ fontSize: 14, fontWeight: 800, color: '#fff' }}>{initial}</span>
                                                </div>
                                                <div>
                                                    <div style={{ fontWeight: 700, color: '#1D1D1D', fontSize: 13 }}>{r.telecaller_name}</div>
                                                    <div style={{ fontSize: 11, color: '#94a3b8' }}>{r.total_calls} calls logged</div>
                                                </div>
                                            </div>
                                        </td>

                                        {/* Total Calls */}
                                        <td style={{ padding: '13px 16px', textAlign: 'center' }}>
                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#eff6ff', color: '#1d4ed8', fontWeight: 700, fontSize: 13, padding: '4px 12px', borderRadius: 20 }}>
                                                <LuPhone style={{ fontSize: 13, width: 13, height: 13 }} />
                                                {r.total_calls}
                                            </span>
                                        </td>

                                        {/* Completed */}
                                        <td style={{ padding: '13px 16px', textAlign: 'center' }}>
                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#dcfce7', color: '#16a34a', fontWeight: 700, fontSize: 13, padding: '4px 12px', borderRadius: 20 }}>
                                                <LuCheck style={{ fontSize: 13, width: 13, height: 13 }} />
                                                {r.completed_calls}
                                            </span>
                                        </td>

                                        {/* Missed */}
                                        <td style={{ padding: '13px 16px', textAlign: 'center' }}>
                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#fee2e2', color: '#dc2626', fontWeight: 700, fontSize: 13, padding: '4px 12px', borderRadius: 20 }}>
                                                <LuX style={{ fontSize: 13, width: 13, height: 13 }} />
                                                {r.missed_calls}
                                            </span>
                                        </td>

                                        {/* Avg Duration */}
                                        <td style={{ padding: '13px 16px', textAlign: 'center' }}>
                                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#f8fafc', color: '#475569', fontWeight: 600, fontSize: 12, padding: '4px 10px', borderRadius: 8, fontFamily: 'monospace' }}>
                                                <LuTimer style={{ fontSize: 12, color: '#94a3b8', width: 12, height: 12 }} />
                                                {fmtDuration(r.avg_duration)}
                                            </span>
                                        </td>

                                        {/* Completion Rate */}
                                        <td style={{ padding: '13px 20px', minWidth: 160 }}>
                                            <RateBar rate={rate} />
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
                            Showing <strong>{(safePage - 1) * PAGE_SIZE + 1}–{Math.min(safePage * PAGE_SIZE, rows.length)}</strong> of <strong>{rows.length}</strong> telecallers
                        </div>
                        <Pagination page={safePage} totalPages={totalPages} onChange={handlePage} />
                    </div>
                )}
            </div>
            </div>
        </>
    );
}
