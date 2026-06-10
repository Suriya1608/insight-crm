@extends('layouts.app')

@section('page_title', 'Edit Intake')

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.course-intakes.index', ['year_id' => $courseIntake->academic_year_id]) }}"
        class="btn btn-sm btn-light d-flex align-items-center gap-1">
        <span class="material-icons" style="font-size:16px;">arrow_back</span> Back
    </a>
    <div>
        <h2 class="page-header-title mb-0">Edit Intake</h2>
        <p class="page-header-subtitle mb-0">
            {{ $courseIntake->course?->name }} &mdash; {{ $courseIntake->academicYear?->name }}
        </p>
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

<div class="row g-4" style="max-width:900px;">

    {{-- Left: Form --}}
    <div class="col-lg-8">
        <div class="chart-card p-0">

            {{-- Card Header --}}
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon blue" style="width:36px;height:36px;border-radius:10px;">
                        <span class="material-icons" style="font-size:18px;">chair_alt</span>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-dark);">Seat Allocation</div>
                        <div style="font-size:12px;color:var(--text-muted);">Adjust seats — cannot go below enrolled count</div>
                    </div>
                </div>
            </div>

            {{-- Stats Banner --}}
            <div class="px-4 py-3" style="background:#f8fafc;border-bottom:1px solid var(--border-color);">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Total Seats</div>
                        <div style="font-size:28px;font-weight:800;color:var(--text-dark);line-height:1.1;margin-top:4px;">
                            {{ $courseIntake->total_seats }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Enrolled</div>
                        <div style="font-size:28px;font-weight:800;color:#10b981;line-height:1.1;margin-top:4px;">
                            {{ $courseIntake->total_enrolled }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Balance</div>
                        <div style="font-size:28px;font-weight:800;line-height:1.1;margin-top:4px;color:{{ $courseIntake->balance_seats <= 0 ? '#ef4444' : '#6366f1' }};">
                            {{ $courseIntake->balance_seats }}
                        </div>
                    </div>
                </div>

                {{-- Fill bar --}}
                @php
                    $fillPct = $courseIntake->total_seats > 0
                        ? round($courseIntake->total_enrolled / $courseIntake->total_seats * 100)
                        : 0;
                @endphp
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:11px;color:var(--text-muted);font-weight:600;">Fill Rate</span>
                        <span style="font-size:12px;font-weight:700;color:{{ $fillPct >= 90 ? '#ef4444' : ($fillPct >= 60 ? '#f59e0b' : '#10b981') }};">{{ $fillPct }}%</span>
                    </div>
                    <div style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $fillPct }}%;border-radius:3px;background:{{ $fillPct >= 90 ? 'var(--grad-danger)' : ($fillPct >= 60 ? 'var(--grad-warning)' : 'var(--grad-success)') }};transition:width .4s ease;"></div>
                    </div>
                </div>
            </div>

            {{-- Quota Breakdown --}}
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
                <div class="row g-3">
                    {{-- Management --}}
                    <div class="col-sm-6">
                        <div class="quota-card quota-mgmt">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width:28px;height:28px;border-radius:8px;background:var(--grad-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;">M</div>
                                <span style="font-size:13px;font-weight:700;color:#4f46e5;">Management</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;">Enrolled / Seats</div>
                                    <div style="font-size:18px;font-weight:800;color:var(--text-dark);margin-top:2px;">
                                        {{ $courseIntake->management_enrolled }}
                                        <span style="color:var(--text-muted);font-size:14px;font-weight:500;">/ {{ $courseIntake->management_seats }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;">Balance</div>
                                    <div style="font-size:20px;font-weight:800;margin-top:2px;color:{{ $courseIntake->management_balance <= 0 ? '#ef4444' : '#6366f1' }};">
                                        {{ $courseIntake->management_balance }}
                                    </div>
                                </div>
                            </div>
                            @php $mgmtPct = $courseIntake->management_seats > 0 ? round($courseIntake->management_enrolled / $courseIntake->management_seats * 100) : 0; @endphp
                            <div style="height:4px;background:#c7d2fe;border-radius:2px;margin-top:12px;overflow:hidden;">
                                <div style="height:100%;width:{{ $mgmtPct }}%;background:var(--grad-primary);border-radius:2px;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Counselling --}}
                    <div class="col-sm-6">
                        <div class="quota-card quota-coun">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width:28px;height:28px;border-radius:8px;background:var(--grad-success);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;">C</div>
                                <span style="font-size:13px;font-weight:700;color:#059669;">Counselling</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;">Enrolled / Seats</div>
                                    <div style="font-size:18px;font-weight:800;color:var(--text-dark);margin-top:2px;">
                                        {{ $courseIntake->counselling_enrolled }}
                                        <span style="color:var(--text-muted);font-size:14px;font-weight:500;">/ {{ $courseIntake->counselling_seats }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;">Balance</div>
                                    <div style="font-size:20px;font-weight:800;margin-top:2px;color:{{ $courseIntake->counselling_balance <= 0 ? '#ef4444' : '#10b981' }};">
                                        {{ $courseIntake->counselling_balance }}
                                    </div>
                                </div>
                            </div>
                            @php $counPct = $courseIntake->counselling_seats > 0 ? round($courseIntake->counselling_enrolled / $courseIntake->counselling_seats * 100) : 0; @endphp
                            <div style="height:4px;background:#a7f3d0;border-radius:2px;margin-top:12px;overflow:hidden;">
                                <div style="height:100%;width:{{ $counPct }}%;background:var(--grad-success);border-radius:2px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Adjust Form --}}
            <form action="{{ route('admin.course-intakes.update', $courseIntake->encrypted_id) }}" method="POST" class="px-4 py-4">
                @csrf @method('PUT')

                <div class="mb-3" style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;">
                    Adjust Seat Allocation
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" style="font-size:13px;font-weight:600;color:var(--text-dark);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div style="width:22px;height:22px;border-radius:6px;background:var(--grad-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:800;">M</div>
                                Management Seats
                            </div>
                        </label>
                        <input type="number" name="management_seats"
                            class="form-control @error('management_seats') is-invalid @enderror"
                            value="{{ old('management_seats', $courseIntake->management_seats) }}"
                            min="{{ $courseIntake->management_enrolled }}" max="9999">
                        <div class="form-text">
                            <span class="material-icons" style="font-size:12px;vertical-align:middle;color:var(--text-muted);">info</span>
                            Minimum {{ $courseIntake->management_enrolled }} (currently enrolled)
                        </div>
                        @error('management_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" style="font-size:13px;font-weight:600;color:var(--text-dark);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div style="width:22px;height:22px;border-radius:6px;background:var(--grad-success);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:800;">C</div>
                                Counselling Seats
                            </div>
                        </label>
                        <input type="number" name="counselling_seats"
                            class="form-control @error('counselling_seats') is-invalid @enderror"
                            value="{{ old('counselling_seats', $courseIntake->counselling_seats) }}"
                            min="{{ $courseIntake->counselling_enrolled }}" max="9999">
                        <div class="form-text">
                            <span class="material-icons" style="font-size:12px;vertical-align:middle;color:var(--text-muted);">info</span>
                            Minimum {{ $courseIntake->counselling_enrolled }} (currently enrolled)
                        </div>
                        @error('counselling_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2" style="border-top:1px solid var(--border-color);padding-top:16px;">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <span class="material-icons" style="font-size:16px;">save</span> Update
                    </button>
                    <a href="{{ route('admin.course-intakes.index', ['year_id' => $courseIntake->academic_year_id]) }}"
                        class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Right: Summary --}}
    <div class="col-lg-4">
        <div class="chart-card p-0">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
                <div style="font-size:13px;font-weight:700;color:var(--text-dark);">Intake Summary</div>
            </div>
            <div class="px-4 py-3">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Course</div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-dark);margin-top:4px;line-height:1.3;">
                            {{ $courseIntake->course?->name ?? '—' }}
                        </div>
                        @if($courseIntake->course?->code)
                            <span style="display:inline-block;margin-top:4px;background:#ede9fe;color:#6d28d9;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;">
                                {{ $courseIntake->course->code }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Academic Year</div>
                        <div style="font-size:18px;font-weight:800;color:var(--text-dark);margin-top:4px;">
                            {{ $courseIntake->academicYear?->name ?? '—' }}
                        </div>
                        @if($courseIntake->academicYear?->is_active)
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#d1fae5;color:#065f46;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;margin-top:4px;">
                                <span class="material-icons" style="font-size:10px;">fiber_manual_record</span> Current Year
                            </span>
                        @endif
                    </div>
                    <div style="border-top:1px solid var(--border-color);padding-top:12px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:12px;color:var(--text-muted);">Management seats</span>
                            <span style="font-size:12px;font-weight:700;color:var(--text-dark);">{{ $courseIntake->management_seats }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:12px;color:var(--text-muted);">Counselling seats</span>
                            <span style="font-size:12px;font-weight:700;color:var(--text-dark);">{{ $courseIntake->counselling_seats }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:12px;color:var(--text-muted);">Total enrolled</span>
                            <span style="font-size:12px;font-weight:700;color:#10b981;">{{ $courseIntake->total_enrolled }}</span>
                        </div>
                        <div class="d-flex justify-content-between" style="border-top:1px solid var(--border-color);padding-top:8px;margin-top:4px;">
                            <span style="font-size:13px;font-weight:600;color:var(--text-dark);">Total seats</span>
                            <span style="font-size:14px;font-weight:800;color:var(--text-dark);">{{ $courseIntake->total_seats }}</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Last Updated</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                            {{ $courseIntake->updated_at->format('d M Y, h:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.quota-card {
    border-radius: 12px;
    padding: 16px;
    border: 1.5px solid;
}
.quota-mgmt {
    background: #eef2ff;
    border-color: #c7d2fe;
}
.quota-coun {
    background: #ecfdf5;
    border-color: #a7f3d0;
}
</style>
@endsection
