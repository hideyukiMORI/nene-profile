<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Support;

use Nene2\Database\DatabaseTransactionManagerInterface;

/**
 * Runs the callback immediately with a placeholder executor (no real
 * transaction). Use-case tests pair this with factories that ignore the
 * executor and return in-memory repositories.
 */
final class ImmediateTransactionManager implements DatabaseTransactionManagerInterface
{
    public function transactional(callable $callback): mixed
    {
        return $callback(new NullQueryExecutor());
    }
}
