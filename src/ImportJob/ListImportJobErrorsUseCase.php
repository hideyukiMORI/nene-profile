<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ListImportJobErrorsUseCase
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    /**
     * @return array{items: list<ImportJobError>, total: int}
     */
    public function execute(int $jobId, int $organizationId, int $limit, int $offset): array
    {
        $job = $this->jobs->findByIdInOrganization($jobId, $organizationId);

        if ($job === null) {
            throw new ImportJobNotFoundException($jobId);
        }

        return [
            'items' => $this->jobs->findErrors($jobId, $limit, $offset),
            'total' => $this->jobs->countErrors($jobId),
        ];
    }
}
