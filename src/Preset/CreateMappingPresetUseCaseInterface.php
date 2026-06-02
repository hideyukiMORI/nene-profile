<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface CreateMappingPresetUseCaseInterface
{
    public function execute(?int $actorUserId, CreateMappingPresetInput $input): CreateMappingPresetOutput;
}
