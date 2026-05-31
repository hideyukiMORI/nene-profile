<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class UpdateOrganizationUseCase implements UpdateOrganizationUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(?int $actorUserId, UpdateOrganizationInput $input): Organization
    {
        $org = $this->organizations->findById($input->id);

        if ($org === null) {
            throw new OrganizationNotFoundException($input->id);
        }

        $newSlug = $input->slug ?? $org->slug;

        // Slug uniqueness check only when the slug actually changes.
        if ($newSlug !== $org->slug && $this->organizations->findBySlug($newSlug) !== null) {
            throw new OrganizationSlugConflictException($newSlug);
        }

        $before = OrganizationSnapshot::toArray($org);

        $updated = new Organization(
            name: $input->name ?? $org->name,
            slug: $newSlug,
            isActive: $input->isActive ?? $org->isActive,
            id: $org->id,
            customDomain: $input->customDomainProvided ? $input->customDomain : $org->customDomain,
            createdAt: $org->createdAt,
        );

        $this->organizations->update($updated);

        $result = $this->organizations->findById($input->id);
        assert($result !== null);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->id,
            action: 'organization.update',
            entityType: 'organization',
            entityId: $input->id,
            before: $before,
            after: OrganizationSnapshot::toArray($result),
        );

        return $result;
    }
}
