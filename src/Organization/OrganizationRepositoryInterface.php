<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

interface OrganizationRepositoryInterface
{
    public function findById(int $id): ?Organization;

    public function findBySlug(string $slug): ?Organization;

    public function findByCustomDomain(string $domain): ?Organization;

    /** @return list<Organization> */
    public function findAll(int $limit, int $offset): array;

    public function count(): int;

    public function save(Organization $organization): int;

    public function update(Organization $organization): void;

    public function delete(int $id): void;
}
