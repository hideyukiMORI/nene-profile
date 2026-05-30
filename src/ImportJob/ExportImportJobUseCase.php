<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ExportImportJobUseCase
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    /**
     * @return array{job: ImportJob, transactions: list<NormalizedTransaction>}
     */
    public function execute(int $jobId, int $organizationId): array
    {
        $job = $this->jobs->findByIdInOrganization($jobId, $organizationId);

        if ($job === null) {
            throw new ImportJobNotFoundException($jobId);
        }

        return [
            'job'          => $job,
            'transactions' => $this->jobs->findTransactions($jobId),
        ];
    }
}
