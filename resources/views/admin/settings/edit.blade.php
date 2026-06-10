@extends('layouts.app')

@section('page_title', 'General Settings')

@push('styles')
<style>
    /* ── Settings Form ── */
    .settings-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .settings-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbff;
    }
    .settings-section-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .settings-section-icon .material-icons { font-size: 19px; color: #fff; }
    .settings-section-title { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .settings-section-subtitle { font-size: 11.5px; color: #64748b; margin-top: 1px; }
    .settings-section-body { padding: 24px; }

    /* ── Image upload zone ── */
    .img-upload-wrap {
        border: 2px dashed #c7d2fe;
        border-radius: 12px;
        background: linear-gradient(135deg, #f0f1ff 0%, #faf5ff 100%);
        padding: 20px;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
        text-align: center;
    }
    .img-upload-wrap:hover { border-color: #6366f1; background: linear-gradient(135deg,#e0e2ff,#f3e8ff); }
    .img-upload-wrap input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .img-upload-icon {
        width: 44px; height: 44px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 8px;
        box-shadow: 0 3px 10px rgba(99,102,241,.25);
    }
    .img-upload-icon .material-icons { font-size: 20px; color: #fff; }

    /* Preview box */
    .img-preview-box {
        margin-top: 14px;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .img-preview-box img { border-radius: 8px; object-fit: contain; }
    .img-preview-label { font-size: 11px; color: #64748b; }
    .img-preview-name  { font-size: 12px; font-weight: 600; color: #334155; word-break: break-all; }

    /* Field icon prefix */
    .field-icon-wrap { position: relative; }
    .field-icon-wrap .field-icon {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        font-size: 16px; color: #94a3b8; pointer-events: none;
    }
    .field-icon-wrap input { padding-left: 38px; }

    /* Save bar */
    .settings-save-bar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .settings-save-note { font-size: 12px; color: #64748b; }
    .settings-save-note strong { color: #334155; }
</style>
@endpush

@section('content')

    @include('admin.settings.partials.nav')

    <form method="POST" action="{{ route('admin.settings.general.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- ── Section 1: Site Identity ── --}}
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <span class="material-icons">language</span>
                </div>
                <div>
                    <div class="settings-section-title">Site Identity</div>
                    <div class="settings-section-subtitle">Basic information about your CRM platform</div>
                </div>
            </div>
            <div class="settings-section-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Site Name <span class="text-danger">*</span>
                        </label>
                        <div class="field-icon-wrap">
                            <span class="material-icons field-icon">badge</span>
                            <input type="text" name="site_name" class="form-control"
                                value="{{ \App\Models\Setting::get('site_name') }}"
                                placeholder="e.g. EduCRM" required>
                        </div>
                        <div class="form-text">Displayed in the browser tab and emails.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Site URL <span class="text-danger">*</span>
                        </label>
                        <div class="field-icon-wrap">
                            <span class="material-icons field-icon">link</span>
                            <input type="text" name="site_url" class="form-control"
                                value="{{ \App\Models\Setting::get('site_url') }}"
                                placeholder="https://yourdomain.com" required>
                        </div>
                        <div class="form-text">Used in notification links and redirects.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Employee ID Prefix <span class="text-danger">*</span>
                        </label>
                        <div class="field-icon-wrap">
                            <span class="material-icons field-icon">tag</span>
                            <input type="text" name="employee_id_prefix" class="form-control"
                                value="{{ \App\Models\Setting::get('employee_id_prefix', 'EMP') }}"
                                placeholder="e.g. IHCM" maxlength="10" required>
                        </div>
                        <div class="form-text">
                            IDs will be generated as
                            <strong class="text-primary">PREFIX0001</strong>,
                            <strong class="text-primary">PREFIX0002</strong>…
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Lead ID Prefix <span class="text-danger">*</span>
                        </label>
                        <div class="field-icon-wrap">
                            <span class="material-icons field-icon">confirmation_number</span>
                            <input type="text" name="lead_prefix" class="form-control"
                                value="{{ \App\Models\Setting::get('lead_prefix', 'SMIT') }}"
                                placeholder="e.g. SMIT" maxlength="10" required
                                style="text-transform:uppercase;"
                                oninput="this.value=this.value.toUpperCase()">
                        </div>
                        <div class="form-text">
                            Lead codes will be generated as
                            <strong class="text-primary">PREFIX-00001</strong>,
                            <strong class="text-primary">PREFIX-00002</strong>…
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 2: Branding ── --}}
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon" style="background:linear-gradient(135deg,#10b981,#06b6d4);">
                    <span class="material-icons">palette</span>
                </div>
                <div>
                    <div class="settings-section-title">Branding</div>
                    <div class="settings-section-subtitle">Logo and favicon shown across the platform</div>
                </div>
            </div>
            <div class="settings-section-body">
                <div class="row g-4">

                    {{-- Logo --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">Site Logo</label>
                        <div class="img-upload-wrap" id="logoDropZone">
                            <input type="file" name="site_logo" id="site_logo"
                                accept="image/png,image/jpeg,image/jpg,image/webp">
                            <div class="img-upload-icon">
                                <span class="material-icons">add_photo_alternate</span>
                            </div>
                            <div style="font-size:13px;font-weight:600;color:#334155;">Click or drag to upload logo</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:4px;">JPG, PNG, WEBP — max 2 MB</div>
                        </div>
                        <div class="img-preview-box" id="logoPreviewBox"
                             style="{{ \App\Models\Setting::get('site_logo') ? '' : 'display:none;' }}">
                            <img id="logoPreview" height="52"
                                 src="{{ \App\Models\Setting::get('site_logo') ? asset('storage/' . \App\Models\Setting::get('site_logo')) : '' }}"
                                 style="{{ \App\Models\Setting::get('site_logo') ? '' : 'display:none;' }}">
                            <div class="img-preview-label">Current logo</div>
                        </div>
                    </div>

                    {{-- Favicon --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">Site Favicon</label>
                        <div class="img-upload-wrap" id="faviconDropZone">
                            <input type="file" name="site_favicon" id="site_favicon"
                                accept="image/png,image/x-icon">
                            <div class="img-upload-icon" style="background:linear-gradient(135deg,#f59e0b,#f97316);">
                                <span class="material-icons">photo_size_select_small</span>
                            </div>
                            <div style="font-size:13px;font-weight:600;color:#334155;">Click or drag to upload favicon</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:4px;">PNG, ICO — max 512 KB</div>
                        </div>
                        <div class="img-preview-box" id="faviconPreviewBox"
                             style="{{ \App\Models\Setting::get('site_favicon') ? '' : 'display:none;' }}">
                            <img id="faviconPreview" height="32"
                                 src="{{ \App\Models\Setting::get('site_favicon') ? asset('storage/' . \App\Models\Setting::get('site_favicon')) : '' }}"
                                 style="{{ \App\Models\Setting::get('site_favicon') ? '' : 'display:none;' }}">
                            <div class="img-preview-label">Current favicon</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Save Bar ── --}}
        <div class="settings-save-bar">
            <div class="settings-save-note">
                <strong>General Settings</strong> — changes take effect immediately after saving.
            </div>
            <button type="submit" class="btn btn-primary fw-semibold px-4" style="border-radius:10px;">
                <span class="material-icons me-1" style="font-size:16px;vertical-align:middle;">save</span>
                Save Settings
            </button>
        </div>

    </form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function setupImageUpload({ inputId, previewId, previewBoxId, allowedTypes, maxBytes, label }) {
        const input      = document.getElementById(inputId);
        const preview    = document.getElementById(previewId);
        const previewBox = document.getElementById(previewBoxId);

        if (!input) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            if (!allowedTypes.includes(file.type)) {
                alert(label + ' must be ' + allowedTypes.map(t => t.split('/')[1].toUpperCase()).join(', ') + ' format.');
                this.value = ''; return;
            }
            if (file.size > maxBytes) {
                alert(label + ' must be less than ' + (maxBytes >= 1048576 ? (maxBytes/1048576)+'MB' : (maxBytes/1024)+'KB') + '.');
                this.value = ''; return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                previewBox.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        });
    }

    setupImageUpload({
        inputId: 'site_logo', previewId: 'logoPreview',
        previewBoxId: 'logoPreviewBox',
        allowedTypes: ['image/jpeg','image/png','image/webp'],
        maxBytes: 2 * 1024 * 1024, label: 'Logo'
    });

    setupImageUpload({
        inputId: 'site_favicon', previewId: 'faviconPreview',
        previewBoxId: 'faviconPreviewBox',
        allowedTypes: ['image/png','image/x-icon'],
        maxBytes: 512 * 1024, label: 'Favicon'
    });

});
</script>
@endsection
