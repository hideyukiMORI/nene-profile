<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use NeneProfile\Audit\AuditLog;
use NeneProfile\Audit\PdoAuditLogRepository;
use NeneProfile\Tests\Support\SqlitePdoTestTrait;
use PHPUnit\Framework\TestCase;

final class PdoAuditLogRepositorySqliteTest extends TestCase
{
    use SqlitePdoTestTrait;

    private PdoAuditLogRepository $repo;

    protected function setUp(): void
    {
        $pdo = $this->sqlitePdo();
        $pdo->exec(
            'CREATE TABLE audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                actor_user_id INTEGER,
                organization_id INTEGER,
                action TEXT NOT NULL,
                entity_type TEXT NOT NULL,
                entity_id INTEGER,
                before_json TEXT,
                after_json TEXT,
                created_at TEXT NOT NULL
            )',
        );

        $this->repo = new PdoAuditLogRepository($this->executor($pdo));
    }

    public function test_append_persists_before_after_json(): void
    {
        $id = $this->repo->append(new AuditLog(
            action: 'organization.updated',
            entityType: 'organization',
            actorUserId: 5,
            organizationId: 7,
            entityId: 9,
            before: ['name' => 'Old'],
            after: ['name' => 'New'],
        ));
        $this->assertGreaterThan(0, $id);

        $logs = $this->repo->findByOrganization(7, 20, 0);
        $this->assertCount(1, $logs);
        $this->assertSame('organization.updated', $logs[0]->action);
        $this->assertSame(5, $logs[0]->actorUserId);
        $this->assertSame(9, $logs[0]->entityId);
        $this->assertSame(['name' => 'Old'], $logs[0]->before);
        $this->assertSame(['name' => 'New'], $logs[0]->after);
    }

    public function test_null_snapshots_round_trip(): void
    {
        $this->repo->append(new AuditLog(
            action: 'organization.deleted',
            entityType: 'organization',
            organizationId: 7,
            before: ['name' => 'Gone'],
            after: null,
        ));

        $log = $this->repo->findByOrganization(7, 20, 0)[0];
        $this->assertNull($log->after);
        $this->assertNull($log->actorUserId);
        $this->assertSame(['name' => 'Gone'], $log->before);
    }

    public function test_organization_scoped_vs_cross_org_listing(): void
    {
        $this->repo->append(new AuditLog(action: 'a', entityType: 'x', organizationId: 7));
        $this->repo->append(new AuditLog(action: 'b', entityType: 'x', organizationId: 7));
        $this->repo->append(new AuditLog(action: 'c', entityType: 'x', organizationId: 99));

        $this->assertSame(2, $this->repo->countByOrganization(7));
        $this->assertCount(2, $this->repo->findByOrganization(7, 20, 0));

        $this->assertSame(3, $this->repo->countAll());
        $this->assertCount(3, $this->repo->findAll(20, 0));
    }

    public function test_pagination_on_listing(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->repo->append(new AuditLog(action: "a{$i}", entityType: 'x', organizationId: 7));
        }

        $this->assertCount(2, $this->repo->findByOrganization(7, 2, 0));
        $this->assertCount(2, $this->repo->findByOrganization(7, 2, 2));
        $this->assertCount(1, $this->repo->findByOrganization(7, 2, 4));
    }
}
