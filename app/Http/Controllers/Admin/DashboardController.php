<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Followup;
use App\Models\Lead;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $now   = now();
        $today = $now->toDateString();

        // ── Core KPIs ──────────────────────────────────────────────────────────
        $totalLeads       = Lead::count();
        $totalManagers    = User::where('role', 'manager')->count();
        $totalTelecallers = User::where('role', 'telecaller')->count();

        $newLeadsToday    = Lead::whereDate('created_at', $today)->count();
        $newLeadsThisWeek = Lead::whereBetween('created_at', [
            $now->copy()->startOfWeek(),
            $now->copy()->endOfWeek(),
        ])->count();

        $activeCallsNow = CallLog::whereIn('status', ['initiated', 'ringing', 'in-progress', 'answered'])->count();

        $missedCallsToday = CallLog::whereDate('created_at', $today)
            ->whereIn('status', ['missed', 'no-answer', 'busy', 'failed', 'canceled'])
            ->count();

        $followupsTodayQuery = Followup::whereDate('next_followup', $today);
        if (Schema::hasColumn('followups', 'completed_at')) {
            $followupsTodayQuery->whereNull('completed_at');
        }
        $followupsToday = $followupsTodayQuery->count();

        $overdueFollowups = 0;
        if (Schema::hasColumn('followups', 'completed_at')) {
            $overdueFollowups = Followup::whereDate('next_followup', '<', $today)
                ->whereNull('completed_at')
                ->count();
        }

        $conversionsThisMonth = Lead::where('status', 'converted')
            ->whereBetween('updated_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])->count();

        $totalLeadsThisMonth     = Lead::whereBetween('created_at', [
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth(),
        ])->count();
        $conversionRateThisMonth = $totalLeadsThisMonth > 0
            ? round($conversionsThisMonth / $totalLeadsThisMonth * 100, 1) : 0;

        $avgCallDurationSeconds = (int) CallLog::whereNotNull('duration')
            ->where('duration', '>', 0)
            ->whereDate('created_at', '>=', $now->copy()->subDays(29))
            ->avg('duration');

        // ── Fresh KPI Cards ───────────────────────────────────────────────────
        $callsThisMonth = CallLog::whereBetween('created_at', [
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth(),
        ])->count();

        $unassignedLeads = Lead::whereNull('assigned_to')->count();

        $waMessagesToday = Schema::hasTable('whatsapp_messages')
            ? \App\Models\WhatsAppMessage::whereDate('created_at', $today)->count()
            : 0;

        $lostThisMonth = Lead::whereIn('status', ['lost', 'dropped', 'disqualified', 'not_interested'])
            ->whereBetween('updated_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])->count();

        $activeStaffToday = CallLog::whereDate('created_at', $today)
            ->distinct('user_id')
            ->count('user_id');

        $followupsDoneToday = 0;
        if (Schema::hasColumn('followups', 'completed_at')) {
            $followupsDoneToday = Followup::whereDate('completed_at', $today)->count();
        }

        $newLeadsThisMonth = $totalLeadsThisMonth;

        $neverContactedLeads = Lead::whereNotIn('id', CallLog::select('lead_id')->whereNotNull('lead_id'))
            ->count();

        // ── Lead Status Breakdown ──────────────────────────────────────────────
        $statusOrder = ['new', 'assigned', 'contacted', 'follow_up', 'converted'];
        $rawStatus   = Lead::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $leadStatusLabels = [];
        $leadStatusValues = [];
        foreach ($statusOrder as $s) {
            if (isset($rawStatus[$s])) {
                $leadStatusLabels[] = ucwords(str_replace('_', ' ', $s));
                $leadStatusValues[] = (int) $rawStatus[$s];
            }
        }
        foreach ($rawStatus as $s => $v) {
            if (!in_array($s, $statusOrder)) {
                $leadStatusLabels[] = ucwords(str_replace('_', ' ', $s));
                $leadStatusValues[] = (int) $v;
            }
        }

        // ── Source-wise Leads ─────────────────────────────────────────────────
        $sourceQuery = Lead::selectRaw("COALESCE(NULLIF(TRIM(source), ''), 'Unknown') as source_name, COUNT(*) as total")
            ->groupBy('source_name')
            ->orderByDesc('total')
            ->limit(8);

        $sourceRows   = $sourceQuery->get();
        $sourceLabels = $sourceRows->pluck('source_name')
            ->map(fn($s) => $s === 'Unknown' ? $s : ucwords(strtolower($s)))
            ->toArray();
        $sourceValues = $sourceRows->pluck('total')->map(fn($v) => (int) $v)->toArray();

        // ── Call Volume (14 days) ─────────────────────────────────────────────
        $callRows = CallLog::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [
                $now->copy()->subDays(13)->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $callVolumeLabels = [];
        $callVolumeValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $day                = $now->copy()->subDays($i)->toDateString();
            $callVolumeLabels[] = Carbon::parse($day)->format('d M');
            $callVolumeValues[] = (int) ($callRows[$day]->total ?? 0);
        }

        // ── Leads vs Conversions Trend (14 days) ──────────────────────────────
        $leadTrendRows = Lead::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [
                $now->copy()->subDays(13)->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $convTrendRows = Lead::selectRaw('DATE(updated_at) as day, COUNT(*) as total')
            ->where('status', 'converted')
            ->whereBetween('updated_at', [
                $now->copy()->subDays(13)->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $leadTrendLabels       = [];
        $leadTrendValues       = [];
        $conversionTrendValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $day                     = $now->copy()->subDays($i)->toDateString();
            $leadTrendLabels[]       = Carbon::parse($day)->format('d M');
            $leadTrendValues[]       = (int) ($leadTrendRows[$day]->total ?? 0);
            $conversionTrendValues[] = (int) ($convTrendRows[$day]->total ?? 0);
        }

        // ── WhatsApp Volume (14 days) ─────────────────────────────────────────
        $waVolumeLabels  = [];
        $waInboundValues = [];
        $waOutboundValues = [];

        if (Schema::hasTable('whatsapp_messages')) {
            $waRows = WhatsAppMessage::selectRaw('DATE(created_at) as day, direction, COUNT(*) as total')
                ->whereBetween('created_at', [
                    $now->copy()->subDays(13)->startOfDay(),
                    $now->copy()->endOfDay(),
                ])
                ->groupBy('day', 'direction')
                ->orderBy('day')
                ->get();

            $waMatrix = [];
            foreach ($waRows as $row) {
                $waMatrix[$row->day][strtolower((string) $row->direction)] = (int) $row->total;
            }

            for ($i = 13; $i >= 0; $i--) {
                $day               = $now->copy()->subDays($i)->toDateString();
                $waVolumeLabels[]  = Carbon::parse($day)->format('d M');
                $waInboundValues[] = (int) ($waMatrix[$day]['inbound'] ?? 0);
                $waOutboundValues[] = (int) ($waMatrix[$day]['outbound'] ?? 0);
            }
        } else {
            for ($i = 13; $i >= 0; $i--) {
                $waVolumeLabels[]   = $now->copy()->subDays($i)->format('d M');
                $waInboundValues[]  = 0;
                $waOutboundValues[] = 0;
            }
        }

        // ── Telecaller Performance (last 30 days) ─────────────────────────────
        $thirtyDaysAgo  = $now->copy()->subDays(29)->startOfDay()->toDateTimeString();
        $telecallerStats = User::where('role', 'telecaller')
            ->selectRaw("
                users.id,
                users.name,
                (SELECT COUNT(*) FROM call_logs WHERE call_logs.user_id = users.id AND call_logs.created_at >= ?) AS calls_count,
                (SELECT COUNT(*) FROM leads WHERE leads.assigned_to = users.id AND leads.status = 'converted') AS conversions_count
            ", [$thirtyDaysAgo])
            ->orderByDesc('calls_count')
            ->limit(8)
            ->get();

        $telecallerNames         = $telecallerStats->pluck('name')->toArray();
        $telecallerCallCounts    = $telecallerStats->pluck('calls_count')->map(fn($v) => (int) $v)->toArray();
        $telecallerConvCounts    = $telecallerStats->pluck('conversions_count')->map(fn($v) => (int) $v)->toArray();

        // ── Service Performance ───────────────────────────────────────────────
        $serviceStats = Lead::join('services', 'leads.service_id', '=', 'services.id')
            ->selectRaw("services.name as service_name, COUNT(*) as total, SUM(leads.status = 'converted') as conversions")
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'service'     => $row->service_name,
                'total'       => (int) $row->total,
                'conversions' => (int) $row->conversions,
                'rate'        => $row->total > 0 ? round($row->conversions / $row->total * 100, 1) : 0,
            ]);

        return view('admin.dashboard', compact(
            'totalLeads', 'totalManagers', 'totalTelecallers',
            'newLeadsToday', 'newLeadsThisWeek',
            'activeCallsNow', 'missedCallsToday',
            'followupsToday', 'overdueFollowups',
            'conversionsThisMonth', 'conversionRateThisMonth',
            'avgCallDurationSeconds',
            'leadStatusLabels', 'leadStatusValues',
            'sourceLabels', 'sourceValues',
            'callVolumeLabels', 'callVolumeValues',
            'leadTrendLabels', 'leadTrendValues', 'conversionTrendValues',
            'waVolumeLabels', 'waInboundValues', 'waOutboundValues',
            'telecallerNames', 'telecallerCallCounts', 'telecallerConvCounts',
            'serviceStats',
            'callsThisMonth', 'unassignedLeads', 'waMessagesToday',
            'lostThisMonth', 'activeStaffToday', 'followupsDoneToday',
            'newLeadsThisMonth', 'neverContactedLeads'
        ));
    }
}
