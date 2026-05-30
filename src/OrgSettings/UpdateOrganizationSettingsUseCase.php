<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class UpdateOrganizationSettingsUseCase implements UpdateOrganizationSettingsUseCaseInterface
{
    private const SUPPORTED_ENCODINGS = ['auto', 'utf-8', 'shift_jis'];

    public function __construct(
        private OrganizationSettingsRepositoryInterface $repository,
        private AuditRecorderInterface $audit,
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

        $this->repository->upsert($updated);

        $persisted = $this->repository->findByOrganizationId($input->organizationId);
        assert($persisted !== null);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->organizationId,
            action: 'organization_settings.updated',
            entityType: 'organization_settings',
            entityId: $input->organizationId,
            before: $before,
            after: OrganizationSettingsSnapshot::toArray($persisted),
        );

        return $persisted;
    }
}
