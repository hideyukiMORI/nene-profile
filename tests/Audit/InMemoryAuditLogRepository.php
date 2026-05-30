<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use NeneProfile\Audit\AuditLog;
use NeneProfile\Audit\AuditLogRepositoryInterface;

final class InMemoryAuditLogRepository implements AuditLogRepositoryInterface
{
    private int $nextId = 1;

    /** @var list<AuditLog> */
    private array $store = [];

    public function append(AuditLog $log): int
    {
        $id = $this->nextId++;
        $this->store[] = new AuditLog(
            action: $log->action,
            entityType: $log->entityType,
            actorUserId: $log->actorUserId,
            organizationId: $log->organizationId,
            entityId: $log->entityId,
            before: $log->before,
            after: $log->after,
            id: $id,
            createdAt: date('Y-m-d H:i:s'),
        );

        return $id;
    }

    /** @return list<AuditLog> */
    public function findByOrganization(int $organizationId, int $limit, int $offset): array
    {
        $filtered = array_values(array_filter(
            $this->store,
            static fn (AuditLog $l) => $l->organizationId === $organizationId,
        ));

        return array_slice($filtered, $offset, $limit);
    }

    public function countByOrganization(int $organizationId): int
    {
        return count(array_filter(
            $this->store,
            static fn (AuditLog $l) => $l->organizationId === $organizationId,
        ));
    }

    /** @return list<AuditLog> */
    public function findAll(int $limit, int $offset): array
    {
        return array_slice($this->store, $offset, $limit);
    }

    public function countAll(): int
    {
        return count($this->store);
    }

    /** @return list<AuditLog> */
    public function all(): array
    {
        return $this->store;
    }
}
