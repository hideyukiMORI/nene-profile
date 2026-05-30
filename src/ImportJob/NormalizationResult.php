<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

/**
 * The outcome of normalizing a parsed CSV: the emitted rows and the per-row
 * errors. The job status is derived from these (completed vs completed_with_errors).
 */
final readonly class NormalizationResult
{
    /**
     * @param list<NormalizedTransaction> $transactions
     * @param list<ImportJobError>        $errors
     */
    public function __construct(
        public array $transactions,
        public array $errors,
        public int $processedRowCount,
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function deriveStatus(): string
    {
        return $this->hasErrors()
            ? ImportJob::STATUS_COMPLETED_WITH_ERRORS
            : ImportJob::STATUS_COMPLETED;
    }
}
