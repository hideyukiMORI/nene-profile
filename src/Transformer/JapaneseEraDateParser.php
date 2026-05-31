<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Converts a Japanese imperial era date (和暦) to ISO 8601, applying the
 * boundary rules from ADR 0003 §3. Returns either ['ok', 'YYYY-MM-DD'] or
 * ['error', 'message'] — a guessed date is never emitted.
 *
 * Supported eras (ADR 0003 §3):
 *   令和 (Reiwa)  R/令/令和  2019-05-01 – present
 *   平成 (Heisei) H/平/平成  1989-01-08 – 2019-04-30
 *   昭和 (Showa)  S/昭/昭和  1926-12-25 – 1989-01-07
 *   大正 (Taisho) T/大/大正  1912-07-30 – 1926-12-24
 */
final class JapaneseEraDateParser
{
    /**
     * Gregorian offset: era year + offset = Gregorian year.
     * maxYear: maximum valid era year (ADR 0003 §3 table).
     *
     * @var array<string, array{offset: int, maxYear: int, name: string}>
     */
    private const ERAS = [
        'reiwa'  => ['offset' => 2018, 'maxYear' => 99, 'name' => 'Reiwa'],
        'heisei' => ['offset' => 1988, 'maxYear' => 31, 'name' => 'Heisei'],
        'showa'  => ['offset' => 1925, 'maxYear' => 64, 'name' => 'Showa'],
        'taisho' => ['offset' => 1911, 'maxYear' => 15, 'name' => 'Taisho'],
    ];

    /**
     * Accepted prefixes (after ASCII lower-casing).
     * Full era names (令和 etc.) are accepted for bank CSV formats that use them.
     *
     * @var array<string, string>
     */
    private const PREFIX_MAP = [
        'r'   => 'reiwa',  'h'   => 'heisei',  's'  => 'showa',  't'  => 'taisho',
        '令'   => 'reiwa',  '平'   => 'heisei',  '昭' => 'showa',  '大' => 'taisho',
        '令和' => 'reiwa',  '平成' => 'heisei',  '昭和' => 'showa', '大正' => 'taisho',
    ];

    /**
     * Convert an era date to ISO 8601.
     *
     * @param  string $prefix  Era prefix: R/H/S/T (case-insensitive), single kanji (令平昭大),
     *                         or full era name (令和/平成/昭和/大正).
     * @param  string $year    Era year as a 1–2-digit string.
     * @param  string $month   Month as a 1–2-digit string.
     * @param  string $day     Day as a 1–2-digit string.
     * @return array{0: 'ok', 1: string}|array{0: 'error', 1: string}
     */
    public static function toIso(string $prefix, string $year, string $month, string $day): array
    {
        // Lowercase ASCII only; kanji characters are unchanged by strtolower.
        $eraKey = self::PREFIX_MAP[strtolower($prefix)] ?? null;
        if ($eraKey === null) {
            return ['error', "unrecognized era prefix '{$prefix}'"];
        }

        if (!ctype_digit($year) || !ctype_digit($month) || !ctype_digit($day)) {
            return ['error', 'era date components must be numeric'];
        }

        $era = self::ERAS[$eraKey];
        $y = (int) $year;
        $m = (int) $month;
        $d = (int) $day;

        if ($y < 1 || $y > $era['maxYear']) {
            return ['error', "era year {$y} is out of valid range for {$era['name']} (1–{$era['maxYear']})"];
        }

        // ADR 0003 §3 — Reiwa started 2019-05-01; R1 Jan–Apr belongs to Heisei 31.
        if ($eraKey === 'reiwa' && $y === 1 && $m >= 1 && $m <= 4) {
            return ['error', 'Reiwa 1 January–April is not a valid date; use Heisei 31'];
        }

        // ADR 0003 §3 — Heisei ended 2019-04-30; H31 May and later do not exist.
        if ($eraKey === 'heisei' && $y === 31 && $m >= 5) {
            return ['error', 'Heisei 31 May and later are not valid Heisei dates; Heisei ended on H31.4.30'];
        }

        // ADR 0003 §3 — Showa ended 1989-01-07; S64 after January 7 does not exist.
        if ($eraKey === 'showa' && $y === 64 && ($m > 1 || ($m === 1 && $d > 7))) {
            return ['error', 'Showa 64 ended on S64.1.7; dates after January 7 are not valid Showa dates'];
        }

        $gregorianYear = $y + $era['offset'];

        if (!checkdate($m, $d, $gregorianYear)) {
            return ['error', "invalid calendar date for {$era['name']} year {$y} month {$m} day {$d}"];
        }

        return ['ok', sprintf('%04d-%02d-%02d', $gregorianYear, $m, $d)];
    }
}
