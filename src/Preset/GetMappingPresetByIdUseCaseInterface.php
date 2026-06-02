<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface GetMappingPresetByIdUseCaseInterface
{
    public function execute(GetMappingPresetByIdInput $input): GetMappingPresetByIdOutput;
}
