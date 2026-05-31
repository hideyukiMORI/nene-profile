<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use NeneProfile\Audit\AuditRecorder;
use NeneProfile\Audit\ListAuditLogsHandler;
use NeneProfile\Audit\ListAuditLogsUseCase;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;

final class AuditHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryAuditLogRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new InMemoryAuditLogRepository();
    }

    private function addLog(int $orgId = 1, string $action = 'organization.create'): void
    {
        $recorder = new AuditRecorder($this->repo);
        $recorder->record(
            actorUserId: 1,
            organizationId: $orgId,
            action: $action,
            entityType: 'organization',
            entityId: 1,
            before: null,
            after: ['id' => 1],
        );
    }

    public function test_list_returns_paginated_envelope(): void
    {
        $this->addLog(1);
        $this->addLog(1);

        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/audit-logs'));

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $payload['total']);
        $this->assertArrayHasKey('items', $payload);
        $this->assertArrayHasKey('limit', $payload);
        $this->assertArrayHasKey('offset', $payload);

        /** @var list<array<string, mixed>> $items */
        $items = $payload['items'];
        $this->assertArrayHasKey('action', $items[0]);
        $this->assertArrayHasKey('entity_type', $items[0]);
        $this->assertArrayHasKey('before', $items[0]);
        $this->assertArrayHasKey('after', $items[0]);
    }

    public function test_list_superadmin_sees_all_orgs(): void
    {
        $this->addLog(orgId: 1);
        $this->addLog(orgId: 2);

        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/audit-logs'), role: 'superadmin');

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(2, $payload['total']); // both orgs
    }

    public function test_list_admin_sees_own_org_only(): void
    {
        $this->addLog(orgId: 1);
        $this->addLog(orgId: 2);

        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/audit-logs'), orgId: 1, role: 'admin');

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(1, $payload['total']); // only org 1
    }

    public function test_list_default_limit_is_20(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->addLog();
        }

        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $response = $handler->handle($this->withAuth($this->request('GET', '/admin/audit-logs')));
        $payload  = $this->decodeJson($response);

        $this->assertSame(20, $payload['limit']);
        $this->assertCount(20, $payload['items']);
        $this->assertSame(25, $payload['total']);
    }

    public function test_list_max_limit_is_100(): void
    {
        for ($i = 0; $i < 110; $i++) {
            $this->addLog();
        }

        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/audit-logs'))
            ->withQueryParams(['limit' => '200']); // request more than max

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(100, $payload['limit']); // capped at 100
        $this->assertCount(100, $payload['items']);
    }

    public function test_list_respects_offset(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->addLog();
        }

        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/audit-logs'))
            ->withQueryParams(['limit' => '2', 'offset' => '3']);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(5, $payload['total']);
        $this->assertCount(2, $payload['items']);
    }

    public function test_list_returns_empty_when_no_logs(): void
    {
        $handler = new ListAuditLogsHandler(
            new ListAuditLogsUseCase($this->repo),
            $this->jsonFactory(),
        );

        $response = $handler->handle($this->withAuth($this->request('GET', '/admin/audit-logs')));
        $payload  = $this->decodeJson($response);

        $this->assertSame(0, $payload['total']);
        $this->assertCount(0, $payload['items']);
    }
}
