<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

final readonly class TrimTransformer implements TransformerInterface
{
    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('trim expects a single source column.');
        }

        // Trim ASCII and common Unicode whitespace (incl. ideographic space).
        $trimmed = preg_replace('/^[\s\x{3000}]+|[\s\x{3000}]+$/u', '', $source);

        return TransformOutcome::ok($trimmed ?? $source);
    }
}
