<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Transformer;

use NeneProfile\Transformer\DateYmdCompactTransformer;
use NeneProfile\Transformer\DateYmdDashTransformer;
use NeneProfile\Transformer\DateYmdSlashTransformer;
use NeneProfile\Transformer\TransformContext;
use PHPUnit\Framework\TestCase;

final class DateTransformersTest extends TestCase
{
    public function test_slash_full_year(): void
    {
        $out = (new DateYmdSlashTransformer())->transform('2026/05/15', new TransformContext());
        $this->assertTrue($out->ok);
        $this->assertSame('2026-05-15', $out->value);
    }

    public function test_slash_single_digit_month_day(): void
    {
        $out = (new DateYmdSlashTransformer())->transform('2026/5/1', new TransformContext());
        $this->assertSame('2026-05-01', $out->value);
    }

    public function test_slash_two_digit_year_below_pivot_is_2000s(): void
    {
        // pivot 50: 26 → 2026
        $out = (new DateYmdSlashTransformer())->transform('26/05/15', new TransformContext(yearPivot: 50));
        $this->assertSame('2026-05-15', $out->value);
    }

    public function test_slash_two_digit_year_at_or_above_pivot_is_1900s(): void
    {
        // pivot 50: 50 → 1950
        $out = (new DateYmdSlashTransformer())->transform('50/05/15', new TransformContext(yearPivot: 50));
        $this->assertSame('1950-05-15', $out->value);
    }

    public function test_slash_pivot_boundary_is_configurable(): void
    {
        // pivot 30: 28 → 2028, 35 → 1935
        $t = new DateYmdSlashTransformer();
        $this->assertSame('2028-01-01', $t->transform('28/1/1', new TransformContext(yearPivot: 30))->value);
        $this->assertSame('1935-01-01', $t->transform('35/1/1', new TransformContext(yearPivot: 30))->value);
    }

    public function test_slash_rejects_invalid_calendar_date(): void
    {
        $out = (new DateYmdSlashTransformer())->transform('2026/13/45', new TransformContext());
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('invalid calendar date', (string) $out->error);
    }

    public function test_slash_rejects_unparseable(): void
    {
        $out = (new DateYmdSlashTransformer())->transform('not-a-date', new TransformContext());
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('unparseable', (string) $out->error);
    }

    public function test_slash_rejects_february_30(): void
    {
        $out = (new DateYmdSlashTransformer())->transform('2026/02/30', new TransformContext());
        $this->assertFalse($out->ok);
    }

    public function test_dash_full_year(): void
    {
        $out = (new DateYmdDashTransformer())->transform('2026-05-15', new TransformContext());
        $this->assertSame('2026-05-15', $out->value);
    }

    public function test_dash_rejects_slash_input(): void
    {
        $out = (new DateYmdDashTransformer())->transform('2026/05/15', new TransformContext());
        $this->assertFalse($out->ok);
    }

    public function test_compact_eight_digits(): void
    {
        $out = (new DateYmdCompactTransformer())->transform('20260515', new TransformContext());
        $this->assertSame('2026-05-15', $out->value);
    }

    public function test_compact_rejects_wrong_length(): void
    {
        $out = (new DateYmdCompactTransformer())->transform('260515', new TransformContext());
        $this->assertFalse($out->ok);
    }

    public function test_leap_day_valid(): void
    {
        $out = (new DateYmdDashTransformer())->transform('2024-02-29', new TransformContext());
        $this->assertSame('2024-02-29', $out->value);
    }

    public function test_non_leap_day_invalid(): void
    {
        $out = (new DateYmdDashTransformer())->transform('2026-02-29', new TransformContext());
        $this->assertFalse($out->ok);
    }
}
