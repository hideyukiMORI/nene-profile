<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Parses a Japanese-yen amount string into an integer number of yen.
 *
 * JPY has no sub-unit, so `amount_cents` for JPY is simply the integer yen
 * value (¥1 = 1 unit). Decimals are rejected — a fractional yen is a data error,
 * never silently rounded (ADR 0003, compliance §10).
 *
 * Handles: full-width digits (０-９), thousands separators, currency symbols
 * (¥ / ￥ / 円), surrounding whitespace, and a leading sign.
 */
final class YenAmountParser
{
    /**
     * @return int|null integer yen, or null if the string is empty / not a valid integer amount
     */
    public static function parse(string $raw): ?int
    {
        $normalized = self::normalize($raw);

        if ($normalized === '') {
            return null;
        }

        // Reject decimals explicitly — never round a fractional yen.
        if (str_contains($normalized, '.')) {
            return null;
        }

        if (!preg_match('/^-?\d+$/', $normalized)) {
            return null;
        }

        return (int) $normalized;
    }

    /** True when the string represents an empty/blank amount (no value). */
    public static function isBlank(string $raw): bool
    {
        return self::normalize($raw) === '';
    }

    private static function normalize(string $raw): string
    {
        // Full-width digits and sign to half-width.
        $value = strtr($raw, [
            '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
            '－' => '-', 'ー' => '-', '，' => ',', '．' => '.',
        ]);

        // Strip currency symbols, separators, and whitespace.
        $value = str_replace(['¥', '￥', '円', ',', ' ', "\t", "\u{3000}"], '', $value);

        return trim($value);
    }
}
