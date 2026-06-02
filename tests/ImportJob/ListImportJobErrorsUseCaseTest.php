<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\ImportJob;
use NeneProfile\ImportJob\ImportJobError;
use NeneProfile\ImportJob\ImportJobNotFoundException;
use NeneProfile\ImportJob\ListImportJobErrorsInput;
use NeneProfile\ImportJob\ListImportJobErrorsUseCase;
use PHPUnit\Framework\TestCase;

final class ListImportJobErrorsUseCaseTest extends TestCase
{
    private InMemoryImportJobRepository $repo;
    private ListImportJobErrorsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryImportJobRepository();
        $this->useCase = new ListImportJobErrorsUseCase($this->repo);
    }

    private function seedJob(int $organizationId): int
    {
        return $this->repo->save(new ImportJob(
            id: 0,
            organizationId: $organizationId,
            actorUserId: 1,
            presetVersionId: 9,
            originalFilename: 'bank.csv',
            originalFileHash: 'hash',
            status: ImportJob::STATUS_COMPLETED_WITH_ERRORS,
            rowCount: 10,
            errorCount: 2,
        ));
    }

    public function test_returns_errors_for_a_job(): void
    {
        $id = $this->seedJob(7);
        $this->repo->appendErrors($id, [
            new ImportJobError(rawRowNumber: 3, message: 'bad date'),
            new ImportJobError(rawRowNumber: 5, message: 'bad amount'),
        ]);

        $output = $this->useCase->execute(new ListImportJobErrorsInput(jobId: $id, organizationId: 7, limit: 20, offset: 0));

        $this->assertSame(2, $output->total);
        $this->assertCount(2, $output->items);
        $this->assertSame(3, $output->items[0]->rawRowNumber);
    }

    public function test_applies_limit_and_offset_to_errors(): void
    {
        $id = $this->seedJob(7);
        $this->repo->appendErrors($id, [
            new ImportJobError(rawRowNumber: 1, message: 'e1'),
            new ImportJobError(rawRowNumber: 2, message: 'e2'),
            new ImportJobError(rawRowNumber: 3, message: 'e3'),
        ]);

        $output = $this->useCase->execute(new ListImportJobErrorsInput(jobId: $id, organizationId: 7, limit: 1, offset: 1));

        $this->assertSame(3, $output->total);
        $this->assertCount(1, $output->items);
        $this->assertSame(2, $output->items[0]->rawRowNumber);
    }

    public function test_throws_not_found_for_unknown_job(): void
    {
        $this->expectException(ImportJobNotFoundException::class);

        $this->useCase->execute(new ListImportJobErrorsInput(jobId: 999, organizationId: 7, limit: 20, offset: 0));
    }

    public function test_throws_not_found_for_cross_tenant_job(): void
    {
        $id = $this->seedJob(7);

        $this->expectException(ImportJobNotFoundException::class);

        $this->useCase->execute(new ListImportJobErrorsInput(jobId: $id, organizationId: 99, limit: 20, offset: 0));
    }
}
