<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class GetMappingPresetByIdInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
    ) {
    }
}
