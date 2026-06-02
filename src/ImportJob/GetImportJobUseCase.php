<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class GetImportJobUseCase implements GetImportJobUseCaseInterface
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    public function execute(GetImportJobInput $input): ImportJob
    {
        $job = $this->jobs->findByIdInOrganization($input->id, $input->organizationId);

        if ($job === null) {
            throw new ImportJobNotFoundException($input->id);
        }

        return $job;
    }
}
