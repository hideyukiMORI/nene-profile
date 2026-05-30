<?php

declare(strict_types=1);

namespace NeneProfile\User;

final readonly class DeleteUserInput
{
    public function __construct(
        public int $id,
        public int $organizationId,
    ) {
    }
}
