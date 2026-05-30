<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\FileStorageInterface;
use RuntimeException;

final class InMemoryFileStorage implements FileStorageInterface
{
    /** @var array<string, string> */
    private array $store = [];

    private int $counter = 0;

    public function store(int $organizationId, string $contents): string
    {
        $key = $organizationId . '/' . (++$this->counter) . '.csv';

        if (isset($this->store[$key])) {
            throw new RuntimeException('Refusing to overwrite');
        }

        $this->store[$key] = $contents;

        return $key;
    }

    public function read(string $key): string
    {
        return $this->store[$key] ?? throw new RuntimeException("not found: {$key}");
    }

    public function exists(string $key): bool
    {
        return isset($this->store[$key]);
    }

    public function count(): int
    {
        return count($this->store);
    }
}
