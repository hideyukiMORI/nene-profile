<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Organization\ListOrganizationsInput;
use NeneProfile\Organization\ListOrganizationsUseCase;
use NeneProfile\Organization\Organization;
use PHPUnit\Framework\TestCase;

final class ListOrganizationsUseCaseTest extends TestCase
{
    private InMemoryOrganizationRepository $repo;
    private ListOrganizationsUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryOrganizationRepository();
        $this->useCase = new ListOrganizationsUseCase($this->repo);
    }

    private function seed(string $slug): void
    {
        $this->repo->save(new Organization(name: $slug, slug: $slug, isActive: true));
    }

    public function test_maps_organizations_to_list_items(): void
    {
        $this->seed('acme');

        $output = $this->useCase->execute(new ListOrganizationsInput());

        $this->assertSame(1, $output->total);
        $this->assertCount(1, $output->items);
        $this->assertSame('acme', $output->items[0]->slug);
        $this->assertSame('acme', $output->items[0]->name);
        $this->assertTrue($output->items[0]->isActive);
    }

    public function test_applies_limit_and_offset(): void
    {
        foreach (['a', 'b', 'c', 'd', 'e'] as $slug) {
            $this->seed($slug);
        }

        $output = $this->useCase->execute(new ListOrganizationsInput(limit: 2, offset: 1));

        $this->assertSame(5, $output->total);
        $this->assertCount(2, $output->items);
        $this->assertSame('b', $output->items[0]->slug);
        $this->assertSame('c', $output->items[1]->slug);
    }

    public function test_empty_when_no_organizations(): void
    {
        $output = $this->useCase->execute(new ListOrganizationsInput());

        $this->assertSame(0, $output->total);
        $this->assertSame([], $output->items);
    }
}
