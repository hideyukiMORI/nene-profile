<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Organization\GetOrganizationByIdInput;
use NeneProfile\Organization\GetOrganizationByIdUseCase;
use NeneProfile\Organization\Organization;
use NeneProfile\Organization\OrganizationNotFoundException;
use PHPUnit\Framework\TestCase;

final class GetOrganizationByIdUseCaseTest extends TestCase
{
    private InMemoryOrganizationRepository $repo;
    private GetOrganizationByIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryOrganizationRepository();
        $this->useCase = new GetOrganizationByIdUseCase($this->repo);
    }

    public function test_returns_organization_by_id(): void
    {
        $id = $this->repo->save(new Organization(name: 'Acme', slug: 'acme', isActive: true));

        $output = $this->useCase->execute(new GetOrganizationByIdInput(id: $id));

        $this->assertSame($id, $output->id);
        $this->assertSame('Acme', $output->name);
        $this->assertSame('acme', $output->slug);
    }

    public function test_throws_not_found_for_unknown_id(): void
    {
        $this->expectException(OrganizationNotFoundException::class);

        $this->useCase->execute(new GetOrganizationByIdInput(id: 12345));
    }
}
