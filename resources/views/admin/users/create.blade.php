@extends('layouts.app')

@section('page_title', 'Add User')

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="stat-icon blue" style="width:48px;height:48px;border-radius:14px;flex-shrink:0;">
        <span class="material-icons" style="font-size:22px;">person_add</span>
    </div>
    <div>
        <h4 class="fw-bold mb-0" style="color:var(--text-dark);font-size:1.2rem;">Add New User</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Fill in the details below to create a new team member account.</p>
    </div>
    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary ms-auto" style="font-size:13px;">
        <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;">arrow_back</span>
        Back to Users
    </a>
</div>

<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    @if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" style="border-radius:12px;">
        <span class="material-icons mt-1" style="font-size:18px;flex-shrink:0;">error_outline</span>
        <ul class="mb-0 ps-2">
            @foreach ($errors->all() as $error)
                <li style="font-size:13px;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Section 1: Account Information ─────────────────────────── --}}
    <div class="card mb-4" style="border-radius:16px;border:1px solid var(--border-color);">
        <div class="card-header d-flex align-items-center gap-2"
             style="background:linear-gradient(135deg,#f8f9ff,#f1f5f9);border-bottom:1px solid var(--border-color);border-radius:16px 16px 0 0;padding:1rem 1.5rem;">
            <span class="material-icons" style="font-size:18px;color:var(--primary-color);">badge</span>
            <span class="fw-bold" style="font-size:14px;color:var(--text-dark);">Account Information</span>
        </div>
        <div class="card-body p-4">

            {{-- Employee ID chip --}}
            <div class="mb-4 d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Employee ID</span>
                <span class="badge d-inline-flex align-items-center gap-1"
                      style="background:#eef2ff;color:#4f46e5;font-size:13px;font-weight:700;padding:6px 12px;border-radius:20px;letter-spacing:.3px;">
                    <span class="material-icons" style="font-size:14px;">tag</span>
                    {{ $previewId }}
                </span>
                <span class="text-muted" style="font-size:12px;">Auto-generated on save</span>
            </div>

            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label">
                        <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:var(--primary-color);">person</span>
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g. Rahul Sharma"
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:var(--primary-color);">email</span>
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="rahul@company.com"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:var(--primary-color);">phone</span>
                        Phone Number <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text fw-semibold"
                              style="background:#eef2ff;color:#4f46e5;border-color:var(--border-color);font-size:13px;">+91</span>
                        <input type="text" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="10-digit number" maxlength="10"
                               value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:var(--primary-color);">lock</span>
                        Password <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="passwordInput"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Min 8 characters" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" title="Show / Hide">
                            <span class="material-icons" id="passwordEye" style="font-size:18px;">visibility</span>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted mt-1 d-block" style="font-size:11.5px;">
                        Min 8 chars · uppercase · lowercase · number · special char (@$!%*#?&amp;^_-)
                    </small>
                </div>

            </div>

            {{-- Role picker --}}
            <div class="mt-4">
                <label class="form-label mb-3">
                    <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:var(--primary-color);">manage_accounts</span>
                    Assign Role <span class="text-danger">*</span>
                </label>
                @error('role')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="d-block" for="role_manager" style="cursor:pointer;">
                            <input type="radio" name="role" id="role_manager" value="manager"
                                   {{ old('role') === 'manager' ? 'checked' : '' }} required class="d-none role-radio">
                            <div class="role-card-inner p-3 rounded-3 border text-center">
                                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;border-radius:12px;background:#eef2ff;">
                                    <span class="material-icons" style="font-size:22px;color:#6366f1;">supervisor_account</span>
                                </div>
                                <div class="fw-bold" style="font-size:13px;color:var(--text-dark);">Manager</div>
                                <div class="text-muted" style="font-size:11px;margin-top:2px;">Team leads &amp; oversight</div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-4">
                        <label class="d-block" for="role_telecaller" style="cursor:pointer;">
                            <input type="radio" name="role" id="role_telecaller" value="telecaller"
                                   {{ old('role') === 'telecaller' ? 'checked' : '' }} class="d-none role-radio">
                            <div class="role-card-inner p-3 rounded-3 border text-center">
                                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;border-radius:12px;background:#ecfdf5;">
                                    <span class="material-icons" style="font-size:22px;color:#10b981;">headset_mic</span>
                                </div>
                                <div class="fw-bold" style="font-size:13px;color:var(--text-dark);">Telecaller</div>
                                <div class="text-muted" style="font-size:11px;margin-top:2px;">Calls &amp; lead follow-up</div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-4">
                        <label class="d-block" for="role_report_viewer" style="cursor:pointer;">
                            <input type="radio" name="role" id="role_report_viewer" value="report_viewer"
                                   {{ old('role') === 'report_viewer' ? 'checked' : '' }} class="d-none role-radio">
                            <div class="role-card-inner p-3 rounded-3 border text-center">
                                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;border-radius:12px;background:#fff7ed;">
                                    <span class="material-icons" style="font-size:22px;color:#f59e0b;">bar_chart</span>
                                </div>
                                <div class="fw-bold" style="font-size:13px;color:var(--text-dark);">Report Viewer</div>
                                <div class="text-muted" style="font-size:11px;margin-top:2px;">Principal / Director</div>
                            </div>
                        </label>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- ── Section 2: TCN Account ─────────────────────────── --}}
    <div class="card mb-4" style="border-radius:16px;border:1px solid var(--border-color);">
        <div class="card-header d-flex align-items-center gap-2 justify-content-between"
             style="background:linear-gradient(135deg,#f8f9ff,#f1f5f9);border-bottom:1px solid var(--border-color);border-radius:16px 16px 0 0;padding:1rem 1.5rem;">
            <div class="d-flex align-items-center gap-2">
                <span class="material-icons" style="font-size:18px;color:#8b5cf6;">settings_phone</span>
                <span class="fw-bold" style="font-size:14px;color:var(--text-dark);">TCN Account</span>
                <span class="badge ms-1"
                      style="background:#f3f4f6;color:#6b7280;font-size:10px;font-weight:600;padding:3px 8px;border-radius:20px;">Optional</span>
            </div>
            <button type="button" id="tcnToggleBtn"
                    class="btn btn-sm"
                    style="font-size:12px;color:#6366f1;background:#eef2ff;border:none;border-radius:8px;padding:5px 12px;"
                    onclick="toggleTcnSection()">
                <span class="material-icons me-1" style="font-size:14px;vertical-align:middle;" id="tcnToggleIcon">expand_more</span>
                <span id="tcnToggleText">Configure now</span>
            </button>
        </div>

        <div id="tcnSection" style="display:none;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-2 mb-4 p-3 rounded-3"
                     style="background:#fefce8;border:1px solid #fef08a;">
                    <span class="material-icons mt-1" style="font-size:16px;color:#d97706;flex-shrink:0;">info</span>
                    <p class="mb-0 text-muted" style="font-size:13px;">
                        You can assign TCN credentials now or configure them later by editing the user.
                        The refresh token will be stored encrypted.
                    </p>
                </div>

                <div class="row g-4">

                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:#8b5cf6;">alternate_email</span>
                            TCN Username
                        </label>
                        <input type="text" name="tcn_username" class="form-control"
                               value="{{ old('tcn_username') }}" placeholder="agent@company.com">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:#8b5cf6;">badge</span>
                            Agent ID
                        </label>
                        <input type="text" name="tcn_agent_id" class="form-control"
                               value="{{ old('tcn_agent_id') }}" placeholder="12345">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:#8b5cf6;">group_work</span>
                            Hunt Group ID
                        </label>
                        <input type="text" name="tcn_hunt_group_id" class="form-control"
                               value="{{ old('tcn_hunt_group_id') }}" placeholder="67890">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="material-icons me-1" style="font-size:15px;vertical-align:middle;color:#8b5cf6;">vpn_key</span>
                            Refresh Token
                            <span class="badge ms-1"
                                  style="background:#fef3c7;color:#92400e;font-size:10px;padding:2px 6px;border-radius:6px;">Encrypted</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="tcn_refresh_token" id="tcnTokenInput"
                                   class="form-control" placeholder="Paste refresh token">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="toggleTcnToken()" title="Show / Hide">
                                <span class="material-icons" id="tcnTokenEye" style="font-size:18px;">visibility</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── Actions ─────────────────────────── --}}
    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn px-4"
                style="background:#0f172a;color:#fff;border:none;border-radius:10px;font-weight:600;padding:.65rem 1.5rem;">
            <span class="material-icons me-1" style="font-size:17px;vertical-align:middle;">save</span>
            Save User
        </button>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary"
           style="border-radius:10px;font-weight:500;padding:.65rem 1.25rem;">
            Cancel
        </a>
    </div>

</form>

@push('styles')
<style>
.role-card-inner {
    border-color: var(--border-color) !important;
    background: #fff;
    transition: all .18s ease;
}
.role-card-inner:hover {
    border-color: #a5b4fc !important;
    background: #f8f9ff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99,102,241,.10);
}
.role-radio:checked + .role-card-inner {
    border-color: #6366f1 !important;
    background: #eef2ff !important;
    box-shadow: 0 4px 14px rgba(99,102,241,.18);
}
.role-radio:checked + .role-card-inner .fw-bold {
    color: #4f46e5 !important;
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('passwordEye');
    input.type       = (input.type === 'password') ? 'text' : 'password';
    icon.textContent = (input.type === 'text') ? 'visibility_off' : 'visibility';
}

function toggleTcnToken() {
    const input = document.getElementById('tcnTokenInput');
    const icon  = document.getElementById('tcnTokenEye');
    input.type       = (input.type === 'password') ? 'text' : 'password';
    icon.textContent = (input.type === 'text') ? 'visibility_off' : 'visibility';
}

function toggleTcnSection() {
    const section = document.getElementById('tcnSection');
    const icon    = document.getElementById('tcnToggleIcon');
    const text    = document.getElementById('tcnToggleText');
    const open    = section.style.display === 'none';
    section.style.display = open ? 'block' : 'none';
    icon.textContent = open ? 'expand_less' : 'expand_more';
    text.textContent = open ? 'Collapse' : 'Configure now';
}

@if(old('tcn_username') || old('tcn_agent_id') || old('tcn_hunt_group_id') || old('tcn_refresh_token'))
    document.addEventListener('DOMContentLoaded', function () { toggleTcnSection(); });
@endif
</script>
@endpush

@endsection
