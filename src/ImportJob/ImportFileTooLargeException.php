<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use RuntimeException;

/**
 * Raised when an uploaded CSV exceeds the organization's configured maximum file
 * size (organization_settings.max_file_size_bytes; default 10 MiB). The limit is
 * per-organization, not a global constant (backend-standards §7).
 */
final class ImportFileTooLargeException extends RuntimeException
{
    public function __construct(public readonly int $maxBytes)
    {
        parent::__construct("The uploaded file exceeds the configured size limit of {$maxBytes} bytes.");
    }
}
