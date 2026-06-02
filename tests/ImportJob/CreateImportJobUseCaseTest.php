<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\Audit\AuditRecorder;
use NeneProfile\ImportJob\CreateImportJobInput;
use NeneProfile\ImportJob\CreateImportJobUseCase;
use NeneProfile\ImportJob\CsvParser;
use NeneProfile\ImportJob\ImportJob;
use NeneProfile\ImportJob\NormalizationRunner;
use NeneProfile\Preset\CreateMappingPresetInput;
use NeneProfile\Preset\CreateMappingPresetUseCase;
use NeneProfile\Preset\MappingDefinitionFactory;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use NeneProfile\Tests\OrgSettings\InMemoryOrganizationSettingsRepository;
use NeneProfile\Tests\Preset\InMemoryMappingPresetRepository;
use NeneProfile\Tests\Preset\InMemoryMappingPresetVersionRepository;
use NeneProfile\Transformer\TransformerRegistry;
use PHPUnit\Framework\TestCase;

final class CreateImportJobUseCaseTest extends TestCase
{
    private InMemoryImportJobRepository $jobs;
    private InMemoryMappingPresetRepository $presets;
    private InMemoryMappingPresetVersionRepository $versions;
    private InMemoryFileStorage $storage;
    private InMemoryAuditLogRepository $auditRepo;
    private CreateImportJobUseCase $useCase;
    private int $presetId;

    protected function setUp(): void
    {
        $this->jobs      = new InMemoryImportJobRepository();
        $this->presets   = new InMemoryMappingPresetRepository();
        $this->versions  = new InMemoryMappingPresetVersionRepository();
        $this->storage   = new InMemoryFileStorage();
        $this->auditRepo = new InMemoryAuditLogRepository();

        // Seed a preset (org 7) with a MUFG-like definition.
        $createPreset = new CreateMappingPresetUseCase($this->presets, $this->versions, new AuditRecorder(new InMemoryAuditLogRepository()));
        $created = $createPreset->execute(1, new CreateMappingPresetInput(
            organizationId: 7,
            name: 'MUFG',
            bankLabel: 'MUFG',
            definition: MappingDefinitionFactory::fromArray([
                'encoding'  => 'auto',
                'delimiter' => 'comma',
                'columns'   => [
                    'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                    'amount_cents'     => ['source' => ['入金', '出金'], 'transform' => 'debit_credit_to_signed_cents'],
                    'description'      => ['source' => '摘要', 'transform' => 'trim'],
                ],
            ]),
        ));
        $this->presetId = $created->preset->id;

        $this->useCase = new CreateImportJobUseCase(
            $this->jobs,
            $this->presets,
            $this->versions,
            $this->storage,
            new CsvParser(),
            new NormalizationRunner(new TransformerRegistry()),
            new AuditRecorder($this->auditRepo),
            new InMemoryOrganizationSettingsRepository(),
        );
    }

    private function input(string $csv): CreateImportJobInput
    {
        return new CreateImportJobInput(
            organizationId: 7,
            actorUserId: 1,
            presetId: $this->presetId,
            originalFilename: 'mufg.csv',
            fileContents: $csv,
        );
    }

    public function test_clean_import_completes_and_stores_original_with_hash(): void
    {
        $csv = "日付,入金,出金,摘要\n2026/05/15,100000,,振込\n2026/05/16,,30000,引落\n";
        $job = $this->useCase->execute($this->input($csv));

        $this->assertSame(ImportJob::STATUS_COMPLETED, $job->status);
        $this->assertSame(2, $job->rowCount);
        $this->assertSame(0, $job->errorCount);

        // Original stored immutably; hash recorded matches raw bytes.
        $this->assertSame(1, $this->storage->count());
        $this->assertSame(hash('sha256', $csv), $job->originalFileHash);

        // Output rows persisted.
        $this->assertCount(2, $this->jobs->findTransactions($job->id));
    }

    public function test_bad_rows_recorded_and_status_completed_with_errors(): void
    {
        $csv = "日付,入金,出金,摘要\n2026/05/15,100000,,ok\nnot-a-date,100000,,bad\n";
        $job = $this->useCase->execute($this->input($csv));

        $this->assertSame(ImportJob::STATUS_COMPLETED_WITH_ERRORS, $job->status);
        $this->assertSame(1, $job->rowCount);
        $this->assertSame(1, $job->errorCount);
        $this->assertCount(1, $this->jobs->findErrors($job->id, 100, 0));
    }

    public function test_audit_recorded_on_completion(): void
    {
        $csv = "日付,入金,出金,摘要\n2026/05/15,100000,,振込\n";
        $job = $this->useCase->execute($this->input($csv));

        $log = $this->auditRepo->all()[0];
        $this->assertSame('import_job.completed', $log->action);
        $this->assertSame('import_job', $log->entityType);
        $this->assertSame($job->id, $log->entityId);
        $this->assertSame(7, $log->organizationId);
        $this->assertSame(1, $log->actorUserId);
    }

    public function test_unparseable_file_marks_job_failed(): void
    {
        $job = $this->useCase->execute($this->input(''));

        $this->assertSame(ImportJob::STATUS_FAILED, $job->status);
        // Original is still stored even on failure (ADR 0004).
        $this->assertSame(1, $this->storage->count());
    }

    public function test_provenance_preset_version_recorded_on_job(): void
    {
        $version = $this->versions->findById(1);
        $this->assertNotNull($version);

        $csv = "日付,入金,出金,摘要\n2026/05/15,100000,,振込\n";
        $job = $this->useCase->execute($this->input($csv));

        $this->assertSame($version->id, $job->presetVersionId);
    }
}
