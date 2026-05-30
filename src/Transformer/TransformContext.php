<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Per-row context passed to every transformer. Carries the preset's year pivot
 * (ADR 0003 §2) and the 1-based source row number for error reporting.
 */
final readonly class TransformContext
{
    public function __construct(
        public int $yearPivot = 50,
        public int $rawRowNumber = 0,
    ) {
    }
}
