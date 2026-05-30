<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use NeneProfile\Audit\AuditRecorder;
use PHPUnit\Framework\TestCase;

final class AuditRecorderTest extends TestCase
{
    public function test_record_appends_to_repository(): void
    {
        $repo     = new InMemoryAuditLogRepository();
        $recorder = new AuditRecorder($repo);

        $recorder->record(
            actorUserId: 42,
            organizationId: 7,
            action: 'organization.created',
            entityType: 'organization',
            entityId: 7,
            before: null,
            after: ['id' => 7, 'name' => 'Acme', 'slug' => 'acme'],
        );

        $logs = $repo->all();
        $this->assertCount(1, $logs);

        $log = $logs[0];
        $this->assertSame(42, $log->actorUserId);
        $this->assertSame(7, $log->organizationId);
        $this->assertSame('organization.created', $log->action);
        $this->assertSame('organization', $log->entityType);
        $this->assertSame(7, $log->entityId);
        $this->assertNull($log->before);
        $this->assertSame(['id' => 7, 'name' => 'Acme', 'slug' => 'acme'], $log->after);
    }

    public function test_delete_records_before_snapshot_and_null_after(): void
    {
        $repo     = new InMemoryAuditLogRepository();
        $recorder = new AuditRecorder($repo);

        $recorder->record(
            actorUserId: 1,
            organizationId: 3,
            action: 'organization.deleted',
            entityType: 'organization',
            entityId: 3,
            before: ['id' => 3, 'name' => 'Old Org'],
            after: null,
        );

        $log = $repo->all()[0];
        $this->assertSame(['id' => 3, 'name' => 'Old Org'], $log->before);
        $this->assertNull($log->after);
    }

    public function test_system_operations_allow_null_actor(): void
    {
        $repo     = new InMemoryAuditLogRepository();
        $recorder = new AuditRecorder($repo);

        $recorder->record(
            actorUserId: null,
            organizationId: null,
            action: 'import_job.completed',
            entityType: 'import_job',
            entityId: 99,
            before: null,
            after: ['status' => 'completed'],
        );

        $log = $repo->all()[0];
        $this->assertNull($log->actorUserId);
        $this->assertNull($log->organizationId);
    }
}
