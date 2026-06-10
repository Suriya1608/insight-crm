import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

function checkRules(val) {
    return {
        upper:   /[A-Z]/.test(val),
        lower:   /[a-z]/.test(val),
        number:  /\d/.test(val),
        special: /[@$!%*?&]/.test(val),
        length:  val.length >= 8,
    };
}

function PasswordToggle({ show, onToggle }) {
    return (
        <button type="button" className="btn btn-outline-secondary" tabIndex={-1} onClick={onToggle}>
            <span className="material-icons" style={{ fontSize: 18, verticalAlign: 'middle' }}>
                {show ? 'visibility_off' : 'visibility'}
            </span>
        </button>
    );
}

export default function ChangePassword({ update_url }) {
    const { data, setData, post, processing, errors } = useForm({
        current_password:      '',
        password:              '',
        password_confirmation: '',
    });

    const [showCurrent, setShowCurrent] = useState(false);
    const [showNew,     setShowNew]     = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);

    const rules   = checkRules(data.password);
    const passed  = Object.values(rules).filter(Boolean).length;
    const showMeter = data.password.length > 0;

    const strengthPct   = (passed / 5) * 100;
    const strengthColor = passed <= 2 ? '#ef4444' : passed <= 3 ? '#f59e0b' : passed <= 4 ? '#3b82f6' : '#10b981';

    const matchOk  = data.password_confirmation.length > 0 && data.password === data.password_confirmation;
    const matchBad = data.password_confirmation.length > 0 && data.password !== data.password_confirmation;

    function submit(e) {
        e.preventDefault();
        post(update_url);
    }

    function ReqBadge({ ok, children }) {
        return (
            <small style={{
                display: 'inline-flex', alignItems: 'center', gap: 3,
                padding: '2px 8px', borderRadius: 20, fontSize: 11,
                background: ok ? '#d1fae5' : '#f1f5f9',
                color:      ok ? '#065f46' : '#64748b',
                border:     `1px solid ${ok ? '#6ee7b7' : '#e2e8f0'}`,
                transition: 'background .2s, color .2s, border-color .2s',
            }}>
                <span className="material-icons" style={{ fontSize: 12, verticalAlign: 'middle' }}>
                    {ok ? 'check' : 'close'}
                </span>
                {children}
            </small>
        );
    }

    return (
        <>
            <Head title="Change Password" />

            <div className="row justify-content-center">
                <div className="col-lg-6 col-md-8">
                    <div className="chart-card">
                        <div className="chart-header mb-4">
                            <div className="d-flex align-items-center gap-3">
                                <div style={{ width: 44, height: 44, borderRadius: 12, background: 'rgba(99,102,241,.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    <span className="material-icons" style={{ color: '#6366f1', fontSize: 22 }}>lock_reset</span>
                                </div>
                                <div>
                                    <h3 className="mb-0">Change Password</h3>
                                    <p className="text-muted mb-0" style={{ fontSize: 13 }}>Update your account password</p>
                                </div>
                            </div>
                        </div>

                        <form onSubmit={submit} noValidate>
                            {/* Current Password */}
                            <div className="mb-4">
                                <label className="form-label fw-semibold" htmlFor="current_password">
                                    Current Password <span className="text-danger">*</span>
                                </label>
                                <div className="input-group">
                                    <input
                                        type={showCurrent ? 'text' : 'password'}
                                        id="current_password"
                                        className={`form-control${errors.current_password ? ' is-invalid' : ''}`}
                                        placeholder="Enter current password"
                                        autoComplete="current-password"
                                        value={data.current_password}
                                        onChange={e => setData('current_password', e.target.value)}
                                        required
                                    />
                                    <PasswordToggle show={showCurrent} onToggle={() => setShowCurrent(v => !v)} />
                                    {errors.current_password && (
                                        <div className="invalid-feedback">{errors.current_password}</div>
                                    )}
                                </div>
                            </div>

                            {/* New Password */}
                            <div className="mb-3">
                                <label className="form-label fw-semibold" htmlFor="password">
                                    New Password <span className="text-danger">*</span>
                                </label>
                                <div className="input-group">
                                    <input
                                        type={showNew ? 'text' : 'password'}
                                        id="password"
                                        className={`form-control${errors.password ? ' is-invalid' : ''}`}
                                        placeholder="Enter new password"
                                        autoComplete="new-password"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                        required
                                    />
                                    <PasswordToggle show={showNew} onToggle={() => setShowNew(v => !v)} />
                                    {errors.password && (
                                        <div className="invalid-feedback">{errors.password}</div>
                                    )}
                                </div>

                                {showMeter && (
                                    <div className="mt-2">
                                        <div className="progress" style={{ height: 4, borderRadius: 2 }}>
                                            <div
                                                className="progress-bar"
                                                role="progressbar"
                                                style={{ width: `${strengthPct}%`, backgroundColor: strengthColor, transition: 'width .3s, background-color .3s' }}
                                            />
                                        </div>
                                        <div className="d-flex flex-wrap gap-2 mt-2">
                                            <ReqBadge ok={rules.upper}>Uppercase</ReqBadge>
                                            <ReqBadge ok={rules.lower}>Lowercase</ReqBadge>
                                            <ReqBadge ok={rules.number}>Number</ReqBadge>
                                            <ReqBadge ok={rules.special}>Special (@$!%*?&)</ReqBadge>
                                            <ReqBadge ok={rules.length}>8+ chars</ReqBadge>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Confirm New Password */}
                            <div className="mb-4">
                                <label className="form-label fw-semibold" htmlFor="password_confirmation">
                                    Confirm New Password <span className="text-danger">*</span>
                                </label>
                                <div className="input-group">
                                    <input
                                        type={showConfirm ? 'text' : 'password'}
                                        id="password_confirmation"
                                        className="form-control"
                                        placeholder="Re-enter new password"
                                        autoComplete="new-password"
                                        value={data.password_confirmation}
                                        onChange={e => setData('password_confirmation', e.target.value)}
                                        required
                                    />
                                    <PasswordToggle show={showConfirm} onToggle={() => setShowConfirm(v => !v)} />
                                </div>
                                {matchOk && (
                                    <div className="form-text" style={{ color: '#10b981' }}>✓ Passwords match</div>
                                )}
                                {matchBad && (
                                    <div className="form-text" style={{ color: '#ef4444' }}>✗ Passwords do not match</div>
                                )}
                            </div>

                            <div className="d-flex gap-3">
                                <button type="submit" className="btn btn-primary px-4" disabled={processing}>
                                    <span className="material-icons align-middle me-1" style={{ fontSize: 18 }}>lock_reset</span>
                                    {processing ? 'Updating…' : 'Update Password'}
                                </button>
                                <a href={window.history.length > 1 ? '#' : '/'} onClick={e => { e.preventDefault(); window.history.back(); }} className="btn btn-outline-secondary px-4">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
