<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class DeleteMappingPresetUseCase implements DeleteMappingPresetUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): MappingPresetRepositoryInterface $presetsFactory
     */
    public function __construct(
        private MappingPresetRepositoryInterface $presets,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $presetsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
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

        $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $before): void {
            $presets = ($this->presetsFactory)($exec);

            $presets->softDelete($input->id);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'mapping_preset.deleted',
                entityType: 'mapping_preset',
                entityId: $input->id,
                actorId: $actorUserId,
                organizationId: $input->organizationId,
                before: $before,
                after: null,
            ));
        });
    }
}
