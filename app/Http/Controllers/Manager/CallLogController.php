<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Lead;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class CallLogController extends Controller
{
    private function baseQuery(Request $request, int $managerId)
    {
        $myLeadsSubquery = Lead::where('assigned_by', $managerId)->select('id');

        $query = CallLog::with([
            'lead:id,lead_code,name,phone',
            'user:id,name,role',
        ])->whereIn('lead_id', $myLeadsSubquery);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }
        if ($request->filled('telecaller')) {
            $query->where('user_id', (int) $request->input('telecaller'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }

    private function applyScope($query, string $scope)
    {
        switch ($scope) {
            case 'inbound':
                $query->where(function ($q) {
                    $q->where('direction', 'inbound')
                      ->orWhere(fn($q2) => $q2->whereNull('direction')->whereNull('user_id'));
                });
                break;
            case 'outbound':
                $query->where(function ($q) {
                    $q->where('direction', 'outbound')
                      ->orWhere(fn($q2) => $q2->whereNull('direction')->whereNotNull('user_id'));
                });
                break;
            case 'missed':
                $query->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled']);
                break;
        }
        return $query;
    }

    private function kpiCounts(Request $request, int $managerId): array
    {
        $base = $this->baseQuery($request, $managerId);

        $total    = (clone $base)->count();
        $inbound  = (clone $base)->where(fn($q) => $q->where('direction','inbound')->orWhere(fn($q2) => $q2->whereNull('direction')->whereNull('user_id')))->count();
        $outbound = (clone $base)->where(fn($q) => $q->where('direction','outbound')->orWhere(fn($q2) => $q2->whereNull('direction')->whereNotNull('user_id')))->count();
        $missed   = (clone $base)->whereIn('status', ['no-answer','busy','failed','canceled'])->count();
        $completed = (clone $base)->where('status', 'completed')->count();

        $durationRow = (clone $base)->selectRaw('AVG(duration) as avg_d, SUM(duration) as total_d')->first();

        return [
            'total'         => $total,
            'inbound'       => $inbound,
            'outbound'      => $outbound,
            'missed'        => $missed,
            'completed'     => $completed,
            'avg_duration'  => round((float) ($durationRow->avg_d ?? 0)),
            'total_seconds' => (int) ($durationRow->total_d ?? 0),
        ];
    }

    private function mapCall(CallLog $call): array
    {
        $dur = (int) ($call->duration ?? 0);
        $h = floor($dur / 3600); $m = floor(($dur % 3600) / 60); $s = $dur % 60;
        $durationFmt = sprintf('%02d:%02d:%02d', $h, $m, $s);

        return [
            'id'               => $call->id,
            'created_at'       => optional($call->created_at)->format('d M Y, h:i A'),
            'lead_code'        => $call->lead->lead_code ?? ('#' . $call->lead_id),
            'lead_name'        => $call->lead->name ?? 'N/A',
            'lead_phone'       => $call->lead->phone ?? '-',
            'encrypted_lead_id'=> $call->lead_id ? encrypt($call->lead_id) : null,
            'type'             => $call->user_id ? 'outbound' : 'inbound',
            'direction'        => $call->direction ?? null,
            'status'           => $call->status ?? '-',
            'duration'         => $dur,
            'duration_fmt'     => $durationFmt,
            'telecaller'       => $call->user->name ?? 'Not assigned',
        ];
    }

    public function index(Request $request)
    {
        $scope = $request->get('scope', 'all');
        if (!in_array($scope, ['all', 'inbound', 'outbound', 'missed'], true)) {
            $scope = 'all';
        }

        $managerId   = Auth::id();
        $query       = $this->applyScope($this->baseQuery($request, $managerId), $scope)->latest('id');
        $callLogs    = $query->paginate(15)->withQueryString()->through(fn($c) => $this->mapCall($c));

        $myTelecallerIds = Lead::where('assigned_by', $managerId)->whereNotNull('assigned_to')->distinct()->pluck('assigned_to');
        $telecallers     = User::where('role', 'telecaller')->where('status', 1)->whereIn('id', $myTelecallerIds)->orderBy('name')->get(['id', 'name']);
        $statusOptions   = ['initiated','ringing','in-progress','answered','completed','busy','failed','no-answer','canceled'];

        return Inertia::render('Manager/CallLogs/Index', [
            'callLogs'      => $callLogs,
            'telecallers'   => $telecallers,
            'statusOptions' => $statusOptions,
            'scope'         => $scope,
            'filters'       => $request->only(['date', 'telecaller', 'status']),
            'kpi'           => $this->kpiCounts($request, $managerId),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $scope     = $request->get('scope', 'all');
        $managerId = Auth::id();

        $calls = $this->applyScope($this->baseQuery($request, $managerId), $scope)->latest('id')->get()->map(fn($c) => $this->mapCall($c));

        $scopeLabels = ['all' => 'All Calls', 'inbound' => 'Inbound Calls', 'outbound' => 'Outbound Calls', 'missed' => 'Missed Calls'];
        $title       = $scopeLabels[$scope] ?? 'Call Logs';
        $headers     = ['#', 'Date & Time', 'Lead', 'Phone', 'Type', 'Status', 'Duration', 'Telecaller'];
        $rows        = $calls->values()->map(fn($c, $idx) => [
            $idx + 1,
            $c['created_at'] ?? '—',
            ($c['lead_name'] ?? '—') . ' (' . ($c['lead_code'] ?? '—') . ')',
            $c['lead_phone'] ?? '—',
            ucfirst($c['type'] ?? '—'),
            ucfirst($c['status'] ?? '—'),
            $c['duration_fmt'] ?? '00:00:00',
            $c['telecaller'] ?? '—',
        ])->all();

        $pdf = Pdf::loadView('exports.manager.call-logs', [
            'title'       => $title,
            'headers'     => $headers,
            'rows'        => $rows,
            'kpi'         => $this->kpiCounts($request, $managerId),
            'manager'     => Auth::user()->name,
            'generatedAt' => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('call-logs-' . $scope . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportEmail(Request $request)
    {
        $scope     = $request->get('scope', 'all');
        $managerId = Auth::id();

        $calls = $this->applyScope($this->baseQuery($request, $managerId), $scope)->latest('id')->get()->map(fn($c) => $this->mapCall($c));

        $scopeLabels = ['all' => 'All Calls', 'inbound' => 'Inbound Calls', 'outbound' => 'Outbound Calls', 'missed' => 'Missed Calls'];
        $title       = $scopeLabels[$scope] ?? 'Call Logs';
        $headers     = ['#', 'Date & Time', 'Lead', 'Phone', 'Type', 'Status', 'Duration', 'Telecaller'];
        $rows        = $calls->values()->map(fn($c, $idx) => [
            $idx + 1,
            $c['created_at'] ?? '—',
            ($c['lead_name'] ?? '—') . ' (' . ($c['lead_code'] ?? '—') . ')',
            $c['lead_phone'] ?? '—',
            ucfirst($c['type'] ?? '—'),
            ucfirst($c['status'] ?? '—'),
            $c['duration_fmt'] ?? '00:00:00',
            $c['telecaller'] ?? '—',
        ])->all();

        $pdfContent = Pdf::loadView('exports.manager.call-logs', [
            'title'       => $title,
            'headers'     => $headers,
            'rows'        => $rows,
            'kpi'         => $this->kpiCounts($request, $managerId),
            'manager'     => Auth::user()->name,
            'generatedAt' => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape')->output();

        $user     = Auth::user();
        $filename = 'call-logs-' . $scope . '-' . now()->format('Y-m-d') . '.pdf';

        Mail::send([], [], function ($message) use ($pdfContent, $filename, $user, $title) {
            $message->to($user->email, $user->name)
                ->subject($title . ' Report — ' . now()->format('d M Y'))
                ->html('<p>Hi ' . e($user->name) . ',</p><p>Please find the <strong>' . e($title) . '</strong> report attached.</p><p>Generated: ' . now()->format('d M Y, h:i A') . '</p>')
                ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
        });

        return response()->json(['ok' => true, 'message' => 'Report sent to ' . $user->email]);
    }
}
