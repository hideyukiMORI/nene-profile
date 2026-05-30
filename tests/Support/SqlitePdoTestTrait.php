<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Support;

use Nene2\Database\DatabaseConnectionFactoryInterface;
use Nene2\Database\PdoDatabaseQueryExecutor;
use PDO;

/**
 * Shared setup for Pdo repository integration tests: a real in-memory SQLite
 * database wired through the NENE2 query executor. Catches DB-portability
 * regressions (e.g. MySQL-only SQL) without needing a MySQL server.
 */
trait SqlitePdoTestTrait
{
    private function sqlitePdo(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    private function executor(PDO $pdo): PdoDatabaseQueryExecutor
    {
        $factory = new class ($pdo) implements DatabaseConnectionFactoryInterface {
            public function __construct(private readonly PDO $pdo)
            {
            }

            public function create(): PDO
            {
                return $this->pdo;
            }
        };

        return new PdoDatabaseQueryExecutor($factory, $pdo);
    }
}
