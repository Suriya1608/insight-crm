<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = ['name', 'language', 'display_name', 'preview_text', 'status'];

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('status', 'active');
    }
}
