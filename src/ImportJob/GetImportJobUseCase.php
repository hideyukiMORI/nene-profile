<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class GetImportJobUseCase
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    public function execute(int $id, int $organizationId): ImportJob
    {
        $job = $this->jobs->findByIdInOrganization($id, $organizationId);

        if ($job === null) {
            throw new ImportJobNotFoundException($id);
        }

        return $job;
    }
}
