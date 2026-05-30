<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use NeneProfile\Preset\InvalidMappingDefinitionException;
use NeneProfile\Preset\MappingDefinitionFactory;
use PHPUnit\Framework\TestCase;

final class MappingDefinitionFactoryTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function validRaw(): array
    {
        return [
            'encoding'         => 'auto',
            'delimiter'        => 'comma',
            'header_row_index' => 0,
            'year_pivot'       => 50,
            'columns'          => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金金額', '出金金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ];
    }

    public function test_parses_valid_definition(): void
    {
        $def = MappingDefinitionFactory::fromArray(self::validRaw());

        $this->assertSame('auto', $def->encoding);
        $this->assertSame('comma', $def->delimiter);
        $this->assertSame(50, $def->yearPivot);
        $this->assertArrayHasKey('transaction_date', $def->columns);
        $this->assertSame('debit_credit_to_signed_cents', $def->columns['amount_cents']->transform);
    }

    public function test_round_trips_through_to_array(): void
    {
        $def = MappingDefinitionFactory::fromArray(self::validRaw());
        $again = MappingDefinitionFactory::fromArray($def->toArray());

        $this->assertEquals($def->toArray(), $again->toArray());
    }

    public function test_rejects_missing_required_field(): void
    {
        $raw = self::validRaw();
        unset($raw['columns']['amount_cents']);

        try {
            MappingDefinitionFactory::fromArray($raw);
            $this->fail('Expected InvalidMappingDefinitionException');
        } catch (InvalidMappingDefinitionException $e) {
            $fields = array_column($e->errors, 'field');
            $this->assertContains('columns.amount_cents', $fields);
        }
    }

    public function test_rejects_unknown_transformer(): void
    {
        $raw = self::validRaw();
        $raw['columns']['description']['transform'] = 'magic_transform';

        try {
            MappingDefinitionFactory::fromArray($raw);
            $this->fail('Expected InvalidMappingDefinitionException');
        } catch (InvalidMappingDefinitionException $e) {
            $codes = array_column($e->errors, 'code');
            $this->assertContains('unsupported_transformer', $codes);
        }
    }

    public function test_rejects_unknown_logical_field(): void
    {
        $raw = self::validRaw();
        $raw['columns']['favourite_colour'] = ['source' => 'x', 'transform' => 'trim'];

        $this->expectException(InvalidMappingDefinitionException::class);
        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_rejects_invalid_encoding(): void
    {
        $raw = self::validRaw();
        $raw['encoding'] = 'euc-jp';

        $this->expectException(InvalidMappingDefinitionException::class);
        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_rejects_year_pivot_out_of_range(): void
    {
        $raw = self::validRaw();
        $raw['year_pivot'] = 150;

        $this->expectException(InvalidMappingDefinitionException::class);
        MappingDefinitionFactory::fromArray($raw);
    }

    public function test_defaults_line_identity_when_absent(): void
    {
        $def = MappingDefinitionFactory::fromArray(self::validRaw());

        $this->assertSame(['transaction_date', 'amount_cents', 'description'], $def->lineIdentity);
    }
}
