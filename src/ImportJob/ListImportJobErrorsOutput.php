<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ListImportJobErrorsOutput
{
    /**
     * @param list<ImportJobError> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $limit,
        public int $offset,
    ) {
    }
}
