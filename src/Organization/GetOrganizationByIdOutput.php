<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

final readonly class GetOrganizationByIdOutput
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public bool $isActive,
        public ?string $customDomain,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
