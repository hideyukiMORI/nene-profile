<?php

declare(strict_types=1);

namespace NeneProfile\Tests\User;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NeneProfile\Tests\Audit\InMemoryAuditRecorderFactory;
use NeneProfile\Tests\Support\FixedClock;
use NeneProfile\Tests\Support\ImmediateTransactionManager;
use NeneProfile\User\CreateUserInput;
use NeneProfile\User\CreateUserUseCase;
use NeneProfile\User\DeleteUserInput;
use NeneProfile\User\DeleteUserUseCase;
use NeneProfile\User\GetUserByIdInput;
use NeneProfile\User\GetUserByIdUseCase;
use NeneProfile\User\ListUsersInput;
use NeneProfile\User\ListUsersUseCase;
use NeneProfile\User\RoleNotAssignableException;
use NeneProfile\User\UpdateUserInput;
use NeneProfile\User\UpdateUserUseCase;
use NeneProfile\User\User;
use NeneProfile\User\UserEmailConflictException;
use NeneProfile\User\UserNotFoundException;
use NeneProfile\User\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class UserUseCaseBoundaryTest extends TestCase
{
    private InMemoryUserRepository $users;
    private AuditRecorderFactoryInterface $audit;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->audit = new InMemoryAuditRecorderFactory(new FixedClock());
    }

    /** @return Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface */
    private function usersFactory(): Closure
    {
        $repo = $this->users;

        return static fn (DatabaseQueryExecutorInterface $exec): UserRepositoryInterface => $repo;
    }

    private function createUserUseCase(): CreateUserUseCase
    {
        return new CreateUserUseCase($this->users, new ImmediateTransactionManager(), $this->usersFactory(), $this->audit);
    }

    private function updateUserUseCase(): UpdateUserUseCase
    {
        return new UpdateUserUseCase($this->users, new ImmediateTransactionManager(), $this->usersFactory(), $this->audit);
    }

    private function deleteUserUseCase(): DeleteUserUseCase
    {
        return new DeleteUserUseCase($this->users, new ImmediateTransactionManager(), $this->usersFactory(), $this->audit);
    }

    private static int $userCounter = 0;

    private function savedUser(int $orgId = 1, string $role = 'member'): User
    {
        $seq = ++self::$userCounter;
        $id  = $this->users->save(new User(
            id: 0,
            email: "user{$seq}@example.com",
            passwordHash: password_hash('password', PASSWORD_BCRYPT),
            role: $role,
            organizationId: $orgId,
        ));

        $user = $this->users->findById($id);
        assert($user !== null);

        return $user;
    }

    // ── CreateUser ────────────────────────────────────────────────────────

    public function test_create_rejects_superadmin_role(): void
    {
        $this->expectException(RoleNotAssignableException::class);

        ($this->createUserUseCase())->execute(
            1,
            1,
            new CreateUserInput(email: 'admin@example.com', password: 'password', role: 'superadmin'),
        );
    }

    public function test_create_rejects_unknown_role(): void
    {
        $this->expectException(RoleNotAssignableException::class);

        ($this->createUserUseCase())->execute(
            1,
            1,
            new CreateUserInput(email: 'a@example.com', password: 'password', role: 'god'),
        );
    }

    public function test_create_rejects_duplicate_email(): void
    {
        $this->expectException(UserEmailConflictException::class);

        $uc = $this->createUserUseCase();
        $uc->execute(1, 1, new CreateUserInput(email: 'dup@example.com', password: 'password', role: 'member'));
        $uc->execute(1, 1, new CreateUserInput(email: 'dup@example.com', password: 'password2', role: 'admin'));
    }

    public function test_create_accepts_all_non_superadmin_roles(): void
    {
        $uc = $this->createUserUseCase();

        foreach (['admin', 'member', 'viewer'] as $i => $role) {
            $user = $uc->execute(1, 1, new CreateUserInput(
                email: "role{$i}@example.com",
                password: 'password',
                role: $role,
            ));
            $this->assertSame($role, $user->role);
        }
    }

    // ── GetUserById ───────────────────────────────────────────────────────

    public function test_get_rejects_cross_tenant(): void
    {
        $this->expectException(UserNotFoundException::class);

        $user = $this->savedUser(orgId: 1);

        (new GetUserByIdUseCase($this->users))->execute(
            new GetUserByIdInput($user->id, organizationId: 2), // wrong org
        );
    }

    public function test_get_returns_404_for_nonexistent_id(): void
    {
        $this->expectException(UserNotFoundException::class);

        (new GetUserByIdUseCase($this->users))->execute(new GetUserByIdInput(99999, 1));
    }

    public function test_get_returns_404_for_id_zero(): void
    {
        $this->expectException(UserNotFoundException::class);

        (new GetUserByIdUseCase($this->users))->execute(new GetUserByIdInput(0, 1));
    }

    // ── ListUsers ─────────────────────────────────────────────────────────

    public function test_list_returns_empty_for_org_with_no_users(): void
    {
        $output = (new ListUsersUseCase($this->users))->execute(
            new ListUsersInput(organizationId: 999, limit: 20, offset: 0),
        );

        $this->assertCount(0, $output->items);
        $this->assertSame(0, $output->total);
    }

    public function test_list_offset_beyond_total_returns_empty(): void
    {
        $this->savedUser(1);
        $this->savedUser(1);

        $output = (new ListUsersUseCase($this->users))->execute(
            new ListUsersInput(organizationId: 1, limit: 20, offset: 100),
        );

        $this->assertCount(0, $output->items);
        $this->assertSame(2, $output->total);
    }

    public function test_list_limit_1_returns_single_item(): void
    {
        $this->savedUser(1);
        $this->savedUser(1);

        $output = (new ListUsersUseCase($this->users))->execute(
            new ListUsersInput(organizationId: 1, limit: 1, offset: 0),
        );

        $this->assertCount(1, $output->items);
        $this->assertSame(2, $output->total);
    }

    public function test_list_does_not_leak_other_org_users(): void
    {
        $this->savedUser(1);
        $this->savedUser(2); // different org

        $output = (new ListUsersUseCase($this->users))->execute(
            new ListUsersInput(organizationId: 1, limit: 20, offset: 0),
        );

        $this->assertCount(1, $output->items);
        $this->assertSame(1, $output->items[0]->organizationId);
    }

    // ── UpdateUser ────────────────────────────────────────────────────────

    public function test_update_rejects_superadmin_role_assignment(): void
    {
        $this->expectException(RoleNotAssignableException::class);

        $user = $this->savedUser();

        ($this->updateUserUseCase())->execute(
            1,
            new UpdateUserInput(id: $user->id, organizationId: 1, role: 'superadmin'),
        );
    }

    public function test_update_cross_tenant_throws_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        $user = $this->savedUser(orgId: 1);

        ($this->updateUserUseCase())->execute(
            1,
            new UpdateUserInput(id: $user->id, organizationId: 2, role: 'admin'),
        );
    }

    public function test_update_null_fields_are_no_ops(): void
    {
        $user = $this->savedUser();

        $updated = ($this->updateUserUseCase())->execute(
            1,
            new UpdateUserInput(id: $user->id, organizationId: 1, role: null, status: null, password: null),
        );

        $this->assertSame($user->role, $updated->role);
    }

    // ── DeleteUser ────────────────────────────────────────────────────────

    public function test_delete_cross_tenant_throws_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        $user = $this->savedUser(orgId: 1);

        ($this->deleteUserUseCase())->execute(
            999,
            new DeleteUserInput($user->id, organizationId: 2),
        );
    }

    public function test_delete_nonexistent_throws_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        ($this->deleteUserUseCase())->execute(
            1,
            new DeleteUserInput(99999, organizationId: 1),
        );
    }
}
