<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\CsvParser;
use NeneProfile\ImportJob\ImportJob;
use NeneProfile\ImportJob\NormalizationRunner;
use NeneProfile\Preset\MappingDefinition;
use NeneProfile\Preset\MappingDefinitionFactory;
use NeneProfile\Transformer\TransformerRegistry;
use PHPUnit\Framework\TestCase;

final class NormalizationRunnerTest extends TestCase
{
    private NormalizationRunner $runner;
    private CsvParser $parser;

    protected function setUp(): void
    {
        $this->runner = new NormalizationRunner(new TransformerRegistry());
        $this->parser = new CsvParser();
    }

    private function mufgDefinition(): MappingDefinition
    {
        return MappingDefinitionFactory::fromArray([
            'encoding'  => 'auto',
            'delimiter' => 'comma',
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金', '出金'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
                'balance_cents'    => ['source' => '残高', 'transform' => 'amount_yen_to_cents', 'optional' => true],
            ],
        ]);
    }

    private function runCsv(string $csv): \NeneProfile\ImportJob\NormalizationResult
    {
        $def = $this->mufgDefinition();
        $parsed = $this->parser->parse($csv, $def);

        return $this->runner->run($parsed, $def);
    }

    public function test_normalizes_clean_rows(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n"
            . "2026/05/15,100000,,振込 アオイ,5025000\n"
            . "2026/05/16,,30000,引落 デンキ,4995000\n";

        $result = $this->runCsv($csv);

        $this->assertCount(2, $result->transactions);
        $this->assertCount(0, $result->errors);
        $this->assertSame(ImportJob::STATUS_COMPLETED, $result->deriveStatus());

        $first = $result->transactions[0];
        $this->assertSame('2026-05-15', $first->transactionDate);
        $this->assertSame(100000, $first->amountCents); // deposit positive
        $this->assertSame('振込 アオイ', $first->description);
        $this->assertSame(5025000, $first->balanceCents);

        $second = $result->transactions[1];
        $this->assertSame(-30000, $second->amountCents); // withdrawal negative
    }

    public function test_line_hash_is_deterministic_and_prefixed(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n2026/05/15,100000,,振込,5025000\n";
        $result = $this->runCsv($csv);

        $hash = $result->transactions[0]->lineHash;
        $this->assertStringStartsWith('sha256:', $hash);
        $expected = 'sha256:' . hash('sha256', '2026-05-15|100000|振込');
        $this->assertSame($expected, $hash);
    }

    public function test_bad_date_row_becomes_error_not_dropped(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n"
            . "2026/05/15,100000,,ok,5025000\n"
            . "not-a-date,100000,,bad,5025000\n";

        $result = $this->runCsv($csv);

        $this->assertCount(1, $result->transactions);
        $this->assertCount(1, $result->errors);
        $this->assertSame(ImportJob::STATUS_COMPLETED_WITH_ERRORS, $result->deriveStatus());
        $this->assertStringContainsString('transaction_date', $result->errors[0]->message);
        $this->assertSame(3, $result->errors[0]->rawRowNumber);
    }

    public function test_both_debit_and_credit_nonzero_is_error(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n2026/05/15,100000,30000,both,5025000\n";
        $result = $this->runCsv($csv);

        $this->assertCount(0, $result->transactions);
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('both debit and credit non-zero', $result->errors[0]->message);
    }

    public function test_duplicate_line_hash_within_job_is_flagged(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n"
            . "2026/05/15,100000,,振込,5025000\n"
            . "2026/05/15,100000,,振込,9999999\n"; // same date/amount/description → dup

        $result = $this->runCsv($csv);

        $this->assertCount(1, $result->transactions);
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('duplicate line hash', $result->errors[0]->message);
    }

    public function test_optional_field_blank_is_tolerated(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n2026/05/15,100000,,振込,\n";
        $result = $this->runCsv($csv);

        $this->assertCount(1, $result->transactions);
        $this->assertNull($result->transactions[0]->balanceCents);
    }

    public function test_value_date_defaults_to_transaction_date(): void
    {
        $csv = "日付,入金,出金,摘要,残高\n2026/05/15,100000,,振込,5025000\n";
        $result = $this->runCsv($csv);

        $this->assertSame('2026-05-15', $result->transactions[0]->valueDate);
    }
}
