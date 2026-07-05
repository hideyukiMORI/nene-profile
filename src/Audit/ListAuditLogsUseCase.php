<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

use Nene2\Audit\AuditEventRepositoryInterface;
use Nene2\Audit\AuditQuery;

final readonly class ListAuditLogsUseCase implements ListAuditLogsUseCaseInterface
{
    public function __construct(
        private AuditEventRepositoryInterface $repository,
    ) {
    }

    public function execute(ListAuditLogsInput $input): ListAuditLogsOutput
    {
        // organizationId null means "no org filter": superadmin cross-org view.
        // ListAuditLogsHandler resolves null vs. tenant-scoped by role.
        $query = new AuditQuery(organizationId: $input->organizationId);

        return new ListAuditLogsOutput(
            items: $this->repository->query($query, $input->limit, $input->offset),
            total: $this->repository->count($query),
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
