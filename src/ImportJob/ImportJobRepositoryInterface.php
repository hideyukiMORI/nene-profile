<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

interface ImportJobRepositoryInterface
{
    public function findByIdInOrganization(int $id, int $organizationId): ?ImportJob;

    /** @return list<ImportJob> */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array;

    public function countByOrganizationId(int $organizationId): int;

    public function save(ImportJob $job): int;

    /** Finalize a job's terminal state. Called once; never mutates a terminal job again. */
    public function complete(int $id, string $status, int $rowCount, int $errorCount): void;

    /** @param list<ImportJobError> $errors */
    public function appendErrors(int $importJobId, array $errors): void;

    /** @return list<ImportJobError> */
    public function findErrors(int $importJobId, int $limit, int $offset): array;

    public function countErrors(int $importJobId): int;

    /** @param list<NormalizedTransaction> $transactions */
    public function appendTransactions(int $importJobId, array $transactions): void;

    /** @return list<NormalizedTransaction> */
    public function findTransactions(int $importJobId): array;
}
