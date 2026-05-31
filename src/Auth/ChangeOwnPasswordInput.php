<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

final readonly class ChangeOwnPasswordInput
{
    public function __construct(
        public int $actorUserId,
        public string $currentPassword,
        public string $newPassword,
    ) {
    }
}
