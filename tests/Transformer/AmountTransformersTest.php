<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Transformer;

use NeneProfile\Transformer\AmountYenToCentsTransformer;
use NeneProfile\Transformer\DebitCreditToSignedCentsTransformer;
use NeneProfile\Transformer\SingleColumnSignedCentsTransformer;
use NeneProfile\Transformer\TransformContext;
use NeneProfile\Transformer\YenAmountParser;
use PHPUnit\Framework\TestCase;

final class AmountTransformersTest extends TestCase
{
    private TransformContext $ctx;

    protected function setUp(): void
    {
        $this->ctx = new TransformContext();
    }

    // ── YenAmountParser ─────────────────────────────────────────────────────

    public function test_parses_plain_integer(): void
    {
        $this->assertSame(150000, YenAmountParser::parse('150000'));
    }

    public function test_parses_with_separators_and_symbol(): void
    {
        $this->assertSame(150000, YenAmountParser::parse('¥150,000'));
        $this->assertSame(150000, YenAmountParser::parse('150,000円'));
    }

    public function test_parses_full_width_digits(): void
    {
        $this->assertSame(12345, YenAmountParser::parse('１２，３４５'));
    }

    public function test_rejects_decimal(): void
    {
        $this->assertNull(YenAmountParser::parse('100.5'));
    }

    public function test_blank_detection(): void
    {
        $this->assertTrue(YenAmountParser::isBlank(''));
        $this->assertTrue(YenAmountParser::isBlank('  '));
        $this->assertFalse(YenAmountParser::isBlank('0'));
    }

    // ── amount_yen_to_cents ─────────────────────────────────────────────────

    public function test_amount_yen_to_cents(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('5,025,000', $this->ctx);
        $this->assertSame(5025000, $out->value);
    }

    public function test_amount_yen_to_cents_blank_is_error(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('', $this->ctx);
        $this->assertFalse($out->ok);
    }

    // ── debit_credit_to_signed_cents (ADR 0003 §1) ──────────────────────────

    public function test_deposit_is_positive(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['100000', ''], $this->ctx);
        $this->assertSame(100000, $out->value);
    }

    public function test_withdrawal_is_negative(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['', '30000'], $this->ctx);
        $this->assertSame(-30000, $out->value);
    }

    public function test_withdrawal_written_positive_still_negative(): void
    {
        // Bank writes withdrawal as a positive figure; convention forces negative.
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['0', '30000'], $this->ctx);
        $this->assertSame(-30000, $out->value);
    }

    public function test_both_non_zero_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['100', '200'], $this->ctx);
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('both debit and credit non-zero', (string) $out->error);
    }

    public function test_both_blank_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['', ''], $this->ctx);
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('no amount', (string) $out->error);
    }

    public function test_both_zero_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['0', '0'], $this->ctx);
        $this->assertFalse($out->ok);
    }

    public function test_unparseable_column_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['abc', ''], $this->ctx);
        $this->assertFalse($out->ok);
    }

    public function test_requires_two_columns(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform('100000', $this->ctx);
        $this->assertFalse($out->ok);
    }

    // ── single_column_signed_cents ──────────────────────────────────────────

    public function test_single_leading_minus_negative(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('-30000', $this->ctx);
        $this->assertSame(-30000, $out->value);
    }

    public function test_single_bare_positive(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('100000', $this->ctx);
        $this->assertSame(100000, $out->value);
    }

    public function test_single_dr_suffix_negative(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('30000 DR', $this->ctx);
        $this->assertSame(-30000, $out->value);
    }

    public function test_single_cr_suffix_positive(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('100000 CR', $this->ctx);
        $this->assertSame(100000, $out->value);
    }

    public function test_single_blank_is_error(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('', $this->ctx);
        $this->assertFalse($out->ok);
    }

    public function test_single_unparseable_is_error(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('abc DR', $this->ctx);
        $this->assertFalse($out->ok);
    }
}
