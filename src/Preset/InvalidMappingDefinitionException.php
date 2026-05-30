<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use RuntimeException;

final class InvalidMappingDefinitionException extends RuntimeException
{
    /**
     * @param list<array{field: string, message: string, code: string}> $errors
     */
    public function __construct(
        public readonly array $errors,
    ) {
        parent::__construct('The mapping definition is invalid.');
    }
}
