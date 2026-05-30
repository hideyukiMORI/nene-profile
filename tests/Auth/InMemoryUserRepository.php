<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\User;
use NeneProfile\Auth\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private int $nextId = 1;

    /** @var array<int, User> */
    private array $store = [];

    public function findByEmail(string $email): ?User
    {
        foreach ($this->store as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?User
    {
        return $this->store[$id] ?? null;
    }

    /** @return list<User> */
    public function findByOrganizationId(int $organizationId): array
    {
        return array_values(array_filter(
            $this->store,
            static fn (User $u) => $u->organizationId === $organizationId,
        ));
    }

    public function create(string $email, string $passwordHash, string $role, ?int $organizationId): User
    {
        $id = $this->nextId++;
        $now = time();
        $user = new User(
            id: $id,
            email: $email,
            passwordHash: $passwordHash,
            role: $role,
            organizationId: $organizationId,
            status: 'active',
            createdAt: $now,
            updatedAt: $now,
        );
        $this->store[$id] = $user;

        return $user;
    }

    public function count(): int
    {
        return count($this->store);
    }
}
