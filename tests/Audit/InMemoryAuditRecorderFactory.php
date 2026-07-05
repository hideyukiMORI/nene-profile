<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Audit;

use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditEventRepositoryInterface;
use Nene2\Audit\AuditQuery;
use Nene2\Audit\AuditRecorder;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Audit\AuditRecorderInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\ClockInterface;

/**
 * Test double for the framework audit recorder factory (NENE2 #1495).
 *
 * Wraps the real {@see AuditRecorder} over an in-memory event store so use-case
 * tests observe exactly what production records (occurredAt filled from the
 * injected clock) without a database. Every `forExecutor()` writes into the
 * same {@see $appended} list, mirroring the transaction-atomic factory.
 */
final class InMemoryAuditRecorderFactory implements AuditRecorderFactoryInterface, AuditEventRepositoryInterface
{
    /** @var list<AuditEvent> */
    public array $appended = [];

    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function forExecutor(DatabaseQueryExecutorInterface $executor): AuditRecorderInterface
    {
        return new AuditRecorder($this, $this->clock);
    }

    public function append(AuditEvent $event): void
    {
        $this->appended[] = $event;
    }

    /** @return list<AuditEvent> */
    public function query(AuditQuery $query, int $limit, int $offset): array
    {
        $filtered = array_values(array_filter(
            $this->appended,
            static fn (AuditEvent $e): bool => $query->organizationId === null || $e->organizationId === $query->organizationId,
        ));

        return array_slice($filtered, $offset, $limit);
    }

    public function count(AuditQuery $query): int
    {
        return count(array_filter(
            $this->appended,
            static fn (AuditEvent $e): bool => $query->organizationId === null || $e->organizationId === $query->organizationId,
        ));
    }
}
