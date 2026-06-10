<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Campaign extends Model
{
    use Auditable;

    protected $casts = [
        'wa_last_blast_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'wa_blast_status',
        'wa_sent_count',
        'wa_failed_count',
        'wa_last_blast_at',
    ];

    public function contacts()
    {
        return $this->hasMany(CampaignContact::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalContactsAttribute(): int
    {
        return $this->contacts()->count();
    }

    public function getContactedCountAttribute(): int
    {
        return $this->contacts()->where('status', '!=', 'pending')->count();
    }

    public function getConvertedCountAttribute(): int
    {
        return $this->contacts()->where('status', 'converted')->count();
    }
}
