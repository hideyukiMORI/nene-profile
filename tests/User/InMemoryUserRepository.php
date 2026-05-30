<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use NeneProfile\User\User;
use NeneProfile\User\UserRepositoryInterface;

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

    public function findByIdInOrganization(int $id, int $organizationId): ?User
    {
        $user = $this->store[$id] ?? null;

        return ($user !== null && $user->organizationId === $organizationId) ? $user : null;
    }

    /** @return list<User> */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array
    {
        $filtered = array_values(array_filter(
            $this->store,
            static fn (User $u) => $u->organizationId === $organizationId,
        ));

        return array_slice($filtered, $offset, $limit);
    }

    public function countByOrganizationId(int $organizationId): int
    {
        return count(array_filter(
            $this->store,
            static fn (User $u) => $u->organizationId === $organizationId,
        ));
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function save(User $user): int
    {
        $id = $this->nextId++;
        $now = time();
        $this->store[$id] = new User(
            id: $id,
            email: $user->email,
            passwordHash: $user->passwordHash,
            role: $user->role,
            organizationId: $user->organizationId,
            status: $user->status,
            createdAt: $now,
            updatedAt: $now,
        );

        return $id;
    }

    public function updateRole(int $id, string $role): void
    {
        $u = $this->store[$id] ?? null;
        if ($u === null) {
            return;
        }
        $this->store[$id] = new User(
            id: $u->id,
            email: $u->email,
            passwordHash: $u->passwordHash,
            role: $role,
            organizationId: $u->organizationId,
            status: $u->status,
            createdAt: $u->createdAt,
            updatedAt: time(),
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $u = $this->store[$id] ?? null;
        if ($u === null) {
            return;
        }
        $this->store[$id] = new User(
            id: $u->id,
            email: $u->email,
            passwordHash: $u->passwordHash,
            role: $u->role,
            organizationId: $u->organizationId,
            status: $status,
            createdAt: $u->createdAt,
            updatedAt: time(),
        );
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $u = $this->store[$id] ?? null;
        if ($u === null) {
            return;
        }
        $this->store[$id] = new User(
            id: $u->id,
            email: $u->email,
            passwordHash: $passwordHash,
            role: $u->role,
            organizationId: $u->organizationId,
            status: $u->status,
            createdAt: $u->createdAt,
            updatedAt: time(),
        );
    }

    public function delete(int $id): void
    {
        unset($this->store[$id]);
    }

    public function count(): int
    {
        return count($this->store);
    }

    /** Test helper: seed a user directly and return its ID. */
    public function seed(User $user): int
    {
        return $this->save($user);
    }
}
