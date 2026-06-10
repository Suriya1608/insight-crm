import { Head } from '@inertiajs/react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend,
    ResponsiveContainer,
} from 'recharts';
import ReportFilters from './_Filters';
import { ReportNavBar } from './Home';
import {
    LuFileSpreadsheet, LuFileText, LuChartBar,
    LuUsers, LuCircleCheck, LuPercent, LuTrophy,
    LuNetwork, LuStar, LuTrendingUp,
} from 'react-icons/lu';

const PALETTE = ['#FF5C00','#10b981','#f59e0b','#06b6d4','#8b5cf6','#ef4444','#0ea5e9','#14b8a6','#f97316','#a855f7'];

const card = {
    background: '#fff',
    borderRadius: 16,
    padding: '20px 22px',
    boxShadow: '0 2px 12px rgba(15,23,42,0.06)',
    border: '1px solid #e2e8f0',
};

const rateColor = (r) => r >= 20 ? '#10b981' : r >= 8 ? '#f59e0b' : '#ef4444';

const KpiCard = ({ icon: Icon, iconColor, iconBg, value, suffix, label }) => (
    <div style={{ ...card, display: 'flex', alignItems: 'center', gap: 16 }}>
        <div style={{ width: 52, height: 52, borderRadius: 14, background: iconBg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
            <Icon style={{ fontSize: 26, color: iconColor, width: 26, height: 26 }} />
        </div>
        <div style={{ overflow: 'hidden' }}>
            <div style={{ fontSize: typeof value === 'string' ? 17 : 26, fontWeight: 800, color: '#1D1D1D', lineHeight: 1.15, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                {value}{suffix ?? ''}
            </div>
            <div style={{ fontSize: 11.5, color: '#64748b', fontWeight: 600, marginTop: 4 }}>{label}</div>
        </div>
    </div>
);

const CustomTooltip = ({ active, payload, label }) => {
    if (!active || !payload?.length) return null;
    return (
        <div style={{ background: '#fff', border: '1px solid #e2e8f0', borderRadius: 10, padding: '10px 14px', boxShadow: '0 4px 16px rgba(15,23,42,0.1)', fontSize: 12 }}>
            <div style={{ fontWeight: 700, color: '#1D1D1D', marginBottom: 6 }}>{payload[0]?.payload?.fullName ?? label}</div>
            {payload.map((p, i) => (
                <div key={i} style={{ color: p.color, fontWeight: 600 }}>{p.name}: <span style={{ color: '#1D1D1D' }}>{p.value}</span></div>
            ))}
        </div>
    );
};


export default function SourcePerformance({ filters, filterOptions, rows, totalLeads, totalConverted, avgRate, topSource, totalSources }) {
    const safeRows = rows ?? [];

    const exportUrl = (fmt) => {
        const p = new URLSearchParams({
            date_range: filters?.date_range ?? '30',
            source:     filters?.source     ?? 'all',
            telecaller: filters?.telecaller ?? 'all',
        });
        return `/manager/reports/export/source-performance/${fmt}?${p}`;
    };

    const chartData = safeRows.slice(0, 10).map(r => ({
        name:     (r.source || '—').length > 13 ? (r.source || '—').slice(0, 13) + '…' : (r.source || '—'),
        fullName: r.source || '—',
        'Total Leads': +r.total_leads,
        'Converted':   +r.converted_leads,
        'Interested':  +(r.interested_leads ?? 0),
    }));

    const totalInterested = safeRows.reduce((a, r) => a + (+(r.interested_leads ?? 0)), 0);

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
.rpt-src,.rpt-src div,.rpt-src span:not([class*="material"]),.rpt-src p,.rpt-src h1,.rpt-src h2,.rpt-src h3,.rpt-src h4,.rpt-src h5,.rpt-src h6,.rpt-src button,.rpt-src input,.rpt-src select,.rpt-src a,.rpt-src th,.rpt-src td,.rpt-src label,.rpt-src small{font-family:'Poppins',sans-serif!important;box-sizing:border-box;}`}</style>
            <Head title="Source Performance" />
            <div className="rpt-src">

            {/* Nav */}
            <ReportNavBar active="/manager/reports/source-performance" />

            {/* Filters */}
            <ReportFilters
                filters={filters}
                filterOptions={filterOptions}
                url="/manager/reports/source-performance"
                exportSlug="source-performance"
            />

            {/* KPI Cards */}
            <div className="row g-3 mb-4">
                <div className="col-6 col-lg-3">
                    <KpiCard icon={LuUsers}        iconColor="#FF5C00" iconBg="rgba(255,92,0,0.11)"   value={totalLeads ?? 0}     label="Total Leads"       />
                </div>
                <div className="col-6 col-lg-3">
                    <KpiCard icon={LuCircleCheck}  iconColor="#10b981" iconBg="rgba(16,185,129,0.11)"  value={totalConverted ?? 0} label="Total Converted"   />
                </div>
                <div className="col-6 col-lg-3">
                    <KpiCard icon={LuPercent}      iconColor="#8b5cf6" iconBg="rgba(139,92,246,0.11)"  value={avgRate ?? 0}        suffix="%" label="Avg Conversion Rate" />
                </div>
                <div className="col-6 col-lg-3">
                    <KpiCard icon={LuTrophy}       iconColor="#f59e0b" iconBg="rgba(245,158,11,0.11)"  value={topSource ?? '—'}    label="Top Source (by Conversions)" />
                </div>
            </div>

            {/* Secondary KPI strip */}
            <div className="row g-3 mb-4">
                <div className="col-4 col-lg-4">
                    <div style={{ ...card, display: 'flex', alignItems: 'center', gap: 12, padding: '14px 18px' }}>
                        <LuNetwork style={{ color: '#06b6d4', fontSize: 22, width: 22, height: 22 }} />
                        <div>
                            <div style={{ fontWeight: 800, fontSize: 20, color: '#1D1D1D' }}>{totalSources ?? 0}</div>
                            <div style={{ fontSize: 11, color: '#64748b', fontWeight: 600 }}>Active Sources</div>
                        </div>
                    </div>
                </div>
                <div className="col-4 col-lg-4">
                    <div style={{ ...card, display: 'flex', alignItems: 'center', gap: 12, padding: '14px 18px' }}>
                        <LuStar style={{ color: '#f59e0b', fontSize: 22, width: 22, height: 22 }} />
                        <div>
                            <div style={{ fontWeight: 800, fontSize: 20, color: '#1D1D1D' }}>{totalInterested}</div>
                            <div style={{ fontSize: 11, color: '#64748b', fontWeight: 600 }}>Interested Leads</div>
                        </div>
                    </div>
                </div>
                <div className="col-4 col-lg-4">
                    <div style={{ ...card, display: 'flex', alignItems: 'center', gap: 12, padding: '14px 18px' }}>
                        <LuTrendingUp style={{ color: '#10b981', fontSize: 22, width: 22, height: 22 }} />
                        <div>
                            <div style={{ fontWeight: 800, fontSize: 20, color: '#1D1D1D' }}>
                                {totalLeads > 0 ? Math.round(((totalInterested + (totalConverted ?? 0)) / totalLeads) * 100) : 0}%
                            </div>
                            <div style={{ fontSize: 11, color: '#64748b', fontWeight: 600 }}>Pipeline Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bar Chart — full width */}
            {safeRows.length > 0 && (
                <div className="mb-4">
                    <div style={card}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 18 }}>
                            <LuChartBar style={{ color: '#FF5C00', fontSize: 20, width: 20, height: 20 }} />
                            <div>
                                <div style={{ fontWeight: 700, fontSize: 14, color: '#1D1D1D' }}>Leads by Source</div>
                                <div style={{ fontSize: 11, color: '#64748b' }}>Top {Math.min(safeRows.length, 10)} sources — Total Leads vs Interested vs Converted</div>
                            </div>
                        </div>
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={chartData} margin={{ top: 0, right: 16, left: -10, bottom: 54 }} barGap={3} barCategoryGap="28%">
                                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" vertical={false} />
                                <XAxis dataKey="name" tick={{ fontSize: 11, fill: '#64748b' }} angle={-32} textAnchor="end" interval={0} />
                                <YAxis tick={{ fontSize: 11, fill: '#64748b' }} />
                                <Tooltip content={<CustomTooltip />} />
                                <Legend wrapperStyle={{ paddingTop: 10, fontSize: 12 }} />
                                <Bar dataKey="Total Leads" fill="#FF5C00" radius={[4,4,0,0]} maxBarSize={36} />
                                <Bar dataKey="Interested"  fill="#f59e0b" radius={[4,4,0,0]} maxBarSize={36} />
                                <Bar dataKey="Converted"   fill="#10b981" radius={[4,4,0,0]} maxBarSize={36} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            )}

            {/* Table */}
            <div className="custom-table">
                <div className="table-header" style={{ flexWrap: 'wrap', gap: 10 }}>
                    <div>
                        <h3 style={{ margin: 0 }}>Source Performance</h3>
                        <p style={{ margin: '3px 0 0', fontSize: 12, color: '#64748b' }}>
                            {safeRows.length} source{safeRows.length !== 1 ? 's' : ''} &nbsp;·&nbsp; {totalLeads ?? 0} total leads
                        </p>
                    </div>
                    <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
                        <a href={exportUrl('excel')}
                            className="btn btn-sm btn-outline-success"
                            style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                            <LuFileSpreadsheet style={{ fontSize: 15, width: 15, height: 15 }} />
                            Export Excel
                        </a>
                        <a href={exportUrl('pdf')}
                            className="btn btn-sm btn-primary"
                            target="_blank" rel="noreferrer"
                            style={{ display: 'flex', alignItems: 'center', gap: 5, background: '#1D1D1D', borderColor: 'transparent' }}>
                            <LuFileText style={{ fontSize: 15, width: 15, height: 15 }} />
                            Export PDF
                        </a>
                    </div>
                </div>

                <div className="table-responsive">
                    <table className="table mb-0" style={{ minWidth: 700 }}>
                        <thead>
                            <tr>
                                <th style={{ width: 46 }}>#</th>
                                <th>Source</th>
                                <th style={{ textAlign: 'right' }}>Total Leads</th>
                                <th style={{ textAlign: 'right' }}>Interested</th>
                                <th style={{ textAlign: 'right' }}>Converted</th>
                                <th style={{ minWidth: 160 }}>Contact Rate</th>
                                <th style={{ minWidth: 160 }}>Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            {safeRows.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="text-center py-5 text-muted">
                                        <LuNetwork style={{ fontSize: 32, width: 32, height: 32, opacity: 0.3, display: 'block', margin: '0 auto 8px' }} />
                                        No data for the selected period.
                                    </td>
                                </tr>
                            ) : safeRows.map((r, i) => (
                                <tr key={i}>
                                    <td>
                                        <span style={{
                                            display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                                            width: 26, height: 26, borderRadius: 7,
                                            background: i === 0 ? '#fef3c7' : i === 1 ? '#f1f5f9' : i === 2 ? '#fff7ed' : '#f8fafc',
                                            color:      i === 0 ? '#d97706' : i === 1 ? '#475569' : i === 2 ? '#ea580c' : '#94a3b8',
                                            fontSize: 11.5, fontWeight: 800,
                                        }}>{i + 1}</span>
                                    </td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                                            <div style={{
                                                width: 9, height: 9, borderRadius: '50%',
                                                background: PALETTE[i % PALETTE.length], flexShrink: 0,
                                            }} />
                                            <span style={{ fontWeight: 600, color: '#1D1D1D' }}>{r.source || '—'}</span>
                                        </div>
                                    </td>
                                    <td style={{ textAlign: 'right' }}>
                                        <span style={{ fontWeight: 700, fontSize: 14, color: '#1D1D1D' }}>{r.total_leads}</span>
                                    </td>
                                    <td style={{ textAlign: 'right' }}>
                                        <span style={{
                                            padding: '3px 10px', borderRadius: 20,
                                            background: 'rgba(245,158,11,0.1)', color: '#d97706',
                                            fontSize: 12, fontWeight: 700,
                                        }}>{r.interested_leads ?? 0}</span>
                                    </td>
                                    <td style={{ textAlign: 'right' }}>
                                        <span style={{
                                            padding: '3px 10px', borderRadius: 20,
                                            background: 'rgba(16,185,129,0.1)', color: '#059669',
                                            fontSize: 12, fontWeight: 700,
                                        }}>{r.converted_leads}</span>
                                    </td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <div style={{ flexGrow: 1, height: 7, borderRadius: 4, background: '#f1f5f9', overflow: 'hidden' }}>
                                                <div style={{ height: '100%', borderRadius: 4, background: '#06b6d4', width: `${Math.min(r.contact_rate ?? 0, 100)}%`, transition: 'width 0.4s' }} />
                                            </div>
                                            <span style={{ fontSize: 12, fontWeight: 600, color: '#475569', minWidth: 40, textAlign: 'right' }}>{r.contact_rate ?? 0}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <div style={{ flexGrow: 1, height: 7, borderRadius: 4, background: '#f1f5f9', overflow: 'hidden' }}>
                                                <div style={{ height: '100%', borderRadius: 4, background: rateColor(r.conversion_rate), width: `${Math.min(r.conversion_rate, 100)}%`, transition: 'width 0.4s' }} />
                                            </div>
                                            <span style={{ fontSize: 12, fontWeight: 700, color: rateColor(r.conversion_rate), minWidth: 40, textAlign: 'right' }}>
                                                {r.conversion_rate}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                        {safeRows.length > 0 && (
                            <tfoot>
                                <tr style={{ borderTop: '2px solid #e2e8f0', background: '#f8fafc' }}>
                                    <td colSpan={2} style={{ fontWeight: 700, fontSize: 13, color: '#1D1D1D', padding: '10px 16px' }}>
                                        Total
                                    </td>
                                    <td style={{ textAlign: 'right', fontWeight: 800, fontSize: 14, color: '#1D1D1D' }}>{totalLeads ?? 0}</td>
                                    <td style={{ textAlign: 'right' }}>
                                        <span style={{ padding: '3px 10px', borderRadius: 20, background: 'rgba(245,158,11,0.1)', color: '#d97706', fontSize: 12, fontWeight: 700 }}>
                                            {totalInterested}
                                        </span>
                                    </td>
                                    <td style={{ textAlign: 'right' }}>
                                        <span style={{ padding: '3px 10px', borderRadius: 20, background: 'rgba(16,185,129,0.1)', color: '#059669', fontSize: 12, fontWeight: 700 }}>
                                            {totalConverted ?? 0}
                                        </span>
                                    </td>
                                    <td />
                                    <td style={{ fontWeight: 800, fontSize: 13, color: '#FF5C00' }}>{avgRate ?? 0}%</td>
                                </tr>
                            </tfoot>
                        )}
                    </table>
                </div>
            </div>
            </div>
        </>
    );
}
