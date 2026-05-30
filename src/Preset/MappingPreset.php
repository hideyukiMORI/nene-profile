<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class MappingPreset
{
    public function __construct(
        public int $id,
        public int $organizationId,
        public string $name,
        public string $bankLabel,
        public ?int $currentVersionId = null,
        public bool $isDeleted = false,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
