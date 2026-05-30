<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

use RuntimeException;

final class UnknownTransformerException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Unknown transformer: {$id}");
    }
}
