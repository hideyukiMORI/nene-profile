<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

/**
 * `definition` is optional: a metadata-only update (name/bank_label) does not
 * create a new version. Supplying a definition always appends a new immutable
 * version (ADR 0004).
 */
final readonly class UpdateMappingPresetInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
        public ?string $name = null,
        public ?string $bankLabel = null,
        public ?MappingDefinition $definition = null,
    ) {
    }
}
