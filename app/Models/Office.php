<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'municipality_id', 'name', 'address', 'latitude', 'longitude',
        'phone', 'email', 'website', 'working_hours', 'logo', 'is_active',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'is_active'     => 'boolean',
    ];

    public function municipality()  { return $this->belongsTo(Municipality::class); }
    public function users()         { return $this->belongsToMany(User::class, 'office_users')->withPivot('role'); }
    public function services()      { return $this->hasMany(Service::class); }
    public function categories()    { return $this->hasMany(ServiceCategory::class); }
    public function requests()      { return $this->hasMany(ServiceRequest::class); }
    public function appointments()  { return $this->hasMany(Appointment::class); }
    public function feedbacks()     { return $this->hasMany(Feedback::class); }

    public function averageRating()
    {
        return $this->feedbacks()->avg('rating');
    }

    /**
     * Return available 30-minute booking slots for the given date as ["HH:MM", ...].
     * Reads working_hours JSON ({"mon": "08:00-16:00", "sat": "closed", ...}),
     * generates half-hour slots within the range, excludes ones already booked
     * (scheduled|confirmed appointments), and excludes past slots for today.
     */
    public function availableSlotsForDate(\Carbon\Carbon $date): array
    {
        $hours = $this->working_hours ?? [];
        $dayKey = strtolower($date->format('D')); // "mon", "tue", ...
        $window = $hours[$dayKey] ?? null;

        if (!$window || !is_string($window) || strtolower($window) === 'closed') {
            return [];
        }

        if (!preg_match('/^(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})$/', trim($window), $m)) {
            return [];
        }

        $start = \Carbon\Carbon::parse($date->format('Y-m-d') . " {$m[1]}:{$m[2]}");
        $end   = \Carbon\Carbon::parse($date->format('Y-m-d') . " {$m[3]}:{$m[4]}");

        if ($end->lessThanOrEqualTo($start)) {
            return [];
        }

        $now = now();
        $cursor = $start->copy();
        $slots = [];
        while ($cursor->lessThan($end)) {
            // Skip past slots if booking today.
            if ($date->isToday() && $cursor->lessThanOrEqualTo($now)) {
                $cursor->addMinutes(30);
                continue;
            }
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes(30);
        }

        $booked = Appointment::where('office_id', $this->id)
            ->whereDate('appointment_date', $date->format('Y-m-d'))
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->pluck('appointment_time')
            ->map(fn ($t) => substr((string) $t, 0, 5))
            ->all();

        return array_values(array_diff($slots, $booked));
    }
}
