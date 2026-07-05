<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use NeneProfile\User\CreateUserInput;
use NeneProfile\User\CreateUserUseCase;
use NeneProfile\User\RoleNotAssignableException;
use NeneProfile\User\User;
use NeneProfile\User\UserEmailConflictException;
use NeneProfile\User\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateUserUseCaseTest extends TestCase
{
    private InMemoryUserRepository $repo;
    private InMemoryAuditRecorderFactory $auditRepo;
    private CreateUserUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo      = new InMemoryUserRepository();
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        $this->useCase   = new CreateUserUseCase(
            $this->repo,
            new ImmediateTransactionManager(),
            $this->usersFactory(),
            $this->auditRepo,
        );
    }

    /** @return Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface */
    private function usersFactory(): Closure
    {
        $repo = $this->repo;

        return static fn (DatabaseQueryExecutorInterface $exec): UserRepositoryInterface => $repo;
    }

    public function test_creates_user_scoped_to_organization(): void
    {
        $user = $this->useCase->execute(7, 1, new CreateUserInput(
            email: 'member@example.com',
            password: 'password123',
            role: 'member',
        ));

        $this->assertSame('member@example.com', $user->email);
        $this->assertSame('member', $user->role);
        $this->assertSame(7, $user->organizationId);
    }

    public function test_password_is_hashed_not_stored_plaintext(): void
    {
        $user = $this->useCase->execute(7, 1, new CreateUserInput(
            email: 'm@example.com',
            password: 'password123',
            role: 'member',
        ));

        $this->assertNotSame('password123', $user->passwordHash);
        $this->assertTrue(password_verify('password123', $user->passwordHash));
    }

    public function test_rejects_superadmin_role(): void
    {
        $this->expectException(RoleNotAssignableException::class);

        $this->useCase->execute(7, 1, new CreateUserInput(
            email: 'x@example.com',
            password: 'password123',
            role: 'superadmin',
        ));
    }

    public function test_rejects_unknown_role(): void
    {
        $this->expectException(RoleNotAssignableException::class);

        $this->useCase->execute(7, 1, new CreateUserInput(
            email: 'x@example.com',
            password: 'password123',
            role: 'wizard',
        ));
    }

    public function test_rejects_duplicate_email(): void
    {
        $this->repo->seed(new User(
            id: 0,
            email: 'dup@example.com',
            passwordHash: 'x',
            role: 'member',
            organizationId: 7,
        ));

        $this->expectException(UserEmailConflictException::class);

        $this->useCase->execute(7, 1, new CreateUserInput(
            email: 'dup@example.com',
            password: 'password123',
            role: 'member',
        ));
    }

    public function test_records_audit_log_without_password_hash(): void
    {
        $user = $this->useCase->execute(7, 99, new CreateUserInput(
            email: 'audited@example.com',
            password: 'password123',
            role: 'member',
        ));

        $log = $this->auditRepo->appended[0];
        $this->assertSame('user.created', $log->action);
        $this->assertSame('user', $log->entityType);
        $this->assertSame($user->id, $log->entityId);
        $this->assertSame(7, $log->organizationId);
        $this->assertSame(99, $log->actorId);
        $this->assertNull($log->before);
        $this->assertNotNull($log->after);
        $this->assertArrayNotHasKey('password_hash', $log->after);
        $this->assertSame('audited@example.com', $log->after['email']);
    }
}
