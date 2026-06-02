<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ListImportJobErrorsInput
{
    public function __construct(
        public int $jobId,
        public int $organizationId,
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
