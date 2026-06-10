import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import {
    LuChevronLeft, LuUpload, LuUsers, LuPhone, LuCheck, LuTrendingUp,
    LuFilter, LuSearch, LuRefreshCw, LuDownload, LuExternalLink,
    LuChartBar, LuSettings2, LuX, LuMessageSquare, LuSend, LuTriangleAlert,
    LuChevronLeft as LuPrev, LuChevronRight as LuNext,
} from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';
const CARD_SHADOW = '0 1px 6px rgba(0,0,0,0.06)';

const STATUS_MAP = {
    new:            { label: 'Pending',        dot: '#94a3b8', bg: '#f1f5f9', color: '#64748b' },
    pending:        { label: 'Pending',        dot: '#94a3b8', bg: '#f1f5f9', color: '#64748b' },
    assigned:       { label: 'Assigned',       dot: '#60a5fa', bg: '#eff6ff', color: '#2563eb' },
    called:         { label: 'Called',         dot: '#38bdf8', bg: '#e0f2fe', color: '#0284c7' },
    contacted:      { label: 'Contacted',      dot: '#38bdf8', bg: '#e0f2fe', color: '#0284c7' },
    interested:     { label: 'Interested',     dot: '#34d399', bg: '#dcfce7', color: '#16a34a' },
    not_interested: { label: 'Not Interested', dot: '#f87171', bg: '#fee2e2', color: '#dc2626' },
    no_answer:      { label: 'No Answer',      dot: '#fbbf24', bg: '#fef9c3', color: '#ca8a04' },
    callback:       { label: 'Callback',       dot: '#a78bfa', bg: '#ede9fe', color: '#7c3aed' },
    follow_up:      { label: 'Follow-up',      dot: '#a78bfa', bg: '#ede9fe', color: '#7c3aed' },
    converted:      { label: 'Converted',      dot: '#10b981', bg: '#d1fae5', color: '#065f46' },
    lost:           { label: 'Lost',           dot: '#9ca3af', bg: '#f3f4f6', color: '#6b7280' },
};

const CAMPAIGN_STATUS_COLORS = {
    active:    { bg: '#dcfce7', color: '#16a34a' },
    paused:    { bg: '#fef9c3', color: '#ca8a04' },
    completed: { bg: '#f1f5f9', color: '#64748b' },
    draft:     { bg: '#f1f5f9', color: '#64748b' },
};

const AVATAR_COLORS = ['#FF5C00','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#14b8a6'];
const STATUSES_FILTER = ['new','contacted','interested','not_interested','no_answer','callback','converted','follow_up','lost'];

// WA_TEMPLATES is now loaded dynamically from the DB (wa_templates prop)

function StatusPill({ status }) {
    const s = STATUS_MAP[status] ?? { label: status, dot: '#94a3b8', bg: '#f1f5f9', color: '#64748b' };
    return (
        <span style={{ background: s.bg, color: s.color, fontSize: 11, fontWeight: 600, padding: '3px 9px', borderRadius: 99, display: 'inline-flex', alignItems: 'center', gap: 5, whiteSpace: 'nowrap' }}>
            <span style={{ width: 6, height: 6, borderRadius: '50%', background: s.dot, flexShrink: 0 }} />
            {s.label}
        </span>
    );
}

function KpiCard({ icon: Icon, label, value, sub, accentColor, topColor }) {
    return (
        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, height: '100%', boxShadow: CARD_SHADOW, overflow: 'hidden' }}>
            <div style={{ height: 3, background: topColor ?? accentColor ?? OR }} />
            <div style={{ padding: '14px 12px', textAlign: 'center' }}>
                <div style={{ width: 38, height: 38, borderRadius: 10, background: (accentColor ?? OR) + '18', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 10px' }}>
                    <Icon size={18} color={accentColor ?? OR} />
                </div>
                <div style={{ fontSize: 10, color: MUT, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 4 }}>{label}</div>
                <div style={{ fontSize: 24, fontWeight: 800, color: DK, lineHeight: 1 }}>{value}</div>
                {sub && <div style={{ fontSize: 10, color: MUT, marginTop: 4 }}>{sub}</div>}
            </div>
        </div>
    );
}

function StatusBar({ label, count, total, color }) {
    const pct = total > 0 ? Math.round((count / total) * 100) : 0;
    return (
        <div style={{ marginBottom: 10 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                <span style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: BDY }}>
                    <span style={{ width: 8, height: 8, borderRadius: '50%', background: color, flexShrink: 0 }} />
                    {label}
                </span>
                <span style={{ fontSize: 12, fontWeight: 700, color: DK }}>
                    {count} <span style={{ color: MUT, fontWeight: 400 }}>({pct}%)</span>
                </span>
            </div>
            <div style={{ height: 5, background: BOR, borderRadius: 99 }}>
                <div style={{ height: 5, width: `${pct}%`, background: color, borderRadius: 99, transition: 'width 0.6s ease' }} />
            </div>
        </div>
    );
}

function ContactAvatar({ name, idx }) {
    const bg = AVATAR_COLORS[idx % AVATAR_COLORS.length];
    return (
        <div style={{ width: 34, height: 34, borderRadius: '50%', background: bg, color: '#fff', fontWeight: 700, fontSize: 13, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
            {(name ?? '?')[0].toUpperCase()}
        </div>
    );
}

function Pagination({ links, lastPage, currentPage, total: totalContacts }) {
    const from = ((currentPage - 1) * 25) + 1;
    const to   = Math.min(currentPage * 25, totalContacts);
    return (
        <div style={{ padding: '12px 20px', borderTop: `1px solid ${BOR}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 8, background: '#fafafa' }}>
            <span style={{ fontSize: 12, color: BDY }}>
                Showing <strong>{from}–{to}</strong> of <strong>{(totalContacts ?? 0).toLocaleString()}</strong> contacts
            </span>
            {lastPage > 1 && (
                <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                    {links.map((link, i) => {
                        const isActive   = link.active;
                        const isDisabled = !link.url;
                        const style = {
                            minWidth: 32, height: 32, borderRadius: 7, border: '1.5px solid',
                            borderColor: isActive ? OR : BOR,
                            background: isActive ? OR : WH,
                            color: isActive ? '#fff' : isDisabled ? MUT : BDY,
                            fontWeight: isActive ? 700 : 500, fontSize: 12,
                            cursor: isDisabled ? 'default' : 'pointer',
                            display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                            textDecoration: 'none',
                        };
                        return link.url
                            ? <Link key={i} href={link.url} style={style} dangerouslySetInnerHTML={{ __html: link.label }} />
                            : <span key={i} style={style} dangerouslySetInnerHTML={{ __html: link.label }} />;
                    })}
                </div>
            )}
        </div>
    );
}

function PanelCard({ title, accent, children }) {
    return (
        <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: CARD_SHADOW, height: '100%', display: 'flex', flexDirection: 'column' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 16px', borderBottom: `1px solid ${BOR}`, flexShrink: 0 }}>
                <div style={{ width: 3, height: 22, background: accent ?? OR, borderRadius: 2 }} />
                <span style={{ color: DK, fontWeight: 700, fontSize: 13 }}>{title}</span>
            </div>
            <div style={{ padding: '14px 16px', flex: 1 }}>
                {children}
            </div>
        </div>
    );
}

export default function Show({ campaign, contacts, telecallers, stats, unassigned_count, assignment_summary, filters, wa_templates }) {
    const WA_TEMPLATES = (wa_templates && wa_templates.length > 0) ? wa_templates : [];
    const s = stats ?? {};
    const cStatusCfg = CAMPAIGN_STATUS_COLORS[campaign.status] ?? { bg: '#f1f5f9', color: '#64748b' };

    const total      = Number(s.total      ?? 0);
    const pending    = Number(s.pending    ?? 0);
    const called     = Number(s.called     ?? 0);
    const interested = Number(s.interested ?? 0);
    const converted  = Number(s.converted  ?? 0);
    const convRate   = total > 0 ? Math.round((converted / total) * 100) : 0;
    const notOther   = Math.max(0, total - pending - called);

    const [search, setSearch]           = useState(filters?.search     ?? '');
    const [status, setStatus]           = useState(filters?.status     ?? '');
    const [telecaller, setTelecaller]   = useState(filters?.telecaller ?? '');
    const [selectedTcs, setSelectedTcs] = useState([]);

    const [waTemplate, setWaTemplate] = useState(null);
    const [waBlasting, setWaBlasting] = useState(false);
    const [waBlastErr, setWaBlastErr] = useState(null);
    const [waProgress, setWaProgress] = useState({
        status:  campaign.wa_blast_status ?? 'idle',
        total,
        sent:    campaign.wa_sent_count   ?? 0,
        failed:  campaign.wa_failed_count ?? 0,
        pending: 0,
    });
    const pollRef = useRef(null);

    function startBlastPolling() {
        if (pollRef.current) return;
        pollRef.current = setInterval(async () => {
            try {
                const res  = await fetch(campaign.wa_status_url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.ok) {
                    setWaProgress({ status: data.status, total: data.total, sent: data.sent, failed: data.failed, pending: data.pending });
                    if (['completed', 'failed', 'idle'].includes(data.status)) {
                        clearInterval(pollRef.current); pollRef.current = null; setWaBlasting(false);
                    }
                }
            } catch (_) {}
        }, 3000);
    }

    useEffect(() => {
        if (WA_TEMPLATES.length > 0 && !waTemplate) setWaTemplate(WA_TEMPLATES[0]);
    }, [WA_TEMPLATES.length]);

    useEffect(() => {
        if (['sending', 'queued'].includes(campaign.wa_blast_status)) { setWaBlasting(true); startBlastPolling(); }
        return () => { if (pollRef.current) clearInterval(pollRef.current); };
    }, []);

    async function handleBlast(e) {
        e.preventDefault();
        if (!window.confirm(`Send WhatsApp blast to all ${total} contacts using "${waTemplate.value}" template?`)) return;
        setWaBlastErr(null); setWaBlasting(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res  = await fetch(campaign.wa_blast_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ template_name: waTemplate.value, template_language: waTemplate.language }),
            });
            const data = await res.json();
            if (data.ok) { setWaProgress(p => ({ ...p, status: 'queued', total: data.total, sent: 0, failed: 0, pending: data.total })); startBlastPolling(); }
            else { setWaBlastErr(data.error ?? 'Failed to start blast.'); setWaBlasting(false); }
        } catch { setWaBlastErr('Network error. Please try again.'); setWaBlasting(false); }
    }

    const waPct = waProgress.total > 0 ? Math.round(((waProgress.sent + waProgress.failed) / waProgress.total) * 100) : 0;

    function buildExportUrl(fmt) {
        const params = new URLSearchParams();
        if (filters?.search)     params.set('search',     filters.search);
        if (filters?.status)     params.set('status',     filters.status);
        if (filters?.telecaller) params.set('telecaller', String(filters.telecaller));
        const qs = params.toString();
        return `/manager/campaigns/${campaign.encrypted_id}/export/${fmt}${qs ? '?' + qs : ''}`;
    }

    function handleFilter(e) {
        e.preventDefault();
        const params = {};
        if (search)     params.search     = search;
        if (status)     params.status     = status;
        if (telecaller) params.telecaller = telecaller;
        router.get(`/manager/campaigns/${campaign.encrypted_id}`, params, { preserveState: false });
    }

    function resetFilter() {
        setSearch(''); setStatus(''); setTelecaller('');
        router.get(`/manager/campaigns/${campaign.encrypted_id}`, {}, { preserveState: false });
    }

    function handleStatusChange(e) {
        const newStatus = e.target.value;
        if (!window.confirm(`Change campaign status to "${newStatus}"?`)) return;
        router.patch(campaign.status_url, { status: newStatus }, { preserveScroll: true });
    }

    function handleDistribute(e) {
        e.preventDefault();
        if (selectedTcs.length === 0) return;
        if (!window.confirm(`Distribute ${unassigned_count} contacts among ${selectedTcs.length} telecaller(s)?`)) return;
        router.post(campaign.distribute_url, { telecaller_ids: selectedTcs }, { preserveScroll: true });
    }

    function toggleTc(id) { setSelectedTcs(p => p.includes(id) ? p.filter(x => x !== id) : [...p, id]); }

    const inp = { borderRadius: 8, borderColor: BOR, fontSize: 13 };

    const kpiCards = [
        { icon: LuUsers,      label: 'Total Contacts', value: total.toLocaleString(),       accentColor: OR,        topColor: OR },
        { icon: LuChartBar,   label: 'Pending',        value: pending.toLocaleString(),      accentColor: '#f59e0b', topColor: '#f59e0b', sub: total > 0 ? `${Math.round((pending/total)*100)}% of total` : null },
        { icon: LuPhone,      label: 'Contacted',      value: called.toLocaleString(),       accentColor: '#06b6d4', topColor: '#06b6d4', sub: total > 0 ? `${Math.round((called/total)*100)}% reached` : null },
        { icon: LuTrendingUp, label: 'Interested',     value: interested.toLocaleString(),   accentColor: '#10b981', topColor: '#10b981', sub: called > 0 ? `${Math.round((interested/called)*100)}% of contacted` : null },
        { icon: LuCheck,      label: 'Converted',      value: converted.toLocaleString(),    accentColor: '#8b5cf6', topColor: '#8b5cf6', sub: interested > 0 ? `${Math.round((converted/interested)*100)}% of interested` : null },
        { icon: LuTrendingUp, label: 'Conv. Rate',     value: `${convRate}%`,                accentColor: OR,        topColor: OR,        sub: 'Overall conversion' },
    ];

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap'); .camp-show * { font-family: 'Poppins', sans-serif !important; } .camp-row-hover:hover { background: #fff8f5 !important; }`}</style>
            <Head title={campaign.name} />

            <div className="camp-show">

                {/* ── Top Nav ── */}
                <div style={{ marginBottom: 20, display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 12 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                        <Link href="/manager/campaigns"
                            style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 8, padding: '6px 13px', fontSize: 13, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 5, textDecoration: 'none', boxShadow: CARD_SHADOW }}>
                            <LuChevronLeft size={16} /> Back to Campaigns
                        </Link>
                        <div>
                            <h2 style={{ fontSize: 18, fontWeight: 800, color: DK, margin: 0 }}>{campaign.name}</h2>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 3 }}>
                                <span style={{ background: cStatusCfg.bg, color: cStatusCfg.color, fontSize: 11, fontWeight: 600, padding: '2px 9px', borderRadius: 99 }}>
                                    {campaign.status.charAt(0).toUpperCase() + campaign.status.slice(1)}
                                </span>
                                <span style={{ color: MUT, fontSize: 12 }}>Created {campaign.created_at}</span>
                            </div>
                        </div>
                    </div>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <a href={campaign.import_url}
                            style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 8, padding: '6px 13px', fontSize: 13, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}>
                            <LuUpload size={14} /> Upload More
                        </a>
                        <select className="form-select form-select-sm" value={campaign.status} onChange={handleStatusChange}
                            style={{ width: 'auto', borderRadius: 8, fontWeight: 600, borderColor: BOR, fontSize: 13 }}>
                            {['active','paused','completed','draft'].map(st => (
                                <option key={st} value={st}>{st.charAt(0).toUpperCase() + st.slice(1)}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* ── KPI Cards ── */}
                <div className="row g-3 mb-4">
                    {kpiCards.map((k, i) => (
                        <div key={i} className="col-6 col-md-4 col-xl-2">
                            <KpiCard {...k} />
                        </div>
                    ))}
                </div>

                {/* ── Top Row: Status Dist / Distribute / Assignment ── */}
                <div className="row g-4 mb-4">

                    {/* Status Distribution */}
                    <div className="col-md-4">
                        <PanelCard title="Status Distribution" accent="#06b6d4">
                            <StatusBar label="Pending"    count={pending}   total={total} color="#f59e0b" />
                            <StatusBar label="Contacted"  count={Math.max(0, called - interested)} total={total} color="#06b6d4" />
                            <StatusBar label="Interested" count={interested} total={total} color="#10b981" />
                            <StatusBar label="Converted"  count={converted} total={total} color={OR} />
                            {notOther > 0 && <StatusBar label="Not Interested" count={notOther} total={total} color="#ef4444" />}
                        </PanelCard>
                    </div>

                    {/* Distribute Contacts */}
                    <div className="col-md-4">
                        <PanelCard title="Distribute Contacts" accent={OR}>
                            <div style={{ background: (unassigned_count ?? 0) > 0 ? '#fef9c3' : '#dcfce7', borderRadius: 10, padding: '9px 13px', marginBottom: 12, display: 'flex', alignItems: 'center', gap: 8 }}>
                                <span style={{ fontSize: 16, color: (unassigned_count ?? 0) > 0 ? '#d97706' : '#16a34a' }}>
                                    {(unassigned_count ?? 0) > 0 ? '⚠' : '✓'}
                                </span>
                                <span style={{ fontSize: 13, fontWeight: 600, color: (unassigned_count ?? 0) > 0 ? '#92400e' : '#166534' }}>
                                    {(unassigned_count ?? 0) > 0 ? `${(unassigned_count ?? 0).toLocaleString()} unassigned contact(s)` : 'All contacts assigned'}
                                </span>
                            </div>
                            {(unassigned_count ?? 0) > 0 && (
                                <form onSubmit={handleDistribute}>
                                    <label style={{ fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 8, display: 'block' }}>Select Telecallers</label>
                                    <div style={{ maxHeight: 150, overflowY: 'auto', marginBottom: 10 }}>
                                        {telecallers.map(tc => (
                                            <div className="form-check mb-1" key={tc.id} style={{ paddingLeft: 24 }}>
                                                <input className="form-check-input" type="checkbox"
                                                    id={`tc_${tc.id}`} checked={selectedTcs.includes(tc.id)}
                                                    onChange={() => toggleTc(tc.id)} />
                                                <label className="form-check-label" htmlFor={`tc_${tc.id}`} style={{ fontSize: 13 }}>{tc.name}</label>
                                            </div>
                                        ))}
                                        {telecallers.length === 0 && <p className="text-muted small">No telecallers found.</p>}
                                    </div>
                                    {telecallers.length > 0 && (
                                        <button type="submit" className="btn btn-sm w-100" disabled={selectedTcs.length === 0}
                                            style={{ background: selectedTcs.length > 0 ? OR : BOR, color: selectedTcs.length > 0 ? '#fff' : MUT, border: 'none', borderRadius: 8, fontWeight: 600, fontSize: 13 }}>
                                            <LuSettings2 size={13} style={{ marginRight: 5, verticalAlign: 'middle' }} />
                                            Auto-Distribute
                                        </button>
                                    )}
                                </form>
                            )}
                        </PanelCard>
                    </div>

                    {/* Assignment Summary */}
                    <div className="col-md-4">
                        <PanelCard title="Assignment Summary" accent="#8b5cf6">
                            {(assignment_summary ?? []).length === 0
                                ? <p className="text-muted small mb-0">No assignments yet.</p>
                                : (assignment_summary ?? []).map((row, i) => {
                                    const pct   = total > 0 ? Math.round((row.cnt / total) * 100) : 0;
                                    const color = AVATAR_COLORS[i % AVATAR_COLORS.length];
                                    const isU   = row.name === 'Unassigned';
                                    return (
                                        <div key={i} style={{ marginBottom: 10 }}>
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                                                    <div style={{ width: 24, height: 24, borderRadius: '50%', background: isU ? BOR : color, color: isU ? MUT : '#fff', fontWeight: 700, fontSize: 11, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                        {(row.name ?? '?')[0].toUpperCase()}
                                                    </div>
                                                    <span style={{ fontSize: 12, color: DK, fontWeight: 500 }}>{row.name}</span>
                                                </div>
                                                <span style={{ fontSize: 12, fontWeight: 700, color: DK }}>{row.cnt}</span>
                                            </div>
                                            <div style={{ height: 4, background: BOR, borderRadius: 99 }}>
                                                <div style={{ height: 4, width: `${pct}%`, background: isU ? MUT : color, borderRadius: 99 }} />
                                            </div>
                                        </div>
                                    );
                                })
                            }
                        </PanelCard>
                    </div>
                </div>

                {/* ── WhatsApp Blast (full width) ── */}
                <div style={{ background: WH, borderRadius: 16, border: '1px solid #bbf7d0', overflow: 'hidden', boxShadow: CARD_SHADOW, marginBottom: 24 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '12px 20px', borderBottom: '1px solid #bbf7d0', background: '#f0fdf4' }}>
                        <div style={{ width: 3, height: 22, background: '#25D366', borderRadius: 2 }} />
                        <LuMessageSquare size={16} color="#25D366" />
                        <span style={{ color: '#166534', fontWeight: 700, fontSize: 14 }}>WhatsApp Blast</span>
                        {campaign.wa_last_blast_at && (
                            <span style={{ marginLeft: 'auto', fontSize: 11, color: MUT }}>
                                Last blast: {campaign.wa_last_blast_at} ·{' '}
                                <span style={{ color: '#16a34a' }}>{campaign.wa_sent_count} sent</span>
                                {campaign.wa_failed_count > 0 && <span style={{ color: '#dc2626' }}> · {campaign.wa_failed_count} failed</span>}
                            </span>
                        )}
                    </div>
                    <div className="row g-0">
                        <div className="col-md-7" style={{ padding: '16px 20px', borderRight: '1px solid #bbf7d0' }}>
                            {['sending','queued'].includes(waProgress.status) && (
                                <div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 12, color: BDY, marginBottom: 6 }}>
                                        <span style={{ fontWeight: 600 }}>{waProgress.status === 'queued' ? 'Queued…' : `Sending ${waProgress.sent + waProgress.failed} / ${waProgress.total}`}</span>
                                        <span>{waPct}%</span>
                                    </div>
                                    <div style={{ height: 8, background: BOR, borderRadius: 99, marginBottom: 8 }}>
                                        <div style={{ height: 8, width: `${waPct}%`, background: '#25D366', borderRadius: 99, transition: 'width 0.5s ease' }} />
                                    </div>
                                    <div style={{ display: 'flex', gap: 16, fontSize: 11 }}>
                                        <span style={{ color: '#16a34a' }}>✓ {waProgress.sent} sent</span>
                                        {waProgress.failed > 0 && <span style={{ color: '#dc2626' }}>✗ {waProgress.failed} failed</span>}
                                        <span style={{ color: MUT }}>{waProgress.pending} pending</span>
                                    </div>
                                </div>
                            )}
                            {waProgress.status === 'completed' && (
                                <div style={{ background: '#f0fdf4', borderRadius: 10, padding: '12px 16px', border: '1px solid #bbf7d0' }}>
                                    <div style={{ fontWeight: 700, fontSize: 14, color: '#166534', marginBottom: 4 }}>Blast Completed</div>
                                    <div style={{ fontSize: 13, color: '#166534' }}>✓ {waProgress.sent} sent &nbsp;·&nbsp; ✗ {waProgress.failed} failed</div>
                                </div>
                            )}
                            {waProgress.status === 'failed' && (
                                <div style={{ background: '#fef2f2', borderRadius: 10, padding: '12px 16px', border: '1px solid #fecaca' }}>
                                    <div style={{ fontWeight: 700, fontSize: 14, color: '#dc2626' }}>Blast Failed</div>
                                    <div style={{ fontSize: 13, color: '#dc2626' }}>Check server logs for details.</div>
                                </div>
                            )}
                            {['idle',''].includes(waProgress.status ?? '') && !campaign.wa_last_blast_at && (
                                <div style={{ color: MUT, fontSize: 13 }}>No blast has been sent for this campaign yet.</div>
                            )}
                            {['idle','completed','failed'].includes(waProgress.status ?? '') && campaign.wa_last_blast_at && waProgress.status !== 'completed' && waProgress.status !== 'failed' && (
                                <div style={{ color: MUT, fontSize: 13 }}>Ready to send a new blast. Select a template and click send.</div>
                            )}
                            {waBlastErr && (
                                <div style={{ background: '#fef2f2', borderRadius: 8, padding: '8px 12px', marginTop: 10, border: '1px solid #fecaca', fontSize: 12, color: '#dc2626', display: 'flex', gap: 6, alignItems: 'flex-start' }}>
                                    <LuTriangleAlert size={14} style={{ flexShrink: 0, marginTop: 1 }} />
                                    {waBlastErr}
                                </div>
                            )}
                            {waBlasting && ['sending','queued'].includes(waProgress.status) && (
                                <div style={{ marginTop: 12, fontSize: 12, color: MUT, display: 'flex', alignItems: 'center', gap: 6 }}>
                                    <div className="spinner-border spinner-border-sm" role="status" style={{ color: '#25D366', width: 14, height: 14 }} />
                                    Processing messages…
                                </div>
                            )}
                        </div>
                        <div className="col-md-5" style={{ padding: '16px 20px' }}>
                            {!waBlasting ? (
                                WA_TEMPLATES.length === 0 ? (
                                    <div style={{ textAlign: 'center', padding: '20px 0', fontSize: 13, color: MUT }}>
                                        <div style={{ fontSize: 28, marginBottom: 8 }}>📋</div>
                                        No active templates found.<br />
                                        <span style={{ fontSize: 12 }}>Ask your admin to add WhatsApp templates.</span>
                                    </div>
                                ) : (
                                <form onSubmit={handleBlast}>
                                    <label style={{ fontSize: 12, fontWeight: 600, color: BDY, marginBottom: 6, display: 'block' }}>Template</label>
                                    <select className="form-select form-select-sm mb-3"
                                        value={waTemplate?.value ?? ''}
                                        onChange={e => setWaTemplate(WA_TEMPLATES.find(t => t.value === e.target.value))}
                                        style={{ borderRadius: 8, borderColor: BOR, fontSize: 12 }}>
                                        {WA_TEMPLATES.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
                                    </select>
                                    <div style={{ background: '#f0fdf4', borderRadius: 8, padding: '8px 12px', marginBottom: 14, fontSize: 12, color: '#166534' }}>
                                        Will send to <strong>{total.toLocaleString()} contacts</strong>. Each message uses 1 template from your 2000/day limit.
                                    </div>
                                    <button type="submit" disabled={total === 0 || !waTemplate}
                                        style={{ width: '100%', background: total > 0 ? '#25D366' : BOR, color: total > 0 ? '#fff' : MUT, border: 'none', borderRadius: 8, padding: '9px 0', fontWeight: 700, fontSize: 13, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, cursor: total > 0 ? 'pointer' : 'not-allowed' }}>
                                        <LuSend size={15} /> Send WhatsApp Blast
                                    </button>
                                </form>
                                )
                            ) : (
                                <div style={{ textAlign: 'center', paddingTop: 24, fontSize: 13, color: MUT }}>
                                    <div className="spinner-border spinner-border-sm mb-2" role="status" style={{ color: '#25D366' }} />
                                    <div>Blast in progress…</div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* ── Contacts Table (full width) ── */}
                <div style={{ background: WH, borderRadius: 16, border: `1px solid ${BOR}`, overflow: 'hidden', boxShadow: CARD_SHADOW, marginBottom: 24 }}>

                    {/* Table header */}
                    <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 10, borderBottom: `1px solid ${BOR}` }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 3, height: 26, background: OR, borderRadius: 2 }} />
                            <span style={{ color: DK, fontWeight: 700, fontSize: 15 }}>Contacts</span>
                            <span style={{ background: `${OR}18`, color: OR, fontSize: 11, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>
                                {(contacts.total ?? contacts.data.length).toLocaleString()}
                            </span>
                        </div>
                        <div style={{ display: 'flex', gap: 8 }}>
                            <a href={buildExportUrl('excel')} style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 7, padding: '5px 12px', fontSize: 12, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}>
                                <LuDownload size={13} /> Excel
                            </a>
                            <a href={buildExportUrl('pdf')} style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 7, padding: '5px 12px', fontSize: 12, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 5, textDecoration: 'none' }}>
                                <LuDownload size={13} /> PDF
                            </a>
                        </div>
                    </div>

                    {/* Filter bar */}
                    <div style={{ padding: '12px 20px', borderBottom: `1px solid ${BOR}`, background: '#fafafa' }}>
                        <form onSubmit={handleFilter} style={{ display: 'flex', gap: 10, flexWrap: 'wrap', alignItems: 'center' }}>
                            <input type="text" className="form-control form-control-sm"
                                placeholder="Search name, phone, email…"
                                value={search} onChange={e => setSearch(e.target.value)}
                                style={{ ...inp, minWidth: 200, flex: '1 1 200px', maxWidth: 280 }} />
                            <select className="form-select form-select-sm" value={status} onChange={e => setStatus(e.target.value)}
                                style={{ ...inp, minWidth: 140, flex: '0 0 160px' }}>
                                <option value="">All Statuses</option>
                                {STATUSES_FILTER.map(st => (
                                    <option key={st} value={st}>{STATUS_MAP[st]?.label ?? st}</option>
                                ))}
                            </select>
                            <select className="form-select form-select-sm" value={telecaller} onChange={e => setTelecaller(e.target.value)}
                                style={{ ...inp, minWidth: 140, flex: '0 0 160px' }}>
                                <option value="">All Telecallers</option>
                                {telecallers.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                            </select>
                            <div style={{ display: 'flex', gap: 6, flexShrink: 0 }}>
                                <button type="submit" style={{ background: OR, color: '#fff', border: 'none', borderRadius: 8, padding: '6px 16px', fontWeight: 600, fontSize: 13, display: 'flex', alignItems: 'center', gap: 5, cursor: 'pointer' }}>
                                    <LuFilter size={13} /> Filter
                                </button>
                                <button type="button" onClick={resetFilter}
                                    style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 8, padding: '6px 10px', fontSize: 13, cursor: 'pointer', display: 'flex', alignItems: 'center' }}>
                                    <LuX size={13} />
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Table */}
                    {contacts.data.length === 0 ? (
                        <div className="text-center py-5">
                            <LuUsers size={44} color={BOR} style={{ display: 'block', margin: '0 auto 10px' }} />
                            <p style={{ color: MUT, marginBottom: 0 }}>No contacts found.</p>
                        </div>
                    ) : (
                        <>
                            <div className="table-responsive">
                                <table className="table table-hover align-middle mb-0" style={{ fontSize: 13 }}>
                                    <thead>
                                        <tr style={{ background: '#F8F9FB' }}>
                                            {['#', 'Contact', 'Course / City', 'Status', 'Assigned', 'Calls', 'Follow-up', ''].map((h, i) => (
                                                <th key={i} style={{ padding: '10px 16px', color: '#6b7280', fontWeight: 600, fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.06em', borderBottom: `2px solid ${BOR}`, whiteSpace: 'nowrap', borderTop: 'none' }}>{h}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {contacts.data.map((contact, idx) => {
                                            const rowNum = ((contacts.current_page - 1) * 25) + idx + 1;
                                            return (
                                                <tr key={contact.id} className="camp-row-hover" style={{ borderBottom: `1px solid ${BOR}` }}>
                                                    <td style={{ padding: '10px 16px', color: MUT, fontSize: 12, width: 36 }}>{rowNum}</td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                            <ContactAvatar name={contact.name} idx={idx} />
                                                            <div>
                                                                <div style={{ fontWeight: 600, color: DK, fontSize: 13 }}>{contact.name}</div>
                                                                <div style={{ color: MUT, fontSize: 12 }}>{contact.phone}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        <div style={{ color: BDY, fontSize: 12 }}>{contact.course || '—'}</div>
                                                        {contact.city && <div style={{ color: MUT, fontSize: 11 }}>{contact.city}</div>}
                                                    </td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        <StatusPill status={contact.status} />
                                                    </td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        {contact.assigned_user
                                                            ? <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                                                <span style={{ width: 24, height: 24, borderRadius: '50%', background: `${OR}20`, color: OR, fontWeight: 700, fontSize: 10, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                                    {contact.assigned_user[0].toUpperCase()}
                                                                </span>
                                                                <span style={{ fontSize: 12, color: BDY }}>{contact.assigned_user}</span>
                                                              </span>
                                                            : <span style={{ color: MUT, fontSize: 12 }}>—</span>
                                                        }
                                                    </td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        <span style={{ background: Number(contact.call_count) > 0 ? `${OR}18` : BOR, color: Number(contact.call_count) > 0 ? OR : MUT, fontSize: 11, fontWeight: 700, padding: '2px 9px', borderRadius: 99 }}>
                                                            {Number(contact.call_count ?? 0)}
                                                        </span>
                                                    </td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        {contact.next_followup
                                                            ? <div>
                                                                <div style={{ color: BDY, fontSize: 12 }}>{contact.next_followup}</div>
                                                                {contact.followup_time && <div style={{ color: OR, fontSize: 11 }}>{contact.followup_time}</div>}
                                                              </div>
                                                            : <span style={{ color: MUT, fontSize: 12 }}>—</span>
                                                        }
                                                    </td>
                                                    <td style={{ padding: '10px 16px' }}>
                                                        <Link href={`/manager/campaigns/${campaign.encrypted_id}/contacts/${contact.encrypted_id}`}
                                                            style={{ background: WH, color: OR, border: `1.5px solid ${OR}40`, borderRadius: 7, padding: '5px 10px', display: 'inline-flex', alignItems: 'center', textDecoration: 'none' }}>
                                                            <LuExternalLink size={14} />
                                                        </Link>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination — always visible */}
                            <Pagination
                                links={contacts.links}
                                lastPage={contacts.last_page}
                                currentPage={contacts.current_page}
                                total={contacts.total ?? contacts.data.length}
                            />
                        </>
                    )}
                </div>


            </div>
        </>
    );
}
