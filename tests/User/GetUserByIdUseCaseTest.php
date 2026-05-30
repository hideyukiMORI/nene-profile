<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use NeneProfile\User\GetUserByIdInput;
use NeneProfile\User\GetUserByIdUseCase;
use NeneProfile\User\User;
use NeneProfile\User\UserNotFoundException;
use PHPUnit\Framework\TestCase;

final class GetUserByIdUseCaseTest extends TestCase
{
    private InMemoryUserRepository $repo;
    private GetUserByIdUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo    = new InMemoryUserRepository();
        $this->useCase = new GetUserByIdUseCase($this->repo);
    }

    private function seed(string $email, int $organizationId): int
    {
        return $this->repo->seed(new User(
            id: 0,
            email: $email,
            passwordHash: 'x',
            role: 'member',
            organizationId: $organizationId,
        ));
    }

    public function test_returns_user_within_organization(): void
    {
        $id = $this->seed('a@example.com', 7);

        $user = $this->useCase->execute(new GetUserByIdInput(id: $id, organizationId: 7));

        $this->assertSame($id, $user->id);
        $this->assertSame('a@example.com', $user->email);
    }

    public function test_throws_not_found_for_unknown_id(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->useCase->execute(new GetUserByIdInput(id: 999, organizationId: 7));
    }

    public function test_throws_not_found_for_cross_tenant_access(): void
    {
        $id = $this->seed('a@example.com', 7);

        $this->expectException(UserNotFoundException::class);

        $this->useCase->execute(new GetUserByIdInput(id: $id, organizationId: 99));
    }
}
