import { Head, Link, useForm, router } from '@inertiajs/react';
import { LuChevronLeft, LuUpload, LuCheck, LuX, LuDownload } from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

function StatCard({ icon: Icon, iconColor, label, value, sub }) {
    return (
        <div style={{ background: WH, borderRadius: 12, border: `1px solid ${BOR}`, padding: '16px 14px', textAlign: 'center', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
            <div style={{ width: 40, height: 40, borderRadius: 10, background: (iconColor ?? OR) + '18', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 10px' }}>
                <Icon size={18} color={iconColor ?? OR} />
            </div>
            <div style={{ fontSize: 10, color: MUT, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: 4 }}>{label}</div>
            <div style={{ fontSize: 22, fontWeight: 800, color: DK }}>{value}</div>
            {sub && <div style={{ fontSize: 10, color: MUT, marginTop: 3 }}>{sub}</div>}
        </div>
    );
}

function rowStatus(row) {
    if (row.is_duplicate) return { bg: '#fee2e2', color: '#dc2626', label: `Duplicate (${row.dup_reason})` };
    if (row.is_invalid && row.invalid_reason === 'invalid_phone') return { bg: '#fef9c3', color: '#ca8a04', label: 'Invalid Phone' };
    if (row.is_invalid) return { bg: '#fef9c3', color: '#ca8a04', label: 'Invalid Email' };
    return { bg: '#dcfce7', color: '#16a34a', label: 'New' };
}

// Step 1: Upload form
function UploadStep({ campaign }) {
    const form = useForm({ file: null });

    function submit(e) {
        e.preventDefault();
        form.post(`/manager/campaigns/${campaign.encrypted_id}/import/preview`, {
            forceFormData: true,
        });
    }

    return (
        <div className="row justify-content-center">
            <div className="col-lg-7">
                <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, boxShadow: '0 2px 12px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
                    {/* Header */}
                    <div style={{ padding: '18px 24px', borderBottom: `1px solid ${BOR}` }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
                            <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                            <h3 style={{ fontSize: 16, fontWeight: 700, color: DK, margin: 0 }}>Upload Student Database</h3>
                        </div>
                        <p style={{ color: MUT, fontSize: 13, margin: 0, marginLeft: 13 }}>
                            Upload an Excel or CSV file. Duplicates, invalid phone numbers, and invalid emails will be automatically skipped.
                        </p>
                    </div>

                    <div style={{ padding: 24 }}>
                        {/* Info box */}
                        <div style={{ background: `${OR}0c`, border: `1px solid ${OR}30`, borderRadius: 10, padding: '14px 16px', marginBottom: 24, display: 'flex', gap: 10 }}>
                            <div style={{ color: OR, fontSize: 18, flexShrink: 0, marginTop: 1 }}>ℹ</div>
                            <div>
                                <strong style={{ fontSize: 13, color: DK }}>Expected column order:</strong>
                                <br />
                                <code style={{ fontSize: 12, background: `${OR}18`, color: OR, padding: '2px 6px', borderRadius: 4 }}>Name | Mobile Number | Email ID | Course | City</code>
                                <br />
                                <small style={{ color: MUT, fontSize: 11, lineHeight: 1.5, display: 'block', marginTop: 4 }}>
                                    Row 1 should be the header row. Only Name and Mobile Number are required.
                                    Numbers must have exactly 10 digits (with or without +91/0 prefix).
                                    Invalid phones and emails are skipped.
                                </small>
                            </div>
                        </div>

                        <form onSubmit={submit}>
                            <div style={{ marginBottom: 24 }}>
                                <label style={{ fontSize: 13, fontWeight: 600, color: BDY, marginBottom: 8, display: 'block' }}>
                                    Select File <span style={{ color: OR }}>*</span>
                                </label>
                                <input type="file" accept=".xlsx,.xls,.csv" required
                                    className={form.errors.file ? 'is-invalid' : ''}
                                    style={{ width: '100%', borderRadius: 8, border: `1.5px solid ${form.errors.file ? '#ef4444' : BOR}`, padding: '9px 12px', fontSize: 13, fontFamily: 'Poppins, sans-serif', color: DK }}
                                    onChange={e => form.setData('file', e.target.files[0])} />
                                {form.errors.file && <div style={{ color: '#ef4444', fontSize: 12, marginTop: 5 }}>{form.errors.file}</div>}
                            </div>

                            <button type="submit"
                                disabled={form.processing}
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 7, padding: '10px 22px', borderRadius: 9, background: form.processing ? MUT : OR, color: '#fff', border: 'none', fontWeight: 700, fontSize: 14, cursor: form.processing ? 'not-allowed' : 'pointer', boxShadow: form.processing ? 'none' : `0 4px 12px ${OR}40` }}>
                                {form.processing
                                    ? <><span className="spinner-border spinner-border-sm me-1" />Uploading…</>
                                    : <><LuUpload size={16} />Preview &amp; Validate</>
                                }
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}

// Step 2: Preview & confirm
function PreviewStep({ campaign, preview, preview_total, total, duplicates, invalid,
    invalid_phone, invalid_email, insertable, valid_rows }) {

    function confirmImport() {
        router.post(`/manager/campaigns/${campaign.encrypted_id}/import/store`, {
            contacts_data: JSON.stringify(valid_rows),
        });
    }

    const subInvalid = [
        invalid_phone > 0 ? `${invalid_phone} phone` : null,
        invalid_email > 0 ? `${invalid_email} email` : null,
    ].filter(Boolean).join(' · ');

    return (
        <>
            {/* Summary card */}
            <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, boxShadow: '0 2px 12px rgba(0,0,0,0.06)', marginBottom: 20, overflow: 'hidden' }}>
                <div style={{ padding: '16px 22px', borderBottom: `1px solid ${BOR}`, display: 'flex', alignItems: 'center', gap: 10 }}>
                    <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                    <h3 style={{ fontSize: 16, fontWeight: 700, color: DK, margin: 0 }}>Import Preview</h3>
                </div>
                <div style={{ padding: 22 }}>
                    <div className="row g-3 mb-4">
                        <div className="col-6 col-md-2">
                            <StatCard icon={LuUpload} iconColor="#FF5C00" label="Total in File" value={total} />
                        </div>
                        <div className="col-6 col-md-2">
                            <StatCard icon={LuCheck} iconColor="#16a34a" label="Will Be Inserted" value={insertable} />
                        </div>
                        <div className="col-6 col-md-3">
                            <StatCard icon={LuX} iconColor="#dc2626" label="Duplicates" value={duplicates} />
                        </div>
                        <div className="col-6 col-md-3">
                            <StatCard icon={LuX} iconColor="#f59e0b" label="Invalid" value={invalid} sub={subInvalid || null} />
                        </div>
                        <div className="col-6 col-md-2">
                            {insertable > 0
                                ? <StatCard icon={LuCheck} iconColor="#16a34a" label="Status" value="Ready" />
                                : <StatCard icon={LuX} iconColor="#dc2626" label="Status" value="Nothing to Import" />
                            }
                        </div>
                    </div>

                    <div style={{ display: 'flex', gap: 10, alignItems: 'center', flexWrap: 'wrap' }}>
                        {insertable > 0 ? (
                            <button
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 7, padding: '10px 22px', borderRadius: 9, background: OR, color: '#fff', border: 'none', fontWeight: 700, fontSize: 14, cursor: 'pointer', boxShadow: `0 4px 12px ${OR}40` }}
                                onClick={confirmImport}>
                                <LuDownload size={16} />
                                Confirm &amp; Import {insertable} Record(s)
                            </button>
                        ) : (
                            <div style={{ background: '#fef9c3', border: '1px solid #fcd34d', borderRadius: 10, padding: '10px 16px', fontSize: 13, color: '#92400e', fontWeight: 500 }}>
                                No valid records to import. All records are either duplicates or have invalid data.
                            </div>
                        )}
                        <Link href={`/manager/campaigns/${campaign.encrypted_id}/import`}
                            style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '10px 18px', borderRadius: 9, background: WH, color: BDY, border: `1.5px solid ${BOR}`, fontWeight: 600, fontSize: 13, textDecoration: 'none' }}>
                            <LuUpload size={14} />
                            Upload a different file
                        </Link>
                    </div>
                </div>
            </div>

            {/* Preview table */}
            <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, boxShadow: '0 2px 12px rgba(0,0,0,0.06)', overflow: 'hidden' }}>
                <div style={{ padding: '16px 22px', borderBottom: `1px solid ${BOR}` }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
                        <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                        <h3 style={{ fontSize: 16, fontWeight: 700, color: DK, margin: 0 }}>Record Preview <span style={{ fontSize: 13, fontWeight: 500, color: MUT }}>(first 100 rows shown)</span></h3>
                    </div>
                    <div style={{ display: 'flex', gap: 8, marginLeft: 13, flexWrap: 'wrap' }}>
                        {[
                            { bg: '#fee2e2', color: '#dc2626', label: 'Duplicate' },
                            { bg: '#fef9c3', color: '#ca8a04', label: 'Invalid Phone' },
                            { bg: '#fef9c3', color: '#ca8a04', label: 'Invalid Email' },
                        ].map(b => (
                            <span key={b.label} style={{ background: b.bg, color: b.color, fontSize: 11, fontWeight: 600, padding: '2px 8px', borderRadius: 6 }}>{b.label}</span>
                        ))}
                        <span style={{ fontSize: 12, color: MUT }}>rows will be skipped.</span>
                    </div>
                </div>
                <div className="table-responsive">
                    <table className="table table-sm align-middle mb-0" style={{ fontSize: 13 }}>
                        <thead>
                            <tr style={{ background: '#F4F6F8', position: 'sticky', top: 0, zIndex: 1 }}>
                                {['#','Name','Mobile (stored as)','Email','Course','City','Status'].map((h, i) => (
                                    <th key={i} style={{ padding: '10px 14px', color: BDY, fontWeight: 700, fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.05em', borderBottom: `2px solid ${BOR}`, whiteSpace: 'nowrap' }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {preview.map((row, i) => {
                                const st = rowStatus(row);
                                return (
                                    <tr key={i} style={{
                                        borderBottom: `1px solid ${BOR}`,
                                        background: row.is_duplicate ? '#fff5f5' : row.is_invalid ? '#fffbeb' : WH,
                                    }}>
                                        <td style={{ padding: '8px 14px', color: MUT, fontSize: 12 }}>{i + 1}</td>
                                        <td style={{ padding: '8px 14px', fontWeight: 600, color: DK }}>{row.name}</td>
                                        <td style={{ padding: '8px 14px' }}><code style={{ fontSize: 12, background: BOR, padding: '1px 6px', borderRadius: 4 }}>{row.phone}</code></td>
                                        <td style={{ padding: '8px 14px', color: BDY }}>{row.email || '—'}</td>
                                        <td style={{ padding: '8px 14px', color: BDY }}>{row.course || '—'}</td>
                                        <td style={{ padding: '8px 14px', color: BDY }}>{row.city || '—'}</td>
                                        <td style={{ padding: '8px 14px' }}>
                                            <span style={{ background: st.bg, color: st.color, fontSize: 11, fontWeight: 600, padding: '2px 9px', borderRadius: 99 }}>{st.label}</span>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                {preview_total > 100 && (
                    <div style={{ padding: '10px 22px', fontSize: 12, color: MUT }}>
                        Showing 100 of {preview_total} rows. All valid records will be imported.
                    </div>
                )}
            </div>
        </>
    );
}

// Main
export default function Import({ campaign, step, ...props }) {
    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap'); .camp-import * { font-family: 'Poppins', sans-serif !important; }`}</style>
            <Head title={`Import Contacts — ${campaign.name}`} />

            <div className="camp-import">
                {/* Page header */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 24 }}>
                    <Link href={campaign.show_url}
                        style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 8, padding: '7px 14px', fontSize: 13, fontWeight: 600, display: 'inline-flex', alignItems: 'center', gap: 6, textDecoration: 'none', boxShadow: '0 1px 4px rgba(0,0,0,0.06)' }}>
                        <LuChevronLeft size={16} />
                        Back to Campaign
                    </Link>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 3, height: 32, background: OR, borderRadius: 2 }} />
                        <div>
                            <h2 style={{ fontSize: 18, fontWeight: 800, color: DK, margin: 0 }}>Import Contacts</h2>
                            <p style={{ color: MUT, fontSize: 13, margin: '2px 0 0' }}>{campaign.name}</p>
                        </div>
                    </div>
                </div>

                {step === 'upload'
                    ? <UploadStep campaign={campaign} />
                    : <PreviewStep campaign={campaign} {...props} />
                }
            </div>
        </>
    );
}
