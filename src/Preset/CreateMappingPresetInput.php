<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class CreateMappingPresetInput
{
    public function __construct(
        public int $organizationId,
        public string $name,
        public string $bankLabel,
        public MappingDefinition $definition,
    ) {
    }
}
