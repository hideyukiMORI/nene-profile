<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

final readonly class ImportJob
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        public int $id,
        public int $organizationId,
        public ?int $actorUserId,
        public int $presetVersionId,
        public string $originalFilename,
        public string $originalFileHash,
        public string $status,
        public int $rowCount,
        public int $errorCount,
        public ?string $startedAt = null,
        public ?string $completedAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
