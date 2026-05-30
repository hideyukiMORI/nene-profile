<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use NeneProfile\Audit\AuditRecorderInterface;
use NeneProfile\Preset\MappingPresetNotFoundException;
use NeneProfile\Preset\MappingPresetRepositoryInterface;
use NeneProfile\Preset\MappingPresetVersionRepositoryInterface;

/**
 * Orchestrates one CSV normalization run (ADR 0004, compliance §3-9):
 *
 *  1. Resolve the preset and its current version (org-scoped).
 *  2. Store the original file IMMUTABLY with its SHA-256 (before processing).
 *  3. Parse + normalize via the runner.
 *  4. Persist normalized rows, errors, and the terminal job state.
 *  5. Record the audit event.
 *
 * The original file hash is computed on the raw bytes and recorded on the job,
 * so any output row can later be re-verified against the stored original.
 */
final readonly class CreateImportJobUseCase implements CreateImportJobUseCaseInterface
{
    public function __construct(
        private ImportJobRepositoryInterface $jobs,
        private MappingPresetRepositoryInterface $presets,
        private MappingPresetVersionRepositoryInterface $versions,
        private FileStorageInterface $storage,
        private CsvParser $parser,
        private NormalizationRunner $runner,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(CreateImportJobInput $input): ImportJob
    {
        $preset = $this->presets->findByIdInOrganization($input->presetId, $input->organizationId);

        if ($preset === null || $preset->currentVersionId === null) {
            throw new MappingPresetNotFoundException($input->presetId);
        }

        $version = $this->versions->findById($preset->currentVersionId);

        if ($version === null) {
            throw new MappingPresetNotFoundException($input->presetId);
        }

        // Store the original immutably + hash BEFORE any processing (ADR 0004).
        $fileHash = hash('sha256', $input->fileContents);
        $this->storage->store($input->organizationId, $input->fileContents);

        $now = date('Y-m-d H:i:s');
        $jobId = $this->jobs->save(new ImportJob(
            id: 0,
            organizationId: $input->organizationId,
            actorUserId: $input->actorUserId,
            presetVersionId: $version->id,
            originalFilename: $input->originalFilename,
            originalFileHash: $fileHash,
            status: ImportJob::STATUS_RUNNING,
            rowCount: 0,
            errorCount: 0,
            startedAt: $now,
        ));

        // Parse + normalize. A whole-file parse failure marks the job failed.
        try {
            $parsed = $this->parser->parse($input->fileContents, $version->definition);
        } catch (CsvParseException $e) {
            $this->jobs->appendErrors($jobId, [new ImportJobError(
                rawRowNumber: 0,
                message: $e->getMessage(),
            )]);
            $this->jobs->complete($jobId, ImportJob::STATUS_FAILED, 0, 1);

            return $this->finish($jobId, $input->organizationId);
        }

        $result = $this->runner->run($parsed, $version->definition);

        $this->jobs->appendTransactions($jobId, $result->transactions);
        $this->jobs->appendErrors($jobId, $result->errors);
        $this->jobs->complete(
            $jobId,
            $result->deriveStatus(),
            count($result->transactions),
            count($result->errors),
        );

        $job = $this->finish($jobId, $input->organizationId);

        $this->audit->record(
            actorUserId: $input->actorUserId,
            organizationId: $input->organizationId,
            action: 'import_job.' . ($result->hasErrors() ? 'completed_with_errors' : 'completed'),
            entityType: 'import_job',
            entityId: $jobId,
            before: null,
            after: ImportJobSnapshot::toArray($job),
        );

        return $job;
    }

    private function finish(int $jobId, int $organizationId): ImportJob
    {
        $job = $this->jobs->findByIdInOrganization($jobId, $organizationId);
        assert($job !== null);

        return $job;
    }
}
