<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(?string $phone, ?string $defaultCountryCode = null): ?string
    {
        if (!is_string($phone)) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        if (preg_match('/^[\d\s()+-]+$/', $phone) !== 1) {
            return null;
        }

        if (substr_count($phone, '+') > 1) {
            return null;
        }

        if (str_contains(substr($phone, 1), '+')) {
            return null;
        }

        $clean = preg_replace('/[^\d+]/', '', $phone);
        if (!is_string($clean) || $clean === '') {
            return null;
        }

        if (str_starts_with($clean, '00')) {
            $clean = '+' . substr($clean, 2);
        }

        if (!str_starts_with($clean, '+')) {
            $defaultCode = (string) ($defaultCountryCode ?? config('services.sms.default_country_code', '+961'));
            $defaultCode = str_starts_with($defaultCode, '+') ? $defaultCode : '+' . $defaultCode;

            if (str_starts_with($clean, '0')) {
                $clean = substr($clean, 1);
            }

            $clean = $defaultCode . $clean;
        }

        return preg_match('/^\+\d{8,15}$/', $clean) === 1 ? $clean : null;
    }
}
