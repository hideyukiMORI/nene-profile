<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

/**
 * The result of decoding + parsing a CSV: the header row and the data rows,
 * each keyed by header name. Each data row also carries its 1-based source line.
 */
final readonly class ParsedCsv
{
    /**
     * @param list<string>                              $header
     * @param list<array{rowNumber: int, cells: array<string, string>, raw: string}> $rows
     */
    public function __construct(
        public array $header,
        public array $rows,
        public string $detectedEncoding,
        public string $detectedDelimiter,
    ) {
    }
}
