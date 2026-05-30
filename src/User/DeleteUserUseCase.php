<?php

declare(strict_types=1);

namespace NeneProfile\User;

use NeneProfile\Audit\AuditRecorderInterface;

final readonly class DeleteUserUseCase implements DeleteUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(?int $actorUserId, DeleteUserInput $input): void
    {
        if ($actorUserId !== null && $actorUserId === $input->id) {
            throw new CannotDeleteSelfException();
        }

        $existing = $this->users->findByIdInOrganization($input->id, $input->organizationId);

        if ($existing === null) {
            throw new UserNotFoundException($input->id);
        }

        $before = UserSnapshot::toArray($existing);

        $this->users->delete($input->id);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->organizationId,
            action: 'user.deleted',
            entityType: 'user',
            entityId: $input->id,
            before: $before,
            after: null,
        );
    }
}
