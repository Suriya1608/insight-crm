<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArrayExport;
use App\Exports\MultiSheetArrayExport;
use App\Exports\TelecallerLeadActivityExport;
use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Followup;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadMeeting;
use App\Models\Course;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function telecallerPerformance(Request $request)
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'telecaller' => $request->get('telecaller', 'all'),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);
        $filterOptions = [
            'sources'     => Lead::select('source')->distinct()->orderBy('source')->pluck('source'),
            'telecallers' => User::where('role', 'telecaller')->where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];

        $rows = User::where('role', 'telecaller')
            ->where('status', 1)
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('id', (int) $filters['telecaller']))
            ->get(['id', 'name'])
            ->map(function ($t) use ($startAt, $endAt, $filters) {
                $leadQ = Lead::where('assigned_to', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                if ($filters['source'] !== 'all') {
                    $leadQ->where('source', $filters['source']);
                }

                $assigned  = (clone $leadQ)->count();
                $converted = (clone $leadQ)->where('status', 'converted')->count();
                $active    = (clone $leadQ)->whereNotIn('status', ['converted', 'lost', 'disqualified'])->count();
                $lost      = (clone $leadQ)->where('status', 'lost')->count();

                $callsQ    = CallLog::where('user_id', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                $calls     = (clone $callsQ)->count();
                $answered  = (clone $callsQ)->where('status', 'completed')->count();
                $missed    = (clone $callsQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                $avgDur    = (float) ((clone $callsQ)->avg('duration') ?: 0);
                $totalSecs = (int) (clone $callsQ)->sum('duration');
                $totalMins = round($totalSecs / 60, 1);
                $answerRate = $calls > 0 ? round(($answered / $calls) * 100, 1) : 0;

                $fuQ              = Followup::where('user_id', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                $followupsTotal   = (clone $fuQ)->count();
                $followupsDone    = (clone $fuQ)->whereNotNull('completed_at')->count();
                $pendingFollowups = (clone $fuQ)->whereDate('next_followup', '<=', now()->toDateString())->whereNull('completed_at')->count();
                $followupRate     = $followupsTotal > 0 ? round(($followupsDone / $followupsTotal) * 100, 1) : 0;

                $convRate  = $assigned > 0 ? round(($converted / $assigned) * 100, 1) : 0;
                $callScore = $calls > 0 ? min(100, round(($answered / max(1, $calls)) * 100)) : 0;
                $effScore  = round(($convRate * 0.40) + ($followupRate * 0.35) + ($callScore * 0.25), 1);

                // Avg calls per lead (activity density)
                $callsPerLead = $assigned > 0 ? round($calls / $assigned, 1) : 0;

                // Avg talk time per answered call in seconds
                $avgTalkSecs = $answered > 0
                    ? (float) ((clone $callsQ)->where('status', 'completed')->avg('duration') ?: 0)
                    : 0;

                return [
                    'id'                => $t->id,
                    'name'              => $t->name,
                    'assigned'          => $assigned,
                    'converted'         => $converted,
                    'active'            => $active,
                    'lost'              => $lost,
                    'calls'             => $calls,
                    'answered'          => $answered,
                    'missed'            => $missed,
                    'answer_rate'       => $answerRate,
                    'avg_talk_time'     => sprintf('%02d:%02d', floor($avgDur / 60), (int) $avgDur % 60),
                    'avg_talk_secs'     => round($avgTalkSecs),
                    'total_talk_mins'   => $totalMins,
                    'total_talk_secs'   => $totalSecs,
                    'followups_total'   => $followupsTotal,
                    'followups_done'    => $followupsDone,
                    'followup_rate'     => $followupRate,
                    'pending_followups' => $pendingFollowups,
                    'conversion_rate'   => $convRate,
                    'calls_per_lead'    => $callsPerLead,
                    'efficiency_score'  => $effScore,
                    'grade'             => $effScore >= 70 ? 'A' : ($effScore >= 40 ? 'B' : ($effScore >= 20 ? 'C' : 'D')),
                ];
            })->sortByDesc('efficiency_score')->values();

        $n = $rows->count();
        $summary = [
            'total_telecallers'   => $n,
            'total_calls'         => $rows->sum('calls'),
            'total_converted'     => $rows->sum('converted'),
            'total_talk_mins'     => $rows->sum('total_talk_mins'),
            'total_talk_fmt'      => sprintf('%dh %dm', floor($rows->sum('total_talk_mins') / 60), (int) $rows->sum('total_talk_mins') % 60),
            'avg_answer_rate'     => $n > 0 ? round($rows->avg('answer_rate'), 1) : 0,
            'avg_conversion_rate' => $n > 0 ? round($rows->avg('conversion_rate'), 1) : 0,
            'avg_followup_rate'   => $n > 0 ? round($rows->avg('followup_rate'), 1) : 0,
            'total_pending_fu'    => $rows->sum('pending_followups'),
            'total_assigned'      => $rows->sum('assigned'),
            'total_answered'      => $rows->sum('answered'),
            'total_missed'        => $rows->sum('missed'),
            'top_performer'       => $rows->first()['name'] ?? '—',
            'top_score'           => $rows->first()['efficiency_score'] ?? 0,
        ];

        // Performance distribution for doughnut
        $perfDist = [
            'high'    => $rows->where('efficiency_score', '>=', 70)->count(),
            'average' => $rows->whereBetween('efficiency_score', [40, 69.9])->count(),
            'low'     => $rows->where('efficiency_score', '<', 40)->count(),
        ];

        // Monthly trend — last 6 months
        $monthLabels    = [];
        $monthAssigned  = [];
        $monthConverted = [];
        $monthCalls     = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $monthLabels[] = $mStart->format('M Y');
            $q = Lead::whereHas('assignedUser', fn($q) => $q->where('role', 'telecaller'))
                ->whereBetween('created_at', [$mStart, $mEnd]);
            if ($filters['telecaller'] !== 'all') {
                $q->where('assigned_to', (int) $filters['telecaller']);
            }
            if ($filters['source'] !== 'all') {
                $q->where('source', $filters['source']);
            }
            $monthAssigned[]  = (clone $q)->count();
            $monthConverted[] = (clone $q)->where('status', 'converted')->count();

            $callQ = CallLog::whereBetween('created_at', [$mStart, $mEnd]);
            if ($filters['telecaller'] !== 'all') {
                $callQ->where('user_id', (int) $filters['telecaller']);
            } else {
                $callQ->whereHas('user', fn($q) => $q->where('role', 'telecaller'));
            }
            $monthCalls[] = (clone $callQ)->count();
        }

        $title        = 'Telecaller Performance';
        $tableHeaders = ['Rank', 'Telecaller', 'Grade', 'Assigned', 'Converted', 'Active', 'Lost', 'Calls', 'Answered', 'Missed', 'Answer %', 'Avg Talk', 'Talk Time', 'Calls/Lead', 'Followup %', 'Pending F/U', 'Conv %', 'Score'];
        $tableRows    = $rows->map(fn($r, $i) => [
            '#' . ($i + 1), $r['name'], $r['grade'],
            $r['assigned'], $r['converted'], $r['active'], $r['lost'],
            $r['calls'], $r['answered'], $r['missed'],
            $r['answer_rate'] . '%', $r['avg_talk_time'],
            $r['total_talk_mins'] . ' min', $r['calls_per_lead'],
            $r['followup_rate'] . '%', $r['pending_followups'],
            $r['conversion_rate'] . '%', $r['efficiency_score'],
        ])->all();

        return view('admin.reports.telecaller_performance', compact(
            'title', 'rows', 'filters', 'filterOptions', 'summary', 'perfDist',
            'tableHeaders', 'tableRows', 'monthLabels', 'monthAssigned', 'monthConverted', 'monthCalls'
        ));
    }

    public function managerPerformance(Request $request)
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'manager'    => $request->get('manager', 'all'),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);
        $filterOptions = [
            'sources'  => Lead::select('source')->distinct()->orderBy('source')->pluck('source'),
            'managers' => User::where('role', 'manager')->where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];

        $rows = User::where('role', 'manager')
            ->when($filters['manager'] !== 'all', fn($q) => $q->where('id', (int) $filters['manager']))
            ->get(['id', 'name'])
            ->map(function ($manager) use ($startAt, $endAt, $filters) {
                $leadQ = Lead::where('assigned_by', $manager->id)->whereBetween('created_at', [$startAt, $endAt]);
                if ($filters['source'] !== 'all') {
                    $leadQ->where('source', $filters['source']);
                }

                $total     = (clone $leadQ)->count();
                $converted = (clone $leadQ)->where('status', 'converted')->count();
                $active    = (clone $leadQ)->whereNotIn('status', ['converted', 'lost', 'disqualified'])->count();
                $lost      = (clone $leadQ)->where('status', 'lost')->count();
                $teamSize  = (clone $leadQ)->whereNotNull('assigned_to')->distinct('assigned_to')->count('assigned_to');

                $leadIds = (clone $leadQ)->pluck('id');

                // Call breakdown
                $callQ    = CallLog::whereIn('lead_id', $leadIds);
                $calls    = (clone $callQ)->count();
                $inbound  = (clone $callQ)->where('direction', 'inbound')->count();
                $outbound = (clone $callQ)->where('direction', 'outbound')->count();
                $answered = (clone $callQ)->where('status', 'completed')->count();
                $missed   = (clone $callQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                $totalSecs   = (int) (clone $callQ)->sum('duration');
                $avgDuration = (float) ((clone $callQ)->avg('duration') ?: 0);
                $answerRate  = $calls > 0 ? round(($answered / $calls) * 100, 1) : 0;
                $totalTalkMins = round($totalSecs / 60, 1);

                // Follow-ups
                $fuQ     = Followup::whereIn('lead_id', $leadIds);
                $fuTotal = (clone $fuQ)->count();
                $fuDone  = (clone $fuQ)->whereNotNull('completed_at')->count();
                $fuPend  = (clone $fuQ)->whereDate('next_followup', '<=', now()->toDateString())->whereNull('completed_at')->count();
                $fuRate  = $fuTotal > 0 ? round(($fuDone / $fuTotal) * 100, 1) : 0;

                // Meetings
                $meetingCount = LeadMeeting::whereIn('lead_id', $leadIds)->count();
                $meetingDone  = LeadMeeting::whereIn('lead_id', $leadIds)->where('status', 'completed')->count();

                // Messages
                $msgCount = WhatsAppMessage::whereIn('lead_id', $leadIds)->count();

                // Per-telecaller breakdown under this manager
                $telecallerIds = (clone $leadQ)->whereNotNull('assigned_to')->distinct('assigned_to')->pluck('assigned_to');
                $telecallerBreakdown = User::whereIn('id', $telecallerIds)->get(['id', 'name'])->map(function ($tc) use ($leadIds, $startAt, $endAt) {
                    $tcLeadQ   = Lead::where('assigned_to', $tc->id)->whereIn('id', $leadIds);
                    $tcLeads   = (clone $tcLeadQ)->count();
                    $tcConv    = (clone $tcLeadQ)->where('status', 'converted')->count();
                    $tcCallQ   = CallLog::where('user_id', $tc->id)->whereIn('lead_id', $leadIds);
                    $tcCalls   = (clone $tcCallQ)->count();
                    $tcAns     = (clone $tcCallQ)->where('status', 'completed')->count();
                    $tcMissed  = (clone $tcCallQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                    $tcFuQ     = Followup::where('user_id', $tc->id)->whereIn('lead_id', $leadIds);
                    $tcFuDone  = (clone $tcFuQ)->whereNotNull('completed_at')->count();
                    $tcFuTotal = (clone $tcFuQ)->count();
                    $tcFuRate  = $tcFuTotal > 0 ? round(($tcFuDone / $tcFuTotal) * 100, 1) : 0;
                    $tcConvRate = $tcLeads > 0 ? round(($tcConv / $tcLeads) * 100, 1) : 0;
                    return [
                        'name'            => $tc->name,
                        'leads'           => $tcLeads,
                        'converted'       => $tcConv,
                        'conversion_rate' => $tcConvRate,
                        'calls'           => $tcCalls,
                        'answered'        => $tcAns,
                        'missed'          => $tcMissed,
                        'followup_rate'   => $tcFuRate,
                    ];
                })->values()->all();

                // Avg response time (minutes from lead created_at to first call)
                $firstCallMap = CallLog::whereIn('lead_id', $leadIds)
                    ->select('lead_id', DB::raw('MIN(created_at) as first_call'))
                    ->groupBy('lead_id')
                    ->pluck('first_call', 'lead_id');
                $responseMins = collect();
                foreach ((clone $leadQ)->get(['id', 'created_at']) as $lead) {
                    if (isset($firstCallMap[$lead->id])) {
                        $diff = $lead->created_at->diffInMinutes($firstCallMap[$lead->id]);
                        if ($diff >= 0 && $diff < 10080) {
                            $responseMins->push($diff);
                        }
                    }
                }
                $avgResponseMins = $responseMins->count() > 0 ? round($responseMins->avg()) : null;

                $convRate  = $total > 0 ? round(($converted / $total) * 100, 1) : 0;
                $callScore = $calls > 0 ? min(100, round(($answered / max(1, $calls)) * 100)) : 0;
                $perfScore = round(($convRate * 0.4) + ($fuRate * 0.35) + ($callScore * 0.25), 1);

                return [
                    'id'                    => $manager->id,
                    'name'                  => $manager->name,
                    'grade'                 => $perfScore >= 70 ? 'A' : ($perfScore >= 40 ? 'B' : ($perfScore >= 20 ? 'C' : 'D')),
                    'assigned'              => $total,
                    'converted'             => $converted,
                    'active'                => $active,
                    'lost'                  => $lost,
                    'team_size'             => $teamSize,
                    'calls'                 => $calls,
                    'calls_inbound'         => $inbound,
                    'calls_outbound'        => $outbound,
                    'calls_answered'        => $answered,
                    'calls_missed'          => $missed,
                    'answer_rate'           => $answerRate,
                    'total_talk_mins'       => $totalTalkMins,
                    'total_talk_fmt'        => sprintf('%dh %dm', floor($totalTalkMins / 60), (int) $totalTalkMins % 60),
                    'avg_talk_time'         => sprintf('%02d:%02d', floor($avgDuration / 60), (int) $avgDuration % 60),
                    'followup_rate'         => $fuRate,
                    'followups_total'       => $fuTotal,
                    'followups_done'        => $fuDone,
                    'pending_followups'     => $fuPend,
                    'meetings'              => $meetingCount,
                    'meetings_done'         => $meetingDone,
                    'messages'              => $msgCount,
                    'avg_response_mins'     => $avgResponseMins,
                    'avg_response_fmt'      => $avgResponseMins !== null ? ($avgResponseMins < 60 ? $avgResponseMins . ' min' : round($avgResponseMins / 60, 1) . ' hr') : '—',
                    'conversion_rate'       => $convRate,
                    'performance_score'     => $perfScore,
                    'telecaller_breakdown'  => $telecallerBreakdown,
                ];
            })->sortByDesc('performance_score')->values();

        $n = $rows->count();
        $summary = [
            'total_managers'     => $n,
            'total_leads'        => $rows->sum('assigned'),
            'total_converted'    => $rows->sum('converted'),
            'total_calls'        => $rows->sum('calls'),
            'total_talk_mins'    => $rows->sum('total_talk_mins'),
            'total_talk_fmt'     => sprintf('%dh %dm', floor($rows->sum('total_talk_mins') / 60), (int) $rows->sum('total_talk_mins') % 60),
            'total_meetings'     => $rows->sum('meetings'),
            'total_messages'     => $rows->sum('messages'),
            'total_pending_fu'   => $rows->sum('pending_followups'),
            'avg_conversion'     => $n > 0 ? round($rows->avg('conversion_rate'), 1) : 0,
            'avg_followup_rate'  => $n > 0 ? round($rows->avg('followup_rate'), 1) : 0,
            'avg_answer_rate'    => $n > 0 ? round($rows->avg('answer_rate'), 1) : 0,
            'top_manager'        => $rows->first()['name'] ?? '—',
            'top_score'          => $rows->first()['performance_score'] ?? 0,
        ];

        $perfDist = [
            'high'    => $rows->where('performance_score', '>=', 70)->count(),
            'average' => $rows->whereBetween('performance_score', [40, 69.9])->count(),
            'low'     => $rows->where('performance_score', '<', 40)->count(),
        ];

        // Monthly trend — last 6 months
        $monthLabels    = [];
        $monthAssigned  = [];
        $monthConverted = [];
        $monthCalls     = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $monthLabels[] = $mStart->format('M Y');
            $q = Lead::whereHas('assignedBy', fn($q) => $q->where('role', 'manager'))
                ->whereBetween('created_at', [$mStart, $mEnd]);
            if ($filters['manager'] !== 'all') {
                $q->where('assigned_by', (int) $filters['manager']);
            }
            if ($filters['source'] !== 'all') {
                $q->where('source', $filters['source']);
            }
            $monthAssigned[]  = (clone $q)->count();
            $monthConverted[] = (clone $q)->where('status', 'converted')->count();
            $mLeadIds = (clone $q)->pluck('id');
            $monthCalls[] = CallLog::whereIn('lead_id', $mLeadIds)->count();
        }

        $title        = 'Manager Performance';
        $tableHeaders = ['Rank', 'Manager', 'Grade', 'Team', 'Assigned', 'Converted', 'Active', 'Lost', 'Calls', 'Inbound', 'Outbound', 'Missed', 'Answer %', 'Talk Time', 'Meetings', 'Messages', 'Followup %', 'Pending F/U', 'Avg Response', 'Conv %', 'Score'];
        $tableRows    = $rows->map(fn($r, $i) => [
            '#' . ($i + 1), $r['name'], $r['grade'], $r['team_size'],
            $r['assigned'], $r['converted'], $r['active'], $r['lost'],
            $r['calls'], $r['calls_inbound'], $r['calls_outbound'], $r['calls_missed'],
            $r['answer_rate'] . '%', $r['total_talk_mins'] . ' min',
            $r['meetings'], $r['messages'],
            $r['followup_rate'] . '%', $r['pending_followups'],
            $r['avg_response_fmt'], $r['conversion_rate'] . '%', $r['performance_score'],
        ])->all();

        return view('admin.reports.manager_performance', compact(
            'title', 'rows', 'filters', 'filterOptions', 'summary', 'perfDist',
            'tableHeaders', 'tableRows', 'monthLabels', 'monthAssigned', 'monthConverted', 'monthCalls'
        ));
    }

    public function conversion(Request $request)
    {
        [$filters, $filterOptions, $startAt, $endAt] = $this->base($request);

        $q = Lead::whereBetween('leads.created_at', [$startAt, $endAt]);
        if ($filters['source'] !== 'all')     $q->where('source', $filters['source']);
        if ($filters['telecaller'] !== 'all') $q->where('assigned_to', (int) $filters['telecaller']);
        if ($filters['manager'] !== 'all')    $q->where('assigned_by', (int) $filters['manager']);

        $total     = (clone $q)->count();
        $converted = (clone $q)->where('status', 'converted')->count();
        $contacted = (clone $q)->whereIn('status', ['contacted', 'interested', 'converted', 'follow_up'])->count();
        $convRate    = $total > 0 ? round(($converted / $total) * 100, 2) : 0;
        $contactRate = $total > 0 ? round(($contacted / $total) * 100, 2) : 0;

        // Avg days to convert
        $convLeads = (clone $q)->where('status', 'converted')->select('created_at', 'updated_at')->get();
        $avgDaysToConvert = $convLeads->count() > 0
            ? round($convLeads->avg(fn($l) => $l->created_at->diffInDays($l->updated_at)), 1)
            : null;

        // Lead velocity (avg per day in period)
        $dayCount = max(1, $startAt->diffInDays($endAt) + 1);
        $velocity = round($total / $dayCount, 1);

        $summary = [
            'total'            => $total,
            'converted'        => $converted,
            'contacted'        => $contacted,
            'convRate'         => $convRate,
            'contactRate'      => $contactRate,
            'avgDaysToConvert' => $avgDaysToConvert,
            'velocity'         => $velocity,
        ];

        // Known acquisition channels — anything not in this list is treated as a course name
        $knownSources = [
            'manual', 'Manual', 'Landing Page', 'landing_page', 'landing page',
            'Facebook Ads', 'Facebook Lead Ads', 'facebook_ads', 'facebook ads',
            'Instagram', 'Instagram Lead Ads', 'instagram_ads', 'instagram',
            'Google Ads', 'google_ads', 'google ads', 'Google AdWords',
            'Walk-in', 'walk-in', 'walkin', 'Walk In', 'walk in',
            'Referral', 'referral',
            'meta_ads', 'Meta Ads',
            'website', 'Website',
            'WhatsApp', 'whatsapp',
            'Cold Call', 'cold_call', 'cold call',
            'Email', 'email',
            'Direct', 'direct',
            'SMS', 'sms',
        ];

        // Source breakdown — acquisition channels only
        $sourceRows = (clone $q)
            ->whereIn('source', $knownSources)
            ->selectRaw("LOWER(source) as src, COUNT(*) as total, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted")
            ->groupByRaw('LOWER(source)')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => (object)[
                'source'    => ucwords(str_replace('_', ' ', $r->src)),
                'total'     => (int) $r->total,
                'converted' => (int) $r->converted,
                'rate'      => $r->total > 0 ? round(($r->converted / $r->total) * 100, 2) : 0,
            ]);

        // Course breakdown — old-style (course name stored in source) + new-style (course_id FK)
        $courseFromSource = (clone $q)
            ->whereNull('course_id')
            ->whereNotIn('source', $knownSources)
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->selectRaw("source as course_name, COUNT(*) as total, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted")
            ->groupBy('source')
            ->get();

        $courseFromId = (clone $q)
            ->whereNotNull('course_id')
            ->join('courses', 'leads.course_id', '=', 'courses.id')
            ->selectRaw("courses.name as course_name, COUNT(*) as total, SUM(CASE WHEN leads.status='converted' THEN 1 ELSE 0 END) as converted")
            ->groupBy('courses.id', 'courses.name')
            ->get();

        $courseRows = $courseFromSource->concat($courseFromId)
            ->groupBy('course_name')
            ->map(fn($group) => (object)[
                'course'    => $group->first()->course_name,
                'total'     => (int) $group->sum('total'),
                'converted' => (int) $group->sum('converted'),
                'rate'      => $group->sum('total') > 0 ? round(($group->sum('converted') / $group->sum('total')) * 100, 2) : 0,
            ])
            ->sortByDesc('total')
            ->values();

        // Telecaller leaderboard
        $tcIds = (clone $q)->whereNotNull('assigned_to')->distinct('assigned_to')->pluck('assigned_to');
        $telecallerRows = User::whereIn('id', $tcIds)->get(['id', 'name'])
            ->map(function ($u) use ($q) {
                $tcQ   = (clone $q)->where('assigned_to', $u->id);
                $tcTot = (clone $tcQ)->count();
                $tcCon = (clone $tcQ)->where('status', 'converted')->count();
                return (object)[
                    'name'      => $u->name,
                    'total'     => $tcTot,
                    'converted' => $tcCon,
                    'rate'      => $tcTot > 0 ? round(($tcCon / $tcTot) * 100, 2) : 0,
                ];
            })->sortByDesc('rate')->values();

        // Pipeline age health — active (unconverted) leads
        $activePipeline = (clone $q)->whereNotIn('status', ['converted', 'lost', 'disqualified'])->select('created_at')->get();
        $ageHealth = ['fresh' => 0, 'warm' => 0, 'aging' => 0, 'stale' => 0];
        foreach ($activePipeline as $lead) {
            $d = $lead->created_at->diffInDays(now());
            if ($d <= 7)       $ageHealth['fresh']++;
            elseif ($d <= 30)  $ageHealth['warm']++;
            elseif ($d <= 60)  $ageHealth['aging']++;
            else               $ageHealth['stale']++;
        }

        // Monthly trend — last 6 months
        [$monthLabels, $monthRate, $monthContacted, $monthVolume] = [[], [], [], []];
        for ($i = 5; $i >= 0; $i--) {
            $mS = now()->subMonths($i)->startOfMonth();
            $mE = now()->subMonths($i)->endOfMonth();
            $monthLabels[] = $mS->format('M Y');
            $mQ = Lead::whereBetween('created_at', [$mS, $mE]);
            if ($filters['source'] !== 'all')     $mQ->where('source', $filters['source']);
            if ($filters['telecaller'] !== 'all') $mQ->where('assigned_to', (int) $filters['telecaller']);
            if ($filters['manager'] !== 'all')    $mQ->where('assigned_by', (int) $filters['manager']);
            $mTot  = (clone $mQ)->count();
            $mCon  = (clone $mQ)->where('status', 'converted')->count();
            $mCont = (clone $mQ)->whereIn('status', ['contacted', 'interested', 'converted', 'follow_up'])->count();
            $monthVolume[]    = $mTot;
            $monthRate[]      = $mTot > 0 ? round(($mCon  / $mTot) * 100, 1) : 0;
            $monthContacted[] = $mTot > 0 ? round(($mCont / $mTot) * 100, 1) : 0;
        }

        // Day-of-week pattern
        $dowRaw = (clone $q)
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as total, SUM(CASE WHEN status="converted" THEN 1 ELSE 0 END) as converted')
            ->groupBy('dow')->orderBy('dow')->get()->keyBy('dow');
        $dowMap    = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat', 1 => 'Sun'];
        $dowLabels = $dowTotal = $dowConv = [];
        foreach ($dowMap as $dow => $label) {
            $dowLabels[] = $label;
            $dowTotal[]  = (int) ($dowRaw[$dow]->total     ?? 0);
            $dowConv[]   = (int) ($dowRaw[$dow]->converted ?? 0);
        }

        // Smart insights
        $insights = [];
        if ($convRate < 5 && $total >= 10) {
            $insights[] = ['type' => 'danger',  'icon' => 'trending_down',     'text' => "Conversion rate is only {$convRate}% — well below the 10%+ benchmark. Review follow-up cadence and lead quality."];
        } elseif ($convRate >= 15) {
            $insights[] = ['type' => 'success', 'icon' => 'emoji_events',      'text' => "Excellent conversion rate of {$convRate}%! Your team is performing above industry benchmarks."];
        }
        if ($contactRate < 40 && $total >= 10) {
            $insights[] = ['type' => 'warning', 'icon' => 'phone_missed',      'text' => "Only {$contactRate}% of leads have been contacted. First contact within 30 minutes increases conversion 3×."];
        }
        if ($ageHealth['stale'] > 0) {
            $sp = $total > 0 ? round(($ageHealth['stale'] / $total) * 100) : 0;
            $insights[] = ['type' => 'warning', 'icon' => 'hourglass_disabled', 'text' => "{$ageHealth['stale']} leads ({$sp}%) are stale (60+ days old). Close or reassign to keep the pipeline healthy."];
        }
        $topSrc = $sourceRows->sortByDesc('rate')->first();
        if ($topSrc && $topSrc->rate > 0) {
            $insights[] = ['type' => 'success', 'icon' => 'star', 'text' => "Best-converting source: '{$topSrc->source}' at {$topSrc->rate}%. Increase volume from this channel."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Expand the date range or remove filters to see more actionable insights.'];
        }

        // Export table data (used by generic export handler)
        $title        = 'Conversion Report';
        $tableHeaders = ['Status', 'Count', 'Share'];
        $statusRows   = (clone $q)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get();
        $tableRows    = $statusRows->map(fn($r) => [
            ucfirst(str_replace('_', ' ', $r->status)),
            (int) $r->total,
            $total > 0 ? round(($r->total / $total) * 100, 1) . '%' : '0%',
        ])->all();

        return view('admin.reports.conversion', compact(
            'filters', 'filterOptions', 'summary', 'sourceRows', 'courseRows', 'telecallerRows',
            'ageHealth', 'monthLabels', 'monthRate', 'monthContacted', 'monthVolume',
            'dowLabels', 'dowTotal', 'dowConv', 'insights',
            'title', 'tableHeaders', 'tableRows'
        ));
    }

    public function sourcePerformance(Request $request)
    {
        [$filters, $filterOptions, $startAt, $endAt] = $this->base($request);

        $courseNames = Course::pluck('name')->toArray();

        // Override sources dropdown to exclude course names
        $filterOptions['sources'] = Lead::whereNotNull('source')
            ->where('source', '!=', '')
            ->whereNotIn('source', $courseNames)
            ->orderBy('source')->distinct()->pluck('source');

        $baseQ = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->whereNotIn('source', $courseNames)
            ->when($filters['manager'] !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']));

        $raw = (clone $baseQ)
            ->select(
                'source',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw("SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted_leads"),
                DB::raw("SUM(CASE WHEN status NOT IN ('converted','lost','disqualified') THEN 1 ELSE 0 END) as active_leads"),
                DB::raw("SUM(CASE WHEN status='lost' THEN 1 ELSE 0 END) as lost_leads")
            )
            ->groupBy('source')
            ->orderByDesc('total_leads')
            ->get();

        $totalAll = (int) $raw->sum('total_leads');

        $convertedLeads = (clone $baseQ)->where('status', 'converted')
            ->select('source', 'created_at', 'updated_at')->get();
        $avgDaysBySource = $convertedLeads->groupBy('source')->map(
            fn($g) => round($g->avg(fn($l) => $l->created_at->diffInDays($l->updated_at)), 1)
        );
        $avgDaysAll = $convertedLeads->count() > 0
            ? round($convertedLeads->avg(fn($l) => $l->created_at->diffInDays($l->updated_at)), 1)
            : null;

        $rows = $raw->map(function ($r) use ($totalAll, $avgDaysBySource) {
            $total     = (int) $r->total_leads;
            $converted = (int) $r->converted_leads;
            $rate      = $total > 0 ? round(($converted / $total) * 100, 2) : 0;
            $share     = $totalAll > 0 ? round(($total / $totalAll) * 100, 1) : 0;
            return [
                'source'   => $r->source ?? 'Unknown',
                'total'    => $total,
                'converted'=> $converted,
                'active'   => (int) $r->active_leads,
                'lost'     => (int) $r->lost_leads,
                'rate'     => $rate,
                'share'    => $share,
                'avg_days' => $avgDaysBySource[$r->source] ?? null,
                'grade'    => $rate >= 10 ? 'A' : ($rate >= 5 ? 'B' : ($rate >= 1 ? 'C' : 'D')),
            ];
        });

        // ── Course enquiry analytics (grouped by course_id via join) ──
        $courseRaw = Lead::from('leads')
            ->join('courses', 'leads.course_id', '=', 'courses.id')
            ->whereBetween('leads.created_at', [$startAt, $endAt])
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('leads.assigned_by', (int) $filters['manager']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('leads.assigned_to', (int) $filters['telecaller']))
            ->select(
                'leads.course_id',
                'courses.name as course_name',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw("SUM(CASE WHEN leads.status='converted' THEN 1 ELSE 0 END) as converted_leads"),
                DB::raw("SUM(CASE WHEN leads.status NOT IN ('converted','lost','disqualified') THEN 1 ELSE 0 END) as active_leads"),
                DB::raw("SUM(CASE WHEN leads.status='lost' THEN 1 ELSE 0 END) as lost_leads"),
                DB::raw("AVG(CASE WHEN leads.status='converted' THEN DATEDIFF(leads.updated_at, leads.created_at) ELSE NULL END) as avg_days")
            )
            ->groupBy('leads.course_id', 'courses.name')
            ->orderByDesc('total_leads')
            ->get();

        $courseTotalAll = (int) $courseRaw->sum('total_leads');
        $courseRows = $courseRaw->map(function ($r) use ($courseTotalAll) {
            $total     = (int) $r->total_leads;
            $converted = (int) $r->converted_leads;
            $rate      = $total > 0 ? round(($converted / $total) * 100, 2) : 0;
            $share     = $courseTotalAll > 0 ? round(($total / $courseTotalAll) * 100, 1) : 0;
            return [
                'course'   => $r->course_name ?? 'Unknown Course',
                'total'    => $total,
                'converted'=> $converted,
                'active'   => (int) $r->active_leads,
                'lost'     => (int) $r->lost_leads,
                'rate'     => $rate,
                'share'    => $share,
                'avg_days' => $r->avg_days !== null ? round($r->avg_days, 1) : null,
                'grade'    => $rate >= 10 ? 'A' : ($rate >= 5 ? 'B' : ($rate >= 1 ? 'C' : 'D')),
            ];
        });

        $totalConverted = $rows->sum('converted');
        $overallRate    = $totalAll > 0 ? round(($totalConverted / $totalAll) * 100, 2) : 0;
        $bestSource     = $rows->where('total', '>=', 3)->sortByDesc('rate')->first();
        $topVolSource   = $rows->sortByDesc('total')->first();

        $summary = [
            'totalSources'   => $rows->count(),
            'totalLeads'     => $totalAll,
            'totalConverted' => $totalConverted,
            'overallRate'    => $overallRate,
            'bestSource'     => $bestSource,
            'topVolSource'   => $topVolSource,
            'avgDaysAll'     => $avgDaysAll,
            'gradeCounts'    => [
                'A' => $rows->where('grade', 'A')->count(),
                'B' => $rows->where('grade', 'B')->count(),
                'C' => $rows->where('grade', 'C')->count(),
                'D' => $rows->where('grade', 'D')->count(),
            ],
        ];

        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthLabels[] = now()->subMonths($i)->startOfMonth()->format('M Y');
        }
        $top5Sources     = $rows->take(5)->pluck('source');
        $sourceMonthData = $top5Sources->map(function ($source) use ($filters) {
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = now()->subMonths($i)->startOfMonth();
                $mEnd   = now()->subMonths($i)->endOfMonth();
                $q      = Lead::where('source', $source)->whereBetween('created_at', [$mStart, $mEnd]);
                if ($filters['manager'] !== 'all')    $q->where('assigned_by', (int) $filters['manager']);
                if ($filters['telecaller'] !== 'all') $q->where('assigned_to', (int) $filters['telecaller']);
                $data[] = $q->count();
            }
            return ['source' => $source, 'data' => $data];
        })->values();

        $insights = [];
        if ($bestSource) {
            $insights[] = ['type' => 'success', 'icon' => 'star', 'text' => "Best converting source: '{$bestSource['source']}' at {$bestSource['rate']}%. Prioritise this channel for higher ROI."];
        }
        if ($overallRate < 5 && $totalAll >= 10) {
            $insights[] = ['type' => 'danger', 'icon' => 'trending_down', 'text' => "Overall conversion rate is only {$overallRate}% — review lead quality and follow-up cadence."];
        }
        $dSources = $rows->where('grade', 'D')->count();
        if ($dSources > 0) {
            $insights[] = ['type' => 'warning', 'icon' => 'warning', 'text' => "{$dSources} source(s) have less than 1% conversion (Grade D). Consider reducing investment in these channels."];
        }
        if ($topVolSource && $topVolSource['rate'] < 2 && $topVolSource['total'] >= 5) {
            $insights[] = ['type' => 'warning', 'icon' => 'volume_up', 'text' => "Highest-volume source '{$topVolSource['source']}' has only {$topVolSource['rate']}% conversion — quality improvement needed."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Expand the date range or remove filters to see more actionable insights.'];
        }

        $title        = 'Lead Source Report';
        $tableHeaders = ['#', 'Source', 'Total', 'Converted', 'Active', 'Lost', 'Conv %', 'Share %', 'Avg Days', 'Grade'];
        $tableRows    = $rows->map(fn($r, $i) => [
            '#' . ($i + 1), $r['source'], $r['total'], $r['converted'], $r['active'], $r['lost'],
            $r['rate'] . '%', $r['share'] . '%', $r['avg_days'] ?? '—', $r['grade'],
        ])->values()->all();

        return view('admin.reports.lead_source', compact(
            'rows', 'courseRows', 'filters', 'filterOptions', 'summary',
            'monthLabels', 'sourceMonthData', 'insights',
            'title', 'tableHeaders', 'tableRows'
        ));
    }

    public function period(Request $request)
    {
        [$filters, $filterOptions, $startAt, $endAt] = $this->base($request);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startAt = \Carbon\Carbon::parse($request->from_date)->startOfDay();
            $endAt   = \Carbon\Carbon::parse($request->to_date)->endOfDay();
            $filters['date_range'] = 'custom';
            $filters['from_date']  = $request->from_date;
            $filters['to_date']    = $request->to_date;
        } else {
            $filters['from_date'] = $startAt->format('Y-m-d');
            $filters['to_date']   = $endAt->format('Y-m-d');
        }

        $baseQ = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source']     !== 'all', fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager']    !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']));

        $dailyRows = (clone $baseQ)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(CASE WHEN status="converted" THEN 1 ELSE 0 END) as converted')
            ->groupBy('day')->orderBy('day')->get()
            ->map(fn($r) => [
                'day'       => $r->day,
                'dow'       => \Carbon\Carbon::parse($r->day)->format('D'),
                'total'     => (int) $r->total,
                'converted' => (int) $r->converted,
                'rate'      => $r->total > 0 ? round(($r->converted / $r->total) * 100, 1) : 0,
            ]);

        $totalLeads     = $dailyRows->sum('total');
        $totalConverted = $dailyRows->sum('converted');
        $activeDays     = $dailyRows->count();
        $overallRate    = $totalLeads > 0 ? round(($totalConverted / $totalLeads) * 100, 1) : 0;
        $avgPerDay      = $activeDays > 0 ? round($totalLeads / $activeDays, 1) : 0;
        $peakRow        = $dailyRows->sortByDesc('total')->first();
        $bestConvRow    = $dailyRows->where('converted', '>', 0)->sortByDesc('rate')->first();
        $topDays        = $dailyRows->sortByDesc('total')->take(5)->values();
        $belowAvg       = $avgPerDay > 0 ? $dailyRows->filter(fn($r) => $r['total'] < $avgPerDay)->count() : 0;

        $weeklyRows = (clone $baseQ)
            ->selectRaw('YEARWEEK(created_at,1) as yw, MIN(DATE(created_at)) as week_start, COUNT(*) as total, SUM(CASE WHEN status="converted" THEN 1 ELSE 0 END) as converted')
            ->groupBy('yw')->orderBy('yw')->get()
            ->map(fn($r) => [
                'label'     => 'W/C ' . \Carbon\Carbon::parse($r->week_start)->format('d M'),
                'total'     => (int) $r->total,
                'converted' => (int) $r->converted,
            ]);

        $dowRaw = (clone $baseQ)
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as total, SUM(CASE WHEN status="converted" THEN 1 ELSE 0 END) as converted')
            ->groupBy('dow')->orderBy('dow')->get()->keyBy('dow');
        $dowLabels = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $dowTotal = $dowConv = [];
        for ($d = 1; $d <= 7; $d++) {
            $dowTotal[] = (int) ($dowRaw[$d]->total     ?? 0);
            $dowConv[]  = (int) ($dowRaw[$d]->converted ?? 0);
        }

        $cumulative = [];
        $running    = 0;
        foreach ($dailyRows as $row) { $running += $row['total']; $cumulative[] = $running; }

        $insights = [];
        if ($overallRate < 5 && $totalLeads >= 10) {
            $insights[] = ['type' => 'danger',  'icon' => 'trending_down', 'text' => "Overall conversion rate is only {$overallRate}% in this period — review follow-up cadence and lead quality."];
        }
        if ($peakRow && $avgPerDay > 0 && $peakRow['total'] > $avgPerDay * 3) {
            $insights[] = ['type' => 'warning', 'icon' => 'bolt', 'text' => "Peak day ({$peakRow['day']}) recorded {$peakRow['total']} leads — over 3× the daily average. Investigate the traffic source."];
        }
        if ($bestConvRow) {
            $insights[] = ['type' => 'success', 'icon' => 'star', 'text' => "Best conversion day: {$bestConvRow['day']} at {$bestConvRow['rate']}%. Replicate the conditions of that day."];
        }
        $dowPeak = !empty($dowTotal) ? array_search(max($dowTotal), $dowTotal) : false;
        if ($dowPeak !== false && ($dowTotal[$dowPeak] ?? 0) > 0) {
            $insights[] = ['type' => 'info', 'icon' => 'schedule', 'text' => "Most leads arrive on {$dowLabels[$dowPeak]}. Plan team capacity and follow-ups accordingly."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Expand the date range or remove filters to see more actionable insights.'];
        }

        $summary = [
            'totalLeads'     => $totalLeads,
            'totalConverted' => $totalConverted,
            'activeDays'     => $activeDays,
            'overallRate'    => $overallRate,
            'avgPerDay'      => $avgPerDay,
            'peakRow'        => $peakRow,
            'bestConvRow'    => $bestConvRow,
            'belowAvg'       => $belowAvg,
            'dateFrom'       => $startAt->format('d M Y'),
            'dateTo'         => $endAt->format('d M Y'),
        ];

        $title        = 'Daily / Weekly / Monthly Report';
        $tableHeaders = ['Date', 'Day', 'Total Leads', 'Converted', 'Conv. Rate'];
        $tableRows    = $dailyRows->sortByDesc('total')->map(fn($r) => [
            $r['day'], $r['dow'], $r['total'], $r['converted'], $r['rate'] . '%',
        ])->values()->all();

        return view('admin.reports.period', compact(
            'filters', 'filterOptions', 'summary', 'dailyRows',
            'weeklyRows', 'dowLabels', 'dowTotal', 'dowConv', 'cumulative', 'topDays',
            'insights', 'title', 'tableHeaders', 'tableRows'
        ));
    }

    public function callEfficiency(Request $request)
    {
        [$filters, $filterOptions, $startAt, $endAt] = $this->base($request);
        $filters['outcome']   = $request->get('outcome', 'all');
        $filters['min_calls'] = $request->get('min_calls', '0');

        $filterOptions['outcomes'] = CallLog::whereNotNull('outcome')
            ->select('outcome')->distinct()->orderBy('outcome')->pluck('outcome');

        $baseCallQ = CallLog::whereBetween('created_at', [$startAt, $endAt])
            ->whereNotNull('user_id')
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('user_id', (int) $filters['telecaller']))
            ->when($filters['outcome'] !== 'all', fn($q) => $q->where('outcome', $filters['outcome']));

        $rows = (clone $baseCallQ)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_calls'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls"),
                DB::raw("SUM(CASE WHEN status IN ('no-answer','busy','failed','canceled','missed') THEN 1 ELSE 0 END) as missed_calls"),
                DB::raw('COALESCE(AVG(NULLIF(duration,0)), 0) as avg_duration'),
                DB::raw('SUM(duration) as total_duration')
            )
            ->groupBy('user_id')
            ->get()
            ->map(function ($r) {
                $name    = User::find($r->user_id)?->name ?? 'N/A';
                $rate    = $r->total_calls > 0 ? round(($r->completed_calls / $r->total_calls) * 100, 2) : 0;
                $avgDur  = round((float) $r->avg_duration, 2);
                $talkMin = round((int) $r->total_duration / 60, 1);
                return [
                    'user_id'        => $r->user_id,
                    'name'           => $name,
                    'total'          => (int) $r->total_calls,
                    'completed'      => (int) $r->completed_calls,
                    'missed'         => (int) $r->missed_calls,
                    'avg'            => $avgDur,
                    'avg_fmt'        => sprintf('%02d:%02d', floor($avgDur / 60), (int) $avgDur % 60),
                    'total_duration' => (int) $r->total_duration,
                    'talk_mins'      => $talkMin,
                    'talk_fmt'       => sprintf('%dh %dm', floor($talkMin / 60), (int) $talkMin % 60),
                    'rate'           => $rate,
                    'grade'          => $rate >= 70 ? 'A' : ($rate >= 50 ? 'B' : ($rate >= 30 ? 'C' : 'D')),
                ];
            })
            ->when((int) $filters['min_calls'] > 0, fn($c) => $c->where('total', '>=', (int) $filters['min_calls']))
            ->sortByDesc('rate')
            ->values();

        $totalCalls     = $rows->sum('total');
        $totalCompleted = $rows->sum('completed');
        $totalMissed    = $rows->sum('missed');
        $totalDurSec    = $rows->sum('total_duration');
        $overallRate    = $totalCalls > 0 ? round(($totalCompleted / $totalCalls) * 100, 1) : 0;
        $avgDurSec      = $totalCalls > 0 ? round($totalDurSec / $totalCalls, 1) : 0;
        $totalTalkMins  = round($totalDurSec / 60, 1);
        $topPerformer   = $rows->sortByDesc('rate')->first();
        $highVolume     = $rows->sortByDesc('total')->first();

        $summary = [
            'total_calls'      => $totalCalls,
            'total_completed'  => $totalCompleted,
            'total_missed'     => $totalMissed,
            'overall_rate'     => $overallRate,
            'avg_dur_fmt'      => sprintf('%02d:%02d', floor($avgDurSec / 60), (int) $avgDurSec % 60),
            'total_talk_mins'  => $totalTalkMins,
            'total_talk_fmt'   => sprintf('%dh %dm', floor($totalTalkMins / 60), (int) $totalTalkMins % 60),
            'top_performer'    => $topPerformer['name'] ?? '—',
            'top_rate'         => $topPerformer['rate'] ?? 0,
            'top_volume'       => $highVolume['name'] ?? '—',
            'top_volume_calls' => $highVolume['total'] ?? 0,
            'telecaller_count' => $rows->count(),
        ];

        $perfDist = [
            'high'    => $rows->where('rate', '>=', 70)->count(),
            'average' => $rows->whereBetween('rate', [40, 69.9])->count(),
            'low'     => $rows->where('rate', '<', 40)->count(),
        ];

        $dailyTrend = (clone $baseCallQ)
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('day')->orderBy('day')->get()
            ->map(fn($r) => ['day' => $r->day, 'total' => (int)$r->total, 'completed' => (int)$r->completed, 'rate' => $r->total > 0 ? round(($r->completed / $r->total) * 100, 1) : 0]);

        $hourlyRaw = (clone $baseCallQ)
            ->selectRaw("HOUR(created_at) as hour, COUNT(*) as total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('hour')->orderBy('hour')->get()->keyBy('hour');
        $hourlyLabels = $hourlyTotal = $hourlyCompleted = [];
        for ($h = 8; $h <= 20; $h++) {
            $hourlyLabels[]    = sprintf('%02d:00', $h);
            $hourlyTotal[]     = (int)($hourlyRaw[$h]->total     ?? 0);
            $hourlyCompleted[] = (int)($hourlyRaw[$h]->completed ?? 0);
        }

        $statusBreakdown  = (clone $baseCallQ)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get();
        $outcomeBreakdown = (clone $baseCallQ)->whereNotNull('outcome')->select('outcome', DB::raw('COUNT(*) as total'))->groupBy('outcome')->orderByDesc('total')->get();

        $insights = [];
        if ($overallRate < 50 && $totalCalls >= 10) {
            $insights[] = ['type' => 'warning', 'icon' => 'warning', 'text' => "Overall completion rate is {$overallRate}% — below 50%. Review call strategies and agent training."];
        }
        if ($perfDist['low'] > 0) {
            $insights[] = ['type' => 'danger', 'icon' => 'trending_down', 'text' => "{$perfDist['low']} telecaller(s) have completion rate below 40%. Individual coaching sessions recommended."];
        }
        if ($topPerformer && $topPerformer['rate'] > 70) {
            $insights[] = ['type' => 'success', 'icon' => 'emoji_events', 'text' => "{$topPerformer['name']} leads with {$topPerformer['rate']}% completion rate. Share their workflow as a best practice."];
        }
        $peakIdx = !empty($hourlyTotal) ? array_search(max($hourlyTotal), $hourlyTotal) : false;
        if ($peakIdx !== false && $hourlyTotal[$peakIdx] > 0) {
            $insights[] = ['type' => 'info', 'icon' => 'schedule', 'text' => "Peak call activity is at {$hourlyLabels[$peakIdx]} — schedule priority follow-ups during this window."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Not enough data for insights. Try broadening the time period or removing filters.'];
        }

        $title        = 'Call Efficiency Report';
        $tableHeaders = ['Telecaller', 'Total Calls', 'Completed', 'Missed', 'Avg Duration', 'Talk Time', 'Completion %', 'Grade'];
        $tableRows    = $rows->map(fn($r) => [$r['name'], $r['total'], $r['completed'], $r['missed'], $r['avg_fmt'], $r['talk_fmt'], $r['rate'] . '%', $r['grade']])->all();

        return view('admin.reports.call_efficiency', compact(
            'title', 'rows', 'filters', 'filterOptions', 'summary', 'perfDist',
            'dailyTrend', 'hourlyLabels', 'hourlyTotal', 'hourlyCompleted',
            'statusBreakdown', 'outcomeBreakdown', 'insights',
            'tableHeaders', 'tableRows'
        ));
    }

    public function responseTime(Request $request)
    {
        [$filters, $filterOptions, $startAt, $endAt] = $this->base($request);
        $filters['rt_bucket'] = $request->get('rt_bucket', 'all');
        $filters['sort']      = $request->get('sort', 'newest');
        $perPage              = 25;
        $page                 = max(1, (int) $request->get('page', 1));
        $slaMinutes           = 30;

        $leadQ = Lead::with(['assignedUser', 'assignedBy'])
            ->whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source'] !== 'all',     fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->latest('id')
            ->get(['id', 'lead_code', 'name', 'assigned_to', 'assigned_by', 'created_at', 'source']);

        $firstMap = LeadActivity::whereIn('lead_activities.lead_id', $leadQ->pluck('id'))
            ->whereNotNull('lead_activities.user_id')
            ->whereNotIn('lead_activities.type', ['assignment'])
            ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
            ->whereRaw('lead_activities.created_at > leads.created_at')
            ->select('lead_activities.lead_id', DB::raw('MIN(lead_activities.created_at) as first_response_at'))
            ->groupBy('lead_activities.lead_id')
            ->pluck('first_response_at', 'lead_activities.lead_id');

        $allRows = $leadQ->map(function ($lead) use ($firstMap) {
            $first   = $firstMap[$lead->id] ?? null;
            $minutes = $first ? $lead->created_at->diffInMinutes($first) : null;
            return [
                'lead_code'         => $lead->lead_code ?? '#' . $lead->id,
                'lead_name'         => $lead->name,
                'telecaller'        => $lead->assignedUser?->name ?? 'Unassigned',
                'manager'           => $lead->assignedBy?->name   ?? '—',
                'source'            => $lead->source ?? '—',
                'created_at'        => $lead->created_at?->format('Y-m-d H:i'),
                'first_response_at' => $first ? \Carbon\Carbon::parse($first)->format('Y-m-d H:i') : null,
                'response_minutes'  => $minutes,
            ];
        });

        $respondedRows  = $allRows->filter(fn($r) => $r['response_minutes'] !== null)->values();
        $neverResponded = $allRows->filter(fn($r) => $r['response_minutes'] === null)->values();
        $minutes        = $respondedRows->pluck('response_minutes');
        $totalLeads     = $allRows->count();
        $sortedMins     = $minutes->sort()->values();
        $avgMinutes     = $minutes->count() > 0 ? round($minutes->avg()) : null;
        $medianMinutes  = $sortedMins->count() > 0 ? $sortedMins->get((int) floor($sortedMins->count() / 2)) : null;
        $fastestMinutes = $minutes->count() > 0 ? (int) $minutes->min() : null;
        $slowestMinutes = $minutes->count() > 0 ? (int) $minutes->max() : null;
        $withinSla      = $respondedRows->filter(fn($r) => $r['response_minutes'] <= $slaMinutes)->count();
        $slaCompliance  = $totalLeads > 0 ? round(($withinSla / $totalLeads) * 100, 1) : 0;
        $responseRate   = $totalLeads > 0 ? round(($respondedRows->count() / $totalLeads) * 100, 1) : 0;
        $fmtMins        = fn($m) => $m === null ? '—' : ($m < 60 ? round($m, 2) . ' min' : round($m / 60, 1) . ' hr');

        $summary = [
            'total_leads'     => $totalLeads,
            'responded'       => $respondedRows->count(),
            'never_responded' => $neverResponded->count(),
            'response_rate'   => $responseRate,
            'avg_fmt'         => $fmtMins($avgMinutes),
            'avg_minutes'     => $avgMinutes,
            'median_fmt'      => $fmtMins($medianMinutes),
            'fastest_fmt'     => $fmtMins($fastestMinutes),
            'slowest_fmt'     => $fmtMins($slowestMinutes),
            'within_sla'      => $withinSla,
            'sla_compliance'  => $slaCompliance,
            'sla_minutes'     => $slaMinutes,
        ];

        $bucketDist = [
            ['label' => '<5 min',    'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] < 5)->count(),                                              'color' => '#10b981'],
            ['label' => '5–30 min',  'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 5   && $r['response_minutes'] < 30)->count(),           'color' => '#06b6d4'],
            ['label' => '30–60 min', 'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 30  && $r['response_minutes'] < 60)->count(),           'color' => '#f59e0b'],
            ['label' => '1–4 hr',    'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 60  && $r['response_minutes'] < 240)->count(),          'color' => '#f97316'],
            ['label' => '4–24 hr',   'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 240 && $r['response_minutes'] < 1440)->count(),         'color' => '#ef4444'],
            ['label' => '24h+',      'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 1440)->count(),                                          'color' => '#991b1b'],
            ['label' => 'No Response','count' => $neverResponded->count(),                                                                                          'color' => '#94a3b8'],
        ];

        $tcBreakdown = $allRows->groupBy('telecaller')->map(function ($grp, $tcName) use ($slaMinutes) {
            $resp   = $grp->filter(fn($r) => $r['response_minutes'] !== null);
            $avg    = $resp->count() > 0 ? round($resp->pluck('response_minutes')->avg()) : null;
            $within = $resp->filter(fn($r) => $r['response_minutes'] <= $slaMinutes)->count();
            return ['name' => $tcName, 'total' => $grp->count(), 'responded' => $resp->count(), 'avg_mins' => $avg, 'within_sla' => $within, 'sla_pct' => $grp->count() > 0 ? round(($within / $grp->count()) * 100, 1) : 0];
        })->sortByDesc('sla_pct')->values();

        $dailyTrend = $respondedRows->groupBy(fn($r) => substr($r['created_at'], 0, 10))
            ->map(fn($g, $d) => ['day' => $d, 'avg' => round($g->pluck('response_minutes')->avg()), 'count' => $g->count()])
            ->sortBy('day')->values();

        $insights = [];
        if ($slaCompliance < 50 && $totalLeads >= 5) {
            $insights[] = ['type' => 'danger',  'icon' => 'timer_off',    'text' => "Only {$slaCompliance}% of leads were contacted within {$slaMinutes} min SLA. Urgent action required."];
        }
        if ($neverResponded->count() > 0) {
            $np = $totalLeads > 0 ? round(($neverResponded->count() / $totalLeads) * 100) : 0;
            $insights[] = ['type' => 'warning', 'icon' => 'phone_missed', 'text' => "{$neverResponded->count()} leads ({$np}%) have received NO contact at all. Assign immediately."];
        }
        if ($avgMinutes !== null && $avgMinutes > 60) {
            $insights[] = ['type' => 'warning', 'icon' => 'schedule',     'text' => "Average response time is " . round($avgMinutes / 60, 1) . " hrs — high latency reduces conversion probability significantly."];
        }
        $bestTc = $tcBreakdown->filter(fn($t) => $t['responded'] >= 3)->sortBy('avg_mins')->first();
        if ($bestTc && $bestTc['avg_mins'] !== null) {
            $insights[] = ['type' => 'success', 'icon' => 'rocket_launch', 'text' => "{$bestTc['name']} has the fastest avg response of " . $fmtMins($bestTc['avg_mins']) . ". Use as benchmark for team coaching."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'check_circle', 'text' => 'Response times look healthy. Broaden the time range or remove filters to see more data.'];
        }

        $filteredRows = match ($filters['rt_bucket']) {
            'fast' => $allRows->filter(fn($r) => $r['response_minutes'] !== null && $r['response_minutes'] <= $slaMinutes),
            'slow' => $allRows->filter(fn($r) => $r['response_minutes'] !== null && $r['response_minutes'] >  $slaMinutes),
            'none' => $allRows->filter(fn($r) => $r['response_minutes'] === null),
            default => $allRows,
        };
        $sortedRows = match ($filters['sort']) {
            'asc'    => $filteredRows->sortBy('response_minutes'),
            'desc'   => $filteredRows->sortByDesc('response_minutes'),
            default  => $filteredRows,
        };
        $sortedRows = $sortedRows->values();

        $paginatedRows = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedRows->forPage($page, $perPage)->values(),
            $sortedRows->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $title        = 'Lead Response Time Report';
        $tableHeaders = ['Lead Code', 'Lead', 'Telecaller', 'Manager', 'Source', 'Created At', 'First Response', 'Response Time'];
        $tableRows    = $sortedRows->map(fn($r) => [
            $r['lead_code'], $r['lead_name'], $r['telecaller'], $r['manager'], $r['source'],
            $r['created_at'], $r['first_response_at'] ?? '—', $r['response_minutes'] !== null ? $fmtMins($r['response_minutes']) : '—',
        ])->all();

        return view('admin.reports.response_time', compact(
            'title', 'filters', 'filterOptions', 'summary', 'slaMinutes',
            'bucketDist', 'tcBreakdown', 'dailyTrend', 'insights',
            'paginatedRows', 'tableHeaders', 'tableRows', 'perPage'
        ));
    }

    public function telecallerLeadActivity(Request $request)
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'telecaller' => $request->get('telecaller', 'all'),
            'search'     => trim($request->get('search', '')),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);

        $filterOptions = [
            'sources'     => Lead::select('source')->distinct()->orderBy('source')->pluck('source'),
            'telecallers' => User::where('role', 'telecaller')->where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];

        $telecallers = collect();

        $tcQuery = User::where('role', 'telecaller')->where('status', 1)->orderBy('name');
        if ($filters['telecaller'] !== 'all') {
            $tcQuery->where('id', (int) $filters['telecaller']);
        }

        foreach ($tcQuery->get(['id', 'name']) as $tc) {
            $leadQ = Lead::where('assigned_to', $tc->id)
                ->whereBetween('created_at', [$startAt, $endAt]);
            if ($filters['source'] !== 'all') {
                $leadQ->where('source', $filters['source']);
            }
            if (!empty($filters['search'])) {
                $s = '%' . $filters['search'] . '%';
                $leadQ->where(function ($q) use ($s) {
                    $q->where('lead_code', 'like', $s)
                      ->orWhere('name', 'like', $s)
                      ->orWhere('email', 'like', $s)
                      ->orWhere('phone', 'like', $s);
                });
            }

            $leads = $leadQ->with(['enrolledCourse', 'finalCourse'])->orderByDesc('created_at')->get();
            $leadIds = $leads->pluck('id');

            // Call logs keyed by lead_id
            $callsByLead = CallLog::whereIn('lead_id', $leadIds)
                ->orderBy('created_at')
                ->get()
                ->groupBy('lead_id');

            // WhatsApp messages keyed by lead_id
            $msgsByLead = WhatsAppMessage::whereIn('lead_id', $leadIds)
                ->orderBy('created_at')
                ->get()
                ->groupBy('lead_id');

            // Meetings keyed by lead_id
            $meetingsByLead = LeadMeeting::whereIn('lead_id', $leadIds)
                ->orderBy('meeting_time')
                ->get()
                ->groupBy('lead_id');

            $leadsData = $leads->map(function ($lead) use ($callsByLead, $msgsByLead, $meetingsByLead) {
                $calls    = $callsByLead->get($lead->id, collect());
                $msgs     = $msgsByLead->get($lead->id, collect());
                $meetings = $meetingsByLead->get($lead->id, collect());

                return [
                    'id'           => $lead->id,
                    'lead_code'    => $lead->lead_code,
                    'name'         => $lead->name,
                    'phone'        => $lead->phone,
                    'status'       => $lead->status,
                    'source'       => $lead->source,
                    'course'       => $lead->enrolledCourse?->name ?? '—',
                    'final_course' => $lead->finalCourse?->name ?? '—',
                    'created_at'   => $lead->created_at?->format('d M Y'),
                    'calls'        => $calls->map(fn($c) => [
                        'date'       => $c->created_at?->format('d M Y H:i'),
                        'direction'  => $c->direction,
                        'status'     => $c->status,
                        'outcome'    => $c->outcome ?? '—',
                        'duration'   => $c->duration ? sprintf('%02d:%02d', floor($c->duration / 60), $c->duration % 60) : '—',
                    ])->values()->all(),
                    'messages'     => $msgs->map(fn($m) => [
                        'date'      => ($m->sent_at ?? $m->created_at)?->format('d M Y H:i'),
                        'direction' => $m->direction,
                        'body'      => $m->message_body ?? $m->message ?? '',
                        'type'      => $m->media_type ?? 'text',
                    ])->values()->all(),
                    'meetings'     => $meetings->map(fn($mt) => [
                        'title'  => $mt->title,
                        'time'   => $mt->meeting_time?->format('d M Y H:i'),
                        'type'   => $mt->meeting_type ?? '—',
                        'status' => $mt->status,
                        'notes'  => $mt->notes ?? '—',
                    ])->values()->all(),
                    'call_count'    => $calls->count(),
                    'msg_count'     => $msgs->count(),
                    'meeting_count' => $meetings->count(),
                ];
            });

            $telecallers->push([
                'id'    => $tc->id,
                'name'  => $tc->name,
                'leads' => $leadsData,
            ]);
        }

        return view('admin.reports.telecaller_lead_activity', compact(
            'filters', 'filterOptions', 'telecallers', 'startAt', 'endAt'
        ));
    }

    public function exportLeadActivity(Request $request, string $format)
    {
        if (!in_array($format, ['pdf', 'excel'], true)) {
            abort(404);
        }

        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'telecaller' => $request->get('telecaller', 'all'),
            'search'     => trim($request->get('search', '')),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);

        $tcQuery = User::where('role', 'telecaller')->where('status', 1)->orderBy('name');
        if ($filters['telecaller'] !== 'all') {
            $tcQuery->where('id', (int) $filters['telecaller']);
        }

        $telecallers = collect();
        foreach ($tcQuery->get(['id', 'name']) as $tc) {
            $leadQ = Lead::where('assigned_to', $tc->id)
                ->whereBetween('created_at', [$startAt, $endAt]);
            if ($filters['source'] !== 'all') {
                $leadQ->where('source', $filters['source']);
            }
            if (!empty($filters['search'])) {
                $s = '%' . $filters['search'] . '%';
                $leadQ->where(function ($q) use ($s) {
                    $q->where('lead_code', 'like', $s)
                      ->orWhere('name', 'like', $s)
                      ->orWhere('email', 'like', $s)
                      ->orWhere('phone', 'like', $s);
                });
            }

            $leads = $leadQ->with(['enrolledCourse', 'finalCourse'])->orderByDesc('created_at')->get();
            $leadIds = $leads->pluck('id');

            $callsByLead    = CallLog::whereIn('lead_id', $leadIds)->orderBy('created_at')->get()->groupBy('lead_id');
            $msgsByLead     = WhatsAppMessage::whereIn('lead_id', $leadIds)->orderBy('created_at')->get()->groupBy('lead_id');
            $meetingsByLead = LeadMeeting::whereIn('lead_id', $leadIds)->orderBy('meeting_time')->get()->groupBy('lead_id');

            $leadsData = $leads->map(function ($lead) use ($callsByLead, $msgsByLead, $meetingsByLead) {
                $calls    = $callsByLead->get($lead->id, collect());
                $msgs     = $msgsByLead->get($lead->id, collect());
                $meetings = $meetingsByLead->get($lead->id, collect());
                return [
                    'id'           => $lead->id,
                    'lead_code'    => $lead->lead_code,
                    'name'         => $lead->name,
                    'phone'        => $lead->phone,
                    'status'       => $lead->status,
                    'source'       => $lead->source,
                    'course'       => $lead->enrolledCourse?->name ?? '—',
                    'final_course' => $lead->finalCourse?->name ?? '—',
                    'created_at'   => $lead->created_at?->format('d M Y'),
                    'calls'        => $calls->map(fn($c) => [
                        'date'      => $c->created_at?->format('d M Y H:i'),
                        'direction' => $c->direction,
                        'status'    => $c->status,
                        'outcome'   => $c->outcome ?? '—',
                        'duration'  => $c->duration ? sprintf('%02d:%02d', floor($c->duration / 60), $c->duration % 60) : '—',
                    ])->values()->all(),
                    'messages'     => $msgs->map(fn($m) => [
                        'date'      => ($m->sent_at ?? $m->created_at)?->format('d M Y H:i'),
                        'direction' => $m->direction,
                        'body'      => $m->message_body ?? $m->message ?? '',
                        'type'      => $m->media_type ?? 'text',
                    ])->values()->all(),
                    'meetings'     => $meetings->map(fn($mt) => [
                        'title'  => $mt->title,
                        'time'   => $mt->meeting_time?->format('d M Y H:i'),
                        'type'   => $mt->meeting_type ?? '—',
                        'status' => $mt->status,
                        'notes'  => $mt->notes ?? '—',
                    ])->values()->all(),
                    'call_count'    => $calls->count(),
                    'msg_count'     => $msgs->count(),
                    'meeting_count' => $meetings->count(),
                ];
            });

            $telecallers->push(['id' => $tc->id, 'name' => $tc->name, 'leads' => $leadsData]);
        }

        $periodLabel = $this->periodLabel($filters['date_range']);

        if ($format === 'excel') {
            $filename = 'telecaller-lead-activity-' . now()->format('Ymd') . '.xlsx';
            return Excel::download(new TelecallerLeadActivityExport($telecallers, $periodLabel), $filename);
        }

        // PDF
        $pdf = Pdf::loadView('exports.admin.telecaller_lead_activity', [
            'telecallers' => $telecallers,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->format('d M Y H:i'),
            'filters'     => $filters,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('telecaller-lead-activity-' . now()->format('Ymd') . '.pdf');
    }

    private function exportManagerPerformance(Request $request, string $format)
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'manager'    => $request->get('manager', 'all'),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);
        $periodLabel = $this->periodLabel($filters['date_range']);

        $rows = User::where('role', 'manager')
            ->when($filters['manager'] !== 'all', fn($q) => $q->where('id', (int) $filters['manager']))
            ->get(['id', 'name'])
            ->map(function ($manager) use ($startAt, $endAt, $filters) {
                $leadQ = Lead::where('assigned_by', $manager->id)->whereBetween('created_at', [$startAt, $endAt]);
                if ($filters['source'] !== 'all') $leadQ->where('source', $filters['source']);
                $total     = (clone $leadQ)->count();
                $converted = (clone $leadQ)->where('status', 'converted')->count();
                $active    = (clone $leadQ)->whereNotIn('status', ['converted', 'lost', 'disqualified'])->count();
                $lost      = (clone $leadQ)->where('status', 'lost')->count();
                $teamSize  = (clone $leadQ)->whereNotNull('assigned_to')->distinct('assigned_to')->count('assigned_to');
                $leadIds   = (clone $leadQ)->pluck('id');
                $callQ     = CallLog::whereIn('lead_id', $leadIds);
                $calls     = (clone $callQ)->count();
                $inbound   = (clone $callQ)->where('direction', 'inbound')->count();
                $outbound  = (clone $callQ)->where('direction', 'outbound')->count();
                $answered  = (clone $callQ)->where('status', 'completed')->count();
                $missed    = (clone $callQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                $talkMins  = round((clone $callQ)->sum('duration') / 60, 1);
                $answerRate = $calls > 0 ? round(($answered / $calls) * 100, 1) : 0;
                $fuQ    = Followup::whereIn('lead_id', $leadIds);
                $fuTotal = (clone $fuQ)->count();
                $fuDone  = (clone $fuQ)->whereNotNull('completed_at')->count();
                $fuPend  = (clone $fuQ)->whereDate('next_followup', '<=', now()->toDateString())->whereNull('completed_at')->count();
                $fuRate  = $fuTotal > 0 ? round(($fuDone / $fuTotal) * 100, 1) : 0;
                $meetings = LeadMeeting::whereIn('lead_id', $leadIds)->count();
                $messages = WhatsAppMessage::whereIn('lead_id', $leadIds)->count();
                $convRate  = $total > 0 ? round(($converted / $total) * 100, 1) : 0;
                $callScore = $calls > 0 ? min(100, round(($answered / max(1, $calls)) * 100)) : 0;
                $perfScore = round(($convRate * 0.4) + ($fuRate * 0.35) + ($callScore * 0.25), 1);
                return [
                    'name'            => $manager->name,
                    'grade'           => $perfScore >= 70 ? 'A' : ($perfScore >= 40 ? 'B' : ($perfScore >= 20 ? 'C' : 'D')),
                    'team_size'       => $teamSize,
                    'assigned'        => $total,
                    'converted'       => $converted,
                    'active'          => $active,
                    'lost'            => $lost,
                    'calls'           => $calls,
                    'calls_inbound'   => $inbound,
                    'calls_outbound'  => $outbound,
                    'calls_missed'    => $missed,
                    'answer_rate'     => $answerRate,
                    'total_talk_mins' => $talkMins,
                    'meetings'        => $meetings,
                    'messages'        => $messages,
                    'followup_rate'   => $fuRate,
                    'pending_followups'=> $fuPend,
                    'conversion_rate' => $convRate,
                    'performance_score'=> $perfScore,
                ];
            })->sortByDesc('performance_score')->values();

        $n = $rows->count();
        $summary = [
            'Period'            => $periodLabel,
            'Total Managers'    => $n,
            'Total Leads'       => $rows->sum('assigned'),
            'Total Converted'   => $rows->sum('converted'),
            'Total Calls'       => $rows->sum('calls'),
            'Total Talk Time'   => round($rows->sum('total_talk_mins')) . ' min',
            'Total Meetings'    => $rows->sum('meetings'),
            'Total Messages'    => $rows->sum('messages'),
            'Avg Conv Rate'     => ($n > 0 ? round($rows->avg('conversion_rate'), 1) : 0) . '%',
            'Avg Answer Rate'   => ($n > 0 ? round($rows->avg('answer_rate'), 1) : 0) . '%',
            'Avg Followup Rate' => ($n > 0 ? round($rows->avg('followup_rate'), 1) : 0) . '%',
            'Pending F/U'       => $rows->sum('pending_followups'),
            'Top Manager'       => $rows->first()['name'] ?? '—',
            'Generated'         => now()->format('d M Y H:i'),
        ];

        if ($format === 'excel') {
            $excelRows = $rows->map(fn($r, $i) => [
                '#' . ($i + 1), $r['name'], $r['grade'], $r['team_size'],
                $r['assigned'], $r['converted'], $r['active'], $r['lost'],
                $r['calls'], $r['calls_inbound'], $r['calls_outbound'], $r['calls_missed'],
                $r['answer_rate'] . '%', $r['total_talk_mins'] . ' min',
                $r['meetings'], $r['messages'],
                $r['followup_rate'] . '%', $r['pending_followups'],
                $r['conversion_rate'] . '%', $r['performance_score'],
            ])->all();
            $headings = ['Rank', 'Manager', 'Grade', 'Team', 'Assigned', 'Converted', 'Active', 'Lost', 'Calls', 'Inbound', 'Outbound', 'Missed', 'Answer %', 'Talk Time', 'Meetings', 'Messages', 'Followup %', 'Pending F/U', 'Conv %', 'Score'];
            return Excel::download(new ArrayExport($excelRows, $headings, 'Manager Performance'), 'manager-performance-' . now()->format('Ymd') . '.xlsx');
        }

        $pdf = Pdf::loadView('exports.admin.manager_performance', [
            'rows'        => $rows,
            'summary'     => $summary,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('manager-performance-' . now()->format('Ymd') . '.pdf');
    }

    private function periodLabel(string $dateRange): string
    {
        return match ($dateRange) {
            '7'       => 'Last 7 Days',
            '90'      => 'Last 90 Days',
            'quarter' => 'This Quarter',
            'year'    => 'This Year',
            default   => 'Last 30 Days',
        };
    }

    public function export(Request $request, string $report, string $format)
    {
        $allowed = [
            'telecaller-performance' => 'telecallerPerformance',
            'manager-performance'    => 'managerPerformance',
            'conversion'             => 'conversion',
            'lead-source'            => 'sourcePerformance',
            'period'                 => 'period',
            'call-efficiency'        => 'callEfficiency',
            'response-time'          => 'responseTime',
        ];
        if (!isset($allowed[$report]) || !in_array($format, ['excel', 'pdf'], true)) {
            abort(404);
        }

        if ($report === 'telecaller-performance') {
            return $this->exportTelecallerPerformance($request, $format);
        }
        if ($report === 'manager-performance') {
            return $this->exportManagerPerformance($request, $format);
        }
        if ($report === 'conversion') {
            return $this->exportConversion($request, $format);
        }
        if ($report === 'lead-source') {
            return $this->exportLeadSource($request, $format);
        }
        if ($report === 'period') {
            return $this->exportPeriod($request, $format);
        }

        $viewResponse = $this->{$allowed[$report]}($request);
        $data    = $viewResponse->getData();
        $headers = $data['tableHeaders'] ?? [];
        $rows    = $data['tableRows']    ?? [];
        $title   = $data['title']        ?? 'Report';

        if ($format === 'excel') {
            return $this->csvDownload($report . '.csv', $headers, $rows);
        }

        return view('admin.reports.print', compact('title', 'headers', 'rows'));
    }

    private function exportPeriod(Request $request, string $format)
    {
        [$filters, , $startAt, $endAt] = $this->base($request);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startAt = \Carbon\Carbon::parse($request->from_date)->startOfDay();
            $endAt   = \Carbon\Carbon::parse($request->to_date)->endOfDay();
            $filters['date_range'] = 'custom';
        }

        $periodLabel = $filters['date_range'] === 'custom'
            ? ($request->from_date . ' to ' . $request->to_date)
            : $this->periodLabel($filters['date_range']);

        $baseQ = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source']     !== 'all', fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager']    !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']));

        $dailyRows = (clone $baseQ)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(CASE WHEN status="converted" THEN 1 ELSE 0 END) as converted')
            ->groupBy('day')->orderBy('day')->get()
            ->map(fn($r) => [
                'day'       => $r->day,
                'dow'       => \Carbon\Carbon::parse($r->day)->format('D'),
                'total'     => (int) $r->total,
                'converted' => (int) $r->converted,
                'rate'      => $r->total > 0 ? round(($r->converted / $r->total) * 100, 1) : 0,
            ]);

        $totalLeads     = $dailyRows->sum('total');
        $totalConverted = $dailyRows->sum('converted');
        $activeDays     = $dailyRows->count();
        $overallRate    = $totalLeads > 0 ? round(($totalConverted / $totalLeads) * 100, 1) : 0;

        $summary = [
            'Period'        => $periodLabel,
            'Total Leads'   => $totalLeads,
            'Converted'     => $totalConverted,
            'Active Days'   => $activeDays,
            'Conv. Rate'    => $overallRate . '%',
            'From'          => $startAt->format('d M Y'),
            'To'            => $endAt->format('d M Y'),
            'Generated'     => now()->format('d M Y H:i'),
        ];

        if ($format === 'excel') {
            $excelRows = $dailyRows->sortByDesc('total')->map(fn($r) => [
                $r['day'], $r['dow'], $r['total'], $r['converted'], $r['rate'] . '%',
            ])->all();
            $headings = ['Date', 'Day', 'Total Leads', 'Converted', 'Conv. Rate'];
            return Excel::download(
                new ArrayExport($excelRows, $headings, 'Period Report'),
                'period-report-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.admin.period', [
            'dailyRows'   => $dailyRows,
            'summary'     => $summary,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('period-report-' . now()->format('Ymd') . '.pdf');
    }

    private function exportLeadSource(Request $request, string $format)
    {
        [$filters, , $startAt, $endAt] = $this->base($request);
        $periodLabel = $this->periodLabel($filters['date_range']);
        $courseNames = Course::pluck('name')->toArray();

        $baseQ = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->whereNotNull('source')->where('source', '!=', '')->whereNotIn('source', $courseNames)
            ->when($filters['manager'] !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']));

        $raw = (clone $baseQ)
            ->select(
                'source',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw("SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted_leads"),
                DB::raw("SUM(CASE WHEN status NOT IN ('converted','lost','disqualified') THEN 1 ELSE 0 END) as active_leads"),
                DB::raw("SUM(CASE WHEN status='lost' THEN 1 ELSE 0 END) as lost_leads")
            )
            ->groupBy('source')->orderByDesc('total_leads')->get();

        $totalAll = (int) $raw->sum('total_leads');

        $convertedLeads  = (clone $baseQ)->where('status', 'converted')->select('source', 'created_at', 'updated_at')->get();
        $avgDaysBySource = $convertedLeads->groupBy('source')->map(
            fn($g) => round($g->avg(fn($l) => $l->created_at->diffInDays($l->updated_at)), 1)
        );

        $rows = $raw->map(function ($r) use ($totalAll, $avgDaysBySource) {
            $total     = (int) $r->total_leads;
            $converted = (int) $r->converted_leads;
            $rate      = $total > 0 ? round(($converted / $total) * 100, 2) : 0;
            $share     = $totalAll > 0 ? round(($total / $totalAll) * 100, 1) : 0;
            return [
                'source'   => $r->source ?? 'Unknown',
                'total'    => $total,
                'converted'=> $converted,
                'active'   => (int) $r->active_leads,
                'lost'     => (int) $r->lost_leads,
                'rate'     => $rate,
                'share'    => $share,
                'avg_days' => $avgDaysBySource[$r->source] ?? null,
                'grade'    => $rate >= 10 ? 'A' : ($rate >= 5 ? 'B' : ($rate >= 1 ? 'C' : 'D')),
            ];
        });

        // Course enquiry rows for export
        $courseRaw = Lead::from('leads')
            ->join('courses', 'leads.course_id', '=', 'courses.id')
            ->whereBetween('leads.created_at', [$startAt, $endAt])
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('leads.assigned_by', (int) $filters['manager']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('leads.assigned_to', (int) $filters['telecaller']))
            ->select(
                'leads.course_id', 'courses.name as course_name',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw("SUM(CASE WHEN leads.status='converted' THEN 1 ELSE 0 END) as converted_leads"),
                DB::raw("SUM(CASE WHEN leads.status='lost' THEN 1 ELSE 0 END) as lost_leads"),
                DB::raw("AVG(CASE WHEN leads.status='converted' THEN DATEDIFF(leads.updated_at, leads.created_at) ELSE NULL END) as avg_days")
            )
            ->groupBy('leads.course_id', 'courses.name')->orderByDesc('total_leads')->get();

        $totalConverted = $rows->sum('converted');
        $overallRate    = $totalAll > 0 ? round(($totalConverted / $totalAll) * 100, 2) : 0;

        $summary = [
            'Period'          => $periodLabel,
            'Total Sources'   => $rows->count(),
            'Total Leads'     => $totalAll,
            'Total Converted' => $totalConverted,
            'Overall Conv %'  => $overallRate . '%',
            'Grade A Sources' => $rows->where('grade', 'A')->count(),
            'Grade B Sources' => $rows->where('grade', 'B')->count(),
            'Grade C Sources' => $rows->where('grade', 'C')->count(),
            'Grade D Sources' => $rows->where('grade', 'D')->count(),
            'Generated'       => now()->format('d M Y H:i'),
        ];

        if ($format === 'excel') {
            $sourceExcelRows = $rows->map(fn($r, $i) => [
                '#' . ($i + 1), $r['source'], $r['total'], $r['converted'], $r['active'], $r['lost'],
                $r['rate'] . '%', $r['share'] . '%', $r['avg_days'] ?? '—', $r['grade'],
            ])->all();
            $courseExcelRows = $courseRaw->map(fn($r, $i) => [
                '#' . ($i + 1), $r->course_name ?? 'Unknown', (int) $r->total_leads,
                (int) $r->converted_leads, (int) $r->lost_leads,
                ($r->total_leads > 0 ? round(($r->converted_leads / $r->total_leads) * 100, 2) : 0) . '%',
                $r->avg_days !== null ? round($r->avg_days, 1) : '—',
            ])->all();

            return Excel::download(new MultiSheetArrayExport([
                [
                    'title'    => 'Lead Source Analytics',
                    'headings' => ['#', 'Source', 'Total Leads', 'Converted', 'Active', 'Lost', 'Conv %', 'Share %', 'Avg Days to Win', 'Grade'],
                    'rows'     => $sourceExcelRows,
                ],
                [
                    'title'    => 'Course Enquiry Analytics',
                    'headings' => ['#', 'Course', 'Total Enquiries', 'Converted', 'Lost', 'Conv %', 'Avg Days to Win'],
                    'rows'     => $courseExcelRows,
                ],
            ]), 'lead-source-report-' . now()->format('Ymd') . '.xlsx');
        }

        $pdf = Pdf::loadView('exports.admin.lead_source', [
            'rows'        => $rows,
            'courseRows'  => $courseRaw,
            'summary'     => $summary,
            'totalAll'    => $totalAll,
            'overallRate' => $overallRate,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('lead-source-report-' . now()->format('Ymd') . '.pdf');
    }

    private function exportConversion(Request $request, string $format)
    {
        [$filters, , $startAt, $endAt] = $this->base($request);
        $periodLabel = $this->periodLabel($filters['date_range']);

        $q = Lead::whereBetween('created_at', [$startAt, $endAt]);
        if ($filters['source'] !== 'all')     $q->where('source', $filters['source']);
        if ($filters['telecaller'] !== 'all') $q->where('assigned_to', (int) $filters['telecaller']);
        if ($filters['manager'] !== 'all')    $q->where('assigned_by', (int) $filters['manager']);

        $total     = (clone $q)->count();
        $converted = (clone $q)->where('status', 'converted')->count();
        $contacted = (clone $q)->whereIn('status', ['contacted', 'interested', 'converted', 'follow_up'])->count();
        $convRate    = $total > 0 ? round(($converted / $total) * 100, 2) : 0;
        $contactRate = $total > 0 ? round(($contacted / $total) * 100, 2) : 0;

        $summary = [
            'Period'           => $periodLabel,
            'Total Leads'      => $total,
            'Converted'        => $converted,
            'Contacted'        => $contacted,
            'Conversion Rate'  => $convRate . '%',
            'Contact Rate'     => $contactRate . '%',
            'Generated'        => now()->format('d M Y H:i'),
        ];

        // Status breakdown
        $statusData = (clone $q)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->orderByDesc('total')->get()
            ->map(fn($r) => [
                ucfirst(str_replace('_', ' ', $r->status)),
                (int) $r->total,
                $total > 0 ? round(($r->total / $total) * 100, 1) . '%' : '0%',
            ]);

        // Source breakdown
        $sourceData = (clone $q)
            ->select('source', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted"))
            ->groupBy('source')->orderByDesc('total')->get()
            ->map(fn($r) => [
                $r->source ?? 'Unknown',
                (int) $r->total,
                (int) $r->converted,
                $r->total > 0 ? round(($r->converted / $r->total) * 100, 2) . '%' : '0%',
            ]);

        // Telecaller breakdown
        $tcIds = (clone $q)->whereNotNull('assigned_to')->distinct('assigned_to')->pluck('assigned_to');
        $tcData = User::whereIn('id', $tcIds)->get(['id', 'name'])
            ->map(function ($u) use ($q) {
                $tcQ   = (clone $q)->where('assigned_to', $u->id);
                $tcTot = (clone $tcQ)->count();
                $tcCon = (clone $tcQ)->where('status', 'converted')->count();
                return [
                    $u->name,
                    $tcTot,
                    $tcCon,
                    $tcTot > 0 ? round(($tcCon / $tcTot) * 100, 2) . '%' : '0%',
                ];
            })
            ->sortByDesc(fn($r) => (float) rtrim($r[3], '%'))
            ->values();

        if ($format === 'excel') {
            $rows = [];
            $rows[] = ['SUMMARY'];
            foreach ($summary as $k => $v) { $rows[] = [$k, $v]; }
            $rows[] = [];
            $rows[] = ['STATUS BREAKDOWN'];
            $rows[] = ['Status', 'Count', 'Share'];
            foreach ($statusData as $r) { $rows[] = $r; }
            $rows[] = [];
            $rows[] = ['SOURCE BREAKDOWN'];
            $rows[] = ['Source', 'Total Leads', 'Converted', 'Conv. Rate'];
            foreach ($sourceData as $r) { $rows[] = $r; }
            $rows[] = [];
            $rows[] = ['TELECALLER BREAKDOWN'];
            $rows[] = ['Telecaller', 'Total Leads', 'Converted', 'Conv. Rate'];
            foreach ($tcData as $r) { $rows[] = $r; }

            return Excel::download(
                new ArrayExport($rows, [], 'Conversion Report'),
                'conversion-report-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.admin.conversion', [
            'summary'     => $summary,
            'statusData'  => $statusData,
            'sourceData'  => $sourceData,
            'tcData'      => $tcData,
            'total'       => $total,
            'convRate'    => $convRate,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('conversion-report-' . now()->format('Ymd') . '.pdf');
    }

    private function exportTelecallerPerformance(Request $request, string $format)
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'telecaller' => $request->get('telecaller', 'all'),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);
        $periodLabel = $this->periodLabel($filters['date_range']);

        // Re-run the same row logic as telecallerPerformance()
        $rows = User::where('role', 'telecaller')->where('status', 1)
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('id', (int) $filters['telecaller']))
            ->get(['id', 'name'])
            ->map(function ($t) use ($startAt, $endAt, $filters) {
                $leadQ  = Lead::where('assigned_to', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                if ($filters['source'] !== 'all') $leadQ->where('source', $filters['source']);
                $assigned  = (clone $leadQ)->count();
                $converted = (clone $leadQ)->where('status', 'converted')->count();
                $active    = (clone $leadQ)->whereNotIn('status', ['converted', 'lost', 'disqualified'])->count();
                $lost      = (clone $leadQ)->where('status', 'lost')->count();
                $callsQ    = CallLog::where('user_id', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                $calls     = (clone $callsQ)->count();
                $answered  = (clone $callsQ)->where('status', 'completed')->count();
                $missed    = (clone $callsQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                $avgDur    = (float) ((clone $callsQ)->avg('duration') ?: 0);
                $totalMins = round((clone $callsQ)->sum('duration') / 60, 1);
                $answerRate = $calls > 0 ? round(($answered / $calls) * 100, 1) : 0;
                $fuQ       = Followup::where('user_id', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                $fuTotal   = (clone $fuQ)->count();
                $fuDone    = (clone $fuQ)->whereNotNull('completed_at')->count();
                $fuPending = (clone $fuQ)->whereDate('next_followup', '<=', now()->toDateString())->whereNull('completed_at')->count();
                $fuRate    = $fuTotal > 0 ? round(($fuDone / $fuTotal) * 100, 1) : 0;
                $convRate  = $assigned > 0 ? round(($converted / $assigned) * 100, 1) : 0;
                $callScore = $calls > 0 ? min(100, round(($answered / max(1, $calls)) * 100)) : 0;
                $effScore  = round(($convRate * 0.40) + ($fuRate * 0.35) + ($callScore * 0.25), 1);
                return [
                    'name'              => $t->name,
                    'grade'             => $effScore >= 70 ? 'A' : ($effScore >= 40 ? 'B' : ($effScore >= 20 ? 'C' : 'D')),
                    'assigned'          => $assigned,
                    'converted'         => $converted,
                    'active'            => $active,
                    'lost'              => $lost,
                    'calls'             => $calls,
                    'answered'          => $answered,
                    'missed'            => $missed,
                    'answer_rate'       => $answerRate,
                    'avg_talk_time'     => sprintf('%02d:%02d', floor($avgDur / 60), (int) $avgDur % 60),
                    'total_talk_mins'   => $totalMins,
                    'calls_per_lead'    => $assigned > 0 ? round($calls / $assigned, 1) : 0,
                    'followup_rate'     => $fuRate,
                    'pending_followups' => $fuPending,
                    'conversion_rate'   => $convRate,
                    'efficiency_score'  => $effScore,
                ];
            })->sortByDesc('efficiency_score')->values();

        $n = $rows->count();
        $summary = [
            'Period'              => $periodLabel,
            'Total Telecallers'   => $n,
            'Total Calls'         => $rows->sum('calls'),
            'Total Answered'      => $rows->sum('answered'),
            'Total Missed'        => $rows->sum('missed'),
            'Total Converted'     => $rows->sum('converted'),
            'Total Assigned'      => $rows->sum('assigned'),
            'Total Talk Time'     => round($rows->sum('total_talk_mins')) . ' min',
            'Avg Answer Rate'     => ($n > 0 ? round($rows->avg('answer_rate'), 1) : 0) . '%',
            'Avg Conversion Rate' => ($n > 0 ? round($rows->avg('conversion_rate'), 1) : 0) . '%',
            'Avg Followup Rate'   => ($n > 0 ? round($rows->avg('followup_rate'), 1) : 0) . '%',
            'Total Pending F/U'   => $rows->sum('pending_followups'),
            'Top Performer'       => $rows->first()?->offsetGet('name') ?? '—',
            'Generated'           => now()->format('d M Y H:i'),
        ];

        if ($format === 'excel') {
            $excelRows = $rows->map(fn($r, $i) => [
                '#' . ($i + 1), $r['name'], $r['grade'],
                $r['assigned'], $r['converted'], $r['active'], $r['lost'],
                $r['calls'], $r['answered'], $r['missed'],
                $r['answer_rate'] . '%', $r['avg_talk_time'], $r['total_talk_mins'] . ' min',
                $r['calls_per_lead'], $r['followup_rate'] . '%', $r['pending_followups'],
                $r['conversion_rate'] . '%', $r['efficiency_score'],
            ])->all();
            $headings = ['Rank', 'Telecaller', 'Grade', 'Assigned', 'Converted', 'Active', 'Lost', 'Calls', 'Answered', 'Missed', 'Answer %', 'Avg Talk', 'Talk Time', 'Calls/Lead', 'Followup %', 'Pending F/U', 'Conv %', 'Score'];
            return Excel::download(new ArrayExport($excelRows, $headings, 'Telecaller Performance'), 'telecaller-performance-' . now()->format('Ymd') . '.xlsx');
        }

        // PDF
        $pdf = Pdf::loadView('exports.admin.telecaller_performance', [
            'rows'        => $rows,
            'summary'     => $summary,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('telecaller-performance-' . now()->format('Ymd') . '.pdf');
    }

    private function renderReport(
        string $title,
        string $reportKey,
        string $baseRoute,
        array $tableHeaders,
        array $tableRows,
        array $chartConfig,
        array $filters,
        array $filterOptions
    ) {
        return view('admin.reports.report', compact(
            'title',
            'reportKey',
            'baseRoute',
            'tableHeaders',
            'tableRows',
            'chartConfig',
            'filters',
            'filterOptions'
        ));
    }

    private function base(Request $request): array
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source' => $request->get('source', 'all'),
            'telecaller' => $request->get('telecaller', 'all'),
            'manager' => $request->get('manager', 'all'),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);
        $filterOptions = [
            'sources' => Lead::query()->select('source')->distinct()->orderBy('source')->pluck('source'),
            'telecallers' => User::where('role', 'telecaller')->where('status', 1)->orderBy('name')->get(['id', 'name']),
            'managers' => User::where('role', 'manager')->where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];
        return [$filters, $filterOptions, $startAt, $endAt];
    }

    private function responseTimeRows($startAt, $endAt, array $filters): Collection
    {
        $leadQ = Lead::with('assignedUser')
            ->whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source'] !== 'all', fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->latest('id')
            ->limit(200)
            ->get();

        $firstMap = LeadActivity::whereIn('lead_activities.lead_id', $leadQ->pluck('id'))
            ->whereNotNull('lead_activities.user_id')
            ->whereNotIn('lead_activities.type', ['assignment'])
            ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
            ->whereRaw('lead_activities.created_at > leads.created_at')
            ->select('lead_activities.lead_id', DB::raw('MIN(lead_activities.created_at) as first_response_at'))
            ->groupBy('lead_activities.lead_id')
            ->pluck('first_response_at', 'lead_activities.lead_id');

        return $leadQ->map(function ($lead) use ($firstMap) {
            $first = $firstMap[$lead->id] ?? null;
            $minutes = $first ? $lead->created_at->diffInMinutes($first) : null;
            return [
                'lead_code' => $lead->lead_code,
                'lead_name' => $lead->name,
                'telecaller' => $lead->assignedUser?->name ?? 'Unassigned',
                'created_at' => $lead->created_at?->format('Y-m-d H:i:s'),
                'first_response_at' => $first,
                'response_minutes' => $minutes,
            ];
        });
    }

    private function periodRange(string $dateRange): array
    {
        $endAt = now()->endOfDay();
        $startAt = match ($dateRange) {
            '7' => now()->subDays(7)->startOfDay(),
            '90' => now()->subDays(90)->startOfDay(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->subDays(30)->startOfDay(),
        };
        return [$startAt, $endAt];
    }

    private function csvDownload(string $fileName, array $headers, array $rows)
    {
        $callback = function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        };

        return response()->streamDownload($callback, $fileName, ['Content-Type' => 'text/csv']);
    }
}

