<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class CreateOrganizationInput
{
    public function __construct(
        public string $name,
        public string $slug,
        public bool $isActive = true,
        public ?string $customDomain = null,
    ) {
    }
}
