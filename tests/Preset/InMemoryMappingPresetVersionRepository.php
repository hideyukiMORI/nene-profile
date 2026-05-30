<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use NeneProfile\Preset\MappingDefinition;
use NeneProfile\Preset\MappingPresetVersion;
use NeneProfile\Preset\MappingPresetVersionRepositoryInterface;

final class InMemoryMappingPresetVersionRepository implements MappingPresetVersionRepositoryInterface
{
    private int $nextId = 1;

    /** @var array<int, MappingPresetVersion> */
    private array $store = [];

    public function findById(int $id): ?MappingPresetVersion
    {
        return $this->store[$id] ?? null;
    }

    public function maxVersionNumber(int $presetId): int
    {
        $max = 0;
        foreach ($this->store as $v) {
            if ($v->presetId === $presetId && $v->versionNumber > $max) {
                $max = $v->versionNumber;
            }
        }

        return $max;
    }

    public function append(int $presetId, int $versionNumber, MappingDefinition $definition): int
    {
        $id = $this->nextId++;
        $this->store[$id] = new MappingPresetVersion(
            id: $id,
            presetId: $presetId,
            versionNumber: $versionNumber,
            definition: $definition,
            createdAt: date('Y-m-d H:i:s'),
        );

        return $id;
    }

    /** Test helper: total versions stored. */
    public function count(): int
    {
        return count($this->store);
    }
}
