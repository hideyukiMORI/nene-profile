<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class DeleteOrganizationUseCase implements DeleteOrganizationUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(?int $actorUserId, DeleteOrganizationInput $input): void
    {
        $org = $this->organizations->findById($input->id);

        if ($org === null) {
            throw new OrganizationNotFoundException($input->id);
        }

        $snapshot = OrganizationSnapshot::toArray($org);

        $this->organizations->delete($input->id);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->id,
            action: 'organization.deleted',
            entityType: 'organization',
            entityId: $input->id,
            before: $snapshot,
            after: null,
        );
    }
}
