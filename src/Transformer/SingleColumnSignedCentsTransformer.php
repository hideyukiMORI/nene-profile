<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Parses one amount column that carries its own sign, via either a leading
 * minus or a DR/CR suffix (ADR 0003 §1):
 *  - leading `-`  → negative (outflow)
 *  - `DR` suffix  → negative (debit / withdrawal reduces the account)
 *  - `CR` suffix  → positive (credit / deposit increases the account)
 *  - bare positive → positive (inflow)
 *
 * An unrecognized sign marker is an error — never guessed.
 */
final readonly class SingleColumnSignedCentsTransformer implements TransformerInterface
{
    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('single_column_signed_cents expects a single source column.');
        }

        $value = trim($source);

        if (YenAmountParser::isBlank($value)) {
            return TransformOutcome::error('no amount value');
        }

        $sign = 1;
        $upper = strtoupper($value);

        if (str_ends_with($upper, 'DR')) {
            $sign = -1;
            $value = trim(substr($value, 0, -2));
        } elseif (str_ends_with($upper, 'CR')) {
            $sign = 1;
            $value = trim(substr($value, 0, -2));
        }

        $magnitude = YenAmountParser::parse($value);

        if ($magnitude === null) {
            return TransformOutcome::error("unrecognized sign marker or unparseable amount '{$source}'");
        }

        // A leading minus inside the number already encodes the sign; combine
        // with a DR/CR suffix only when no explicit minus was present.
        if ($magnitude < 0) {
            return TransformOutcome::ok($magnitude);
        }

        return TransformOutcome::ok($sign * $magnitude);
    }
}
