<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'phone_verified_at', 'national_id',
        'id_document',          // column in migration
        'citizen_verification_status',
        'citizen_verification_notes',
        'citizen_verified_at',
        'citizen_verified_by',
        'is_active',
        'two_factor_secret',    // column in migration
        'two_factor_enabled',   // column in migration
        'social_provider', 'social_id', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'citizen_verified_at' => 'datetime',
        'is_active'          => 'boolean',
        'two_factor_enabled' => 'boolean',
    ];

    // ── Role helpers ──────────────────────────────────────────────
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isOfficeUser(): bool { return $this->role === 'office_user'; }
    public function isCitizen(): bool    { return $this->role === 'citizen'; }

    public function hasCompletedCitizenProfile(): bool
    {
        if (!$this->isCitizen()) {
            return true;
        }

        return filled($this->name)
            && filled($this->national_id)
            && filled($this->id_document);
    }

    public function missingCitizenProfileFields(): array
    {
        if (!$this->isCitizen()) {
            return [];
        }

        $missing = [];

        if (blank($this->national_id)) {
            $missing[] = 'National ID';
        }

        if (blank($this->id_document)) {
            $missing[] = 'National ID document';
        }

        return $missing;
    }

    public function hasVerifiedCitizenIdentity(): bool
    {
        return !$this->isCitizen() || $this->citizen_verification_status === 'approved';
    }

    public function isCitizenIdentityPending(): bool
    {
        return $this->isCitizen() && $this->citizen_verification_status === 'pending';
    }

    public function isCitizenIdentityApproved(): bool
    {
        return $this->isCitizen() && $this->citizen_verification_status === 'approved';
    }

    public function isCitizenIdentityRejected(): bool
    {
        return $this->isCitizen() && $this->citizen_verification_status === 'rejected';
    }

    // ── Relationships ─────────────────────────────────────────────
    public function offices()
    {
        return $this->belongsToMany(Office::class, 'office_users')
                    ->withPivot('role')->withTimestamps();
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'citizen_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'citizen_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'citizen_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function citizenVerifier()
    {
        return $this->belongsTo(User::class, 'citizen_verified_by');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function routeNotificationForSms(): ?string
    {
        return $this->phone;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        return Storage::url($this->avatar);
    }
}
