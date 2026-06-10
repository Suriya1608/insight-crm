<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Followup;
use App\Models\Lead;
use App\Notifications\LeadAssignmentNotification;
use App\Notifications\MissedFollowupEscalationNotification;
use App\Notifications\SlaViolationEscalationNotification;
use App\Notifications\WhatsAppInboundNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class FollowupManagementController extends Controller
{
    private function mapFollowup(Followup $f): array
    {
        $nf   = $f->next_followup;
        $now  = now()->startOfDay();
        $date = $nf ? $nf->startOfDay() : null;

        if ($f->completed_at) {
            $statusLabel = 'completed';
        } elseif ($date && $date->lt($now)) {
            $statusLabel = 'overdue';
        } elseif ($date && $date->eq($now)) {
            $statusLabel = 'today';
        } else {
            $statusLabel = 'upcoming';
        }

        return [
            'id'                 => $f->id,
            'next_followup'      => $nf?->format('Y-m-d'),
            'next_followup_fmt'  => $nf?->format('d M Y'),
            'followup_time'      => $f->followup_time,
            'followup_time_fmt'  => $f->followup_time ? Carbon::parse($f->followup_time)->format('h:i A') : null,
            'remarks'            => $f->remarks,
            'status_label'       => $statusLabel,
            'is_completed'       => (bool) $f->completed_at,
            'lead_id'            => $f->lead_id,
            'encrypted_lead_id'  => $f->lead_id ? encrypt($f->lead_id) : null,
            'lead_code'          => $f->lead?->lead_code,
            'lead_name'          => $f->lead?->name,
            'lead_phone'         => $f->lead?->phone,
            'telecaller_name'    => $f->lead?->assignedUser?->name ?? $f->user?->name,
        ];
    }

    private function kpiCounts(): array
    {
        $leadIds = $this->teamLeadIds();
        $now     = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        $today = Followup::whereIn('lead_id', $leadIds)
            ->whereNull('completed_at')
            ->whereDate('next_followup', $now)
            ->count();

        $overdue = Followup::whereIn('lead_id', $leadIds)
            ->whereNull('completed_at')
            ->where(function ($q) use ($now, $nowTime) {
                $q->whereDate('next_followup', '<', $now)
                  ->orWhere(function ($q2) use ($now, $nowTime) {
                      $q2->whereDate('next_followup', $now)
                         ->whereNotNull('followup_time')
                         ->whereRaw('followup_time < ?', [$nowTime]);
                  });
            })
            ->count();

        $upcoming = Followup::whereIn('lead_id', $leadIds)
            ->whereNull('completed_at')
            ->whereDate('next_followup', '>', $now)
            ->count();

        $missed = Lead::where('assigned_by', Auth::id())
            ->whereHas('followups', function ($q) use ($now) {
                $q->whereNull('completed_at')->whereDate('next_followup', '<', $now);
            })
            ->distinct()
            ->count('assigned_to');

        return compact('today', 'overdue', 'upcoming', 'missed');
    }

    private function scopeFollowups(string $scope): \Illuminate\Database\Eloquent\Builder
    {
        $leadIds = $this->teamLeadIds();
        $now     = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        $q = Followup::with(['lead.assignedUser', 'user'])->whereIn('lead_id', $leadIds)->whereNull('completed_at');

        return match ($scope) {
            'overdue'  => $q->where(function ($q) use ($now, $nowTime) {
                $q->whereDate('next_followup', '<', $now)
                  ->orWhere(fn($q2) => $q2->whereDate('next_followup', $now)->whereNotNull('followup_time')->whereRaw('followup_time < ?', [$nowTime]));
            }),
            'upcoming' => $q->whereDate('next_followup', '>', $now),
            default    => $q->whereDate('next_followup', $now),
        };
    }

    public function exportPdf(Request $request, string $scope)
    {
        $validScopes = ['today', 'overdue', 'upcoming'];
        if (!in_array($scope, $validScopes, true)) {
            abort(404);
        }

        $followups = $this->scopeFollowups($scope)->orderBy('next_followup')->get()->map(fn($f) => $this->mapFollowup($f));

        $titles = ['today' => 'Today Follow-ups', 'overdue' => 'Overdue Follow-ups', 'upcoming' => 'Upcoming Follow-ups'];
        $title  = $titles[$scope];

        $headers = ['#', 'Date & Time', 'Lead', 'Phone', 'Telecaller', 'Remarks', 'Status'];
        $rows    = $followups->values()->map(fn($item, $idx) => [
            $idx + 1,
            ($item['next_followup_fmt'] ?? '—') . ($item['followup_time_fmt'] ? ' ' . $item['followup_time_fmt'] : ''),
            ($item['lead_name'] ?? '—') . ' (' . ($item['lead_code'] ?? '—') . ')',
            $item['lead_phone'] ?? '—',
            $item['telecaller_name'] ?? '—',
            $item['remarks'] ?? '—',
            ucfirst($item['status_label'] ?? '—'),
        ])->all();

        $pdf = Pdf::loadView('exports.manager.followups', [
            'title'       => $title,
            'scope'       => $scope,
            'headers'     => $headers,
            'rows'        => $rows,
            'kpi'         => $this->kpiCounts(),
            'manager'     => Auth::user()->name,
            'generatedAt' => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('followups-' . $scope . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportEmail(Request $request, string $scope)
    {
        $validScopes = ['today', 'overdue', 'upcoming'];
        if (!in_array($scope, $validScopes, true)) {
            return response()->json(['ok' => false, 'message' => 'Invalid scope'], 422);
        }

        $followups = $this->scopeFollowups($scope)->orderBy('next_followup')->get()->map(fn($f) => $this->mapFollowup($f));

        $titles = ['today' => 'Today Follow-ups', 'overdue' => 'Overdue Follow-ups', 'upcoming' => 'Upcoming Follow-ups'];
        $title  = $titles[$scope];

        $headers = ['#', 'Date & Time', 'Lead', 'Phone', 'Telecaller', 'Remarks', 'Status'];
        $rows    = $followups->values()->map(fn($item, $idx) => [
            $idx + 1,
            ($item['next_followup_fmt'] ?? '—') . ($item['followup_time_fmt'] ? ' ' . $item['followup_time_fmt'] : ''),
            ($item['lead_name'] ?? '—') . ' (' . ($item['lead_code'] ?? '—') . ')',
            $item['lead_phone'] ?? '—',
            $item['telecaller_name'] ?? '—',
            $item['remarks'] ?? '—',
            ucfirst($item['status_label'] ?? '—'),
        ])->all();

        $pdfContent = Pdf::loadView('exports.manager.followups', [
            'title'       => $title,
            'scope'       => $scope,
            'headers'     => $headers,
            'rows'        => $rows,
            'kpi'         => $this->kpiCounts(),
            'manager'     => Auth::user()->name,
            'generatedAt' => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape')->output();

        $user     = Auth::user();
        $filename = 'followups-' . $scope . '-' . now()->format('Y-m-d') . '.pdf';

        Mail::send([], [], function ($message) use ($pdfContent, $filename, $user, $title) {
            $message->to($user->email, $user->name)
                ->subject($title . ' — ' . now()->format('d M Y'))
                ->html('<p>Hi ' . e($user->name) . ',</p><p>Please find the <strong>' . e($title) . '</strong> report attached.</p><p>Generated: ' . now()->format('d M Y, h:i A') . '</p>')
                ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
        });

        return response()->json(['ok' => true, 'message' => 'Report sent to ' . $user->email]);
    }

    private function teamLeadIds(): array
    {
        $managerId = Auth::id();
        // Leads directly owned by manager
        $direct = Lead::where('assigned_by', $managerId)->pluck('id');
        // Telecallers who have at least one lead assigned by this manager
        $telecallerIds = Lead::where('assigned_by', $managerId)
            ->whereNotNull('assigned_to')
            ->distinct()
            ->pluck('assigned_to');
        // Also include leads assigned to those telecallers by admin or others
        $indirect = $telecallerIds->isNotEmpty()
            ? Lead::whereIn('assigned_to', $telecallerIds)->pluck('id')
            : collect();

        return $direct->merge($indirect)->unique()->values()->all();
    }

    public function today(Request $request)
    {
        $leadIds = $this->teamLeadIds();

        $followups = Followup::with(['lead.assignedUser', 'user'])
            ->whereIn('lead_id', $leadIds)
            ->whereNull('completed_at')
            ->whereDate('next_followup', now()->toDateString())
            ->orderBy('next_followup')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($f) => $this->mapFollowup($f));

        return Inertia::render('Manager/Followups/Index', [
            'scope'     => 'today',
            'title'     => 'Today Follow-ups',
            'followups' => $followups,
            'kpi'       => $this->kpiCounts(),
        ]);
    }

    public function overdue(Request $request)
    {
        $leadIds = $this->teamLeadIds();
        $nowTime = now()->format('H:i:s');

        $followups = Followup::with(['lead.assignedUser', 'user'])
            ->whereIn('lead_id', $leadIds)
            ->whereNull('completed_at')
            ->where(function ($q) use ($nowTime) {
                $q->whereDate('next_followup', '<', now()->toDateString())
                  ->orWhere(function ($q2) use ($nowTime) {
                      $q2->whereDate('next_followup', now()->toDateString())
                         ->whereNotNull('followup_time')
                         ->whereRaw('followup_time < ?', [$nowTime]);
                  });
            })
            ->orderBy('next_followup')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($f) => $this->mapFollowup($f));

        return Inertia::render('Manager/Followups/Index', [
            'scope'     => 'overdue',
            'title'     => 'Overdue Follow-ups',
            'followups' => $followups,
            'kpi'       => $this->kpiCounts(),
        ]);
    }

    public function upcoming(Request $request)
    {
        $leadIds = $this->teamLeadIds();

        $followups = Followup::with(['lead.assignedUser', 'user'])
            ->whereIn('lead_id', $leadIds)
            ->whereNull('completed_at')
            ->whereDate('next_followup', '>', now()->toDateString())
            ->orderBy('next_followup')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($f) => $this->mapFollowup($f));

        return Inertia::render('Manager/Followups/Index', [
            'scope'     => 'upcoming',
            'title'     => 'Upcoming Follow-ups',
            'followups' => $followups,
            'kpi'       => $this->kpiCounts(),
        ]);
    }

    public function missedByTelecaller(Request $request)
    {
        $rows = Followup::query()
            ->join('leads', 'leads.id', '=', 'followups.lead_id')
            ->leftJoin('users as telecaller', 'telecaller.id', '=', 'leads.assigned_to')
            ->whereDate('followups.next_followup', '<', now()->toDateString())
            ->where('leads.assigned_by', Auth::id())
            ->select(
                'telecaller.id as telecaller_id',
                DB::raw("COALESCE(telecaller.name, 'Unassigned') as telecaller_name"),
                DB::raw('COUNT(followups.id) as missed_count'),
                DB::raw('MIN(followups.next_followup) as oldest_pending'),
                DB::raw('MAX(followups.next_followup) as latest_pending')
            )
            ->groupBy('telecaller.id', 'telecaller.name')
            ->orderByDesc('missed_count')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($row) => [
                'telecaller_id'   => $row->telecaller_id,
                'telecaller_name' => $row->telecaller_name,
                'missed_count'    => (int) $row->missed_count,
                'oldest_pending'  => $row->oldest_pending ? Carbon::parse($row->oldest_pending)->format('d M Y') : '—',
                'latest_pending'  => $row->latest_pending ? Carbon::parse($row->latest_pending)->format('d M Y') : '—',
            ]);

        return Inertia::render('Manager/Followups/Missed', [
            'rows' => $rows,
            'kpi'  => $this->kpiCounts(),
        ]);
    }

    public function calendarData(Request $request): \Illuminate\Http\JsonResponse
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        $myLeadsSubquery = Lead::where('assigned_by', Auth::id())->select('id');

        $query = Followup::whereIn('lead_id', $myLeadsSubquery)
            ->whereYear('next_followup', $year)
            ->whereMonth('next_followup', $month);

        if (Schema::hasColumn('followups', 'completed_at')) {
            $query->whereNull('completed_at');
        }

        $days = $query
            ->selectRaw('DATE(next_followup) as day, COUNT(*) as total')
            ->groupByRaw('DATE(next_followup)')
            ->pluck('total', 'day');

        return response()->json([
            'year'  => $year,
            'month' => $month,
            'days'  => $days,
        ]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        if ($request->user()) {
            $request->user()->unreadNotifications->markAsRead();
        }

        return back();
    }

    public function notificationsSnapshot(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'manager') {
            return response()->json(['ok' => false], 403);
        }

        if (!Schema::hasTable('notifications')) {
            return response()->json([
                'ok'                   => true,
                'badge_count'          => 0,
                'lead_notifications'   => [],
                'followup_notifications' => [],
                'sla_notifications'    => [],
                'whatsapp_notifications' => [],
            ]);
        }

        $allUnread = $user->unreadNotifications()->latest()->limit(30)->get();

        $mapItem = fn($n) => [
            'id'      => $n->id,
            'title'   => $n->data['title']   ?? 'Notification',
            'message' => $n->data['message'] ?? $n->data['body'] ?? '-',
            'link'    => $n->data['link']    ?? '#',
            'time'    => optional($n->created_at)->diffForHumans(),
        ];

        $leadNotifications = $allUnread
            ->where('type', LeadAssignmentNotification::class)
            ->take(5)->map($mapItem)->values();

        $followupNotifications = $allUnread
            ->filter(fn($n) =>
                $n->type === MissedFollowupEscalationNotification::class ||
                ($n->type === SlaViolationEscalationNotification::class && ($n->data['type'] ?? '') === 'followup_reminder')
            )
            ->take(5)->map($mapItem)->values();

        $slaNotifications = $allUnread
            ->filter(fn($n) =>
                $n->type === SlaViolationEscalationNotification::class &&
                ($n->data['type'] ?? '') !== 'followup_reminder'
            )
            ->take(5)->map($mapItem)->values();

        $whatsappNotifications = $allUnread
            ->where('type', WhatsAppInboundNotification::class)
            ->take(5)->map($mapItem)->values();

        $badgeCount = $user->unreadNotifications()->count();

        return response()->json([
            'ok'                     => true,
            'badge_count'            => $badgeCount,
            'lead_notifications'     => $leadNotifications,
            'followup_notifications' => $followupNotifications,
            'sla_notifications'      => $slaNotifications,
            'whatsapp_notifications' => $whatsappNotifications,
        ]);
    }

    /**
     * Fast-poll endpoint for WhatsApp inbound notifications only.
     * Called every 5 s by the real-time toast script.
     *
     * ?after=<ISO-8601 timestamp> — returns notifications created after that time.
     * Omit `after` (first load / login) — returns last 2 h of unread notifications.
     */
    public function whatsappInboxPoll(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'manager') {
            return response()->json(['ok' => false], 403);
        }

        if (!Schema::hasTable('notifications')) {
            return response()->json(['ok' => true, 'items' => [], 'ts' => now()->toISOString()]);
        }

        $after   = $request->query('after');
        $isFirst = !$after;

        $query = $user->unreadNotifications()
            ->where('type', WhatsAppInboundNotification::class);

        if ($after) {
            $query->where('created_at', '>', Carbon::parse($after));
        } else {
            $query->where('created_at', '>', now()->subHours(2));
        }

        $items = $query->latest()->limit(10)->get()->map(fn($n) => [
            'id'      => $n->id,
            'title'   => $n->data['title']   ?? 'New WhatsApp',
            'message' => $n->data['message'] ?? '',
            'link'    => $n->data['link']    ?? '#',
        ])->values();

        return response()->json([
            'ok'       => true,
            'is_first' => $isFirst,
            'items'    => $items,
            'ts'       => now()->toISOString(),
        ]);
    }
}
