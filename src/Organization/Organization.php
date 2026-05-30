<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class Organization
{
    public function __construct(
        public string $name,
        public string $slug,
        public bool $isActive,
        public ?int $id = null,
        public ?string $customDomain = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
