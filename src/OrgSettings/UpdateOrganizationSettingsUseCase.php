<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class UpdateOrganizationSettingsUseCase implements UpdateOrganizationSettingsUseCaseInterface
{
    private const SUPPORTED_ENCODINGS = ['auto', 'utf-8', 'shift_jis'];

    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationSettingsRepositoryInterface $settingsFactory
     */
    public function __construct(
        private OrganizationSettingsRepositoryInterface $repository,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $settingsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
    ) {
    }

    public function execute(?int $actorUserId, UpdateOrganizationSettingsInput $input): OrganizationSettings
    {
        $current = $this->repository->findByOrganizationId($input->organizationId)
            ?? OrganizationSettings::defaultsFor($input->organizationId);

        $before = OrganizationSettingsSnapshot::toArray($current);

        if ($input->defaultEncoding !== null && !in_array($input->defaultEncoding, self::SUPPORTED_ENCODINGS, true)) {
            throw new EncodingNotSupportedException($input->defaultEncoding);
        }

        $updated = new OrganizationSettings(
            organizationId: $input->organizationId,
            defaultEncoding: $input->defaultEncoding ?? $current->defaultEncoding,
            maxFileSizeBytes: $input->maxFileSizeBytes ?? $current->maxFileSizeBytes,
            clearBearerToken: $input->clearBearerTokenProvided ? $input->clearBearerToken : $current->clearBearerToken,
        );

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $before, $updated): OrganizationSettings {
            $repository = ($this->settingsFactory)($exec);

            $repository->upsert($updated);

            $persisted = $repository->findByOrganizationId($input->organizationId);
            assert($persisted !== null);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization_settings.updated',
                entityType: 'organization_settings',
                entityId: $input->organizationId,
                actorId: $actorUserId,
                organizationId: $input->organizationId,
                before: $before,
                after: OrganizationSettingsSnapshot::toArray($persisted),
            ));

            return $persisted;
        });
    }
}
