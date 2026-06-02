<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface UpdateMappingPresetUseCaseInterface
{
    public function execute(?int $actorUserId, UpdateMappingPresetInput $input): UpdateMappingPresetOutput;
}
