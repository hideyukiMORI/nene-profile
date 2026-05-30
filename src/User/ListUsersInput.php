<?php

declare(strict_types=1);

namespace NeneProfile\User;

final readonly class ListUsersInput
{
    public function __construct(
        public int $organizationId,
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
