<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupportTicketMessage extends Model
{
    protected $fillable = [
        'support_ticket_id', 'sender_id', 'body',
        'attachment', 'attachment_name', 'attachment_size',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $appends = ['attachment_url'];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? Storage::url($this->attachment) : null;
    }
}
