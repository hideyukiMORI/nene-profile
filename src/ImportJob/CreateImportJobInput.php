<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class CreateImportJobInput
{
    public function __construct(
        public int $organizationId,
        public ?int $actorUserId,
        public int $presetId,
        public string $originalFilename,
        public string $fileContents,
    ) {
    }
}
