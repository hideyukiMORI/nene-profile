<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * @phpstan-type PresetWithVersion array{preset: MappingPreset, version: MappingPresetVersion}
 */
interface CreateMappingPresetUseCaseInterface
{
    /**
     * @return array{preset: MappingPreset, version: MappingPresetVersion}
     */
    public function execute(?int $actorUserId, CreateMappingPresetInput $input): array;
}
