<?php

declare(strict_types=1);

namespace NeneProfile\User;

interface GetUserByIdUseCaseInterface
{
    public function execute(GetUserByIdInput $input): User;
}
