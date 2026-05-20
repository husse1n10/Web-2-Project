<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_id', 'category_id', 'name', 'description',
        'price', 'currency', 'estimated_duration_days',
        'required_documents', 'is_active',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'is_active'          => 'boolean',
    ];

    public static function normalizeCurrency(?string $currency): string
    {
        return strtoupper($currency ?: 'USD');
    }

    public static function decimalPlacesForCurrency(?string $currency): int
    {
        return static::normalizeCurrency($currency) === 'LBP' ? 0 : 2;
    }

    public static function formatCurrencyAmount(
        float|int|string|null $amount,
        ?string $currency = null,
        bool $includeCurrency = true
    ): string {
        $normalizedCurrency = static::normalizeCurrency($currency);
        $numericAmount = (float) ($amount ?? 0);
        $formattedAmount = number_format($numericAmount, static::decimalPlacesForCurrency($normalizedCurrency));

        if (!$includeCurrency) {
            return $formattedAmount;
        }

        return match ($normalizedCurrency) {
            'LBP' => 'LBP ' . $formattedAmount,
            'EUR' => 'EUR ' . $formattedAmount,
            'USD' => '$' . $formattedAmount,
            default => $normalizedCurrency . ' ' . $formattedAmount,
        };
    }

    public static function formatCurrencyBreakdown(iterable $currencyTotals): string
    {
        $totals = [];

        foreach ($currencyTotals as $currency => $total) {
            $normalizedCurrency = static::normalizeCurrency(is_string($currency) ? $currency : null);
            $numericTotal = (float) $total;

            if (!array_key_exists($normalizedCurrency, $totals)) {
                $totals[$normalizedCurrency] = 0.0;
            }

            $totals[$normalizedCurrency] += $numericTotal;
        }

        $totals = array_filter($totals, static fn (float $total): bool => abs($total) > 0.00001);

        if ($totals === []) {
            return '0';
        }

        uksort($totals, static function (string $left, string $right): int {
            $order = [
                'LBP' => 0,
                'USD' => 1,
                'EUR' => 2,
            ];

            $leftRank = $order[$left] ?? 99;
            $rightRank = $order[$right] ?? 99;

            return $leftRank === $rightRank
                ? strcmp($left, $right)
                : $leftRank <=> $rightRank;
        });

        return collect($totals)
            ->map(fn (float $total, string $currency): string => static::formatCurrencyAmount($total, $currency))
            ->implode(', ');
    }

    public function formatAmount(float|int|string|null $amount = null, bool $includeCurrency = true): string
    {
        return static::formatCurrencyAmount($amount ?? $this->price, $this->currency, $includeCurrency);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->formatAmount($this->price);
    }

    public function getFormattedPriceValueAttribute(): string
    {
        return $this->formatAmount($this->price, false);
    }

    public function office()   { return $this->belongsTo(Office::class); }
    public function category() { return $this->belongsTo(ServiceCategory::class, 'category_id'); }
    public function requests() { return $this->hasMany(ServiceRequest::class); }
}
