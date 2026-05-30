<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    /** @return list<User> */
    public function findByOrganizationId(int $organizationId): array;

    public function create(
        string $email,
        string $passwordHash,
        string $role,
        ?int $organizationId,
    ): User;

    public function count(): int;
}
