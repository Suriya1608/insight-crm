<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class CampaignContact extends Model
{
    use Auditable;

    protected $fillable = [
        'campaign_id',
        'name',
        'phone',
        'email',
        'course',
        'city',
        'status',
        'quota',
        'converted_course_id',
        'assigned_to',
        'next_followup',
        'followup_time',
        'call_count',
        'wa_status',
        'wa_sent_at',
        'wa_error',
    ];

    protected $casts = [
        'next_followup' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(CampaignActivity::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'new'            => 'New',
            'assigned'       => 'Assigned',
            'contacted'      => 'Contacted',
            'interested'     => 'Interested',
            'not_interested' => 'Not Interested',
            'converted'      => 'Converted',
            'follow_up'      => 'Follow-up',
            'lost'           => 'Lost',
            default          => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'new'            => 'secondary',
            'assigned'       => 'primary',
            'contacted'      => 'info',
            'interested'     => 'success',
            'not_interested' => 'danger',
            'converted'      => 'success',
            'follow_up'      => 'warning',
            'lost'           => 'dark',
            default          => 'secondary',
        };
    }
}
