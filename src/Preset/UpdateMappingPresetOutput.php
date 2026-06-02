<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class UpdateMappingPresetOutput
{
    public function __construct(
        public MappingPreset $preset,
        public ?MappingPresetVersion $version,
    ) {
    }
}
