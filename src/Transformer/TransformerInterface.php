<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

interface TransformerInterface
{
    /**
     * @param string|list<string> $source raw source value(s) from the CSV row
     */
    public function transform(string|array $source, TransformContext $context): TransformOutcome;
}
