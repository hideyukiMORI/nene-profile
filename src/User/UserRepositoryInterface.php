<?php

declare(strict_types=1);

namespace NeneProfile\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    /** Find a user by ID scoped to an organization (tenant isolation). */
    public function findByIdInOrganization(int $id, int $organizationId): ?User;

    /** @return list<User> */
    public function findByOrganizationId(int $organizationId, int $limit, int $offset): array;

    public function countByOrganizationId(int $organizationId): int;

    public function emailExists(string $email): bool;

    public function save(User $user): int;

    public function updateRole(int $id, string $role): void;

    public function updateStatus(int $id, string $status): void;

    public function updatePassword(int $id, string $passwordHash): void;

    public function delete(int $id): void;

    public function count(): int;
}
