<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadAssignmentService
{
    private const TC_RR_KEY = 'lead_rr_last_telecaller_id';

    public function __construct(
        private ManagerLeadAllocator $managerAllocator,
        private AutomationSettings   $settings,
    ) {}

    public function assignIncomingLead(Lead $lead): void
    {
        $mode = $this->settings->leadAssignmentMode();

        if ($mode === 'open_pool') {
            return;
        }

        $managerId = $this->managerAllocator->resolveManagerIdForIncomingLead();

        if ($managerId) {
            $lead->assigned_by        = $managerId;
            $lead->manager_assigned_at = now();
            $lead->saveQuietly();
        }
    }

    public function claimLead(Lead $lead, int $managerId): void
    {
        $lead->assigned_by         = $managerId;
        $lead->manager_assigned_at = now();
        $lead->save();
    }

    public function roundRobinTelecaller(): ?int
    {
        $ids = User::where('role', 'telecaller')
            ->where('status', 1)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return null;
        }

        if ($ids->count() === 1) {
            return (int) $ids->first();
        }

        return DB::transaction(function () use ($ids) {
            $row    = DB::table('settings')->where('key', self::TC_RR_KEY)->lockForUpdate()->first();
            $lastId = $row ? (int) $row->value : 0;

            $nextId = $ids->first(fn($id) => $id > $lastId) ?? (int) $ids->first();

            DB::table('settings')->updateOrInsert(
                ['key' => self::TC_RR_KEY],
                ['value' => (string) $nextId, 'updated_at' => now(), 'created_at' => now()]
            );

            return $nextId;
        });
    }
}
