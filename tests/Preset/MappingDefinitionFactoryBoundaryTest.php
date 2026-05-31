<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use NeneProfile\Preset\InvalidMappingDefinitionException;
use NeneProfile\Preset\MappingDefinitionFactory;
use PHPUnit\Framework\TestCase;

final class MappingDefinitionFactoryBoundaryTest extends TestCase
{
    /** @return array<string, mixed> */
    private function base(): array
    {
        return [
            'encoding'  => 'auto',
            'delimiter' => 'auto',
            'columns'   => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ];
    }

    // ── year_pivot boundaries ─────────────────────────────────────────────

    public function test_year_pivot_0_is_valid(): void
    {
        $def = MappingDefinitionFactory::fromArray(array_merge($this->base(), ['year_pivot' => 0]));

        $this->assertSame(0, $def->yearPivot);
    }

    public function test_year_pivot_99_is_valid(): void
    {
        $def = MappingDefinitionFactory::fromArray(array_merge($this->base(), ['year_pivot' => 99]));

        $this->assertSame(99, $def->yearPivot);
    }

    public function test_year_pivot_50_default_when_absent(): void
    {
        $raw = $this->base();
        unset($raw['year_pivot']);

        $def = MappingDefinitionFactory::fromArray($raw);

        $this->assertSame(50, $def->yearPivot);
    }

    public function test_year_pivot_100_is_invalid(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        MappingDefinitionFactory::fromArray(array_merge($this->base(), ['year_pivot' => 100]));
    }

    public function test_year_pivot_negative_1_is_invalid(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        MappingDefinitionFactory::fromArray(array_merge($this->base(), ['year_pivot' => -1]));
    }

    // ── header_row_index boundaries ───────────────────────────────────────

    public function test_header_row_index_0_is_valid(): void
    {
        $def = MappingDefinitionFactory::fromArray(array_merge($this->base(), ['header_row_index' => 0]));

        $this->assertSame(0, $def->headerRowIndex);
    }

    public function test_header_row_index_negative_is_invalid(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        MappingDefinitionFactory::fromArray(array_merge($this->base(), ['header_row_index' => -1]));
    }

    // ── Encoding/delimiter validation ─────────────────────────────────────

    public function test_all_valid_encodings_accepted(): void
    {
        foreach (['auto', 'utf-8', 'shift_jis'] as $enc) {
            $def = MappingDefinitionFactory::fromArray(array_merge($this->base(), ['encoding' => $enc]));
            $this->assertSame($enc, $def->encoding);
        }
    }

    public function test_unsupported_encoding_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        MappingDefinitionFactory::fromArray(array_merge($this->base(), ['encoding' => 'euc-jp']));
    }

    public function test_all_valid_delimiters_accepted(): void
    {
        foreach (['auto', 'comma', 'tab'] as $delim) {
            $def = MappingDefinitionFactory::fromArray(array_merge($this->base(), ['delimiter' => $delim]));
            $this->assertSame($delim, $def->delimiter);
        }
    }

    public function test_unknown_delimiter_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        MappingDefinitionFactory::fromArray(array_merge($this->base(), ['delimiter' => 'pipe']));
    }

    // ── Column validation ─────────────────────────────────────────────────

    public function test_unknown_logical_field_is_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        $raw = $this->base();
        $raw['columns']['flying_monkeys'] = ['source' => '何か', 'transform' => 'trim'];

        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_all_transformers_accepted_in_columns(): void
    {
        foreach (['trim', 'date_ymd_slash', 'date_ymd_dash', 'date_ymd_compact', 'date_era', 'amount_yen_to_cents', 'regex_extract'] as $transform) {
            $raw = $this->base();
            $raw['columns']['description'] = ['source' => '摘要', 'transform' => $transform];

            $def = MappingDefinitionFactory::fromArray($raw);
            $this->assertSame($transform, $def->columns['description']->transform);
        }
    }

    public function test_unknown_transformer_is_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        $raw = $this->base();
        $raw['columns']['description'] = ['source' => '摘要', 'transform' => 'nonexistent_transformer'];

        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_missing_required_field_transaction_date_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        $raw = $this->base();
        unset($raw['columns']['transaction_date']);

        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_missing_required_field_amount_cents_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        $raw = $this->base();
        unset($raw['columns']['amount_cents']);

        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_missing_required_field_description_rejected(): void
    {
        $this->expectException(InvalidMappingDefinitionException::class);

        $raw = $this->base();
        unset($raw['columns']['description']);

        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_optional_field_counterparty_accepted(): void
    {
        $raw = $this->base();
        $raw['columns']['counterparty'] = ['source' => '相手先', 'transform' => 'trim', 'optional' => true];

        $def = MappingDefinitionFactory::fromArray($raw);

        $this->assertTrue($def->columns['counterparty']->optional);
    }

    public function test_multi_source_column_accepted(): void
    {
        $def = MappingDefinitionFactory::fromArray($this->base());

        $this->assertIsArray($def->columns['amount_cents']->source);
    }

    // ── line_identity defaults ────────────────────────────────────────────

    public function test_line_identity_defaults_to_date_amount_description(): void
    {
        $raw = $this->base();
        unset($raw['line_identity']);

        $def = MappingDefinitionFactory::fromArray($raw);

        $this->assertSame(['transaction_date', 'amount_cents', 'description'], $def->lineIdentity);
    }

    public function test_custom_line_identity_accepted(): void
    {
        $raw = array_merge($this->base(), [
            'line_identity' => ['transaction_date', 'description'],
        ]);

        $def = MappingDefinitionFactory::fromArray($raw);

        $this->assertSame(['transaction_date', 'description'], $def->lineIdentity);
    }
}
