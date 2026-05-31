<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Transformer;

use NeneProfile\Transformer\AmountYenToCentsTransformer;
use NeneProfile\Transformer\DebitCreditToSignedCentsTransformer;
use NeneProfile\Transformer\SingleColumnSignedCentsTransformer;
use NeneProfile\Transformer\TransformContext;
use PHPUnit\Framework\TestCase;

final class AmountTransformerBoundaryTest extends TestCase
{
    private TransformContext $ctx;

    protected function setUp(): void
    {
        $this->ctx = new TransformContext();
    }

    // ── AmountYenToCentsTransformer boundaries ────────────────────────────

    public function test_amount_yen_to_cents_zero_is_valid(): void
    {
        // Zero is a parseable integer — not blank — so it succeeds.
        $out = (new AmountYenToCentsTransformer())->transform('0', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(0, $out->value);
    }

    public function test_amount_yen_to_cents_one_yen(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('1', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(1, $out->value);
    }

    public function test_amount_yen_to_cents_large_amount(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('999999999', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(999999999, $out->value);
    }

    public function test_amount_yen_to_cents_with_commas_and_yen(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('¥1,234,567', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(1234567, $out->value);
    }

    public function test_amount_yen_to_cents_fullwidth_digits(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('１２３４５', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(12345, $out->value);
    }

    public function test_amount_yen_to_cents_empty_string_is_error(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('', $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_amount_yen_to_cents_whitespace_only_is_error(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('   ', $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_amount_yen_to_cents_decimal_is_error(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('100.50', $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_amount_yen_to_cents_non_numeric_is_error(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform('abc', $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_amount_yen_to_cents_rejects_multi_source(): void
    {
        $out = (new AmountYenToCentsTransformer())->transform(['1000', '0'], $this->ctx);

        $this->assertFalse($out->ok);
    }

    // ── DebitCreditToSignedCentsTransformer boundaries ────────────────────

    public function test_debit_credit_both_zero_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['0', '0'], $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_debit_credit_large_deposit(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['99999999', ''], $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(99999999, $out->value);
    }

    public function test_debit_credit_large_withdrawal(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['', '99999999'], $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(-99999999, $out->value);
    }

    public function test_debit_credit_requires_two_source_columns(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform('1000', $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_debit_credit_three_source_columns_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['1000', '0', 'extra'], $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_debit_credit_non_numeric_debit_is_error(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['abc', ''], $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_debit_credit_commas_in_amount_parsed(): void
    {
        $out = (new DebitCreditToSignedCentsTransformer())->transform(['1,234,567', ''], $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(1234567, $out->value);
    }

    // ── SingleColumnSignedCentsTransformer boundaries ─────────────────────

    public function test_single_column_positive_integer(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('5000', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(5000, $out->value);
    }

    public function test_single_column_leading_minus_negative(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('-3000', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(-3000, $out->value);
    }

    public function test_single_column_dr_suffix_lowercase(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('1000dr', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(-1000, $out->value);
    }

    public function test_single_column_cr_suffix_uppercase(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('2000CR', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(2000, $out->value);
    }

    public function test_single_column_blank_is_error(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('', $this->ctx);

        $this->assertFalse($out->ok);
    }

    public function test_single_column_zero_is_valid(): void
    {
        // '0' is not blank so it parses successfully to 0.
        $out = (new SingleColumnSignedCentsTransformer())->transform('0', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(0, $out->value);
    }

    public function test_single_column_large_positive(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('9999999', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(9999999, $out->value);
    }

    public function test_single_column_with_commas(): void
    {
        $out = (new SingleColumnSignedCentsTransformer())->transform('1,000,000', $this->ctx);

        $this->assertTrue($out->ok);
        $this->assertSame(1000000, $out->value);
    }
}
