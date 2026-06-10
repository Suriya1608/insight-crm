@extends('layouts.app')

@section('page_title', 'Add Academic Year')

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-light d-flex align-items-center gap-1">
        <span class="material-icons" style="font-size:16px;">arrow_back</span> Back
    </a>
    <div>
        <h2 class="page-header-title mb-0">Add Academic Year</h2>
        <p class="page-header-subtitle mb-0">Define a new academic year for course intake planning</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <span class="material-icons mt-1" style="font-size:18px;">error_outline</span>
            <ul class="mb-0 ps-2">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4" style="max-width:860px;">
    {{-- Form Card --}}
    <div class="col-lg-8">
        <div class="chart-card p-0">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon blue" style="width:36px;height:36px;border-radius:10px;">
                        <span class="material-icons" style="font-size:18px;">add_circle</span>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-dark);">New Academic Year</div>
                        <div style="font-size:12px;color:var(--text-muted);">Fill in the details to create a new year</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.academic-years.store') }}" method="POST" class="px-4 py-4">
                @csrf

                {{-- Year Name --}}
                <div class="mb-4">
                    <label class="form-label" style="font-size:13px;font-weight:600;color:var(--text-dark);">
                        Year Name <span class="text-danger">*</span>
                    </label>
                    <div class="input-group" style="max-width:220px;">
                        <span class="input-group-text bg-white">
                            <span class="material-icons" style="font-size:16px;color:var(--text-muted);">calendar_today</span>
                        </span>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="e.g. 2026-27" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Use a short label like "2026-27" or "AY2027". Must be unique.</div>
                </div>

                {{-- Date Range --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" style="font-size:13px;font-weight:600;color:var(--text-dark);">
                            Start Date
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <span class="material-icons" style="font-size:16px;color:var(--text-muted);">event</span>
                            </span>
                            <input type="date" name="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}">
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" style="font-size:13px;font-weight:600;color:var(--text-dark);">
                            End Date
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <span class="material-icons" style="font-size:16px;color:var(--text-muted);">event_busy</span>
                            </span>
                            <input type="date" name="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}">
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Active Toggle --}}
                <div class="mb-4">
                    <div class="active-toggle-card" id="activeToggleCard">
                        <div class="d-flex align-items-start gap-3">
                            <div class="pt-1">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="isActive"
                                        {{ old('is_active') == '1' ? 'checked' : '' }}
                                        onchange="document.getElementById('activeToggleCard').classList.toggle('active-on', this.checked)">
                                </div>
                            </div>
                            <div>
                                <label for="isActive" class="form-check-label" style="font-size:14px;font-weight:600;color:var(--text-dark);cursor:pointer;">
                                    Set as Active Year
                                </label>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:3px;">
                                    Only one year can be active at a time. Enabling this will automatically deactivate the current active year.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2" style="border-top:1px solid var(--border-color);padding-top:16px;">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <span class="material-icons" style="font-size:16px;">save</span> Save Year
                    </button>
                    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tips Card --}}
    <div class="col-lg-4">
        <div class="chart-card p-0">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
                <div style="font-size:13px;font-weight:700;color:var(--text-dark);">Quick Tips</div>
            </div>
            <div class="px-4 py-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-2">
                        <div style="width:28px;height:28px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons" style="font-size:14px;color:#6d28d9;">label</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-muted);">
                            Use a short unique label like <strong style="color:var(--text-dark);">2026-27</strong> or <strong style="color:var(--text-dark);">AY2026</strong>.
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div style="width:28px;height:28px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons" style="font-size:14px;color:#1d4ed8;">date_range</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-muted);">
                            Start and end dates are optional but recommended for timeline tracking.
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div style="width:28px;height:28px;border-radius:8px;background:#d1fae5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons" style="font-size:14px;color:#065f46;">check_circle</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-muted);">
                            Only one year can be <strong style="color:var(--text-dark);">Active</strong> at a time. The active year is used by default for new leads and intakes.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.active-toggle-card {
    border: 1.5px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    background: #f8fafc;
    transition: all .2s;
}
.active-toggle-card.active-on {
    border-color: #10b981;
    background: #f0fdf4;
}
</style>
@endsection
