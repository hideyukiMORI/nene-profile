<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ListImportJobsUseCase implements ListImportJobsUseCaseInterface
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    public function execute(ListImportJobsInput $input): ListImportJobsOutput
    {
        return new ListImportJobsOutput(
            items: $this->jobs->findByOrganizationId($input->organizationId, $input->limit, $input->offset),
            total: $this->jobs->countByOrganizationId($input->organizationId),
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
