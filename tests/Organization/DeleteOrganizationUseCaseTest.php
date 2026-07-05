<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\Organization\CreateOrganizationInput;
use NeneProfile\Organization\CreateOrganizationUseCase;
use NeneProfile\Organization\DeleteOrganizationInput;
use NeneProfile\Organization\DeleteOrganizationUseCase;
use NeneProfile\Organization\OrganizationNotFoundException;
use NeneProfile\Organization\OrganizationRepositoryInterface;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class DeleteOrganizationUseCaseTest extends TestCase
{
    private InMemoryOrganizationRepository $repo;
    private InMemoryAuditRecorderFactory $auditRepo;
    private DeleteOrganizationUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryOrganizationRepository();
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $this->useCase   = new DeleteOrganizationUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory($this->repo),
            $this->auditRepo,
        );
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface */
    private function organizationsFactory(OrganizationRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationRepositoryInterface => $repo;
    }

    private function createOrg(string $name, string $slug): int
    {
        $createUseCase = new CreateOrganizationUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory($this->repo),
            $this->auditRepo,
        );

        return $createUseCase->execute(null, new CreateOrganizationInput($name, $slug))->id;
    }

    public function test_deletes_organization(): void
    {
        $id = $this->createOrg('Delete Me', 'delete-me');

        $this->useCase->execute(7, new DeleteOrganizationInput($id));

        $this->assertNull($this->repo->findById($id));
    }

    public function test_records_audit_log_with_before_snapshot(): void
    {
        $id = $this->createOrg('Doomed Org', 'doomed');

        // Reset audit to isolate delete event
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $this->useCase   = new DeleteOrganizationUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory($this->repo),
            $this->auditRepo,
        );

        $this->useCase->execute(5, new DeleteOrganizationInput($id));

        $logs = $this->auditRepo->appended;
        $this->assertCount(1, $logs);

        $log = $logs[0];
        $this->assertSame('organization.deleted', $log->action);
        $this->assertSame($id, $log->entityId);
        $this->assertSame(5, $log->actorId);
        $this->assertNotNull($log->before);
        $this->assertSame('doomed', $log->before['slug']);
        $this->assertNull($log->after);
    }

    public function test_throws_when_organization_not_found(): void
    {
        $this->expectException(OrganizationNotFoundException::class);

        $this->useCase->execute(1, new DeleteOrganizationInput(9999));
    }

    public function test_no_audit_log_written_when_not_found(): void
    {
        try {
            $this->useCase->execute(1, new DeleteOrganizationInput(9999));
        } catch (OrganizationNotFoundException) {
        }

        $this->assertCount(0, $this->auditRepo->appended);
    }
}
