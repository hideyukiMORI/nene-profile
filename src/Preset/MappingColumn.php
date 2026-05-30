<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * A single logical-field → source-column(s) mapping with a transformer.
 *
 * `source` is either a single column header or, for two-column transforms
 * (e.g. debit_credit_to_signed_cents), a list of headers.
 */
final readonly class MappingColumn
{
    /**
     * @param string|list<string> $source
     */
    public function __construct(
        public string|array $source,
        public string $transform,
        public bool $optional = false,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'source'    => $this->source,
            'transform' => $this->transform,
            'optional'  => $this->optional,
        ];
    }
}
