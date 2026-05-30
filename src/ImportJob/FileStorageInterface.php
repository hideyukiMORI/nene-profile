<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

/**
 * Immutable storage for original uploaded files (ADR 0004). Once stored, a file
 * is never modified, overwritten, or deleted by application processes.
 */
interface FileStorageInterface
{
    /**
     * Store the raw bytes for an organization's import and return an opaque
     * storage key. Implementations must not overwrite an existing key.
     */
    public function store(int $organizationId, string $contents): string;

    public function read(string $key): string;

    public function exists(string $key): bool;
}
