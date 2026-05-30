<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

use LogicException;

/**
 * The result of a single transform: either a successful value or an error
 * message. This type makes silent failure impossible — a transformer cannot
 * "guess" a value (ADR 0003): it must return either a definite value or an
 * explicit error that the import job records in import_job_errors.
 */
final readonly class TransformOutcome
{
    private function __construct(
        public bool $ok,
        public string|int|null $value,
        public ?string $error,
    ) {
    }

    public static function ok(string|int $value): self
    {
        return new self(true, $value, null);
    }

    public static function error(string $message): self
    {
        return new self(false, null, $message);
    }

    public function valueOrFail(): string|int
    {
        if (!$this->ok || $this->value === null) {
            throw new LogicException('Cannot read value from a failed transform outcome.');
        }

        return $this->value;
    }
}
