<?php

declare(strict_types=1);

namespace NeneProfile\Audit;

/**
 * Append-only persistence for the audit trail (ADR 0005).
 * No UPDATE or DELETE operations are permitted.
 */
interface AuditLogRepositoryInterface
{
    public function append(AuditLog $log): int;

    /** @return list<AuditLog> */
    public function findByOrganization(int $organizationId, int $limit, int $offset): array;

    public function countByOrganization(int $organizationId): int;

    /** @return list<AuditLog> */
    public function findAll(int $limit, int $offset): array;

    public function countAll(): int;
}
