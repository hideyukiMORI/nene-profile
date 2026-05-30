<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use RuntimeException;

final class MappingPresetNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Mapping preset not found: {$id}");
    }
}
