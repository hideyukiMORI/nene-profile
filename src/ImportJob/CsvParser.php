<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use NeneProfile\Preset\MappingDefinition;

/**
 * Decodes and parses bank CSV into header + data rows.
 *
 * Encoding detection: UTF-8 (BOM optional) and Shift_JIS (CSV spec §1, ADR 0003 §4).
 * Delimiter detection: comma or tab. Both can be forced via the preset definition.
 * Header row index and skip patterns come from the definition.
 */
final readonly class CsvParser
{
    public function parse(string $rawBytes, MappingDefinition $definition): ParsedCsv
    {
        $encoding = $this->resolveEncoding($rawBytes, $definition->encoding);
        $text = $this->decode($rawBytes, $encoding);

        $lines = preg_split('/\r\n|\r|\n/', $text);
        if ($lines === false) {
            throw new CsvParseException('Could not split file into lines.');
        }

        // Drop a trailing empty line from a final newline.
        while ($lines !== [] && end($lines) === '') {
            array_pop($lines);
        }

        if ($lines === []) {
            throw new CsvParseException('The file contains no rows.');
        }

        $delimiter = $this->resolveDelimiter($lines, $definition->delimiter);

        $headerIndex = $definition->headerRowIndex;
        if (!isset($lines[$headerIndex])) {
            throw new CsvParseException('Header row index is beyond the end of the file.');
        }

        $header = $this->splitLine($lines[$headerIndex], $delimiter);

        $rows = [];
        $skipPatterns = $definition->skipRowsMatching;

        foreach ($lines as $index => $line) {
            if ($index <= $headerIndex) {
                continue;
            }

            if ($this->shouldSkip($line, $skipPatterns)) {
                continue;
            }

            $cells = $this->splitLine($line, $delimiter);
            $assoc = [];
            foreach ($header as $i => $name) {
                $assoc[$name] = $cells[$i] ?? '';
            }

            $rows[] = [
                'rowNumber' => $index + 1, // 1-based source line
                'cells'     => $assoc,
                'raw'       => $line,
            ];
        }

        return new ParsedCsv(
            header: $header,
            rows: $rows,
            detectedEncoding: $encoding,
            detectedDelimiter: $delimiter === "\t" ? 'tab' : 'comma',
        );
    }

    private function resolveEncoding(string $bytes, string $configured): string
    {
        if ($configured === 'utf-8' || $configured === 'shift_jis') {
            return $configured;
        }

        // BOM → UTF-8.
        if (str_starts_with($bytes, "\xEF\xBB\xBF")) {
            return 'utf-8';
        }

        // Valid UTF-8 → UTF-8; otherwise assume Shift_JIS.
        return mb_check_encoding($bytes, 'UTF-8') ? 'utf-8' : 'shift_jis';
    }

    private function decode(string $bytes, string $encoding): string
    {
        if ($encoding === 'utf-8') {
            // Strip BOM if present.
            if (str_starts_with($bytes, "\xEF\xBB\xBF")) {
                $bytes = substr($bytes, 3);
            }

            if (!mb_check_encoding($bytes, 'UTF-8')) {
                throw new CsvParseException('File declared as UTF-8 is not valid UTF-8.');
            }

            return $bytes;
        }

        $converted = mb_convert_encoding($bytes, 'UTF-8', 'SJIS-win');

        if ($converted === '') {
            throw new CsvParseException('Could not decode Shift_JIS content.');
        }

        return $converted;
    }

    /**
     * @param list<string> $lines
     */
    private function resolveDelimiter(array $lines, string $configured): string
    {
        if ($configured === 'comma') {
            return ',';
        }

        if ($configured === 'tab') {
            return "\t";
        }

        // Auto: pick whichever appears more often on the first non-empty line.
        $sample = '';
        foreach ($lines as $line) {
            if ($line !== '') {
                $sample = $line;
                break;
            }
        }

        return substr_count($sample, "\t") > substr_count($sample, ',') ? "\t" : ',';
    }

    /**
     * @return list<string>
     */
    private function splitLine(string $line, string $delimiter): array
    {
        $parsed = str_getcsv($line, $delimiter, '"', '\\');

        return array_map(static fn ($cell): string => (string) ($cell ?? ''), $parsed);
    }

    /**
     * @param list<string> $patterns
     */
    private function shouldSkip(string $line, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (@preg_match('/' . str_replace('/', '\/', $pattern) . '/u', $line) === 1) {
                return true;
            }
        }

        return false;
    }
}
