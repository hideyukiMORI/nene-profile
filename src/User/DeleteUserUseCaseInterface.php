<?php

declare(strict_types=1);

namespace NeneProfile\User;

interface DeleteUserUseCaseInterface
{
    public function execute(?int $actorUserId, DeleteUserInput $input): void;
}
