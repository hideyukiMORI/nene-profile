<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Support;

use DateTimeImmutable;
use Nene2\Http\ClockInterface;

/**
 * Deterministic clock for tests: makes recorded `occurred_at` timestamps
 * assertable instead of drifting with wall-clock time.
 */
final readonly class FixedClock implements ClockInterface
{
    private DateTimeImmutable $now;

    public function __construct(string $iso = '2026-07-05T00:00:00+00:00')
    {
        $this->now = new DateTimeImmutable($iso);
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
