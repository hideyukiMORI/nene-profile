<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class UpdateMappingPresetUseCase implements UpdateMappingPresetUseCaseInterface
{
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
        private MappingPresetVersionRepositoryInterface $versions,
        private AuditRecorderInterface $audit,
    ) {
    }

    /**
     * Metadata changes update the preset row; a supplied definition appends a
     * NEW version and points current_version_id at it. Existing versions are
     * never mutated (ADR 0004) — immutability is structural, not enforced by a flag.
     */
    public function execute(?int $actorUserId, UpdateMappingPresetInput $input): UpdateMappingPresetOutput
    {
        $existing = $this->presets->findByIdInOrganization($input->id, $input->organizationId);

        if ($existing === null) {
            throw new MappingPresetNotFoundException($input->id);
        }

        $beforeVersion = $existing->currentVersionId !== null
            ? $this->versions->findById($existing->currentVersionId)
            : null;
        $before = MappingPresetSnapshot::toArray($existing, $beforeVersion);

        $name = $input->name ?? $existing->name;
        $bankLabel = $input->bankLabel ?? $existing->bankLabel;

        if ($input->name !== null || $input->bankLabel !== null) {
            $this->presets->updateMetadata($input->id, $name, $bankLabel);
        }

        if ($input->definition !== null) {
            $nextVersion = $this->versions->maxVersionNumber($input->id) + 1;
            $versionId = $this->versions->append($input->id, $nextVersion, $input->definition);
            $this->presets->setCurrentVersion($input->id, $versionId);
        }

        $updated = $this->presets->findByIdInOrganization($input->id, $input->organizationId);
        assert($updated !== null);
        $currentVersion = $updated->currentVersionId !== null
            ? $this->versions->findById($updated->currentVersionId)
            : null;

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->organizationId,
            action: 'mapping_preset.updated',
            entityType: 'mapping_preset',
            entityId: $input->id,
            before: $before,
            after: MappingPresetSnapshot::toArray($updated, $currentVersion),
        );

        return new UpdateMappingPresetOutput(preset: $updated, version: $currentVersion);
    }
}
