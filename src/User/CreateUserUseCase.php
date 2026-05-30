<?php

declare(strict_types=1);

namespace NeneProfile\User;

use NeneProfile\Audit\AuditRecorderInterface;
use NeneProfile\Auth\Role;

final readonly class CreateUserUseCase implements CreateUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AuditRecorderInterface $audit,
    ) {
    }

    /**
     * Creates a user in the caller's organization. The organization comes from
     * the resolved tenant context, never from request input, so a user cannot be
     * created in another tenant.
     */
    public function execute(int $organizationId, ?int $actorUserId, CreateUserInput $input): User
    {
        $role = Role::tryFrom($input->role);

        if ($role === null || $role === Role::Superadmin) {
            throw new RoleNotAssignableException($input->role);
        }

        if ($this->users->emailExists($input->email)) {
            throw new UserEmailConflictException($input->email);
        }

        $id = $this->users->save(new User(
            id: 0,
            email: $input->email,
            passwordHash: password_hash($input->password, PASSWORD_BCRYPT),
            role: $role->value,
            organizationId: $organizationId,
            status: 'active',
        ));

        $created = $this->users->findById($id);
        assert($created !== null);

        $this->audit->record(
            actorUserId: $actorUserId,
            organizationId: $organizationId,
            action: 'user.created',
            entityType: 'user',
            entityId: $id,
            before: null,
            after: UserSnapshot::toArray($created),
        );

        return $created;
    }
}
