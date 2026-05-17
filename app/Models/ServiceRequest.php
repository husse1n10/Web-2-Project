<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number', 'citizen_id', 'service_id', 'office_id',
        'status', 'notes', 'office_notes', 'qr_code',
        'amount_paid', 'payment_method', 'payment_status',
        'transaction_id', 'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function citizen()    { return $this->belongsTo(User::class, 'citizen_id'); }
    public function service()    { return $this->belongsTo(Service::class); }
    public function office()     { return $this->belongsTo(Office::class); }
    public function documents()  { return $this->hasMany(RequestDocument::class); }
    public function statusLogs() { return $this->hasMany(RequestStatusLog::class); }
    public function messages()   { return $this->hasMany(Message::class); }
    public function appointment() { return $this->hasOne(Appointment::class); }

    // ── Helpers ───────────────────────────────────────────────────
    public static function generateReference(): string
    {
        // Random 8-char suffix prevents enumeration of the public /track/{ref} page.
        // Race-safe (no concurrent counter read) and 36^8 ≈ 2.8 trillion combinations.
        // Year prefix kept for human readability ("which year was this submitted?").
        $year = now()->year;

        do {
            $suffix = strtoupper(Str::random(8));
            $reference = 'SRQ-' . $year . '-' . $suffix;
        } while (static::where('reference_number', $reference)->exists());

        return $reference;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
