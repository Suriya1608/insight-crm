import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import {
    LuArrowLeft, LuSend, LuUpload, LuDownload, LuSearch,
    LuFilter, LuCheck, LuX, LuCalendar, LuFileSpreadsheet,
} from 'react-icons/lu';

/* ── Design tokens ─────────────────────────────────────────── */
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

const CARD = {
    background: WH, border: `1px solid ${BOR}`,
    borderRadius: 14, boxShadow: '0 2px 8px rgba(0,0,0,0.04)', padding: '20px 22px',
};

function SourceBadge({ source }) {
    const styles = {
        Lead:     { background: '#FFF5EF', color: OR },
        Excel:    { background: '#DCFCE7', color: '#15803D' },
        Campaign: { background: '#FEF3C7', color: '#B45309' },
    };
    const s = styles[source] ?? styles.Campaign;
    return <span className="badge" style={{ ...s, fontWeight: 600, fontSize: 11 }}>{source}</span>;
}

export default function Create({ templates, courses, campaigns }) {
    const { errors } = usePage().props;

    const [data, setData] = useState({ name: '', description: '', template_id: '', scheduled_at: '' });
    const [allContacts, setAllContacts] = useState([]);
    const [selected, setSelected] = useState(new Set());
    const [loadingEmails, setLoadingEmails] = useState(true);
    const [emailLoadError, setEmailLoadError] = useState(false);
    const [sourceFilter, setSourceFilter] = useState('all');
    const [courseFilter, setCourseFilter] = useState('all');
    const [campaignFilter, setCampaignFilter] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [excelStatus, setExcelStatus] = useState(null);
    const [processing, setProcessing] = useState(false);

    const fileInputRef = useRef(null);

    function loadEmails(source, course, campaign) {
        setLoadingEmails(true);
        setEmailLoadError(false);
        const params = new URLSearchParams({
            source:      source ?? sourceFilter,
            course:      course ?? courseFilter,
            campaign_id: campaign ?? campaignFilter,
        });
        fetch(`/manager/email-campaigns/contacts?${params}`)
            .then(r => r.json())
            .then(contacts => { setAllContacts(contacts); setLoadingEmails(false); })
            .catch(() => { setEmailLoadError(true); setLoadingEmails(false); });
    }

    useEffect(() => { loadEmails(); }, []);

    function handleSourceChange(val) { setSourceFilter(val); loadEmails(val, courseFilter, campaignFilter); }
    function handleCourseChange(val) { setCourseFilter(val); loadEmails(sourceFilter, val, campaignFilter); }
    function handleCampaignChange(val) { setCampaignFilter(val); loadEmails(sourceFilter, courseFilter, val); }

    function handleTemplateChange(id) {
        setData(d => ({ ...d, template_id: id }));
        setSelectedTemplate(templates.find(t => String(t.id) === id) ?? null);
    }

    const filteredContacts = searchQuery
        ? allContacts.filter(c => {
            const q = searchQuery.toLowerCase();
            return c.email.toLowerCase().includes(q) ||
                   (c.name || '').toLowerCase().includes(q) ||
                   (c.course || '').toLowerCase().includes(q);
          })
        : allContacts;

    const allVisibleSelected = filteredContacts.length > 0 && filteredContacts.every(c => selected.has(c.email));

    function toggleAll(checked) {
        setSelected(prev => {
            const next = new Set(prev);
            filteredContacts.forEach(c => checked ? next.add(c.email) : next.delete(c.email));
            return next;
        });
    }

    function toggleRow(email, checked) {
        setSelected(prev => {
            const next = new Set(prev);
            checked ? next.add(email) : next.delete(email);
            return next;
        });
    }

    function importExcel(e) {
        const file = e.target.files[0];
        if (!file) return;
        setExcelStatus(null);
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const fd = new FormData();
        fd.append('file', file);
        if (csrfMeta) fd.append('_token', csrfMeta.content);

        fetch('/manager/email-campaigns/parse-excel', { method: 'POST', body: fd })
            .then(async r => {
                const json = await r.json();
                if (!r.ok) { setExcelStatus({ type: 'error', msg: json.error || 'Error parsing file.' }); return; }
                let added = 0;
                setAllContacts(prev => {
                    const existing = new Set(prev.map(c => c.email));
                    const merged = [...prev];
                    json.forEach(c => { if (!existing.has(c.email)) { merged.push(c); existing.add(c.email); added++; } });
                    return merged;
                });
                setSelected(prev => { const next = new Set(prev); json.forEach(c => next.add(c.email)); return next; });
                setExcelStatus({ type: 'ok', msg: `${json.length} found, ${added} new added` });
                e.target.value = '';
            })
            .catch(() => setExcelStatus({ type: 'error', msg: 'Upload failed.' }));
    }

    function submit(e) {
        e.preventDefault();
        const nameMap = {};
        allContacts.forEach(c => { nameMap[c.email] = c.name || ''; });
        const emails = [...selected];
        setProcessing(true);
        router.post('/manager/email-campaigns', {
            ...data,
            recipient_emails: emails,
            recipient_names: emails.map(em => nameMap[em] || ''),
        }, { onFinish: () => setProcessing(false) });
    }

    const inputStyle = {
        borderRadius: 8, border: `1.5px solid ${BOR}`, fontSize: 13,
        color: DK, padding: '8px 12px', width: '100%', outline: 'none',
        transition: 'border-color .18s, box-shadow .18s',
        fontFamily: 'Poppins, sans-serif',
    };

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');.ec-create{font-family:'Poppins',sans-serif;}.ec-create input:focus,.ec-create select:focus,.ec-create textarea:focus{border-color:${OR}!important;box-shadow:0 0 0 3px rgba(255,92,0,0.09)!important;outline:none;}.ec-create .table-hover tbody tr:hover td{background:rgba(255,92,0,0.04);}`}</style>
            <Head title="Create Email Campaign" />

            <div className="ec-create">
                {/* Header */}
                <div className="d-flex align-items-center gap-3 mb-4">
                    <Link href="/manager/email-campaigns"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 12px', borderRadius: 8, background: WH, border: `1px solid ${BOR}`, color: BDY, textDecoration: 'none', fontSize: 13, fontWeight: 600 }}>
                        <LuArrowLeft size={16} />Back
                    </Link>
                    <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            <div style={{ width: 4, height: 24, background: OR, borderRadius: 2 }} />
                            <h2 style={{ fontSize: 20, fontWeight: 700, margin: 0, color: DK }}>Create Email Campaign</h2>
                        </div>
                        <p style={{ color: MUT, margin: '2px 0 0 12px', fontSize: 13 }}>Select recipients, choose a template and send or schedule</p>
                    </div>
                </div>

                {errors && Object.keys(errors).length > 0 && (
                    <div className="alert alert-danger mb-3" style={{ borderRadius: 10, fontSize: 13 }}>
                        <ul className="mb-0 ps-3">
                            {Object.values(errors).map((msg, i) => <li key={i}>{msg}</li>)}
                        </ul>
                    </div>
                )}

                <form onSubmit={submit}>
                    <div className="row g-4">
                        {/* ── Left: Campaign details ── */}
                        <div className="col-lg-5">
                            <div style={CARD}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 18 }}>
                                    <div style={{ width: 3, height: 20, background: OR, borderRadius: 2 }} />
                                    <h6 style={{ fontWeight: 700, margin: 0, color: DK }}>Campaign Details</h6>
                                </div>

                                <div className="mb-3">
                                    <label style={{ fontSize: 12, fontWeight: 700, color: BDY, display: 'block', marginBottom: 6 }}>Campaign Name <span style={{ color: '#EF4444' }}>*</span></label>
                                    <input type="text" style={{ ...inputStyle, borderColor: errors?.name ? '#EF4444' : BOR }}
                                        placeholder="e.g. March Admission Drive"
                                        value={data.name}
                                        onChange={e => setData(d => ({ ...d, name: e.target.value }))} />
                                    {errors?.name && <div style={{ fontSize: 11, color: '#EF4444', marginTop: 4 }}>{errors.name}</div>}
                                </div>

                                <div className="mb-3">
                                    <label style={{ fontSize: 12, fontWeight: 700, color: BDY, display: 'block', marginBottom: 6 }}>Description</label>
                                    <textarea style={{ ...inputStyle, resize: 'vertical' }} rows={2} placeholder="Optional notes"
                                        value={data.description}
                                        onChange={e => setData(d => ({ ...d, description: e.target.value }))} />
                                </div>

                                <div className="mb-3">
                                    <label style={{ fontSize: 12, fontWeight: 700, color: BDY, display: 'block', marginBottom: 6 }}>Email Template <span style={{ color: '#EF4444' }}>*</span></label>
                                    <select style={{ ...inputStyle, borderColor: errors?.template_id ? '#EF4444' : BOR, cursor: 'pointer' }}
                                        value={data.template_id}
                                        onChange={e => handleTemplateChange(e.target.value)}>
                                        <option value="">— Choose a template —</option>
                                        {templates.map(tpl => <option key={tpl.id} value={tpl.id}>{tpl.name}</option>)}
                                    </select>
                                    {errors?.template_id && <div style={{ fontSize: 11, color: '#EF4444', marginTop: 4 }}>{errors.template_id}</div>}
                                </div>

                                {selectedTemplate && (
                                    <div className="mb-3">
                                        <div style={{ background: '#FFF5EF', borderRadius: 8, padding: '8px 12px', marginBottom: 8, fontSize: 12, border: `1px solid ${OR}20` }}>
                                            <strong>Subject:</strong> {selectedTemplate.subject}
                                        </div>
                                        <div style={{ border: `1px solid ${BOR}`, borderRadius: 8, padding: 10, maxHeight: 200, overflow: 'auto', fontSize: 12 }}
                                            dangerouslySetInnerHTML={{ __html: selectedTemplate.body }} />
                                    </div>
                                )}

                                <div className="mb-3">
                                    <label style={{ fontSize: 12, fontWeight: 700, color: BDY, marginBottom: 6, display: 'flex', alignItems: 'center', gap: 5 }}>
                                        <LuCalendar size={13} />Schedule
                                    </label>
                                    <input type="datetime-local" style={{ ...inputStyle, borderColor: errors?.scheduled_at ? '#EF4444' : BOR }}
                                        value={data.scheduled_at}
                                        onChange={e => setData(d => ({ ...d, scheduled_at: e.target.value }))} />
                                    <div style={{ fontSize: 11, color: MUT, marginTop: 4 }}>Leave blank to send immediately.</div>
                                    {errors?.scheduled_at && <div style={{ fontSize: 11, color: '#EF4444', marginTop: 4 }}>{errors.scheduled_at}</div>}
                                </div>
                            </div>
                        </div>

                        {/* ── Right: Recipients ── */}
                        <div className="col-lg-7">
                            <div style={CARD}>
                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16, flexWrap: 'wrap', gap: 8 }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                        <div style={{ width: 3, height: 20, background: OR, borderRadius: 2 }} />
                                        <h6 style={{ fontWeight: 700, margin: 0, color: DK }}>Select Recipients</h6>
                                    </div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                                        {[
                                            { val: sourceFilter, handler: handleSourceChange, opts: [['all', 'All Sources'], ['leads', 'Leads'], ['campaign_contacts', 'Campaign Contacts']] },
                                        ].map((f, fi) => (
                                            <select key={fi} style={{ height: 34, borderRadius: 8, border: `1px solid ${BOR}`, fontSize: 12, color: BDY, padding: '0 8px', cursor: 'pointer' }}
                                                value={f.val} onChange={e => f.handler(e.target.value)}>
                                                {f.opts.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                                            </select>
                                        ))}
                                        <select style={{ height: 34, borderRadius: 8, border: `1px solid ${BOR}`, fontSize: 12, color: BDY, padding: '0 8px', cursor: 'pointer' }}
                                            value={courseFilter} onChange={e => handleCourseChange(e.target.value)}>
                                            <option value="all">All Courses</option>
                                            {courses.map(c => <option key={c} value={c}>{c}</option>)}
                                        </select>
                                        <select style={{ height: 34, borderRadius: 8, border: `1px solid ${BOR}`, fontSize: 12, color: BDY, padding: '0 8px', cursor: 'pointer' }}
                                            value={campaignFilter} onChange={e => handleCampaignChange(e.target.value)}>
                                            <option value="all">All Campaigns</option>
                                            {campaigns.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                        </select>
                                        <span style={{ background: OR, color: '#fff', fontSize: 11, fontWeight: 700, padding: '3px 10px', borderRadius: 20 }}>{selected.size} selected</span>
                                    </div>
                                </div>

                                {errors?.recipient_emails && (
                                    <div style={{ background: '#FEF2F2', border: '1px solid #FECACA', borderRadius: 8, padding: '8px 12px', marginBottom: 12, fontSize: 13, color: '#DC2626' }}>
                                        {errors.recipient_emails}
                                    </div>
                                )}

                                {/* Excel import */}
                                <div style={{ marginBottom: 14, padding: '12px 14px', borderRadius: 10, background: '#F9FAFB', border: `1.5px dashed ${BOR}` }}>
                                    <div style={{ display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 10 }}>
                                        <LuFileSpreadsheet size={20} color={OR} />
                                        <div>
                                            <span style={{ fontWeight: 700, fontSize: 13, color: DK }}>Import from Excel / CSV</span>
                                            <span style={{ display: 'block', color: MUT, fontSize: 11 }}>
                                                Columns: <code>email</code> (required), <code>name</code> (optional). First row can be a header or raw emails.
                                            </span>
                                        </div>
                                        <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                                            <a href="/manager/email-campaigns/sample-excel"
                                                style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '6px 12px', borderRadius: 8, border: `1px solid ${BOR}`, background: WH, color: BDY, textDecoration: 'none', fontSize: 12, fontWeight: 600 }}>
                                                <LuDownload size={13} />Sample File
                                            </a>
                                            <input ref={fileInputRef} type="file" accept=".xlsx,.xls,.csv"
                                                style={{ maxWidth: 200, fontSize: 12 }}
                                                className="form-control form-control-sm"
                                                onChange={importExcel} />
                                            {excelStatus && (
                                                excelStatus.type === 'ok'
                                                    ? <span style={{ background: '#DCFCE7', color: '#15803D', fontSize: 12, fontWeight: 600, padding: '3px 9px', borderRadius: 20 }}>{excelStatus.msg}</span>
                                                    : <span style={{ color: '#DC2626', fontSize: 12 }}>{excelStatus.msg}</span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Search */}
                                <div style={{ position: 'relative', marginBottom: 10 }}>
                                    <LuSearch size={14} color={MUT} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)' }} />
                                    <input type="text"
                                        style={{ ...inputStyle, paddingLeft: 32 }}
                                        placeholder="Search emails..."
                                        value={searchQuery}
                                        onChange={e => setSearchQuery(e.target.value)} />
                                </div>

                                {/* Recipients table */}
                                <div style={{ maxHeight: 400, overflowY: 'auto', border: `1px solid ${BOR}`, borderRadius: 10, overflow: 'hidden' }}>
                                    <table className="table table-sm align-middle mb-0" style={{ fontSize: 13 }}>
                                        <thead style={{ position: 'sticky', top: 0, background: '#F4F6F8', zIndex: 1 }}>
                                            <tr>
                                                <th style={{ width: 36, padding: '8px 12px' }}>
                                                    <input type="checkbox" className="form-check-input"
                                                        checked={allVisibleSelected}
                                                        onChange={e => toggleAll(e.target.checked)}
                                                        style={{ accentColor: OR }} />
                                                </th>
                                                {['Email', 'Name', 'Course', 'Source'].map(h => (
                                                    <th key={h} style={{ padding: '8px 12px', fontSize: 11, fontWeight: 700, color: BDY, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{h}</th>
                                                ))}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {loadingEmails ? (
                                                <tr><td colSpan={5} style={{ textAlign: 'center', padding: '24px', color: MUT, fontSize: 13 }}>
                                                    <span className="spinner-border spinner-border-sm me-2" style={{ color: OR }}></span>Loading...
                                                </td></tr>
                                            ) : emailLoadError ? (
                                                <tr><td colSpan={5} style={{ textAlign: 'center', padding: '20px', color: '#DC2626', fontSize: 13 }}>Failed to load contacts.</td></tr>
                                            ) : filteredContacts.length === 0 ? (
                                                <tr><td colSpan={5} style={{ textAlign: 'center', padding: '24px', color: MUT, fontSize: 13 }}>No email addresses found.</td></tr>
                                            ) : filteredContacts.map(c => (
                                                <tr key={c.email} style={{ borderBottom: `1px solid ${BOR}` }}
                                                    onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,92,0,0.03)'}
                                                    onMouseLeave={e => e.currentTarget.style.background = ''}>
                                                    <td style={{ padding: '8px 12px' }}>
                                                        <input type="checkbox" className="form-check-input"
                                                            checked={selected.has(c.email)}
                                                            onChange={e => toggleRow(c.email, e.target.checked)}
                                                            style={{ accentColor: OR }} />
                                                    </td>
                                                    <td style={{ fontSize: 13, padding: '8px 12px' }}>{c.email}</td>
                                                    <td style={{ color: MUT, fontSize: 13, padding: '8px 12px' }}>{c.name || '—'}</td>
                                                    <td style={{ color: MUT, fontSize: 13, padding: '8px 12px' }}>{c.course || '—'}</td>
                                                    <td style={{ padding: '8px 12px' }}><SourceBadge source={c.source} /></td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div style={{ display: 'flex', gap: 10, marginTop: 16 }}>
                                <button type="submit"
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '10px 20px', borderRadius: 8, background: OR, color: '#fff', border: 'none', fontWeight: 700, fontSize: 13, cursor: processing ? 'not-allowed' : 'pointer', opacity: processing ? 0.7 : 1, fontFamily: 'Poppins, sans-serif' }}
                                    disabled={processing}>
                                    {processing ? (
                                        <><span className="spinner-border spinner-border-sm me-1" />&nbsp;Processing...</>
                                    ) : (
                                        <><LuSend size={15} />{data.scheduled_at ? 'Schedule Campaign' : 'Send Campaign'}</>
                                    )}
                                </button>
                                <Link href="/manager/email-campaigns"
                                    style={{ display: 'inline-flex', alignItems: 'center', padding: '10px 18px', borderRadius: 8, background: WH, border: `1px solid ${BOR}`, color: BDY, textDecoration: 'none', fontWeight: 600, fontSize: 13 }}>
                                    Cancel
                                </Link>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </>
    );
}
