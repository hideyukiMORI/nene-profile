<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Organization\CreateOrganizationInput;
use NeneProfile\Organization\CreateOrganizationUseCase;
use NeneProfile\Organization\OrganizationSlugConflictException;
use PHPUnit\Framework\TestCase;

final class CreateOrganizationUseCaseTest extends TestCase
{
    private InMemoryOrganizationRepository $repo;
    private CreateOrganizationUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryOrganizationRepository();
        $this->useCase = new CreateOrganizationUseCase($this->repo);
    }

    public function test_creates_organization_and_returns_output(): void
    {
        $output = $this->useCase->execute(new CreateOrganizationInput(
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
        $output = $this->useCase->execute(new CreateOrganizationInput(
            name: 'Acme Corp',
            slug: 'acme',
            customDomain: 'acme.example.com',
        ));

        $this->assertSame('acme.example.com', $output->customDomain);
    }

    public function test_throws_when_slug_already_exists(): void
    {
        $this->useCase->execute(new CreateOrganizationInput(name: 'Acme', slug: 'acme'));

        $this->expectException(OrganizationSlugConflictException::class);

        $this->useCase->execute(new CreateOrganizationInput(name: 'Another Acme', slug: 'acme'));
    }

    public function test_stores_organization_in_repository(): void
    {
        $output = $this->useCase->execute(new CreateOrganizationInput(name: 'Test Org', slug: 'test'));

        $org = $this->repo->findById($output->id);
        $this->assertNotNull($org);
        $this->assertSame('test', $org->slug);
    }
}
