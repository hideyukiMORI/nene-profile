<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

/**
 * No-op implementation for use in unit tests that do not need to verify audit recording.
 */
final class NullAuditRecorder implements AuditRecorderInterface
{
    public function record(
        ?int $actorUserId,
        ?int $organizationId,
        string $action,
        string $entityType,
        ?int $entityId,
        ?array $before,
        ?array $after,
    ): void {
        // no-op
    }
}
