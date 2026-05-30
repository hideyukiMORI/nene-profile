<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ImportJobError
{
    public function __construct(
        public int $rawRowNumber,
        public string $message,
        public ?string $rawSnippet = null,
        public ?int $id = null,
        public ?int $importJobId = null,
    ) {
    }
}
