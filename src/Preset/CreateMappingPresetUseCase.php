<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class CreateMappingPresetUseCase implements CreateMappingPresetUseCaseInterface
{
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
        private MappingPresetVersionRepositoryInterface $versions,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(?int $actorUserId, CreateMappingPresetInput $input): CreateMappingPresetOutput
    {
        $presetId = $this->presets->save(new MappingPreset(
            id: 0,
            organizationId: $input->organizationId,
            name: $input->name,
            bankLabel: $input->bankLabel,
        ));

        $versionId = $this->versions->append($presetId, 1, $input->definition);
        $this->presets->setCurrentVersion($presetId, $versionId);

        $preset = $this->presets->findByIdInOrganization($presetId, $input->organizationId);
        assert($preset !== null);
        $version = $this->versions->findById($versionId);
        assert($version !== null);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->organizationId,
            action: 'mapping_preset.created',
            entityType: 'mapping_preset',
            entityId: $presetId,
            before: null,
            after: MappingPresetSnapshot::toArray($preset, $version),
        );

        return new CreateMappingPresetOutput(preset: $preset, version: $version);
    }
}
