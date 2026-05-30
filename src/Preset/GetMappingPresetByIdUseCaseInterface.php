<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface GetMappingPresetByIdUseCaseInterface
{
    /**
     * @return array{preset: MappingPreset, version: ?MappingPresetVersion}
     */
    public function execute(GetMappingPresetByIdInput $input): array;
}
