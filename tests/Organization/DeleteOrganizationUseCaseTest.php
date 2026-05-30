<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Audit\AuditRecorder;
use NeneProfile\Organization\CreateOrganizationInput;
use NeneProfile\Organization\CreateOrganizationUseCase;
use NeneProfile\Organization\DeleteOrganizationInput;
use NeneProfile\Organization\DeleteOrganizationUseCase;
use NeneProfile\Organization\OrganizationNotFoundException;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use PHPUnit\Framework\TestCase;

final class DeleteOrganizationUseCaseTest extends TestCase
{
    private InMemoryOrganizationRepository $repo;
    private InMemoryAuditLogRepository $auditRepo;
    private AuditRecorder $audit;
    private DeleteOrganizationUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryOrganizationRepository();
        $this->auditRepo = new InMemoryAuditLogRepository();
        $this->audit     = new AuditRecorder($this->auditRepo);
        $this->useCase   = new DeleteOrganizationUseCase($this->repo, $this->audit);
    }

    private function createOrg(string $name, string $slug): int
    {
        $createUseCase = new CreateOrganizationUseCase($this->repo, $this->audit);

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
        $this->auditRepo = new InMemoryAuditLogRepository();
        $this->useCase   = new DeleteOrganizationUseCase($this->repo, new AuditRecorder($this->auditRepo));

        $this->useCase->execute(5, new DeleteOrganizationInput($id));

        $logs = $this->auditRepo->all();
        $this->assertCount(1, $logs);

        $log = $logs[0];
        $this->assertSame('organization.deleted', $log->action);
        $this->assertSame($id, $log->entityId);
        $this->assertSame(5, $log->actorUserId);
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

        $this->assertCount(0, $this->auditRepo->all());
    }
}
