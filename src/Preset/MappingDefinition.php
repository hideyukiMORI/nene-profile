<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * The complete, self-contained specification of how a bank CSV maps to
 * StandardTransaction rows. Stored as JSON in mapping_preset_versions and never
 * mutated once persisted (ADR 0004).
 */
final readonly class MappingDefinition
{
    /**
     * @param array<string, MappingColumn> $columns        logical field => mapping
     * @param list<string>                  $skipRowsMatching regex patterns; matching rows are skipped
     * @param list<string>                  $lineIdentity    fields hashed into line_hash
     */
    public function __construct(
        public string $encoding,
        public string $delimiter,
        public int $headerRowIndex,
        public int $yearPivot,
        public array $columns,
        public array $skipRowsMatching = [],
        public array $lineIdentity = ['transaction_date', 'amount_cents', 'description'],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $columns = [];
        foreach ($this->columns as $field => $column) {
            $columns[$field] = $column->toArray();
        }

        return [
            'encoding'           => $this->encoding,
            'delimiter'          => $this->delimiter,
            'header_row_index'   => $this->headerRowIndex,
            'year_pivot'         => $this->yearPivot,
            'columns'            => $columns,
            'skip_rows_matching' => $this->skipRowsMatching,
            'line_identity'      => $this->lineIdentity,
        ];
    }
}
