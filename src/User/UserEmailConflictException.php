<?php

declare(strict_types=1);

namespace NeneProfile\User;

use RuntimeException;

final class UserEmailConflictException extends RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct("Email already in use: {$email}");
    }
}
