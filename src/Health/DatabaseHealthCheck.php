<?php

declare(strict_types=1);

namespace NeneProfile\Health;

use Nene2\Database\DatabaseConnectionFactoryInterface;
use Nene2\Http\HealthCheckInterface;
use Nene2\Http\HealthStatus;
use Throwable;

final readonly class DatabaseHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private DatabaseConnectionFactoryInterface $connectionFactory,
    ) {
    }

    public function name(): string
    {
        return 'database';
    }

    public function check(): HealthStatus
    {
        try {
            $pdo = $this->connectionFactory->create();
            $pdo->query('SELECT 1');

            return HealthStatus::Ok;
        } catch (Throwable) {
            return HealthStatus::Error;
        }
    }
}
