<?php

declare(strict_types=1);

namespace NeneProfile\User;

use RuntimeException;

final class UserNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("User not found: {$id}");
    }
}
