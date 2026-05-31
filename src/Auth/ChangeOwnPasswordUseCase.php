<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

use NeneProfile\User\UserRepositoryInterface;

final readonly class ChangeOwnPasswordUseCase implements ChangeOwnPasswordUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(ChangeOwnPasswordInput $input): void
    {
        $user = $this->users->findById($input->actorUserId);

        if ($user === null || !password_verify($input->currentPassword, $user->passwordHash)) {
            throw new InvalidCurrentPasswordException();
        }

        $this->users->updatePassword($input->actorUserId, password_hash($input->newPassword, PASSWORD_BCRYPT));
    }
}
