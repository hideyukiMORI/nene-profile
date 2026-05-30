<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

final readonly class ListMappingPresetsOutput
{
    /**
     * @param list<MappingPreset> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $limit,
        public int $offset,
    ) {
    }
}
