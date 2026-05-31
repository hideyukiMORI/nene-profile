<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\CsvExporter;
use NeneProfile\ImportJob\NormalizedTransaction;
use PHPUnit\Framework\TestCase;

final class CsvExporterBoundaryTest extends TestCase
{
    private function tx(string $description, ?string $counterparty = null, int $amountCents = 1000): NormalizedTransaction
    {
        return new NormalizedTransaction(
            rawRowNumber: 1,
            transactionDate: '2026-05-01',
            valueDate: '2026-05-01',
            amountCents: $amountCents,
            description: $description,
            counterparty: $counterparty,
            balanceCents: null,
            lineHash: 'sha256:abc',
        );
    }

    // ── Formula injection strip (ADR compliance §3.4) ─────────────────────

    public function test_strips_leading_equals_sign(): void
    {
        $csv = CsvExporter::export([$this->tx('=CMD')], 1, 1);

        $this->assertStringContainsString("'=CMD", $csv);
        $this->assertStringNotContainsString(',=CMD', $csv);
    }

    public function test_strips_leading_plus_sign(): void
    {
        $csv = CsvExporter::export([$this->tx('+1+1')], 1, 1);

        $this->assertStringContainsString("'+1+1", $csv);
    }

    public function test_strips_leading_minus_in_description(): void
    {
        $csv = CsvExporter::export([$this->tx('-formula')], 1, 1);

        $this->assertStringContainsString("'-formula", $csv);
    }

    public function test_strips_leading_at_sign(): void
    {
        $csv = CsvExporter::export([$this->tx('@SUM(A1)')], 1, 1);

        $this->assertStringContainsString("'@SUM(A1)", $csv);
    }

    public function test_does_not_strip_from_non_text_fields(): void
    {
        // amount_cents=-5000: leading minus is legitimate and must NOT be prefixed with quote
        $csv = CsvExporter::export([$this->tx('正常', null, -5000)], 1, 1);

        $this->assertStringContainsString('-5000', $csv);
        $this->assertStringNotContainsString("'-5000", $csv);
    }

    public function test_strips_formula_from_counterparty(): void
    {
        $csv = CsvExporter::export([$this->tx('desc', '=malicious')], 1, 1);

        $this->assertStringContainsString("'=malicious", $csv);
    }

    public function test_does_not_modify_normal_description(): void
    {
        $csv = CsvExporter::export([$this->tx('振込入金 ACME社')], 1, 1);

        $this->assertStringContainsString('振込入金 ACME社', $csv);
        $this->assertStringNotContainsString("'振込入金", $csv);
    }

    public function test_empty_description_is_not_prefixed(): void
    {
        $csv = CsvExporter::export([$this->tx('')], 1, 1);

        // Empty string should not become "'" — no quote prefix for empty
        $this->assertStringNotContainsString("'", $csv);
    }

    // ── Required fields presence ──────────────────────────────────────────

    public function test_all_standard_transaction_fields_present(): void
    {
        $csv = CsvExporter::export([$this->tx('入金')], 1, 1);

        foreach (['transaction_date', 'value_date', 'amount_cents', 'description',
                  'currency', 'raw_row_number', 'schema_version', 'line_hash'] as $col) {
            $this->assertStringContainsString($col, $csv);
        }
    }

    public function test_negative_amount_appears_correctly(): void
    {
        $csv = CsvExporter::export([$this->tx('出金', null, -30000)], 1, 1);

        $this->assertStringContainsString('-30000', $csv);
    }

    public function test_zero_transactions_returns_header_only(): void
    {
        $csv = CsvExporter::export([], 1, 1);

        $lines = array_filter(explode("\n", trim($csv)));
        $this->assertCount(1, $lines); // header only
    }

    // ── Import/preset ID injection ────────────────────────────────────────

    public function test_import_job_id_and_preset_version_id_injected(): void
    {
        $csv = CsvExporter::export([$this->tx('入金')], importJobId: 42, presetVersionId: 7);

        $this->assertStringContainsString('42', $csv);
        $this->assertStringContainsString('7', $csv);
    }
}
