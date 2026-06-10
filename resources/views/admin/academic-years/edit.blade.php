@extends('layouts.app')

@section('page_title', 'Edit Academic Year')

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-light d-flex align-items-center gap-1">
        <span class="material-icons" style="font-size:16px;">arrow_back</span> Back
    </a>
    <div>
        <h2 class="page-header-title mb-0">Edit Academic Year</h2>
        <p class="page-header-subtitle mb-0">{{ $academicYear->name }}</p>
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
                        <span class="material-icons" style="font-size:18px;">edit_calendar</span>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-dark);">Academic Year Details</div>
                        <div style="font-size:12px;color:var(--text-muted);">Update the year information below</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.academic-years.update', $academicYear->encrypted_id) }}" method="POST" class="px-4 py-4">
                @csrf @method('PUT')

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
                            value="{{ old('name', $academicYear->name) }}"
                            placeholder="e.g. 2026-27">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Use a short label like "2026-27" or "AY2027".</div>
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
                                value="{{ old('start_date', $academicYear->start_date?->format('Y-m-d')) }}">
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
                                value="{{ old('end_date', $academicYear->end_date?->format('Y-m-d')) }}">
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Active Toggle --}}
                <div class="mb-4">
                    <div class="active-toggle-card {{ $academicYear->is_active ? 'active-on' : '' }}" id="activeToggleCard">
                        <div class="d-flex align-items-start gap-3">
                            <div class="pt-1">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="isActive"
                                        {{ old('is_active', $academicYear->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
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
                <div class="d-flex gap-2 pt-2" style="border-top:1px solid var(--border-color);padding-top:16px !important;">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <span class="material-icons" style="font-size:16px;">save</span> Update
                    </button>
                    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="col-lg-4">
        <div class="chart-card p-0">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
                <div style="font-size:13px;font-weight:700;color:var(--text-dark);">Year Summary</div>
            </div>
            <div class="px-4 py-3">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Year</div>
                        <div style="font-size:20px;font-weight:800;color:var(--text-dark);margin-top:3px;">{{ $academicYear->name }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Status</div>
                        <div class="mt-1">
                            @if($academicYear->is_active)
                                <span style="display:inline-flex;align-items:center;gap:5px;background:#d1fae5;color:#065f46;font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;">
                                    <span class="material-icons" style="font-size:12px;">check_circle</span> Active / Current
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;color:var(--text-muted);font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;">
                                    <span class="material-icons" style="font-size:12px;">radio_button_unchecked</span> Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Course Intakes</div>
                        <div style="font-size:22px;font-weight:800;color:var(--text-dark);margin-top:3px;">
                            {{ $academicYear->intakes()->count() }}
                        </div>
                    </div>
                    @if($academicYear->start_date && $academicYear->end_date)
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Period</div>
                        <div style="font-size:13px;color:var(--text-dark);font-weight:500;margin-top:3px;">
                            {{ $academicYear->start_date->format('d M Y') }} → {{ $academicYear->end_date->format('d M Y') }}
                        </div>
                    </div>
                    @endif
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Last Updated</div>
                        <div style="font-size:13px;color:var(--text-muted);margin-top:3px;">
                            {{ $academicYear->updated_at->format('d M Y, h:i A') }}
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
