<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Extracts a capture group from a source value using a configured pattern.
 *
 * Unlike the other transformers, this one is parameterized: the pattern and
 * group are bound when the transformer is constructed (from the preset column
 * spec), so the registry builds a fresh instance per column.
 */
final readonly class RegexExtractTransformer implements TransformerInterface
{
    public function __construct(
        private string $pattern,
        private int $group = 0,
    ) {
    }

    public function transform(string|array $source, TransformContext $context): TransformOutcome
    {
        if (is_array($source)) {
            return TransformOutcome::error('regex_extract expects a single source column.');
        }

        $result = @preg_match($this->pattern, $source, $matches);

        if ($result === false) {
            return TransformOutcome::error('invalid regex pattern');
        }

        if ($result === 0 || !array_key_exists($this->group, $matches)) {
            return TransformOutcome::error("pattern did not match group {$this->group}");
        }

        return TransformOutcome::ok($matches[$this->group]);
    }
}
