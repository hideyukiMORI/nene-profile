<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Resolves a (year, month, day) triple to an ISO 8601 date string, applying the
 * two-digit-year pivot (ADR 0003 §2) and validating that the result is a real
 * calendar date. Returns null on any invalid input — the caller turns null into
 * an explicit row error; a guessed date is never emitted.
 */
final class GregorianDateParser
{
    /**
     * @param string $year  1–4 digit year as it appeared in the source
     * @param string $month 1–2 digit month
     * @param string $day   1–2 digit day
     * @return string|null  ISO 8601 `YYYY-MM-DD`, or null if invalid
     */
    public static function toIso(string $year, string $month, string $day, int $yearPivot): ?string
    {
        if (!ctype_digit($year) || !ctype_digit($month) || !ctype_digit($day)) {
            return null;
        }

        $y = (int) $year;
        $m = (int) $month;
        $d = (int) $day;

        // Two-digit year → pivot. 00..pivot-? Years 00–49 → 2000s, 50–99 → 1900s
        // when pivot is 50 (the boundary is configurable).
        if (strlen($year) <= 2) {
            $y = $y < $yearPivot ? 2000 + $y : 1900 + $y;
        }

        if (!checkdate($m, $d, $y)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $y, $m, $d);
    }
}
