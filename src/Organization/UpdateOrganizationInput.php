<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class UpdateOrganizationInput
{
    public function __construct(
        public int $id,
        public ?string $name = null,
        public ?string $slug = null,
        public ?bool $isActive = null,
        public ?string $customDomain = null,
        public bool $customDomainProvided = false,
    ) {
    }
}
