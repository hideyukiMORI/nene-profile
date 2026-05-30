<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface UpdateMappingPresetUseCaseInterface
{
    /**
     * @return array{preset: MappingPreset, version: ?MappingPresetVersion}
     */
    public function execute(?int $actorUserId, UpdateMappingPresetInput $input): array;
}
