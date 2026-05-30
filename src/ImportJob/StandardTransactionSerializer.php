<?php

declare(strict_types=1);

namespace NeneProfile\ImportJob;

/**
 * Serializes a NormalizedTransaction to the StandardTransaction v1.0 shape,
 * attaching the five provenance fields (ADR 0004). The import_job_id and
 * preset_version_id are supplied by the caller (they live on the job).
 */
final class StandardTransactionSerializer
{
    public const SCHEMA_VERSION = '1.0';

    /** Output field order for CSV export (output-schema.md). */
    public const CSV_COLUMNS = [
        'schema_version', 'transaction_date', 'value_date', 'amount_cents', 'description',
        'counterparty', 'balance_cents', 'currency', 'raw_row_number', 'import_job_id',
        'preset_version_id', 'line_hash',
    ];

    /** @return array<string, mixed> */
    public static function toArray(NormalizedTransaction $t, int $importJobId, int $presetVersionId): array
    {
        return [
            'schema_version'    => self::SCHEMA_VERSION,
            'transaction_date'  => $t->transactionDate,
            'value_date'        => $t->valueDate,
            'amount_cents'      => $t->amountCents,
            'description'       => $t->description,
            'counterparty'      => $t->counterparty ?? '',
            'balance_cents'     => $t->balanceCents,
            'currency'          => $t->currency,
            'raw_row_number'    => $t->rawRowNumber,
            'import_job_id'     => (string) $importJobId,
            'preset_version_id' => (string) $presetVersionId,
            'line_hash'         => $t->lineHash,
        ];
    }
}
