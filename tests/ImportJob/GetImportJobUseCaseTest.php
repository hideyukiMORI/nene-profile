<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\GetImportJobUseCase;
use NeneProfile\ImportJob\ImportJob;
use NeneProfile\ImportJob\ImportJobNotFoundException;
use PHPUnit\Framework\TestCase;

final class GetImportJobUseCaseTest extends TestCase
{
    private InMemoryImportJobRepository $repo;
    private GetImportJobUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryImportJobRepository();
        $this->useCase = new GetImportJobUseCase($this->repo);
    }

    private function seed(int $organizationId): int
    {
        return $this->repo->save(new ImportJob(
            id: 0,
            organizationId: $organizationId,
            actorUserId: 1,
            presetVersionId: 9,
            originalFilename: 'bank.csv',
            originalFileHash: 'hash',
            status: ImportJob::STATUS_COMPLETED,
            rowCount: 10,
            errorCount: 0,
        ));
    }

    public function test_returns_job_within_organization(): void
    {
        $id = $this->seed(7);

        $job = $this->useCase->execute($id, 7);

        $this->assertSame($id, $job->id);
        $this->assertSame('bank.csv', $job->originalFilename);
    }

    public function test_throws_not_found_for_unknown_id(): void
    {
        $this->expectException(ImportJobNotFoundException::class);

        $this->useCase->execute(999, 7);
    }

    public function test_throws_not_found_for_cross_tenant_access(): void
    {
        $id = $this->seed(7);

        $this->expectException(ImportJobNotFoundException::class);

        $this->useCase->execute($id, 99);
    }
}
