<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Parses a single yen amount column to an integer (e.g. balance, or a
 * pre-signed amount). Sign is preserved as-is; for inflow/outflow conventions
 * use debit_credit_to_signed_cents or single_column_signed_cents.
 */
final readonly class AmountYenToCentsTransformer implements TransformerInterface
{
    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('amount_yen_to_cents expects a single source column.');
        }

        if (YenAmountParser::isBlank($source)) {
            return TransformOutcome::error('no amount value');
        }

        $value = YenAmountParser::parse($source);

        return $value !== null
            ? TransformOutcome::ok($value)
            : TransformOutcome::error("unparseable amount '{$source}'");
    }
}
