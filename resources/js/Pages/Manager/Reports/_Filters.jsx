import { router } from '@inertiajs/react';
import { useState } from 'react';
import {
    LuCalendar, LuFilter, LuRefreshCw, LuDownload,
    LuSearch, LuFileText,
} from 'react-icons/lu';

/* ── Design tokens ─────────────────────────────────────────── */
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

const DATE_OPTS = [
    { value: '1',       label: 'Today'          },
    { value: '7',       label: 'Last 7 Days'    },
    { value: '30',      label: 'Last 30 Days'   },
    { value: '90',      label: 'Last 90 Days'   },
    { value: 'week',    label: 'This Week'      },
    { value: 'month',   label: 'This Month'     },
    { value: 'quarter', label: 'This Quarter'   },
    { value: 'year',    label: 'This Year'      },
];

const CALL_TYPE_OPTS = [
    { value: 'all',      label: 'All Calls'  },
    { value: 'inbound',  label: 'Inbound'    },
    { value: 'outbound', label: 'Outbound'   },
];

const selectStyle = {
    borderRadius: 8,
    border: `1.5px solid ${BOR}`,
    fontSize: 13,
    fontWeight: 500,
    color: DK,
    background: WH,
    padding: '8px 32px 8px 12px',
    height: 40,
    appearance: 'none',
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%239CA3AF'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E")`,
    backgroundRepeat: 'no-repeat',
    backgroundPosition: 'right 10px center',
    backgroundSize: '16px',
    width: '100%',
    boxShadow: '0 1px 3px rgba(0,0,0,0.04)',
    transition: 'border-color 0.18s, box-shadow 0.18s',
    cursor: 'pointer',
    outline: 'none',
    fontFamily: 'Poppins, sans-serif',
};

const labelStyle = {
    display: 'flex',
    alignItems: 'center',
    gap: 5,
    fontSize: 10.5,
    fontWeight: 700,
    color: BDY,
    marginBottom: 7,
    textTransform: 'uppercase',
    letterSpacing: '0.07em',
};

function FilterLabel({ Icon, label }) {
    return (
        <label style={labelStyle}>
            <Icon size={13} color={OR} />
            {label}
        </label>
    );
}

export default function ReportFilters({ filters, filterOptions, url, showCampaign = false, showCallType = false, exportSlug = null }) {
    const [dateRange,  setDateRange]  = useState(filters?.date_range ?? '30');
    const [source,     setSource]     = useState(filters?.source     ?? 'all');
    const [telecaller, setTelecaller] = useState(filters?.telecaller ?? 'all');
    const [campaign,   setCampaign]   = useState(filters?.campaign   ?? 'all');
    const [callType,   setCallType]   = useState(filters?.call_type  ?? 'all');

    function apply(e) {
        e.preventDefault();
        const params = { date_range: dateRange, source, telecaller };
        if (showCampaign) params.campaign  = campaign;
        if (showCallType) params.call_type = callType;
        router.get(url, params, { preserveState: false });
    }

    function reset() {
        setDateRange('30'); setSource('all'); setTelecaller('all');
        setCampaign('all'); setCallType('all');
        router.get(url, {}, { preserveState: false });
    }

    const extraCols = (showCampaign ? 1 : 0) + (showCallType ? 1 : 0);
    const colClass  = extraCols >= 2 ? 'col-md-2' : extraCols === 1 ? 'col-md-2' : 'col-md-3';

    const exportParams = new URLSearchParams({ date_range: dateRange, source, telecaller });
    if (showCampaign) exportParams.set('campaign', campaign);
    if (showCallType) exportParams.set('call_type', callType);
    const exportQs = exportParams.toString();

    return (
        <div style={{
            background: '#FAFAFA',
            border: `1.5px solid ${BOR}`,
            borderRadius: 14,
            padding: '18px 22px',
            marginBottom: 22,
            boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
            fontFamily: 'Poppins, sans-serif',
        }}>
            <form onSubmit={apply}>
                <div className="row g-3 align-items-end">
                    <div className={colClass}>
                        <FilterLabel Icon={LuCalendar} label="Period" />
                        <select style={selectStyle} value={dateRange} onChange={e => setDateRange(e.target.value)}>
                            {DATE_OPTS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                        </select>
                    </div>
                    <div className={colClass}>
                        <FilterLabel Icon={LuFilter} label="Source" />
                        <select style={selectStyle} value={source} onChange={e => setSource(e.target.value)}>
                            <option value="all">All Sources</option>
                            {(filterOptions?.sources ?? []).map(s => <option key={s} value={s}>{s}</option>)}
                        </select>
                    </div>
                    <div className={colClass}>
                        <FilterLabel Icon={LuFilter} label="Telecaller" />
                        <select style={selectStyle} value={telecaller} onChange={e => setTelecaller(e.target.value)}>
                            <option value="all">All Telecallers</option>
                            {(filterOptions?.telecallers ?? []).map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                        </select>
                    </div>
                    {showCampaign && (
                        <div className={colClass}>
                            <FilterLabel Icon={LuFilter} label="Campaign" />
                            <select style={selectStyle} value={campaign} onChange={e => setCampaign(e.target.value)}>
                                <option value="all">All Campaigns</option>
                                {(filterOptions?.campaigns ?? []).map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        </div>
                    )}
                    {showCallType && (
                        <div className={colClass}>
                            <FilterLabel Icon={LuFilter} label="Call Type" />
                            <select style={selectStyle} value={callType} onChange={e => setCallType(e.target.value)}>
                                {CALL_TYPE_OPTS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                            </select>
                        </div>
                    )}

                    {/* Action buttons */}
                    <div className="col d-flex gap-2 flex-wrap align-items-center">
                        <button type="submit" style={{
                            height: 40, padding: '0 20px',
                            background: OR, color: '#fff',
                            border: 'none', borderRadius: 8,
                            fontWeight: 700, fontSize: 13, cursor: 'pointer',
                            display: 'flex', alignItems: 'center', gap: 6,
                            boxShadow: '0 4px 12px rgba(255,92,0,0.28)',
                            whiteSpace: 'nowrap',
                            fontFamily: 'Poppins, sans-serif',
                        }}>
                            <LuSearch size={15} />Apply
                        </button>
                        <button type="button" onClick={reset} style={{
                            height: 40, padding: '0 16px',
                            background: WH, color: BDY,
                            border: `1.5px solid ${BOR}`, borderRadius: 8,
                            fontWeight: 600, fontSize: 13, cursor: 'pointer',
                            display: 'flex', alignItems: 'center', gap: 5,
                            whiteSpace: 'nowrap',
                            fontFamily: 'Poppins, sans-serif',
                        }}>
                            <LuRefreshCw size={14} />Reset
                        </button>
                        {exportSlug && (
                            <div className="dropdown">
                                <button
                                    className="dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    style={{
                                        height: 40, padding: '0 14px',
                                        background: WH, color: BDY,
                                        border: `1.5px solid ${BOR}`, borderRadius: 8,
                                        fontWeight: 600, fontSize: 13, cursor: 'pointer',
                                        display: 'flex', alignItems: 'center', gap: 6,
                                        whiteSpace: 'nowrap',
                                        fontFamily: 'Poppins, sans-serif',
                                    }}>
                                    <LuDownload size={15} color={OR} />Export
                                </button>
                                <ul className="dropdown-menu shadow" style={{
                                    borderRadius: 12, padding: 6,
                                    border: `1.5px solid ${BOR}`, minWidth: 160,
                                    boxShadow: '0 8px 24px rgba(0,0,0,0.10)',
                                    fontFamily: 'Poppins, sans-serif',
                                }}>
                                    <li>
                                        <a className="dropdown-item"
                                            href={`/manager/reports/export/${exportSlug}/excel?${exportQs}`}
                                            style={{ borderRadius: 8, fontSize: 13, fontWeight: 600, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <LuFileText size={16} color="#16A34A" />Excel (.xlsx)
                                        </a>
                                    </li>
                                    <li>
                                        <a className="dropdown-item"
                                            href={`/manager/reports/export/${exportSlug}/pdf?${exportQs}`}
                                            target="_blank" rel="noreferrer"
                                            style={{ borderRadius: 8, fontSize: 13, fontWeight: 600, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 8 }}>
                                            <LuFileText size={16} color="#DC2626" />PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        )}
                    </div>
                </div>
            </form>
        </div>
    );
}
