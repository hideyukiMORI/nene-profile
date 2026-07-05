<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\Organization\CreateOrganizationInput;
use NeneProfile\Organization\CreateOrganizationUseCase;
use NeneProfile\Organization\OrganizationRepositoryInterface;
use NeneProfile\Organization\OrganizationSlugConflictException;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class CreateOrganizationUseCaseTest extends TestCase
{
    private InMemoryOrganizationRepository $repo;
    private InMemoryAuditRecorderFactory $auditRepo;
    private CreateOrganizationUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryOrganizationRepository();
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $this->useCase   = new CreateOrganizationUseCase(
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

    public function test_creates_organization_and_returns_output(): void
    {
        $output = $this->useCase->execute(null, new CreateOrganizationInput(
            name: 'Acme Corp',
            slug: 'acme',
        ));

        $this->assertSame('Acme Corp', $output->name);
        $this->assertSame('acme', $output->slug);
        $this->assertTrue($output->isActive);
        $this->assertNull($output->customDomain);
        $this->assertGreaterThan(0, $output->id);
    }

    public function test_creates_organization_with_custom_domain(): void
    {
        $output = $this->useCase->execute(null, new CreateOrganizationInput(
            name: 'Acme Corp',
            slug: 'acme',
            customDomain: 'acme.example.com',
        ));

        $this->assertSame('acme.example.com', $output->customDomain);
    }

    public function test_throws_when_slug_already_exists(): void
    {
        $this->useCase->execute(null, new CreateOrganizationInput(name: 'Acme', slug: 'acme'));

        $this->expectException(OrganizationSlugConflictException::class);

        $this->useCase->execute(null, new CreateOrganizationInput(name: 'Another Acme', slug: 'acme'));
    }

    public function test_stores_organization_in_repository(): void
    {
        $output = $this->useCase->execute(null, new CreateOrganizationInput(name: 'Test Org', slug: 'test'));

        $org = $this->repo->findById($output->id);
        $this->assertNotNull($org);
        $this->assertSame('test', $org->slug);
    }

    public function test_records_audit_log_on_create(): void
    {
        $output = $this->useCase->execute(42, new CreateOrganizationInput(name: 'Audited Org', slug: 'audited'));

        $logs = $this->auditRepo->appended;
        $this->assertCount(1, $logs);

        $log = $logs[0];
        $this->assertSame('organization.created', $log->action);
        $this->assertSame('organization', $log->entityType);
        $this->assertSame($output->id, $log->entityId);
        $this->assertSame($output->id, $log->organizationId);
        $this->assertSame(42, $log->actorId);
        $this->assertNull($log->before);
        $this->assertNotNull($log->after);
        $this->assertSame('audited', $log->after['slug']);
    }

    public function test_audit_log_contains_no_secrets(): void
    {
        $this->useCase->execute(1, new CreateOrganizationInput(name: 'Org', slug: 'org'));

        $log = $this->auditRepo->appended[0];
        $this->assertArrayNotHasKey('password_hash', $log->after ?? []);
    }
}
