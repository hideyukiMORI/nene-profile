<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\CsvParseException;
use NeneProfile\ImportJob\CsvParser;
use NeneProfile\Preset\MappingDefinitionFactory;
use PHPUnit\Framework\TestCase;

final class CsvParserTest extends TestCase
{
    private function definition(string $encoding = 'auto', string $delimiter = 'comma', int $headerRow = 0): \NeneProfile\Preset\MappingDefinition
    {
        return MappingDefinitionFactory::fromArray([
            'encoding'         => $encoding,
            'delimiter'        => $delimiter,
            'header_row_index' => $headerRow,
            'columns'          => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => '金額', 'transform' => 'amount_yen_to_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ]);
    }

    public function test_parses_utf8_comma_csv(): void
    {
        $csv = "日付,金額,摘要\n2026/05/15,100000,振込\n2026/05/16,-2000,引落\n";
        $parsed = (new CsvParser())->parse($csv, $this->definition());

        $this->assertSame(['日付', '金額', '摘要'], $parsed->header);
        $this->assertCount(2, $parsed->rows);
        $this->assertSame('振込', $parsed->rows[0]['cells']['摘要']);
        $this->assertSame(2, $parsed->rows[0]['rowNumber']); // line 2 (1-based)
        $this->assertSame('utf-8', $parsed->detectedEncoding);
    }

    public function test_strips_utf8_bom(): void
    {
        $csv = "\xEF\xBB\xBF日付,金額,摘要\n2026/05/15,100000,振込\n";
        $parsed = (new CsvParser())->parse($csv, $this->definition());

        $this->assertSame('日付', $parsed->header[0]);
    }

    public function test_decodes_shift_jis(): void
    {
        $utf8 = "日付,金額,摘要\n2026/05/15,100000,振込\n";
        $sjis = mb_convert_encoding($utf8, 'SJIS-win', 'UTF-8');
        $parsed = (new CsvParser())->parse($sjis, $this->definition('auto'));

        $this->assertSame('shift_jis', $parsed->detectedEncoding);
        $this->assertSame('振込', $parsed->rows[0]['cells']['摘要']);
    }

    public function test_detects_tab_delimiter(): void
    {
        $csv = "日付\t金額\t摘要\n2026/05/15\t100000\t振込\n";
        $parsed = (new CsvParser())->parse($csv, $this->definition('auto', 'auto'));

        $this->assertSame('tab', $parsed->detectedDelimiter);
        $this->assertSame('振込', $parsed->rows[0]['cells']['摘要']);
    }

    public function test_skips_preamble_with_header_row_index(): void
    {
        $csv = "ダミー口座明細\n\n日付,金額,摘要\n2026/05/15,100000,振込\n";
        $parsed = (new CsvParser())->parse($csv, $this->definition('auto', 'comma', 2));

        $this->assertSame(['日付', '金額', '摘要'], $parsed->header);
        $this->assertCount(1, $parsed->rows);
    }

    public function test_empty_file_throws(): void
    {
        $this->expectException(CsvParseException::class);
        (new CsvParser())->parse('', $this->definition());
    }
}
