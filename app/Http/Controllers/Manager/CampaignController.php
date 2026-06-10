<?php

namespace App\Http\Controllers\Manager;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppBulkCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignActivity;
use App\Models\CampaignContact;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignController extends Controller
{
    // ─── Campaign List ────────────────────────────────────────────────────────

    public function index()
    {
        $managerId = Auth::id();

        $statusCounts = Campaign::where('created_by', $managerId)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $myCampaignIds = Campaign::where('created_by', $managerId)->pluck('id');

        $contactStats = CampaignContact::whereIn('campaign_id', $myCampaignIds)
            ->selectRaw('
                COUNT(*) as total_contacts,
                SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted_contacts,
                SUM(CASE WHEN status = "interested" THEN 1 ELSE 0 END) as interested_contacts,
                SUM(COALESCE(call_count, 0)) as total_calls
            ')
            ->first();

        $totalStats = [
            'total'               => $statusCounts->sum(),
            'active'              => (int) $statusCounts->get('active', 0),
            'paused'              => (int) $statusCounts->get('paused', 0),
            'completed'           => (int) $statusCounts->get('completed', 0),
            'total_contacts'      => (int) ($contactStats->total_contacts    ?? 0),
            'converted_contacts'  => (int) ($contactStats->converted_contacts ?? 0),
            'interested_contacts' => (int) ($contactStats->interested_contacts ?? 0),
            'total_calls'         => (int) ($contactStats->total_calls        ?? 0),
        ];

        $campaigns = Campaign::where('created_by', $managerId)
            ->withCount('contacts')
            ->withCount(['contacts as converted_count' => fn($q) => $q->where('status', 'converted')])
            ->withCount(['contacts as called_count'    => fn($q) => $q->where('call_count', '>', 0)])
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($c) => [
                'id'              => $c->id,
                'encrypted_id'    => encrypt($c->id),
                'name'            => $c->name,
                'description'     => $c->description,
                'status'          => $c->status,
                'contacts_count'  => $c->contacts_count,
                'converted_count' => $c->converted_count,
                'called_count'    => $c->called_count,
                'created_at'      => $c->created_at->format('d M Y'),
            ]);

        return Inertia::render('Manager/Campaigns/Index', compact('campaigns', 'totalStats'));
    }

    // ─── Create Campaign Form ─────────────────────────────────────────────────

    public function create()
    {
        return Inertia::render('Manager/Campaigns/Create');
    }

    // ─── Store New Campaign ───────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $campaign = Campaign::create([
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => 'active',
            'created_by'  => Auth::id(),
        ]);

        session()->flash('success', 'Campaign created. Now upload your student database.');
        return Inertia::location(route('manager.campaigns.import', encrypt($campaign->id)));
    }

    // ─── Show Campaign Detail ─────────────────────────────────────────────────

    public function show(Request $request, string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        $query = $campaign->contacts()->with('assignedUser');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('telecaller')) {
            $query->where('assigned_to', $request->telecaller);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $contacts    = $query->latest()->paginate(25)->withQueryString()
            ->through(fn($c) => [
                'id'            => $c->id,
                'encrypted_id'  => encrypt($c->id),
                'name'          => $c->name,
                'phone'         => $c->phone,
                'email'         => $c->email,
                'course'        => $c->course,
                'city'          => $c->city,
                'status'        => $c->status,
                'assigned_to'   => $c->assigned_to,
                'assigned_user' => $c->assignedUser?->name,
                'next_followup' => $c->next_followup?->format('d M Y'),
                'followup_time' => $c->followup_time ? date('h:i A', strtotime($c->followup_time)) : null,
                'call_count'    => $c->call_count,
            ]);

        $telecallers = User::where('role', 'telecaller')->orderBy('name')->get(['id', 'name']);

        $contactCounts = $campaign->contacts()
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');
        $totalContacts = $contactCounts->sum();
        $stats = [
            'total'      => $totalContacts,
            'pending'    => (int) $contactCounts->get('new', 0),
            'interested' => (int) $contactCounts->get('interested', 0),
            'converted'  => (int) $contactCounts->get('converted', 0),
            'called'     => $totalContacts - (int) $contactCounts->get('new', 0),
        ];

        $unassignedCount = $campaign->contacts()->whereNull('assigned_to')->count();

        $assignmentSummary = $campaign->contacts()
            ->selectRaw('assigned_to, count(*) as cnt')
            ->with('assignedUser:id,name')
            ->groupBy('assigned_to')
            ->get()
            ->map(fn($r) => [
                'name' => $r->assignedUser?->name ?? 'Unassigned',
                'cnt'  => $r->cnt,
            ]);

        $campaignData = [
            'id'              => $campaign->id,
            'encrypted_id'    => encrypt($campaign->id),
            'name'            => $campaign->name,
            'description'     => $campaign->description,
            'status'          => $campaign->status,
            'created_at'      => $campaign->created_at->format('d M Y'),
            'import_url'      => route('manager.campaigns.import', encrypt($campaign->id)),
            'status_url'      => route('manager.campaigns.status', encrypt($campaign->id)),
            'distribute_url'  => route('manager.campaigns.distribute', encrypt($campaign->id)),
            'wa_blast_url'    => route('manager.campaigns.whatsapp-blast', encrypt($campaign->id)),
            'wa_status_url'   => route('manager.campaigns.whatsapp-blast.status', encrypt($campaign->id)),
            'wa_blast_status' => $campaign->wa_blast_status ?? 'idle',
            'wa_sent_count'   => (int) ($campaign->wa_sent_count ?? 0),
            'wa_failed_count' => (int) ($campaign->wa_failed_count ?? 0),
            'wa_last_blast_at'=> $campaign->wa_last_blast_at?->format('d M Y H:i'),
        ];

        $waTemplates = WhatsAppTemplate::active()
            ->orderBy('display_name')
            ->get(['id', 'name', 'language', 'display_name', 'preview_text'])
            ->map(fn($t) => [
                'value'    => $t->name,
                'language' => $t->language,
                'label'    => $t->display_name . ($t->preview_text ? ' — ' . \Str::limit($t->preview_text, 50) : ''),
            ]);

        return Inertia::render('Manager/Campaigns/Show', [
            'campaign'          => $campaignData,
            'contacts'          => $contacts,
            'telecallers'       => $telecallers,
            'stats'             => $stats,
            'unassigned_count'  => $unassignedCount,
            'assignment_summary'=> $assignmentSummary,
            'filters'           => $request->only(['status', 'telecaller', 'search']),
            'wa_templates'      => $waTemplates,
        ]);
    }

    // ─── Import Form ──────────────────────────────────────────────────────────

    public function importForm(string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);
        return Inertia::render('Manager/Campaigns/Import', [
            'campaign' => [
                'id'           => $campaign->id,
                'encrypted_id' => encrypt($campaign->id),
                'name'         => $campaign->name,
                'show_url'     => route('manager.campaigns.show', encrypt($campaign->id)),
            ],
            'step' => 'upload',
        ]);
    }

    // ─── Preview Upload ───────────────────────────────────────────────────────

    public function importPreview(Request $request, string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        $request->validate(['file' => 'required|mimes:xlsx,csv,xls']);

        $data = Excel::toArray([], $request->file('file'));
        $rows = $data[0];
        array_shift($rows); // remove header

        // Use last-10-digits as the normalised key for duplicate detection.
        // Stored phones are +91XXXXXXXXXX; stripping non-digits yields 12 chars.
        // Taking the last 10 gives a uniform comparison key regardless of prefix.
        $existingPhones = $campaign->contacts()->pluck('phone')
            ->map(fn($p) => substr(preg_replace('/\D+/', '', (string) $p), -10))
            ->filter(fn($p) => strlen($p) === 10)
            ->flip();                                         // O(1) lookup
        $existingEmails = $campaign->contacts()->whereNotNull('email')->pluck('email')
            ->map(fn($e) => strtolower(trim((string) $e)))
            ->filter()->flip();                              // O(1) lookup

        $preview    = [];
        $seenPhones = [];   // key-based for O(1) within-file dedup
        $seenEmails = [];   // key-based for O(1) within-file dedup

        foreach ($rows as $row) {
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $rawPhone       = trim((string) ($row[1] ?? ''));
            $formattedPhone = $this->normalizePhone($rawPhone);

            // Reject rows with invalid phone numbers
            if ($formattedPhone === null) {
                $preview[] = [
                    'name'           => $row[0] ?? '',
                    'phone'          => $rawPhone,
                    'email'          => $row[2] ?? '',
                    'course'         => $row[3] ?? '',
                    'city'           => $row[4] ?? '',
                    'is_duplicate'   => false,
                    'is_invalid'     => true,
                    'invalid_reason' => 'invalid_phone',
                    'dup_reason'     => '',
                ];
                continue;
            }

            $email = strtolower(trim((string) ($row[2] ?? '')));

            // Reject rows with malformed email
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $preview[] = [
                    'name'           => $row[0] ?? '',
                    'phone'          => $formattedPhone,
                    'email'          => $row[2] ?? '',
                    'course'         => $row[3] ?? '',
                    'city'           => $row[4] ?? '',
                    'is_duplicate'   => false,
                    'is_invalid'     => true,
                    'invalid_reason' => 'invalid_email',
                    'dup_reason'     => '',
                ];
                continue;
            }

            // Normalised 10-digit key for dedup
            $phoneKey    = substr(preg_replace('/\D+/', '', $formattedPhone), -10);
            $dupPhone    = isset($existingPhones[$phoneKey]) || isset($seenPhones[$phoneKey]);
            $dupEmail    = $email !== '' && (isset($existingEmails[$email]) || isset($seenEmails[$email]));
            $isDuplicate = $dupPhone || $dupEmail;

            $preview[] = [
                'name'           => $row[0] ?? '',
                'phone'          => $formattedPhone,
                'email'          => $row[2] ?? '',
                'course'         => $row[3] ?? '',
                'city'           => $row[4] ?? '',
                'is_duplicate'   => $isDuplicate,
                'is_invalid'     => false,
                'invalid_reason' => '',
                'dup_reason'     => $dupPhone ? 'phone' : ($dupEmail ? 'email' : ''),
            ];

            if (!$isDuplicate) {
                $seenPhones[$phoneKey] = true;
                if ($email !== '') {
                    $seenEmails[$email] = true;
                }
            }
        }

        $total         = count($preview);
        $duplicates    = count(array_filter($preview, fn($r) => $r['is_duplicate']));
        $invalidPhone  = count(array_filter($preview, fn($r) => $r['is_invalid'] && $r['invalid_reason'] === 'invalid_phone'));
        $invalidEmail  = count(array_filter($preview, fn($r) => $r['is_invalid'] && $r['invalid_reason'] === 'invalid_email'));
        $invalid       = $invalidPhone + $invalidEmail;
        $insertable    = $total - $duplicates - $invalid;

        $encId = encrypt($campaign->id);
        return Inertia::render('Manager/Campaigns/Import', [
            'campaign' => [
                'id'           => $campaign->id,
                'encrypted_id' => $encId,
                'name'         => $campaign->name,
                'show_url'     => route('manager.campaigns.show', $encId),
            ],
            'step'         => 'preview',
            'preview'      => array_slice($preview, 0, 100),
            'preview_total'=> count($preview),
            'total'        => $total,
            'duplicates'   => $duplicates,
            'invalid'      => $invalid,
            'invalid_phone'=> $invalidPhone,
            'invalid_email'=> $invalidEmail,
            'insertable'   => $insertable,
            'valid_rows'   => array_values(array_filter($preview, fn($r) => !$r['is_duplicate'] && !$r['is_invalid'])),
        ]);
    }

    // ─── Store Imported Contacts ──────────────────────────────────────────────

    public function importStore(Request $request, string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        $rows = json_decode($request->input('contacts_data'), true);
        if (!is_array($rows) || empty($rows)) {
            return back()->with('error', 'No valid records to import.');
        }

        // Use last-10-digit keys for consistent duplicate detection
        $existingPhones = $campaign->contacts()->pluck('phone')
            ->map(fn($p) => substr(preg_replace('/\D+/', '', (string) $p), -10))
            ->filter(fn($p) => strlen($p) === 10)
            ->flip();
        $existingEmails = $campaign->contacts()->whereNotNull('email')->pluck('email')
            ->map(fn($e) => strtolower(trim((string) $e)))
            ->filter()->flip();

        $inserted   = 0;
        $skipped    = 0;
        $seenPhones = [];   // key-based O(1)
        $seenEmails = [];   // key-based O(1)
        $inserts    = [];

        foreach ($rows as $row) {
            $rawPhone       = trim((string) ($row['phone'] ?? ''));
            $formattedPhone = $this->normalizePhone($rawPhone);
            $email          = strtolower(trim((string) ($row['email'] ?? '')));

            // Defense-in-depth: skip if phone or email is still invalid
            if ($formattedPhone === null) {
                $skipped++;
                continue;
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $phoneKey = substr(preg_replace('/\D+/', '', $formattedPhone), -10);
            $dupPhone = isset($existingPhones[$phoneKey]) || isset($seenPhones[$phoneKey]);
            $dupEmail = $email !== '' && (isset($existingEmails[$email]) || isset($seenEmails[$email]));

            if ($dupPhone || $dupEmail) {
                $skipped++;
                continue;
            }

            $inserts[] = [
                'campaign_id' => $campaign->id,
                'name'        => $row['name'] ?? '',
                'phone'       => $formattedPhone,
                'email'       => $email ?: null,
                'course'      => $row['course'] ?? null,
                'city'        => $row['city'] ?? null,
                'status'      => 'new',
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $seenPhones[$phoneKey] = true;
            if ($email !== '') {
                $seenEmails[$email] = true;
            }
            $inserted++;
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            CampaignContact::insert($chunk);
        }

        return redirect()->route('manager.campaigns.show', encrypt($campaign->id))
            ->with('success', "{$inserted} record(s) imported. {$skipped} duplicate(s)/invalid(s) skipped.");
    }

    // ─── Auto-Distribute Contacts Among Telecallers ───────────────────────────

    public function distribute(Request $request, string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        $request->validate([
            'telecaller_ids'   => 'required|array|min:1',
            'telecaller_ids.*' => 'exists:users,id',
        ]);

        $telecallerIds = $request->telecaller_ids;

        $contacts = $campaign->contacts()
            ->whereNull('assigned_to')
            ->pluck('id')
            ->toArray();

        if (empty($contacts)) {
            return back()->with('error', 'No unassigned contacts to distribute.');
        }

        $total     = count($contacts);
        $tcCount   = count($telecallerIds);
        $perPerson = (int) floor($total / $tcCount);
        $remainder = $total % $tcCount;

        $offset = 0;
        foreach ($telecallerIds as $i => $tcId) {
            $count = $perPerson + ($i < $remainder ? 1 : 0);
            $chunk = array_slice($contacts, $offset, $count);
            CampaignContact::whereIn('id', $chunk)->update(['assigned_to' => $tcId]);
            $offset += $count;
        }

        return back()->with('success', "{$total} contacts distributed among {$tcCount} telecaller(s). ~{$perPerson} each.");
    }

    // ─── Update Campaign Status ───────────────────────────────────────────────

    public function updateStatus(Request $request, string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        $request->validate(['status' => 'required|in:draft,active,paused,completed']);
        $campaign->update(['status' => $request->status]);

        return back()->with('success', 'Campaign status updated.');
    }

    // ─── Contact Detail Page ──────────────────────────────────────────────────

    public function contact(string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact  = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $activities      = $contact->activities()->with('createdBy')->latest()->get();
        $contactMessages = WhatsAppMessage::where('campaign_contact_id', $contact->id)
            ->latest()->limit(50)->get()->reverse()->values();

        $telecallers = User::where('role', 'telecaller')->orderBy('name')->get(['id', 'name']);

        $encCampaignId = encrypt($campaign->id);
        $encContactId  = encrypt($contact->id);

        return Inertia::render('Manager/Campaigns/Contact', [
            'campaign' => [
                'id'           => $campaign->id,
                'encrypted_id' => $encCampaignId,
                'name'         => $campaign->name,
            ],
            'contact' => [
                'id'            => $contact->id,
                'encrypted_id'  => $encContactId,
                'name'          => $contact->name,
                'phone'         => $contact->phone,
                'email'         => $contact->email,
                'course'        => $contact->course,
                'city'          => $contact->city,
                'status'        => $contact->status,
                'assigned_to'   => $contact->assigned_to,
                'assigned_user' => $contact->assignedUser?->name,
                'next_followup' => $contact->next_followup?->format('Y-m-d'),
                'followup_time' => $contact->followup_time,
                'call_count'    => $contact->call_count ?? 0,
            ],
            'activities' => $activities->map(fn($a) => [
                'id'          => $a->id,
                'type'        => $a->type,
                'description' => $a->description,
                'meta'        => $a->meta,
                'created_by'  => $a->createdBy?->name ?? '-',
                'created_at'  => $a->created_at->diffForHumans(),
            ]),
            'whatsapp_messages' => $contactMessages->map(fn($m) => [
                'id'             => $m->id,
                'direction'      => $m->direction,
                'body'           => $m->message_body,
                'media_type'     => $m->media_type,
                'media_url'      => $m->media_url ? asset('storage/' . $m->media_url) : null,
                'media_filename' => $m->media_filename,
                'status'         => data_get($m->meta_data, 'meta_status', 'sent'),
                'time'           => $m->created_at?->format('h:i A'),
            ]),
            'telecallers' => $telecallers,
            'urls' => [
                'back'          => route('manager.campaigns.show', $encCampaignId),
                'change_status' => route('manager.campaigns.contact.status', [$encCampaignId, $encContactId]),
                'set_followup'  => route('manager.campaigns.contact.followup', [$encCampaignId, $encContactId]),
                'add_note'      => route('manager.campaigns.contact.note', [$encCampaignId, $encContactId]),
                'log_call'      => route('manager.campaigns.contact.call', [$encCampaignId, $encContactId]),
                'reassign'      => route('manager.campaigns.contact.reassign', [$encCampaignId, $encContactId]),
                'wa_store'      => route('manager.campaigns.contact.whatsapp.store', [$encCampaignId, $encContactId]),
                'wa_media'      => route('manager.campaigns.contact.whatsapp.media', [$encCampaignId, $encContactId]),
                'wa_fetch'      => route('manager.campaigns.contact.whatsapp.fetch', [$encCampaignId, $encContactId]),
            ],
        ]);
    }

    // ─── Update Contact Status (Manager) ─────────────────────────────────────

    public function updateContactStatus(Request $request, string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact  = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $request->validate(['status' => 'required|in:new,assigned,contacted,interested,not_interested,converted,follow_up,lost']);

        $old = $contact->status;
        $contact->update(['status' => $request->status]);

        if ($old !== $request->status) {
            CampaignActivity::create([
                'campaign_contact_id' => $contact->id,
                'type'                => 'status_change',
                'description'         => "Status changed from {$old} to {$request->status}",
                'meta'                => ['old_status' => $old, 'new_status' => $request->status],
                'created_by'          => Auth::id(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Status updated.');
    }

    // ─── Set Follow-Up (Manager) ─────────────────────────────────────────────

    public function setContactFollowup(Request $request, string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact  = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $request->validate([
            'followup_date' => 'required|date|after_or_equal:today',
            'followup_time' => 'nullable|date_format:H:i',
            'notes'         => 'nullable|string|max:500',
            'status'        => 'nullable|in:new,assigned,contacted,interested,not_interested,converted,follow_up,lost',
        ]);

        $updates = ['next_followup' => $request->followup_date, 'followup_time' => $request->followup_time];
        if ($request->filled('status')) {
            $updates['status'] = $request->status;
        }
        $contact->update($updates);

        $timeStr = $request->followup_time ? ' at ' . date('h:i A', strtotime($request->followup_time)) : '';
        $desc = 'Follow-up scheduled for ' . $request->followup_date . $timeStr;
        if ($request->filled('notes')) {
            $desc .= ' — ' . $request->notes;
        }

        CampaignActivity::create([
            'campaign_contact_id' => $contact->id,
            'type'                => 'followup_set',
            'description'         => $desc,
            'meta'                => ['date' => $request->followup_date, 'time' => $request->followup_time, 'notes' => $request->notes],
            'created_by'          => Auth::id(),
        ]);

        return back()->with('success', 'Follow-up scheduled.');
    }

    // ─── Add Note (Manager) ──────────────────────────────────────────────────

    public function addContactNote(Request $request, string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact  = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $request->validate(['note' => 'required|string|max:1000']);

        CampaignActivity::create([
            'campaign_contact_id' => $contact->id,
            'type'                => 'note',
            'description'         => $request->note,
            'created_by'          => Auth::id(),
        ]);

        return back()->with('success', 'Note added.');
    }

    // ─── Log Call (Manager) ──────────────────────────────────────────────────

    public function logContactCall(Request $request, string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact  = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $contact->increment('call_count');

        CampaignActivity::create([
            'campaign_contact_id' => $contact->id,
            'type'                => 'call',
            'description'         => 'Outbound call made',
            'meta'                => [
                'outcome'  => $request->input('outcome', 'called'),
                'duration' => $request->input('duration'),
                'notes'    => $request->input('notes'),
            ],
            'created_by' => Auth::id(),
        ]);

        if ($contact->status === 'new') {
            $contact->update(['status' => 'contacted']);
        }

        return response()->json(['ok' => true]);
    }

    // ─── Log Meeting (Manager) ───────────────────────────────────────────────

    public function logContactMeeting(Request $request, string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $request->validate([
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|date_format:H:i',
            'meeting_type' => 'required|in:online,in_person,phone_call',
            'notes'        => 'nullable|string|max:500',
        ]);

        $typeLabel = ['online' => 'Online', 'in_person' => 'In-Person', 'phone_call' => 'Phone Call'][$request->meeting_type];
        $desc = 'Meeting scheduled for ' . date('d M Y', strtotime($request->meeting_date));
        if ($request->meeting_time) {
            $desc .= ' at ' . date('h:i A', strtotime($request->meeting_time));
        }
        $desc .= " ({$typeLabel})";
        if ($request->filled('notes')) {
            $desc .= ' — ' . $request->notes;
        }

        CampaignActivity::create([
            'campaign_contact_id' => $contact->id,
            'type'                => 'meeting',
            'description'         => $desc,
            'meta'                => [
                'date'  => $request->meeting_date,
                'time'  => $request->meeting_time,
                'type'  => $request->meeting_type,
                'notes' => $request->notes,
            ],
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Meeting scheduled.');
    }

    // ─── Reassign Contact (Manager) ───────────────────────────────────────────

    public function reassignContact(Request $request, string $campaignId, string $contactId)
    {
        $campaignId = decrypt($campaignId);
        $contactId  = decrypt($contactId);

        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($campaignId);
        $contact  = CampaignContact::where('campaign_id', $campaignId)->findOrFail($contactId);

        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $newUser = User::find($request->assigned_to);
        $contact->update(['assigned_to' => $request->assigned_to]);

        CampaignActivity::create([
            'campaign_contact_id' => $contact->id,
            'type'                => 'note',
            'description'         => 'Assigned to ' . ($newUser?->name ?? 'telecaller'),
            'created_by'          => Auth::id(),
        ]);

        return back()->with('success', 'Contact assigned to ' . ($newUser?->name ?? 'telecaller') . '.');
    }

    // ─── Phone Normalisation ──────────────────────────────────────────────────

    /**
     * Normalise a raw phone string to +91XXXXXXXXXX.
     * Returns null when the number cannot be resolved to exactly 10 digits.
     *
     * Accepted inputs (all spaces/hyphens stripped before processing):
     *   7397315203          → +917397315203
     *   917397315203        → +917397315203
     *   +917397315203       → +917397315203
     *   07397315203         → +917397315203
     *   +91 73973 15203     → +917397315203
     * Rejected:
     *   123, 1234567890123, abc+91xyz, empty
     */
    private function normalizePhone(string $raw): ?string
    {
        // Strip all whitespace and common separators
        $stripped = preg_replace('/[\s\-().]+/', '', $raw);
        // Extract only digits
        $digits = preg_replace('/\D+/', '', $stripped);

        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+91' . substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '+91' . substr($digits, 1);
        }

        return null; // invalid
    }

    // ─── Export Campaign Contacts ─────────────────────────────────────────────

    public function exportContacts(Request $request, string $id, string $format)
    {
        if (!in_array($format, ['excel', 'pdf'], true)) {
            abort(404);
        }

        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        $query = $campaign->contacts()->with('assignedUser');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('telecaller')) {
            $query->where('assigned_to', $request->telecaller);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $contacts = $query->latest()->get();

        $headers = ['Name', 'Phone', 'Email', 'Course', 'City', 'Status', 'Assigned To', 'Calls', 'Follow-up', 'Created'];
        $rows = $contacts->map(fn($c) => [
            $c->name,
            $c->phone,
            $c->email ?? '',
            $c->course ?? '',
            $c->city ?? '',
            ucfirst(str_replace('_', ' ', $c->status ?? '')),
            $c->assignedUser?->name ?? 'Unassigned',
            $c->call_count ?? 0,
            $c->next_followup?->format('d M Y') ?? '',
            $c->created_at->format('d M Y'),
        ])->all();

        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $campaign->name));
        $filename = 'campaign-contacts-' . $slug . '-' . now()->format('Ymd');

        if ($format === 'excel') {
            return Excel::download(
                new ArrayExport($rows, $headers, $campaign->name . ' — Contacts'),
                $filename . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.manager.report', [
            'title'       => $campaign->name . ' — Contacts',
            'headers'     => $headers,
            'rows'        => $rows,
            'manager'     => Auth::user()->name,
            'period'      => 'All Time',
            'generatedAt' => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    // ─── Campaign Performance Dashboard ──────────────────────────────────────

    public function performance(Request $request)
    {
        $campaigns   = Campaign::where('created_by', Auth::id())->orderBy('name')->get();
        $telecallers = User::where('role', 'telecaller')->orderBy('name')->get();

        $query = Campaign::where('created_by', Auth::id());

        if ($request->filled('campaign')) {
            $query->where('id', $request->campaign);
        }

        $selectedCampaigns = $query->with('contacts')->get();

        // Aggregate stats across selected campaigns
        $stats = [
            'total_contacts'   => 0,
            'assigned'         => 0,
            'calls_completed'  => 0,
            'whatsapp_sent'    => 0,
            'interested'       => 0,
            'not_interested'   => 0,
            'followups_pending'=> 0,
            'converted'        => 0,
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

            $total    = $allContacts->count();
            $called   = $allContacts->where('call_count', '>', 0)->count();
            $interest = $allContacts->where('status', 'interested')->count();
            $conv     = $allContacts->where('status', 'converted')->count();

            $campStats = [
                'name'              => $camp->name,
                'status'            => $camp->status,
                'total_contacts'    => $total,
                'assigned'          => $allContacts->whereNotNull('assigned_to')->count(),
                'calls_completed'   => $called,
                'whatsapp_sent'     => $camp->contacts()
                    ->whereHas('activities', fn($q) => $q->where('type', 'whatsapp'))
                    ->count(),
                'interested'        => $interest,
                'not_interested'    => $allContacts->where('status', 'not_interested')->count(),
                'followups_pending' => $allContacts->whereIn('status', ['callback'])->whereNotNull('next_followup')->count(),
                'converted'         => $conv,
                'conversion_rate'   => $total > 0 ? round($conv / $total * 100, 1) : 0,
                'contact_rate'      => $total > 0 ? round($called / $total * 100, 1) : 0,
                'interest_rate'     => $called > 0 ? round($interest / $called * 100, 1) : 0,
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

        // Computed overall rates
        $totalC   = $stats['total_contacts'];
        $calledC  = $stats['calls_completed'];
        $stats['conversion_rate'] = $totalC > 0 ? round($stats['converted'] / $totalC * 100, 1) : 0;
        $stats['contact_rate']    = $totalC > 0 ? round($calledC / $totalC * 100, 1) : 0;
        $stats['interest_rate']   = $calledC > 0 ? round($stats['interested'] / $calledC * 100, 1) : 0;

        // Paginate perCampaign array
        $perPage  = 10;
        $page     = max(1, (int) $request->query('page', 1));
        $total    = count($perCampaign);
        $items    = array_slice($perCampaign, ($page - 1) * $perPage, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        $links = [];
        $links[] = ['label' => '&laquo; Previous', 'url' => $page > 1 ? $request->fullUrlWithQuery(['page' => $page - 1]) : null, 'active' => false];
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = ['label' => (string)$i, 'url' => $request->fullUrlWithQuery(['page' => $i]), 'active' => $i === $page];
        }
        $links[] = ['label' => 'Next &raquo;', 'url' => $page < $lastPage ? $request->fullUrlWithQuery(['page' => $page + 1]) : null, 'active' => false];

        return Inertia::render('Manager/Campaigns/Performance', [
            'campaigns'   => $campaigns->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'status' => $c->status]),
            'telecallers' => $telecallers->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            'stats'       => $stats,
            'perCampaign' => [
                'data'         => $items,
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'links'        => $links,
            ],
            'filters'     => $request->only(['campaign', 'telecaller', 'date_from', 'date_to']),
        ]);
    }

    // ─── WhatsApp Bulk Blast ──────────────────────────────────────────────────

    public function sendWhatsAppBlast(Request $request, string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

        if ($campaign->wa_blast_status === 'sending' || $campaign->wa_blast_status === 'queued') {
            return response()->json(['ok' => false, 'error' => 'A blast is already in progress for this campaign.'], 422);
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

    public function whatsappBlastStatus(string $id)
    {
        $id       = decrypt($id);
        $campaign = Campaign::where('created_by', Auth::id())->findOrFail($id);

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
