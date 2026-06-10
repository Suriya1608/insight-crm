import { Head, router } from '@inertiajs/react';
import ReportFilters from './_Filters';
import { useState, useMemo } from 'react';
import { ReportNavBar } from './Home';
import {
    LuChevronLeft, LuChevronRight, LuCalendar, LuFileSpreadsheet,
    LuFileText, LuList, LuUsers, LuX, LuUser,
} from 'react-icons/lu';

const PAGE_SIZE = 20;

const TABS = [
    { key: 'daily',   label: 'Daily',   Icon: LuCalendar },
    { key: 'weekly',  label: 'Weekly',  Icon: LuCalendar },
    { key: 'monthly', label: 'Monthly', Icon: LuCalendar },
];

function ConvBar({ converted, total }) {
    converted = Number(converted ?? 0);
    total     = Number(total     ?? 0);
    const rate = total > 0 ? Math.round((converted / total) * 100) : 0;
    const color = rate >= 20 ? '#10b981' : rate >= 8 ? '#f59e0b' : '#ef4444';
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 140 }}>
            <span style={{ fontWeight: 700, color, fontSize: 13, minWidth: 20, textAlign: 'right' }}>{converted}</span>
            <div style={{ flex: 1, height: 5, background: '#f1f5f9', borderRadius: 3 }}>
                <div style={{ width: `${rate}%`, height: '100%', background: color, borderRadius: 3, transition: 'width .4s' }} />
            </div>
            <span style={{ fontSize: 11, fontWeight: 600, color: '#94a3b8', minWidth: 30 }}>{rate}%</span>
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
    const btn = (label, target, disabled = false) => (
        <button key={label} disabled={disabled || target === page}
            onClick={() => !disabled && onChange(target)}
            style={{
                minWidth: 34, height: 34, borderRadius: 8, border: '1.5px solid',
                borderColor: target === page ? 'transparent' : '#e2e8f0',
                background: target === page ? '#FF5C00' : '#fff',
                color: target === page ? '#fff' : disabled ? '#cbd5e1' : '#475569',
                fontWeight: target === page ? 700 : 500, fontSize: 13,
                cursor: disabled ? 'default' : 'pointer', display: 'inline-flex',
                alignItems: 'center', justifyContent: 'center',
                boxShadow: target === page ? '0 2px 8px rgba(255,92,0,0.2)' : 'none',
            }}>{label}</button>
    );
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 4, flexWrap: 'wrap' }}>
            {btn(<LuChevronLeft style={{ fontSize: 16, width: 16, height: 16 }} />, page - 1, page === 1)}
            {pages.map((p, i) => p === '...'
                ? <span key={`e${i}`} style={{ padding: '0 4px', color: '#94a3b8' }}>…</span>
                : btn(p, p)
            )}
            {btn(<LuChevronRight style={{ fontSize: 16, width: 16, height: 16 }} />, page + 1, page === totalPages)}
        </div>
    );
}

function PeriodTable({ rows = [], colLabel, tabKey, dateFrom, dateTo, exportUrl }) {
    const [page, setPage] = useState(1);

    const filtered = useMemo(() => {
        let r = rows;
        if (tabKey === 'daily') {
            if (dateFrom) r = r.filter(row => (row.period_date ?? '') >= dateFrom);
            if (dateTo)   r = r.filter(row => (row.period_date ?? '') <= dateTo);
        }
        return r;
    }, [rows, dateFrom, dateTo, tabKey]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    const safePage   = Math.min(page, totalPages);
    const paged      = filtered.slice((safePage - 1) * PAGE_SIZE, safePage * PAGE_SIZE);
    const totalLeads     = filtered.reduce((s, r) => s + Number(r.total     ?? 0), 0);
    const totalConverted = filtered.reduce((s, r) => s + Number(r.converted ?? 0), 0);
    const overallRate    = totalLeads > 0 ? Math.round((totalConverted / totalLeads) * 100) : 0;

    const handlePageChange = (p) => { setPage(p); window.scrollTo({ top: 0, behavior: 'smooth' }); };

    return (
        <div style={{ background: '#fff', borderRadius: 16, border: '1px solid #e2e8f0', overflow: 'hidden', boxShadow: '0 2px 12px rgba(15,23,42,0.05)' }}>
            {/* Table header bar */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10, padding: '13px 18px', borderBottom: '1px solid #F0F0F0', background: 'linear-gradient(135deg,#FAFBFC,#FFFFFF)' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                    <div style={{ width: 3, height: 28, borderRadius: 2, background: '#FF5C00', flexShrink: 0 }} />
                    <div>
                        <div style={{ fontSize: 13.5, fontWeight: 700, color: '#1D1D1D' }}>
                            {tabKey === 'daily' ? 'Daily' : tabKey === 'weekly' ? 'Weekly' : 'Monthly'} Breakdown
                        </div>
                        <div style={{ fontSize: 11, color: '#9CA3AF', marginTop: 1 }}>
                            {filtered.length} records · {totalLeads} leads
                        </div>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: 8 }}>
                    <a href={exportUrl('excel')}
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 8, background: '#F0FDF4', border: '1.5px solid #BBF7D0', color: '#16A34A', fontSize: 12, fontWeight: 600, textDecoration: 'none', cursor: 'pointer' }}>
                        <LuFileSpreadsheet style={{ fontSize: 14, width: 14, height: 14 }} />
                        Excel
                    </a>
                    <a href={exportUrl('pdf')} target="_blank" rel="noreferrer"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 8, background: '#FEF2F2', border: '1.5px solid #FECACA', color: '#DC2626', fontSize: 12, fontWeight: 600, textDecoration: 'none', cursor: 'pointer' }}>
                        <LuFileText style={{ fontSize: 14, width: 14, height: 14 }} />
                        PDF
                    </a>
                </div>
            </div>

            {/* Summary strip */}
            <div style={{ display: 'flex', gap: 0, borderBottom: '1px solid #f1f5f9', background: '#f8fafc' }}>
                {[
                    { label: 'Total Records', value: filtered.length,  Icon: LuList,  color: '#FF5C00' },
                    { label: 'Total Leads',   value: totalLeads,        Icon: LuUsers, color: '#1D1D1D' },
                    { label: 'Converted',     value: totalConverted,   Icon: LuCalendar, color: '#10b981' },
                    { label: 'Conv. Rate',    value: overallRate + '%', Icon: LuCalendar, color: overallRate >= 20 ? '#10b981' : overallRate >= 8 ? '#f59e0b' : '#ef4444' },
                ].map(stat => (
                    <div key={stat.label} style={{ flex: 1, padding: '12px 18px', display: 'flex', alignItems: 'center', gap: 10, borderRight: '1px solid #e2e8f0' }}>
                        <stat.Icon style={{ fontSize: 18, color: stat.color, width: 18, height: 18 }} />
                        <div>
                            <div style={{ fontSize: 18, fontWeight: 800, color: stat.color, lineHeight: 1.1 }}>{stat.value}</div>
                            <div style={{ fontSize: 11, color: '#94a3b8', fontWeight: 500 }}>{stat.label}</div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Table */}
            <div style={{ overflowX: 'auto' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr style={{ background: '#f8fafc', borderBottom: '2px solid #e2e8f0' }}>
                            <th style={{ padding: '12px 20px', textAlign: 'left', fontSize: 11, fontWeight: 700, color: '#64748b', textTransform: 'uppercase', letterSpacing: '.06em', width: 50 }}>#</th>
                            <th style={{ padding: '12px 20px', textAlign: 'left', fontSize: 11, fontWeight: 700, color: '#64748b', textTransform: 'uppercase', letterSpacing: '.06em' }}>{colLabel}</th>
                            <th style={{ padding: '12px 20px', textAlign: 'right', fontSize: 11, fontWeight: 700, color: '#64748b', textTransform: 'uppercase', letterSpacing: '.06em' }}>Total Leads</th>
                            <th style={{ padding: '12px 20px', textAlign: 'left',  fontSize: 11, fontWeight: 700, color: '#64748b', textTransform: 'uppercase', letterSpacing: '.06em', minWidth: 180 }}>Converted</th>
                        </tr>
                    </thead>
                    <tbody>
                        {paged.length === 0 ? (
                            <tr>
                                <td colSpan={4} style={{ padding: '48px 20px', textAlign: 'center' }}>
                                    <LuCalendar style={{ fontSize: 40, color: '#cbd5e1', display: 'block', marginBottom: 8, width: 40, height: 40, margin: '0 auto 8px' }} />
                                    <div style={{ color: '#94a3b8', fontSize: 14 }}>No records match your search</div>
                                </td>
                            </tr>
                        ) : paged.map((r, i) => {
                            const sno = (safePage - 1) * PAGE_SIZE + i + 1;
                            const period = r.period_date ?? r.period_week ?? r.period_month ?? '—';
                            const isEven = i % 2 === 1;
                            return (
                                <tr key={i} style={{ background: isEven ? '#fafbfc' : '#fff', borderBottom: '1px solid #f1f5f9', transition: 'background .1s' }}
                                    onMouseEnter={e => e.currentTarget.style.background = '#f0f4ff'}
                                    onMouseLeave={e => e.currentTarget.style.background = isEven ? '#fafbfc' : '#fff'}>
                                    <td style={{ padding: '13px 20px', color: '#94a3b8', fontWeight: 600, fontSize: 12 }}>{sno}</td>
                                    <td style={{ padding: '13px 20px' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <div style={{ width: 32, height: 32, borderRadius: 8, background: 'linear-gradient(135deg,#ffe8d6,#ffd0b5)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                <LuCalendar style={{ fontSize: 14, color: '#FF5C00', width: 14, height: 14 }} />
                                            </div>
                                            <span style={{ fontWeight: 600, color: '#1D1D1D', fontSize: 13 }}>{period}</span>
                                        </div>
                                    </td>
                                    <td style={{ padding: '13px 20px', textAlign: 'right' }}>
                                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, background: '#eff6ff', color: '#1d4ed8', fontWeight: 700, fontSize: 13, padding: '3px 12px', borderRadius: 20 }}>
                                            <LuUser style={{ fontSize: 13, width: 13, height: 13 }} />
                                            {r.total}
                                        </span>
                                    </td>
                                    <td style={{ padding: '13px 20px' }}>
                                        <ConvBar converted={r.converted} total={r.total} />
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            {/* Pagination footer */}
            {filtered.length > 0 && (
                <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderTop: '1px solid #f1f5f9', flexWrap: 'wrap', gap: 10, background: '#fafbfc' }}>
                    <div style={{ fontSize: 12, color: '#64748b', fontWeight: 500 }}>
                        Showing <strong>{(safePage - 1) * PAGE_SIZE + 1}–{Math.min(safePage * PAGE_SIZE, filtered.length)}</strong> of <strong>{filtered.length}</strong> records
                    </div>
                    <Pagination page={safePage} totalPages={totalPages} onChange={handlePageChange} />
                </div>
            )}
        </div>
    );
}

export default function Period({ filters, filterOptions, daily, weekly, monthly }) {
    const [tab,      setTab]      = useState('daily');
    const [dateFrom, setDateFrom] = useState('');
    const [dateTo,   setDateTo]   = useState('');

    const exportUrl = (fmt) => {
        const p = new URLSearchParams({
            date_range: filters?.date_range ?? '30',
            source:     filters?.source     ?? 'all',
            telecaller: filters?.telecaller ?? 'all',
        });
        return `/manager/reports/export/period/${fmt}?${p}`;
    };

    const rows = tab === 'daily' ? (daily ?? []) : tab === 'weekly' ? (weekly ?? []) : (monthly ?? []);
    const colLabel = tab === 'daily' ? 'Date' : tab === 'weekly' ? 'Week' : 'Month';

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.rpt-period,.rpt-period div,.rpt-period span:not([class*="material"]),.rpt-period p,.rpt-period h1,.rpt-period h2,.rpt-period h3,.rpt-period h4,.rpt-period h5,.rpt-period h6,.rpt-period button,.rpt-period input,.rpt-period select,.rpt-period a,.rpt-period th,.rpt-period td,.rpt-period label,.rpt-period small{font-family:'Poppins',sans-serif!important;box-sizing:border-box;}`}</style>
            <Head title="Period Analysis" />
            <div className="rpt-period">
            <ReportNavBar active="/manager/reports/period" />

            <ReportFilters filters={filters} filterOptions={filterOptions} url="/manager/reports/period" exportSlug="period" />

            {/* Tab + Search bar */}
            <div style={{ background: '#fff', border: '1.5px solid #e2e8f0', borderRadius: 14, padding: '16px 20px', marginBottom: 20, boxShadow: '0 2px 10px rgba(15,23,42,0.04)' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
                    {/* Tabs */}
                    <div style={{ display: 'flex', gap: 6, background: '#f1f5f9', borderRadius: 10, padding: 4 }}>
                        {TABS.map(t => (
                            <button key={t.key} type="button"
                                onClick={() => setTab(t.key)}
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 5,
                                    padding: '7px 16px', borderRadius: 8, border: 'none', cursor: 'pointer',
                                    fontWeight: 600, fontSize: 13,
                                    background: tab === t.key ? '#FF5C00' : 'transparent',
                                    color: tab === t.key ? '#fff' : '#64748b',
                                    boxShadow: tab === t.key ? '0 2px 8px rgba(255,92,0,0.25)' : 'none',
                                    transition: 'all .15s',
                                }}>
                                <t.Icon style={{ fontSize: 14, width: 14, height: 14 }} />
                                {t.label}
                            </button>
                        ))}
                    </div>

                    {/* Date from/to (only for daily) */}
                    {tab === 'daily' && (
                        <>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <span style={{ fontSize: 11, fontWeight: 700, color: '#64748b', whiteSpace: 'nowrap' }}>FROM</span>
                                <input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)}
                                    style={{ height: 36, borderRadius: 8, border: '1.5px solid #e2e8f0', fontSize: 13, color: '#1D1D1D', padding: '0 10px', outline: 'none', background: '#f8fafc' }} />
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <span style={{ fontSize: 11, fontWeight: 700, color: '#64748b', whiteSpace: 'nowrap' }}>TO</span>
                                <input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)}
                                    style={{ height: 36, borderRadius: 8, border: '1.5px solid #e2e8f0', fontSize: 13, color: '#1D1D1D', padding: '0 10px', outline: 'none', background: '#f8fafc' }} />
                            </div>
                            {(dateFrom || dateTo) && (
                                <button type="button" onClick={() => { setDateFrom(''); setDateTo(''); }}
                                    style={{ height: 36, padding: '0 12px', borderRadius: 8, border: '1.5px solid #fecaca', background: '#fef2f2', color: '#ef4444', fontSize: 12, fontWeight: 600, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 4 }}>
                                    <LuX style={{ fontSize: 14, width: 14, height: 14 }} />
                                    Clear
                                </button>
                            )}
                        </>
                    )}

                    <div style={{ marginLeft: 'auto', fontSize: 12, color: '#94a3b8' }}>
                        {rows.length} total records
                    </div>
                </div>
            </div>

            {/* Table */}
            <PeriodTable
                key={tab}
                rows={rows}
                colLabel={colLabel}
                tabKey={tab}
                dateFrom={dateFrom}
                dateTo={dateTo}
                exportUrl={exportUrl}
            />
            </div>
        </>
    );
}
