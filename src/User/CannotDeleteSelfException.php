<?php

declare(strict_types=1);

namespace NeneProfile\User;

use RuntimeException;

final class CannotDeleteSelfException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('You cannot delete your own user account.');
    }
}
