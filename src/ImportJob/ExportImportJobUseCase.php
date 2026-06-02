<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ExportImportJobUseCase implements ExportImportJobUseCaseInterface
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    public function execute(ExportImportJobInput $input): ExportImportJobOutput
    {
        $job = $this->jobs->findByIdInOrganization($input->jobId, $input->organizationId);

        if ($job === null) {
            throw new ImportJobNotFoundException($input->jobId);
        }

        return new ExportImportJobOutput(
            job: $job,
            transactions: $this->jobs->findTransactions($input->jobId),
        );
    }
}
