<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Transformer;

use NeneProfile\Transformer\DateEraTransformer;
use NeneProfile\Transformer\JapaneseEraDateParser;
use NeneProfile\Transformer\TransformContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests for JapaneseEraDateParser and DateEraTransformer.
 * Coverage mirrors the error cases specified in ADR 0003 §3.
 */
final class JapaneseEraDateParserTest extends TestCase
{
    // ── Happy paths: each era, various formats ─────────────────────────────

    public function test_reiwa_ascii_prefix(): void
    {
        $this->assertOk('2026-05-15', JapaneseEraDateParser::toIso('R', '8', '5', '15'));
    }

    public function test_reiwa_lowercase_ascii_prefix(): void
    {
        $this->assertOk('2026-05-15', JapaneseEraDateParser::toIso('r', '8', '5', '15'));
    }

    public function test_reiwa_single_kanji_prefix(): void
    {
        $this->assertOk('2026-05-15', JapaneseEraDateParser::toIso('令', '8', '5', '15'));
    }

    public function test_reiwa_full_name_prefix(): void
    {
        $this->assertOk('2026-05-15', JapaneseEraDateParser::toIso('令和', '8', '5', '15'));
    }

    public function test_heisei_standard(): void
    {
        $this->assertOk('2000-04-01', JapaneseEraDateParser::toIso('H', '12', '4', '1'));
    }

    public function test_heisei_kanji_prefix(): void
    {
        $this->assertOk('2000-04-01', JapaneseEraDateParser::toIso('平', '12', '4', '1'));
    }

    public function test_heisei_full_name_prefix(): void
    {
        $this->assertOk('2000-04-01', JapaneseEraDateParser::toIso('平成', '12', '4', '1'));
    }

    public function test_showa_standard(): void
    {
        $this->assertOk('1989-01-07', JapaneseEraDateParser::toIso('S', '64', '1', '7'));
    }

    public function test_showa_kanji_prefix(): void
    {
        $this->assertOk('1975-06-10', JapaneseEraDateParser::toIso('昭', '50', '6', '10'));
    }

    public function test_taisho_standard(): void
    {
        $this->assertOk('1920-03-05', JapaneseEraDateParser::toIso('T', '9', '3', '5'));
    }

    public function test_taisho_kanji_prefix(): void
    {
        $this->assertOk('1912-09-01', JapaneseEraDateParser::toIso('大', '1', '9', '1'));
    }

    // ── ADR 0003 §3 — Reiwa boundary ───────────────────────────────────────

    /** R1 May 1 is the first valid Reiwa date. */
    public function test_reiwa_year1_may_is_valid(): void
    {
        $this->assertOk('2019-05-01', JapaneseEraDateParser::toIso('R', '1', '5', '1'));
    }

    /** R1 Jan–Apr belong to Heisei 31 — must be an error. */
    public function test_reiwa_year1_january_is_error(): void
    {
        $this->assertError('Reiwa 1 January–April', JapaneseEraDateParser::toIso('R', '1', '1', '1'));
    }

    public function test_reiwa_year1_february_is_error(): void
    {
        $this->assertError('Reiwa 1 January–April', JapaneseEraDateParser::toIso('R', '1', '2', '14'));
    }

    public function test_reiwa_year1_march_is_error(): void
    {
        $this->assertError('Reiwa 1 January–April', JapaneseEraDateParser::toIso('R', '1', '3', '31'));
    }

    public function test_reiwa_year1_april_is_error(): void
    {
        $this->assertError('Reiwa 1 January–April', JapaneseEraDateParser::toIso('R', '1', '4', '30'));
    }

    // ── ADR 0003 §3 — Heisei boundary ──────────────────────────────────────

    /** H31.4.30 is the last valid Heisei date. */
    public function test_heisei_last_valid_date(): void
    {
        $this->assertOk('2019-04-30', JapaneseEraDateParser::toIso('H', '31', '4', '30'));
    }

    /** H31 May and later do not exist as Heisei. */
    public function test_heisei_year31_may_is_error(): void
    {
        $this->assertError('Heisei ended on H31.4.30', JapaneseEraDateParser::toIso('H', '31', '5', '1'));
    }

    public function test_heisei_year31_december_is_error(): void
    {
        $this->assertError('Heisei ended on H31.4.30', JapaneseEraDateParser::toIso('H', '31', '12', '31'));
    }

    // ── ADR 0003 §3 — Showa boundary ───────────────────────────────────────

    /** S64.1.7 is the last valid Showa date. */
    public function test_showa_last_valid_date(): void
    {
        $this->assertOk('1989-01-07', JapaneseEraDateParser::toIso('S', '64', '1', '7'));
    }

    /** S64.1.8 does not exist as Showa. */
    public function test_showa_year64_jan8_is_error(): void
    {
        $this->assertError('Showa 64 ended on S64.1.7', JapaneseEraDateParser::toIso('S', '64', '1', '8'));
    }

    public function test_showa_year64_february_is_error(): void
    {
        $this->assertError('Showa 64 ended on S64.1.7', JapaneseEraDateParser::toIso('S', '64', '2', '1'));
    }

    // ── ADR 0003 §3 — Era year exceeds maximum ─────────────────────────────

    public function test_reiwa_year_exceeds_maximum(): void
    {
        $this->assertError('out of valid range for Reiwa', JapaneseEraDateParser::toIso('R', '100', '1', '1'));
    }

    public function test_heisei_year_exceeds_maximum(): void
    {
        $this->assertError('out of valid range for Heisei', JapaneseEraDateParser::toIso('H', '32', '1', '1'));
    }

    public function test_showa_year_exceeds_maximum(): void
    {
        $this->assertError('out of valid range for Showa', JapaneseEraDateParser::toIso('S', '65', '1', '1'));
    }

    public function test_taisho_year_exceeds_maximum(): void
    {
        $this->assertError('out of valid range for Taisho', JapaneseEraDateParser::toIso('T', '16', '1', '1'));
    }

    public function test_year_zero_is_error(): void
    {
        $this->assertError('out of valid range', JapaneseEraDateParser::toIso('R', '0', '5', '1'));
    }

    // ── ADR 0003 §3 — Unrecognized era prefix ──────────────────────────────

    public function test_unrecognized_ascii_prefix_is_error(): void
    {
        $this->assertError("unrecognized era prefix 'M'", JapaneseEraDateParser::toIso('M', '6', '5', '15'));
    }

    public function test_unrecognized_kanji_prefix_is_error(): void
    {
        $this->assertError("unrecognized era prefix '明'", JapaneseEraDateParser::toIso('明', '45', '7', '30'));
    }

    // ── ADR 0003 §3 — Invalid calendar date ────────────────────────────────

    public function test_invalid_month_is_error(): void
    {
        $this->assertError('invalid calendar date', JapaneseEraDateParser::toIso('R', '6', '13', '1'));
    }

    public function test_invalid_day_is_error(): void
    {
        $this->assertError('invalid calendar date', JapaneseEraDateParser::toIso('R', '6', '2', '30'));
    }

    // ── DateEraTransformer: input format parsing ───────────────────────────

    public function test_transformer_dot_separator(): void
    {
        $out = (new DateEraTransformer())->transform('R6.05.15', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('2024-05-15', $out->value);
    }

    public function test_transformer_slash_separator(): void
    {
        $out = (new DateEraTransformer())->transform('H31/4/30', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('2019-04-30', $out->value);
    }

    public function test_transformer_dash_separator(): void
    {
        $out = (new DateEraTransformer())->transform('S64-1-7', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('1989-01-07', $out->value);
    }

    public function test_transformer_kanji_separators(): void
    {
        $out = (new DateEraTransformer())->transform('令和6年5月15日', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('2024-05-15', $out->value);
    }

    public function test_transformer_kanji_separators_without_trailing_nichi(): void
    {
        $out = (new DateEraTransformer())->transform('平成31年4月30', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('2019-04-30', $out->value);
    }

    public function test_transformer_full_era_name_with_dot(): void
    {
        $out = (new DateEraTransformer())->transform('昭和64.1.7', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('1989-01-07', $out->value);
    }

    public function test_transformer_leading_whitespace_trimmed(): void
    {
        $out = (new DateEraTransformer())->transform('  R6.05.15  ', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('2024-05-15', $out->value);
    }

    public function test_transformer_rejects_multi_source(): void
    {
        $out = (new DateEraTransformer())->transform(['R6.05.15', 'extra'], new TransformContext());
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('single source column', (string) $out->error);
    }

    public function test_transformer_rejects_gregorian_date(): void
    {
        $out = (new DateEraTransformer())->transform('2026/05/15', new TransformContext());
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('unparseable era date', (string) $out->error);
    }

    public function test_transformer_propagates_reiwa_boundary_error(): void
    {
        $out = (new DateEraTransformer())->transform('R1.3.15', new TransformContext());
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('Reiwa 1 January–April', (string) $out->error);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * @param array{0: 'ok'|'error', 1: string} $result
     */
    private function assertOk(string $expected, array $result): void
    {
        $this->assertSame('ok', $result[0], "Expected ok but got error: {$result[1]}");
        $this->assertSame($expected, $result[1]);
    }

    /**
     * @param array{0: 'ok'|'error', 1: string} $result
     */
    private function assertError(string $expectedFragment, array $result): void
    {
        $this->assertSame('error', $result[0], "Expected error but got ok: {$result[1]}");
        $this->assertStringContainsString($expectedFragment, $result[1]);
    }
}
