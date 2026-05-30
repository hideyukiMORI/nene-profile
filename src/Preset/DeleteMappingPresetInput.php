<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class DeleteMappingPresetInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
    ) {
    }
}
