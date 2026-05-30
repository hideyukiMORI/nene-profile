<?php

declare(strict_types=1);

namespace NeneProfile\User;

final readonly class ListUsersUseCase implements ListUsersUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(ListUsersInput $input): ListUsersOutput
    {
        $items = $this->users->findByOrganizationId($input->organizationId, $input->limit, $input->offset);
        $total = $this->users->countByOrganizationId($input->organizationId);

        return new ListUsersOutput(
            items: $items,
            total: $total,
            limit: $input->limit,
            offset: $input->offset,
        );
    }
}
