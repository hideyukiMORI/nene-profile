<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use NeneProfile\Preset\MappingPreset;
use NeneProfile\Preset\MappingPresetRepositoryInterface;

final class InMemoryMappingPresetRepository implements MappingPresetRepositoryInterface
{
    private int $nextId = 1;

    /** @var array<int, MappingPreset> */
    private array $store = [];

    public function findByIdInOrganization(int $id, int $organizationId): ?MappingPreset
    {
        $p = $this->store[$id] ?? null;

        return ($p !== null && $p->organizationId === $organizationId && !$p->isDeleted) ? $p : null;
    }

    /** @return list<MappingPreset> */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array
    {
        $filtered = array_values(array_filter(
            $this->store,
            static fn (MappingPreset $p) => $p->organizationId === $organizationId && !$p->isDeleted,
        ));

        return array_slice($filtered, $offset, $limit);
    }

    public function countByOrganizationId(int $organizationId): int
    {
        return count(array_filter(
            $this->store,
            static fn (MappingPreset $p) => $p->organizationId === $organizationId && !$p->isDeleted,
        ));
    }

    public function save(MappingPreset $preset): int
    {
        $id = $this->nextId++;
        $now = date('Y-m-d H:i:s');
        $this->store[$id] = new MappingPreset(
            id: $id,
            organizationId: $preset->organizationId,
            name: $preset->name,
            bankLabel: $preset->bankLabel,
            currentVersionId: $preset->currentVersionId,
            isDeleted: $preset->isDeleted,
            createdAt: $now,
            updatedAt: $now,
        );

        return $id;
    }

    public function updateMetadata(int $id, string $name, string $bankLabel): void
    {
        $p = $this->store[$id] ?? null;
        if ($p === null) {
            return;
        }
        $this->store[$id] = new MappingPreset(
            id: $p->id,
            organizationId: $p->organizationId,
            name: $name,
            bankLabel: $bankLabel,
            currentVersionId: $p->currentVersionId,
            isDeleted: $p->isDeleted,
            createdAt: $p->createdAt,
            updatedAt: date('Y-m-d H:i:s'),
        );
    }

    public function setCurrentVersion(int $id, int $versionId): void
    {
        $p = $this->store[$id] ?? null;
        if ($p === null) {
            return;
        }
        $this->store[$id] = new MappingPreset(
            id: $p->id,
            organizationId: $p->organizationId,
            name: $p->name,
            bankLabel: $p->bankLabel,
            currentVersionId: $versionId,
            isDeleted: $p->isDeleted,
            createdAt: $p->createdAt,
            updatedAt: date('Y-m-d H:i:s'),
        );
    }

    public function softDelete(int $id): void
    {
        $p = $this->store[$id] ?? null;
        if ($p === null) {
            return;
        }
        $this->store[$id] = new MappingPreset(
            id: $p->id,
            organizationId: $p->organizationId,
            name: $p->name,
            bankLabel: $p->bankLabel,
            currentVersionId: $p->currentVersionId,
            isDeleted: true,
            createdAt: $p->createdAt,
            updatedAt: date('Y-m-d H:i:s'),
        );
    }
}
