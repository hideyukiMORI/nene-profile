<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Organization;

use NeneProfile\Organization\Organization;
use NeneProfile\Organization\OrganizationNotFoundException;
use NeneProfile\Organization\OrganizationRepositoryInterface;
use NeneProfile\Organization\OrganizationSlugConflictException;

final class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    private int $nextId = 1;

    /** @var array<int, Organization> */
    private array $store = [];

    public function findById(int $id): ?Organization
    {
        return $this->store[$id] ?? null;
    }

    public function findBySlug(string $slug): ?Organization
    {
        foreach ($this->store as $org) {
            if ($org->slug === $slug) {
                return $org;
            }
        }

        return null;
    }

    public function findByCustomDomain(string $domain): ?Organization
    {
        foreach ($this->store as $org) {
            if ($org->customDomain === $domain) {
                return $org;
            }
        }

        return null;
    }

    /** @return list<Organization> */
    public function findAll(int $limit, int $offset): array
    {
        return array_slice(array_values($this->store), $offset, $limit);
    }

    public function count(): int
    {
        return count($this->store);
    }

    public function save(Organization $organization): int
    {
        if ($this->findBySlug($organization->slug) !== null) {
            throw new OrganizationSlugConflictException($organization->slug);
        }

        $id = $this->nextId++;
        $now = date('Y-m-d H:i:s');
        $this->store[$id] = new Organization(
            name: $organization->name,
            slug: $organization->slug,
            isActive: $organization->isActive,
            id: $id,
            customDomain: $organization->customDomain,
            createdAt: $now,
            updatedAt: $now,
        );

        return $id;
    }

    public function update(Organization $organization): void
    {
        if ($organization->id === null || !isset($this->store[$organization->id])) {
            throw new OrganizationNotFoundException($organization->id ?? 0);
        }

        $this->store[$organization->id] = $organization;
    }

    public function delete(int $id): void
    {
        if (!isset($this->store[$id])) {
            throw new OrganizationNotFoundException($id);
        }

        unset($this->store[$id]);
    }
}
