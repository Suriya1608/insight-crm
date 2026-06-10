<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppBulkCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\User;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $campaigns   = Campaign::orderBy('name')->get();
        $managers    = User::where('role', 'manager')->orderBy('name')->get();
        $telecallers = User::where('role', 'telecaller')->orderBy('name')->get();

        $query = Campaign::query();

        if ($request->filled('manager')) {
            $query->where('created_by', $request->manager);
        }

        if ($request->filled('campaign')) {
            $query->where('id', $request->campaign);
        }

        $selectedCampaigns = $query->with('contacts')->get();

        $stats = [
            'total_contacts'    => 0,
            'assigned'          => 0,
            'calls_completed'   => 0,
            'whatsapp_sent'     => 0,
            'interested'        => 0,
            'not_interested'    => 0,
            'followups_pending' => 0,
            'converted'         => 0,
        ];

        $perCampaign = [];

        foreach ($selectedCampaigns as $camp) {
            $contactQuery = $camp->contacts();

            if ($request->filled('telecaller')) {
                $contactQuery = $camp->contacts()->where('assigned_to', $request->telecaller);
            }

            if ($request->filled('date_from')) {
                $contactQuery->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $contactQuery->whereDate('created_at', '<=', $request->date_to);
            }

            $allContacts = $contactQuery->get();

            $campStats = [
                'name'              => $camp->name,
                'status'            => $camp->status,
                'manager'           => $camp->createdBy?->name ?? '—',
                'total_contacts'    => $allContacts->count(),
                'assigned'          => $allContacts->whereNotNull('assigned_to')->count(),
                'calls_completed'   => $allContacts->where('call_count', '>', 0)->count(),
                'whatsapp_sent'     => $camp->contacts()
                    ->whereHas('activities', fn($q) => $q->where('type', 'whatsapp'))
                    ->count(),
                'interested'        => $allContacts->where('status', 'interested')->count(),
                'not_interested'    => $allContacts->where('status', 'not_interested')->count(),
                'followups_pending' => $allContacts->whereIn('status', ['callback'])->whereNotNull('next_followup')->count(),
                'converted'         => $allContacts->where('status', 'converted')->count(),
            ];

            $perCampaign[] = $campStats;

            $stats['total_contacts']    += $campStats['total_contacts'];
            $stats['assigned']          += $campStats['assigned'];
            $stats['calls_completed']   += $campStats['calls_completed'];
            $stats['whatsapp_sent']     += $campStats['whatsapp_sent'];
            $stats['interested']        += $campStats['interested'];
            $stats['not_interested']    += $campStats['not_interested'];
            $stats['followups_pending'] += $campStats['followups_pending'];
            $stats['converted']         += $campStats['converted'];
        }

        return view('admin.campaigns.performance', compact(
            'campaigns', 'managers', 'telecallers', 'stats', 'perCampaign'
        ));
    }

    public function contacts(Request $request)
    {
        $query = CampaignContact::with('campaign', 'assignedUser');

        if ($request->filled('campaign')) {
            $query->where('campaign_id', $request->campaign);
        }
        if ($request->filled('telecaller')) {
            $query->where('assigned_to', $request->telecaller);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $contacts    = $query->latest()->paginate(25)->withQueryString();
        $campaigns   = Campaign::orderBy('name')->get();
        $telecallers = User::where('role', 'telecaller')->orderBy('name')->get();

        return view('admin.campaigns.contacts', compact('contacts', 'campaigns', 'telecallers'));
    }

    public function campaigns(Request $request)
    {
        $query = Campaign::withCount('contacts')
            ->withCount(['contacts as wa_sent_count_live' => fn($q) => $q->where('wa_status', 'sent')])
            ->withCount(['contacts as converted_count'    => fn($q) => $q->where('status', 'converted')])
            ->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns   = $query->paginate(20)->withQueryString();
        $waTemplates = WhatsAppTemplate::active()->orderBy('display_name')
            ->get(['name', 'language', 'display_name']);

        return view('admin.campaigns.index', compact('campaigns', 'waTemplates'));
    }

    public function sendWhatsAppBlast(Request $request, int $id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->wa_blast_status === 'sending' || $campaign->wa_blast_status === 'queued') {
            return response()->json(['ok' => false, 'error' => 'A blast is already in progress.'], 422);
        }

        $request->validate([
            'template_name'     => 'required|string|max:100',
            'template_language' => 'nullable|string|max:20',
        ]);

        $total = $campaign->contacts()->count();
        if ($total === 0) {
            return response()->json(['ok' => false, 'error' => 'No contacts in this campaign.'], 422);
        }

        $campaign->contacts()->update(['wa_status' => 'pending', 'wa_sent_at' => null, 'wa_error' => null]);
        $campaign->update([
            'wa_blast_status'  => 'sending',
            'wa_sent_count'    => 0,
            'wa_failed_count'  => 0,
            'wa_last_blast_at' => now(),
        ]);

        try {
            SendWhatsAppBulkCampaignJob::dispatchSync(
                $campaign->id,
                $request->template_name,
                $request->template_language ?? 'en_US',
                Auth::id(),
            );
        } catch (\Throwable $e) {
            $campaign->update(['wa_blast_status' => 'failed']);
            return response()->json(['ok' => false, 'error' => 'Blast failed: ' . $e->getMessage()], 500);
        }

        $campaign->refresh();
        return response()->json([
            'ok'      => true,
            'message' => "WhatsApp blast complete. {$campaign->wa_sent_count} sent, {$campaign->wa_failed_count} failed.",
            'total'   => $total,
        ]);
    }

    public function whatsappBlastStatus(int $id)
    {
        $campaign = Campaign::findOrFail($id);

        // Auto-reset stale queued/sending state (no progress after 5 minutes)
        if (in_array($campaign->wa_blast_status, ['queued', 'sending'])) {
            $stale = $campaign->wa_last_blast_at
                && $campaign->wa_last_blast_at->lt(now()->subMinutes(5))
                && $campaign->wa_sent_count === 0
                && $campaign->wa_failed_count === 0;
            if ($stale) {
                $campaign->update(['wa_blast_status' => 'idle']);
            }
        }

        $total   = $campaign->contacts()->count();
        $sent    = $campaign->contacts()->where('wa_status', 'sent')->count();
        $failed  = $campaign->contacts()->where('wa_status', 'failed')->count();
        $pending = $total - $sent - $failed;

        return response()->json([
            'ok'      => true,
            'status'  => $campaign->wa_blast_status,
            'total'   => $total,
            'sent'    => $sent,
            'failed'  => $failed,
            'pending' => $pending,
        ]);
    }
}
