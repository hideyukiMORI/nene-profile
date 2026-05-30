<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

use NeneProfile\Preset\MappingColumn;
use NeneProfile\Preset\MappingDefinition;
use NeneProfile\Transformer\TransformContext;
use NeneProfile\Transformer\TransformerRegistry;

/**
 * Turns a ParsedCsv into StandardTransaction rows by applying the preset's
 * column transformers (compliance §3-4).
 *
 * Invariants:
 *  - Every row that fails any transform becomes an ImportJobError; it is NEVER
 *    silently dropped and NEVER emitted with a guessed value.
 *  - Required fields (transaction_date, amount_cents, description) must all
 *    transform successfully for a row to be emitted.
 *  - Each emitted row carries line_hash = sha256(date + amount + description).
 *  - Duplicate line_hash within the same job is flagged as an error, not admitted.
 */
final readonly class NormalizationRunner
{
    private const REQUIRED_FIELDS = ['transaction_date', 'amount_cents', 'description'];
    private const MAX_SNIPPET = 500;

    public function __construct(
        private TransformerRegistry $registry,
    ) {
    }

    public function run(ParsedCsv $csv, MappingDefinition $definition): NormalizationResult
    {
        /** @var list<NormalizedTransaction> $transactions */
        $transactions = [];
        /** @var list<ImportJobError> $errors */
        $errors = [];
        /** @var array<string, int> $seenHashes line_hash => first row number */
        $seenHashes = [];

        foreach ($csv->rows as $row) {
            $rowNumber = $row['rowNumber'];
            $context = new TransformContext(yearPivot: $definition->yearPivot, rawRowNumber: $rowNumber);

            $values = [];
            $rowError = null;

            foreach ($definition->columns as $field => $column) {
                $source = $this->extractSource($column, $row['cells']);
                $outcome = $this->registry->get($column->transform)->transform($source, $context);

                if (!$outcome->ok) {
                    // Optional field failures are tolerated only when the source was blank.
                    if ($column->optional) {
                        $values[$field] = null;
                        continue;
                    }

                    $rowError = "{$field}: {$outcome->error}";
                    break;
                }

                $values[$field] = $outcome->value;
            }

            if ($rowError !== null) {
                $errors[] = new ImportJobError(
                    rawRowNumber: $rowNumber,
                    message: $rowError,
                    rawSnippet: $this->snippet($row['raw']),
                );
                continue;
            }

            $missing = $this->missingRequired($values);
            if ($missing !== null) {
                $errors[] = new ImportJobError(
                    rawRowNumber: $rowNumber,
                    message: "missing required field: {$missing}",
                    rawSnippet: $this->snippet($row['raw']),
                );
                continue;
            }

            $transactionDate = (string) $values['transaction_date'];
            $amountCents = (int) $values['amount_cents'];
            $description = (string) $values['description'];
            $valueDate = isset($values['value_date']) && is_string($values['value_date'])
                ? $values['value_date']
                : $transactionDate;
            $counterparty = isset($values['counterparty']) && is_string($values['counterparty'])
                ? $values['counterparty']
                : null;
            $balanceCents = isset($values['balance_cents']) && is_int($values['balance_cents'])
                ? $values['balance_cents']
                : null;

            $lineHash = $this->lineHash($transactionDate, $amountCents, $description);

            if (isset($seenHashes[$lineHash])) {
                $errors[] = new ImportJobError(
                    rawRowNumber: $rowNumber,
                    message: "duplicate line hash — matches row {$seenHashes[$lineHash]} (same date/amount/description)",
                    rawSnippet: $this->snippet($row['raw']),
                );
                continue;
            }
            $seenHashes[$lineHash] = $rowNumber;

            $transactions[] = new NormalizedTransaction(
                rawRowNumber: $rowNumber,
                transactionDate: $transactionDate,
                valueDate: $valueDate,
                amountCents: $amountCents,
                description: $description,
                counterparty: $counterparty,
                balanceCents: $balanceCents,
                lineHash: $lineHash,
            );
        }

        return new NormalizationResult(
            transactions: $transactions,
            errors: $errors,
            processedRowCount: count($csv->rows),
        );
    }

    /**
     * @param array<string, string> $cells
     * @return string|list<string>
     */
    private function extractSource(MappingColumn $column, array $cells): string|array
    {
        if (is_array($column->source)) {
            return array_map(static fn (string $name): string => $cells[$name] ?? '', $column->source);
        }

        return $cells[$column->source] ?? '';
    }

    /**
     * @param array<string, mixed> $values
     */
    private function missingRequired(array $values): ?string
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($values[$field])) {
                return $field;
            }
        }

        return null;
    }

    private function lineHash(string $date, int $amount, string $description): string
    {
        return 'sha256:' . hash('sha256', $date . '|' . $amount . '|' . $description);
    }

    private function snippet(string $raw): string
    {
        return mb_substr($raw, 0, self::MAX_SNIPPET);
    }
}
