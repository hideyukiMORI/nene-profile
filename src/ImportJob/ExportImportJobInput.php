<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ExportImportJobInput
{
    public function __construct(
        public int $jobId,
        public int $organizationId,
    ) {
    }
}
