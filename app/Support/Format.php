<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Locale-aware formatting helpers.
 *
 * The app ships in two locales (en / sr).  Carbon's `translatedFormat`
 * does the heavy lifting but requires the intl extension.  Where the
 * extension isn't loaded we fall back to the format strings below
 * (the result is still locale-appropriate, just not e.g. month-name
 * localised).
 *
 *   - en: `Y-m-d`        -> `2026-06-22`
 *   - sr: `d.m.Y.`       -> `22.06.2026.`  (Serbian convention: dot suffix)
 *
 * Datetime variants mirror that pattern with hours/minutes appended.
 *
 * The helper takes anything Carbon can parse so callers can pass
 * `created_at`, `now()`, a string, etc. without thinking about it.
 */
class Format
{
    public static function date(mixed $value): string
    {
        return self::localised($value, 'date');
    }

    public static function datetime(mixed $value): string
    {
        return self::localised($value, 'datetime');
    }

    public static function money(float $amount, string $currency = 'RSD'): string
    {
        $formatted = number_format($amount, 2, '.', ',');
        return "{$formatted} {$currency}";
    }

    private static function localised(mixed $value, string $kind): string
    {
        $carbon = $value instanceof CarbonInterface
            ? $value
            : Carbon::parse((string) $value);

        $locale = app()->getLocale();

        if ($locale === 'sr') {
            return $kind === 'date'
                ? $carbon->format('d.m.Y.')
                : $carbon->format('d.m.Y. H:i');
        }

        // Default / English
        return $kind === 'date'
            ? $carbon->format('Y-m-d')
            : $carbon->format('Y-m-d H:i');
    }
}