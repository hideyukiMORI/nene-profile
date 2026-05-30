<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

/**
 * One emitted StandardTransaction row (output-schema v1.0). All amounts are
 * integer yen; dates are ISO 8601. Carries the five provenance fields (ADR 0004).
 */
final readonly class NormalizedTransaction
{
    public function __construct(
        public int $rawRowNumber,
        public string $transactionDate,
        public string $valueDate,
        public int $amountCents,
        public string $description,
        public ?string $counterparty,
        public ?int $balanceCents,
        public string $lineHash,
        public string $currency = 'JPY',
        public ?int $id = null,
        public ?int $importJobId = null,
    ) {
    }
}
