<?php

declare(strict_types=1);

namespace NeneProfile\User;

final readonly class GetUserByIdUseCase implements GetUserByIdUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(GetUserByIdInput $input): User
    {
        $user = $this->users->findByIdInOrganization($input->id, $input->organizationId);

        if ($user === null) {
            throw new UserNotFoundException($input->id);
        }

        return $user;
    }
}
