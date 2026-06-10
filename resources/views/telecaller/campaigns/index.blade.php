@extends('layouts.app')

@section('page_title', 'My Campaigns')

@section('content')
    {{-- Hero stat strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--grad-primary);">
                    <span class="material-icons">campaign</span>
                </div>
                <div class="stat-label">Assigned Campaigns</div>
                <div class="stat-value">{{ $totalStats['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--grad-warning);">
                    <span class="material-icons">people</span>
                </div>
                <div class="stat-label">Total Contacts</div>
                <div class="stat-value">{{ number_format($totalStats['contacts']) }}</div>
            </div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header mb-4">
            <h3>My Campaigns</h3>
            <span class="badge rounded-pill" style="background:var(--primary-light);color:var(--primary-color);font-size:11px;padding:5px 12px;">
                {{ $campaigns->total() }} total
            </span>
        </div>

        @if ($campaigns->isEmpty())
            <div class="text-center py-5">
                <div style="width:72px;height:72px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <span class="material-icons" style="font-size:36px;color:var(--primary-color);">campaign</span>
                </div>
                <p class="fw-semibold mb-1" style="color:var(--text-dark);">No campaigns yet</p>
                <p class="text-muted small">Your manager hasn't assigned any campaigns to you.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach ($campaigns as $campaign)
                    @php
                        $statusColors = ['active'=>['bg'=>'#dcfce7','text'=>'#16a34a'],'paused'=>['bg'=>'#fef9c3','text'=>'#ca8a04'],'completed'=>['bg'=>'#f1f5f9','text'=>'#64748b'],'draft'=>['bg'=>'#f1f5f9','text'=>'#64748b']];
                        $sc = $statusColors[$campaign->status] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
                        $total = $campaign->my_contacts_count;
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="h-100" style="background:#fff;border-radius:16px;border:1.5px solid var(--border-color);overflow:hidden;transition:box-shadow 0.18s,transform 0.18s;box-shadow:0 2px 8px rgba(0,0,0,.04);"
                             onmouseover="this.style.boxShadow='0 8px 32px rgba(99,102,241,.13)';this.style.transform='translateY(-2px)'"
                             onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,.04)';this.style.transform=''">

                            {{-- Card accent bar --}}
                            <div style="height:4px;background:var(--grad-primary);"></div>

                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:42px;height:42px;border-radius:12px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <span class="material-icons" style="font-size:22px;color:var(--primary-color);">campaign</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold" style="font-size:14px;color:var(--text-dark);line-height:1.3;">{{ $campaign->name }}</h5>
                                            @if ($campaign->description)
                                                <p class="mb-0 small" style="color:var(--text-muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">{{ $campaign->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge" style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;letter-spacing:.3px;">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </div>

                                {{-- Contact count --}}
                                <div style="background:#f8fafc;border-radius:10px;padding:10px 14px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:8px;background:var(--grad-warning);display:flex;align-items:center;justify-content:center;">
                                        <span class="material-icons" style="font-size:16px;color:#fff;">group</span>
                                    </div>
                                    <div>
                                        <div style="font-size:18px;font-weight:800;color:var(--text-dark);line-height:1;">{{ number_format($total) }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">contacts assigned to you</div>
                                    </div>
                                </div>

                                <a href="{{ route('telecaller.campaigns.show', encrypt($campaign->id)) }}"
                                   class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                                   style="border-radius:10px;font-weight:700;font-size:13px;padding:10px;">
                                    <span class="material-icons" style="font-size:18px;">phone_in_talk</span>
                                    Start Calling
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($campaigns->hasPages())
                <div class="mt-4">{{ $campaigns->links() }}</div>
            @endif
        @endif
    </div>
@endsection
