<?php

declare(strict_types=1);

namespace NeneProfile\User;

use NeneProfile\Audit\AuditRecorderInterface;
use NeneProfile\Auth\Role;

final readonly class UpdateUserUseCase implements UpdateUserUseCaseInterface
{
    private const ALLOWED_STATUSES = ['active', 'invited'];

    public function __construct(
        private UserRepositoryInterface $users,
        private AuditRecorderInterface $audit,
    ) {
    }

    public function execute(?int $actorUserId, UpdateUserInput $input): User
    {
        $existing = $this->users->findByIdInOrganization($input->id, $input->organizationId);

        if ($existing === null) {
            throw new UserNotFoundException($input->id);
        }

        $before = UserSnapshot::toArray($existing);

        if ($input->role !== null) {
            $role = Role::tryFrom($input->role);

            if ($role === null || $role === Role::Superadmin) {
                throw new RoleNotAssignableException($input->role);
            }

            $this->users->updateRole($input->id, $role->value);
        }

        if ($input->status !== null) {
            if (!in_array($input->status, self::ALLOWED_STATUSES, true)) {
                throw new RoleNotAssignableException($input->status);
            }

            $this->users->updateStatus($input->id, $input->status);
        }

        if ($input->password !== null) {
            $this->users->updatePassword($input->id, password_hash($input->password, PASSWORD_BCRYPT));
        }

        $updated = $this->users->findById($input->id);
        assert($updated !== null);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $input->organizationId,
            action: 'user.updated',
            entityType: 'user',
            entityId: $input->id,
            before: $before,
            after: UserSnapshot::toArray($updated),
        );

        return $updated;
    }
}
