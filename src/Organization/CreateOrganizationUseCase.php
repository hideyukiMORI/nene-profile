<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class CreateOrganizationUseCase implements CreateOrganizationUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(?int $actorUserId, CreateOrganizationInput $input): CreateOrganizationOutput
    {
        if ($this->organizations->findBySlug($input->slug) !== null) {
            throw new OrganizationSlugConflictException($input->slug);
        }

        $id = $this->organizations->save(new Organization(
            name: $input->name,
            slug: $input->slug,
            isActive: $input->isActive,
            customDomain: $input->customDomain,
        ));

        $org = $this->organizations->findById($id);
        assert($org !== null);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $id,
            action: 'organization.created',
            entityType: 'organization',
            entityId: $id,
            before: null,
            after: OrganizationSnapshot::toArray($org),
        );

        return new CreateOrganizationOutput(
            id: $id,
            name: $org->name,
            slug: $org->slug,
            isActive: $org->isActive,
            customDomain: $org->customDomain,
            createdAt: (string) $org->createdAt,
            updatedAt: (string) $org->updatedAt,
        );
    }
}
