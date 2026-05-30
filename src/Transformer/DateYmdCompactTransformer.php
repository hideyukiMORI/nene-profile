<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Parses `YYYYMMDD` (8 digits) to ISO 8601. The compact form is unambiguous, so
 * a 4-digit year is always required.
 */
final readonly class DateYmdCompactTransformer implements TransformerInterface
{
    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('date_ymd_compact expects a single source column.');
        }

        $value = trim($source);

        if (!preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m)) {
            return TransformOutcome::error("unparseable date '{$source}' (expected YYYYMMDD)");
        }

        $iso = GregorianDateParser::toIso($m[1], $m[2], $m[3], $context->yearPivot);

        return $iso !== null
            ? TransformOutcome::ok($iso)
            : TransformOutcome::error("invalid calendar date '{$source}'");
    }
}
