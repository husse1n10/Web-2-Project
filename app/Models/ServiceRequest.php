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
        'service_name', 'service_price', 'service_currency',
        'assigned_to', 'due_at',
        'status', 'notes', 'office_notes', 'qr_code',
        'amount_paid', 'payment_method', 'payment_status',
        'transaction_id', 'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'due_at'       => 'datetime',
        'service_price' => 'float',
        'amount_paid'   => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function citizen()    { return $this->belongsTo(User::class, 'citizen_id'); }
    public function service()    { return $this->belongsTo(Service::class)->withTrashed(); }
    public function office()     { return $this->belongsTo(Office::class); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function documents()  { return $this->hasMany(RequestDocument::class); }
    public function statusLogs() { return $this->hasMany(RequestStatusLog::class); }
    public function messages()   { return $this->hasMany(Message::class); }
    public function appointment() { return $this->hasOne(Appointment::class); }
    public function feedback()   { return $this->hasOne(Feedback::class); }

    public function isOverdue(): bool
    {
        if (!$this->due_at) {
            return false;
        }
        if (in_array($this->status, ['completed', 'rejected'], true)) {
            return false;
        }
        return $this->due_at->isPast();
    }

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

    public function snapshotServiceDetails(?Service $service = null): array
    {
        $service ??= $this->service;

        return [
            'service_name' => $service?->name,
            'service_price' => $service?->price,
            'service_currency' => Service::normalizeCurrency($service?->currency),
        ];
    }

    public function getResolvedServiceNameAttribute(): string
    {
        return (string) ($this->service_name ?: $this->service?->name ?: 'Service');
    }

    public function getResolvedServiceCurrencyAttribute(): string
    {
        return Service::normalizeCurrency($this->service_currency ?: $this->service?->currency);
    }

    public function getResolvedServicePriceAttribute(): float
    {
        return (float) ($this->service_price ?? $this->amount_paid ?? $this->service?->price ?? 0);
    }

    public function getRecordedAmountAttribute(): float
    {
        return (float) ($this->amount_paid ?? $this->resolved_service_price);
    }

    public function formatServiceAmount(float|int|string|null $amount = null, bool $includeCurrency = true): string
    {
        return Service::formatCurrencyAmount(
            $amount ?? $this->resolved_service_price,
            $this->resolved_service_currency,
            $includeCurrency
        );
    }

    public function getFormattedServicePriceAttribute(): string
    {
        return $this->formatServiceAmount($this->resolved_service_price);
    }

    public function getFormattedServicePriceValueAttribute(): string
    {
        return $this->formatServiceAmount($this->resolved_service_price, false);
    }

    public function getFormattedRecordedAmountAttribute(): string
    {
        return $this->formatServiceAmount($this->recorded_amount);
    }

    public function canDownloadReceipt(): bool
    {
        return $this->isPaid();
    }

    public function canDownloadApprovalLetter(): bool
    {
        return in_array($this->status, ['approved', 'completed'], true);
    }

    public function canDownloadCertificate(): bool
    {
        return $this->status === 'completed';
    }
}
