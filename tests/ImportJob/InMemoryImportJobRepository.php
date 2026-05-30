<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\ImportJob;
use NeneProfile\ImportJob\ImportJobError;
use NeneProfile\ImportJob\ImportJobRepositoryInterface;
use NeneProfile\ImportJob\NormalizedTransaction;

final class InMemoryImportJobRepository implements ImportJobRepositoryInterface
{
    private int $nextId = 1;

    /** @var array<int, ImportJob> */
    private array $jobs = [];

    /** @var array<int, list<ImportJobError>> */
    private array $errors = [];

    /** @var array<int, list<NormalizedTransaction>> */
    private array $transactions = [];

    public function findByIdInOrganization(int $id, int $organizationId): ?ImportJob
    {
        $job = $this->jobs[$id] ?? null;

        return ($job !== null && $job->organizationId === $organizationId) ? $job : null;
    }

    /** @return list<ImportJob> */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array
    {
        $filtered = array_values(array_filter($this->jobs, static fn (ImportJob $j) => $j->organizationId === $organizationId));

        return array_slice($filtered, $offset, $limit);
    }

    public function countByOrganizationId(int $organizationId): int
    {
        return count(array_filter($this->jobs, static fn (ImportJob $j) => $j->organizationId === $organizationId));
    }

    public function save(ImportJob $job): int
    {
        $id = $this->nextId++;
        $this->jobs[$id] = new ImportJob(
            id: $id,
            organizationId: $job->organizationId,
            actorUserId: $job->actorUserId,
            presetVersionId: $job->presetVersionId,
            originalFilename: $job->originalFilename,
            originalFileHash: $job->originalFileHash,
            status: $job->status,
            rowCount: $job->rowCount,
            errorCount: $job->errorCount,
            startedAt: $job->startedAt,
            completedAt: $job->completedAt,
            createdAt: date('Y-m-d H:i:s'),
            updatedAt: date('Y-m-d H:i:s'),
        );

        return $id;
    }

    public function complete(int $id, string $status, int $rowCount, int $errorCount): void
    {
        $job = $this->jobs[$id] ?? null;
        if ($job === null) {
            return;
        }
        $this->jobs[$id] = new ImportJob(
            id: $job->id,
            organizationId: $job->organizationId,
            actorUserId: $job->actorUserId,
            presetVersionId: $job->presetVersionId,
            originalFilename: $job->originalFilename,
            originalFileHash: $job->originalFileHash,
            status: $status,
            rowCount: $rowCount,
            errorCount: $errorCount,
            startedAt: $job->startedAt,
            completedAt: date('Y-m-d H:i:s'),
            createdAt: $job->createdAt,
            updatedAt: date('Y-m-d H:i:s'),
        );
    }

    /** @param list<ImportJobError> $errors */
    public function appendErrors(int $importJobId, array $errors): void
    {
        $this->errors[$importJobId] = array_merge($this->errors[$importJobId] ?? [], $errors);
    }

    /** @return list<ImportJobError> */
    public function findErrors(int $importJobId, int $limit, int $offset): array
    {
        return array_slice($this->errors[$importJobId] ?? [], $offset, $limit);
    }

    public function countErrors(int $importJobId): int
    {
        return count($this->errors[$importJobId] ?? []);
    }

    /** @param list<NormalizedTransaction> $transactions */
    public function appendTransactions(int $importJobId, array $transactions): void
    {
        $this->transactions[$importJobId] = array_merge($this->transactions[$importJobId] ?? [], $transactions);
    }

    /** @return list<NormalizedTransaction> */
    public function findTransactions(int $importJobId): array
    {
        return $this->transactions[$importJobId] ?? [];
    }
}
