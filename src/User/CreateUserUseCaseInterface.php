<?php

declare(strict_types=1);

namespace NeneProfile\User;

interface CreateUserUseCaseInterface
{
    public function execute(int $organizationId, ?int $actorUserId, CreateUserInput $input): User;
}
