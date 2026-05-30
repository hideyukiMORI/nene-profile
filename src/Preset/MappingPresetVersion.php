<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class MappingPresetVersion
{
    public function __construct(
        public int $id,
        public int $presetId,
        public int $versionNumber,
        public MappingDefinition $definition,
        public ?string $createdAt = null,
    ) {
    }
}
