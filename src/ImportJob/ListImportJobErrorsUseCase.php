<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ListImportJobErrorsUseCase implements ListImportJobErrorsUseCaseInterface
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    public function execute(ListImportJobErrorsInput $input): ListImportJobErrorsOutput
    {
        $job = $this->jobs->findByIdInOrganization($input->jobId, $input->organizationId);

        if ($job === null) {
            throw new ImportJobNotFoundException($input->jobId);
        }

        return new ListImportJobErrorsOutput(
            items: $this->jobs->findErrors($input->jobId, $input->limit, $input->offset),
            total: $this->jobs->countErrors($input->jobId),
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
