<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ListImportJobsUseCase
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
    ) {
    }

    /**
     * @return array{items: list<ImportJob>, total: int}
     */
    public function execute(int $organizationId, int $limit, int $offset): array
    {
        return [
            'items' => $this->jobs->findByOrganizationId($organizationId, $limit, $offset),
            'total' => $this->jobs->countByOrganizationId($organizationId),
        ];
    }
}
