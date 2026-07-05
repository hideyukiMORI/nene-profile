<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NeneProfile\Auth\Role;

final readonly class UpdateUserUseCase implements UpdateUserUseCaseInterface
{
    private const ALLOWED_STATUSES = ['active', 'invited'];

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

    public function execute(?int $actorUserId, UpdateUserInput $input): User
    {
        $existing = $this->users->findByIdInOrganization($input->id, $input->organizationId);

        if ($existing === null) {
            throw new UserNotFoundException($input->id);
        }

        $before = UserSnapshot::toArray($existing);

        $role = null;

        if ($input->role !== null) {
            $role = Role::tryFrom($input->role);

            if ($role === null || $role === Role::Superadmin) {
                throw new RoleNotAssignableException($input->role);
            }
        }

        if ($input->status !== null && !in_array($input->status, self::ALLOWED_STATUSES, true)) {
            throw new RoleNotAssignableException($input->status);
        }

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $before, $role): User {
            $users = ($this->usersFactory)($exec);

            if ($role !== null) {
                $users->updateRole($input->id, $role->value);
            }

            if ($input->status !== null) {
                $users->updateStatus($input->id, $input->status);
            }

            if ($input->password !== null) {
                $users->updatePassword($input->id, password_hash($input->password, PASSWORD_BCRYPT));
            }

            $updated = $users->findById($input->id);
            assert($updated !== null);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'user.updated',
                entityType: 'user',
                entityId: $input->id,
                actorId: $actorUserId,
                organizationId: $input->organizationId,
                before: $before,
                after: UserSnapshot::toArray($updated),
            ));

            return $updated;
        });
    }
}
