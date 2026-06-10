@extends('layouts.app')

@section('page_title', 'Academic Years')

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 class="page-header-title mb-0">Academic Years</h2>
        <p class="page-header-subtitle mb-0">Manage academic years — only one can be active at a time</p>
    </div>
    <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
        <span class="material-icons" style="font-size:16px;">add</span> New Academic Year
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="material-icons me-2" style="font-size:16px;vertical-align:middle;">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="material-icons me-2" style="font-size:16px;vertical-align:middle;">error</span>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <span class="material-icons" style="font-size:20px;">calendar_today</span>
            </div>
            <div class="stat-label">Total Years</div>
            <div class="stat-value">{{ $totalYears }}</div>
            <div class="stat-trend stable">
                <span class="material-icons" style="font-size:12px;">info</span> All registered
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card highlight-success">
            <div class="stat-icon green">
                <span class="material-icons" style="font-size:20px;">event_available</span>
            </div>
            <div class="stat-label">Current Year</div>
            <div class="stat-value" style="font-size:{{ $activeYear ? '18px' : '26px' }};font-weight:800;">
                {{ $activeYear ? $activeYear->name : '—' }}
            </div>
            <div class="stat-trend up">
                <span class="material-icons" style="font-size:12px;">check_circle</span>
                {{ $activeYear ? 'Active now' : 'None set' }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <span class="material-icons" style="font-size:20px;">layers</span>
            </div>
            <div class="stat-label">Total Intakes</div>
            <div class="stat-value">{{ $totalIntakes }}</div>
            <div class="stat-trend stable">
                <span class="material-icons" style="font-size:12px;">school</span> Across all years
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon cyan">
                <span class="material-icons" style="font-size:20px;">date_range</span>
            </div>
            <div class="stat-label">Years With Intakes</div>
            <div class="stat-value">{{ $yearsWithData }}</div>
            <div class="stat-trend stable">
                <span class="material-icons" style="font-size:12px;">bar_chart</span>
                {{ $totalYears > 0 ? round($yearsWithData / $totalYears * 100) : 0 }}% of total
            </div>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="chart-card p-0">

    {{-- Toolbar --}}
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap px-4 py-3" style="border-bottom:1px solid var(--border-color);">
        <div class="d-flex align-items-center gap-2">
            <span class="material-icons" style="font-size:18px;color:var(--text-muted);">calendar_month</span>
            <span style="font-size:14px;font-weight:600;color:var(--text-dark);">All Academic Years</span>
        </div>
        <div style="font-size:13px;color:var(--text-muted);">
            {{ $years->total() }} {{ Str::plural('year', $years->total()) }} total
        </div>
    </div>

    @if ($years->isEmpty())
        <div class="text-center py-5 text-muted">
            <span class="material-icons" style="font-size:52px;opacity:.25;">calendar_today</span>
            <p class="mt-2 mb-3">No academic years added yet.</p>
            <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary btn-sm">Add First Year</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid var(--border-color);">
                        <th class="px-4 py-3" style="width:60px;font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">#</th>
                        <th class="py-3" style="font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">Year</th>
                        <th class="py-3" style="font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">Start Date</th>
                        <th class="py-3" style="font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">End Date</th>
                        <th class="py-3 text-center" style="font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">Intakes</th>
                        <th class="py-3 text-center" style="width:120px;font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">Status</th>
                        <th class="py-3 pe-4 text-end" style="width:100px;font-size:11px;color:var(--text-muted);letter-spacing:.6px;font-weight:700;text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($years as $year)
                        <tr class="ay-row {{ $year->is_active ? 'ay-active-row' : '' }}" style="border-bottom:1px solid var(--border-color);transition:background .15s;">
                            <td class="px-4 py-3">
                                <span style="font-size:12px;color:var(--text-muted);font-weight:600;">
                                    {{ ($years->currentPage() - 1) * $years->perPage() + $loop->iteration }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ay-avatar {{ $year->is_active ? 'ay-avatar-active' : '' }}">
                                        <span class="material-icons" style="font-size:16px;">{{ $year->is_active ? 'event_available' : 'calendar_today' }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size:15px;color:var(--text-dark);">{{ $year->name }}</div>
                                        @if ($year->is_active)
                                            <span class="current-badge">
                                                <span class="material-icons" style="font-size:10px;">fiber_manual_record</span> Current
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                @if($year->start_date)
                                    <div style="font-size:13px;color:var(--text-dark);font-weight:500;">{{ $year->start_date->format('d M Y') }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $year->start_date->format('l') }}</div>
                                @else
                                    <span style="color:var(--text-muted);font-size:13px;">—</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($year->end_date)
                                    <div style="font-size:13px;color:var(--text-dark);font-weight:500;">{{ $year->end_date->format('d M Y') }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $year->end_date->format('l') }}</div>
                                @else
                                    <span style="color:var(--text-muted);font-size:13px;">—</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <a href="{{ route('admin.course-intakes.index', ['year_id' => $year->id]) }}"
                                    class="intakes-link {{ $year->intakes_count > 0 ? 'intakes-link-filled' : 'intakes-link-empty' }}">
                                    <span class="material-icons" style="font-size:13px;">layers</span>
                                    {{ $year->intakes_count }} {{ Str::plural('intake', $year->intakes_count) }}
                                </a>
                            </td>
                            <td class="py-3 text-center">
                                <form action="{{ route('admin.academic-years.toggle-active', $year->encrypted_id) }}"
                                    method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Click to toggle"
                                        class="status-toggle-btn {{ $year->is_active ? 'status-active' : 'status-inactive' }}">
                                        <span class="material-icons" style="font-size:12px;">{{ $year->is_active ? 'check_circle' : 'radio_button_unchecked' }}</span>
                                        {{ $year->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 pe-4 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.academic-years.edit', $year->encrypted_id) }}"
                                        class="btn btn-sm btn-outline-primary d-flex align-items-center" title="Edit">
                                        <span class="material-icons" style="font-size:15px;">edit</span>
                                    </a>
                                    <form action="{{ route('admin.academic-years.destroy', $year->encrypted_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete {{ addslashes($year->name) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger d-flex align-items-center" title="Delete">
                                            <span class="material-icons" style="font-size:15px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($years->hasPages())
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-4 py-3" style="border-top:1px solid var(--border-color);">
                <div style="font-size:13px;color:var(--text-muted);">
                    Showing <strong>{{ $years->firstItem() }}</strong>–<strong>{{ $years->lastItem() }}</strong>
                    of <strong>{{ $years->total() }}</strong> academic years
                </div>
                <div class="pagination-wrapper">
                    {{ $years->links() }}
                </div>
            </div>
        @else
            <div class="px-4 py-3" style="border-top:1px solid var(--border-color);font-size:13px;color:var(--text-muted);">
                Showing all {{ $years->total() }} {{ Str::plural('academic year', $years->total()) }}
            </div>
        @endif
    @endif
</div>

<style>
.ay-row:hover { background: #f8faff; }
.ay-row:last-child { border-bottom: none !important; }
.ay-active-row { background: #f0fdf4; }
.ay-active-row:hover { background: #ecfdf5; }

.ay-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f1f5f9;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ay-avatar-active {
    background: var(--grad-success);
    color: #fff;
    box-shadow: 0 4px 12px rgba(16,185,129,0.28);
}

.current-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    font-weight: 700;
    color: #065f46;
    background: #d1fae5;
    padding: 2px 7px;
    border-radius: 20px;
    letter-spacing: .3px;
}

.intakes-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s;
}
.intakes-link-filled {
    background: #ede9fe;
    color: #6d28d9;
}
.intakes-link-filled:hover {
    background: #ddd6fe;
    color: #5b21b6;
}
.intakes-link-empty {
    background: #f1f5f9;
    color: var(--text-muted);
}
.intakes-link-empty:hover {
    background: #e2e8f0;
    color: var(--text-dark);
}

.status-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all .2s;
}
.status-active   { background: #d1fae5; color: #065f46; }
.status-inactive { background: #f1f5f9; color: var(--text-muted); }
.status-toggle-btn:hover { filter: brightness(.93); }

.pagination-wrapper .pagination {
    margin: 0;
    gap: 4px;
}
.pagination-wrapper .page-link {
    border-radius: 8px !important;
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    font-size: 13px;
    padding: 5px 11px;
    font-weight: 500;
    transition: all .15s;
}
.pagination-wrapper .page-link:hover,
.pagination-wrapper .page-item.active .page-link {
    background: var(--grad-primary);
    border-color: transparent;
    color: #fff;
}
.pagination-wrapper .page-item.disabled .page-link {
    background: #f8fafc;
    color: #cbd5e1;
}
</style>
@endsection
