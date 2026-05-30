<?php

declare(strict_types=1);

namespace NeneProfile\User;

final readonly class UpdateUserInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
        public ?string $role = null,
        public ?string $status = null,
        public ?string $password = null,
    ) {
    }
}
