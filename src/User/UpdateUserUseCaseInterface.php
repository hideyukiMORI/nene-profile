<?php

declare(strict_types=1);

namespace NeneProfile\User;

interface UpdateUserUseCaseInterface
{
    public function execute(?int $actorUserId, UpdateUserInput $input): User;
}
