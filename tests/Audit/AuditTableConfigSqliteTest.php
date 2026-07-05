<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditPayloadMode;
use Nene2\Audit\AuditQuery;
use Nene2\Audit\AuditTableConfig;
use Nene2\Audit\PdoAuditEventRepository;
use NeneProfile\Tests\Support\SqlitePdoTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Product-specific coverage: confirms profile's {@see AuditTableConfig}
 * (registered in AuditServiceProvider) correctly maps `Nene2\Audit\AuditEvent`
 * onto the real `audit_logs` schema (int autoincrement id, no metadata column).
 * The framework repository's own SQL logic is covered upstream in NENE2.
 */
final class AuditTableConfigSqliteTest extends TestCase
{
    use SqlitePdoTestTrait;

    private PdoAuditEventRepository $repo;

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

        $config = new AuditTableConfig(
            table: 'audit_logs',
            mode: AuditPayloadMode::BeforeAfter,
            idColumn: 'id',
            actionColumn: 'action',
            entityTypeColumn: 'entity_type',
            entityIdColumn: 'entity_id',
            actorColumn: 'actor_user_id',
            organizationColumn: 'organization_id',
            occurredAtColumn: 'created_at',
            metadataColumn: null,
            beforeColumn: 'before_json',
            afterColumn: 'after_json',
            payloadColumn: null,
            idIsAutoIncrement: true,
        );

        $this->repo = new PdoAuditEventRepository($this->executor($pdo), $config);
    }

    public function test_append_and_query_round_trip_before_after_json(): void
    {
        $this->repo->append(new AuditEvent(
            action: 'organization.update',
            entityType: 'organization',
            entityId: 9,
            actorId: 5,
            organizationId: 7,
            before: ['name' => 'Old'],
            after: ['name' => 'New'],
            occurredAt: '2026-07-05 00:00:00',
        ));

        $events = $this->repo->query(new AuditQuery(organizationId: 7), 20, 0);
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertSame('organization.update', $event->action);
        $this->assertSame(5, $event->actorId);
        $this->assertSame(9, $event->entityId);
        $this->assertSame(['name' => 'Old'], $event->before);
        $this->assertSame(['name' => 'New'], $event->after);
        $this->assertIsInt($event->id);
    }

    public function test_null_snapshots_round_trip(): void
    {
        $this->repo->append(new AuditEvent(
            action: 'organization.deleted',
            entityType: 'organization',
            organizationId: 7,
            before: ['name' => 'Gone'],
            after: null,
            occurredAt: '2026-07-05 00:00:00',
        ));

        $event = $this->repo->query(new AuditQuery(organizationId: 7), 20, 0)[0];
        $this->assertNull($event->after);
        $this->assertNull($event->actorId);
        $this->assertSame(['name' => 'Gone'], $event->before);
    }

    public function test_organization_scoped_vs_cross_org_listing(): void
    {
        $this->repo->append(new AuditEvent(action: 'a', entityType: 'x', organizationId: 7, occurredAt: '2026-07-05 00:00:00'));
        $this->repo->append(new AuditEvent(action: 'b', entityType: 'x', organizationId: 7, occurredAt: '2026-07-05 00:00:01'));
        $this->repo->append(new AuditEvent(action: 'c', entityType: 'x', organizationId: 99, occurredAt: '2026-07-05 00:00:02'));

        $scoped = new AuditQuery(organizationId: 7);
        $this->assertSame(2, $this->repo->count($scoped));
        $this->assertCount(2, $this->repo->query($scoped, 20, 0));

        $crossOrg = new AuditQuery();
        $this->assertSame(3, $this->repo->count($crossOrg));
        $this->assertCount(3, $this->repo->query($crossOrg, 20, 0));
    }

    public function test_pagination_on_listing(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->repo->append(new AuditEvent(action: "a{$i}", entityType: 'x', organizationId: 7, occurredAt: "2026-07-05 00:00:0{$i}"));
        }

        $query = new AuditQuery(organizationId: 7);
        $this->assertCount(2, $this->repo->query($query, 2, 0));
        $this->assertCount(2, $this->repo->query($query, 2, 2));
        $this->assertCount(1, $this->repo->query($query, 2, 4));
    }
}
