<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * Parses and validates a raw mapping definition array into a MappingDefinition.
 *
 * This is the compliance gate for preset definitions: the rules here ensure a
 * preset can only ever produce schema-valid StandardTransaction output. Invalid
 * definitions are rejected with field-level errors rather than silently accepted.
 *
 * See: csv-normalization-spec.md, ADR 0003 (transformers), terminology.md §7.
 */
final class MappingDefinitionFactory
{
    /** Built-in transformer IDs (terminology.md §7). */
    private const TRANSFORMERS = [
        'trim',
        'date_ymd_slash',
        'date_ymd_dash',
        'date_ymd_compact',
        'date_era',
        'amount_yen_to_cents',
        'debit_credit_to_signed_cents',
        'single_column_signed_cents',
        'regex_extract',
    ];

    /** Output fields that every preset MUST map (csv-normalization-spec §2.1). */
    private const REQUIRED_FIELDS = ['transaction_date', 'amount_cents', 'description'];

    /** All logical fields an operator may map. */
    private const KNOWN_FIELDS = [
        'transaction_date',
        'value_date',
        'amount_cents',
        'description',
        'counterparty',
        'balance_cents',
    ];

    private const ENCODINGS = ['auto', 'utf-8', 'shift_jis'];
    private const DELIMITERS = ['auto', 'comma', 'tab'];

    /**
     * @param array<string, mixed> $raw
     * @throws InvalidMappingDefinitionException
     */
    public static function fromArray(array $raw): MappingDefinition
    {
        /** @var list<array{field: string, message: string, code: string}> $errors */
        $errors = [];

        $encoding = is_string($raw['encoding'] ?? null) ? (string) $raw['encoding'] : 'auto';
        if (!in_array($encoding, self::ENCODINGS, true)) {
            $errors[] = ['field' => 'encoding', 'message' => 'Unsupported encoding.', 'code' => 'invalid'];
        }

        $delimiter = is_string($raw['delimiter'] ?? null) ? (string) $raw['delimiter'] : 'auto';
        if (!in_array($delimiter, self::DELIMITERS, true)) {
            $errors[] = ['field' => 'delimiter', 'message' => 'Unsupported delimiter.', 'code' => 'invalid'];
        }

        $headerRowIndex = is_int($raw['header_row_index'] ?? null) ? (int) $raw['header_row_index'] : 0;
        if ($headerRowIndex < 0) {
            $errors[] = ['field' => 'header_row_index', 'message' => 'Must be zero or greater.', 'code' => 'invalid'];
        }

        $yearPivot = is_int($raw['year_pivot'] ?? null) ? (int) $raw['year_pivot'] : 50;
        if ($yearPivot < 0 || $yearPivot > 99) {
            $errors[] = ['field' => 'year_pivot', 'message' => 'Must be between 0 and 99.', 'code' => 'invalid'];
        }

        $rawColumns = is_array($raw['columns'] ?? null) ? $raw['columns'] : [];
        $columns = self::parseColumns($rawColumns, $errors);

        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($columns[$field])) {
                $errors[] = ['field' => "columns.{$field}", 'message' => 'This field mapping is required.', 'code' => 'required'];
            }
        }

        $skipRows = self::stringList($raw['skip_rows_matching'] ?? null);
        $lineIdentity = self::stringList($raw['line_identity'] ?? null);
        if ($lineIdentity === []) {
            $lineIdentity = ['transaction_date', 'amount_cents', 'description'];
        }

        if ($errors !== []) {
            throw new InvalidMappingDefinitionException($errors);
        }

        return new MappingDefinition(
            encoding: $encoding,
            delimiter: $delimiter,
            headerRowIndex: $headerRowIndex,
            yearPivot: $yearPivot,
            columns: $columns,
            skipRowsMatching: $skipRows,
            lineIdentity: $lineIdentity,
        );
    }

    /**
     * @param array<mixed> $rawColumns
     * @param list<array{field: string, message: string, code: string}> $errors
     * @return array<string, MappingColumn>
     */
    private static function parseColumns(array $rawColumns, array &$errors): array
    {
        $columns = [];

        foreach ($rawColumns as $field => $spec) {
            $field = (string) $field;

            if (!in_array($field, self::KNOWN_FIELDS, true)) {
                $errors[] = ['field' => "columns.{$field}", 'message' => 'Unknown logical field.', 'code' => 'unknown_field'];
                continue;
            }

            if (!is_array($spec)) {
                $errors[] = ['field' => "columns.{$field}", 'message' => 'Column spec must be an object.', 'code' => 'invalid'];
                continue;
            }

            $transform = is_string($spec['transform'] ?? null) ? (string) $spec['transform'] : '';
            if (!in_array($transform, self::TRANSFORMERS, true)) {
                $errors[] = ['field' => "columns.{$field}.transform", 'message' => 'Unknown transformer.', 'code' => 'unsupported_transformer'];
                continue;
            }

            $source = self::parseSource($spec['source'] ?? null);
            if ($source === null) {
                $errors[] = ['field' => "columns.{$field}.source", 'message' => 'Source column is required.', 'code' => 'required'];
                continue;
            }

            $optional = is_bool($spec['optional'] ?? null) ? (bool) $spec['optional'] : false;

            $columns[$field] = new MappingColumn(source: $source, transform: $transform, optional: $optional);
        }

        return $columns;
    }

    /**
     * @return string|list<string>|null
     */
    private static function parseSource(mixed $source): string|array|null
    {
        if (is_string($source) && $source !== '') {
            return $source;
        }

        if (is_array($source)) {
            $list = [];
            foreach ($source as $item) {
                if (is_string($item) && $item !== '') {
                    $list[] = $item;
                }
            }

            return $list !== [] ? $list : null;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $list = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $list[] = $item;
            }
        }

        return $list;
    }
}
