<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class UpdateMappingPresetUseCase implements UpdateMappingPresetUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): MappingPresetRepositoryInterface $presetsFactory
     * @param Closure(DatabaseQueryExecutorInterface): MappingPresetVersionRepositoryInterface $versionsFactory
     */
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
        private MappingPresetVersionRepositoryInterface $versions,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $presetsFactory,
        private Closure $versionsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
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

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $before, $name, $bankLabel): UpdateMappingPresetOutput {
            $presets  = ($this->presetsFactory)($exec);
            $versions = ($this->versionsFactory)($exec);

            if ($input->name !== null || $input->bankLabel !== null) {
                $presets->updateMetadata($input->id, $name, $bankLabel);
            }

            if ($input->definition !== null) {
                $nextVersion = $versions->maxVersionNumber($input->id) + 1;
                $versionId = $versions->append($input->id, $nextVersion, $input->definition);
                $presets->setCurrentVersion($input->id, $versionId);
            }

            $updated = $presets->findByIdInOrganization($input->id, $input->organizationId);
            assert($updated !== null);
            $currentVersion = $updated->currentVersionId !== null
                ? $versions->findById($updated->currentVersionId)
                : null;

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'mapping_preset.updated',
                entityType: 'mapping_preset',
                entityId: $input->id,
                actorId: $actorUserId,
                organizationId: $input->organizationId,
                before: $before,
                after: MappingPresetSnapshot::toArray($updated, $currentVersion),
            ));

            return new UpdateMappingPresetOutput(preset: $updated, version: $currentVersion);
        });
    }
}
