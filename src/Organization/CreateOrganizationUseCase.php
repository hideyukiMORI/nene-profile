<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class CreateOrganizationUseCase implements CreateOrganizationUseCaseInterface
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

    public function execute(?int $actorUserId, CreateOrganizationInput $input): CreateOrganizationOutput
    {
        if ($this->organizations->findBySlug($input->slug) !== null) {
            throw new OrganizationSlugConflictException($input->slug);
        }

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input): CreateOrganizationOutput {
            $organizations = ($this->organizationsFactory)($exec);

            $id = $organizations->save(new Organization(
                name: $input->name,
                slug: $input->slug,
                isActive: $input->isActive,
                customDomain: $input->customDomain,
            ));

            $org = $organizations->findById($id);
            assert($org !== null);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization.created',
                entityType: 'organization',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $id,
                before: null,
                after: OrganizationSnapshot::toArray($org),
            ));

            return new CreateOrganizationOutput(
                id: $id,
                name: $org->name,
                slug: $org->slug,
                isActive: $org->isActive,
                customDomain: $org->customDomain,
                createdAt: (string) $org->createdAt,
                updatedAt: (string) $org->updatedAt,
            );
        });
    }
}
