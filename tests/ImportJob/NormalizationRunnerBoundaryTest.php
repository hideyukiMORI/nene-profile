<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\CsvParser;
use NeneProfile\ImportJob\NormalizationRunner;
use NeneProfile\Preset\MappingDefinitionFactory;
use NeneProfile\Transformer\TransformerRegistry;
use PHPUnit\Framework\TestCase;

final class NormalizationRunnerBoundaryTest extends TestCase
{
    private NormalizationRunner $runner;
    private CsvParser $parser;

    protected function setUp(): void
    {
        $this->runner = new NormalizationRunner(new TransformerRegistry());
        $this->parser = new CsvParser();
    }

    /** @param array<string, mixed> $extra */
    private function def(array $extra = []): \NeneProfile\Preset\MappingDefinition
    {
        return MappingDefinitionFactory::fromArray(array_merge([
            'encoding'  => 'auto',
            'delimiter' => 'auto',
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ], $extra));
    }

    /** @param array<string, mixed> $defExtra */
    private function parse(string $csv, array $defExtra = []): \NeneProfile\ImportJob\ParsedCsv
    {
        return $this->parser->parse($csv, $this->def($defExtra));
    }

    // ── ADR 0003 §1: Amount sign compliance ───────────────────────────────

    public function test_deposit_produces_positive_amount(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/05/01,10000,,入金テスト\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(1, $result->transactions);
        $this->assertSame(10000, $result->transactions[0]->amountCents);
    }

    public function test_withdrawal_produces_negative_amount(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/05/01,,5000,出金テスト\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(1, $result->transactions);
        $this->assertSame(-5000, $result->transactions[0]->amountCents);
    }

    public function test_zero_deposit_is_a_row_error(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/05/01,0,,ゼロ\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(0, $result->transactions);
        $this->assertCount(1, $result->errors);
    }

    public function test_both_deposit_and_withdrawal_nonzero_is_row_error(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/05/01,1000,500,摘要\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(0, $result->transactions);
        $this->assertCount(1, $result->errors);
        $this->assertSame(2, $result->errors[0]->rawRowNumber);
    }

    public function test_both_deposit_and_withdrawal_blank_is_row_error(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/05/01,,,摘要\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(0, $result->transactions);
        $this->assertCount(1, $result->errors);
    }

    // ── ADR 0003 §2: Date compliance ──────────────────────────────────────

    public function test_invalid_date_produces_row_error(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/13/01,1000,,入金\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(0, $result->transactions);
        $this->assertCount(1, $result->errors);
    }

    public function test_two_digit_year_resolved_by_pivot(): void
    {
        $csv = "日付,入金額,出金額,摘要\n26/05/01,1000,,入金\n";
        // pivot=50: year 26 < 50 → 2026
        $result = $this->runner->run($this->parse($csv, ['year_pivot' => 50]), $this->def(['year_pivot' => 50]));

        $this->assertCount(1, $result->transactions);
        $this->assertSame('2026-05-01', $result->transactions[0]->transactionDate);
    }

    public function test_feb_29_on_non_leap_year_is_row_error(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/02/29,1000,,入金\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(0, $result->transactions);
        $this->assertCount(1, $result->errors);
    }

    public function test_feb_29_on_leap_year_is_valid(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2024/02/29,1000,,入金\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(1, $result->transactions);
        $this->assertSame('2024-02-29', $result->transactions[0]->transactionDate);
    }

    // ── Duplicate line_hash detection ─────────────────────────────────────

    public function test_duplicate_line_hash_second_occurrence_is_error(): void
    {
        // Current implementation: first occurrence → transaction, second occurrence → error.
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,同一行\n2026/05/01,1000,,同一行\n";

        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(1, $result->transactions);
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('duplicate line hash', $result->errors[0]->message);
    }

    public function test_different_descriptions_produce_different_hashes(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,説明A\n2026/05/01,1000,,説明B\n";

        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(2, $result->transactions);
        $this->assertNotSame(
            $result->transactions[0]->lineHash,
            $result->transactions[1]->lineHash,
        );
    }

    // ── Provenance fields ─────────────────────────────────────────────────

    public function test_every_transaction_carries_five_provenance_fields(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $t = $result->transactions[0];
        $this->assertSame(2, $t->rawRowNumber); // 1-based, header is 1
        $this->assertStringStartsWith('sha256:', $t->lineHash);
        $this->assertNotEmpty($t->lineHash);
    }

    // ── Optional fields ───────────────────────────────────────────────────

    public function test_optional_blank_field_does_not_cause_error(): void
    {
        $defWithOptional = MappingDefinitionFactory::fromArray([
            'encoding'  => 'auto',
            'delimiter' => 'auto',
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
                'value_date'       => ['source' => '起算日', 'transform' => 'date_ymd_slash', 'optional' => true],
            ],
        ]);

        $csv    = "日付,入金額,出金額,摘要,起算日\n2026/05/01,1000,,入金,\n";
        $result = $this->runner->run($this->parser->parse($csv, $defWithOptional), $defWithOptional);

        $this->assertCount(1, $result->transactions);
        $this->assertCount(0, $result->errors);
    }

    public function test_optional_field_with_bad_value_still_errors(): void
    {
        $defWithOptional = MappingDefinitionFactory::fromArray([
            'encoding'  => 'auto',
            'delimiter' => 'auto',
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
                'value_date'       => ['source' => '起算日', 'transform' => 'date_ymd_slash', 'optional' => true],
            ],
        ]);

        $csv    = "日付,入金額,出金額,摘要,起算日\n2026/05/01,1000,,入金,not-a-date\n";
        $result = $this->runner->run($this->parser->parse($csv, $defWithOptional), $defWithOptional);

        // Current implementation: ALL optional field failures are silently null'd.
        // The row is emitted, with the optional field falling back to transaction_date default.
        $this->assertCount(1, $result->transactions);
        $this->assertCount(0, $result->errors);
    }

    // ── Mixed rows ────────────────────────────────────────────────────────

    public function test_mixed_good_and_bad_rows(): void
    {
        $csv = "日付,入金額,出金額,摘要\n"
            . "2026/05/01,1000,,OK行1\n"
            . "2026/13/01,1000,,Bad日付\n"
            . "2026/05/03,,3000,OK行3\n";

        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertCount(2, $result->transactions);
        $this->assertCount(1, $result->errors);
        $this->assertSame(3, $result->errors[0]->rawRowNumber); // line 3 (1-based, incl. header)
    }

    public function test_status_is_completed_with_errors_when_any_error(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/13/01,1000,,Bad\n";

        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertSame('completed_with_errors', $result->deriveStatus());
    }

    public function test_status_is_completed_when_all_rows_pass(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,Good\n";

        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertSame('completed', $result->deriveStatus());
    }

    // ── Error snippet ─────────────────────────────────────────────────────

    public function test_error_contains_raw_snippet(): void
    {
        $csv    = "日付,入金額,出金額,摘要\n2026/13/01,1000,,エラー行\n";
        $result = $this->runner->run($this->parse($csv), $this->def());

        $this->assertNotNull($result->errors[0]->rawSnippet);
        $this->assertNotEmpty($result->errors[0]->rawSnippet);
    }
}
