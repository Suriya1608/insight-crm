@extends('layouts.app')

@section('page_title', $campaign->name)

@section('content')
    {{-- Page header --}}
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('telecaller.campaigns.index') }}"
               style="width:36px;height:36px;border-radius:10px;background:#f1f5f9;border:1.5px solid var(--border-color);display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--text-dark);transition:background .15s;"
               onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <span class="material-icons" style="font-size:20px;">arrow_back</span>
            </a>
            <div>
                <h2 style="font-size:18px;font-weight:800;color:var(--text-dark);margin:0;line-height:1.2;">{{ $campaign->name }}</h2>
                <span style="font-size:11px;color:var(--text-muted);">Campaign contacts assigned to you</span>
            </div>
        </div>
        @php
            $statusColors = ['active'=>['bg'=>'#dcfce7','text'=>'#16a34a'],'paused'=>['bg'=>'#fef9c3','text'=>'#ca8a04'],'completed'=>['bg'=>'#f1f5f9','text'=>'#64748b'],'draft'=>['bg'=>'#f1f5f9','text'=>'#64748b']];
            $sc = $statusColors[$campaign->status] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
        @endphp
        <span class="badge" style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};font-size:11px;font-weight:700;padding:6px 14px;border-radius:20px;">
            {{ ucfirst($campaign->status) }}
        </span>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--grad-primary);"><span class="material-icons">people</span></div>
                <div class="stat-label">My Contacts</div>
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--grad-warning);"><span class="material-icons">hourglass_empty</span></div>
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ number_format($stats['pending']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--grad-cyan);"><span class="material-icons">phone_in_talk</span></div>
                <div class="stat-label">Contacted</div>
                <div class="stat-value">{{ number_format($stats['called']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--grad-success);"><span class="material-icons">check_circle</span></div>
                <div class="stat-label">Converted</div>
                <div class="stat-value">{{ number_format($stats['converted']) }}</div>
            </div>
        </div>
    </div>

    {{-- Progress bar --}}
    @if ($stats['total'] > 0)
        @php
            $calledPct = round(($stats['called'] / $stats['total']) * 100);
            $convertedPct = round(($stats['converted'] / $stats['total']) * 100);
        @endphp
        <div class="chart-card mb-4 py-3 px-4" style="border-radius:16px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:12px;font-weight:600;color:var(--text-dark);">Overall Progress</span>
                <span style="font-size:12px;color:var(--text-muted);">{{ $calledPct }}% contacted</span>
            </div>
            <div style="height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;position:relative;">
                <div style="height:100%;width:{{ $calledPct }}%;background:var(--grad-primary);border-radius:99px;transition:width .5s;"></div>
            </div>
            <div class="d-flex gap-4 mt-2">
                <div class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:#6366f1;display:inline-block;"></span>
                    <span style="font-size:11px;color:var(--text-muted);">Contacted {{ $calledPct }}%</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                    <span style="font-size:11px;color:var(--text-muted);">Converted {{ $convertedPct }}%</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:#f1f5f9;border:1px solid #cbd5e1;display:inline-block;"></span>
                    <span style="font-size:11px;color:var(--text-muted);">Remaining {{ 100 - $calledPct }}%</span>
                </div>
            </div>
        </div>
    @endif

    <div class="chart-card">
        <div class="chart-header mb-3">
            <h3>Contact List</h3>
        </div>

        {{-- Filters --}}
        <form method="GET" class="row g-2 mb-4 align-items-end">
            <div class="col-12 col-md-5">
                <div style="position:relative;">
                    <span class="material-icons" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:16px;color:var(--text-muted);pointer-events:none;">search</span>
                    <input type="text" name="search" class="form-control form-control-sm"
                           style="padding-left:34px;border-radius:10px;"
                           placeholder="Search name, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-4">
                <select name="status" class="form-select form-select-sm" style="border-radius:10px;">
                    <option value="">All Statuses</option>
                    @foreach (['pending','called','interested','not_interested','no_answer','callback','converted'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" style="border-radius:10px;font-weight:600;">
                    <span class="material-icons me-1" style="font-size:14px;">filter_list</span>Filter
                </button>
                <a href="{{ route('telecaller.campaigns.show', encrypt($campaign->id)) }}"
                   class="btn btn-sm btn-light" style="border-radius:10px;">
                    <span class="material-icons" style="font-size:14px;">close</span>
                </a>
            </div>
        </form>

        @if ($contacts->isEmpty())
            <div class="text-center py-5">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <span class="material-icons" style="font-size:30px;color:var(--primary-color);">people</span>
                </div>
                <p class="fw-semibold mb-1">No contacts found</p>
                <p class="text-muted small">Try adjusting your search or filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-spacing:0 4px;border-collapse:separate;">
                    <thead>
                        <tr style="background:transparent;">
                            <th style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);border:none;padding:8px 14px;">Student</th>
                            <th style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);border:none;padding:8px 14px;">Mobile</th>
                            <th style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);border:none;padding:8px 14px;">Course</th>
                            <th style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);border:none;padding:8px 14px;">Status</th>
                            <th style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);border:none;padding:8px 14px;">Follow-up</th>
                            <th style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);border:none;padding:8px 14px;">Calls</th>
                            <th style="border:none;padding:8px 14px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contacts as $contact)
                            @php
                                $initial = strtoupper(substr($contact->name, 0, 1));
                                $colors = ['A'=>'#6366f1','B'=>'#10b981','C'=>'#f59e0b','D'=>'#ef4444','E'=>'#8b5cf6','F'=>'#06b6d4','G'=>'#ec4899','H'=>'#14b8a6','I'=>'#f97316','J'=>'#6366f1','K'=>'#10b981','L'=>'#f59e0b','M'=>'#8b5cf6','N'=>'#06b6d4','O'=>'#ec4899','P'=>'#14b8a6','Q'=>'#6366f1','R'=>'#ef4444','S'=>'#10b981','T'=>'#f59e0b','U'=>'#8b5cf6','V'=>'#06b6d4','W'=>'#ec4899','X'=>'#6366f1','Y'=>'#10b981','Z'=>'#ef4444'];
                                $avatarColor = $colors[$initial] ?? '#6366f1';
                            @endphp
                            <tr style="background:#fff;border-radius:12px;transition:box-shadow .15s;"
                                onmouseover="this.style.boxShadow='0 2px 12px rgba(99,102,241,.10)'"
                                onmouseout="this.style.boxShadow=''">
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;border-left:1px solid #f1f5f9;border-radius:12px 0 0 12px;padding:12px 14px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:36px;height:36px;border-radius:50%;background:{{ $avatarColor }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <span style="font-size:14px;font-weight:800;color:{{ $avatarColor }};">{{ $initial }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:var(--text-dark);">{{ $contact->name }}</div>
                                            @if ($contact->city)
                                                <div style="font-size:11px;color:var(--text-muted);">{{ $contact->city }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;padding:12px 14px;">
                                    <a href="tel:{{ $contact->phone }}"
                                       style="font-size:12.5px;color:var(--primary-color);text-decoration:none;font-weight:600;">
                                        {{ $contact->phone }}
                                    </a>
                                </td>
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;padding:12px 14px;font-size:12px;color:var(--text-muted);">
                                    {{ $contact->course ?: '—' }}
                                </td>
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;padding:12px 14px;">
                                    @php
                                        $statusBadge = ['pending'=>['bg'=>'#f1f5f9','text'=>'#64748b'],'called'=>['bg'=>'#dbeafe','text'=>'#1d4ed8'],'interested'=>['bg'=>'#dcfce7','text'=>'#16a34a'],'not_interested'=>['bg'=>'#fee2e2','text'=>'#dc2626'],'no_answer'=>['bg'=>'#fef9c3','text'=>'#b45309'],'callback'=>['bg'=>'#ede9fe','text'=>'#7c3aed'],'converted'=>['bg'=>'#d1fae5','text'=>'#065f46']];
                                        $sb = $statusBadge[$contact->status] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
                                    @endphp
                                    <span style="background:{{ $sb['bg'] }};color:{{ $sb['text'] }};font-size:10.5px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;">
                                        {{ App\Models\CampaignContact::statusLabel($contact->status) }}
                                    </span>
                                </td>
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;padding:12px 14px;font-size:12px;color:var(--text-muted);">
                                    @if ($contact->next_followup)
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="material-icons" style="font-size:13px;color:var(--primary-color);">event</span>
                                            {{ $contact->next_followup->format('d M Y') }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;padding:12px 14px;">
                                    <div style="display:inline-flex;align-items:center;gap:4px;background:#f8fafc;border-radius:8px;padding:4px 10px;">
                                        <span class="material-icons" style="font-size:12px;color:var(--text-muted);">call</span>
                                        <span style="font-size:12px;font-weight:700;color:var(--text-dark);">{{ $contact->call_count }}</span>
                                    </div>
                                </td>
                                <td style="border:none;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;border-right:1px solid #f1f5f9;border-radius:0 12px 12px 0;padding:12px 14px;text-align:right;">
                                    <a href="{{ route('telecaller.campaigns.contact', [encrypt($campaign->id), encrypt($contact->id)]) }}"
                                       class="btn btn-sm btn-primary"
                                       style="border-radius:8px;font-size:12px;padding:5px 14px;font-weight:600;">
                                        Open
                                        <span class="material-icons ms-1" style="font-size:13px;">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                            <tr style="height:4px;background:transparent;"><td colspan="7" style="border:none;padding:0;"></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $contacts->links() }}</div>
        @endif
    </div>
@endsection
