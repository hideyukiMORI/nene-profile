<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface ListMappingPresetsUseCaseInterface
{
    public function execute(ListMappingPresetsInput $input): ListMappingPresetsOutput;
}
