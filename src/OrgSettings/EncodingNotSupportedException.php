<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

use RuntimeException;

final class EncodingNotSupportedException extends RuntimeException
{
    public function __construct(string $encoding)
    {
        parent::__construct("Unsupported encoding: {$encoding}");
    }
}
