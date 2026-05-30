<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use RuntimeException;

final class ImportJobNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Import job not found: {$id}");
    }
}
