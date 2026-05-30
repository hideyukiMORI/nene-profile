<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use NeneProfile\User\ListUsersInput;
use NeneProfile\User\ListUsersUseCase;
use NeneProfile\User\User;
use PHPUnit\Framework\TestCase;

final class ListUsersUseCaseTest extends TestCase
{
    private InMemoryUserRepository $repo;
    private ListUsersUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryUserRepository();
        $this->useCase = new ListUsersUseCase($this->repo);
    }

    private function seed(string $email, int $organizationId): void
    {
        $this->repo->seed(new User(
            id: 0,
            email: $email,
            passwordHash: 'x',
            role: 'member',
            organizationId: $organizationId,
        ));
    }

    public function test_returns_only_users_of_the_requested_organization(): void
    {
        $this->seed('a@example.com', 7);
        $this->seed('b@example.com', 7);
        $this->seed('other@example.com', 99);

        $output = $this->useCase->execute(new ListUsersInput(organizationId: 7));

        $this->assertSame(2, $output->total);
        $this->assertCount(2, $output->items);
        foreach ($output->items as $user) {
            $this->assertSame(7, $user->organizationId);
        }
    }

    public function test_applies_limit_and_offset(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->seed("u{$i}@example.com", 7);
        }

        $output = $this->useCase->execute(new ListUsersInput(organizationId: 7, limit: 2, offset: 2));

        $this->assertSame(5, $output->total, 'total reflects all rows, not the page');
        $this->assertCount(2, $output->items);
        $this->assertSame(2, $output->limit);
        $this->assertSame(2, $output->offset);
    }

    public function test_empty_when_organization_has_no_users(): void
    {
        $this->seed('a@example.com', 7);

        $output = $this->useCase->execute(new ListUsersInput(organizationId: 1));

        $this->assertSame(0, $output->total);
        $this->assertSame([], $output->items);
    }
}
