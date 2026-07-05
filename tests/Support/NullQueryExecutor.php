<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Support;

use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;

/**
 * Placeholder executor for transaction-manager test doubles. Use cases under
 * test build repositories from factories that ignore the executor, so these
 * methods must never be called.
 */
final class NullQueryExecutor implements DatabaseQueryExecutorInterface
{
    public function execute(string $sql, array $parameters = []): int
    {
        throw new LogicException('NullQueryExecutor::execute should not be called in tests.');
    }

    public function insert(string $sql, array $parameters = []): int
    {
        throw new LogicException('NullQueryExecutor::insert should not be called in tests.');
    }

    public function lastInsertId(): int
    {
        throw new LogicException('NullQueryExecutor::lastInsertId should not be called in tests.');
    }

    public function fetchOne(string $sql, array $parameters = []): ?array
    {
        throw new LogicException('NullQueryExecutor::fetchOne should not be called in tests.');
    }

    public function fetchAll(string $sql, array $parameters = []): array
    {
        throw new LogicException('NullQueryExecutor::fetchAll should not be called in tests.');
    }
}
