<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\CallLog;
use App\Models\WhatsAppMessage;
use App\Traits\Auditable;

class Lead extends Model
{
    use Auditable;

    protected $fillable = [
        'lead_code',
        'name',
        'phone',
        'email',
        'email_valid',
        'gender',
        'dob',
        'address',
        'city',
        'district',
        'state',
        'pincode',
        'service_id',
        'source',
        'source_type',
        'source_category',
        'source_detail',
        'fbclid',
        'utm_campaign',
        'utm_medium',
        'utm_content',
        'utm_term',
        'meta_ad_id',
        'meta_adset_id',
        'meta_campaign_id',
        'meta_form_id',
        'assigned_by',
        'assigned_to',
        'manager_assigned_at',
        'status',
        'sla_escalated_at',
        'sla_level',
        'sla_manager_deadline_at',
        'is_duplicate',
        'merged_into_lead_id',
        'is_active',
    ];

    protected $casts = [
        'sla_escalated_at'        => 'datetime',
        'sla_level'               => 'integer',
        'sla_manager_deadline_at' => 'datetime',
        'manager_assigned_at'     => 'datetime',
        'dob'                     => 'date',
        'is_duplicate'            => 'boolean',
        'email_valid'             => 'boolean',
        'merged_into_lead_id'     => 'integer',
        'service_id'              => 'integer',
        'is_active'               => 'boolean',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function lastActivity(): HasOne
    {
        return $this->hasOne(LeadActivity::class)->latestOfMany('created_at');
    }

    // ─── Computed Accessors ──────────────────────────────────────────────────────

    public function getDaysAgedAttribute(): int
    {
        return (int) $this->created_at?->diffInDays(now()) ?? 0;
    }

    public function getDaysSinceLastActivityAttribute(): int
    {
        $lastAt = $this->relationLoaded('lastActivity')
            ? $this->lastActivity?->created_at
            : $this->activities()->latest('created_at')->value('created_at');

        if (!$lastAt) {
            return $this->days_aged;
        }

        return (int) \Carbon\Carbon::parse($lastAt)->diffInDays(now());
    }
}
