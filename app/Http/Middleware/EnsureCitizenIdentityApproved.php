<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCitizenIdentityApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->isCitizen()) {
            return $next($request);
        }

        if (!$user->hasCompletedCitizenProfile()) {
            $missingFields = $user->missingCitizenProfileFields();
            $message = 'Please complete your profile before using citizen services.';

            if (!empty($missingFields)) {
                $message .= ' Missing: ' . implode(', ', $missingFields) . '.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'missing_fields' => $missingFields,
                ], 422);
            }

            return redirect()->route('citizen.profile')->with('error', $message);
        }

        if ($user->hasVerifiedCitizenIdentity()) {
            return $next($request);
        }

        $message = match ($user->citizen_verification_status) {
            'rejected' => 'Your National ID document was rejected. Upload a corrected document and wait for admin approval.',
            default => 'Your National ID document is pending admin validation. You can use the portal after approval.',
        };

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('citizen.profile')->with('warning', $message);
    }
}
