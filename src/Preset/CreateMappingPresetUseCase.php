<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class CreateMappingPresetUseCase implements CreateMappingPresetUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): MappingPresetRepositoryInterface $presetsFactory
     * @param Closure(DatabaseQueryExecutorInterface): MappingPresetVersionRepositoryInterface $versionsFactory
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $presetsFactory,
        private Closure $versionsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
    ) {
    }

    public function execute(?int $actorUserId, CreateMappingPresetInput $input): CreateMappingPresetOutput
    {
        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input): CreateMappingPresetOutput {
            $presets  = ($this->presetsFactory)($exec);
            $versions = ($this->versionsFactory)($exec);

            $presetId = $presets->save(new MappingPreset(
                id: 0,
                organizationId: $input->organizationId,
                name: $input->name,
                bankLabel: $input->bankLabel,
            ));

            $versionId = $versions->append($presetId, 1, $input->definition);
            $presets->setCurrentVersion($presetId, $versionId);

            $preset = $presets->findByIdInOrganization($presetId, $input->organizationId);
            assert($preset !== null);
            $version = $versions->findById($versionId);
            assert($version !== null);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'mapping_preset.created',
                entityType: 'mapping_preset',
                entityId: $presetId,
                actorId: $actorUserId,
                organizationId: $input->organizationId,
                before: null,
                after: MappingPresetSnapshot::toArray($preset, $version),
            ));

            return new CreateMappingPresetOutput(preset: $preset, version: $version);
        });
    }
}
