<?php

namespace App\Http\Controllers\ReportViewer;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Followup;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadMeeting;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Services\AutomationSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use App\Exports\MultiSheetArrayExport;
use App\Models\Course;

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

                $callsQ     = CallLog::where('user_id', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                $calls      = (clone $callsQ)->count();
                $answered   = (clone $callsQ)->where('status', 'completed')->count();
                $missed     = (clone $callsQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                $avgDur     = (float) ((clone $callsQ)->avg('duration') ?: 0);
                $totalMins  = round((clone $callsQ)->sum('duration') / 60, 1);
                $answerRate = $calls > 0 ? round(($answered / $calls) * 100, 1) : 0;

                $fuQ              = Followup::where('user_id', $t->id)->whereBetween('created_at', [$startAt, $endAt]);
                $followupsTotal   = (clone $fuQ)->count();
                $followupsDone    = (clone $fuQ)->whereNotNull('completed_at')->count();
                $pendingFollowups = (clone $fuQ)->whereDate('next_followup', '<=', now()->toDateString())->whereNull('completed_at')->count();
                $followupRate     = $followupsTotal > 0 ? round(($followupsDone / $followupsTotal) * 100, 1) : 0;

                $convRate  = $assigned > 0 ? round(($converted / $assigned) * 100, 1) : 0;
                $callScore = $calls > 0 ? min(100, round(($answered / max(1, $calls)) * 100)) : 0;
                $effScore  = round(($convRate * 0.40) + ($followupRate * 0.35) + ($callScore * 0.25), 1);

                return [
                    'id'               => $t->id,
                    'name'             => $t->name,
                    'assigned'         => $assigned,
                    'converted'        => $converted,
                    'active'           => $active,
                    'lost'             => $lost,
                    'calls'            => $calls,
                    'answered'         => $answered,
                    'missed'           => $missed,
                    'answer_rate'      => $answerRate,
                    'avg_talk_time'    => sprintf('%02d:%02d', floor($avgDur / 60), (int) $avgDur % 60),
                    'total_talk_mins'  => $totalMins,
                    'followups_total'  => $followupsTotal,
                    'followups_done'   => $followupsDone,
                    'followup_rate'    => $followupRate,
                    'pending_followups'=> $pendingFollowups,
                    'conversion_rate'  => $convRate,
                    'efficiency_score' => $effScore,
                    'grade'            => $effScore >= 70 ? 'A' : ($effScore >= 40 ? 'B' : ($effScore >= 20 ? 'C' : 'D')),
                    'calls_per_lead'   => $assigned > 0 ? round($calls / $assigned, 1) : 0,
                ];
            })->sortByDesc('efficiency_score')->values();

        $n = $rows->count();
        $totalTalkMins = $rows->sum('total_talk_mins');
        $summary = [
            'total_telecallers' => $n,
            'total_calls'       => $rows->sum('calls'),
            'total_answered'    => $rows->sum('answered'),
            'total_missed'      => $rows->sum('missed'),
            'total_converted'   => $rows->sum('converted'),
            'total_assigned'    => $rows->sum('assigned'),
            'avg_conversion'    => $n > 0 ? round($rows->avg('conversion_rate'), 1) : 0,
            'avg_conversion_rate' => $n > 0 ? round($rows->avg('conversion_rate'), 1) : 0,
            'avg_answer_rate'   => $n > 0 ? round($rows->avg('answer_rate'), 1) : 0,
            'avg_followup_rate' => $n > 0 ? round($rows->avg('followup_rate'), 1) : 0,
            'total_pending_fu'  => $rows->sum('pending_followups'),
            'total_talk_mins'   => $totalTalkMins,
            'total_talk_fmt'    => sprintf('%dh %dm', floor($totalTalkMins / 60), $totalTalkMins % 60),
            'top_performer'     => $rows->first()['name'] ?? '—',
            'top_score'         => $rows->first()['efficiency_score'] ?? 0,
        ];

        $perfDist = [
            'high'    => $rows->where('efficiency_score', '>=', 70)->count(),
            'average' => $rows->whereBetween('efficiency_score', [40, 69.9])->count(),
            'low'     => $rows->where('efficiency_score', '<', 40)->count(),
        ];

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

                $callQ       = CallLog::whereIn('lead_id', $leadIds);
                $calls       = (clone $callQ)->count();
                $inbound     = (clone $callQ)->where('direction', 'inbound')->count();
                $outbound    = (clone $callQ)->where('direction', 'outbound')->count();
                $answered    = (clone $callQ)->where('status', 'completed')->count();
                $missed      = (clone $callQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                $totalSecs   = (int) (clone $callQ)->sum('duration');
                $avgDuration = (float) ((clone $callQ)->avg('duration') ?: 0);
                $answerRate  = $calls > 0 ? round(($answered / $calls) * 100, 1) : 0;
                $totalTalkMins = round($totalSecs / 60, 1);

                $fuQ     = Followup::whereIn('lead_id', $leadIds);
                $fuTotal = (clone $fuQ)->count();
                $fuDone  = (clone $fuQ)->whereNotNull('completed_at')->count();
                $fuPend  = (clone $fuQ)->whereDate('next_followup', '<=', now()->toDateString())->whereNull('completed_at')->count();
                $fuRate  = $fuTotal > 0 ? round(($fuDone / $fuTotal) * 100, 1) : 0;

                $meetingCount = LeadMeeting::whereIn('lead_id', $leadIds)->count();
                $meetingDone  = LeadMeeting::whereIn('lead_id', $leadIds)->where('status', 'completed')->count();
                $msgCount     = WhatsAppMessage::whereIn('lead_id', $leadIds)->count();

                $telecallerIds = (clone $leadQ)->whereNotNull('assigned_to')->distinct('assigned_to')->pluck('assigned_to');
                $telecallerBreakdown = User::whereIn('id', $telecallerIds)->get(['id', 'name'])->map(function ($tc) use ($leadIds) {
                    $tcLeadQ  = Lead::where('assigned_to', $tc->id)->whereIn('id', $leadIds);
                    $tcLeads  = (clone $tcLeadQ)->count();
                    $tcConv   = (clone $tcLeadQ)->where('status', 'converted')->count();
                    $tcCallQ  = CallLog::where('user_id', $tc->id)->whereIn('lead_id', $leadIds);
                    $tcCalls  = (clone $tcCallQ)->count();
                    $tcAns    = (clone $tcCallQ)->where('status', 'completed')->count();
                    $tcMissed = (clone $tcCallQ)->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled', 'missed'])->count();
                    $tcFuQ    = Followup::where('user_id', $tc->id)->whereIn('lead_id', $leadIds);
                    $tcFuDone = (clone $tcFuQ)->whereNotNull('completed_at')->count();
                    $tcFuTot  = (clone $tcFuQ)->count();
                    $tcFuRate = $tcFuTot > 0 ? round(($tcFuDone / $tcFuTot) * 100, 1) : 0;
                    $tcConvR  = $tcLeads > 0 ? round(($tcConv / $tcLeads) * 100, 1) : 0;
                    return [
                        'name'            => $tc->name,
                        'leads'           => $tcLeads,
                        'converted'       => $tcConv,
                        'conversion_rate' => $tcConvR,
                        'calls'           => $tcCalls,
                        'answered'        => $tcAns,
                        'missed'          => $tcMissed,
                        'followup_rate'   => $tcFuRate,
                    ];
                })->values()->all();

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
                    'id'                   => $manager->id,
                    'name'                 => $manager->name,
                    'grade'                => $perfScore >= 70 ? 'A' : ($perfScore >= 40 ? 'B' : ($perfScore >= 20 ? 'C' : 'D')),
                    'assigned'             => $total,
                    'converted'            => $converted,
                    'active'               => $active,
                    'lost'                 => $lost,
                    'team_size'            => $teamSize,
                    'calls'                => $calls,
                    'calls_inbound'        => $inbound,
                    'calls_outbound'       => $outbound,
                    'calls_missed'         => $missed,
                    'answer_rate'          => $answerRate,
                    'total_talk_mins'      => $totalTalkMins,
                    'total_talk_fmt'       => sprintf('%dh %dm', floor($totalTalkMins / 60), (int) $totalTalkMins % 60),
                    'avg_talk_time'        => sprintf('%02d:%02d', floor($avgDuration / 60), (int) $avgDuration % 60),
                    'meetings'             => $meetingCount,
                    'meetings_done'        => $meetingDone,
                    'messages'             => $msgCount,
                    'followup_rate'        => $fuRate,
                    'pending_followups'    => $fuPend,
                    'avg_response_mins'    => $avgResponseMins,
                    'avg_response_fmt'     => $avgResponseMins !== null ? ($avgResponseMins < 60 ? $avgResponseMins . ' min' : round($avgResponseMins / 60, 1) . ' hr') : '—',
                    'conversion_rate'      => $convRate,
                    'performance_score'    => $perfScore,
                    'telecaller_breakdown' => $telecallerBreakdown,
                ];
            })->sortByDesc('performance_score')->values();

        $n = $rows->count();
        $summary = [
            'total_managers'    => $n,
            'total_leads'       => $rows->sum('assigned'),
            'total_converted'   => $rows->sum('converted'),
            'total_calls'       => $rows->sum('calls'),
            'total_talk_mins'   => $rows->sum('total_talk_mins'),
            'total_talk_fmt'    => sprintf('%dh %dm', floor($rows->sum('total_talk_mins') / 60), (int) $rows->sum('total_talk_mins') % 60),
            'total_meetings'    => $rows->sum('meetings'),
            'total_messages'    => $rows->sum('messages'),
            'total_pending_fu'  => $rows->sum('pending_followups'),
            'avg_conversion'    => $n > 0 ? round($rows->avg('conversion_rate'), 1) : 0,
            'avg_followup_rate' => $n > 0 ? round($rows->avg('followup_rate'), 1) : 0,
            'avg_answer_rate'   => $n > 0 ? round($rows->avg('answer_rate'), 1) : 0,
            'top_manager'       => $rows->first()['name'] ?? '—',
            'top_score'         => $rows->first()['performance_score'] ?? 0,
        ];

        $perfDist = [
            'high'    => $rows->where('performance_score', '>=', 70)->count(),
            'average' => $rows->whereBetween('performance_score', [40, 69.9])->count(),
            'low'     => $rows->where('performance_score', '<', 40)->count(),
        ];

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

        $q = Lead::whereBetween('created_at', [$startAt, $endAt]);
        if ($filters['source'] !== 'all')     $q->where('source', $filters['source']);
        if ($filters['telecaller'] !== 'all') $q->where('assigned_to', (int) $filters['telecaller']);
        if ($filters['manager'] !== 'all')    $q->where('assigned_by', (int) $filters['manager']);

        $total     = (clone $q)->count();
        $converted = (clone $q)->where('status', 'converted')->count();
        $lost      = (clone $q)->where('status', 'lost')->count();
        $contacted = (clone $q)->whereNotIn('status', ['new'])->count();
        $convRate  = $total > 0 ? round(($converted / $total) * 100, 1) : 0;
        $contactRate = $total > 0 ? round(($contacted / $total) * 100, 1) : 0;
        $days      = max(1, now()->diffInDays($startAt));
        $velocity  = round($total / $days, 1);

        $avgDaysToConvert = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->where('status', 'converted')
            ->when($filters['source'] !== 'all',     fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');
        $avgDaysToConvert = $avgDaysToConvert !== null ? round($avgDaysToConvert, 1) : null;

        // Monthly 6-month trend — conversion RATE (%) not just counts
        $monthLabels = $monthRate = $monthVolume = $monthContacted = [];
        for ($i = 5; $i >= 0; $i--) {
            $ms = now()->subMonths($i)->startOfMonth();
            $me = now()->subMonths($i)->endOfMonth();
            $monthLabels[] = $ms->format('M Y');
            $mq = Lead::whereBetween('created_at', [$ms, $me]);
            if ($filters['source'] !== 'all')     $mq->where('source', $filters['source']);
            if ($filters['telecaller'] !== 'all') $mq->where('assigned_to', (int) $filters['telecaller']);
            if ($filters['manager'] !== 'all')    $mq->where('assigned_by', (int) $filters['manager']);
            $mt = (clone $mq)->count();
            $mc = (clone $mq)->where('status', 'converted')->count();
            $mk = (clone $mq)->whereNotIn('status', ['new'])->count();
            $monthVolume[]    = $mt;
            $monthRate[]      = $mt > 0 ? round(($mc / $mt) * 100, 1) : 0;
            $monthContacted[] = $mt > 0 ? round(($mk / $mt) * 100, 1) : 0;
        }

        // Lead age health (active/pipeline leads only)
        $ageData = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->whereNotIn('status', ['converted', 'lost', 'disqualified'])
            ->when($filters['source'] !== 'all',     fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->selectRaw("
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 1 ELSE 0 END) as fresh,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) BETWEEN 8  AND 30 THEN 1 ELSE 0 END) as warm,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as aging,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) > 60 THEN 1 ELSE 0 END) as stale
            ")
            ->first();
        $ageHealth = [
            'fresh' => (int) ($ageData->fresh ?? 0),
            'warm'  => (int) ($ageData->warm  ?? 0),
            'aging' => (int) ($ageData->aging ?? 0),
            'stale' => (int) ($ageData->stale ?? 0),
        ];

        // Day-of-week lead intake & conversion pattern
        $dowRaw = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source'] !== 'all',     fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->selectRaw("DAYOFWEEK(created_at) as dow, COUNT(*) as total, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted")
            ->groupBy('dow')->orderBy('dow')->get()->keyBy('dow');
        $dowLabels = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $dowTotal = $dowConv = [];
        for ($d = 1; $d <= 7; $d++) {
            $dowTotal[] = (int) ($dowRaw[$d]->total     ?? 0);
            $dowConv[]  = (int) ($dowRaw[$d]->converted ?? 0);
        }

        // Source breakdown
        $sourceRows = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->select('source', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted"))
            ->groupBy('source')->orderByDesc('total')->get()
            ->map(fn($r) => [
                'source'    => $r->source ?: 'Unknown',
                'total'     => (int) $r->total,
                'converted' => (int) $r->converted,
                'rate'      => $r->total > 0 ? round(($r->converted / $r->total) * 100, 1) : 0,
            ]);
        $bestSource = $sourceRows->where('total', '>=', 3)->sortByDesc('rate')->first();

        // Telecaller breakdown
        $telecallerRows = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source'] !== 'all',     fn($q) => $q->where('source', $filters['source']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted"))
            ->groupBy('assigned_to')->orderByDesc('total')->limit(10)->get()
            ->map(function ($r) {
                $user = User::find($r->assigned_to);
                return [
                    'name'      => $user?->name ?? 'Unknown',
                    'total'     => (int) $r->total,
                    'converted' => (int) $r->converted,
                    'rate'      => $r->total > 0 ? round(($r->converted / $r->total) * 100, 1) : 0,
                ];
            });

        // Smart auto-insights
        $insights = [];
        $newCount = Lead::whereBetween('created_at', [$startAt, $endAt])
            ->when($filters['source'] !== 'all',     fn($q) => $q->where('source', $filters['source']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->where('status', 'new')->count();
        $newPct = $total > 0 ? round(($newCount / $total) * 100) : 0;
        if ($newPct >= 50) {
            $insights[] = ['type' => 'warning', 'icon' => 'warning', 'text' => "{$newPct}% of leads are still 'New' — outreach bottleneck detected. Assign & contact more leads."];
        }
        if ($bestSource) {
            $avgRate = $sourceRows->avg('rate');
            $mult = $avgRate > 0 ? round($bestSource['rate'] / $avgRate, 1) : 0;
            $insights[] = ['type' => 'success', 'icon' => 'star', 'text' => "'{$bestSource['source']}' converts at {$bestSource['rate']}% — {$mult}x the average. Prioritise this source."];
        }
        if ($ageHealth['stale'] > 0) {
            $insights[] = ['type' => 'danger', 'icon' => 'hourglass_disabled', 'text' => "{$ageHealth['stale']} leads have been in pipeline 60+ days without converting. Review or disqualify."];
        }
        if ($convRate < 5 && $total >= 10) {
            $insights[] = ['type' => 'warning', 'icon' => 'trending_down', 'text' => "Conversion rate is {$convRate}% — below the 5% threshold. Review qualification criteria."];
        }
        $topTc = $telecallerRows->sortByDesc('rate')->first();
        if ($topTc && $topTc['converted'] > 0) {
            $insights[] = ['type' => 'info', 'icon' => 'emoji_events', 'text' => "{$topTc['name']} leads with {$topTc['rate']}% conversion rate. Review their workflow for best practices."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Not enough data to generate insights. Broaden the time period or remove filters.'];
        }

        $summary = compact('total', 'converted', 'contacted', 'lost', 'convRate', 'contactRate', 'velocity', 'avgDaysToConvert');

        // Export table data
        $title        = 'Conversion Report';
        $tableHeaders = ['Status', 'Count', 'Share'];
        $statusRows   = (clone $q)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get();
        $tableRows    = $statusRows->map(fn($r) => [
            ucfirst(str_replace('_', ' ', $r->status)),
            (int) $r->total,
            $total > 0 ? round(($r->total / $total) * 100, 1) . '%' : '0%',
        ])->all();

        return view('admin.reports.conversion', compact(
            'filters', 'filterOptions', 'summary', 'bestSource',
            'monthLabels', 'monthRate', 'monthVolume', 'monthContacted',
            'ageHealth', 'dowLabels', 'dowTotal', 'dowConv',
            'sourceRows', 'telecallerRows', 'insights',
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
            ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
            ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']));

        $totalLeads = (clone $baseQ)->count();

        $rows = (clone $baseQ)
            ->select(
                'source',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='converted'    THEN 1 ELSE 0 END) as converted"),
                DB::raw("SUM(CASE WHEN status='lost'         THEN 1 ELSE 0 END) as lost"),
                DB::raw("SUM(CASE WHEN status NOT IN ('converted','lost','disqualified') THEN 1 ELSE 0 END) as active"),
                DB::raw("AVG(CASE WHEN status='converted' THEN DATEDIFF(updated_at,created_at) ELSE NULL END) as avg_days")
            )
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->map(function ($r) use ($totalLeads) {
                $rate  = $r->total > 0 ? round(($r->converted / $r->total) * 100, 1) : 0;
                $share = $totalLeads > 0 ? round(($r->total / $totalLeads) * 100, 1) : 0;
                if ($rate >= 10)     { $grade = 'A'; }
                elseif ($rate >= 5)  { $grade = 'B'; }
                elseif ($rate >= 1)  { $grade = 'C'; }
                else                 { $grade = 'D'; }
                return [
                    'source'    => $r->source ? ucfirst($r->source) : 'Unknown',
                    'total'     => (int) $r->total,
                    'converted' => (int) $r->converted,
                    'lost'      => (int) $r->lost,
                    'active'    => (int) $r->active,
                    'rate'      => $rate,
                    'share'     => $share,
                    'avg_days'  => $r->avg_days !== null ? round($r->avg_days, 1) : null,
                    'grade'     => $grade,
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

        // Summary KPIs
        $totalSources   = $rows->count();
        $totalConverted = $rows->sum('converted');
        $overallRate    = $totalLeads > 0 ? round(($totalConverted / $totalLeads) * 100, 1) : 0;
        $bestSource     = $rows->where('total', '>=', 3)->sortByDesc('rate')->first();
        $topVolSource   = $rows->first();
        $avgDaysAll     = $rows->whereNotNull('avg_days')->avg('avg_days');
        $avgDaysAll     = $avgDaysAll ? round($avgDaysAll, 1) : null;
        $gradeCounts    = ['A' => $rows->where('grade','A')->count(), 'B' => $rows->where('grade','B')->count(), 'C' => $rows->where('grade','C')->count(), 'D' => $rows->where('grade','D')->count()];

        // Monthly 6-month trend — total + converted
        $monthLabels = $monthTotal = $monthConv = [];
        for ($i = 5; $i >= 0; $i--) {
            $ms = now()->subMonths($i)->startOfMonth();
            $me = now()->subMonths($i)->endOfMonth();
            $monthLabels[] = $ms->format('M Y');
            $mq = Lead::whereBetween('created_at', [$ms, $me])
                ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
                ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']));
            $mt = (clone $mq)->count();
            $mc = (clone $mq)->where('status', 'converted')->count();
            $monthTotal[] = $mt;
            $monthConv[]  = $mc;
        }

        // Top 5 sources monthly trend (for multi-line chart)
        $top5 = $rows->take(5)->pluck('source')->all();
        $sourceMonthData = [];
        foreach ($top5 as $src) {
            $pts = [];
            for ($i = 5; $i >= 0; $i--) {
                $ms = now()->subMonths($i)->startOfMonth();
                $me = now()->subMonths($i)->endOfMonth();
                $pts[] = Lead::whereBetween('created_at', [$ms, $me])
                    ->where('source', $src)
                    ->when($filters['manager'] !== 'all',    fn($q) => $q->where('assigned_by', (int) $filters['manager']))
                    ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
                    ->count();
            }
            $sourceMonthData[] = ['source' => $src, 'data' => $pts];
        }

        $summary = compact('totalLeads', 'totalSources', 'totalConverted', 'overallRate', 'bestSource', 'topVolSource', 'avgDaysAll', 'gradeCounts');

        // Insights
        $insights = [];
        if ($bestSource) {
            $insights[] = ['type' => 'success', 'icon' => 'star', 'text' => "Best converting source: '{$bestSource['source']}' at {$bestSource['rate']}%. Prioritise this channel for higher ROI."];
        }
        if ($overallRate < 5 && $totalLeads >= 10) {
            $insights[] = ['type' => 'danger', 'icon' => 'trending_down', 'text' => "Overall conversion rate is only {$overallRate}% — review lead quality and follow-up cadence."];
        }
        $dSources = $rows->where('grade', 'D')->count();
        if ($dSources > 0) {
            $insights[] = ['type' => 'warning', 'icon' => 'warning', 'text' => "{$dSources} source(s) have less than 1% conversion (Grade D). Consider reducing investment in these channels."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Expand the date range or remove filters to see more actionable insights.'];
        }

        // For export
        $title        = 'Lead Source Report';
        $tableHeaders = ['#', 'Source', 'Total', 'Converted', 'Active', 'Lost', 'Conv %', 'Share %', 'Avg Days', 'Grade'];
        $tableRows    = $rows->map(fn($r, $i) => [
            '#' . ($i + 1), $r['source'], $r['total'], $r['converted'], $r['active'], $r['lost'],
            $r['rate'] . '%', $r['share'] . '%', $r['avg_days'] ?? '—', $r['grade'],
        ])->values()->all();

        return view('admin.reports.lead_source', compact(
            'filters', 'filterOptions', 'summary', 'rows', 'courseRows',
            'monthLabels', 'monthTotal', 'monthConv',
            'sourceMonthData', 'top5',
            'insights', 'title', 'tableHeaders', 'tableRows'
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

        $periodLabel = $filters['date_range'] === 'custom'
            ? ($filters['from_date'] . ' to ' . $filters['to_date'])
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
        $dowTotal  = $dowConv = [];
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
            'insights', 'title', 'tableHeaders', 'tableRows', 'periodLabel'
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
            ['label' => '<5 min',     'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] < 5)->count(),                                             'color' => '#10b981'],
            ['label' => '5–30 min',   'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 5   && $r['response_minutes'] < 30)->count(),           'color' => '#06b6d4'],
            ['label' => '30–60 min',  'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 30  && $r['response_minutes'] < 60)->count(),           'color' => '#f59e0b'],
            ['label' => '1–4 hr',     'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 60  && $r['response_minutes'] < 240)->count(),          'color' => '#f97316'],
            ['label' => '4–24 hr',    'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 240 && $r['response_minutes'] < 1440)->count(),         'color' => '#ef4444'],
            ['label' => '24h+',       'count' => $respondedRows->filter(fn($r) => $r['response_minutes'] >= 1440)->count(),                                          'color' => '#991b1b'],
            ['label' => 'No Response','count' => $neverResponded->count(),                                                                                           'color' => '#94a3b8'],
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
            'asc'   => $filteredRows->sortBy('response_minutes'),
            'desc'  => $filteredRows->sortByDesc('response_minutes'),
            default => $filteredRows,
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

    public function escalationMatrix(Request $request)
    {
        [$filters, $filterOptions, $startAt, $endAt] = $this->base($request);
        $filters['type'] = $request->get('type', 'all'); // all / sla / missed

        $slaMinutes = app(AutomationSettings::class)->responseSlaMinutes();

        $slaRows = collect();
        if ($filters['type'] !== 'missed') {
            $slaRows = Lead::with(['assignedUser', 'assignedBy'])
                ->whereNotNull('sla_escalated_at')
                ->whereBetween('sla_escalated_at', [$startAt, $endAt])
                ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
                ->when($filters['manager'] !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']))
                ->latest('sla_escalated_at')
                ->get()
                ->map(fn($lead) => [
                    'type'         => 'Response SLA',
                    'lead_code'    => $lead->lead_code ?? ('#' . $lead->id),
                    'lead_name'    => $lead->name,
                    'telecaller'   => $lead->assignedUser?->name ?? 'Unassigned',
                    'manager'      => $lead->assignedBy?->name ?? '—',
                    'escalated_at' => $lead->sla_escalated_at?->format('Y-m-d H:i'),
                    'detail'       => 'No contact within ' . $slaMinutes . ' min',
                ]);
        }

        $missedRows = collect();
        if ($filters['type'] !== 'sla') {
            $missedRows = Followup::with(['lead.assignedUser', 'lead.assignedBy'])
                ->whereNotNull('escalated_at')
                ->whereBetween('escalated_at', [$startAt, $endAt])
                ->when($filters['telecaller'] !== 'all', fn($q) => $q->whereHas('lead', fn($lq) => $lq->where('assigned_to', (int) $filters['telecaller'])))
                ->when($filters['manager'] !== 'all', fn($q) => $q->whereHas('lead', fn($lq) => $lq->where('assigned_by', (int) $filters['manager'])))
                ->latest('escalated_at')
                ->get()
                ->map(fn($f) => [
                    'type'         => 'Missed Follow-up',
                    'lead_code'    => $f->lead?->lead_code ?? ('#' . $f->lead_id),
                    'lead_name'    => $f->lead?->name ?? '—',
                    'telecaller'   => $f->lead?->assignedUser?->name ?? 'Unassigned',
                    'manager'      => $f->lead?->assignedBy?->name ?? '—',
                    'escalated_at' => $f->escalated_at?->format('Y-m-d H:i'),
                    'detail'       => 'Follow-up due ' . ($f->next_followup?->format('d M Y') ?? '—'),
                ]);
        }

        $rows = $slaRows->concat($missedRows)->sortByDesc('escalated_at')->values();

        $totalEscalations   = $rows->count();
        $slaCount           = $slaRows->count();
        $missedCount        = $missedRows->count();
        $slaRate            = $totalEscalations > 0 ? round(($slaCount / $totalEscalations) * 100, 1) : 0;

        $byManager    = $rows->groupBy('manager')->map(fn($g) => $g->count())->sortDesc()->take(10);
        $byTelecaller = $rows->groupBy('telecaller')->map(fn($g) => $g->count())->sortDesc()->take(10);
        $topManager         = $byManager->keys()->first() ?? '—';
        $topManagerCount    = $byManager->first() ?? 0;
        $topTelecaller      = $byTelecaller->keys()->first() ?? '—';
        $topTelecallerCount = $byTelecaller->first() ?? 0;

        $monthLabels = $monthSla = $monthMissed = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $monthLabels[] = $mStart->format('M Y');
            $monthSla[] = Lead::whereNotNull('sla_escalated_at')
                ->whereBetween('sla_escalated_at', [$mStart, $mEnd])
                ->when($filters['telecaller'] !== 'all', fn($q) => $q->where('assigned_to', (int) $filters['telecaller']))
                ->when($filters['manager'] !== 'all', fn($q) => $q->where('assigned_by', (int) $filters['manager']))
                ->count();
            $monthMissed[] = Followup::whereNotNull('escalated_at')
                ->whereBetween('escalated_at', [$mStart, $mEnd])
                ->when($filters['telecaller'] !== 'all', fn($q) => $q->whereHas('lead', fn($lq) => $lq->where('assigned_to', (int) $filters['telecaller'])))
                ->when($filters['manager'] !== 'all', fn($q) => $q->whereHas('lead', fn($lq) => $lq->where('assigned_by', (int) $filters['manager'])))
                ->count();
        }

        $summary = [
            'total'               => $totalEscalations,
            'sla_count'           => $slaCount,
            'missed_count'        => $missedCount,
            'sla_rate'            => $slaRate,
            'top_manager'         => $topManager,
            'top_manager_count'   => $topManagerCount,
            'top_telecaller'      => $topTelecaller,
            'top_telecaller_count'=> $topTelecallerCount,
        ];

        $insights = [];
        if ($totalEscalations === 0) {
            $insights[] = ['type' => 'success', 'icon' => 'check_circle', 'text' => 'No escalations found for the selected period. Excellent team performance!'];
        }
        if ($slaRate > 60 && $totalEscalations >= 5) {
            $insights[] = ['type' => 'danger', 'icon' => 'warning', 'text' => "SLA breaches account for {$slaRate}% of all escalations. Review first-response time targets and call scheduling."];
        }
        if ($topManagerCount > 5) {
            $insights[] = ['type' => 'warning', 'icon' => 'group', 'text' => "Manager '{$topManager}' has {$topManagerCount} escalations — highest in the team. Consider workload distribution review."];
        }
        if ($missedCount > $slaCount && $totalEscalations >= 3) {
            $insights[] = ['type' => 'warning', 'icon' => 'event_busy', 'text' => "Missed follow-ups ({$missedCount}) outpace SLA breaches ({$slaCount}). Strengthen follow-up scheduling and reminders."];
        }
        if ($topTelecallerCount >= 3 && $topTelecaller !== 'Unassigned') {
            $insights[] = ['type' => 'info', 'icon' => 'person', 'text' => "'{$topTelecaller}' has {$topTelecallerCount} escalations — highest among telecallers. A focused coaching session may help."];
        }
        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'text' => 'Escalation levels are within acceptable thresholds. Continue monitoring for any emerging trends.'];
        }

        $perPage   = 20;
        $totalRows = $totalEscalations;
        $page      = max(1, (int) $request->get('page', 1));
        $lastPage  = (int) ceil(max($totalRows, 1) / $perPage);
        $page      = min($page, $lastPage);
        $pagedRows = $rows->forPage($page, $perPage);

        $title        = 'Escalation Matrix';
        $tableHeaders = ['Type', 'Lead Code', 'Lead Name', 'Telecaller', 'Manager', 'Escalated At', 'Detail'];
        $tableRows    = $rows->map(fn($r) => [$r['type'], $r['lead_code'], $r['lead_name'], $r['telecaller'], $r['manager'], $r['escalated_at'], $r['detail']])->all();

        return view('admin.reports.escalation_matrix', compact(
            'title', 'filters', 'filterOptions', 'summary',
            'rows', 'pagedRows', 'page', 'perPage', 'totalRows', 'lastPage',
            'byManager', 'byTelecaller',
            'monthLabels', 'monthSla', 'monthMissed',
            'insights', 'slaMinutes',
            'tableHeaders', 'tableRows'
        ));
    }

    public function reportsPage()
    {
        $courseWiseRows = DB::table('leads')
            ->join('courses', 'courses.id', '=', 'leads.course_id')
            ->whereNotNull('leads.course_id')
            ->selectRaw('courses.id as course_id, courses.name as course_name')
            ->groupBy('courses.id', 'courses.name')
            ->orderBy('course_name')
            ->get()
            ->map(fn($r) => ['course_id' => (int) $r->course_id, 'course' => $r->course_name])
            ->values();

        $finalCourseRows = DB::table('leads')
            ->join('courses', 'courses.id', '=', 'leads.final_course_id')
            ->where('leads.status', 'converted')
            ->whereNotNull('leads.final_course_id')
            ->selectRaw('courses.id as course_id, courses.name as course_name')
            ->groupBy('courses.id', 'courses.name')
            ->orderBy('course_name')
            ->get()
            ->map(fn($r) => ['course_id' => (int) $r->course_id, 'course' => $r->course_name])
            ->values();

        $telecallers = User::where('role', 'telecaller')->where('status', 1)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();

        $managers = User::where('role', 'manager')->where('status', 1)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();

        $sources = Lead::select('source')->whereNotNull('source')->where('source', '!=', '')
            ->distinct()->orderBy('source')->pluck('source')->values();

        return Inertia::render('ReportViewer/Reports/Index', compact(
            'courseWiseRows', 'finalCourseRows', 'telecallers', 'managers', 'sources'
        ));
    }

    public function downloadLeads(Request $request)
    {
        $format = $request->input('format', 'excel');
        $from   = $request->input('date_from');
        $to     = $request->input('date_to');
        $start  = $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth()->startOfDay();
        $end    = $to   ? Carbon::parse($to)->endOfDay()     : now()->endOfDay();

        $query = Lead::whereBetween('created_at', [$start, $end])
            ->with(['enrolledCourse:id,name', 'finalCourse:id,name', 'assignedUser:id,name', 'assignedBy:id,name'])
            ->select('id', 'name', 'lead_code', 'phone', 'gender', 'quota', 'status',
                'source', 'course_id', 'final_course_id', 'assigned_to', 'assigned_by', 'created_at');

        if ($request->filled('telecaller') && $request->input('telecaller') !== 'all') {
            $query->where('assigned_to', (int) $request->input('telecaller'));
        }
        if ($request->filled('manager') && $request->input('manager') !== 'all') {
            $query->where('assigned_by', (int) $request->input('manager'));
        }
        if ($request->filled('source') && $request->input('source') !== 'all') {
            $query->where('source', $request->input('source'));
        }
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('gender') && $request->input('gender') !== 'all') {
            $g = $request->input('gender');
            $g === 'not_specified'
                ? $query->where(fn($q) => $q->whereNull('gender')->orWhere('gender', ''))
                : $query->where('gender', $g);
        }
        if ($request->filled('course_id') && $request->input('course_id') !== 'all') {
            $query->where('course_id', (int) $request->input('course_id'));
        }
        if ($request->filled('final_course_id') && $request->input('final_course_id') !== 'all') {
            $query->where('final_course_id', (int) $request->input('final_course_id'));
        }
        if ($request->filled('quota') && $request->input('quota') !== 'all') {
            $q = $request->input('quota');
            $q === 'not_specified'
                ? $query->where(fn($q2) => $q2->whereNull('quota')->orWhere('quota', ''))
                : $query->where('quota', $q);
        }

        $leads    = $query->latest()->limit(2000)->get();
        $period   = $start->format('d M Y') . ' – ' . $end->format('d M Y');
        $filename = 'leads-export-' . now()->format('Ymd-His');

        $filterParts = [];
        if ($request->filled('telecaller') && $request->input('telecaller') !== 'all')
            $filterParts[] = 'Telecaller: ' . (User::find((int) $request->input('telecaller'))?->name ?? $request->input('telecaller'));
        if ($request->filled('manager') && $request->input('manager') !== 'all')
            $filterParts[] = 'Manager: ' . (User::find((int) $request->input('manager'))?->name ?? $request->input('manager'));
        if ($request->filled('source') && $request->input('source') !== 'all')
            $filterParts[] = 'Source: ' . $request->input('source');
        if ($request->filled('status') && $request->input('status') !== 'all')
            $filterParts[] = 'Status: ' . ucfirst(str_replace('_', ' ', $request->input('status')));
        if ($request->filled('gender') && $request->input('gender') !== 'all')
            $filterParts[] = 'Gender: ' . ucfirst(str_replace('_', ' ', $request->input('gender')));
        if ($request->filled('quota') && $request->input('quota') !== 'all')
            $filterParts[] = 'Quota: ' . ucfirst($request->input('quota'));
        $filterDesc = $filterParts ? implode(' | ', $filterParts) : 'None';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.report_viewer.lead-report', [
                'leads'       => $leads,
                'period'      => $period,
                'generatedAt' => now()->format('d M Y, h:i A'),
                'filterDesc'  => $filterDesc,
            ])->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        $headers = ['Lead Code', 'Name', 'Phone', 'Gender', 'Source', 'Telecaller', 'Manager', 'Enquired Course', 'Final Course', 'Quota', 'Status', 'Date'];
        $rows = $leads->map(fn($l) => [
            $l->lead_code,
            $l->name,
            $l->phone,
            ucfirst($l->gender ?? '-'),
            $l->source ?? '-',
            $l->assignedUser?->name ?? '-',
            $l->assignedBy?->name ?? '-',
            $l->enrolledCourse?->name ?? '-',
            $l->finalCourse?->name ?? '-',
            ucfirst($l->quota ?? '-'),
            ucfirst(str_replace('_', ' ', $l->status)),
            $l->created_at->format('d M Y'),
        ])->toArray();

        return Excel::download(new ArrayExport($rows, $headers, 'Lead Report'), $filename . '.xlsx');
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
            'escalation-matrix'      => 'escalationMatrix',
        ];
        if (!isset($allowed[$report]) || !in_array($format, ['excel', 'pdf'], true)) {
            abort(404);
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
        $data         = $viewResponse->getData();
        $headers      = $data['tableHeaders'] ?? [];
        $rows         = $data['tableRows'] ?? [];
        $title        = $data['title'] ?? 'Report';

        if ($format === 'excel') {
            return $this->csvDownload($report . '.csv', $headers, $rows);
        }

        return view('admin.reports.print', compact('title', 'headers', 'rows'));
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

        $totalAll        = (int) $raw->sum('total_leads');
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
            'Period'      => $periodLabel,
            'Total Leads' => $totalLeads,
            'Converted'   => $totalConverted,
            'Active Days' => $activeDays,
            'Conv. Rate'  => $overallRate . '%',
            'From'        => $startAt->format('d M Y'),
            'To'          => $endAt->format('d M Y'),
            'Generated'   => now()->format('d M Y H:i'),
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
            'Period'          => $periodLabel,
            'Total Leads'     => $total,
            'Converted'       => $converted,
            'Contacted'       => $contacted,
            'Conversion Rate' => $convRate . '%',
            'Contact Rate'    => $contactRate . '%',
            'Generated'       => now()->format('d M Y H:i'),
        ];

        $statusData = (clone $q)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get()
            ->map(fn($r) => [
                ucfirst(str_replace('_', ' ', $r->status)),
                (int) $r->total,
                $total > 0 ? round(($r->total / $total) * 100, 1) . '%' : '0%',
            ]);

        $sourceData = (clone $q)->select('source', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as converted"))
            ->groupBy('source')->orderByDesc('total')->get()
            ->map(fn($r) => [
                $r->source ?? 'Unknown',
                (int) $r->total,
                (int) $r->converted,
                $r->total > 0 ? round(($r->converted / $r->total) * 100, 2) . '%' : '0%',
            ]);

        $tcIds = (clone $q)->whereNotNull('assigned_to')->distinct('assigned_to')->pluck('assigned_to');
        $tcData = User::whereIn('id', $tcIds)->get(['id', 'name'])
            ->map(function ($u) use ($q) {
                $tcQ   = (clone $q)->where('assigned_to', $u->id);
                $tcTot = (clone $tcQ)->count();
                $tcCon = (clone $tcQ)->where('status', 'converted')->count();
                return [$u->name, $tcTot, $tcCon, $tcTot > 0 ? round(($tcCon / $tcTot) * 100, 2) . '%' : '0%'];
            })
            ->sortByDesc(fn($r) => (float) rtrim($r[3], '%'))->values();

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
            return Excel::download(new ArrayExport($rows, [], 'Conversion Report'), 'conversion-report-' . now()->format('Ymd') . '.xlsx');
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

    // ── Private helpers (identical logic to Admin\ReportsController) ───────────

    private function renderReport(
        string $title,
        string $reportKey,
        string $baseRoute,
        array  $tableHeaders,
        array  $tableRows,
        array  $chartConfig,
        array  $filters,
        array  $filterOptions
    ) {
        return view('admin.reports.report', compact(
            'title', 'reportKey', 'baseRoute',
            'tableHeaders', 'tableRows', 'chartConfig',
            'filters', 'filterOptions'
        ));
    }

    private function base(Request $request): array
    {
        $filters = [
            'date_range' => $request->get('date_range', '30'),
            'source'     => $request->get('source', 'all'),
            'telecaller' => $request->get('telecaller', 'all'),
            'manager'    => $request->get('manager', 'all'),
        ];
        [$startAt, $endAt] = $this->periodRange($filters['date_range']);
        $filterOptions = [
            'sources'     => Lead::query()->select('source')->distinct()->orderBy('source')->pluck('source'),
            'telecallers' => User::where('role', 'telecaller')->where('status', 1)->orderBy('name')->get(['id', 'name']),
            'managers'    => User::where('role', 'manager')->where('status', 1)->orderBy('name')->get(['id', 'name']),
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
            $first   = $firstMap[$lead->id] ?? null;
            $minutes = $first ? $lead->created_at->diffInMinutes($first) : null;
            return [
                'lead_code'        => $lead->lead_code,
                'lead_name'        => $lead->name,
                'telecaller'       => $lead->assignedUser?->name ?? 'Unassigned',
                'created_at'       => $lead->created_at?->format('Y-m-d H:i:s'),
                'first_response_at'=> $first,
                'response_minutes' => $minutes,
            ];
        });
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

    private function periodRange(string $dateRange): array
    {
        $endAt   = now()->endOfDay();
        $startAt = match ($dateRange) {
            '7'       => now()->subDays(7)->startOfDay(),
            '90'      => now()->subDays(90)->startOfDay(),
            'quarter' => now()->startOfQuarter(),
            'year'    => now()->startOfYear(),
            default   => now()->subDays(30)->startOfDay(),
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
