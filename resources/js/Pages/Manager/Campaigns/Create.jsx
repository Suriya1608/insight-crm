import { Head, Link, useForm } from '@inertiajs/react';
import { LuChevronLeft, LuPlus, LuX } from 'react-icons/lu';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

export default function Create({ academicYears = [] }) {
    const form = useForm({ name: '', description: '', academic_year_id: '' });

    const activeYear = academicYears.find(y => y.is_active);

    function submit(e) {
        e.preventDefault();
        form.post('/manager/campaigns');
    }

    const inputStyle = {
        borderRadius: 8,
        borderColor: BOR,
        fontSize: 14,
        fontFamily: 'Poppins, sans-serif',
        color: DK,
        padding: '9px 14px',
    };

    const labelStyle = {
        fontSize: 13,
        fontWeight: 600,
        color: BDY,
        marginBottom: 6,
        display: 'block',
    };

    return (
        <>
            <style>{`@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap'); .camp-create * { font-family: 'Poppins', sans-serif !important; }`}</style>
            <Head title="New Campaign" />

            <div className="camp-create">
                {/* Page header */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 28 }}>
                    <Link href="/manager/campaigns"
                        style={{ background: WH, color: BDY, border: `1.5px solid ${BOR}`, borderRadius: 8, padding: '7px 14px', fontSize: 13, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 6, textDecoration: 'none', boxShadow: '0 1px 4px rgba(0,0,0,0.06)' }}>
                        <LuChevronLeft size={16} />
                        Back to Campaigns
                    </Link>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 3, height: 32, background: OR, borderRadius: 2 }} />
                        <div>
                            <h2 style={{ fontSize: 20, fontWeight: 800, color: DK, margin: 0 }}>New Campaign</h2>
                            <p style={{ color: MUT, fontSize: 13, margin: '2px 0 0' }}>Create a new outreach campaign</p>
                        </div>
                    </div>
                </div>

                {/* Form card */}
                <div style={{ background: WH, borderRadius: 14, border: `1px solid ${BOR}`, boxShadow: '0 2px 12px rgba(0,0,0,0.06)', maxWidth: 560, overflow: 'hidden' }}>
                    {/* Card header accent */}
                    <div style={{ height: 4, background: OR, borderRadius: '14px 14px 0 0' }} />
                    <div style={{ padding: 28 }}>
                        <form onSubmit={submit}>
                            <div style={{ marginBottom: 20 }}>
                                <label style={labelStyle}>Campaign Name <span style={{ color: OR }}>*</span></label>
                                <input type="text"
                                    className={form.errors.name ? 'is-invalid' : ''}
                                    style={{ ...inputStyle, width: '100%', border: `1.5px solid ${form.errors.name ? '#ef4444' : BOR}`, outline: 'none' }}
                                    value={form.data.name}
                                    onChange={e => form.setData('name', e.target.value)}
                                    required />
                                {form.errors.name && <div style={{ color: '#ef4444', fontSize: 12, marginTop: 5 }}>{form.errors.name}</div>}
                            </div>

                            <div style={{ marginBottom: 20 }}>
                                <label style={labelStyle}>Academic Year</label>
                                <select
                                    className={form.errors.academic_year_id ? 'is-invalid' : ''}
                                    style={{ ...inputStyle, width: '100%', border: `1.5px solid ${form.errors.academic_year_id ? '#ef4444' : BOR}`, appearance: 'auto' }}
                                    value={form.data.academic_year_id}
                                    onChange={e => form.setData('academic_year_id', e.target.value)}
                                >
                                    <option value="">— Select Academic Year —</option>
                                    {academicYears.map(y => (
                                        <option key={y.id} value={y.id}>
                                            {y.name}{y.is_active ? ' (Active)' : ''}
                                        </option>
                                    ))}
                                </select>
                                {form.errors.academic_year_id && <div style={{ color: '#ef4444', fontSize: 12, marginTop: 5 }}>{form.errors.academic_year_id}</div>}
                                {activeYear && !form.data.academic_year_id && (
                                    <div style={{ fontSize: 12, color: MUT, marginTop: 5 }}>
                                        Active year: <strong style={{ color: BDY }}>{activeYear.name}</strong>
                                    </div>
                                )}
                            </div>

                            <div style={{ marginBottom: 28 }}>
                                <label style={labelStyle}>Description</label>
                                <textarea
                                    rows={3}
                                    style={{ ...inputStyle, width: '100%', border: `1.5px solid ${BOR}`, resize: 'vertical' }}
                                    value={form.data.description}
                                    onChange={e => form.setData('description', e.target.value)} />
                                {form.errors.description && <div style={{ color: '#ef4444', fontSize: 12, marginTop: 5 }}>{form.errors.description}</div>}
                            </div>

                            <div style={{ display: 'flex', gap: 10 }}>
                                <button type="submit"
                                    disabled={form.processing}
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '10px 22px', borderRadius: 9, background: form.processing ? MUT : OR, color: '#fff', border: 'none', fontWeight: 700, fontSize: 14, cursor: form.processing ? 'not-allowed' : 'pointer', boxShadow: form.processing ? 'none' : `0 4px 12px ${OR}40` }}>
                                    <LuPlus size={16} />
                                    {form.processing ? 'Creating…' : 'Create Campaign'}
                                </button>
                                <Link href="/manager/campaigns"
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '10px 20px', borderRadius: 9, background: WH, color: BDY, border: `1.5px solid ${BOR}`, fontWeight: 600, fontSize: 14, textDecoration: 'none' }}>
                                    <LuX size={15} />
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
