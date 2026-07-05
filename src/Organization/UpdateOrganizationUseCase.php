<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class UpdateOrganizationUseCase implements UpdateOrganizationUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface $organizationsFactory
     */
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $organizationsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
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

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $before, $updated): Organization {
            $organizations = ($this->organizationsFactory)($exec);

            $organizations->update($updated);

            $result = $organizations->findById($input->id);
            assert($result !== null);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization.update',
                entityType: 'organization',
                entityId: $input->id,
                actorId: $actorUserId,
                organizationId: $input->id,
                before: $before,
                after: OrganizationSnapshot::toArray($result),
            ));

            return $result;
        });
    }
}
