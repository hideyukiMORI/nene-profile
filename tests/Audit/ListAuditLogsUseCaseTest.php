<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use NeneProfile\Audit\AuditLog;
use NeneProfile\Audit\ListAuditLogsInput;
use NeneProfile\Audit\ListAuditLogsUseCase;
use PHPUnit\Framework\TestCase;

final class ListAuditLogsUseCaseTest extends TestCase
{
    private InMemoryAuditLogRepository $repo;
    private ListAuditLogsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryAuditLogRepository();
        $this->useCase = new ListAuditLogsUseCase($this->repo);
    }

    private function seed(string $action, ?int $organizationId): void
    {
        $this->repo->append(new AuditLog(
            action: $action,
            entityType: 'organization',
            organizationId: $organizationId,
        ));
    }

    public function test_organization_scoped_view_returns_only_that_org(): void
    {
        $this->seed('organization.created', 7);
        $this->seed('user.created', 7);
        $this->seed('organization.created', 99);

        $output = $this->useCase->execute(new ListAuditLogsInput(organizationId: 7));

        $this->assertSame(2, $output->total);
        $this->assertCount(2, $output->items);
        foreach ($output->items as $log) {
            $this->assertSame(7, $log->organizationId);
        }
    }

    public function test_null_organization_returns_all_logs_for_superadmin(): void
    {
        $this->seed('organization.created', 7);
        $this->seed('organization.created', 99);
        $this->seed('system.event', null);

        $output = $this->useCase->execute(new ListAuditLogsInput(organizationId: null));

        $this->assertSame(3, $output->total);
        $this->assertCount(3, $output->items);
    }

    public function test_applies_limit_and_offset(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->seed("action.{$i}", 7);
        }

        $output = $this->useCase->execute(new ListAuditLogsInput(organizationId: 7, limit: 2, offset: 3));

        $this->assertSame(5, $output->total);
        $this->assertCount(2, $output->items);
        $this->assertSame(2, $output->limit);
        $this->assertSame(3, $output->offset);
    }
}
