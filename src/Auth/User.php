<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $email,
        public string $passwordHash,
        public string $role,
        public ?int $organizationId = null,
        public string $status = 'active',
        public ?int $createdAt = null,
        public ?int $updatedAt = null,
    ) {
    }
}
