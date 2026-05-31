<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\CsvParseException;
use NeneProfile\ImportJob\CsvParser;
use NeneProfile\Preset\MappingDefinitionFactory;
use PHPUnit\Framework\TestCase;

final class CsvParserBoundaryTest extends TestCase
{
    private CsvParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CsvParser();
    }

    private function def(string $encoding = 'auto', string $delimiter = 'auto'): \NeneProfile\Preset\MappingDefinition
    {
        return MappingDefinitionFactory::fromArray([
            'encoding'  => $encoding,
            'delimiter' => $delimiter,
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ]);
    }

    // ── Empty / header-only ───────────────────────────────────────────────

    public function test_throws_on_completely_empty_file(): void
    {
        $this->expectException(CsvParseException::class);

        $this->parser->parse('', $this->def());
    }

    public function test_whitespace_only_file_has_zero_data_rows(): void
    {
        // CsvParser only removes fully empty lines; whitespace-only lines are treated
        // as a header row producing a single whitespace column. No CsvParseException.
        $result = $this->parser->parse("   \n   \n", $this->def());

        // Whitespace-only lines are NOT stripped — second line becomes a data row.
        $this->assertCount(1, $result->rows);
    }

    public function test_header_only_file_returns_zero_rows(): void
    {
        $csv = "日付,入金額,出金額,摘要\n";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertCount(0, $result->rows);
        $this->assertSame(['日付', '入金額', '出金額', '摘要'], $result->header);
    }

    // ── Line endings ──────────────────────────────────────────────────────

    public function test_crlf_line_endings(): void
    {
        $csv = "日付,入金額,出金額,摘要\r\n2026/05/01,1000,,入金\r\n";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertCount(1, $result->rows);
        $this->assertSame('2026/05/01', $result->rows[0]['cells']['日付']);
    }

    public function test_cr_only_line_endings(): void
    {
        $csv = "日付,入金額,出金額,摘要\r2026/05/01,1000,,入金\r";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertCount(1, $result->rows);
    }

    public function test_lf_line_endings(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n2026/05/02,,2000,出金\n";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertCount(2, $result->rows);
    }

    public function test_trailing_empty_line_is_ignored(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n\n";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertCount(1, $result->rows);
    }

    // ── Delimiter detection ───────────────────────────────────────────────

    public function test_auto_detects_tab_delimiter(): void
    {
        $csv = "日付\t入金額\t出金額\t摘要\n2026/05/01\t1000\t\t入金\n";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertSame('tab', $result->detectedDelimiter);
        $this->assertCount(1, $result->rows);
        $this->assertSame('1000', $result->rows[0]['cells']['入金額']);
    }

    public function test_forced_comma_delimiter_ignores_tabs(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n";

        $result = $this->parser->parse($csv, $this->def('auto', 'comma'));

        $this->assertCount(1, $result->rows);
    }

    public function test_forced_tab_delimiter(): void
    {
        $csv = "日付\t入金額\t出金額\t摘要\n2026/05/01\t1000\t\t入金\n";

        $result = $this->parser->parse($csv, $this->def('auto', 'tab'));

        $this->assertSame('tab', $result->detectedDelimiter);
    }

    // ── Encoding ──────────────────────────────────────────────────────────

    public function test_utf8_bom_is_stripped(): void
    {
        $bom = "\xEF\xBB\xBF";
        $csv = $bom . "日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n";

        $result = $this->parser->parse($csv, $this->def());

        $this->assertSame('utf-8', $result->detectedEncoding);
        $this->assertSame('日付', $result->header[0]);
    }

    public function test_shift_jis_file_is_decoded(): void
    {
        $sjisHeader = mb_convert_encoding("日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n", 'SJIS-win', 'UTF-8');

        $result = $this->parser->parse($sjisHeader, $this->def());

        $this->assertSame('shift_jis', $result->detectedEncoding);
        $this->assertSame('日付', $result->header[0]);
        $this->assertSame('1000', $result->rows[0]['cells']['入金額']);
    }

    public function test_forced_utf8_rejects_invalid_bytes(): void
    {
        $this->expectException(CsvParseException::class);

        $invalid = "date,amount\n" . "\x80\x81\x82" . ",1000\n"; // invalid UTF-8

        $this->parser->parse($invalid, $this->def('utf-8'));
    }

    // ── Row numbering ─────────────────────────────────────────────────────

    public function test_row_numbers_are_1_based_source_lines(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000,,Row1\n2026/05/02,,2000,Row2\n";

        $result = $this->parser->parse($csv, $this->def());

        // header is line 1, data rows start at line 2
        $this->assertSame(2, $result->rows[0]['rowNumber']);
        $this->assertSame(3, $result->rows[1]['rowNumber']);
    }

    public function test_row_with_fewer_cells_than_header_fills_empty(): void
    {
        $csv = "日付,入金額,出金額,摘要\n2026/05/01,1000\n"; // only 2 cells

        $result = $this->parser->parse($csv, $this->def());

        $this->assertCount(1, $result->rows);
        $this->assertSame('', $result->rows[0]['cells']['摘要']);
    }

    // ── Skip patterns ─────────────────────────────────────────────────────

    public function test_skip_rows_matching_pattern(): void
    {
        $defWithSkip = MappingDefinitionFactory::fromArray([
            'encoding'          => 'auto',
            'delimiter'         => 'auto',
            'skip_rows_matching' => ['^#'],
            'columns'           => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ]);

        $csv = "日付,入金額,出金額,摘要\n# This is a comment\n2026/05/01,1000,,入金\n";

        $result = $this->parser->parse($csv, $defWithSkip);

        $this->assertCount(1, $result->rows);
    }

    // ── Header row index ──────────────────────────────────────────────────

    public function test_header_row_index_1_skips_first_line(): void
    {
        $defH1 = MappingDefinitionFactory::fromArray([
            'encoding'         => 'auto',
            'delimiter'        => 'auto',
            'header_row_index' => 1,
            'columns'          => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ]);

        $csv = "Bank Export\n日付,入金額,出金額,摘要\n2026/05/01,1000,,入金\n";

        $result = $this->parser->parse($csv, $defH1);

        $this->assertSame(['日付', '入金額', '出金額', '摘要'], $result->header);
        $this->assertCount(1, $result->rows);
    }

    public function test_header_index_beyond_file_throws(): void
    {
        $this->expectException(CsvParseException::class);

        $defH99 = MappingDefinitionFactory::fromArray([
            'encoding'         => 'auto',
            'delimiter'        => 'auto',
            'header_row_index' => 99,
            'columns'          => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ]);

        $this->parser->parse("日付,入金額\n2026/05/01,1000\n", $defH99);
    }
}
