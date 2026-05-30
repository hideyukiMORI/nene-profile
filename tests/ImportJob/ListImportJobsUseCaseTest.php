<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\ImportJob;
use NeneProfile\ImportJob\ListImportJobsUseCase;
use PHPUnit\Framework\TestCase;

final class ListImportJobsUseCaseTest extends TestCase
{
    private InMemoryImportJobRepository $repo;
    private ListImportJobsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryImportJobRepository();
        $this->useCase = new ListImportJobsUseCase($this->repo);
    }

    private function seed(string $filename, int $organizationId): int
    {
        return $this->repo->save(new ImportJob(
            id: 0,
            organizationId: $organizationId,
            actorUserId: 1,
            presetVersionId: 9,
            originalFilename: $filename,
            originalFileHash: 'hash',
            status: ImportJob::STATUS_COMPLETED,
            rowCount: 10,
            errorCount: 0,
        ));
    }

    public function test_returns_only_jobs_of_the_organization(): void
    {
        $this->seed('a.csv', 7);
        $this->seed('b.csv', 7);
        $this->seed('other.csv', 99);

        $result = $this->useCase->execute(7, 20, 0);

        $this->assertSame(2, $result['total']);
        $this->assertCount(2, $result['items']);
        foreach ($result['items'] as $job) {
            $this->assertSame(7, $job->organizationId);
        }
    }

    public function test_applies_limit_and_offset(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->seed("j{$i}.csv", 7);
        }

        $result = $this->useCase->execute(7, 1, 1);

        $this->assertSame(3, $result['total']);
        $this->assertCount(1, $result['items']);
    }

    public function test_empty_for_organization_without_jobs(): void
    {
        $this->seed('a.csv', 7);

        $result = $this->useCase->execute(1, 20, 0);

        $this->assertSame(0, $result['total']);
        $this->assertSame([], $result['items']);
    }
}
