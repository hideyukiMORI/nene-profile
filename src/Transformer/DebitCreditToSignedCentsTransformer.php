<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Merges two columns — [deposit, withdrawal] — into one signed integer per the
 * fixed sign convention (ADR 0003 §1): deposit (inflow) is positive, withdrawal
 * (outflow) is negative.
 *
 * Error cases (never guessed — ADR 0003 §1):
 *  - both columns non-zero  → ambiguous, error
 *  - both columns blank/zero → no amount, error
 */
final readonly class DebitCreditToSignedCentsTransformer implements TransformerInterface
{
    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (!is_array($source) || count($source) !== 2) {
            return TransformOutcome::error('debit_credit_to_signed_cents expects exactly two source columns [deposit, withdrawal].');
        }

        [$depositRaw, $withdrawalRaw] = $source;

        $deposit = self::amountOrZero($depositRaw);
        $withdrawal = self::amountOrZero($withdrawalRaw);

        if ($deposit === false || $withdrawal === false) {
            return TransformOutcome::error('unparseable amount in deposit/withdrawal columns');
        }

        $depositNonZero = $deposit !== 0;
        $withdrawalNonZero = $withdrawal !== 0;

        if ($depositNonZero && $withdrawalNonZero) {
            return TransformOutcome::error('both debit and credit non-zero');
        }

        if (!$depositNonZero && !$withdrawalNonZero) {
            return TransformOutcome::error('no amount (both debit and credit empty)');
        }

        // Deposit positive, withdrawal negative. Use absolute magnitude so a
        // bank that writes withdrawals as a positive number still becomes negative.
        $signed = $depositNonZero ? abs($deposit) : -abs($withdrawal);

        return TransformOutcome::ok($signed);
    }

    /** @return int|false 0 for blank; false when present but unparseable. */
    private static function amountOrZero(string $raw): int|false
    {
        if (YenAmountParser::isBlank($raw)) {
            return 0;
        }

        $value = YenAmountParser::parse($raw);

        return $value ?? false;
    }
}
