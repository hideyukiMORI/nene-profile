<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class DeleteOrganizationUseCase implements DeleteOrganizationUseCaseInterface
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

    public function execute(?int $actorUserId, DeleteOrganizationInput $input): void
    {
        $org = $this->organizations->findById($input->id);

        if ($org === null) {
            throw new OrganizationNotFoundException($input->id);
        }

        $snapshot = OrganizationSnapshot::toArray($org);

        $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $snapshot): void {
            $organizations = ($this->organizationsFactory)($exec);

            $organizations->delete($input->id);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization.deleted',
                entityType: 'organization',
                entityId: $input->id,
                actorId: $actorUserId,
                organizationId: $input->id,
                before: $snapshot,
                after: null,
            ));
        });
    }
}
