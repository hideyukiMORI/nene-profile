<?php

declare(strict_types=1);

namespace NeneProfile\User;

interface ListUsersUseCaseInterface
{
    public function execute(ListUsersInput $input): ListUsersOutput;
}
