<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * Append-only repository for preset versions (ADR 0004). No update or delete:
 * editing a preset always creates a new version.
 */
interface MappingPresetVersionRepositoryInterface
{
    public function findById(int $id): ?MappingPresetVersion;

    /** The highest version_number for a preset, or 0 if none yet. */
    public function maxVersionNumber(int $presetId): int;

    /** Append a new version and return its ID. */
    public function append(int $presetId, int $versionNumber, MappingDefinition $definition): int;
}
