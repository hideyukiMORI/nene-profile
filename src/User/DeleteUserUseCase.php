<?php

declare(strict_types=1);

namespace NeneProfile\User;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;

final readonly class DeleteUserUseCase implements DeleteUserUseCaseInterface
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

        $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $input, $before): void {
            $users = ($this->usersFactory)($exec);

            $users->delete($input->id);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'user.deleted',
                entityType: 'user',
                entityId: $input->id,
                actorId: $actorUserId,
                organizationId: $input->organizationId,
                before: $before,
                after: null,
            ));
        });
    }
}
