<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use RuntimeException;

/**
 * Thrown when the whole file cannot be read (undecodable, empty, no rows).
 * Row-level problems are NOT exceptions — they become import_job_errors.
 */
final class CsvParseException extends RuntimeException
{
}
