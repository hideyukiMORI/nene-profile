<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

final readonly class ListAuditLogsUseCase implements ListAuditLogsUseCaseInterface
{
    public function __construct(
        private AuditLogRepositoryInterface $repository,
    ) {
    }

    public function execute(ListAuditLogsInput $input): ListAuditLogsOutput
    {
        if ($input->organizationId !== null) {
            $items = $this->repository->findByOrganization($input->organizationId, $input->limit, $input->offset);
            $total = $this->repository->countByOrganization($input->organizationId);
        } else {
            // superadmin: cross-org view
            $items = $this->repository->findAll($input->limit, $input->offset);
            $total = $this->repository->countAll();
        }

        return new ListAuditLogsOutput(
            items: $items,
            total: $total,
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
