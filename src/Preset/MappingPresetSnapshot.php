<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * Builds the API/audit representation of a preset, optionally including the
 * current version's definition.
 */
final class MappingPresetSnapshot
{
    /** @return array<string, mixed> */
    public static function toArray(MappingPreset $preset, ?MappingPresetVersion $version = null): array
    {
        $data = [
            'id'                 => $preset->id,
            'name'               => $preset->name,
            'bank_label'         => $preset->bankLabel,
            'current_version_id' => $preset->currentVersionId,
            'version_number'     => $version !== null ? $version->versionNumber : 0,
            'is_deleted'         => $preset->isDeleted,
            'created_at'         => $preset->createdAt,
            'updated_at'         => $preset->updatedAt,
        ];

        if ($version !== null) {
            $data['definition'] = $version->definition->toArray();
        }

        return $data;
    }
}
