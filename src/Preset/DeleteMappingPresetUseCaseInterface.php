<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface DeleteMappingPresetUseCaseInterface
{
    public function execute(?int $actorUserId, DeleteMappingPresetInput $input): void;
}
