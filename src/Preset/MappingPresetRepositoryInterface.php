<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

interface MappingPresetRepositoryInterface
{
    public function findByIdInOrganization(int $id, int $organizationId): ?MappingPreset;

    /** @return list<MappingPreset> Non-deleted presets for the organization. */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array;

    public function countByOrganizationId(int $organizationId): int;

    /** Persist a new preset and return its ID. */
    public function save(MappingPreset $preset): int;

    public function updateMetadata(int $id, string $name, string $bankLabel): void;

    public function setCurrentVersion(int $id, int $versionId): void;

    public function softDelete(int $id): void;
}
