<?php

declare(strict_types=1);

namespace NeneProfile\User;

final readonly class GetUserByIdInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
    ) {
    }
}
