<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Parses `YYYY/MM/DD` or `YY/MM/DD` (also single-digit month/day) to ISO 8601.
 * Two-digit years use the context pivot (ADR 0003 §2).
 */
final readonly class DateYmdSlashTransformer implements TransformerInterface
{
    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('date_ymd_slash expects a single source column.');
        }

        $value = trim($source);

        if (!preg_match('#^(\d{1,4})/(\d{1,2})/(\d{1,2})$#', $value, $m)) {
            return TransformOutcome::error("unparseable date '{$source}' (expected YYYY/MM/DD)");
        }

        $iso = GregorianDateParser::toIso($m[1], $m[2], $m[3], $context->yearPivot);

        return $iso !== null
            ? TransformOutcome::ok($iso)
            : TransformOutcome::error("invalid calendar date '{$source}'");
    }
}
