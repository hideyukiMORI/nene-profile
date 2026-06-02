<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ExportImportJobOutput
{
    /**
     * @param list<NormalizedTransaction> $transactions
     */
    public function __construct(
        public ImportJob $job,
        public array $transactions,
    ) {
    }
}
