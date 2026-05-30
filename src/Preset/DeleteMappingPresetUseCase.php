<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class DeleteMappingPresetUseCase implements DeleteMappingPresetUseCaseInterface
{
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
        private AuditRecorderInterface $audit,
    ) {
    }

    /**
     * Soft-deletes the preset. Versions referenced by completed import jobs are
     * retained in the database (ADR 0004); only the preset row is flagged deleted.
     */
    public function execute(?int $actorUserId, DeleteMappingPresetInput $input): void
    {
        $existing = $this->presets->findByIdInOrganization($input->id, $input->organizationId);

        if ($existing === null) {
            throw new MappingPresetNotFoundException($input->id);
        }

        $before = MappingPresetSnapshot::toArray($existing);

        $this->presets->softDelete($input->id);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->organizationId,
            action: 'mapping_preset.deleted',
            entityType: 'mapping_preset',
            entityId: $input->id,
            before: $before,
            after: null,
        );
    }
}
