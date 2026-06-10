import { Head, Link, useForm } from '@inertiajs/react';
import { LuPlus, LuChevronLeft, LuUser, LuPhone, LuMail, LuCalendar, LuMapPin, LuFileText } from 'react-icons/lu';

// ─── Design tokens ────────────────────────────────────────────────────────────
const OR  = '#FF5C00';
const DK  = '#1D1D1D';
const WH  = '#FEFEFE';
const MUT = '#9CA3AF';
const BOR = '#F0F0F0';
const BDY = '#374151';

const MANUAL_CATEGORIES = [
    { value: 'social_media', label: 'Social Media', detailPlaceholder: 'e.g. Facebook, Instagram, LinkedIn' },
    { value: 'newspaper',    label: 'Newspaper',    detailPlaceholder: 'e.g. The Hindu, Times of India' },
    { value: 'tv',           label: 'TV Advertisement', detailPlaceholder: 'e.g. Sun TV, Vijay TV' },
    { value: 'referral',     label: 'Referral',     detailPlaceholder: 'Referrer name & contact (e.g. John Doe – 9876543210)' },
    { value: 'walk_in',      label: 'Walk-in / Self', detailPlaceholder: null },
    { value: 'other',        label: 'Other',        detailPlaceholder: 'Please specify' },
];

// ─── Shared form field styles ─────────────────────────────────────────────────
const inputStyle = (hasError) => ({
    width: '100%',
    padding: '9px 12px',
    borderRadius: 10,
    border: `1.5px solid ${hasError ? '#ef4444' : BOR}`,
    fontSize: 13,
    outline: 'none',
    background: WH,
    color: DK,
    fontFamily: 'inherit',
    boxSizing: 'border-box',
    transition: 'border-color .15s',
});

const labelStyle = {
    display: 'block',
    fontSize: 12,
    fontWeight: 700,
    color: BDY,
    marginBottom: 5,
};

function SectionDivider({ icon: Icon, title }) {
    return (
        <div className="col-12">
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '8px 0 4px' }}>
                <div style={{ width: 3, height: 22, background: OR, borderRadius: 2 }} />
                {Icon && <Icon size={16} style={{ color: OR }} />}
                <span style={{ fontSize: 12, fontWeight: 700, color: BDY, textTransform: 'uppercase', letterSpacing: '.6px' }}>
                    {title}
                </span>
            </div>
            <div style={{ height: 1, background: BOR, margin: '8px 0' }} />
        </div>
    );
}

export default function Create({ courses, academic_years, store_url }) {
    const activeYear = academic_years?.find(y => y.is_active);

    const form = useForm({
        name:             '',
        phone:            '',
        email:            '',
        gender:           '',
        dob:              '',
        address:          '',
        city:             '',
        district:         '',
        state:            '',
        pincode:          '',
        course_id:        '',
        academic_year_id: activeYear ? String(activeYear.id) : '',
        source_category:  '',
        source_detail:    '',
    });

    function submit(e) {
        e.preventDefault();
        form.post(store_url);
    }

    const selectedCat     = MANUAL_CATEGORIES.find(c => c.value === form.data.source_category);
    const showDetailField = selectedCat && selectedCat.detailPlaceholder !== null;

    return (
        <>
            <Head title="Add Lead" />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
                .mgr-cr-wrap { font-family: 'Poppins', sans-serif; }
                .mgr-cr-wrap .form-control, .mgr-cr-wrap .form-select, .mgr-cr-wrap .input-group-text {
                    font-family: 'Poppins', sans-serif;
                    border-radius: 10px;
                    border: 1.5px solid ${BOR};
                    font-size: 13px;
                    color: ${DK};
                }
                .mgr-cr-wrap .form-control:focus, .mgr-cr-wrap .form-select:focus {
                    border-color: ${OR};
                    box-shadow: 0 0 0 3px ${OR}18;
                }
                .mgr-cr-wrap .form-control.is-invalid, .mgr-cr-wrap .form-select.is-invalid {
                    border-color: #ef4444;
                }
                .mgr-cr-wrap .form-label {
                    font-size: 12px;
                    font-weight: 700;
                    color: ${BDY};
                    margin-bottom: 5px;
                }
                .mgr-cr-wrap .form-text {
                    font-size: 11.5px;
                    color: ${MUT};
                }
                .mgr-cr-wrap .input-group-text {
                    background: #f8fafc;
                    color: ${MUT};
                    border-right: none;
                }
                .mgr-cr-wrap .input-group .form-control {
                    border-left: none;
                }
            `}</style>

            <div className="mgr-cr-wrap">
                {/* ── Page header ─────────────────────────── */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24 }}>
                    <Link href="/manager/leads"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '7px 14px', borderRadius: 9, fontSize: 13, fontWeight: 600, background: WH, border: `1.5px solid ${BOR}`, color: BDY, textDecoration: 'none' }}>
                        <LuChevronLeft size={16} /> Back to Leads
                    </Link>
                    <div style={{ width: 3, height: 28, background: OR, borderRadius: 2 }} />
                    <LuPlus size={20} style={{ color: OR }} />
                    <h2 style={{ margin: 0, fontSize: 20, fontWeight: 800, color: DK }}>Add Lead</h2>
                </div>

                {/* ── Form card ───────────────────────────── */}
                <div style={{ background: WH, border: `1px solid ${BOR}`, borderRadius: 16, padding: '28px', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' }}>
                    <form onSubmit={submit}>
                        <div className="row g-3">

                            {/* ── Contact ── */}
                            <SectionDivider icon={LuUser} title="Contact Information" />

                            <div className="col-md-6">
                                <label className="form-label">Name *</label>
                                <input type="text" className={`form-control${form.errors.name ? ' is-invalid' : ''}`}
                                    value={form.data.name} onChange={e => form.setData('name', e.target.value)} required />
                                {form.errors.name && <div className="invalid-feedback">{form.errors.name}</div>}
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">Phone *</label>
                                <div className="input-group">
                                    <span className="input-group-text">+91</span>
                                    <input type="tel" className={`form-control${form.errors.phone ? ' is-invalid' : ''}`}
                                        placeholder="10-digit mobile number" maxLength={10} pattern="[0-9]{10}"
                                        inputMode="numeric" required
                                        value={form.data.phone} onChange={e => form.setData('phone', e.target.value)} />
                                    {form.errors.phone && <div className="invalid-feedback">{form.errors.phone}</div>}
                                </div>
                                <div className="form-text">Enter 10-digit number — +91 is added automatically.</div>
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">Email</label>
                                <input type="email" className={`form-control${form.errors.email ? ' is-invalid' : ''}`}
                                    value={form.data.email} onChange={e => form.setData('email', e.target.value)} />
                                {form.errors.email && <div className="invalid-feedback">{form.errors.email}</div>}
                            </div>

                            {/* ── Demographics ── */}
                            <SectionDivider icon={LuCalendar} title="Demographics" />

                            <div className="col-md-4">
                                <label className="form-label">Gender</label>
                                <select className="form-select" value={form.data.gender}
                                    onChange={e => form.setData('gender', e.target.value)}>
                                    <option value="">— Select —</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                {form.errors.gender && <div className="invalid-feedback d-block">{form.errors.gender}</div>}
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Date of Birth</label>
                                <input type="date" className={`form-control${form.errors.dob ? ' is-invalid' : ''}`}
                                    value={form.data.dob}
                                    max={new Date().toISOString().split('T')[0]}
                                    onChange={e => form.setData('dob', e.target.value)} />
                                {form.errors.dob && <div className="invalid-feedback">{form.errors.dob}</div>}
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">Pincode</label>
                                <input type="text" className={`form-control${form.errors.pincode ? ' is-invalid' : ''}`}
                                    placeholder="e.g. 600001" maxLength={10}
                                    value={form.data.pincode}
                                    onChange={e => form.setData('pincode', e.target.value)} />
                                {form.errors.pincode && <div className="invalid-feedback">{form.errors.pincode}</div>}
                            </div>

                            {/* ── Location ── */}
                            <SectionDivider icon={LuMapPin} title="Location" />

                            <div className="col-md-4">
                                <label className="form-label">City</label>
                                <input type="text" className={`form-control${form.errors.city ? ' is-invalid' : ''}`}
                                    placeholder="e.g. Chennai"
                                    value={form.data.city}
                                    onChange={e => form.setData('city', e.target.value)} />
                                {form.errors.city && <div className="invalid-feedback">{form.errors.city}</div>}
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">District</label>
                                <input type="text" className={`form-control${form.errors.district ? ' is-invalid' : ''}`}
                                    placeholder="e.g. Chennai"
                                    value={form.data.district}
                                    onChange={e => form.setData('district', e.target.value)} />
                                {form.errors.district && <div className="invalid-feedback">{form.errors.district}</div>}
                            </div>

                            <div className="col-md-4">
                                <label className="form-label">State</label>
                                <input type="text" className={`form-control${form.errors.state ? ' is-invalid' : ''}`}
                                    placeholder="e.g. Tamil Nadu"
                                    value={form.data.state}
                                    onChange={e => form.setData('state', e.target.value)} />
                                {form.errors.state && <div className="invalid-feedback">{form.errors.state}</div>}
                            </div>

                            <div className="col-12">
                                <label className="form-label">Address</label>
                                <textarea className={`form-control${form.errors.address ? ' is-invalid' : ''}`}
                                    rows={2} placeholder="Street address, landmark…"
                                    value={form.data.address}
                                    onChange={e => form.setData('address', e.target.value)} />
                                {form.errors.address && <div className="invalid-feedback">{form.errors.address}</div>}
                            </div>

                            {/* ── Enrolment ── */}
                            <SectionDivider icon={LuFileText} title="Enrolment Details" />

                            <div className="col-md-6">
                                <label className="form-label">Academic Year</label>
                                <select className="form-select" value={form.data.academic_year_id}
                                    onChange={e => form.setData('academic_year_id', e.target.value)}>
                                    <option value="">— Select Year —</option>
                                    {(academic_years || []).map(y => (
                                        <option key={y.id} value={y.id}>
                                            {y.name}{y.is_active ? ' (Current)' : ''}
                                        </option>
                                    ))}
                                </select>
                                {activeYear && !form.data.academic_year_id && (
                                    <div className="form-text">Current year: <strong>{activeYear.name}</strong></div>
                                )}
                            </div>

                            <div className="col-md-6">
                                <label className="form-label">Course</label>
                                <select className="form-select" value={form.data.course_id}
                                    onChange={e => form.setData('course_id', e.target.value)}>
                                    <option value="">— Select Course —</option>
                                    {courses.map(c => (
                                        <option key={c.id} value={c.id}>{c.name}</option>
                                    ))}
                                </select>
                            </div>

                            {/* ── Source ── */}
                            <SectionDivider title="Lead Source" />

                            <div className="col-md-6">
                                <label className="form-label">Source Category</label>
                                <select className={`form-select${form.errors.source_category ? ' is-invalid' : ''}`}
                                    value={form.data.source_category}
                                    onChange={e => {
                                        form.setData('source_category', e.target.value);
                                        form.setData('source_detail', '');
                                    }}>
                                    <option value="">— Select Source —</option>
                                    {MANUAL_CATEGORIES.map(c => (
                                        <option key={c.value} value={c.value}>{c.label}</option>
                                    ))}
                                </select>
                                {form.errors.source_category && <div className="invalid-feedback">{form.errors.source_category}</div>}
                            </div>

                            {showDetailField && (
                                <div className="col-md-6">
                                    <label className="form-label">
                                        {form.data.source_category === 'referral' ? 'Referrer Details' : 'Specify'}
                                    </label>
                                    <input type="text"
                                        className={`form-control${form.errors.source_detail ? ' is-invalid' : ''}`}
                                        placeholder={selectedCat.detailPlaceholder}
                                        value={form.data.source_detail}
                                        onChange={e => form.setData('source_detail', e.target.value)} />
                                    {form.errors.source_detail && <div className="invalid-feedback">{form.errors.source_detail}</div>}
                                </div>
                            )}
                        </div>

                        {/* ── Submit ── */}
                        <div style={{ marginTop: 28, display: 'flex', gap: 12, alignItems: 'center' }}>
                            <button type="submit" disabled={form.processing}
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 7,
                                    padding: '10px 24px', borderRadius: 10, border: 'none',
                                    background: OR, color: '#fff', fontSize: 14, fontWeight: 700,
                                    cursor: form.processing ? 'not-allowed' : 'pointer',
                                    opacity: form.processing ? .7 : 1,
                                    fontFamily: 'inherit',
                                }}>
                                <LuPlus size={16} />
                                {form.processing ? 'Saving…' : 'Save Lead'}
                            </button>
                            <Link href="/manager/leads"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '10px 20px', borderRadius: 10, fontSize: 13, fontWeight: 600, background: WH, border: `1.5px solid ${BOR}`, color: BDY, textDecoration: 'none' }}>
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
