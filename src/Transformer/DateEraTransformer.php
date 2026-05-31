<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Converts a Japanese imperial era date string to ISO 8601 YYYY-MM-DD.
 *
 * Accepted formats (ADR 0003 §3):
 *   [PREFIX][YEAR][SEP][MONTH][SEP][DAY]
 *
 *   PREFIX : R/H/S/T (case-insensitive), single kanji 令/平/昭/大,
 *            or full era name 令和/平成/昭和/大正
 *   YEAR   : 1–2-digit era year
 *   SEP    : . / - or kanji 年/月 (trailing 日 is optional)
 *
 * Examples: R6.05.15  H31/4/30  S64-1-7  令和6年5月15日  平成31.4.30
 */
final readonly class DateEraTransformer implements TransformerInterface
{
    private const PATTERN =
        '#^(令和|平成|昭和|大正|[令平昭大RrHhSsTt])(\d{1,2})[.\/\-年](\d{1,2})[.\/\-月](\d{1,2})日?$#u';

    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('date_era expects a single source column.');
        }

        $value = trim($source);

        if (!preg_match(self::PATTERN, $value, $m)) {
            return TransformOutcome::error(
                "unparseable era date '{$source}' (expected e.g. R6.05.15 or 令和6年5月15日)"
            );
        }

        $result = JapaneseEraDateParser::toIso($m[1], $m[2], $m[3], $m[4]);

        return $result[0] === 'ok'
            ? TransformOutcome::ok($result[1])
            : TransformOutcome::error($result[1]);
    }
}
