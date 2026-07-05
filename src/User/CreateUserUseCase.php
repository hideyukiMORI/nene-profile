<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NeneProfile\Auth\Role;

final readonly class CreateUserUseCase implements CreateUserUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface $usersFactory
     */
    public function __construct(
        private UserRepositoryInterface $users,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $usersFactory,
        private AuditRecorderFactoryInterface $auditFactory,
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

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($organizationId, $actorUserId, $role, $input): User {
            $users = ($this->usersFactory)($exec);

            $id = $users->save(new User(
                id: 0,
                email: $input->email,
                passwordHash: password_hash($input->password, PASSWORD_BCRYPT),
                role: $role->value,
                organizationId: $organizationId,
                status: 'active',
            ));

            $created = $users->findById($id);
            assert($created !== null);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'user.created',
                entityType: 'user',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: null,
                after: UserSnapshot::toArray($created),
            ));

            return $created;
        });
    }
}
